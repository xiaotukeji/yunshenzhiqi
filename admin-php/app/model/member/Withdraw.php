<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\member;

use app\model\BaseModel;
use app\model\message\Message;
use app\model\message\Sms;
use app\model\shop\ShopAcceptMessage;
use app\model\system\Config as ConfigModel;
use app\model\system\Pay;
use app\model\system\PayTransfer;
use app\model\system\Stat;
use think\facade\Cache;
use addon\memberwithdraw\model\Withdraw as MemberWithdraw;
use addon\wechat\model\Message as WechatMessage;
use addon\weapp\model\Message as WeappMessage;
use think\facade\Db;

/**
 * 会员提现
 */
class Withdraw extends BaseModel
{
    const STATUS_WAIT_AUDIT = 0;//待审核
    const STATUS_WAIT_TRANSFER = 1;//待转账
    const STATUS_SUCCESS = 2;//已转账
    const STATUS_IN_PROCESS = 3;//转账中
    const STATUS_FAIL = -2;//转账失败
    const STATUS_REFUSE = -1;//已拒绝

    public $status = array (
        self::STATUS_WAIT_AUDIT => '待审核',
        self::STATUS_WAIT_TRANSFER => '待转账',
        self::STATUS_SUCCESS => '已转账',
        self::STATUS_IN_PROCESS => '转账中',
        self::STATUS_FAIL => '转账失败',
        self::STATUS_REFUSE => '已拒绝',
    );

    /**************************************************************************** 会员提现设置 *************************************************************/
    /**
     * 会员提现设置
     * @param $data
     * @param $is_use
     * @param int $site_id
     * @param string $app_module
     * @return array
     */
    public function setConfig($data, $is_use, $site_id = 0, $app_module = 'shop')
    {
        $config = new ConfigModel();
        $res = $config->setConfig($data, '会员提现设置', $is_use, [ [ 'site_id', '=', $site_id ], [ 'app_module', '=', $app_module ], [ 'config_key', '=', 'MEMBER_WITHDRAW_CONFIG' ] ]);
        return $res;
    }

    /**
     * 会员提现设置
     * @param int $site_id
     * @param string $app_module
     * @return array
     */
    public function getConfig($site_id = 0, $app_module = 'shop')
    {
        $config = new ConfigModel();
        $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', $app_module ], [ 'config_key', '=', 'MEMBER_WITHDRAW_CONFIG' ] ]);
        if (empty($res[ 'data' ][ 'value' ])) {
            $res[ 'data' ][ 'value' ] = [
                'is_auto_audit' => 0,
                'rate' => 0,
                'transfer_type' => '',
                'is_auto_transfer' => 0,
                'min' => 0,
                'max' => 0,
            ];
        }
        return $res;
    }
    /**************************************************************************** 会员提现设置 *************************************************************/
    /**
     * 申请提现
     * @param $data
     * @param int $site_id
     * @param string $app_module
     * @return array
     */
    public function apply($data, $site_id = 0, $app_module = 'shop')
    {

        $config_result = $this->getConfig($site_id, $app_module);
        $config = $config_result[ "data" ][ 'value' ];
        if ($config_result[ "data" ][ "is_use" ] == 0)
            return $this->error([], "提现未开启");

        $withdraw_no = $this->createWithdrawNo();
        $apply_money = round($data[ "apply_money" ], 2);
        if ($apply_money < $config[ "min" ])
            return $this->error([], "申请提现金额不能小于最低提现额度" . $config[ "min" ]);
        if ($apply_money > $config[ 'max' ]) return $this->error([], "申请提现金额不能大于最高提现额度" . $config[ "max" ]);
        $member_id = $data[ "member_id" ];
        $member_model = new Member();
        $member_info_result = $member_model->getMemberInfo([ [ "member_id", "=", $member_id ] ], "balance_money,headimg,wx_openid,username,mobile,weapp_openid,nickname");
        $member_info = $member_info_result[ "data" ];
        if (empty($member_info))
            return $this->error([], "MEMBER_NOT_EXIST");

        $balance_money = $member_info[ "balance_money" ];
        if ($apply_money > $balance_money)
            return $this->error([], "申请提现金额不能大于会员可提现金额");
        $transfer_type = $data[ "transfer_type" ];
        $transfer_type_list = $this->getTransferType($site_id, $app_module);
        $transfer_type_name = $transfer_type_list[ $transfer_type ] ?? '';
        if (empty($transfer_type_name))
            return $this->error([], "不支持的提现方式");

        model('member_withdraw')->startTrans();
        try {
            $rate = $config[ "rate" ];
            $bank_name = "";
            $account_number = "";
            $applet_type = 0;
            switch ( $transfer_type ) {
                case "bank":
                    $bank_name = $data[ "bank_name" ];
                    $account_number = $data[ "account_number" ];

                    break;
                case "alipay":
                    $bank_name = '';
                    $account_number = $data[ "account_number" ];
                    break;
                case "wechatpay":
                    $bank_name = '';
                    if (empty($member_info[ "wx_openid" ]) && empty($member_info[ "weapp_openid" ])) {
                        return $this->error('', '请绑定微信或更换提现账户');
                    }
                    if ($data['app_type'] != 'weapp') {
                        $account_number = $member_info[ "wx_openid" ];
                        $applet_type = 0; // 公众号
                    } else {
                        $account_number = $member_info[ "weapp_openid" ];
                        $applet_type = 1; // 小程序
                    }
                    break;

            }

            $service_money = round($apply_money * $rate / 100, 2);//手续费
            $money = $apply_money - $service_money;
            $data = array (
                "site_id" => $site_id,
                "withdraw_no" => $withdraw_no,
                "member_name" => $member_info[ "username" ] == '' ? $member_info[ "mobile" ] : $member_info[ "username" ],
                "member_id" => $data[ "member_id" ],
                "transfer_type" => $data[ "transfer_type" ],
                "transfer_type_name" => $transfer_type_name,
                "apply_money" => $apply_money,
                "service_money" => $service_money,
                "rate" => $rate,
                "money" => $money,
                "apply_time" => time(),
                "status" => self::STATUS_WAIT_AUDIT,
                "status_name" => $this->status[self::STATUS_WAIT_AUDIT],
                "member_headimg" => $member_info[ "headimg" ],
                "realname" => $data[ "realname" ],
                "bank_name" => $bank_name,
                "account_number" => $account_number,
                "mobile" => $data[ "mobile" ],
                "applet_type" => $applet_type
            );

            //减少现金余额
            $member_account = new MemberAccount();
            $account_res = $member_account->addMemberAccount($site_id, $member_id, 'balance_money', -$apply_money, 'withdraw', '会员提现', '会员提现扣除');
            if ($account_res[ 'code' ] < 0) {
                model('member_withdraw')->rollback();
                return $account_res;
            }

            //增加提现中余额
            model("member")->setInc([ [ "member_id", "=", $member_id ] ], "balance_withdraw_apply", $apply_money);

            $result = model("member_withdraw")->add($data);

            //添加转账记录
            $pay_transfer_model = new PayTransfer();
            $info = model('member_withdraw')->getInfo([['id', '=', $result]]);
            $pay_transfer_model->add([
                "real_name" => $info[ "realname" ],
                "amount" => $info[ "money" ],
                "desc" => "会员提现" . $info[ "memo" ],
                "transfer_type" => $info[ "transfer_type" ],
                "account_number" => $info[ "account_number" ],
                "site_id" => $info[ "site_id" ],
                "is_weapp" => $info[ "applet_type" ],
                "member_id" => $info[ 'member_id' ],
                'from_type' => 'member_withdraw',
                "relate_tag" => $info['id'],
            ]);

            //是否启用自动通过审核(必须是微信)
            if ($config[ "is_auto_audit" ] == 0) {
                $this->agree([ [ "id", "=", $result ], [ 'site_id', '=', $site_id ] ]);
            }

            model('member_withdraw')->commit();

            //申请提现发送消息
            $data[ 'keywords' ] = 'USER_WITHDRAWAL_APPLY';
            $data[ 'member_name' ] = $member_info[ 'nickname' ];
            $message_model = new Message();
            $message_model->sendMessage($data);

            return $this->success();
        } catch (\Exception $e) {
            model('member_withdraw')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 同意提现申请
     * @param $condition
     * @return array
     */
    public function agree($condition)
    {
        $check_condition = array_column($condition, 2, 0);
        $site_id = $check_condition[ 'site_id' ];
        $app_module = $check_condition[ 'app_module' ] ?? 'shop';
        if (empty($site_id)) {
            return $this->error(-1, '参数错误');
        }
        $info = model("member_withdraw")->getInfo($condition);
        if (empty($info))
            return $this->error();

        $config_result = $this->getConfig($site_id, $app_module);
        $config = $config_result[ "data" ];

        model('member_withdraw')->startTrans();
        try {
            $data = array (
                "status" => self::STATUS_WAIT_TRANSFER,
                "status_name" => $this->status[self::STATUS_WAIT_TRANSFER],
                "audit_time" => time(),
            );
            $result = model("member_withdraw")->update($data, $condition);

            //是否启用自动转账(必须是微信或支付宝)
            if ($config[ "value" ][ "is_auto_transfer" ] == 1) {
                $member_withdraw_model = new MemberWithdraw();
                $member_withdraw_model->transfer($info[ "id" ]);
                /*if ($transfer_res['code'] == 0) {
                    //提现成功发送消息
                    $info['keywords'] = 'USER_WITHDRAWAL_SUCCESS';
                    $message_model = new Message();
                    $res = $message_model->sendMessage($info);
                }*/
            }
            model('member_withdraw')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('member_withdraw')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 拒绝提现申请
     * @param $condition
     * @param $param
     * @return array
     */
    public function refuse($condition, $param)
    {
        $info = model("member_withdraw")->getInfo($condition, "status,site_id,transfer_type,member_id,apply_money");
        if (empty($info)){
            return $this->error(null, '提现信息有误');
        }
        if (!in_array($info['status'], [self::STATUS_WAIT_AUDIT, self::STATUS_WAIT_TRANSFER])){
            return $this->error(null, '提现状态有误');
        }

        model('member_withdraw')->startTrans();
        try {
            $data = array (
                "status" => self::STATUS_REFUSE,
                "status_name" => $this->status[self::STATUS_REFUSE],
                "refuse_reason" => $param[ "refuse_reason" ],
                "audit_time" => time(),
            );
            $result = model("member_withdraw")->update($data, $condition);

            //增加现金余额
            $member_account = new MemberAccount();
            $account_res = $member_account->addMemberAccount($info[ 'site_id' ], $info[ 'member_id' ], 'balance_money', $info[ "apply_money" ], 'withdraw', '会员提现申请未通过', '提现申请未通过返还');
            if ($account_res[ 'code' ] != 0) {
                model('member_withdraw')->rollback();
                return $account_res;
            }
            //减少提现中余额
            model("member")->setDec([ [ "member_id", "=", $info[ "member_id" ] ] ], "balance_withdraw_apply", $info[ "apply_money" ]);

            model('member_withdraw')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('member_withdraw')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 提现转账完成
     * @param array $param
     * @return array
     */
    public function transferFinish($param = [])
    {
        $condition = [
            [ 'id', '=', $param[ 'id' ] ],
            [ 'site_id', '=', $param[ 'site_id' ] ],
            [ 'status', 'in', [self::STATUS_WAIT_TRANSFER, self::STATUS_IN_PROCESS] ],
        ];
        $info = model("member_withdraw")->getInfo($condition);
        if (empty($info)) return $this->error();

        $payment_time = time();
        model('member_withdraw')->startTrans();
        try {
            $data = [
                'status' => self::STATUS_SUCCESS,
                'status_name' => $this->status[self::STATUS_SUCCESS],
                'payment_time' => $payment_time,
                'certificate' => $param[ 'certificate' ] ?? '',
                'certificate_remark' => $param[ 'certificate_remark' ] ?? ''
            ];
            $result = model("member_withdraw")->update($data, $condition);

            //增加已提现余额
            model("member")->setInc([ [ "member_id", "=", $info[ "member_id" ] ] ], "balance_withdraw", $info[ "apply_money" ]);
            //减少提现中余额
            model("member")->setDec([ [ "member_id", "=", $info[ "member_id" ] ] ], "balance_withdraw_apply", $info[ "apply_money" ]);

            model('member_withdraw')->commit();

            $member_info = model("member")->getInfo([ [ "member_id", "=", $info[ "member_id" ] ] ], 'nickname');

            //提现成功发送消息
            $info[ 'keywords' ] = 'USER_WITHDRAWAL_SUCCESS';
            $info[ 'payment_time' ] = $payment_time;
            $info[ 'member_name' ] = $member_info[ 'nickname' ];
            $message_model = new Message();
            $message_model->sendMessage($info);

            $stat_model = new Stat();
            $stat_model->switchStat([ 'type' => 'member_withdraw', 'data' => [ 'site_id' => $info[ 'site_id' ], 'id' => $info[ 'id' ] ] ]);
            return $this->success();
        } catch (\Exception $e) {
            model('member_withdraw')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 转账失败
     * @param $param
     * @return array
     */
    public function transferFail($param)
    {
        $id = $param['id'];
        $site_id = $param['site_id'];
        $fail_reason = $param['fail_reason'];

        $condition = [
            [ 'id', '=', $id ],
            [ 'site_id', '=', $site_id ],
            [ 'status', 'in', [self::STATUS_WAIT_TRANSFER, self::STATUS_IN_PROCESS] ],
        ];
        $info = model("member_withdraw")->getInfo($condition);
        if (empty($info)) return $this->error(null, '提现信息有误');

        model('member_withdraw')->startTrans();
        try {
            $data = [
                'status' => self::STATUS_FAIL,
                'status_name' => $this->status[self::STATUS_FAIL],
                'fail_reason' => $fail_reason,
            ];
            model("member_withdraw")->update($data, $condition);

            //减少提现中余额
            model("member")->setDec([ [ "member_id", "=", $info[ "member_id" ] ] ], "balance_withdraw_apply", $info[ "apply_money" ]);

            //减少现金余额
            $member_account = new MemberAccount();
            $account_res = $member_account->addMemberAccount($site_id, $info[ "member_id" ], 'balance_money', $info[ "apply_money" ], 'withdraw_fail', $id, '会员提现失败退回');
            if ($account_res[ 'code' ] < 0) {
                model('member_withdraw')->rollback();
                return $account_res;
            }

            model('member_withdraw')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('member_withdraw')->rollback();
            return $this->error(['file' => $e->getFile(), 'line' => $e->getLine(), 'message' => $e->getMessage()], $e->getMessage());
        }
    }

    /**
     * 转账中
     * @param $param
     * @return array
     */
    public function transferInProcess($param)
    {
        $id = $param['id'];
        $site_id = $param['site_id'];

        $condition = [
            [ 'id', '=', $id ],
            [ 'site_id', '=', $site_id ],
            [ 'status', '=', self::STATUS_WAIT_TRANSFER ]
        ];
        $info = model("member_withdraw")->getInfo($condition);
        if (empty($info)) return $this->error(null, '提现信息有误');

        model("member_withdraw")->update([
            'status' => self::STATUS_IN_PROCESS,
            'status_name' => $this->status[self::STATUS_IN_PROCESS],
        ], [['id', '=', $id]]);

        return $this->success();
    }

    /**
     * @param $condition
     * @param string $field
     * @return array
     */
    public function getMemberWithdrawInfo($condition, $field = "*")
    {
        $info = model('member_withdraw')->getInfo($condition, $field);
        return $this->success($info);
    }

    /**
     * 提现详情
     * @param $condition
     * @return array
     */
    public function getMemberWithdrawDetail($condition)
    {
        $info = model('member_withdraw')->getInfo($condition, "*");
        return $this->success($info);
    }

    /**
     * 提现单数
     * @param $condition
     * @return array
     */
    public function getMemberWithdrawCount($condition)
    {
        $count = model('member_withdraw')->getCount($condition, "id");
        return $this->success($count);
    }

    /**
     * 提现总和
     * @param $condition
     * @param string $field
     * @return array
     */
    public function getMemberWithdrawSum($condition, $field = 'apply_money')
    {
        $count = model('member_withdraw')->getSum($condition, $field);
        return $this->success($count);
    }

    /**
     * 获取会员提现分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @param string $alias
     * @param string $join
     * @return array
     */
    public function getMemberWithdrawPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*',$alias='',$join='')
    {
        $list = model('member_withdraw')->pageList($condition, $field, $order, $page, $page_size, $alias, $join, '');
        return $this->success($list);
    }

    /**
     * 获取会员提现列表
     * @param array $where
     * @param bool $field
     * @param string $order
     * @param string $alias
     * @param array $join
     * @param string $group
     * @param null $limit
     * @return array
     */
    public function getMemberWithdrawList($where = [], $field = true, $order = '', $alias = 'a', $join = [], $group = '', $limit = null)
    {
        $res = model('member_withdraw')->getList($where, $field, $order, $alias, $join, $group, $limit);
        return $this->success($res);
    }

    /**
     * 提现流水号
     */
    private function createWithdrawNo()
    {
        $cache = Cache::get("member_withdraw_no" . time());
        if (empty($cache)) {
            Cache::set("niutk" . time(), 1000);
            $cache = Cache::get("member_withdraw_no" . time());
        } else {
            $cache = $cache + 1;
            Cache::set("member_withdraw_no" . time(), $cache);
        }
        $no = date('Ymdhis', time()) . rand(1000, 9999) . $cache;
        return $no;
    }

    /**
     * 转账方式
     * @param int $site_id
     * @param string $app_module
     * @return array
     */
    public function getTransferType($site_id = 0, $app_module = 'shop')
    {
        $pay_model = new Pay();
        $transfer_type_list = $pay_model->getTransferType($site_id);
        $config_result = $this->getConfig($site_id, $app_module);
        $config = $config_result[ "data" ][ 'value' ];
        $data = [];
        $support_type = explode(",", $config[ "transfer_type" ]);
        foreach ($transfer_type_list as $k => $v) {
            if (in_array($k, $support_type)) {
                $data[ $k ] = $v;
            }
        }
        return $data;
    }

    /**
     * 会员提现成功通知
     * @param $data
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function messageUserWithdrawalSuccess($data)
    {
        //发送短信
        $sms_model = new Sms();

        $var_parse = array (
            'username' => $data[ "member_name" ],//会员名
            'money' => $data[ 'apply_money' ]
        );

        $data[ "sms_account" ] = $data[ "mobile" ];//手机号
        $data[ "var_parse" ] = $var_parse;
        $sms_model->sendMessage($data);

        $member_model = new Member();
        $member_info_result = $member_model->getMemberInfo([ [ "member_id", "=", $data[ "member_id" ] ] ]);
        $member_info = $member_info_result[ "data" ];

        //绑定微信公众号才发送
        if (!empty($member_info) && !empty($member_info[ "wx_openid" ])) {
            $wechat_model = new WechatMessage();
            $data[ "openid" ] = $member_info[ "wx_openid" ];
            $data[ "template_data" ] = [
                'amount1' => $data[ 'apply_money' ], // 提现金额
                'time3' => time_to_date($data[ 'payment_time' ]), // 提现日期
            ];
            $data[ "page" ] = "";
            $wechat_model->sendMessage($data);
        }

        //发送订阅消息
        if (!empty($member_info) && !empty($member_info[ "weapp_openid" ])) {
            $weapp_model = new WeappMessage();
            $data[ "openid" ] = $member_info[ "weapp_openid" ];
            $data[ "template_data" ] = [
                'amount6' => [
                    'value' => $data[ 'apply_money' ]
                ],
                'date3' => [
                    'value' => time_to_date(time())
                ]
            ];
            $data[ "page" ] = "";
            $weapp_model->sendMessage($data);
        }

    }

    /**
     * 会员提现失败通知
     * @param $data
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function messageUserWithdrawalError($data)
    {
        //发送短信
        $sms_model = new Sms();

        $var_parse = array (
            'username' => $data[ "member_name" ],//会员名
            'money' => $data[ 'apply_money' ]
        );

        $data[ "sms_account" ] = $data[ "mobile" ];//手机号
        $data[ "var_parse" ] = $var_parse;
        $sms_model->sendMessage($data);

        $member_model = new Member();
        $member_info_result = $member_model->getMemberInfo([ [ "member_id", "=", $data[ "member_id" ] ] ]);
        $member_info = $member_info_result[ "data" ];

        //绑定微信公众号才发送
        if (!empty($member_info) && !empty($member_info[ "wx_openid" ])) {
            $wechat_model = new WechatMessage();
            $data[ "openid" ] = $member_info[ "wx_openid" ];
            $data[ "template_data" ] = [
                'keyword1' => time_to_date($data[ 'create_time' ]),
                'keyword2' => '审核失败',
                'keyword3' => '会员申请提现',
                'keyword4' => $data[ 'apply_money' ],
            ];
            $data[ "page" ] = "";
            $wechat_model->sendMessage($data);
        }

        //发送订阅消息
        if (!empty($member_info) && !empty($member_info[ "weapp_openid" ])) {
            $weapp_model = new WeappMessage();
            $data[ "openid" ] = $member_info[ "weapp_openid" ];
            $data[ "template_data" ] = [
                'amount3' => [
                    'value' => $data[ 'apply_money' ]
                ],
                'phrase4' => [
                    'value' => '审核失败'
                ],
                'date2' => [
                    'value' => time_to_date(time())
                ]
            ];
            $data[ "page" ] = "";
            $weapp_model->sendMessage($data);
        }

    }

    /**
     * 会员申请提现通知，卖家通知
     * @param $data
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function messageUserWithdrawalApply($data)
    {
        //发送短信
        $sms_model = new Sms();

        $var_parse = array (
            "username" => replaceSpecialChar($data[ "member_name" ]),//会员名
            "money" => $data[ "apply_money" ],//退款申请金额
        );
//        $site_id    = $data['site_id'];
//        $shop_info  = model("shop")->getInfo([["site_id", "=", $site_id]], "mobile,email");
//        $message_data["sms_account"] = $shop_info["mobile"];//手机号
        $data[ "var_parse" ] = $var_parse;

        $shop_accept_message_model = new ShopAcceptMessage();
        $list = $shop_accept_message_model->getShopAcceptMessageList()[ 'data' ];
        if (!empty($list)) {
            foreach ($list as $v) {
                $message_data = $data;
                $message_data[ "sms_account" ] = $v[ "mobile" ];//手机号
                $sms_model->sendMessage($message_data);

                if ($v[ 'wx_openid' ] != "") {

                    $wechat_model = new WechatMessage();
                    $data[ "openid" ] = $v[ 'wx_openid' ];
                    $data[ "template_data" ] = [
                        'thing3' => replaceSpecialChar($data[ "member_name" ]), // 客户名称
                        'amount6' => $data[ "apply_money" ], // 提现金额
                        'time8' => time_to_date($data[ 'apply_time' ]) // 提现时间
                    ];

                    $data[ "page" ] = "";
                    $wechat_model->sendMessage($data);
                }
            }
        }
    }

    public function exportWithdraw($condition, $order)
    {
        try {
            $file_name = date('Y年m月d日-余额提现', time()) . '.csv';
//            $file_name = date('YmdHis').'.csv';//csv文件名
            //通过分批次执行数据导出(防止内存超出配置设置的)
            set_time_limit(0);
            ini_set('memory_limit', '256M');
            //设置header头
            header('Content-Description: File Transfer');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="' . $file_name . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            //打开php数据输入缓冲区
            $fp = fopen('php://output', 'a');
//            fwrite($fp, chr(0xEF).chr(0xBB).chr(0xBF)); // 添加 BOM
            $heade = [ '会员账号', '提现方式', '申请提现金额', '提现手续费', '实际转账金额', '提现状态', '申请时间', '银行名称', '收款账号', '真实姓名', '手机号' ];
            //将数据编码转换成GBK格式
            mb_convert_variables('GBK', 'UTF-8', $heade);
            //将数据格式化为CSV格式并写入到output流中
            fputcsv($fp, $heade);
            //写入第一行表头
            Db::name('member_withdraw')->where($condition)->order($order)->chunk(500, function($item_list) use ($fp) {
                //写入导出信息
                foreach ($item_list as $k => $item_v) {
                    $temp_data = [
                        $item_v[ 'member_name' ] . "\t",
                        $item_v[ 'transfer_type_name' ] . "\t",
                        (float) $item_v[ 'apply_money' ] . "\t",
                        (float) $item_v[ 'service_money' ] . "\t",
                        (float) $item_v[ 'money' ] . "\t",
                        $item_v[ 'status_name' ] . "\t",
                        time_to_date($item_v[ 'apply_time' ]) . "\t",
                        $item_v[ 'bank_name' ] . "\t",
                        $item_v[ 'account_number' ] . "\t",
                        $item_v[ 'realname' ] . "\t",
                        $item_v[ 'mobile' ] . "\t",
                    ];
                    mb_convert_variables('GBK', 'UTF-8', $temp_data);
                    fputcsv($fp, $temp_data);
                    //将已经存储到csv中的变量数据销毁，释放内存
                    unset($item_v);
                }
                unset($item_list);
            });

            //关闭句柄
            fclose($fp);
            die;

        } catch (\Exception $e) {
            return $this->error([], $e->getMessage() . $e->getFile() . $e->getLine());
        }
    }
}

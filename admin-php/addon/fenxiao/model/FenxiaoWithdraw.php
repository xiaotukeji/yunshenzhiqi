<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\fenxiao\model;

use app\model\BaseModel;
use app\model\member\Member;
use app\model\member\MemberAccount;
use app\model\message\Message;
use app\model\message\Sms;
use app\model\shop\ShopAcceptMessage;
use addon\wechat\model\Message as WechatMessage;
use app\model\member\Member as MemberModel;
use addon\weapp\model\Message as WeappMessage;
use app\model\system\Pay;
use app\model\system\PayTransfer;
use think\facade\Cache;
use think\facade\Db;

/**
 * 分销商提现
 */
class FenxiaoWithdraw extends BaseModel
{
    //提现类型
    public $withdraw_type = [
        'balance' => '余额',
        'weixin' => '微信',
        'alipay' => '支付宝',
        'bank' => '银行卡',
    ];

    const STATUS_WAIT_AUDIT = 1;//待审核
    const STATUS_WAIT_TRANSFER = 2;//待转账
    const STATUS_SUCCESS = 3;//转账成功
    const STATUS_IN_PROCESS = 4;//转账中
    const STATUS_REFUSE = -1;//审核拒绝
    const STATUS_FAIL = -2;//转账失败

    public $status = array (
        self::STATUS_WAIT_AUDIT => '待审核',
        self::STATUS_WAIT_TRANSFER => '待转账',
        self::STATUS_SUCCESS => '已转账',
        self::STATUS_IN_PROCESS => '转账中',
        self::STATUS_REFUSE => '已拒绝',
        self::STATUS_FAIL => '转账失败',
    );

    public function getTransferType($site_id)
    {
        $pay_model = new Pay();
        $transfer_type_list = $pay_model->getTransferType($site_id);
        $transfer_type_list[ 'balance' ] = '余额';
        return $transfer_type_list;
    }

    /**
     * 分销商申请提现
     * @param $data
     * @return array
     */
    public function addFenxiaoWithdraw($data)
    {
        //获取分销商信息
        $fenxiao_model = new Fenxiao();
        $fenxiao_info = $fenxiao_model->getFenxiaoInfo([ [ 'member_id', '=', $data[ 'member_id' ] ] ], 'fenxiao_id,fenxiao_name,account');
        if (empty($fenxiao_info[ 'data' ])) {
            return $this->error('该分销商不存在');
        }

        if ($fenxiao_info[ 'data' ][ 'account' ] < $data[ 'money' ]) {
            return $this->error('', '提现金额大于可提现金额');
        }
        //获取提现配置信息
        $config_model = new Config();
        $config_info = $config_model->getFenxiaoWithdrawConfig($data[ 'site_id' ])[ 'data' ][ 'value' ];
        if ($config_info[ 'withdraw' ] > $data[ 'money' ]) {
            return $this->error('', '提现金额小于最低提现金额');
        }
        if ($data[ 'money' ] >= $config_info[ 'min_no_fee' ] && $data[ 'money' ] <= $config_info[ 'max_no_fee' ]) {
            $data[ 'withdraw_rate' ] = 0;
            $data[ 'withdraw_rate_money' ] = 0;
            $data[ 'real_money' ] = $data[ 'money' ];
        } else {
            $data[ 'withdraw_rate' ] = $config_info[ 'withdraw_rate' ];
            if ($config_info[ 'withdraw_rate' ] == 0) {
                $data[ 'withdraw_rate' ] = 0;
                $data[ 'withdraw_rate_money' ] = 0;
                $data[ 'real_money' ] = $data[ 'money' ];
            } else {
                $data[ 'withdraw_rate' ] = $config_info[ 'withdraw_rate' ];
                $data[ 'withdraw_rate_money' ] = round($data[ 'money' ] * $config_info[ 'withdraw_rate' ] / 100, 2);
                $data[ 'real_money' ] = $data[ 'money' ] - $data[ 'withdraw_rate_money' ];
            }
        }

        $data[ 'withdraw_no' ] = date('YmdHis') . rand(1000, 9999);
        $data[ 'create_time' ] = time();

        model('fenxiao_withdraw')->startTrans();
        try {

            $data[ 'fenxiao_id' ] = $fenxiao_info[ 'data' ][ 'fenxiao_id' ];
            $data[ 'fenxiao_name' ] = $fenxiao_info[ 'data' ][ 'fenxiao_name' ];

            $res = model('fenxiao_withdraw')->add($data);

            //修改分销商提现中金额
            model('fenxiao')->setInc([ [ 'member_id', '=', $data[ 'member_id' ] ] ], 'account_withdraw_apply', $data[ 'money' ]);
            //修改分销商可提现金额
            model('fenxiao')->setDec([ [ 'member_id', '=', $data[ 'member_id' ] ] ], 'account', $data[ 'money' ]);

            //判断是否需要审核
            if ($config_info[ 'withdraw_status' ] == 2) {//不需要
                $result = $this->withdrawPass($res, $data[ 'site_id' ]);
                if ($result[ 'code' ] < 0) {
                    model('fenxiao_withdraw')->rollback();
                    return $result;
                }
            }

            model('fenxiao_withdraw')->commit();

            //申请提现发送消息
            $data[ 'keywords' ] = 'FENXIAO_WITHDRAWAL_APPLY';
            $message_model = new Message();
            $message_model->sendMessage($data);

            return $this->success($res);
        } catch (\Exception $e) {
            model('fenxiao_withdraw')->rollback();
            return $this->error('', $e->getMessage());
        }

    }

    /**
     * 提现审核通过
     * @param $ids
     * @param $site_id
     * @return array
     */
    public function withdrawPass($ids, $site_id)
    {
        model('fenxiao_withdraw')->startTrans();
        try {

            $withdraw_list = $this->getFenxiaoWithdrawList([ [ 'id', 'in', $ids ] ], '*');
            foreach ($withdraw_list[ 'data' ] as $k => $v) {
                if ($v[ 'status' ] == self::STATUS_WAIT_AUDIT) {
                    $this->agree([ 'id' => $v[ 'id' ], 'site_id' => $site_id ]);
                }
            }
            model('fenxiao_withdraw')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('fenxiao_withdraw')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 获取提现详情
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getFenxiaoWithdrawInfo($condition = [], $field = '*')
    {
        $res = model('fenxiao_withdraw')->getInfo($condition, $field);
        return $this->success($res);
    }

    /**
     * 提现详情
     * @param $params
     * @return array
     */
    public function getFenxiaoWithdrawDetail($params)
    {
        $id = $params[ 'id' ];
        $site_id = $params[ 'site_id' ] ?? 0;
        $member_id = $params[ 'member_id' ] ?? 0;
        $condition = array (
            [ 'id', '=', $id ]
        );
        if ($site_id > 0) {
            $condition[] = [ 'site_id', '=', $site_id ];
        }
        if ($member_id > 0) {
            $condition[] = [ 'member_id', '=', $member_id ];
        }
        $info = model('fenxiao_withdraw')->getInfo($condition, '*');
        if (!empty($info)) {
            $info = $this->tran($info);
        }
        return $this->success($info);
    }

    public function tran($data)
    {
        $status = $data[ 'status' ] ?? 0;
        if ($status != 0) {
            $data[ 'status_name' ] = $this->status[ $status ] ?? '';
        }
        return $data;
    }

    /**
     * 获取分销列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param null $limit
     * @return array
     */
    public function getFenxiaoWithdrawList($condition = [], $field = '*', $order = '', $limit = null)
    {
        $list = model('fenxiao_withdraw')->getList($condition, $field, $order, '', '', '', $limit);
        return $this->success($list);
    }

    /**
     * 获取分销提现分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @param string $alias
     * @param array $join
     * @return array
     */
    public function getFenxiaoWithdrawPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*', $alias = '', $join = [])
    {
        $list = model('fenxiao_withdraw')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        return $this->success($list);
    }

    /**
     * 分销佣金发放通知
     * @param $data
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function messageOrderCommissionGrant($data)
    {
        //发送短信
        $sms_model = new Sms();

        // 分销订单
        $fenxiao_order_model = new FenxiaoOrder();
        $fenxiao_order_info = $fenxiao_order_model->getFenxiaoOrderInfo([ [ 'fenxiao_order_id', '=', $data[ 'order_id' ] ] ])[ 'data' ];
        $commission = $fenxiao_order_info[ $data[ 'level' ] . '_commission' ];

        $fenxiao_id = $fenxiao_order_info[ $data[ 'level' ] . '_fenxiao_id' ];
        $fenxiao_model = new Fenxiao();
        $fenxiao_info = $fenxiao_model->getFenxiaoInfo([ [ 'fenxiao_id', '=', $fenxiao_id ] ], 'fenxiao_id, member_id')[ 'data' ];

        $member_model = new MemberModel();
        $member_info_result = $member_model->getMemberInfo([ [ 'member_id', '=', $fenxiao_info[ 'member_id' ] ?? 0 ] ]);
        $member_info = $member_info_result[ 'data' ];

        //绑定微信公众号才发送
        if (!empty($member_info) && !empty($member_info[ 'wx_openid' ])) {
            $wechat_model = new WechatMessage();
            $data[ 'openid' ] = $member_info[ 'wx_openid' ];
            $data[ 'template_data' ] = [
                'amount1' => $commission, // 提现金额
                'time3' => time_to_date($fenxiao_order_info[ 'create_time' ]), // 提现日期
            ];
            $data[ 'page' ] = '';
            $wechat_model->sendMessage($data);
        }

        //发送订阅消息
        if (!empty($member_info) && !empty($member_info[ 'weapp_openid' ])) {
            $weapp_model = new WeappMessage();
            $data[ 'openid' ] = $member_info[ 'weapp_openid' ];
            $data[ 'template_data' ] = [
                'amount1' => [
                    'value' => $fenxiao_order_info[ 'real_goods_money' ]
                ],
                'amount2' => [
                    'value' => $commission
                ],
                'thing3' => [
                    'value' => $fenxiao_order_info[ 'sku_name' ]
                ],
                'time4' => [
                    'value' => time_to_date($fenxiao_order_info[ 'create_time' ]),
                ],
            ];
            $data[ 'page' ] = '';
            $weapp_model->sendMessage($data);
        }

        $buyer_member = $member_model->getMemberInfo([ [ 'member_id', '=', $fenxiao_order_info[ 'member_id' ] ?? 0 ] ]);
        $buyer_member_info = $buyer_member[ 'data' ];

        $var_parse = [
            'sitename' => replaceSpecialChar($data[ 'site_info' ][ 'site_name' ]),
            'level' => $data[ 'level' ],
            'username' => empty(replaceSpecialChar($buyer_member_info[ 'nickname' ])) ? $buyer_member_info[ 'mobile' ] : replaceSpecialChar($buyer_member_info[ 'nickname' ]),
        ];
        $data[ 'sms_account' ] = $member_info[ 'mobile' ];
        $data[ 'var_parse' ] = $var_parse;
        $sms_model->sendMessage($data);
    }

    /**
     * 分销提现成功通知
     * @param $data
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function messageFenxiaoWithdrawalSuccess($data)
    {
        //发送短信
        $sms_model = new Sms();

        $var_parse = array (
            'username' => $data[ 'fenxiao_name' ],//会员名
            'money' => $data[ 'money' ]
        );

        $data[ 'sms_account' ] = $data[ 'mobile' ];//手机号
        $data[ 'var_parse' ] = $var_parse;
        $sms_model->sendMessage($data);

        $member_model = new MemberModel();
        $member_info_result = $member_model->getMemberInfo([ [ 'member_id', '=', $data[ 'member_id' ] ] ]);
        $member_info = $member_info_result[ 'data' ];

        //绑定微信公众号才发送
        if (!empty($member_info) && !empty($member_info[ 'wx_openid' ])) {
            $wechat_model = new WechatMessage();
            $data[ 'openid' ] = $member_info[ 'wx_openid' ];
            $data[ 'template_data' ] = [
                'amount1' => $data[ 'money' ], // 提现金额
                'time3' => time_to_date($data[ 'payment_time' ]), // 提现日期
            ];
            $data[ 'page' ] = '';
            $wechat_model->sendMessage($data);
        }

        //发送订阅消息
        if (!empty($member_info) && !empty($member_info[ 'weapp_openid' ])) {
            $weapp_model = new WeappMessage();
            $data[ 'openid' ] = $member_info[ 'weapp_openid' ];
            $data[ 'template_data' ] = [
                'amount1' => [
                    'value' => $data[ 'money' ]
                ],
                'time2' => [
                    'value' => time_to_date(time())
                ],
                'thing3' => [
                    'value' => '提现成功'
                ]
            ];
            $data[ 'page' ] = '';
            $weapp_model->sendMessage($data);
        }

    }

    /**
     * 分销提现失败通知
     * @param $data
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function messageFenxiaoWithdrawalError($data)
    {
        //发送短信
        $sms_model = new Sms();

        $member_model = new MemberModel();
        $member_info_result = $member_model->getMemberInfo([ [ 'member_id', '=', $data[ 'member_id' ] ] ]);
        $member_info = $member_info_result[ 'data' ];

        $var_parse = array (
            'fenxiaoname' => str_replace(' ', '', $data[ 'fenxiao_name' ]),//会员名
            'money' => $data[ 'money' ]
        );

        $data[ 'sms_account' ] = $member_info[ 'mobile' ];//手机号
        $data[ 'var_parse' ] = $var_parse;
        $sms_model->sendMessage($data);

        // 【弃用，暂无模板信息，无法使用，等待后续微信支持后开发】绑定微信公众号才发送
//        if (!empty($member_info) && !empty($member_info[ 'wx_openid' ])) {
//            $wechat_model = new WechatMessage();
//            $data[ 'openid' ] = $member_info[ 'wx_openid' ];
//            $data[ 'template_data' ] = [
//                'keyword1' => time_to_date($data[ 'create_time' ]),
//                'keyword2' => '审核失败',
//                'keyword3' => '会员申请提现',
//                'keyword4' => $data[ 'money' ],
//            ];
//            $data[ 'page' ] = '';
//            $wechat_model->sendMessage($data);
//        }

        //发送订阅消息
        if (!empty($member_info) && !empty($member_info[ 'weapp_openid' ])) {
            $weapp_model = new WeappMessage();
            $data[ 'openid' ] = $member_info[ 'weapp_openid' ];
            $data[ 'template_data' ] = [
                'amount2' => [
                    'value' => $data[ 'money' ]
                ],
                'thing4' => [
                    'value' => '提现审核失败'
                ]
            ];
            $data[ 'page' ] = '';
            $weapp_model->sendMessage($data);
        }

    }

    /**
     * 分销申请提现通知，卖家通知
     * @param $data
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function messageFenxiaoWithdrawalApply($data)
    {
        //发送短信
        $sms_model = new Sms();

        $var_parse = array (
            'fenxiaoname' => replaceSpecialChar($data[ 'fenxiao_name' ]),//会员名
            'money' => $data[ 'money' ],//退款申请金额
        );
//        $site_id    = $data['site_id'];
//        $shop_info  = model('shop')->getInfo([['site_id', '=', $site_id]], 'mobile,email');
//        $message_data['sms_account'] = $shop_info['mobile'];//手机号
        $data[ 'var_parse' ] = $var_parse;

        $shop_accept_message_model = new ShopAcceptMessage();
        $result = $shop_accept_message_model->getShopAcceptMessageList();
        $list = $result[ 'data' ];
        if (!empty($list)) {
            foreach ($list as $v) {
                $message_data = $data;
                $message_data[ 'sms_account' ] = $v[ 'mobile' ];//手机号
                $sms_model->sendMessage($message_data);

                if ($v[ 'wx_openid' ] != '') {
                    $wechat_model = new WechatMessage();
                    $data[ 'openid' ] = $v[ 'wx_openid' ];
                    $data[ 'template_data' ] = [
                        'thing3' => replaceSpecialChar($data[ 'fenxiao_name' ]), // 客户名称
                        'amount6' => $data[ 'money' ], // 提现金额
                        'time8' => time_to_date($data[ 'create_time' ]) // 提现时间
                    ];
                    $data[ 'page' ] = '';
                    $wechat_model->sendMessage($data);
                }
            }
        }
    }

    /**
     * 获取提现数量
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getFenxiaoWithdrawCount($condition = [], $field = '*')
    {
        $res = model('fenxiao_withdraw')->getCount($condition, $field);
        return $this->success($res);
    }

    public function apply($data, $site_id = 0)
    {
        $config_model = new Config();
        $config = $config_model->getFenxiaoWithdrawConfig($site_id)[ 'data' ][ 'value' ] ?? [];

        $withdraw_no = $this->createWithdrawNo();
        $apply_money = round($data[ 'apply_money' ], 2);

        $withdraw_min_money = $config[ 'withdraw' ];
        $withdraw_max_money = $config[ 'max' ];
        if ($apply_money < $withdraw_min_money) return $this->error([], '申请提现金额不能小于最低提现额度' . $withdraw_min_money);
        if ($withdraw_max_money > 0 && $apply_money > $withdraw_max_money) return $this->error([], '申请提现金额不能大于最高提现额度' . $withdraw_max_money);

        $member_id = $data[ 'member_id' ];
        $member_model = new Member();
        $member_condition = array (
            [ 'member_id', '=', $member_id ]
        );
        $member_info = $member_model->getMemberInfo($member_condition, 'balance_money,headimg,wx_openid,username,mobile,weapp_openid,nickname')[ 'data' ] ?? [];
        if (empty($member_info))
            return $this->error([], 'MEMBER_NOT_EXIST');

        $fenxiao_model = new Fenxiao();
        $fenxiao_info = $fenxiao_model->getFenxiaoInfo($member_condition, 'fenxiao_id,fenxiao_name,account')[ 'data' ] ?? [];
        if (empty($fenxiao_info)) {
            return $this->error('该分销商不存在');
        }
        $fenxiao_account = $fenxiao_info[ 'account' ];//会员的分销佣金
        if ($fenxiao_account < $apply_money) {
            return $this->error('', '提现金额大于可提现金额');
        }

        $transfer_type = $data[ 'transfer_type' ];
        $transfer_type_list = $this->getTransferType($site_id);
        $transfer_type_name = $transfer_type_list[ $transfer_type ] ?? '';
        if (empty($transfer_type_name))
            return $this->error([], '不支持的提现方式');

        model('fenxiao_withdraw')->startTrans();
        try {
            $withdraw_rate = $config[ 'withdraw_rate' ];
            $bank_name = '';
            $account_number = '';
            $applet_type = 0;
            switch ( $transfer_type ) {
                case 'bank':
                    $bank_name = $data[ 'bank_name' ];
                    $account_number = $data[ 'account_number' ];
                    break;
                case 'alipay':
                    $bank_name = '';
                    $account_number = $data[ 'account_number' ];
                    break;
                case 'wechatpay':
                    $bank_name = '';
                    if (empty($member_info[ 'wx_openid' ]) && empty($member_info[ 'weapp_openid' ])) {
                        return $this->error('', '请绑定微信或更换提现账户');
                    }
                    if ($data['app_type'] != 'weapp') {
                        $account_number = $member_info[ 'wx_openid' ];
                        $applet_type = 0; // 公众号
                    } else {
                        $account_number = $member_info[ 'weapp_openid' ];
                        $applet_type = 1; // 小程序
                    }
                    break;

            }
            if ($transfer_type == 'balance') {
                $withdraw_rate = 0;
            }
            $service_money = round($apply_money * $withdraw_rate / 100, 2);//手续费
            $real_money = $apply_money - $service_money;
            $data = array (
                'site_id' => $site_id,
                'withdraw_no' => $withdraw_no,
                'member_id' => $member_id,
                'fenxiao_id' => $fenxiao_info[ 'fenxiao_id' ],
                'fenxiao_name' => $fenxiao_info[ 'fenxiao_name' ],
                'transfer_type' => $transfer_type,
                'transfer_name' => $transfer_type_name,
                'money' => $apply_money,
                'withdraw_rate_money' => $service_money,
                'withdraw_rate' => $withdraw_rate,
                'real_money' => $real_money,
                'create_time' => time(),
                'status' => self::STATUS_WAIT_AUDIT,
                'status_name' => $this->status[self::STATUS_WAIT_AUDIT],

                'member_headimg' => $member_info[ 'headimg' ],
                'realname' => $data[ 'realname' ],
                'bank_name' => $bank_name,
                'account_number' => $account_number,
                'mobile' => $data[ 'mobile' ],
                'applet_type' => $applet_type
            );

            $result = model('fenxiao_withdraw')->add($data);

            //添加转账记录
            $pay_transfer_model = new PayTransfer();
            $info = model('fenxiao_withdraw')->getInfo([['id', '=', $result]]);
            $pay_transfer_model->add([
                'real_name' => $info[ 'realname' ],
                'amount' => $info[ 'real_money' ],
                'desc' => '会员提现',
                'transfer_type' => $transfer_type,
                'account_number' => $info[ 'account_number' ],
                'site_id' => $info[ 'site_id' ],
                'is_weapp' => $info[ 'applet_type' ],
                'member_id' => $info[ 'member_id' ],
                'from_type' => 'fenxiao_withdraw',
                'relate_tag' => $info['id'],
            ]);

            //修改分销商提现中金额
            model('fenxiao')->setInc($member_condition, 'account_withdraw_apply', $apply_money);

            //修改分销商可提现金额
            model('fenxiao')->setDec($member_condition, 'account', $apply_money);

            //申请提现发送消息
            $data[ 'keywords' ] = 'FENXIAO_WITHDRAWAL_APPLY';
            $message_model = new Message();
            $message_model->sendMessage($data);

            //判断是否需要审核
            if ($config[ 'withdraw_status' ] == 2) {//不需要
                $result = $this->agree([ 'id' => $result, 'site_id' => $site_id ]);
                if ($result[ 'code' ] < 0) {
                    model('fenxiao_withdraw')->rollback();
                    return $result;
                }
            }
            model('fenxiao_withdraw')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('fenxiao_withdraw')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 提现流水号
     */
    private function createWithdrawNo()
    {
        $cache = Cache::get('member_withdraw_no' . time());
        if (empty($cache)) {
            Cache::set('niutk' . time(), 1000);
            $cache = Cache::get('member_withdraw_no' . time());
        } else {
            $cache = $cache + 1;
            Cache::set('member_withdraw_no' . time(), $cache);
        }
        $no = date('Ymdhis', time()) . rand(1000, 9999) . $cache;
        return $no;
    }

    public function agree($params)
    {
        $id = $params[ 'id' ];
        $site_id = $params[ 'site_id' ];
        if (empty($site_id)) {
            return $this->error(-1, '参数错误');
        }
        $condition = array (
            [ 'id', '=', $id ],
            [ 'site_id', '=', $site_id ],
        );
        $info = model('fenxiao_withdraw')->getInfo($condition);

        if (empty($info))
            return $this->error();

        $config_model = new Config();
        $config = $config_model->getFenxiaoWithdrawConfig($site_id)[ 'data' ][ 'value' ] ?? [];

        model('fenxiao_withdraw')->startTrans();
        try {
            $data = array (
                'status' => self::STATUS_WAIT_TRANSFER,
                'status_name' => $this->status[self::STATUS_WAIT_TRANSFER],//已审核待转账
                'audit_time' => time(),
            );
            $result = model('fenxiao_withdraw')->update($data, $condition);
            //是否启用自动转账(必须是微信或支付宝)
            if ($config[ 'is_auto_transfer' ] == 1) {
                $this->transfer([ 'id' => $id ]);
            }
            model('fenxiao_withdraw')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('fenxiao_withdraw')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 转账
     * @param $params
     * @return array|mixed|void
     */
    public function transfer($params)
    {
        $id = $params[ 'id' ];
        $site_id = $params[ 'site_id' ] ?? 0;
        $condition = array (
            [ 'id', '=', $id ],
        );
        if ($site_id > 0) {
            $condition[] = [ 'site_id', '=', $site_id ];
        }
        $info = model('fenxiao_withdraw')->getInfo($condition);
        if (empty($info))
            return $this->error();
        $site_id = $info[ 'site_id' ];
        $transfer_type = $info[ 'transfer_type' ];
        $member_id = $info[ 'member_id' ];
        $real_money = $info[ 'real_money' ];
        if ($transfer_type == 'balance') {
            //添加会员账户流水
            $member_account = new MemberAccount();
            $member_result = $member_account->addMemberAccount($site_id, $member_id, 'balance_money', $real_money, 'fenxiao', '佣金提现', '分销佣金提现');
            if ($member_result[ 'code' ] < 0) {
                return $member_result;
            }
            return $this->transferFinish(['id' => $id, 'site_id' => $site_id]);
        } else {
            if (!in_array($transfer_type, [ 'wechatpay', 'alipay' ]))
                return $this->error('', '当前提现方式不支持在线转账');

            $pay_transfer_model = new PayTransfer();
            $res = $pay_transfer_model->transfer('fenxiao_withdraw', $info['id']);
            return $res;
        }
    }

    /**
     * 转账结果通知
     * @param $param
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function transferNotify($param)
    {
        $id = $param['relate_tag'];
        $site_id = $param['site_id'];
        $withdraw_info = model('fenxiao_withdraw')->getInfo([
            ['id', '=', $id],
            ['site_id', '=', $site_id],
        ]);
        if(empty($withdraw_info)){
            return $this->error(null, '提现信息有误');
        }

        //成功的处理
        switch($param['status']){
            case PayTransfer::STATUS_IN_PROCESS:
                model('fenxiao_withdraw')->update([
                    'status' => self::STATUS_IN_PROCESS,
                    'status_name' => $this->status[self::STATUS_IN_PROCESS],
                    'modify_time' => time(),
                ], [['id', '=', $withdraw_info['id']]]);
                return $this->success();
                break;
            case PayTransfer::STATUS_SUCCESS:
                //添加账户流水
                $account_model = new FenxiaoAccount();
                $account_model->addAccountLog($withdraw_info['fenxiao_id'], $withdraw_info[ 'fenxiao_name' ], 'withdraw', '-' . $withdraw_info['money'], $withdraw_info['id']);

                return $this->transferFinish([ 'id' => $withdraw_info['id'], 'site_id' => $withdraw_info[ 'site_id' ] ]);
                break;
            case PayTransfer::STATUS_FAIL:
                $resp_data = json_decode($param['resp_data'], true);
                $fail_reason = $resp_data['fail_reason'] ?? '';
                model('fenxiao_withdraw')->update([
                    'status' => self::STATUS_FAIL,
                    'status_name' => $this->status[self::STATUS_FAIL],
                    'fail_reason' => $fail_reason,
                    'modify_time' => time(),
                ], [['id', '=', $withdraw_info['id']]]);

                //账户金额回退
                $member_condition = [['fenxiao_id', '=', $withdraw_info['fenxiao_id']]];
                $apply_money = $withdraw_info['money'];
                model('fenxiao')->setDec($member_condition, 'account_withdraw_apply', $apply_money);
                model('fenxiao')->setInc($member_condition, 'account', $apply_money);

                return $this->success();
                break;
            default:
                return $this->error(null, '转账结果状态有误');
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
            [ 'status', 'in', [self::STATUS_WAIT_TRANSFER, self::STATUS_IN_PROCESS] ]
        ];
        $info = model('fenxiao_withdraw')->getInfo($condition);
        if (empty($info)) return $this->error(null, '提现信息有误');

        $fenxiao_id = $info[ 'fenxiao_id' ];
        $money = $info[ 'money' ];
        $payment_time = time();
        model('fenxiao_withdraw')->startTrans();
        try {
            $data = [
                'status' => self::STATUS_SUCCESS,
                'status_name' => $this->status[self::STATUS_SUCCESS],
                'payment_time' => $payment_time,
                'document' => $param[ 'certificate' ] ?? '',
                'transfer_remark' => $param[ 'certificate_remark' ] ?? ''
            ];
            model('fenxiao_withdraw')->update($data, $condition);

            $fenxiao_condition = array (
                [ 'fenxiao_id', '=', $fenxiao_id ]
            );
            //修改分销商提现中金额
            model('fenxiao')->setDec($fenxiao_condition, 'account_withdraw_apply', $money);
            //修改分销商已提现金额
            model('fenxiao')->setInc($fenxiao_condition, 'account_withdraw', $money);

            model('fenxiao_withdraw')->commit();

            $message_model = new Message();
            $info[ 'keywords' ] = 'FENXIAO_WITHDRAWAL_SUCCESS';
            $message_model->sendMessage($info);
            return $this->success();
        } catch (\Exception $e) {
            model('fenxiao_withdraw')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 拒绝提现申请
     * @param $params
     * @return array
     */
    public function refuse($params)
    {
        $id = $params[ 'id' ];
        $site_id = $params[ 'site_id' ];
        $condition = array (
            [ 'id', '=', $id ],
            [ 'site_id', '=', $site_id ]
        );
        $info = model('fenxiao_withdraw')->getInfo($condition, '*');
        if (empty($info)) return $this->error(null, '提现信息有误');
        if(!in_array($info['status'], [self::STATUS_WAIT_AUDIT, self::STATUS_WAIT_TRANSFER])){
            return $this->error(null, '提现状态有误');
        }

        model('fenxiao_withdraw')->startTrans();
        try {
            $money = $info[ 'money' ];
            $fenxiao_id = $info[ 'fenxiao_id' ];
            $data = [
                'status' => self::STATUS_REFUSE,
                'status_name' => $this->status[self::STATUS_REFUSE],
                'refuse_reason' => $params['refuse_reason'],
                'audit_time' => time(),
            ];
            model('fenxiao_withdraw')->update($data, $condition);
            $fenxiao_condition = array (
                [ 'fenxiao_id', '=', $fenxiao_id ]
            );
            //修改分销商提现中金额
            model('fenxiao')->setDec($fenxiao_condition, 'account_withdraw_apply', $money);

            //修改分销商可提现金额
            model('fenxiao')->setInc($fenxiao_condition, 'account', $money);

            //提现失败发送消息
            $message_model = new Message();
            $info[ 'keywords' ] = 'FENXIAO_WITHDRAWAL_ERROR';
            $message_model->sendMessage($info);
            model('fenxiao_withdraw')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('fenxiao_withdraw')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    public function exportFenxiaoWithdraw($condition, $order, $site_id)
    {
        try {
            $file_name = date('Y年m月d日-分销提现', time()) . '.csv';
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
            $heade = [ '分销商', '提现方式', '申请提现金额', '提现手续费', '实际转账金额', '提现状态', '申请时间', '收款账号', '真实姓名', '手机号', '银行名称', '银行账号' ];
            //将数据编码转换成GBK格式
            mb_convert_variables('GBK', 'UTF-8', $heade);
            //将数据格式化为CSV格式并写入到output流中
            fputcsv($fp, $heade);
            $transfer_type_list = $this->getTransferType($site_id);
            $status_name = [ 1 => '待审核', 2 => '待转账', 3 => '已转账', -1 => '已拒绝', -2 => '转账失败'];
            //写入第一行表头
            Db::name('fenxiao_withdraw')->where($condition)->order($order)->chunk(500, function($item_list) use ($fp, $transfer_type_list, $status_name) {
                //写入导出信息
                foreach ($item_list as $k => $item_v) {
                    $temp_data = [
                        $item_v[ 'fenxiao_name' ] . "\t",
                        $transfer_type_list[ $item_v[ 'transfer_type' ] ] . "\t",
                        (float) $item_v[ 'money' ] . "\t",
                        (float) $item_v[ 'withdraw_rate_money' ] . "\t",
                        (float) $item_v[ 'real_money' ] . "\t",
                        $status_name[ $item_v[ 'status' ] ] . "\t",
                        time_to_date($item_v[ 'create_time' ]) . "\t",
                        $item_v[ 'account_number' ] . "\t",
                        $item_v[ 'realname' ] . "\t",
                        $item_v[ 'mobile' ] . "\t",
                        $item_v[ 'bank_name' ] . "\t",
                        $item_v[ 'transfer_account_no' ] . "\t",
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


    /**
     * 转账检测
     * @param $id
     */
    public function transferCheck($id)
    {
        $info_result = $this->getFenxiaoWithdrawInfo([ [ "id", "=", $id ] ], "withdraw_no,account_number,realname,money,memo,transfer_type,status");
        if (empty($info_result["data"]))
            return $this->error(null, '提现信息缺失');

        $info = $info_result["data"];
        if(!in_array($info["transfer_type"], ["wechatpay","alipay"]))
            return $this->error('', "当前提现方式不支持在线转账");
        if($info['status'] != self::STATUS_WAIT_TRANSFER){
            return $this->error('', "当前提现单非待转账状态");
        }
        return $this->success();
    }


}
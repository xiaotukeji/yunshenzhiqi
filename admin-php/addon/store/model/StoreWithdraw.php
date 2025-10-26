<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\store\model;

use app\model\BaseModel;
use app\model\member\MemberAccount;
use app\model\member\Withdraw as MemberWithdraw;
use app\model\store\Store;
use app\model\system\Pay;
use app\model\system\PayTransfer;
use think\facade\Cache;

class StoreWithdraw extends BaseModel
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

    public $settlement_type = array (
        'apply' => '申请结算',
        'day' => '每日结算',
        'week' => '每周结算',
        'month' => '每月结算',
    );

    /**
     * 门店账户提现
     * @param $params
     */
    public function apply($params)
    {
        $money = $params[ 'money' ] ?? 0;
        $site_id = $params[ 'site_id' ];
        $store_id = $params[ 'store_id' ];
        $settlement_type = $params[ 'settlement_type' ] ?? 'apply';

        $store_config_model = new Config();
        $config = $store_config_model->getStoreWithdrawConfig($site_id)[ 'data' ][ 'value' ] ?? [];
        $is_audit = $config[ 'is_audit' ];
        $is_settlement = $config[ 'is_settlement' ];
        if ($is_settlement == 0)
            return $this->error([], '门店结算未开启');
        $withdraw_least = $config[ 'withdraw_least' ];

        $store_model = new Store();
        $store_condition = array (
            [ 'site_id', '=', $site_id ],
            [ 'store_id', '=', $store_id ]
        );
        $store_info = $store_model->getStoreInfoByAccount($store_condition)[ 'data' ] ?? [];
        if (empty($store_info))
            return $this->error([], '找不到可结算的门店');

        if ($settlement_type == 'apply') {
            if ($money < $withdraw_least) {
                return $this->error([], '门店最低结算金额为' . $withdraw_least . '元');
            }
        }
        if ($money > $store_info[ 'account' ]) {
            return $this->error([], '申请结算金额不能大于门店最大可结算金额');
        }
        $bank_type = $store_info[ 'bank_type' ];
        if ($bank_type == 0)
            return $this->error([], '当前门店未配置结算账户,无法结算');

        $transfer_type = array ( 3 => 'bank', 2 => 'alipay', 1 => 'wechatpay' )[ $store_info[ 'bank_type' ] ];//转账方式
        $transfer_type_list = $this->getTransferType($site_id);
        $transfer_type_name = $transfer_type_list[ $transfer_type ] ?? '';
        switch ( $transfer_type ) {
            case 'bank':
                $bank_name = $store_info[ 'bank_type_name' ];
                $realname = $store_info[ 'bank_user_name' ];//户头
                $account_number = $store_info[ 'bank_type_account' ];
                break;
            case 'alipay':
                $realname = $store_info[ 'bank_user_name' ];
                $account_number = $store_info[ 'bank_type_account' ];
                break;
            case 'wechatpay':
                $realname = $store_info[ 'bank_user_name' ];
                $account_number = $store_info[ 'bank_type_account' ];
                break;
        }
        $data = array (
            'site_id' => $site_id,
            'withdraw_no' => $this->createWithdrawNo(),
            'store_name' => $store_info[ 'store_name' ],
            'store_id' => $store_id,
            'transfer_type' => $transfer_type,
            'transfer_type_name' => $transfer_type_name,
            'money' => $money,
            'apply_time' => time(),
            'status' => 0,
            'status_name' => $this->status[ 0 ],
            'realname' => $realname ?? '',
            'bank_name' => $bank_name ?? '',
            'account_number' => $account_number ?? '',
            'settlement_type' => $settlement_type,
            'settlement_type_name' => $this->settlement_type[ $settlement_type ]
        );

        model('store_withdraw')->startTrans();
        try {
            $withdraw_id = model('store_withdraw')->add($data);

            //添加转账记录
            $pay_transfer_model = new PayTransfer();
            $info = model('store_withdraw')->getInfo([['withdraw_id', '=', $withdraw_id]]);
            $pay_transfer_model->add([
                "out_trade_no" => $info[ "withdraw_no" ],
                "real_name" => $info[ "realname" ],
                "amount" => $info[ "money" ],
                "desc" => "门店申请提现",
                "transfer_type" => $info[ "transfer_type" ],
                "account_number" => $info[ "account_number" ],
                "site_id" => $info[ "site_id" ],
                "is_weapp" => 0,
                "member_id" => $info[ 'store_id' ],
                'from_type' => 'store_withdraw',
                "relate_tag" => $info['withdraw_id'],
            ]);

            //如果是申请提现，也同时生成一个结算单
            if($settlement_type == 'apply'){
                $start_time = 0;
                $last_withdraw_info = model('store_withdraw')->getFirstData([['store_id', '=', $store_id]], '*', 'apply_time desc');
                if(!empty($last_withdraw_info)) $start_time = $last_withdraw_info['apply_time'];
                $settlement_model = new Settlement();
                $settlement_model->addSettlement([
                    'site_id' => $site_id,
                    'store_id' => $store_id,
                    'start_time' => $start_time,
                    'end_time' => $data['apply_time'],
                    'store_commission' => $money,
                    'withdraw_id' => $withdraw_id,
                ]);
            }

            $store_account_model = new StoreAccount();
            $store_account_data = array (
                'account_data' => -$money,
                'site_id' => $site_id,
                'store_id' => $store_id,
                'from_type' => 'withdraw',
                'remark' => '门店申请结算，结算金额' . $money,
                'related_id' => $withdraw_id
            );
            $result = $store_account_model->addStoreAccount($store_account_data);
            if ($result[ 'code' ] < 0) {
                model('store_withdraw')->rollback();
                return $result;
            }
            //增加结算中余额
            model('store')->setInc([ [ 'store_id', '=', $store_id ] ], 'account_apply', $money);

            //结算无需审核的话,就直接结算
            if ($is_audit == 0) {
                $result = $this->agree([ 'withdraw_id' => $withdraw_id, 'site_id' => $site_id, 'store_id' => $store_id ]);
                if ($result[ 'code' ] < 0) {
                    model('store_withdraw')->rollback();
                    return $result;
                }
            }
            model('store_withdraw')->commit();
            return $this->success($withdraw_id);
        } catch (\Exception $e) {
            model('store_withdraw')->rollback();
            return $this->error('', $e->getMessage());
        }

    }

    /**
     * 同意结算申请
     * @param $condition
     */
    public function agree($params)
    {
        $site_id = $params[ 'site_id' ];
        $store_id = $params[ 'store_id' ] ?? 0;
        $withdraw_id = $params[ 'withdraw_id' ];
        $condition = array (
            [ 'site_id', '=', $site_id ],
            [ 'withdraw_id', '=', $withdraw_id ],
            [ 'status', '=', 0 ]
        );
        if ($store_id > 0) {
            $condition[] = [ 'store_id', '=', $store_id ];
        }
        $info = model('store_withdraw')->getInfo($condition);
        if (empty($info))
            return $this->error(null, '未获取到提现信息');

        $data = array (
            'status' => 1,
            'status_name' => $this->status[ 1 ],
            'audit_time' => time(),
        );
        model('store_withdraw')->update($data, $condition);

        $store_config_model = new Config();
        $config = $store_config_model->getStoreWithdrawConfig($site_id)[ 'data' ][ 'value' ];
        if($config['is_auto_transfer'] == 1){
            $this->transfer($info['withdraw_id']);
        }

        return $this->success();
    }

    /**
     * 拒绝结算申请
     * @param $condition
     */
    public function refuse($params)
    {
        $site_id = $params[ 'site_id' ];
        $store_id = $params[ 'store_id' ] ?? 0;
        $withdraw_id = $params[ 'withdraw_id' ];
        $condition = array (
            [ 'site_id', '=', $site_id ],
            [ 'withdraw_id', '=', $withdraw_id ],
        );
        if ($store_id > 0) {
            $condition[] = [ 'store_id', '=', $store_id ];
        }
        $info = model('store_withdraw')->getInfo($condition);
        if (empty($info))
            return $this->error(null, '提现信息不存在');
        if(!in_array($info['status'], [self::STATUS_WAIT_AUDIT, self::STATUS_WAIT_TRANSFER])){
            return $this->error(null, '提现状态有误');
        }

        model('store_withdraw')->startTrans();
        try {
            $data = array (
                'status' => -1,
                'status_name' => $this->status[ -1 ],
                'refuse_reason' => $params[ 'refuse_reason' ],
                'audit_time' => time(),
            );
            model('store_withdraw')->update($data, $condition);
            $money = $info[ 'money' ];
            //增加现金余额
            $store_id = $info[ 'store_id' ];
            $store_account_model = new StoreAccount();
            $store_account_data = array (
                'account_data' => $money,
                'site_id' => $site_id,
                'store_id' => $store_id,
                'from_type' => 'withdraw',
                'remark' => '门店结算拒绝，返还金额' . $money.'，拒绝原因：'.$params[ 'refuse_reason' ],
                'related_id' => $withdraw_id
            );
            $result = $store_account_model->addStoreAccount($store_account_data);
            if ($result[ 'code' ] < 0) {
                model('store_withdraw')->rollback();
                return $result;
            }
            //减少结算中余额
            model('store')->setDec([ [ 'store_id', '=', $store_id ] ], 'account_apply', $money);

            //如果关联结算，要把订单的结算状态和结算id清除
            $settlement_model = new Settlement();
            $settlement_model->refuseWithdraw($withdraw_id);

            model('store_withdraw')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('store_withdraw')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 结算转账完成
     * @param $id
     */
    public function transferFinish($params)
    {
        $site_id = $params[ 'site_id' ];
        $store_id = $params[ 'store_id' ] ?? 0;
        $withdraw_id = $params[ 'withdraw_id' ];
        $condition = array (
            [ 'site_id', '=', $site_id ],
            [ 'withdraw_id', '=', $withdraw_id ]
        );
        if ($store_id > 0) {
            $condition[] = [ 'store_id', '=', $store_id ];
        }
        $info = model('store_withdraw')->getInfo($condition);
        if (empty($info))
            return $this->error();

        $transfer_time = time();
        model('store_withdraw')->startTrans();
        try {
            $store_id = $info[ 'store_id' ];
            $data = [
                'status' => 2,
                'status_name' => $this->status[ 2 ],
                'transfer_time' => $transfer_time,
                'voucher_img' => $params[ 'voucher_img' ] ?? '',
                'voucher_desc' => $params[ 'voucher_desc' ] ?? ''
            ];
            model('store_withdraw')->update($data, $condition);
            $store_condition = array (
                [ 'store_id', '=', $store_id ]
            );
            $money = $info[ 'money' ];
            //增加已结算余额
            model('store')->setInc($store_condition, 'account_withdraw', $money);
            //减少结算中余额
            model('store')->setDec($store_condition, 'account_apply', $money);
            model('store_withdraw')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('store_withdraw')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 转账
     * @param $condition
     */
    public function transfer($withdraw_id)
    {
        $info_result = $this->getStoreWithdrawInfo([ [ "withdraw_id", "=", $withdraw_id ] ]);
        if (empty($info_result[ "data" ]))
            return $this->error(null, '提现信息有误');

        $info = $info_result[ "data" ];
        if (!in_array($info[ "transfer_type" ], [ "wechatpay", "alipay" ]))
            return $this->error('', "当前提现方式不支持在线转账");

        $pay_transfer_model = new PayTransfer();
        $transfer_res = $pay_transfer_model->transfer('store_withdraw', $info['withdraw_id']);
        return $transfer_res;
    }

    /**
     * 转账结果通知
     * @param $param
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function transferNotify($param)
    {
        $withdraw_id = $param['relate_tag'];
        $site_id = $param['site_id'];
        $withdraw_info = $this->getStoreWithdrawInfo([
            ['withdraw_id', '=', $withdraw_id ],
            ['site_id', '=', $site_id],
        ], "*")['data'];
        if(empty($withdraw_info)){
            return $this->error(null, '提现信息有误');
        }

        //成功的处理
        switch($param['status']){
            case PayTransfer::STATUS_IN_PROCESS:
                return $this->transferInProcess([
                    'withdraw_id' => $withdraw_info['withdraw_id'],
                    'site_id' => $withdraw_info['site_id'],
                ]);
                break;
            case PayTransfer::STATUS_SUCCESS:
                return $this->transferFinish([
                    'withdraw_id' => $withdraw_info['withdraw_id'],
                    'site_id' => $withdraw_info['site_id'],
                ]);
                break;
            case PayTransfer::STATUS_FAIL:
                $resp_data = json_decode($param['resp_data'], true);
                $fail_reason = $resp_data['fail_reason'] ?? '';
                return $this->transferFail([
                    'withdraw_id' => $withdraw_info['withdraw_id'],
                    'site_id' => $withdraw_info['site_id'],
                    'fail_reason' => $fail_reason,
                ]);
                break;
            default:
                return $this->error(null, '转账结果状态有误');
        }
    }

    /**
     * 转账失败
     * @param $param
     * @return array
     */
    public function transferFail($param)
    {
        $withdraw_id = $param['withdraw_id'];
        $site_id = $param['site_id'];
        $fail_reason = $param['fail_reason'];

        $condition = [
            [ 'withdraw_id', '=', $withdraw_id ],
            [ 'site_id', '=', $site_id ],
            [ 'status', 'in', [self::STATUS_WAIT_TRANSFER, self::STATUS_IN_PROCESS] ],
        ];
        $info = $this->getStoreWithdrawInfo($condition)['data'];
        if (empty($info)) return $this->error(null, '提现信息有误');

        model('store_withdraw')->startTrans();
        try {
            $data = [
                'status' => self::STATUS_FAIL,
                'status_name' => $this->status[self::STATUS_FAIL],
                'refuse_reason' => $fail_reason,
            ];
            model("store_withdraw")->update($data, $condition);

            //增加现金余额
            $store_id = $info[ 'store_id' ];
            $store_account_model = new StoreAccount();
            $store_account_data = array (
                'account_data' => $info['money'],
                'site_id' => $site_id,
                'store_id' => $store_id,
                'from_type' => 'withdraw',
                'remark' => '提现失败返还，原因：'.$fail_reason,
                'related_id' => $info['withdraw_id']
            );
            $result = $store_account_model->addStoreAccount($store_account_data);
            if ($result[ 'code' ] < 0) {
                model('store_withdraw')->rollback();
                return $result;
            }

            //减少结算中余额
            model('store')->setDec([ [ 'store_id', '=', $store_id ] ], 'account_apply', $info['money']);

            model('store_withdraw')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('store_withdraw')->rollback();
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
        $withdraw_id = $param['withdraw_id'];
        $site_id = $param['site_id'];

        $condition = [
            [ 'withdraw_id', '=', $withdraw_id ],
            [ 'site_id', '=', $site_id ],
            [ 'status', '=', self::STATUS_WAIT_TRANSFER ]
        ];
        $info = $this->getStoreWithdrawInfo($condition)['data'];
        if (empty($info)) return $this->error(null, '提现信息有误');

        model("store_withdraw")->update([
            'status' => self::STATUS_IN_PROCESS,
            'status_name' => $this->status[self::STATUS_IN_PROCESS],
        ], [['withdraw_id', '=', $withdraw_id]]);

        return $this->success();
    }

    /**
     * 转账方式
     */
    public function getTransferType($site_id = 0)
    {
        $pay_model = new Pay();
        $transfer_type_list = $pay_model->getTransferType($site_id);
        $data = [];
        foreach ($transfer_type_list as $k => $v) {
            $data[ $k ] = $v;
        }
        return $data;
    }

    /**
     * 结算
     * @param $condition
     */
    public function getStoreWithdrawInfo($condition, $field = '*')
    {
        $info = model('store_withdraw')->getInfo($condition, $field);
        return $this->success($info);
    }

    /**
     * 结算记录
     * @param $condition
     * @param string $field
     */
    public function getStoreWithdrawList($condition = [], $field = '*', $order = '', $limit = null)
    {
        $list = model('store_withdraw')->getList($condition, $field, $order, '', '', '', $limit);
        return $this->success($list);
    }

    /**
     * 获取账户分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array|\multitype
     */
    public function getStoreWithdrawPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'apply_time desc', $field = '*', $alias = 'a', $join = [])
    {
        $list = model('store_withdraw')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        return $this->success($list);
    }

    /**
     * 获取结算单数量
     * @param $condition
     * @return array
     */
    public function getStoreWithdrawCount($condition)
    {
        $data = model('store_withdraw')->getCount($condition);
        return $this->success($data);
    }

    /**
     * 获取结算单字段总和
     * @param $condition
     * @return array
     */
    public function getStoreWithdrawSum($condition, $field)
    {
        $data = model('store_withdraw')->getSum($condition, $field);
        return $this->success($data);
    }

    /**
     * 结算详情
     * @param $params
     * @return array
     */
    public function detail($params)
    {
        $withdraw_id = $params[ 'withdraw_id' ];
        $site_id = $params[ 'site_id' ] ?? 0;
        $store_id = $params[ 'store_id' ] ?? 0;
        $condition = array (
            [ 'withdraw_id', '=', $withdraw_id ]
        );
        if ($site_id > 0) {
            $condition[] = [ 'site_id', '=', $site_id ];
        }
        if ($store_id > 0) {
            $condition[] = [ 'store_id', '=', $store_id ];
        }
        $info = model('store_withdraw')->getInfo($condition);
        if (empty($info))
            return $this->error();
        $settlement_type = $info[ 'settlement_type' ];
        //非主动申请的结算,会有周期性结算信息
        if ($settlement_type != 'apply') {
            $settlement_model = new Settlement();
            $settlement_condition = array (
                [ 'withdraw_id', '=', $withdraw_id ]
            );
            $settlement_info = $settlement_model->getSettlementInfo($settlement_condition)[ 'data' ] ?? [];
            if (!empty($settlement_info))
                $info[ 'settlement_info' ] = $settlement_info;
        }

        return $this->success($info);

    }

    /**
     * 翻译
     * @param $data
     */
//    public function translate($data){
//        $settlement_type = $data['settlement_type'] ?? '';
//        if(!empty($settlement_type)){
//            $data['settlement_type_name'] = $settlement_type;
//        }
//
//        return $data;
//    }
    /**
     * 结算流水号
     */
    private function createWithdrawNo()
    {
        $cache = Cache::get('store_withdraw_no' . time());
        if (empty($cache)) {
            Cache::set('niutk' . time(), 1000);
            $cache = Cache::get('store_withdraw_no' . time());
        } else {
            $cache = $cache + 1;
            Cache::set('store_withdraw_no' . time(), $cache);
        }
        $no = date('Ymdhis', time()) . rand(1000, 9999) . $cache;
        return $no;
    }

    /**
     * 转账检测
     * @param $id
     */
    public function transferCheck($id)
    {
        $info_result = $this->getStoreWithdrawInfo([ [ "withdraw_id", "=", $id ] ]);
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
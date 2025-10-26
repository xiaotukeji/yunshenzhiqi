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

use app\dict\order\OrderPayDict;
use app\model\BaseModel;
use app\model\order\OrderCommon;

/**
 * 门店订单与结算
 */
class Settlement extends BaseModel
{

    /**
     * 店铺门店结算
     * @param $site_id
     * @param $end_time
     * @return array
     */
    public function settlement($site_id, $end_time)
    {
        //获取最近的店铺结算时间
        $last_time = model('shop')->getInfo([ [ 'site_id', '=', $site_id ] ], 'store_settlement_time');
        $start_time = $last_time[ 'store_settlement_time' ];

        //查询门店结算配置
        $config_model = new Config();
        $config = $config_model->getStoreWithdrawConfig($site_id)[ 'data' ][ 'value' ] ?? [];
        if ($config[ 'is_settlement' ] != 1) {
            return $this->success();
        }
        $period_type = $config[ 'period_type' ];
        //结算周期为4的话是立即结算
        if ($period_type == 4)
            return $this->success();

        $period_type = array ( 1 => 'day', 2 => 'week', 3 => 'month' )[ $period_type ] ?? 'day';
        //只有开启周期性结算开关,才会进行

        if (( $end_time - $start_time ) < ( 3600 * 20 )) {
            return $this->success();
        }

        //店铺列表
        $store_list = model('store')->getList([ [ 'site_id', '=', $site_id ] ], 'site_name, store_id, store_name, account');
        model('store_settlement')->startTrans();
        try {
            $store_withdraw_model = new StoreWithdraw();
            //循环各个店铺数据
            foreach ($store_list as $store) {
                $store_id = $store[ 'store_id' ];
                //周期性结算会将所有抽成全部提现
                $store_commission = $store[ 'account' ];
                $withdraw_id = 0;
                if ($store_commission > 0) {
                    $apply_params = array (
                        'site_id' => $site_id,
                        'store_id' => $store_id,
                        'money' => $store_commission,
                        'settlement_type' => $period_type
                    );
                    $withdraw_result = $store_withdraw_model->apply($apply_params);
                    $withdraw_id = $withdraw_result[ 'data' ];
                }
                $this->addSettlement([
                    'site_id' => $site_id,
                    'store_id' => $store_id,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'store_commission' => $store_commission,
                    'withdraw_id' => $withdraw_id,
                ])['data'];
            }
            model('shop')->update([ 'store_settlement_time' => $end_time ], [ [ 'site_id', '=', $site_id ] ]);
            model('store_settlement')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('store_settlement')->rollback();
            return $this->error($e->getMessage());
        }
    }

    /**
     * 添加结算
     * @param $param
     * @return array
     */
    public function addSettlement($param)
    {
        $site_id = $param['site_id'];
        $store_id = $param['store_id'];
        $start_time = $param['start_time'];
        $end_time = $param['end_time'];
        $store_commission = $param['store_commission'];
        $withdraw_id = $param['withdraw_id'];

        $store_info = model('store')->getInfo([['store_id', '=', $store_id]], 'store_name,site_name');

        $online_settlement = model('order')->getInfo([
            [ 'order_status', '=', 10 ],
            [ 'is_settlement', '=', 0 ],
            [ 'store_id', '=', $store_id ],
            [ 'finish_time', '<=', $end_time ],
            [ 'pay_type', '<>', OrderPayDict::offline_pay ],//todo  支付方式应该都可以
        ], 'sum(pay_money) as order_money, sum(refund_money) as refund_money, sum(commission) as commission');

        $offline_settlement = model('order')->getInfo([
            [ 'order_status', '=', 10 ],
            [ 'is_settlement', '=', 0 ],
            [ 'store_id', '=', $store_id ],
            [ 'finish_time', '<=', $end_time ],
            [ 'pay_type', '=', 'offlinepay' ],
        ], 'sum(pay_money) as offline_order_money, sum(refund_money) as offline_refund_money');


        $settlement = [
            'settlement_no' => date('YmdHi') . $store_id . rand(1111, 9999),
            'site_id' => $site_id,
            'site_name' => $store_info[ 'site_name' ],
            'store_id' => $store_id,
            'store_name' => $store_info[ 'store_name' ],
            'order_money' => $online_settlement[ 'order_money' ] ?? 0,
            'refund_money' => $online_settlement[ 'refund_money' ] ?? 0,
            'commission' => $online_settlement[ 'commission' ] ?? 0,
            'offline_order_money' => $offline_settlement[ 'offline_order_money' ] ?? 0,
            'offline_refund_money' => $offline_settlement[ 'offline_refund_money' ] ?? 0,
            'create_time' => $end_time,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'store_commission' => $store_commission,
            'withdraw_id' => $withdraw_id,
        ];
        $store_settlement_id = model('store_settlement')->add($settlement);

        model('order')->update([ 'store_settlement_id' => $store_settlement_id, 'is_settlement' => 1 ], [
            [ 'order_status', '=', 10 ],
            [ 'is_settlement', '=', 0 ],
            [ 'store_id', '=', $store_id ],
            [ 'finish_time', '<=', $end_time ],
        ]);

        return $this->success($store_settlement_id);
    }


    /**
     * 获取详情
     * @param $condition
     * @param string $fields
     * @return array
     */
    public function getSettlementInfo($condition, $fields = '*')
    {
        $res = model('store_settlement')->getInfo($condition, $fields);
        return $this->success($res);
    }

    /**
     * 修改结算记录
     * @param $data
     * @param $condition
     * @return array
     */
    public function editSettlement($data, $condition)
    {
        $res = model('store_settlement')->update($data, $condition);
        return $this->success($res);
    }

    /**
     * 获取店铺待结算订单金额
     * @param array $condition
     * @return array|mixed
     */
    public function getStoreSettlementData($condition = [])
    {
        $money_info = model('order')->getInfo($condition, 'sum(order_money) as order_money, sum(refund_money) as refund_money, sum(shop_money) as shop_money, sum(platform_money) as platform_money, sum(refund_shop_money) as refund_shop_money, sum(refund_platform_money) as refund_platform_money, sum(commission) as commission');
        if (empty($money_info) || $money_info == null) {

            $money_info = [
                'order_money' => 0,
                'refund_money' => 0,
                'shop_money' => 0,
                'platform_money' => 0,
                'refund_shop_money' => 0,
                'refund_platform_money' => 0,
                'commission' => 0
            ];

        }

        return $money_info;
    }

    /**
     * 获取店铺结算周期结算分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getStoreSettlementPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*')
    {
        $list = model('store_settlement')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }

    /**
     * 拒绝提现后的处理
     * @param $withdraw_id
     * @return array
     */
    public function refuseWithdraw($withdraw_id)
    {
        $settlement_info = model('store_settlement')->getInfo([['withdraw_id', '=', $withdraw_id]]);
        if(!empty($settlement_info)){
            $store_settlement_id = $settlement_info['id'];
            model('order')->update(['is_settlement' => 0, 'store_settlement_id' => 0], [['store_settlement_id', '=', $store_settlement_id]]);
        }
        return $this->success();
    }
}
<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\cashier\event;

use app\model\order\Order as OrderModel;

/**
 * 资金收入统计
 */
class IncomeStatistics
{
    public function handle($param)
    {
        $money = ( new OrderModel() )->getOrderMoneySum([ [ 'site_id', '=', $param[ 'site_id' ] ], [ 'pay_time', 'between', [ $param[ 'start_time' ], $param[ 'end_time' ] ] ], [ 'order_scene', '=', 'cashier' ], [ 'cashier_order_type', '<>', 'recharge' ] ], 'pay_money')[ 'data' ];
        return [
            [
                'title' => '收银订单',
                'value' => $money,
                'desc' => '统计时间内，收银台所有开放售卡订单实付金额之和',
                'url' => 'cashier://shop/order/lists'
            ]
        ];
    }
}
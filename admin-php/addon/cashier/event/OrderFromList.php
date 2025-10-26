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

use addon\cashier\model\order\CashierOrder;

/**
 * 订单来源
 */
class OrderFromList
{
    /**
     * 订单来源
     */
    public function handle($params)
    {
        $order_scene = $params[ 'order_scene' ] ?? '';
        if (empty($order_scene) || $order_scene = 'cashier') {
            return ( new CashierOrder )->order_from_list;
        }

    }
}
<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\store\event;

use addon\store\model\StoreOrder;

/**
 * 添加下单用户为门店用户
 */
class OrderComplete
{
    public function handle($order)
    {
        $store_order = new StoreOrder();
        $res = $store_order->orderComplete($order[ 'order_id' ]);
        return $res;
    }
}
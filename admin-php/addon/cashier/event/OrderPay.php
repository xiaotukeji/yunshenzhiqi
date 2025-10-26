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

use addon\cashier\model\Push as PushModel;

/**
 * 订单类型
 */
class OrderPay
{
    /**
     * 订单类型
     */
    public function handle($order_info)
    {
        return (new PushModel())->orderPay($order_info);
    }
}
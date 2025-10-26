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
use app\dict\order\OrderDict;

/**
 * 订单项退款
 */
class OrderGoodsRefund
{
    /**
     * 支付方式及配置
     */
    public function handle($params)
    {
        $order_info = $params[ 'order_info' ];
        if ($order_info[ 'order_type' ] == OrderDict::cashier) {
            $order_model = new CashierOrder();
            $result = $order_model->refund($params);
            return $result;
        }
    }
}
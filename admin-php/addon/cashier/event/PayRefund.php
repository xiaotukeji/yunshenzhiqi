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
 * 原路退款
 */
class PayRefund
{
    /**
     * 关闭支付
     */
    public function handle($params)
    {
        $pay_type_array = array (
            'cash', 'own_wechatpay', 'own_alipay', 'own_pos'
        );
        if (in_array($params[ "pay_info" ][ "pay_type" ], $pay_type_array)) {
            $cashier_order_model = new CashierOrder();
            return $cashier_order_model->success();
        }
    }
}
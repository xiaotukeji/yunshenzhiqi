<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace addon\printer\event;

use addon\printer\model\PrinterOrder;

/**
 * 充值订单打印
 */
class MemberRechargeOrderPayPrinter
{
    public function handle($param)
    {
        $printer_order_model = new PrinterOrder();
        return $printer_order_model->printer([
            'order_id' => $param['relate_id'],
            'type' => 'recharge',
        ]);
    }
}
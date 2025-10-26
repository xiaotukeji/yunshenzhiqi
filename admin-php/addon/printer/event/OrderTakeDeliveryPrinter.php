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
use think\facade\Log;

/**
 * 订单收货打印
 */
class OrderTakeDeliveryPrinter
{

    public function handle($param)
    {
        Log::write('订单收货打印OrderTakeDeliveryPrinter' . json_encode($param));
        $printer_order_model = new PrinterOrder();
        return $printer_order_model->printer([
            'order_id' => $param[ 'relate_id' ],
            'type' => 'goodsorder',
            'printer_type' => 'take_delivery',
        ]);
    }
}
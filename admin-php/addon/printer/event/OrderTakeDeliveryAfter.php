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

use app\model\system\Cron;
use think\facade\Log;

/**
 * 订单收货
 */
class OrderTakeDeliveryAfter
{

    public function handle($param)
    {
        Log::write('订单收货小票打印_OrderTakeDelivery' . json_encode($param));
        $cron = new Cron();
        $cron->addCron(1, 0, "订单收货小票打印", "OrderTakeDeliveryPrinter", time(), $param[ 'order_id' ]);
    }
}
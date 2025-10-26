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
 * 充值完成
 */
class MemberRechargeOrderPay
{

    public function handle($param)
    {
        // 收银台主动调用小票打印接口了，执行回调不需要再打印了
        if (!empty($param[ 'order_from' ]) && $param[ 'order_from' ] == 'cashier') {
            return;
        }
        Log::write('会员充值完成，小票打印_MemberRechargeOrderPay' . json_encode($param));
        $cron = new Cron();
        $cron->addCron(1, 0, "充值小票打印", "MemberRechargeOrderPayPrinter", time(), $param[ 'order_id' ]);
    }
}
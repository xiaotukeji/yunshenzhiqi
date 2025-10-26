<?php

namespace addon\cashier\job\order;

use addon\cashier\model\order\CashierOrderCreate;
use addon\cashier\model\order\CashierOrderPay;
use app\model\order\OrderLog;
use Exception;
use think\facade\Log;
use think\queue\Job;

/**
 * 收银订单订单支付后操作
 */
class CashierOrderPayAfter
{
    public function fire(Job $job, $data)
    {
        $job->delete();
        try {
            (new CashierOrderPay())->cashierOrderPayAfter($data);
        } catch ( Exception $e) {
            Log::write('cashierOrderPayAfter_error_'.$e->getMessage());
        }
    }

}

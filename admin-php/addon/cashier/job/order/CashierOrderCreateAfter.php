<?php

namespace addon\cashier\job\order;

use addon\cashier\model\order\CashierOrderCreate;
use app\model\order\OrderLog;
use Exception;
use think\facade\Log;
use think\queue\Job;

/**
 * 收银订单订单创建后事件
 */
class CashierOrderCreateAfter
{
    public function fire(Job $job, $data)
    {
        $job->delete();
        try {
            $create_data = $data['create_data'];
            $order_object = (new CashierOrderCreate())->invokeClass($create_data);

            $log_data = array(
                'order_id' => $create_data['order_id'],
                'action' => 'create',
                'site_id' => $create_data['site_id'],
                'member_id' => $create_data['member_id']
            );
            (new OrderLog())->addLog($log_data);
            //执行自动关闭
            $order_object->addOrderCronClose(); //增加关闭订单自动事件
            //自动删除时间   订单不能删除
            // $order_object->addOrderCronDelete(); // 增加订单自动删除事件（5分钟内未支付）
        } catch ( Exception $e) {
            Log::write('CashierOrderCreateAfter_error_'.$e->getMessage());
            $job->delete();
        }
    }

}

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

use addon\cashier\model\order\CashierOrder as CashierOrderModel;
use app\model\order\OrderCommon;

/**
 * 订单自动删除事件（5分钟内未支付）
 */
class CronOrderDelete
{
    // 行为扩展的执行入口必须是run
    public function handle($data)
    {
        $order = new OrderCommon();
        $order_info = $order->getOrderInfo([ ['order_id', '=', $data['relate_id'] ] ], 'order_id,order_status,store_id,site_id')[ 'data' ];
        if (!empty($order_info) && $order_info['order_status'] == 0) {

            //订单关闭并删除
            $order_common_model = new OrderCommon();
            $close_result = $order_common_model->orderClose($order_info[ 'order_id' ]);
            if ($close_result[ 'code' ] < 0) {
                return $close_result;
            }
            $order_model = new CashierOrderModel();
            $condition = array (
                [ 'site_id', '=', $order_info[ 'site_id' ] ],
                [ 'store_id', '=', $order_info[ 'store_id' ] ],
                [ 'order_id', '=', $order_info[ 'order_id' ] ],
            );

            $res = $order_model->deleteOrder($condition);
            return $res;
        }


    }
}
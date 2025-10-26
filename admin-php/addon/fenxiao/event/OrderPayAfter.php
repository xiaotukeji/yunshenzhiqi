<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace addon\fenxiao\event;

use addon\fenxiao\model\FenxiaoOrder;
use addon\fenxiao\model\Fenxiao as FenxiaoModel;
use think\facade\Log;

/**
 * 活动展示
 */
class OrderPayAfter
{

    /**
     * 订单结算
     */
    public function handle($order)
    {
        //先检测是否需要绑定上下线
        $fenxiao_model = new FenxiaoModel();
        $fenxiao_model->bindRelation([
            'site_id' => $order['site_id'],
            'member_id' => $order['member_id'],
            'action' => 'order_pay',
        ]);
        // 自动成为分销商
        $fenxiao_model->autoBecomeFenxiao($order['member_id'], $order['site_id']);
        $fenxiao_order = new FenxiaoOrder();
        return $fenxiao_order->calculate($order);
    }
}
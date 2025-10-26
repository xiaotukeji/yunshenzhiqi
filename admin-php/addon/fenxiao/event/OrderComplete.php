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

use addon\fenxiao\model\Fenxiao as FenxiaoModel;
use app\model\order\OrderCommon;

/**
 * 订单完成事件
 */
class OrderComplete
{
    /**
     * 订单创建后绑定上下线关系
     * @param $param
     */
    public function handle($param)
    {
        $order_id = $param[ 'order_id' ];
        $order_model = new OrderCommon();
        $order_info = $order_model->getOrderInfo([ [ 'order_id', '=', $order_id ] ])[ 'data' ];
        if (!empty($order_info)) {
            $fenxiao_model = new FenxiaoModel();
            $fenxiao_model->autoBecomeFenxiao($order_info[ 'member_id' ], $order_info[ 'site_id' ]);
        }
    }
}
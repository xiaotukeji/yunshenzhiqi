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
use addon\presale\model\PresaleOrder;

/**
 * 预售订单创建
 */
class PresaleOrderCreate
{
    /**
     * 订单创建后绑定上下线关系
     * @param $param
     * @return array|void
     */
    public function handle($param)
    {
        $id = $param[ 'id' ];
        $order_model = new PresaleOrder();
        $order_info = $order_model->getPresaleOrderInfo([ [ 'id', '=', $id ] ])[ 'data' ];
        if (!empty($order_info)) {
            $fenxiao_model = new FenxiaoModel();
            return $fenxiao_model->bindRelation([
                'site_id' => $order_info[ 'site_id' ],
                'member_id' => $order_info[ 'member_id' ],
                'action' => 'order_create',
            ]);
        }
    }
}
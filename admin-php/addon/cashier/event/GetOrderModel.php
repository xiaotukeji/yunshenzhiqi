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


use addon\cashier\model\order\CashierOrder;
use app\dict\goods\GoodsDict;

/**
 * 订单类型映射
 */
class GetOrderModel
{
    /**
     * 支付方式及配置
     */
    public function handle($param)
    {
        if($param['order_type'] == 5){
            if(isset($param['order_goods']) && count($param['order_goods']) == 1){
                if(in_array($param['order_goods'][0]['goods_class'], [GoodsDict::virtual, GoodsDict::virtualcard, GoodsDict::service, GoodsDict::card])){
                    $order_model = new \app\model\order\VirtualOrder();
                    return $order_model;
                }
            }
            $order_model = new CashierOrder();
            return $order_model;
        }

    }
}
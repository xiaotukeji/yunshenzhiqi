<?php

/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 上海牛之云网络科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\cashier\model\order;


use addon\cardservice\model\MemberCard;
use app\dict\goods\GoodsDict;
use app\dict\order\OrderGoodsDict;
use app\model\BaseModel;
use app\model\goods\Goods;


/**
 * 收银业务操作
 *
 * @author Administrator
 *
 */
class CashierTrade extends BaseModel
{

    public function toSend($item, $order_info)
    {
        $action_type = $item['goods_class'];//操作类型
        if (in_array($action_type, [GoodsDict::real, GoodsDict::service, GoodsDict::card, GoodsDict::weigh])) {
            switch ($action_type) {
                case GoodsDict::card://卡项
                    $result = $this->toCard($item, $order_info);
                    break;
                case GoodsDict::real://实物
                    $result = $this->toGoods($item, $order_info);
                    break;
                case GoodsDict::weigh://称重商品
                    $result = $this->toGoods($item, $order_info);
                    break;
                case GoodsDict::service://服务
                    $result = $this->toService($item, $order_info);
                    break;
            }
            if (!empty($result)) {
                if ($result['code'] < 0) {
                    return $result;
                }
            }
            //累加销量
            $goods_model = new Goods();
            $goods_model->incGoodsSaleNum($item['sku_id'], $item['num'], $item['store_id'] ?? 0);
        }
        $order_goods_data = [
            //配送状态
            'delivery_status' => OrderGoodsDict::delivery_finish,
            'delivery_status_name' => OrderGoodsDict::getDeliveryStatus(OrderGoodsDict::delivery_finish),
        ];
        model('order_goods')->update($order_goods_data, [['order_goods_id', '=', $item['order_goods_id']]]);
        return $this->success();
    }

    /**
     * 生成卡项
     * @param $params
     * @param $order_info
     * @return array
     */
    public function toCard($params, $order_info)
    {
        $member_card_model = new MemberCard();
        $num = $params['num'];
//        $i = 0;
//        while ($i < $num) {
        $result = $member_card_model->create($params);
        if ($result['code'] < 0) {
            return $result;
        }
//            $i++;
//        }

        return $this->success();
    }

    /**
     * 实物处理
     * @param $params
     * @param $order_info
     */
    public function toGoods($params, $order_info)
    {
//        $store_id = $order_info['store_id'];
//
//        //扣除库存
//        $order_stock_model = new OrderStock();
//        $stock_params = array(
//            'store_id' => $store_id,
//            'sku_id' => $params['sku_id'],
//            'goods_id' => $params['goods_id'],
//            'stock' => $params['num'],
//            'goods_class' => $params['goods_class'],
//            'site_id' => $order_info['site_id'],
//            'user_info' => [
//                'uid' => $order_info['cashier_operator_id'],
//                'username' => $order_info['cashier_operator_name'],
//            ]
//        );
//        $result = $order_stock_model->decOrderStock($stock_params);
//        return $result;
    }

    /**
     * 生成预约服务
     * @param $params
     * @param $order_info
     * @return array
     */
    public function toService($params, $order_info)
    {
        $store_id = $order_info['store_id'];
        return $this->success();
    }

}
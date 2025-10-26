<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 上海牛之云网络科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 */

namespace addon\store\event;

use addon\cashier\model\order\CashierOrderPay;

/**
 * 订单项计算
 */
class OrderGoodsCalculate
{
    public function handle($data)
    {
        /** @var \app\model\order\OrderCreate $order_object */
        $order_object = $data['order_object'];
        $store_id = $order_object->store_id;

        if($store_id > 0){
            $site_id = $order_object->site_id;
//                $delivery_array = $data[ 'delivery' ] ?? [];
//                $delivery_type = $delivery_array[ 'delivery_type' ] ?? 'express';
            $store_config_model = new \addon\store\model\Config();
            $store_config = $store_config_model->getStoreBusinessConfig($site_id)[ 'data' ][ 'value' ] ?? [];
//                if ($store_config[ 'store_business' ] == 'shop' && !in_array($delivery_type, [ 'local', 'store' ])) {
//                    return null;
//                }
            //只要是平台运营模式,就不参与商品设置的门店,所在门店的上下架状态
            if ($store_config[ 'store_business' ] == 'shop') {
                return true;
            }

            if(!empty($order_object->goods_list)) {
                $sku_ids = array_column($order_object->goods_list, 'sku_id');
                $store_sku_condition = array(
                    ['sku_id', 'in', $sku_ids],
                    ['store_id', '=', $store_id]
                );
                $store_sku_column = model('store_goods_sku')->getColumn($store_sku_condition, 'sku_id, price, stock, status', 'sku_id');
                foreach ($order_object->goods_list as &$goods_v) {

                    $sale_store = $goods_v[ 'sale_store' ] ?? '';
                    if ($sale_store != 'all') {
                        $sale_store_ids = explode(',', $sale_store);
                        if (!in_array($store_id, $sale_store_ids)) {
                            $error = array (
                                'message' => '当前门店暂不支持销售此项商品'
                            );
                            $goods_v[ 'error' ] = $error;
                            $order_object->setError(1, '存在商品不支持在当前门店销售！');
                            continue;
                        }
                    }
                    //门店sku信息
                    $store_sku_info = $store_sku_column[$goods_v['sku_id']] ?? [];
                    if (!empty($store_sku_info)) {
                        $is_unify_price = $goods_v[ 'is_unify_price' ];
                        if ($is_unify_price == 1) {
                            //表逻辑,无实际意义
                        } else {
                            $goods_v[ 'price' ] = $store_sku_info[ 'price' ];
                        }
                        $goods_v[ 'stock' ] = numberFormat($store_sku_info[ 'stock' ]);

                        if ($store_sku_info[ 'status' ] != 1) {
                            $goods_v[ 'error' ] = array (
                                'message' => '当前门店暂不支持销售此项商品'
                            );
                            $order_object->setError(1, '存在商品不支持在当前门店销售！');

                        }
                    } else {
                        $goods_v[ 'error' ] = array (
                            'message' => '当前门店暂不支持销售此项商品'
                        );
                        $order_object->setError(1, '存在商品不支持在当前门店销售！');
                    }
                }
            }
        }


//        $store_id = $data[ 'store_id' ] ?? 0;
//        if ($store_id > 0) {
//
//
//            $site_id = $data[ 'site_id' ];
//            //还需要判断配送方式(平台运营模式如果是  同城   门店自提的话  才有可能传入store_id)
//            $delivery_array = $data[ 'delivery' ] ?? [];
//            $delivery_type = $delivery_array[ 'delivery_type' ] ?? 'express';
//            $store_config_model = new \addon\store\model\Config();
//            $store_config = $store_config_model->getStoreBusinessConfig($site_id)[ 'data' ][ 'value' ] ?? [];
//            if ($store_config[ 'store_business' ] == 'shop' && !in_array($delivery_type, [ 'local', 'store' ])) {
//                return null;
//            }
//
//            $goods_id = $data[ 'goods_id' ];
//            $goods_info = model('goods')->getInfo([ [ 'goods_id', '=', $goods_id ] ], 'sale_store');
//            $sale_store = $goods_info[ 'sale_store' ] ?? '';
//
//            if ($sale_store != 'all') {
//                $sale_store_ids = explode(',', $sale_store);
//                if (!in_array($store_id, $sale_store_ids)) {
//                    $error = array (
////                    'code' => '',//暂无映射关系
//                        'message' => '当前门店暂不支持销售此项商品'
//                    );
//                    $data[ 'error' ] = $error;
//                    return ( new CashierOrderPay() )->success($data);
//                }
//            }
//
//            $sku_id = $data[ 'sku_id' ];
//            $store_sku_condition = array (
//                [ 'sku_id', '=', $sku_id ],
//                [ 'store_id', '=', $store_id ]
//            );
//            $store_sku_info = model('store_goods_sku')->getInfo($store_sku_condition);
//            if (!empty($store_sku_info)) {
//                $is_unify_price = $data[ 'is_unify_price' ];
//                if ($is_unify_price == 1) {
//                    //表逻辑,无实际意义
//                } else {
//                    $data[ 'price' ] = $store_sku_info[ 'price' ];
//                }
//                $data[ 'stock' ] = numberFormat($store_sku_info[ 'stock' ]);
//
//                if ($store_sku_info[ 'status' ] != 1) {
//                    $error = array (
////                    'code' => '',//暂无映射关系
//                        'message' => '当前门店暂不支持销售此项商品'
//                    );
//                }
//            } else {
//                $error = array (
////                    'code' => '',//暂无映射关系
//                    'message' => '当前门店暂不支持销售此项商品'
//                );
//            }
//
//
//            if (!empty($error)) {
//                $data[ 'error' ] = $error;
//            }
//            return ( new CashierOrderPay() )->success($data);
//        }
    }

}
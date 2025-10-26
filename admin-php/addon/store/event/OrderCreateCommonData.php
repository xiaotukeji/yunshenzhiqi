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
use app\model\express\Express;
use app\model\express\Local;
use app\model\order\OrderCommon;
use app\model\order\OrderCreateTool;
use app\model\store\Store;

/**
 * 订单创建相关
 */
class OrderCreateCommonData
{
    use OrderCreateTool;
    // 行为扩展的执行入口必须是run
    public function handle($params)
    {
        /** @var \app\model\order\OrderCreate $order_object */
        $order_object = $params['order_object'];

        $type = $params['type'];
        $store_id = $order_object->store_id ?? 0;
        $site_id = $order_object->site_id;
        if($store_id == 0)
            return null;

        $store_model = new Store();
        $store_condition = array(
            ['store_id', '=', $store_id]
        );
        $store_info = $store_model->getStoreInfo($store_condition)['data'] ?? [];
        $store_is_express = $store_info['is_express'];
        $store_is_o2o = $store_info['is_o2o'];
        $store_is_pickup = $store_info['is_pickup'];

        $store_config_model = new \addon\store\model\Config();

        $store_config = $store_config_model->getStoreBusinessConfig($site_id)['data']['value'] ?? [];
        $deliver_type_sort = $order_object->config('delivery_type_sort');
        $deliver_sort_list = explode(',', $deliver_type_sort['value']['deliver_type']);
//        $deliver_sort_list = $params['deliver_sort_list'];
        $latitude = $order_object->param[ 'latitude' ] ?? 0;
        $longitude= $order_object->param[ 'longitude' ] ?? 0;
        $order_model = new OrderCommon();
        switch($type){
            case 'trade':

                //必须是连锁模式  还需要判断某一个插件是否存在
                if($store_id > 0 &&  $store_config['store_business'] == 'store'){

                    foreach ($deliver_sort_list as $type) {
                        // 物流
                        if ($type == 'express') {
                            $store_is_express = $store_is_express ?? 0;
                            if($store_is_express == 1){
                                $title = $order_object->config('express')[ 'value' ][ 'express_name' ];
                                if ($title == '') {
                                    $title = Express::express_type[ 'express' ][ 'title' ];
                                }
                                $express_type[] = [ 'title' => $title, 'name' => 'express' ];
                            }

                        }
                        // 自提
                        if ($type == 'store') {
                            $store_is_pickup = $store_is_pickup ?? 0;
                            if($store_is_pickup == 1){
                                //根据坐标查询门店
                                $store_model = new Store();
                                $store_condition = array (
                                    [ 'site_id', '=', $site_id ],
                                    [ 'is_pickup', '=', 1 ],
                                    [ 'status', '=', 1 ],
                                    [ 'is_frozen', '=', 0 ],
                                );
                                $store_condition[] = ['store_id', '=', $store_id];
                                $latlng = array (
                                    'lat' => $latitude,
                                    'lng' => $longitude,
                                );
                                $store_list = $store_model->getLocationStoreList($store_condition, '*', $latlng)['data'] ?? [];

                                $title = $order_object->config('store')[ 'value' ][ 'store_name' ];
                                if ($title == '') {
                                    $title = Express::express_type[ 'store' ][ 'title' ];
                                }
                                $express_type[] = [ 'title' => $title, 'name' => 'store', 'store_list' => $store_list ];
                            }

                        }
                        // 外卖
                        if ($type == 'local') {

                            $store_is_o2o = $store_is_o2o ?? 0;
                            if($store_is_o2o == 1){
                                //查询本店的通讯地址
                                $title = $order_object->config('local')[ 'value' ][ 'local_name' ];
                                if ($title == '') {
                                    $title = '外卖配送';
                                }
                                $store_model = new Store();
                                $local_condition = array (
                                    [ 'site_id', '=', $site_id ],
                                );
//                                if (addon_is_exit('store', $site_id)) {
                                $local_condition[] = [ 'is_o2o', '=', 1 ];
                                $local_condition[] = [ 'status', '=', 1 ];
                                $local_condition[] = [ 'is_frozen', '=', 0 ];
//                                } else {
//                                    $store_condition[] = ['is_default', '=', 1];
//                                }

                                $local_condition[] = ['store_id', '=', $store_id];

                                $latlng = array (
                                    'lat' => $latitude,
                                    'lng' => $longitude,
                                );
                                $local_store_list = $store_model->getLocationStoreList($local_condition, '*', $latlng)['data'] ?? [];
                                $express_type[] = [ 'title' => $title, 'name' => 'local', 'store_list' => $local_store_list ];
                            }
                        }
                    }
                    return $order_model->success($express_type ?? []);
                }

                break;
            case 'trade_calc':
                if($store_id > 0 &&  $store_config['store_business'] == 'store'){

                    //如果本地配送开启, 则查询出本地配送的配置
                    $local_model = new Local();
                    $local_info = $local_model->getLocalInfo([ [ 'site_id', '=', $site_id ], ['store_id', '=', $store_id ] ])['data'] ?? [];
                    $order_object->delivery[ 'local' ][ 'info' ] = $local_info;


                    $delivery_array = $order_object->param[ 'delivery' ] ?? [];
                    $delivery_type = $delivery_array[ 'delivery_type' ] ?? 'express';

                    if ($delivery_type == 'store') {
                        //门店自提
                        $delivery_money = 0;
                        $order_object->delivery[ 'delivery_type' ] = 'store';
                        if ($store_is_pickup == 0) {
                            $error = ['error_msg' => '门店自提方式未开启！'];
                        }

                        $order_object->delivery[ 'store_id' ] = $delivery_array[ 'store_id' ] ?? 0;
//                        $order_object->delivery[ 'buyer_ask_delivery_time' ] = $delivery_array[ 'buyer_ask_delivery_time' ] ?? [];
                        //同步门店信息
                        $order_object->storeOrderData();
                    } else {
                        if (empty($order_object->delivery[ 'member_address' ])) {
                            $delivery_money = 0;
                            $order_object->delivery[ 'delivery_type' ] = 'express';
                            $error = ['error_msg' => '未配置默认收货地址！'];
                        } else {
                            if ($delivery_type == 'express') {
                                if ($store_is_express == 1) {
                                    //物流配送
                                    $express = new Express();
                                    $express_fee_result = $express->calculate(['order_object' => $order_object]);
                                    if ($express_fee_result[ 'code' ] < 0) {
                                        $error = ['error_msg' => $express_fee_result[ 'message' ]];
                                        $delivery_fee = 0;
                                    } else {
                                        $delivery_fee = $express_fee_result[ 'data' ][ 'delivery_fee' ];
                                    }
                                } else {
                                    $error = ['error_msg' => '物流配送方式未开启！'];
                                    $delivery_fee = 0;
                                }
                                $order_object->delivery_money = $delivery_fee;
                                $order_object->delivery[ 'delivery_type' ] = 'express';
                            } else if ($delivery_type == 'local') {
                                //外卖配送
                                $delivery_money = 0;
                                $order_object->delivery[ 'delivery_type' ] = 'local';
                                if ($store_is_o2o == 0) {
                                    $error = ['error_msg' => '外卖配送方式未开启！'];
                                } else {
                                    if (empty($delivery_array[ 'store_id' ])) {
                                        $error = ['error_msg' => '门店未选择！'];
                                    }
//                                    $local_delivery_time = $delivery_array[ 'buyer_ask_delivery_time' ] ?? [];
//                                    $order_object->delivery[ 'buyer_ask_delivery_time' ] = $local_delivery_time;
                                    $local_model = new Local();
                                    $local_result = $local_model->calculate(['order_object' => $order_object]);
                                    $order_object->delivery[ 'start_money' ] = 0;
                                    if ($local_result[ 'code' ] < 0) {
                                        $order_object->delivery[ 'start_money' ] = $local_result[ 'data' ][ 'start_money_array' ][ 0 ] ?? 0;
                                        $error = ['error_msg' => $local_result[ 'message' ], 'priority' => 1, 'error' => $local_result[ 'data' ][ 'code' ]];
                                    } else {
                                        $order_object->delivery_money = $local_result[ 'data' ][ 'delivery_money' ];
                                        if (!empty($local_result[ 'data' ][ 'error_code' ])) {
                                            $error = ['error_msg' => $local_result[ 'data' ][ 'error' ], 'priority' => 1, 'error' => $local_result[ 'data' ][ 'error' ]];
                                        }
                                    }
                                    $order_object->delivery[ 'error' ] = $error['error'] ?? 0;
                                    $order_object->delivery[ 'error_msg' ] = $error['error_msg'] ?? '';
                                }
                            }
                        }
                    }
                    if(!empty($error)){
                        $order_object->setError($error['error'] ?? 1, $error['error_msg'] ?? '', $error['priority'] ?? 0);
                    }
                    return true;
//                    return $order_model->success(['shop_goods' => $shop_goods, 'delivery_money' => $delivery_money, 'error' => $error ?? []]);
                }

                break;
        }
    }

}
<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 上海牛之云网络科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\store\storeapi\controller;

use addon\stock\model\stock\Stock as StockModel;
use addon\store\model\Category;
use addon\store\model\Label;
use app\model\express\Local as LocalModel;
use app\model\store\Store as StoreModel;
use app\storeapi\controller\BaseStoreApi;
use addon\store\model\Config;

/**
 * 门店控制器
 */
class Store extends BaseStoreApi
{
    /**
     * 获取门店信息
     * @return false|string
     * @throws \app\exception\ApiException
     */
    public function info()
    {
        $store_model = new StoreModel();
        $store_info = $store_model->getStoreDetail([ [ 'store_id', '=', $this->store_id ] ]);

        $stock_config = [];
        if (addon_is_exit('stock')) {
            $stock_model = new StockModel();
            $stock_config = $stock_model->getStockConfig($this->site_id)[ 'data' ][ 'value' ];
        }
        $store_info[ 'data' ][ 'stock_config' ] = $stock_config;

        return $this->response($store_info);
    }

    /**
     * 门店修改
     */
    public function edit()
    {
        $condition = array (
            [ "site_id", "=", $this->site_id ],
            [ "store_id", "=", $this->store_id ]
        );
        $store_model = new StoreModel();
        $store_name = $this->params[ 'store_name' ] ?? '';
        $telphone = $this->params[ 'telphone' ] ?? '';
        $store_image = $this->params[ 'store_image' ] ?? '';
        $status = $this->params[ 'status' ] ?? 0;
        $province_id = $this->params[ 'province_id' ] ?? 0;
        $city_id = $this->params[ 'city_id' ] ?? 0;
        $district_id = $this->params[ 'district_id' ] ?? 0;
        $community_id = $this->params[ 'community_id' ] ?? 0;
        $address = $this->params[ 'address' ] ?? '';
        $full_address = $this->params[ 'full_address' ] ?? '';
        $longitude = $this->params[ 'longitude' ] ?? 0;
        $latitude = $this->params[ 'latitude' ] ?? 0;
        $open_date = $this->params[ 'open_date' ] ?? '';
        $start_time = $this->params[ 'start_time' ] ?? 0;
        $end_time = $this->params[ 'end_time' ] ?? 0;
        $time_type = $this->params[ 'time_type' ] ?? 0;
        $time_week = $this->params[ 'time_week' ] ?? '';
        $store_type = $this->params[ 'store_type' ] ?? '';
        $support_trade_type = $this->params[ 'support_trade_type' ] ?? '';
        $stock_type = $this->params[ 'stock_type' ] ?? '';
        $data = array (
            "store_name" => $store_name,
            "telphone" => $telphone,
            "store_image" => $store_image,
            "status" => $status,
            "province_id" => $province_id,
            "city_id" => $city_id,
            "district_id" => $district_id,
            "community_id" => $community_id,
            "address" => $address,
            "full_address" => $full_address,
            "longitude" => $longitude,
            "latitude" => $latitude,
            "open_date" => $open_date,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'time_type' => $time_type,
            'time_week' => $time_week,
            'support_trade_type' => $support_trade_type,
            'stock_type' => $stock_type,
            'category_id' => $this->params[ 'category_id' ] ?? 0,
            'category_name' => $this->params[ 'category_name' ] ?? '',
            'label_id' => $this->params[ 'label_id' ] ?? '',
            'label_name' => $this->params[ 'label_name' ] ?? '',
            'time_interval' => $this->params[ 'time_interval' ] ?? 30,
            'delivery_time' => $this->params[ 'delivery_time' ] ?? '',
            'advance_day' => input('advance_day', 0),
            'most_day' => input('most_day', 7),
            'store_type' => $store_type
        );
        $result = $store_model->editStore($data, $condition, [], 1, 1);
        return $this->response($result);
    }

    /**
     * 同城配送详情
     * @return false|string
     */
    public function localInfo()
    {
        $local_model = new LocalModel();
        $local_result = $local_model->getLocalInfo([ [ 'site_id', '=', $this->site_id ], [ 'store_id', '=', $this->store_id ] ]);
        return $this->response($local_result);
    }

    /**
     * 同城配送修改
     * @return false|string
     */
    public function editLocal()
    {
        $data = [
            'type' => $this->params[ 'type' ] ?? 'default',
            'area_type' => $this->params[ 'area_type' ] ?? 1,
            'local_area_json' => $this->params[ 'local_area_json' ] ?? '',//区域及业务集合json
            'time_is_open' => $this->params[ 'time_is_open' ] ?? 0,
            'time_type' => $this->params[ 'time_type' ] ?? 0,//时间选取类型 0 全天  1 自定义
            'time_week' => $this->params[ 'time_week' ] ?? '',
            'start_time' => $this->params[ 'start_time' ] ?? 0,
            'end_time' => $this->params[ 'end_time' ] ?? 0,
            'update_time' => time(),
            'is_open_step' => $this->params[ 'is_open_step' ] ?? 0,
            'start_distance' => $this->params[ 'start_distance' ] ?? 0,
            'start_delivery_money' => $this->params[ 'start_delivery_money' ] ?? 0,
            'continued_distance' => $this->params[ 'continued_distance' ] ?? 0,
            'continued_delivery_money' => $this->params[ 'continued_delivery_money' ] ?? 0,
            'start_money' => $this->params[ 'start_money' ] ?? 0,
            'delivery_money' => $this->params[ 'delivery_money' ] ?? 0,
            'area_array' => $this->params[ 'area_array' ] ?? '',//地域集合
            'man_money' => $this->params[ 'man_money' ] ?? '',
            'man_type' => $this->params[ 'man_type' ] ?? '',
            'man_discount' => $this->params[ 'man_discount' ] ?? '',
            'time_interval' => $this->params[ 'time_interval' ] ?? 30,
            'delivery_time' => $this->params[ 'delivery_time' ] ?? '',
            'advance_day' => input('advance_day', 0),
            'most_day' => input('most_day', 7)
        ];

        $condition = array (
            [ 'site_id', '=', $this->site_id ],
            [ 'store_id', '=', $this->store_id ],
        );
        $local_model = new LocalModel();
        $res = $local_model->editLocal($data, $condition);
        return $this->response($res);
    }

    /**
     * 门店标签
     */
    public function label()
    {
        $label_list = ( new Label() )->getStoreLabelList([ [ 'site_id', '=', $this->site_id ] ], 'label_id,label_name');
        return $this->response($label_list);
    }

    /**
     * 门点分类
     * @return false|string
     */
    public function category()
    {
        $category = new Category();

        $category_config = $category->getCategoryConfig($this->site_id)[ 'data' ][ 'value' ];
        $category_list = $category->getStoreCategoryList([ [ 'site_id', '=', $this->site_id ] ], 'category_id,category_name')[ 'data' ];

        return $this->response($this->success([
            'status' => $category_config[ 'status' ],
            'list' => $category_list
        ]));
    }

    /**
     * 提现配置
     * @return false|string
     */
    public function withdrawConfig()
    {
        $config = ( new Config() )->getStoreWithdrawConfig($this->site_id)[ 'data' ][ 'value' ];
        return $this->response($this->success($config));
    }
}
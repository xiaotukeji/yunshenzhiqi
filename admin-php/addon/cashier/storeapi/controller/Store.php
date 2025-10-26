<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 上海牛之云网络科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\cashier\storeapi\controller;

use addon\cashier\model\Menu;
use addon\stock\model\stock\Stock as StockModel;
use app\model\express\ExpressDeliver;
use app\model\store\Store as StoreModel;
use app\storeapi\controller\BaseStoreApi;

/**
 * 门店控制器
 */
class Store extends BaseStoreApi
{

    public function checkPageAuth()
    {
        $page = $this->params[ 'page' ] ?? '';

        if ($this->user_info[ 'is_admin' ]) return $this->response($this->success());

        $name = ( new Menu() )->getMenuValue([ [ 'url', '=', substr($page, 1) ], [ 'type', '=', 'page' ] ], 'name')[ 'data' ];
        if (empty($name)) return $this->response($this->success());

        $menu_array = $this->store_list[ $this->store_id ][ 'menu_array' ] ?? '';
        if (empty($menu_array)) return $this->response($this->success());
        if (!in_array($name, explode(',', $menu_array))) return $this->response($this->error([], 'NO_PERMISSION'));

        return $this->response($this->success());
    }

    /**
     * 获取可管理的门店列表
     * @return false|string
     */
    public function lists()
    {
        $store_ids = array_keys($this->store_list);

        $store_model = new StoreModel();
        $store_info = $store_model->getStoreList([ [ 'store_id', 'in', $store_ids ] ], 'store_id,store_image,store_name,full_address,address,open_date', 'is_default desc,store_id desc');

        return $this->response($store_info);
    }

    /**
     * 编辑门店
     * @return false|string
     */
    public function editStore()
    {
        $store_id = $this->store_id;
        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'store_id', '=', $store_id ]
        ];
        $store_model = new StoreModel();

        $data = [
            'store_name' => $this->params[ 'store_name' ] ?? '',
            'telphone' => $this->params[ 'telphone' ] ?? '',
            'store_image' => $this->params[ 'store_image' ] ?? '',

            'province_id' => $this->params[ 'province_id' ] ?? 0,
            'city_id' => $this->params[ 'city_id' ] ?? 0,
            'district_id' => $this->params[ 'district_id' ] ?? 0,
            'community_id' => $this->params[ 'community_id' ] ?? 0,
            'address' => $this->params[ 'address' ] ?? '',
            'full_address' => $this->params[ 'full_address' ] ?? '',
            'longitude' => $this->params[ 'longitude' ] ?? '',
            'latitude' => $this->params[ 'latitude' ] ?? '',
            'store_type' => $this->params[ 'store_type' ] ?? '',
        ];

        $result = $store_model->editStore($data, $condition, [], 1, 1);
        return $this->response($result);
    }

    /**
     * 编辑门店运营设置
     * @return false|string
     */
    public function editStoreOperate()
    {
        $store_id = $this->store_id;
        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'store_id', '=', $store_id ]
        ];
        $store_model = new StoreModel();

        $data = [
            'store_image' => $this->params[ 'store_image' ] ?? '',
            'start_time' => $this->params[ 'start_time' ] ?? '',
            'end_time' => $this->params[ 'end_time' ] ?? '',
            'time_type' => $this->params[ 'time_type' ] ?? '',
            'time_week' => $this->params[ 'time_week' ] ?? '',
            'stock_type' => $this->params[ 'stock_type' ] ?? '',
            'is_pickup' => $this->params[ 'is_pickup' ] ?? '',
            'is_o2o' => $this->params[ 'is_o2o' ] ?? '',
            'open_date' => $this->params[ 'open_date' ] ?? '',
            'status' => $this->params[ 'status' ] ?? 0,
        ];

        $result = $store_model->editStore($data, $condition, [], 1, 1);
        return $this->response($result);
    }

    /**
     *  配送员列表
     */
    public function deliverLists()
    {
        $deliver_model = new ExpressDeliver();
        $page = $this->params[ 'page' ] ?? '';
        $page_size = $this->params[ 'page_size' ] ?? PAGE_LIST_ROWS;
        $search_text = $this->params[ 'search_text' ] ?? '';
        $condition = [
            [
                'site_id', '=', $this->site_id,
            ],
            [
                'store_id', '=', $this->store_id,
            ]
        ];
        if (!empty($search_text)) {
            $condition[] = [ 'deliver_name', 'like', '%' . $search_text . '%' ];
        }
        $deliver_lists = $deliver_model->getDeliverPageLists($condition, '*', 'create_time desc', $page, $page_size);
        return $this->response($deliver_lists);
    }

    /**
     *  添加配送员
     */
    public function deliverinfo()
    {
        $deliver_id = $this->params[ 'deliver_id' ] ?? 0;
        $deliver_model = new ExpressDeliver();

        $result = $deliver_model->getDeliverInfo($deliver_id, $this->site_id, $this->store_id);
        return $this->response($result);
    }

    /**
     *  添加配送员
     */
    public function addDeliver()
    {
        $deliver_model = new ExpressDeliver();
        $data = [
            'deliver_name' => $this->params[ 'deliver_name' ] ?? '',
            'deliver_mobile' => $this->params[ 'deliver_mobile' ] ?? '',
            'store_id' => $this->store_id,
            'site_id' => $this->site_id,
        ];
        $result = $deliver_model->addDeliver($data);
        return $this->response($result);
    }

    /**
     *  编辑配送员
     */
    public function editDeliver()
    {
        $deliver_id = $this->params[ 'deliver_id' ] ?? 0;
        $deliver_model = new ExpressDeliver();
        $data = [
            'deliver_name' => $this->params[ 'deliver_name' ] ?? '',
            'deliver_mobile' => $this->params[ 'deliver_mobile' ] ?? '',
            'site_id' => $this->site_id,
            'store_id' => $this->store_id
        ];
        $result = $deliver_model->editDeliver($data, $deliver_id);
        return $this->response($result);
    }

    /**
     *  删除配送员
     */
    public function deleteDeliver()
    {
        $deliver_model = new ExpressDeliver();
        $deliver_id = $this->params[ 'deliver_id' ] ?? 0;
        $site_id = $this->site_id;
        $result = $deliver_model->deleteDeliver($deliver_id, $site_id, $this->store_id);
        return $this->response($result);
    }

    /**
     * 获取门店信息
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
}
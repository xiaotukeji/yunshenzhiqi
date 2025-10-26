<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\store\shop\controller;

use addon\store\model\Category;
use addon\store\model\Label;
use addon\store\model\StoreWithdraw;
use app\model\express\Config as ExpressConfig;
use app\model\express\ExpressDeliver;
use app\model\express\Local as LocalModel;
use app\model\order\Order;
use app\model\shop\ShopApply as ShopApplyModel;
use app\model\store\Store as StoreModel;
use app\model\system\Address as AddressModel;
use app\model\system\User;
use app\model\web\Config as ConfigModel;
use app\model\web\Config as WebConfig;
use app\shop\controller\BaseShop;
use addon\store\model\Config as StoreConfig;
use think\facade\Cache;
use think\facade\Session;

/**
 * 门店
 * Class Store
 * @package app\shop\controller
 */
class Store extends BaseShop
{
    /**
     * 门店首页
     */
    public function index()
    {
        if (request()->isJson()) {
            $store_model = new StoreModel();
            $order_model = new Order();
            $withdrawal_model = new StoreWithdraw();
            $data = [
                'store_num' => $store_model->getStoreCount([ [ 'site_id', '=', $this->site_id ] ])[ 'data' ],
                'in_business_num' => $store_model->getStoreCount([ [ 'site_id', '=', $this->site_id ], [ 'status', '=', 1 ] ])[ 'data' ],
                'total_order_num' => $order_model->getOrderCount([ [ 'site_id', '=', $this->site_id ], [ 'store_id', '>', 0 ], [ 'is_delete', '=', 0 ], [ 'pay_status', '=', 1 ] ])[ 'data' ],
                'total_order_money' => $order_model->getOrderMoneySum([ [ 'site_id', '=', $this->site_id ], [ 'store_id', '>', 0 ], [ 'is_delete', '=', 0 ], [ 'pay_status', '=', 1 ] ])[ 'data' ],
                'account_apply' => $store_model->getStoreSum([ [ 'site_id', '=', $this->site_id ] ], 'account_apply')[ 'data' ],
                'wait_audit_num' => $withdrawal_model->getStoreWithdrawCount([ [ 'site_id', '=', $this->site_id ], [ 'status', '=', 0 ] ])[ 'data' ],
                'wait_audit_money' => $withdrawal_model->getStoreWithdrawSum([ [ 'site_id', '=', $this->site_id ], [ 'status', '=', 0 ] ], 'money')[ 'data' ],
                'wait_transfer_num' => $withdrawal_model->getStoreWithdrawCount([ [ 'site_id', '=', $this->site_id ], [ 'status', '=', 1 ] ])[ 'data' ],
                'wait_transfer_money' => $withdrawal_model->getStoreWithdrawSum([ [ 'site_id', '=', $this->site_id ], [ 'status', '=', 1 ] ], 'money')[ 'data' ],
            ];
            return $store_model->success($data);
        }
        return $this->fetch('store/index');
    }

    /**
     * 门店排行
     */
    public function storeRanking()
    {
        if (request()->isJson()) {
            $order = input('order', 'num');
            $stat_model = new \addon\store\model\Stat();
            $data = $stat_model->getStoreOrderRank([
                'site_id' => $this->site_id,
                'order' => $order,
            ]);
            return $data;
        }
    }

    /**
     * 商品排行
     */
    public function goodsRanking()
    {
        if (request()->isJson()) {
            $order_model = new Order();
            $order = input('order', 'num');
            $condition = [
                [ 'og.site_id', '=', $this->site_id ],
                [ 'og.store_id', '>', 0 ],
                [ 'o.pay_status', '=', 1 ],
                [ 'o.is_delete', '=', 0 ]
            ];
            $join = [
                [ 'order o', 'o.order_id = og.order_id', 'inner' ],
                [ 'store s', 's.store_id = o.store_id', 'inner' ]
            ];
            $order = $order == 'num' ? 'goods_num desc' : 'goods_money desc';
            $res = $order_model->getOrderGoodsList($condition, 'sum(og.num) as goods_num, sum(o.goods_money) as goods_money,og.goods_name', $order, 5, 'og.goods_id', 'og', $join);
            return $res;
        }
    }

    /**
     * 门店列表
     * @return mixed
     */
    public function lists()
    {
        if (request()->isJson()) {
            $store_model = new StoreModel();
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
//            $order       = input("order", "create_time desc");
            $keyword = input('search_text', '');
            $status = input('status', '');
            $type = input('type', '');

            $condition = [];
            if ($type == 1) {
                if ($status != null) {
                    $condition[] = [ 'status', '=', $status ];
                    $condition[] = [ 'is_frozen', '=', 0 ];
                }
            } else if ($type == 2) {
                $condition[] = [ 'is_frozen', '=', $status ];
            }
            $condition[] = [ 'site_id', '=', $this->site_id ];
            //关键字查询
            if (!empty($keyword)) {
                $condition[] = ['store_name', 'like', '%' . $keyword . '%'];
            }
            $order = 'is_default desc,store_id desc';
            $list = $store_model->getStorePageListByAccount($condition, $page, $page_size, $order);
            return $list;
        } else {

            //判断门店插件是否存在
            $store_is_exit = addon_is_exit('store', $this->site_id);
            $this->assign('store_is_exit', $store_is_exit);
            $this->assign('title', $store_is_exit ? '门店' : '自提点');
            $this->assign('store_type', ( new StoreModel() )->getStoreType());

            $config_model = new ConfigModel();
            $default_img = $config_model->getDefaultImg($this->site_id, $this->app_module)[ 'data' ][ 'value' ];
            $this->assign('default_img', $default_img);

            return $this->fetch('store/lists');
        }
    }

    /**
     * 添加门店
     * @return mixed
     */
    public function addStore()
    {
        $is_store = addon_is_exit('store');

        if (request()->isJson()) {
            $store_name = input('store_name', '');
            $telphone = input('telphone', '');
            $store_image = input('store_image', '');
            $province_id = input('province_id', 0);
            $city_id = input('city_id', 0);
            $district_id = input('district_id', 0);
            $community_id = input('community_id', 0);
            $address = input('address', '');
            $full_address = input('full_address', '');
            $longitude = input('longitude', 0);
            $latitude = input('latitude', 0);
            $is_pickup = input('is_pickup', 0);
            $is_o2o = input('is_o2o', 0);
            $start_time = input('start_time', 0);
            $end_time = input('end_time', 0);
            $time_type = input('time_type', 0);
            $time_week = input('time_week', '');
            $stock_type = input('stock_type', '');
            if (!empty($time_week)) {
                $time_week = implode(',', $time_week);
            }
            $data = [
                'store_name' => $store_name,
                'telphone' => $telphone,
                'store_image' => $store_image,
                'province_id' => $province_id,
                'city_id' => $city_id,
                'district_id' => $district_id,
                'community_id' => $community_id,
                'address' => $address,
                'full_address' => $full_address,
                'longitude' => $longitude,
                'latitude' => $latitude,
                'open_date' => '全天营业',
                'site_id' => $this->site_id,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'time_type' => $time_type,
                'time_week' => $time_week,
                'stock_type' => $stock_type,
                'is_pickup' => $is_pickup,
                'is_o2o' => $is_o2o,
                'store_type' => input('store_type', ''),
                'category_id' => input('category_id', 0),
                'category_name' => input('category_name', ''),
                'label_id' => input('label_id', ''),
                'label_name' => input('label_name', ''),
                'store_images' => input('store_images', ''),
                'store_introduce' => input('store_introduce', '')
            ];

            //判断是否开启多门店
            if ($is_store == 1) {
                $user_data = [
                    'uid' => input('uid', ''),
                ];
            } else {
                $user_data = [];
            }
            $store_model = new StoreModel();
            $result = $store_model->addStore($data, $user_data, $is_store);
            return $result;
        } else {
            //查询省级数据列表
            $address_model = new AddressModel();
            $list = $address_model->getAreaList([ ['pid', '=', 0 ], ['level', '=', 1 ] ]);
            $this->assign('province_list', $list['data']);

            $this->assign('is_exit', $is_store);

            $this->assign('title', $is_store ? '门店' : '自提点');

            $this->assign('http_type', get_http_type());

            $config_model = new ConfigModel();
            $mp_config = $config_model->getMapConfig($this->site_id);
            $this->assign('tencent_map_key', $mp_config[ 'data' ][ 'value' ][ 'tencent_map_key' ]);

            //效验腾讯地图KEY
            $check_map_key = $config_model->checkQqMapKey($mp_config[ 'data' ][ 'value' ][ 'tencent_map_key' ]);
            $this->assign('check_map_key', $check_map_key);

            $express_type = ( new ExpressConfig() )->getEnabledExpressType($this->site_id);
            if (isset($express_type[ 'express' ])) unset($express_type[ 'express' ]);
            $this->assign('express_type', $express_type);
            $this->assign('store_type', ( new StoreModel() )->getStoreType());

            $user_list = ( new User() )->getUserList([ [ 'site_id', '=', $this->site_id ],['app_module', '=', 'shop'],['is_admin', '=', 0] ], 'uid,username')[ 'data' ];
            $this->assign('user_list', $user_list);

            $category = new Category();
            $category_config = $category->getCategoryConfig($this->site_id)[ 'data' ][ 'value' ];
            if ($category_config[ 'status' ]) {
                $category_list = $category->getStoreCategoryList([ [ 'site_id', '=', $this->site_id ] ], 'category_id,category_name')[ 'data' ];
                $this->assign('category_list', $category_list);
            }
            $this->assign('category_status', $category_config[ 'status' ]);

            $label_list = ( new Label() )->getStoreLabelList([ [ 'site_id', '=', $this->site_id ] ], 'label_id,label_name')[ 'data' ];
            $this->assign('label_list', $label_list);

            return $this->fetch('store/add_store');
        }
    }

    /**
     * 编辑门店
     * @return mixed
     */
    public function editStore()
    {
        $is_exit = addon_is_exit('store');
        $store_id = input('store_id', 0);
        $condition = [
            ['site_id', '=', $this->site_id ],
            ['store_id', '=', $store_id ]
        ];
        $store_model = new StoreModel();
        if (request()->isJson()) {
            $store_name = input('store_name', '');
            $telphone = input('telphone', '');
            $store_image = input('store_image', '');
            $province_id = input('province_id', 0);
            $city_id = input('city_id', 0);
            $district_id = input('district_id', 0);
            $community_id = input('community_id', 0);
            $address = input('address', '');
            $full_address = input('full_address', '');
            $longitude = input('longitude', 0);
            $latitude = input('latitude', 0);
            $is_pickup = input('is_pickup', 0);
            $is_o2o = input('is_o2o', 0);
            $start_time = input('start_time', 0);
            $end_time = input('end_time', 0);
            $time_type = input('time_type', 0);
            $time_week = input('time_week', '');
            $stock_type = input('stock_type', '');
            if (!empty($time_week)) {
                $time_week = implode(',', $time_week);
            }
            $data = [
                'store_name' => $store_name,
                'telphone' => $telphone,
                'store_image' => $store_image,
                'province_id' => $province_id,
                'city_id' => $city_id,
                'district_id' => $district_id,
                'community_id' => $community_id,
                'address' => $address,
                'full_address' => $full_address,
                'longitude' => $longitude,
                'latitude' => $latitude,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'time_type' => $time_type,
                'time_week' => $time_week,
                'stock_type' => $stock_type,
                'is_pickup' => $is_pickup,
                'is_o2o' => $is_o2o,
                'store_type' => input('store_type', ''),
                'category_id' => input('category_id', 0),
                'category_name' => input('category_name', ''),
                'label_id' => input('label_id', ''),
                'label_name' => input('label_name', ''),
                'store_images' => input('store_images', ''),
                'store_introduce' => input('store_introduce', '')
            ];
            $result = $store_model->editStore($data, $condition, [], $is_exit, 1);
            return $result;
        } else {
            //查询省级数据列表
            $address_model = new AddressModel();
            $list = $address_model->getAreaList([ ['pid', '=', 0 ], ['level', '=', 1 ] ]);
            $this->assign('province_list', $list['data']);
            $info_result = $store_model->getStoreDetail($condition);//门店信息
            $info = $info_result['data'];

            if (empty($info)) $this->error('未获取到门店数据', href_url('store://shop/store/lists'));

            $this->assign('info', $info);
            $this->assign('store_id', $store_id);

            $this->assign('is_exit', $is_exit);
            $this->assign('title', $is_exit ? '门店' : '自提点');
            $this->assign('http_type', get_http_type());

            $config_model = new ConfigModel();
            $mp_config = $config_model->getMapConfig($this->site_id);
            $this->assign('tencent_map_key', $mp_config[ 'data' ][ 'value' ][ 'tencent_map_key' ]);

            //效验腾讯地图KEY
            $check_map_key = $config_model->checkQqMapKey($mp_config[ 'data' ][ 'value' ][ 'tencent_map_key' ]);
            $this->assign('check_map_key', $check_map_key);

            $express_type = ( new ExpressConfig() )->getEnabledExpressType($this->site_id);
            if (isset($express_type[ 'express' ])) unset($express_type[ 'express' ]);
            $this->assign('express_type', $express_type);
            $this->assign('store_type', ( new StoreModel() )->getStoreType());

            $category = new Category();
            $category_config = $category->getCategoryConfig($this->site_id)[ 'data' ][ 'value' ];
            if ($category_config[ 'status' ]) {
                $category_list = $category->getStoreCategoryList([ [ 'site_id', '=', $this->site_id ] ], 'category_id,category_name')[ 'data' ];
                $this->assign('category_list', $category_list);
            }
            $this->assign('category_status', $category_config[ 'status' ]);

            $label_list = ( new Label() )->getStoreLabelList([ [ 'site_id', '=', $this->site_id ] ], 'label_id,label_name')[ 'data' ];
            $this->assign('label_list', $label_list);

            return $this->fetch('store/edit_store');
        }
    }

    /**
     * @return mixed
     */
    public function operate()
    {
        $store_id = input('store_id', 0);
        $condition = [
            ['site_id', '=', $this->site_id ],
            ['store_id', '=', $store_id ]
        ];
        $store_model = new StoreModel();
        if (request()->isJson()) {
            $status = input('status', 0);
            $is_pickup = input('is_pickup', 0);
            $is_o2o = input('is_o2o', 0);
            $start_time = input('start_time', 0);
            $end_time = input('end_time', 0);
            $time_type = input('time_type', 0);
            $time_week = input('time_week', '');
            $stock_type = input('stock_type', '');
            if (!empty($time_week)) {
                $time_week = implode(',', $time_week);
            }
            $data = [
                'status' => $status,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'time_type' => $time_type,
                'time_week' => $time_week,
                'stock_type' => $stock_type,
                'is_pickup' => $is_pickup,
                'is_o2o' => $is_o2o,
                'time_interval' => input('time_interval', 30),
                'delivery_time' => input('delivery_time', ''),
                'advance_day' => input('advance_day', 0),
                'most_day' => input('most_day', 7),
                'is_express' => input('is_express', 0),
                'open_date_config' => input('open_date_config', '[]'),
                'open_date' => input('open_date', ''),
                'out_open_date_o2o_pay' => input('out_open_date_o2o_pay', 1),
                'close_show' => input('close_show', 0),
                'close_desc' => input('close_desc', ''),
            ];
            $result = $store_model->editStore($data, $condition, [], 1, 1);
            return $result;
        }

        $store_info = $store_model->getStoreDetail($condition)[ 'data' ];//门店信息
        if (empty($store_info)) $this->error('未获取到门店信息');

        $this->assign('info', $store_info);
        $this->assign('store_id', $store_id);
        $business_config = ( new StoreConfig() )->getStoreBusinessConfig($this->site_id);
        $this->assign('business_config', $business_config[ 'data' ][ 'value' ]);
        return $this->fetch('store/operate');
    }

    public function frozenStore()
    {
        if (request()->isJson()) {
            $store_id = input('store_id', 0);
            $is_frozen = input('is_frozen', 0);
            $condition = [
                ['site_id', '=', $this->site_id ],
                ['store_id', '=', $store_id ]
            ];
            $store_model = new StoreModel();
            $res = $store_model->frozenStore($condition, $is_frozen);
            return $res;
        }
    }

    /**
     * 重置密码
     */
    public function modifyPassword()
    {
        if (request()->isJson()) {
            $store_id = input('store_id', '');
            $password = input('password', '123456');
            $store_model = new StoreModel();
            return $store_model->resetStorePassword($password, [ [ 'store_id', '=', $store_id ] ]);
        }
    }

    /**
     * 同城配送
     */
    public function local()
    {
        $store_id = input('store_id', 0);
        $store_model = new StoreModel();
        $info_result = $store_model->getStoreInfo([ [ 'site_id', '=', $this->site_id ], [ 'store_id', '=', $store_id ] ]);//门店信息
        $info = $info_result['data'];
        if (empty($info)) {
            $this->error([], '门店未找到');
        }
        $local_model = new LocalModel();
        if (request()->isJson()) {

            $data = [
                'type' => input('type', 'default'),//配送方式  default 商家自配送  other 第三方配送
                'area_type' => input('area_type', 1),//配送区域
                'local_area_json' => input('local_area_json', ''),//区域及业务集合json
                'time_is_open' => input('time_is_open', 0),
                'time_type' => input('time_type', 0),//时间选取类型 0 全天  1 自定义
                'time_week' => input('time_week', ''),
                'start_time' => input('start_time', 0),
                'end_time' => input('end_time', 0),
                'update_time' => time(),
                'is_open_step' => input('is_open_step', 0),
                'start_distance' => input('start_distance', 0),
                'start_delivery_money' => input('start_delivery_money', 0),
                'continued_distance' => input('continued_distance', 0),
                'continued_delivery_money' => input('continued_delivery_money', 0),
                'start_money' => input('start_money', 0),
                'delivery_money' => input('delivery_money', 0),
                'area_array' => input('area_array', ''),//地域集合
                'man_money' => input('man_money', ''),
                'man_type' => input('man_type', ''),
                'man_discount' => input('man_discount', ''),
                'time_interval' => input('time_interval', 30),
                'delivery_time' => input('delivery_time', ''),
                'advance_day' => input('advance_day', 0),
                'most_day' => input('most_day', 7)
            ];

            $condition = [
                [ 'site_id', '=', $this->site_id ],
                [ 'store_id', '=', $store_id ],
            ];
            return $local_model->editLocal($data, $condition);
        } else {

            $this->assign('store_detail', $info);
            $local_result = $local_model->getLocalInfo([ [ 'site_id', '=', $this->site_id ], [ 'store_id', '=', $store_id ] ]);

            $district_list = [];
            if ($info[ 'province_id' ] > 0 && $info[ 'city_id' ] > 0) {
                //查询省级数据列表
                $address_model = new AddressModel();
                $list = $address_model->getAreaList([ ['pid', '=', $info[ 'city_id' ] ], ['level', '=', 3 ] ]);
                $district_list = $list['data'];
            }
            $this->assign('district_list', $district_list);
            $this->assign('local_info', $local_result[ 'data' ]);

            $config_model = new WebConfig();
            $mp_config = $config_model->getMapConfig($this->site_id);
            $this->assign('tencent_map_key', $mp_config[ 'data' ][ 'value' ][ 'tencent_map_key' ]);

            //效验腾讯地图KEY
            $check_map_key = $config_model->checkQqMapKey($mp_config[ 'data' ][ 'value' ][ 'tencent_map_key' ]);
            $this->assign('check_map_key', $check_map_key);

            $this->assign('store_id', $store_id);
            return $this->fetch('store/local');
        }

    }

    /**
     *  结算设置
     */
    public function settlement()
    {
        $store_id = input('store_id', 0);
        if (empty($store_id)) {
            $this->error('未获取到门店信息');
        }
        $store_model = new StoreModel();
        if (request()->isJson()) {
            $is_settlement = input('is_settlement', 0);
            if (empty($is_settlement)) {
                $data = [
                    'is_settlement' => 0,
                    'settlement_rate' => 0
                ];
            } else {
                $data = [
                    'is_settlement' => 1,
                    'settlement_rate' => input('settlement_rate', 0),//跟随系统传入0，独立设置大于0
                    'bank_type' => input('bank_type', 0),//1微信 2.支付宝 3，银行卡
                    'bank_type_name' => input('bank_type_name', ''),  //账户类型名称 微信默认为微信  支付宝默认是支付宝 银行卡需要传银行名称
                    'bank_user_name' => input('bank_user_name', ''), //账户所属人姓名 针对银行卡需要传入
                    'bank_type_account' => input('bank_type_account', ''), //具体账户信息，微信需要传入微信名称
                ];
            }

            $condition = [
                [ 'site_id', '=', $this->site_id ],
                [ 'store_id', '=', $store_id ],
            ];
            $result = $store_model->editStore($data, $condition, [], 1, 1);
            return $result;
        }

        $store_info = $store_model->getStoreInfo([ [ 'site_id', '=', $this->site_id ], [ 'store_id', '=', $store_id ] ]);//门店信息
        if (empty($store_info)) $this->error('未获取到门店信息');

        $this->assign('info', $store_info[ 'data' ]);
        $this->assign('store_id', $store_id);
        $withdraw_config = ( new StoreConfig() )->getStoreWithdrawConfig($this->site_id);
        $this->assign('withdraw_config', $withdraw_config[ 'data' ][ 'value' ]);
        return $this->fetch('store/settlement');
    }

    /**
     * 微信授权二维码
     */
    public function createWechatAuthQrcode()
    {
        $cache_key = 'wechat_auth_' . md5(uniqid(null, true));
        $model = new \app\model\system\Qrcode();
        $url = addon_url("wechat://api/auth/getAuthInfo", [ "cache_key" => $cache_key ]);
        $qrcode = $model->createBase64Qrcode($url)['data'];
        return $model->success([
            'cache_key' => $cache_key,
            'qrcode' => $qrcode,
        ]);
    }

    /**
     * 获取微信授权数据
     */
    public function getWechatAuthData()
    {
        $cache_key = input('cache_key', '');
        $data = Cache::get($cache_key);
        $model = new StoreModel();
        return $model->success($data);
    }

    /**
     *  配送员列表
     */
    public function deliverLists()
    {
        $store_id = input('store_id', 0);

        $deliver_model = new ExpressDeliver();
        if (request()->isJson()) {
            $page = input('page', '1');
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $condition = [
                [
                    'site_id', '=', $this->site_id,
                ],
                [
                    'store_id', '=', $store_id,
                ]
            ];
            $search_text = input('search_text', '');
            if (!empty($search_text)) {
                $condition[] = [ 'deliver_name', 'like', '%' . $search_text . '%' ];
            }
            $deliver_lists = $deliver_model->getDeliverPageLists($condition, '*', 'create_time desc', $page, $page_size);
            return $deliver_lists;
        } else {
            $this->assign('store_id', $store_id);
            return $this->fetch('store/deliverlists');
        }
    }

    /**
     *  添加配送员
     */
    public function addDeliver()
    {
        $store_id = input('store_id', 0);
        $this->assign('store_id', $store_id);
        return $this->fetch('store/adddeliver');
    }

    /**
     *  编辑配送员
     */
    public function editDeliver()
    {
        $store_id = input('store_id', 0);
        $this->assign('store_id', $store_id);
        $deliver_model = new ExpressDeliver();
        $deliver_id = input('deliver_id', 0);
        $this->assign('deliver_id', $deliver_id);
        $deliver_info = $deliver_model->getDeliverInfo($deliver_id, $this->site_id);
        $this->assign('deliver_info', $deliver_info[ 'data' ]);
        return $this->fetch('store/editdeliver');
    }

    /**
     * 选择门店
     * @return mixed
     */
    public function selectStore()
    {
        $store_list = ( new StoreModel() )->getStoreList([ [ 'site_id', '=', $this->site_id ] ], 'store_id,store_name,status,address,full_address,is_frozen');
        $this->assign('store_list', $store_list[ 'data' ]);
        $store_id = explode(',', input('store_id', ''));
        $this->assign('store_id', $store_id);
        return $this->fetch('store/select');
    }

    /**
     * 门店主页装修
     */
    public function diy()
    {
        $data = [
            'site_id' => $this->site_id,
            'name' => 'DIY_STORE'
        ];
        $edit_view = event('DiyViewEdit', $data, true);
        return $edit_view;
    }

    /**
     * 门店分类
     * @return mixed
     */
    public function category()
    {
        $category = new Category();
        if (request()->isJson()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $condition = [
                [ 'site_id', '=', $this->site_id ]
            ];
            if (!empty($search_text)) $condition[] = [ 'category_name', 'like', "%{$search_text}%" ];
            return $category->getStoreCategoryPageList($condition, $page, $page_size);
        }
        $config = $category->getCategoryConfig($this->site_id)[ 'data' ][ 'value' ];
        $this->assign('status', $config[ 'status' ]);
        return $this->fetch('store/category');
    }

    /**
     * 添加分类
     * @return array
     */
    public function addCategory()
    {
        if (request()->isJson()) {
            $data = [
                'category_name' => input('category_name', ''),
                'site_id' => $this->site_id
            ];
            return ( new Category() )->addStoreCategory($data);
        }
    }

    /**
     * 编辑分类
     * @return array
     */
    public function editCategory()
    {
        if (request()->isJson()) {
            $category_id = input('category_id', 0);
            $data = [
                'category_name' => input('category_name', ''),
                'sort' => input('sort', 0)
            ];
            return ( new Category() )->editStoreCategory($data, [ [ 'category_id', '=', $category_id ], [ 'site_id', '=', $this->site_id ] ]);
        }
    }

    /**
     * 删除分类
     * @return array
     */
    public function deleteCategory()
    {
        if (request()->isJson()) {
            $category_id = input('category_id', 0);
            return ( new Category() )->deleteStoreCategory([ [ 'category_id', 'in', $category_id ], [ 'site_id', '=', $this->site_id ] ]);
        }
    }

    /**
     * 门店分类是否启用
     */
    public function categoryConfig()
    {
        if (request()->isJson()) {
            $status = input('status', 0);
            return ( new Category() )->setCategoryConfig([ 'status' => $status ], $this->site_id);
        }
    }

    /**
     * 门店标签
     * @return mixed
     */
    public function tag()
    {
        if (request()->isJson()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $search_text = input('search_text', '');
            $condition = [
                [ 'site_id', '=', $this->site_id ]
            ];
            if (!empty($search_text)) $condition[] = [ 'label_name', 'like', "%{$search_text}%" ];
            return ( new Label() )->getStoreLabelPageList($condition, $page, $page_size);
        }
        return $this->fetch('store/tag');
    }

    /**
     * 添加标签
     * @return array
     */
    public function addLabel()
    {
        if (request()->isJson()) {
            $data = [
                'label_name' => input('label_name', ''),
                'site_id' => $this->site_id,
                'create_time' => time()
            ];
            return ( new Label() )->addStoreLabel($data);
        }
    }

    /**
     * 编辑标签
     * @return array
     */
    public function editLabel()
    {
        if (request()->isJson()) {
            $label_id = input('label_id', 0);
            $data = [
                'label_name' => input('label_name', ''),
                'sort' => input('sort', 0)
            ];
            return ( new Label() )->editStoreLabel($data, [ [ 'label_id', '=', $label_id ], [ 'site_id', '=', $this->site_id ] ]);
        }
    }

    /**
     * 删除标签
     * @return array
     */
    public function deleteLabel()
    {
        if (request()->isJson()) {
            $label_id = input('label_id', 0);
            return ( new Label() )->deleteStoreLabel([ [ 'label_id', '=', $label_id ], [ 'site_id', '=', $this->site_id ] ]);
        }
    }

    /**
     * 门店标签选择框
     * @return mixed
     */
    public function labelSelect()
    {
        if (request()->isJson()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $search_text = input('search_text', '');
            $condition = [
                [ 'site_id', '=', $this->site_id ]
            ];
            if (!empty($search_text)) $condition[] = [ 'label_name', 'like', "%{$search_text}%" ];
            return ( new Label() )->getStoreLabelPageList($condition, $page, $page_size);
        } else {
            $select_id = input('select_id', '');
            $this->assign('select_id', $select_id);
            return $this->fetch('store/label_select');
        }
    }

    /**
     * 修改排序
     */
    public function modifySort()
    {
        if (request()->isJson()) {
            $sort = input('sort', 0);
            $label_id = input('label_id', 0);
            $label_model = new Label();
            $res = $label_model->modifySort($sort, $label_id, $this->site_id);
            return $res;
        }
    }

}
<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\replacebuy\shop\controller;

use addon\replacebuy\model\GoodsSku;
use app\model\member\Member as MemberModel;
use app\model\store\Store;
use app\model\storegoods\StoreGoods;
use app\model\web\Config as WebConfig;
use app\shop\controller\BaseShop;
use app\model\goods\GoodsCategory as GoodsCategoryModel;
use app\model\goods\Goods as GoodsModel;
use think\App;
use think\facade\Db;
use think\facade\Session;
use app\model\goods\GoodsLabel as GoodsLabelModel;
use app\model\member\MemberAddress as MemberAddressModel;
use app\model\system\Address as AddressModel;
use app\model\express\Config as ExpressConfig;

class Replacebuy extends BaseShop
{
    public $buyer_info = [];//买家
    public $cart = [];//购物车
    public $default_member_info = [];//默认绑定会员
    public $address_id;

    public function __construct(App $app = null)
    {
        //执行父类构造函数
        $this->buyer_info = Session::get("replacebuy_buyer_info") ?? [];
        $this->default_member_info = Session::get("default_member_info") ?? [];
        $this->address_id = Session::get("address_id") ?? 0;

        $this->replace = [
            'REPLACEBUY_CSS' => __ROOT__ . '/addon/replacebuy/shop/view/public/css',
            'REPLACEBUY_JS' => __ROOT__ . '/addon/replacebuy/shop/view/public/js',
            'REPLACEBUY_IMG' => __ROOT__ . '/addon/replacebuy/shop/view/public/img',
        ];
        parent::__construct($app);
    }

    /**
     * 代客下单页面
     */
    public function index()
    {
        Session::set("address_id", []);

        //新增
        Session::set("replacebuy_buyer_info", []);
        Session::set("r_cart", []);

        //修改
//        $this->assign("buyer_info", $this->buyer_info);
        $this->assign("buyer_info", []);

        $cart = Session::get("r_cart") ?? [];
        if (!empty($cart)) {
            foreach ($cart as $k => $item) {
                $goods = new GoodsModel();
                $sku_info = $goods->getGoodsSkuInfo([
                    [ 'goods_state', '=', 1 ],
                    [ 'is_delete', '=', 0 ],
                    [ 'sku_id', '=', $item[ 'sku_id' ] ],
                    [ 'site_id', '=', $this->site_id ],
                    [ '', 'exp', Db::raw("(sale_channel = 'all' OR sale_channel = 'online')") ]
                ], 'discount_price');
                if (!empty($sku_info[ 'data' ])) {
                    $cart[ $k ][ 'sku_price' ] = $sku_info[ 'data' ][ 'discount_price' ];
                } else {
                    unset($cart[ $k ]);
                }
            }
            Session::set("r_cart", $cart);
        }
        $this->assign("cart", $cart);

        //获取商品分类
        $goods_catrgory_model = new GoodsCategoryModel();
        $goods_catrgory_list = $goods_catrgory_model->getCategoryList([ [ 'site_id', "=", $this->site_id ], [ "pid", "=", 0 ], [ "is_show", "=", 0 ] ]);
        $this->assign("goods_catrgory_list", $goods_catrgory_list[ "data" ]);

        //获取商品分组
        $goods_label_model = new GoodsLabelModel();
        $label_list = $goods_label_model->getLabelList([ [ 'site_id', '=', $this->site_id ] ], 'id,label_name', 'create_time desc');
        $label_list = $label_list[ 'data' ];
        $this->assign("label_list", $label_list);

        return $this->fetch("replacebuy/index");
    }

    /**
     * 提交订单页面
     */
    public function order()
    {
        $this->assign("buyer_info", $this->buyer_info);
        $cart = Session::get("r_cart") ?? [];
        if (!empty($cart)) {
            foreach ($cart as $k => $item) {
                $goods = new GoodsModel();
                $sku_info = $goods->getGoodsSkuInfo([ [ 'goods_state', '=', 1 ], [ 'is_delete', '=', 0 ], [ 'sku_id', '=', $item[ 'sku_id' ] ], [ 'site_id', '=', $this->site_id ] ], 'discount_price,support_trade_type');
                if (!empty($sku_info[ 'data' ])) {
                    $cart[ $k ][ 'sku_price' ] = $sku_info[ 'data' ][ 'discount_price' ];
                    $cart[ $k ][ 'support_trade_type' ] = $sku_info[ 'data' ][ 'support_trade_type' ];
                    $goods_member_price = $goods->getGoodsPrice($item[ 'sku_id' ], $this->buyer_info[ 'member_id' ])[ 'data' ];
                    if (!empty($goods_member_price[ 'member_price' ])) {
                        $cart[ $k ][ 'sku_price' ] = $goods_member_price[ 'member_price' ];
                    }
                } else {
                    unset($cart[ $k ]);
                }
            }
            Session::set("r_cart", $cart);
        }
        $this->assign("cart", $cart);

        //渲染缓存中的地址信息
//        $this->assign("address_id", $this->address_id);

        //查询省级数据列表
        $address_model = new AddressModel();
        $list = $address_model->getAreaList([ [ "pid", "=", 0 ], [ "level", "=", 1 ] ]);
        $this->assign("province_list", $list[ "data" ]);

        //查询是否开启快递配送
        $express_config_model = new ExpressConfig();
        $express_config_result = $express_config_model->getExpressConfig($this->site_id);
        $express_config = $express_config_result[ "data" ];
        $shop_goods[ "express_config" ] = $express_config;
        $is_use = 0;
        if ($shop_goods[ "express_config" ][ "is_use" ] == 1) {
            $is_use = 1;
        }
        $this->assign("is_use", $is_use);

        //查询默认地址
        if ($this->address_id) {
            $this->assign("address_id", $this->address_id);
        } else {
            $member_address = new MemberAddressModel();
            $info = $member_address->getMemberDefault($this->buyer_info[ 'member_id' ]);
            $address_id = 0;
            if ($info) {
                $address_id = $info[ 'id' ];
            }
            Session::set("address_id", $address_id);
            $this->assign("address_id", $address_id);
        }

        //查询默认店铺
        $store_model = new Store();
        $store_info = $store_model->getDefaultStore($this->site_id,'store_id,store_name,is_frozen,status,full_address,address')['data'] ?? [];
        $this->assign("store_id", $store_info['store_id'] ?? 0);
        $this->assign("store", $store_info);
        //查询门店运营模式
        $store_business = 'shop';
        if(addon_is_exit('store')){
            $config_model = new \addon\store\model\Config();
            $business_config = $config_model->getStoreBusinessConfig($this->site_id)['data']['value'];
            if ($business_config['store_business'] == 'store'){
                $store_business = 'store';
            }
        }
        $this->assign("store_business", $store_business);

        $config_model = new WebConfig();
        $mp_config = $config_model->getMapConfig($this->site_id);
        $this->assign('tencent_map_key', $mp_config['data']['value']['tencent_map_key']);
        return $this->fetch("replacebuy/order");
    }

    /**
     * 获取商品列表
     */
    public function getGoodsSkuList()
    {
        if (request()->isJson()) {
            $page_index = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $search_text = input('search_text', "");
            $category_id = input('category_id', '');
            $label_id = input('label_id', "");
            $condition = [
                [ 'g.goods_state', '=', 1 ],
                [ 'g.is_delete', '=', 0 ],
                [ 'g.site_id', '=', $this->site_id ],
                [ 'g.is_virtual', "=", 0 ],
                [ '', 'exp', Db::raw("(g.sale_channel = 'all' OR g.sale_channel = 'online')") ]
            ];
            if (!empty($search_text)) {
                $condition[] = [ 'gs.sku_name|gs.sku_no', 'like', '%' . $search_text . '%' ];
            }

            if (!empty($category_id)) {
                $condition[] = [ 'g.category_id', 'like', '%,' . $category_id . ',%' ];
            }

            if (!empty($label_id)) {
                $condition[] = [ 'g.label_id', '=', $label_id ];
            }

            $goods_sku_model = new GoodsSku();
            $res = $goods_sku_model->getGoodsSkuPageList($condition, $page_index, $page_size);
            return $res;
        }
    }

    /**
     * 选择会员
     */
    public function loginBuyer()
    {
        if (request()->isJson()) {
            $member_model = new MemberModel();
            $member_search = input("member_search", '');
            $member_id = input('member_id', '');
            if (empty($member_id))
                return $member_model->error([], '请选择会员！');
            $condition = [];
            $condition[] = [ 'mobile|email|username', '=', $member_search ];
            $condition[] = [ 'site_id', '=', $this->site_id ];
            $condition[] = [ 'member_id', '=', $member_id ];

            $member_info_result = $member_model->getMemberInfo($condition);
            $member_info = $member_info_result[ "data" ];
            if (empty($member_info))
                return $member_model->error([], "账号不存在！");

            Session::set("replacebuy_buyer_info", $member_info);
            Session::set("address_id", []);
            return $member_info_result;
        }
    }

    /**
     * 会员列表
     */
    public function getMemberList()
    {
        $page_index = input('page', 1);
        $page_size = input('page_size', PAGE_LIST_ROWS);
        $member_search = input("member_search", '');
        $condition = [];
        $condition[] = [ 'mobile|email|username|nickname', 'like', '%' . $member_search . '%' ];
        $condition[] = [ 'site_id', '=', $this->site_id ];
        $member_model = new MemberModel();
        $list = $member_model->getMemberPageList($condition, $page_index, $page_size, 'member_id desc', 'member_id,headimg,nickname,username,mobile,point,balance,balance_money');
        return $list;
    }

    /**
     * 注销会员
     */
    public function logoutBuyer()
    {
        if (request()->isJson()) {
            Session::set("replacebuy_buyer_info", []);
            $member_model = new MemberModel();
            return $member_model->success();
        }
    }

    /**
     * 购物车同步
     */
    public function cart()
    {
        $cart_json = input("cart_json", "");

        $temp_array = [];
        if (!empty($cart_json)) {
            $temp_array = json_decode($cart_json, true);
        }
        Session::set("r_cart", $temp_array);
    }

    /**
     * 得到买家id
     */
    public function buyer()
    {
        //查看是否登陆了会员
        if (!empty($this->buyer_info)) {
            $buyer_member_id = $this->buyer_info[ "member_id" ];
        } else {
            $buyer_member_id = $this->default_member_info[ "member_id" ];
        }
        return $buyer_member_id;
    }

    /**
     * 新增用户收货地址
     */
    public function addAddress()
    {
        if (request()->isJson()) {
            $data = [
                'site_id' => $this->site_id,
                'member_id' => $this->buyer(),
                'name' => input("name", ''),
                'mobile' => input("mobile", ''),
                'telephone' => input("telephone", ''),
                'province_id' => input("province_id", ''),
                'city_id' => input("city_id", ''),
                'district_id' => input("district_id", ''),
                'community_id' => input("community_id", ''),
                'address' => input("address", ''),
                'full_address' => input("full_address", ''),
                'longitude' => input("longitude", ''),
                'latitude' => input("latitude", ''),
                'is_default' => 1
            ];
            $member_address = new MemberAddressModel();
            $res = $member_address->addMemberAddress($data);
            return $res;
        }

    }

    /**
     * 用户地址分页列表信息
     */
    public function addressPage()
    {
        if (request()->isJson()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $type = input('type', 1);
            $member_address = new MemberAddressModel();
            $condition = [
                [ 'member_id', '=', $this->buyer() ],
                [ 'site_id', '=', $this->site_id ]
            ];
            if (!empty($type)) {
                $condition[] = [ 'type', '=', $type ];
            }
//            $list = $member_address->getMemberAddressPageList($condition, $page, $page_size);
            $list = $member_address->getMemberAddressList($condition);
            return $list;
        }
    }

    /**
     * 选择地址
     */
    public function choiceAddress()
    {
        if (request()->isJson()) {
            $address_id = input('address_id', 0);
            Session::set("address_id", $address_id);
            $member_address = new MemberAddressModel();
            $member_address->setMemberDefaultAddress($address_id,$this->buyer());
        }
    }
}
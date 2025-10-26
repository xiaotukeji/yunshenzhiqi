<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\cardservice\shop\controller;

use addon\cardservice\model\CardGoods;
use addon\cardservice\model\MemberCard as MemberCardModel;
use addon\form\model\Form;
use addon\supply\model\Supplier as SupplierModel;
use app\model\goods\Goods as GoodsModel;
use app\model\goods\GoodsBrand as GoodsBrandModel;
use app\model\goods\GoodsService as GoodsServiceModel;
use app\model\store\Store as StoreModel;
use app\model\web\Config as ConfigModel;
use app\shop\controller\BaseShop;
use think\App;


/**
 * 虚拟商品
 * Class Virtualgoods
 * @package app\shop\controller
 */
class Card extends BaseShop
{

    public function __construct(App $app = null)
    {
        $this->replace = [
            'ADDON_CARDSERVICE_CSS' => __ROOT__ . '/addon/cardservice/shop/view/public/css',
            'ADDON_CARDSERVICE_JS' => __ROOT__ . '/addon/cardservice/shop/view/public/js',
            'ADDON_CARDSERVICE_IMG' => __ROOT__ . '/addon/cardservice/shop/view/public/img',
        ];
        parent::__construct($app);
    }

    /**
     * 添加商品
     * @return mixed
     */
    public function addGoods()
    {
        if (request()->isJson()) {

            $category_id = input('category_id', 0);// 分类id
            $category_json = json_encode($category_id);//分类字符串
            $category_id = '';

            $data = [
                'goods_name' => input('goods_name', ''),// 商品名称,
                'goods_attr_class' => input('goods_attr_class', ''),// 商品类型id,
                'goods_attr_name' => input('goods_attr_name', ''),// 商品类型名称,
                'is_limit' => input('is_limit', '0'),// 商品是否限购,
                'limit_type' => input('limit_type', '1'),// 商品限购类型,
                'site_id' => $this->site_id,
                'category_id' => $category_id,
                'category_json' => $category_json,
                'goods_image' => input('goods_image', ''),// 商品主图路径
                'goods_content' => input('goods_content', ''),// 商品详情
                'goods_state' => input('goods_state', ''),// 商品状态（1.正常0下架）
                'price' => input('price', 0),// 商品价格（取第一个sku）
                'renew_price' => input('renew_price', 0), // 续费价格
                'market_price' => input('market_price', 0),// 市场价格（取第一个sku）
                'cost_price' => input('cost_price', 0),// 成本价（取第一个sku）
                'sku_no' => input('sku_no', ''),// 商品sku编码
                'weight' => input('weight', ''),// 重量
                'volume' => input('volume', ''),// 体积
                'goods_stock' => input('goods_stock', 0),// 商品库存（总和）
                'goods_stock_alarm' => input('goods_stock_alarm', 0),// 库存预警
                'goods_spec_format' => input('goods_spec_format', ''),// 商品规格格式
                'goods_attr_format' => input('goods_attr_format', ''),// 商品参数格式
                'introduction' => input('introduction', ''),// 促销语
                'keywords' => input('keywords', ''),// 关键词
                'unit' => input('unit', ''),// 单位
                'sort' => input('sort', 0),// 排序,
                'video_url' => input('video_url', ''),// 视频
                'goods_sku_data' => input('goods_sku_data', ''),// SKU商品数据
                'goods_service_ids' => input('goods_service_ids', ''),// 商品服务id集合
                'label_id' => input('label_id', ''),// 商品分组id
                'brand_id' => input('brand_id', 0),//品牌id
                'virtual_sale' => input('virtual_sale', 0),// 虚拟销量
                'max_buy' => input('max_buy', 0),// 限购
                'min_buy' => input('min_buy', 0),// 起售
                'recommend_way' => input('recommend_way', 0), // 推荐方式，1：新品，2：精品，3；推荐
                'timer_on' => strtotime(input('timer_on', 0)),//定时上架
                'timer_off' => strtotime(input('timer_off', 0)),//定时下架
                'is_consume_discount' => input('is_consume_discount', 0),//是否参与会员折扣
                'qr_id' => input('qr_id', 0),// 社群二维码id
                'template_id' => input('template_id', 0), // 商品海报id
                'sale_show' => input('sale_show', 0),//
                'stock_show' => input('stock_show', 0),//
                'market_price_show' => input('market_price_show', 0),//
                'barrage_show' => input('barrage_show', 0),//
                'form_id' => input('form_id', 0),
                'sale_channel' => input('sale_channel', 'all'),
                'sale_store' => input('sale_store', 'all'),
                'card_type' => input('card_type', ''),
                'validity_type' => input('validity_type', 0),
                'discount_goods_type' => input('discount_goods_type', 'all'),
                'discount' => input('discount', 0),
                'common_num' => input('common_num', 0),
                'relation_goods' => input('relation_goods', '[]'),
                'is_unify_price' => input('is_unify_price', 1),
                'supplier_id' => input('supplier_id', 0)
            ];
            if ($data[ 'validity_type' ] == 1) {
                $data[ 'validity_day' ] = input('validity_day', 0);
            } else if ($data[ 'validity_type' ] == 2) {
                $data[ 'validity_time' ] = strtotime(input('validity_time', ''));
            }

            $virtual_goods_model = new CardGoods();
            $res = $virtual_goods_model->addGoods($data);
            return $res;
        } else {
            $virtual_goods_model = new CardGoods();

            // 商品服务
            $goods_service_model = new GoodsServiceModel();
            $service_list = $goods_service_model->getServiceList([ [ 'site_id', '=', $this->site_id ] ], 'id,service_name,icon')[ 'data' ];
            $this->assign('service_list', $service_list);

            // 商品品牌
            $goods_brand_model = new GoodsBrandModel();
            $brand_list = $goods_brand_model->getBrandList([ [ 'site_id', '=', $this->site_id ] ], 'brand_id,brand_name', 'sort asc')[ 'data' ];
            $this->assign('brand_list', $brand_list);

            //商品默认排序值
            $config_model = new ConfigModel();
            $sort_config = $config_model->getGoodsSort($this->site_id)[ 'data' ][ 'value' ];
            $this->assign('sort_config', $sort_config);

            //获取商品海报
            $poster_list = event('PosterTemplate', [ 'site_id' => $this->site_id ], true);
            if (!empty($poster_list)) {
                $poster_list = $poster_list[ 'data' ];
            }

            $this->assign('poster_list', $poster_list);
            $this->assign('virtualcard_exit', addon_is_exit('virtualcard', $this->site_id));

            $form_is_exit = addon_is_exit('form', $this->site_id);
            if ($form_is_exit) {
                $form_list = ( new Form() )->getFormList([ [ 'site_id', '=', $this->site_id ], [ 'form_type', '=', 'goods' ], [ 'is_use', '=', 1 ] ], 'id desc', 'id, form_name')[ 'data' ];
                $this->assign('form_list', $form_list);
            }
            $this->assign('form_is_exit', $form_is_exit);

            $this->assign('all_goodsclass', event('GoodsClass'));
            $this->assign('goods_class', $virtual_goods_model->getGoodsClass());
            $this->assign('card_type', $virtual_goods_model->getCardType());

            $this->assign('store_is_exit', addon_is_exit('store', $this->site_id));

            $is_install_supply = addon_is_exit('supply');
            if ($is_install_supply) {
                $supplier_model = new SupplierModel();
                $supplier_list = $supplier_model->getSupplyList([ [ 'supplier_site_id', '=', $this->site_id ] ], 'supplier_id,title', 'supplier_id desc')['data'];
                $this->assign('supplier_list', $supplier_list);
            }
            $this->assign('is_install_supply', $is_install_supply);

            return $this->fetch('card/add_goods');
        }
    }

    /**
     * 编辑商品
     * @return mixed
     */
    public function editGoods()
    {
        $virtual_goods_model = new CardGoods();
        if (request()->isJson()) {

            $category_id = input('category_id', 0);// 分类id
            $category_json = json_encode($category_id);//分类字符串
            $category_id = '';

            $data = [
                'goods_id' => input('goods_id', 0),// 商品id
                'goods_name' => input('goods_name', ''),// 商品名称,
                'goods_attr_class' => input('goods_attr_class', ''),// 商品类型id,
                'goods_attr_name' => input('goods_attr_name', ''),// 商品类型名称,
                'is_limit' => input('is_limit', '0'),// 商品是否限购,
                'limit_type' => input('limit_type', '1'),// 商品限购类型,
                'site_id' => $this->site_id,
                'category_id' => $category_id,
                'category_json' => $category_json,
                'goods_image' => input('goods_image', ''),// 商品主图路径
                'goods_content' => input('goods_content', ''),// 商品详情
                'goods_state' => input('goods_state', ''),// 商品状态（1.正常0下架）
                'price' => input('price', 0),// 商品价格（取第一个sku）
                'renew_price' => input('renew_price', 0), // 续费价格
                'market_price' => input('market_price', 0),// 市场价格（取第一个sku）
                'cost_price' => input('cost_price', 0),// 成本价（取第一个sku）
                'sku_no' => input('sku_no', ''),// 商品sku编码
                'weight' => input('weight', ''),// 重量
                'volume' => input('volume', ''),// 体积
                'goods_stock' => input('goods_stock', 0),// 商品库存（总和）
                'goods_stock_alarm' => input('goods_stock_alarm', 0),// 库存预警
                'goods_spec_format' => input('goods_spec_format', ''),// 商品规格格式
                'goods_attr_format' => input('goods_attr_format', ''),// 商品参数格式
                'introduction' => input('introduction', ''),// 促销语
                'keywords' => input('keywords', ''),// 关键词
                'unit' => input('unit', ''),// 单位
                'sort' => input('sort', 0),// 排序,
                'video_url' => input('video_url', ''),// 视频
                'goods_sku_data' => input('goods_sku_data', ''),// SKU商品数据
                'goods_service_ids' => input('goods_service_ids', ''),// 商品服务id集合
                'label_id' => input('label_id', ''),// 商品分组id
                'brand_id' => input('brand_id', 0),//品牌id
                'virtual_sale' => input('virtual_sale', 0),// 虚拟销量
                'max_buy' => input('max_buy', 0),// 限购
                'min_buy' => input('min_buy', 0),// 起售
                'recommend_way' => input('recommend_way', 0), // 推荐方式，1：新品，2：精品，3；推荐
                'timer_on' => strtotime(input('timer_on', 0)),//定时上架
                'timer_off' => strtotime(input('timer_off', 0)),//定时下架
                'spec_type_status' => input('spec_type_status', 0),
                'is_consume_discount' => input('is_consume_discount', 0),//是否参与会员折扣
                'qr_id' => input('qr_id', 0),// 社群二维码id
                'template_id' => input('template_id', 0), // 商品海报id
                'sale_show' => input('sale_show', 0),//
                'stock_show' => input('stock_show', 0),//
                'market_price_show' => input('market_price_show', 0),//
                'barrage_show' => input('barrage_show', 0),//
                'form_id' => input('form_id', 0),
                'sale_channel' => input('sale_channel', 'all'),
                'sale_store' => input('sale_store', 'all'),
                'validity_type' => input('validity_type', 0),
                'discount_goods_type' => input('discount_goods_type', 'all'),
                'discount' => input('discount', 0),
                'common_num' => input('common_num', 0),
                'relation_goods' => input('relation_goods', '[]'),
                'is_unify_price' => input('is_unify_price', 1),
                'supplier_id' => input('supplier_id', 0)
            ];
            if ($data[ 'validity_type' ] == 1) {
                $data[ 'validity_day' ] = input('validity_day', 0);
            } else if ($data[ 'validity_type' ] == 2) {
                $data[ 'validity_time' ] = strtotime(input('validity_time', ''));
            }
            $res = $virtual_goods_model->editGoods($data);
            return $res;
        } else {
            $goods_model = new GoodsModel();
            $goods_id = input('goods_id', 0);
            $goods_info = $virtual_goods_model->editGetGoodsInfo([ 'goods_id' => $goods_id, 'site_id' => $this->site_id ])[ 'data' ];
            if (empty($goods_info)) $this->error('未获取到商品数据', href_url('shop/goods/lists'));

            $goods_sku_list = $goods_model->getGoodsSkuList([ [ 'goods_id', '=', $goods_id ], [ 'site_id', '=', $this->site_id ] ], 'sku_id,sku_name,sku_no,sku_spec_format,price,market_price,cost_price,stock,virtual_indate,sku_image,sku_images,goods_spec_format,spec_name,stock_alarm,is_default', '')[ 'data' ];
            $goods_info[ 'sku_list' ] = $goods_sku_list;
            $this->assign('goods_info', $goods_info);

            // 商品服务
            $goods_service_model = new GoodsServiceModel();
            $service_list = $goods_service_model->getServiceList([ [ 'site_id', '=', $this->site_id ] ], 'id,service_name,icon')[ 'data' ];
            $this->assign('service_list', $service_list);

            // 商品品牌
            $goods_brand_model = new GoodsBrandModel();
            $brand_list = $goods_brand_model->getBrandList([ [ 'site_id', '=', $this->site_id ] ], 'brand_id,brand_name', 'sort asc')[ 'data' ];
            $this->assign('brand_list', $brand_list);

            //获取商品海报
            $poster_list = event('PosterTemplate', [ 'site_id' => $this->site_id ], true);
            if (!empty($poster_list)) {
                $poster_list = $poster_list[ 'data' ];
            }
            $this->assign('poster_list', $poster_list);

            $form_is_exit = addon_is_exit('form', $this->site_id);
            if ($form_is_exit) {
                $form_list = ( new Form() )->getFormList([ [ 'site_id', '=', $this->site_id ], [ 'form_type', '=', 'goods' ], [ 'is_use', '=', 1 ] ], 'id desc', 'id, form_name')[ 'data' ];
                $this->assign('form_list', $form_list);
            }
            $this->assign('form_is_exit', $form_is_exit);

            $store_is_exit = addon_is_exit('store', $this->site_id);
            if ($store_is_exit && $goods_info[ 'sale_store' ] != 'all') {
                $store_list = ( new StoreModel() )->getStoreList([ [ 'site_id', '=', $this->site_id ], [ 'store_id', 'in', $goods_info[ 'sale_store' ] ] ], 'store_id,store_name,status,address,full_address,is_frozen');
                $this->assign('store_list', $store_list[ 'data' ]);
            }
            $this->assign('store_is_exit', $store_is_exit);
            $this->assign('card_type', $virtual_goods_model->getCardType());

            $is_install_supply = addon_is_exit('supply');
            if ($is_install_supply) {
                $supplier_model = new SupplierModel();
                $supplier_list = $supplier_model->getSupplyList([ [ 'supplier_site_id', '=', $this->site_id ] ], 'supplier_id,title', 'supplier_id desc')['data'];
                $this->assign('supplier_list', $supplier_list);
            }
            $this->assign('is_install_supply', $is_install_supply);

            return $this->fetch('card/edit_goods');
        }
    }

    public function goodscard()
    {
        $goods_id = input('goods_id', 0);
        $model = new MemberCardModel();
        if (request()->isJson()) {
            $page_index = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $search_text = input('search_text', '');
            $condition = [
                [ 'mgc.site_id', '=', $this->site_id ],
                [ 'mgc.goods_id', '=', $goods_id ],
            ];
            if (!empty($search_text)) {
                $condition[] = [ 'm.nickname', 'like', '%' . $search_text . '%' ];
            }

            $field = 'mgc.*, g.goods_name,g.price,g.goods_image,m.username,m.nickname,m.headimg';
            $join = [
                [ 'goods g', 'mgc.goods_id = g.goods_id', 'inner' ],
                [ 'member m', 'mgc.member_id = m.member_id', 'left' ],
            ];
            $list = $model->getCardPageList($condition, $field, 'mgc.create_time desc', $page_index, $page_size, 'mgc', $join);
            return $list;
        } else {
            $this->assign('goods_id', $goods_id);
            $goods_model = new GoodsModel();
            $goods_info = $goods_model->getGoodsInfo([ [ 'goods_id', '=', $goods_id ], [ 'site_id', '=', $this->site_id ] ], 'goods_id, goods_name,goods_image,price,goods_state,goods_stock,sale_num');
            $this->assign('goods_info', $goods_info[ 'data' ]);
            $card_stat = $model->getCardInfo([ [ 'goods_id', '=', $goods_id ], [ 'site_id', '=', $this->site_id ] ], 'sum(total_num) as total_num, sum(total_use_num) as total_use_num')[ 'data' ] ?? [];
            $this->assign('total_num', $card_stat[ 'total_num' ] ?? 0);
            $this->assign('total_use_num', $card_stat[ 'total_use_num' ] ?? 0);
            $card_info = $model->getCardSelect([ [ 'goods_id', '=', $goods_id ], [ 'site_id', '=', $this->site_id ] ])[ 'data' ] ?? [];
            $this->assign('card_info', $card_info);
            return $this->fetch('card/goods_card');
        }
    }

    public function membergoodscard()
    {
        $member_id = input('member_id', 0);
        if (request()->isJson()) {
            $page_index = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $search_text = input('search_text', '');
            $condition = [
                [ 'mgc.site_id', '=', $this->site_id ],
                [ 'mgc.member_id', '=', $member_id ],
                [ 'mgc.status', '=', 1 ],
            ];
            if (!empty($search_text)) {
                $condition[] = [ 'g.goods_name', 'like', '%' . $search_text . '%' ];
            }
            $model = new MemberCardModel();
            $card_goods = new CardGoods();
            $field = 'mgc.*, g.goods_name,g.price,g.goods_image,m.username,m.nickname,m.headimg';
            $join = [
                [ 'goods g', 'mgc.goods_id = g.goods_id', 'inner' ],
                [ 'member m', 'mgc.member_id = m.member_id', 'left' ],
            ];
            $list = $model->getCardPageList($condition, $field, 'mgc.create_time desc', $page_index, $page_size, 'mgc', $join);
            foreach ($list[ 'data' ][ 'list' ] as $k => $v) {
                $list[ 'data' ][ 'list' ][ $k ][ 'card_type_name' ] = $card_goods->getCardType($v[ 'card_type' ])[ 'title' ];
            }
            return $list;
        } else {
            $this->assign('member_id', $member_id);
            return $this->fetch('card/member_goods_card');
        }
    }

    public function detail()
    {
        $card_id = input('card_id', 0);
        $model = new MemberCardModel();
        $card_goods = new CardGoods();
        $condition = [
            [ 'mgc.card_id', '=', $card_id ],
            [ 'mgc.site_id', '=', $this->site_id ],
        ];
        $field = 'mgc.*, g.goods_name,g.price,g.goods_image,m.username,m.nickname,m.headimg';
        $join = [
            [ 'goods g', 'mgc.goods_id = g.goods_id', 'inner' ],
            [ 'member m', 'mgc.member_id = m.member_id', 'left' ],
        ];
        $detail = $model->getCardInfo($condition, $field, 'mgc', $join)[ 'data' ] ?? [];

        $detail[ 'card_type_name' ] = $card_goods->getCardType($detail[ 'card_type' ])[ 'title' ];
        $this->assign('detail', $detail);

        $condition = [];
        $condition[] = [ 'mgc.card_id', '=', $card_id ];

        $condition[] = [ 'g.goods_state', '=', 1 ];
        $condition[] = [ 'g.is_delete', '=', 0 ];
        $field = 'mgc.*, g.sku_name';

        $join = [
            [ 'goods_sku g', 'mgc.sku_id = g.sku_id', 'left' ],
        ];
        $item_list = $model->getCartItemList($condition, $field, 'mgc.item_id asc', 'mgc', $join)[ 'data' ] ?? [];
        $this->assign('item_list', $item_list);
        return $this->fetch('card/detail');
    }

    public function getCardItem()
    {
        if (request()->isJson()) {
            $page_index = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $card_id = input('card_id', 0);

            $condition = [];
            $condition[] = [ 'mgc.site_id', '=', $this->site_id ];
            $condition[] = [ 'mgc.card_id', '=', $card_id ];

            $condition[] = [ 'g.goods_state', '=', 1 ];
            $condition[] = [ 'g.is_delete', '=', 0 ];
            $alias = 'mgc';

            $field = 'mgc.*, g.goods_name,g.price,g.goods_image,g.introduction,m.nickname,m.headimg,m.mobile';

            $join = [
                [ 'goods g', 'mgc.goods_id = g.goods_id', 'inner' ],
                [ 'member m', 'mgc.member_id = m.member_id', 'left' ],
            ];

            $model = new MemberCardModel();
            $card_goods = new CardGoods();
            $list = $model->getCartItemPageList($condition, $field, 'mgc.item_id asc', $page_index, $page_size, $alias, $join);
            foreach ($list[ 'data' ][ 'list' ] as $k => $v) {
                $list[ 'data' ][ 'list' ][ $k ][ 'card_type_name' ] = $card_goods->getCardType($v[ 'card_type' ])[ 'title' ];
            }
            return $list;
        }
    }

    public function records()
    {
        if (request()->isJson()) {
            $page_index = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $card_id = input('card_id', 0);
            $item_id = input('item_id', 0);

            $condition = [];
            $condition[] = [ 'cr.site_id', '=', $this->site_id ];
            if (!empty($item_id)) {
                $condition[] = [ 'cr.card_item_id', '=', $item_id ];
            }
            if (!empty($card_id)) {
                $condition[] = [ 'cr.card_id', '=', $card_id ];
            }
            $alias = 'cr';
            $prefix = config('database.connections.mysql.prefix');
            $field = 'cr.*, sku.sku_name,sku.sku_image,sku.sku_images,sku.price,ci.num as item_num,
        IF(cr.type = \'order\', (select order_id from `' . $prefix . 'order_goods` og where og.order_goods_id = cr.relation_id), 0) as order_id, s.store_name';

            $join = [
                [ 'member_goods_card_item ci', 'ci.item_id = cr.card_item_id', 'left' ],
                [ 'goods_sku sku', 'ci.sku_id = sku.sku_id', 'left' ],
                [ 'store s', 'cr.store_id = s.store_id', 'left' ],
            ];

            $model = new MemberCardModel();
            $list = $model->getMemberCardRecordsPageList($condition, $field, 'cr.create_time desc', $page_index, $page_size, $alias, $join);
            return $list;
        }
    }

}
<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\cardservice\api\controller;

use addon\cardservice\model\CardGoods;
use app\api\controller\BaseApi;
use app\model\goods\Goods;
use app\model\goods\GoodsService;
use app\model\store\Store;
use app\model\storegoods\StoreGoods;
use app\model\web\Config as ConfigModel;
use think\facade\Db;


/**
 * 卡项
 */
class Card extends BaseApi
{
    public function __construct()
    {
        parent::__construct();
        $this->initStoreData();
    }

    public function detail()
    {
        $sku_id = $this->params['sku_id'] ?? 0;
        $goods_id = $this->params['goods_id'] ?? 0;
        $goods = new CardGoods();
        if (empty($sku_id) && !empty($goods_id)) {
            $sku_id = $goods->getGoodsInfo([ [ 'goods_id', '=', $goods_id ] ], 'sku_id')[ 'data' ][ 'sku_id' ] ?? 0;
        }
        if (empty($sku_id) && empty($goods_id)) {
            return $this->response($this->error('', 'REQUEST_ID'));
        }

        $condition = [
            [ 'gs.sku_id', '=', $sku_id ],
            [ 'gs.site_id', '=', $this->site_id ],
            [ 'gs.is_delete', '=', 0 ],
            [ 'g.goods_class', '=', $goods->getGoodsClass()[ 'id' ] ]
        ];
        $field = 'gs.goods_id,gs.sku_id,gs.qr_id,gs.goods_name,gs.sku_name,gs.sku_spec_format,gs.price,gs.market_price,gs.discount_price,gs.promotion_type,gs.start_time
        ,gs.end_time,gs.stock,gs.click_num,(g.sale_num + g.virtual_sale) as sale_num,gs.collect_num,gs.sku_image,gs.sku_images
        ,gc.card_type,gc.card_type_name,gc.renew_price,gc.recharge_money,gc.common_num,gc.discount_goods_type,gc.discount,gc.validity_type,gc.validity_day,gc.validity_time
        ,gs.goods_content,gs.goods_state,gs.is_free_shipping,gs.goods_spec_format,gs.goods_attr_format,gs.introduction,gs.unit,gs.video_url
        ,gs.is_virtual,gs.goods_service_ids,gs.max_buy,gs.min_buy,gs.is_limit,gs.limit_type,gs.support_trade_type,g.goods_image,g.keywords,g.stock_show,g.sale_show,g.market_price_show,g.barrage_show,g.evaluate,g.sale_store,g.sale_channel';
        $join = [
            [ 'goods g', 'g.goods_id = gs.goods_id', 'inner' ]
        ];

        // 如果是连锁运营模式
        if ($this->store_data[ 'config' ][ 'store_business' ] == 'store') {
            $join[] = [ 'store_goods_sku sgs', 'gs.goods_id = sgs.goods_id and sgs.store_id=' . $this->store_id, 'left' ];

            $field .= ',IFNULL(sgs.status, 0) as store_goods_status';

            $field = str_replace('gs.price', 'IFNULL(IF(g.is_unify_price = 1,gs.price,sgs.price), gs.price) as price', $field);
            $field = str_replace('gs.discount_price', 'IFNULL(IF(g.is_unify_price = 1,gs.discount_price,sgs.price), gs.discount_price) as discount_price', $field);
            if ($this->store_data[ 'store_info' ][ 'stock_type' ] == 'store') {
                $field = str_replace('gs.stock', 'IFNULL(sgs.stock, 0) as stock', $field);
            }
        }
        $goods_sku_detail = $goods->getGoodsSkuInfo($condition, $field, 'gs', $join)[ 'data' ];
        if (empty($goods_sku_detail)) return $this->response($this->error());

        if ($this->store_data[ 'config' ][ 'store_business' ] == 'store') {
            // 销售渠道设置为线上销售时门店商品状态为1
            if ($goods_sku_detail[ 'sale_channel' ] == 'online') {
                $goods_sku_detail[ 'store_goods_status' ] = 1;
            }
        }

        $goods_sku_detail[ 'purchased_num' ] = 0; // 该商品已购数量
        $res[ 'goods_sku_detail' ] = $goods_sku_detail;

        // 商品服务
        $goods_service = new GoodsService();
        $goods_service_list = $goods_service->getServiceList([ [ 'site_id', '=', $this->site_id ], [ 'id', 'in', $res[ 'goods_sku_detail' ][ 'goods_service_ids' ] ] ], 'service_name,desc,icon');
        $res[ 'goods_sku_detail' ][ 'goods_service' ] = $goods_service_list[ 'data' ];

        return $this->response($this->success($res));
    }

    /**
     * 列表信息
     */
    public function page()
    {
        $page = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;
        $goods_id_arr = $this->params['goods_id_arr'] ?? '';//goods_id数组
        $keyword = isset($this->params[ 'keyword' ]) ? trim($this->params[ 'keyword' ]) : '';//关键词
        $service_category = $this->params['service_category'] ?? 0;//分类
        $is_free_shipping = $this->params['is_free_shipping'] ?? 0;//是否免邮
        $order = $this->params['order'] ?? '';//排序（综合、销量、价格）
        $sort = $this->params['sort'] ?? '';//升序、降序
        $card_type = $this->params['card_type'] ?? '';

        $condition = [];
        $condition[] = [ 'gs.site_id', '=', $this->site_id ];
        $condition[] = [ 'g.goods_class', '=', ( new CardGoods() )->getGoodsClass()[ 'id' ] ];
        $condition[] = [ '', 'exp', Db::raw("(g.sale_channel = 'all' OR g.sale_channel = 'online')") ];

        if (!empty($goods_id_arr)) {
            $condition[] = [ 'gs.goods_id', 'in', $goods_id_arr ];
        }
        if (!empty($card_type)) {
            $condition[] = [ 'gc.card_type', '=', $card_type ];
        }
        if (!empty($service_category)) {
            $condition[] = [ 'g.service_category', 'like', '%,' . $service_category . ',%' ];
        }

        if (!empty($keyword)) {
            $condition[] = [ 'g.goods_name|gs.sku_name|gs.keywords', 'like', '%' . $keyword . '%' ];
        }

        if (!empty($is_free_shipping)) {
            $condition[] = [ 'gs.is_free_shipping', '=', $is_free_shipping ];
        }

        // 非法参数进行过滤
        if ($sort != 'desc' && $sort != 'asc') {
            $sort = '';
        }

        // 非法参数进行过滤
        if ($order != '') {
            if ($order != 'sale_num' && $order != 'discount_price') {
                $order = 'gs.sort';
            } elseif ($order == 'sale_num') {
                $order = 'sale_sort';
            } else {
                $order = 'gs.' . $order;
            }
            $order_by = $order . ' ' . $sort;
        } else {

            $config_model = new ConfigModel();
            $sort_config = $config_model->getGoodsSort($this->site_id)[ 'data' ][ 'value' ];
            $order_by = 'g.sort ' . $sort_config[ 'type' ] . ',g.create_time desc';
        }

        $condition[] = [ 'g.goods_state', '=', 1 ];
        $condition[] = [ 'g.is_delete', '=', 0 ];
        $condition[] = [ '', 'exp', Db::raw("(g.sale_channel = 'all' OR g.sale_channel = 'offline')") ];
        $alias = 'gs';

        $field = 'gs.is_consume_discount,gs.discount_config,gs.discount_method,gs.member_price,gs.goods_id,gs.sort,gs.sku_id,gs.sku_name,gs.price,gs.market_price,gs.discount_price,gs.stock,(g.sale_num + g.virtual_sale) as sale_num,(gs.sale_num + gs.virtual_sale) as sale_sort,gs.sku_image,gs.goods_name,gs.site_id,gs.is_free_shipping,gs.introduction,gs.promotion_type,g.goods_image,g.promotion_addon,gs.is_virtual,g.goods_spec_format,g.recommend_way,gs.max_buy,gs.min_buy,gs.unit,gs.is_limit,gs.limit_type,g.label_name,g.stock_show,g.sale_show,g.market_price_show,g.barrage_show,g.sale_channel,g.sale_store,
            gc.card_type,gc.card_type_name,gc.renew_price,gc.recharge_money,gc.common_num,gc.discount_goods_type,gc.discount,gc.validity_type,gc.validity_day,gc.validity_time';
        $join = [
            [ 'goods g', 'gs.sku_id = g.sku_id', 'inner' ],
            [ 'goods_card gc', 'gc.goods_id=gs.goods_id', 'left' ],
        ];
        // 如果是连锁运营模式
        if ($this->store_data[ 'config' ][ 'store_business' ] == 'store') {
            $join[] = [ 'store_goods_sku sgs', 'sgs.status = 1 and g.sku_id = sgs.sku_id and sgs.store_id=' . $this->store_id, 'right' ];

            $condition[] = [ 'g.sale_store', 'like', [ '%all%', '%,' . $this->store_id . ',%' ], 'or' ];

            $field = str_replace('gs.price', 'IFNULL(IF(g.is_unify_price = 1,gs.price,sgs.price), gs.price) as price', $field);
            $field = str_replace('gs.discount_price', 'IFNULL(IF(g.is_unify_price = 1,gs.price,sgs.price), gs.price) as discount_price', $field);
            if ($this->store_data[ 'store_info' ][ 'stock_type' ] == 'store') {
                $field = str_replace('gs.stock', 'IFNULL(sgs.stock, 0) as stock', $field);
            }
        }

        $goods = new Goods();
        $list = $goods->getGoodsSkuPageList($condition, $page, $page_size, $order_by, $field, $alias, $join);

        return $this->response($list);
    }

    public function getCardListByType()
    {
        $goods = new CardGoods();
        $list = $goods->getCardType();
        $condition = [
            [ 'gs.site_id', '=', $this->site_id ],
            [ 'g.goods_class', '=', $goods->getGoodsClass()[ 'id' ] ],
            [ 'g.goods_state', '=', 1 ],
            [ 'g.is_delete', '=', 0 ],
            [ '', 'exp', Db::raw("(g.sale_channel = 'all' OR g.sale_channel = 'online')") ]
        ];

        $field = 'gs.is_consume_discount,gs.discount_config,gs.discount_method,gs.member_price,gs.goods_id,gs.sort,gs.sku_id,gs.sku_name,gs.price,gs.market_price,gs.discount_price,gs.stock,(g.sale_num + g.virtual_sale) as sale_num,(gs.sale_num + gs.virtual_sale) as sale_sort,gs.sku_image,gs.goods_name,gs.site_id,gs.is_free_shipping,gs.introduction,gs.promotion_type,g.goods_image,g.promotion_addon,gs.is_virtual,g.goods_spec_format,g.recommend_way,gs.max_buy,gs.min_buy,gs.unit,gs.is_limit,gs.limit_type,g.label_name,g.stock_show,g.sale_show,g.market_price_show,g.barrage_show,g.sale_channel,g.sale_store,
            gc.card_type,gc.card_type_name,gc.renew_price,gc.recharge_money,gc.common_num,gc.discount_goods_type,gc.discount,gc.validity_type,gc.validity_day,gc.validity_time';
        $join = [
            [ 'goods_sku gs', 'gs.sku_id = g.sku_id', 'inner' ],
            [ 'goods_card gc', 'gc.goods_id=gs.goods_id', 'left' ],
        ];

        // 如果是连锁运营模式
        if ($this->store_data[ 'config' ][ 'store_business' ] == 'store') {
            $join[] = [ 'store_goods_sku sgs', 'sgs.status = 1 and g.sku_id = sgs.sku_id and sgs.store_id=' . $this->store_id, 'right' ];

            $condition[] = [ 'g.sale_store', 'like', [ '%all%', '%,' . $this->store_id . ',%' ], 'or' ];

            $field = str_replace('gs.price', 'IFNULL(IF(g.is_unify_price = 1,gs.price,sgs.price), gs.price) as price', $field);
            $field = str_replace('gs.discount_price', 'IFNULL(IF(g.is_unify_price = 1,gs.price,sgs.price), gs.price) as discount_price', $field);
            if ($this->store_data[ 'store_info' ][ 'stock_type' ] == 'store') {
                $field = str_replace('gs.stock', 'IFNULL(sgs.stock, 0) as stock', $field);
            }
        }
        $card_list = $goods->getGoodsList($condition, $field, 'g.sort asc', 'g', $join);

        foreach ($list as $k => $v) {
            $list[ $k ][ 'card_list' ] = [];
            foreach ($card_list[ 'data' ] as $key => $val) {
                if ($val[ 'card_type' ] == $v[ 'type' ]) {
                    $list[ $k ][ 'card_list' ][] = $val;
                }
            }
            if (empty($list[ $k ][ 'card_list' ])) unset($list[ $k ]);
        }
        return $this->response($this->success($list));
    }

    /**
     * 获取商品关联的卡项套餐
     * @return array|false|string
     */
    public function getRelationCardGoods()
    {
        $goods_id = $this->params[ 'goods_id' ] ?? '';
        $store_id = $this->params[ 'store_id' ] ?? '';
        if(empty($goods_id)){
            return $this->response($this->error('', 'REQUEST_ID'));
        }
        $condition = [
            [ 'goods_id', '=', $goods_id ]
        ];
        $goods_card_res = (new CardGoods())->getGoodSCardItem($condition,'card_goods_id','id desc','','','card_goods_id');

        if($goods_card_res['code'] < 0){
            return $this->response($this->error([]));
        }

        $card_goods_id =  array_column($goods_card_res['data'],'card_goods_id');
        $goods_condition = [
            ['gci.card_goods_id','in',$card_goods_id],
            [ 'g.goods_state', '=', 1 ],
            [ 'g.is_delete', '=', 0 ],
        ];
        $join = [
            [ 'goods_card gc', 'gci.card_goods_id = gc.goods_id', 'left'],
            [ 'goods_sku gs', 'gci.card_goods_id = gs.goods_id', 'left' ],
            [ 'goods g', 'g.goods_id = gs.goods_id', 'left' ],
            [ 'goods_sku gck', 'gck.sku_id = gci.sku_id', 'left' ],
        ];

        if($store_id){
            if(addon_is_exit('store')){
                $config_model = new \addon\store\model\Config();
                $business_config = $config_model->getStoreBusinessConfig($this->site_id)['data']['value'];
                if ($business_config['store_business'] == 'store'){
                    $goods_condition[] =  ['', 'exp', Db::raw("g.sale_store = 'all' or FIND_IN_SET('{$store_id}', g.sale_store)")];
                    //连锁门店模式
                    $join[] = ['store_goods sg','sg.goods_id = gci.card_goods_id','left'];
                    $goods_condition[] = [ 'sg.store_id', '=', $store_id];
                    $goods_condition[] = [ 'sg.status', '=', 1];
                }
            }
        }

        $alias = 'gci';
        $field = "gc.card_type,gc.card_type_name,
                  gci.num,gci.goods_id,gci.card_goods_id as goods_id,
                  gs.is_consume_discount,gs.discount_config,gs.discount_method,gs.member_price,gs.sort,gs.sku_id,gs.sku_name,gs.price,gs.market_price,gs.discount_price,gs.stock,gs.sku_image,gs.goods_name,gs.site_id,gs.is_free_shipping,gs.introduction,gs.promotion_type,g.goods_stock,g.goods_image,g.promotion_addon,gs.is_virtual,g.goods_spec_format,g.recommend_way,gs.max_buy,gs.min_buy,gs.unit,gs.is_limit,gs.limit_type,g.label_name,g.stock_show,g.sale_show,g.market_price_show,g.barrage_show,g.sale_channel,g.sale_store,
                  gck.sku_image as item_sku_image,gck.sku_name as item_sku_name,gck.goods_name as item_goods_name,gck.price as item_price,gck.goods_id as item_goods_id,gck.sku_id as item_sku_id";
        $all_goods_arr = (new CardGoods())->getGoodSCardItem($goods_condition,$field,'gci.id desc',$alias,$join);
        if($all_goods_arr['code'] < 0){
            return $this->response($this->error([]));
        }
        $data = [];
        foreach ($all_goods_arr['data'] as $k=>$v){
            if(!isset($data[$v['goods_id']])){
                $data[$v['goods_id']] = $v;
                $data[$v['goods_id']]['goods'] = [];
            }
            $group =  $data[$v['goods_id']];
            $group['goods'][$v['item_sku_id']] = [
                'goods_id' => $v['item_goods_id'],
                'sku_id'=>$v['item_sku_id'],
                'sku_image' => $v['item_sku_image'],
                'goods_name' => $v['item_goods_name'],
                'sku_name' => $v['item_sku_name'],
                'price'=>$v['item_price'],
                'num'=>$v['num']
            ];
            unset($group['item_goods_id'],$group['item_sku_image'],$group['item_sku_name'],$group['item_goods_name'],$group['item_price'],$group['num']);
            $data[$v['goods_id']] = $group;
        }
        $data = array_values($data);
        foreach ($data as $k=>$v){
            $data[$k]['goods'] = array_values($v['goods']);
        }
        $token = $this->checkToken();
        if ($token[ 'code' ] >= 0) {
            $goods = new Goods();
            $data = $goods->getGoodsListMemberPrice($data, $this->member_id);
        }
        return $this->response($this->success($data));
    }

}
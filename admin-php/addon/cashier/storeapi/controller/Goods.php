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

namespace addon\cashier\storeapi\controller;

use addon\supply\model\Supplier as SupplierModel;
use app\dict\goods\GoodsDict;
use app\model\goods\Goods as GoodsModel;
use app\model\goods\GoodsBrand as GoodsBrandModel;
use app\model\goods\GoodsCategory;
use app\model\storegoods\StoreGoods as StoreGoodsModel;
use app\model\system\Export as ExportModel;
use app\model\web\Config as ConfigModel;
use app\storeapi\controller\BaseStoreApi;
use think\facade\Db;

/**
 * Class Goods
 * @package addon\cashier\storeapi\controller
 */
class Goods extends BaseStoreApi
{
    /**
     * 获取商品分类的组织
     * @return false|string
     */
    public function category()
    {
        $level = $this->params['level'] ?? 1;
        $category_ids = $this->params['category_ids'] ?? '';

        $category_model = new GoodsCategory();
        $condition = [
            ['is_show', '=', 0],
            ['level', '<=', $level],
            ['site_id', '=', $this->site_id]
        ];
        if ($category_ids) {
            $condition[] = ['category_id', 'in', $category_ids];
        }
        $list = $category_model->getCategoryList($condition, 'pid,category_id,category_name,image,level', 'sort asc,category_id desc')['data'];
        $tree = list_to_tree($list, 'category_id', 'pid', 'child_list', 0);
        $tree = keyArrToIndexArr($tree, 'child_list');

        return $this->response($category_model->success($tree));
    }

    public function page()
    {
        $page_index = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;
        $goods_category = $this->params['category'] ?? 'all';
        $search_text = $this->params['search_text'] ?? '';
        $goods_class = $this->params['goods_class'] ?? 'all';
        $status = $this->params['status'] ?? 1;
        $start_price = $this->params['start_price'] ?? 0;
        $end_price = $this->params['end_price'] ?? 0;
        $goods_state = $this->params['goods_state'] ?? 'all';
        $sku_no = $this->params['sku_no'] ?? '';
        $scene = $this->params['scene'] ?? 'common';//普通common 开单billing

        $model = new GoodsModel();
        $condition = [
            ['g.site_id', '=', $this->site_id],
            ['g.is_delete', '=', 0],
            ['g.sale_store', 'like', ['%all', '%,' . $this->store_id . ',%'], 'or'],
           // ['', 'exp', Db::raw("(g.sale_channel = 'all' OR g.sale_channel = 'offline')")]
        ];

        if ($goods_class !== 'all') {
            $condition[] = ['g.goods_class', 'in', $goods_class];
        } else {
            $condition[] = ['g.goods_class', 'in', '1,4,5,6'];
        }

        if ($goods_category != 'all') $condition[] = ['g.category_id', 'like', "%,{$goods_category},%"];

        if (!empty($search_text)) {
            $goods_sku_list = $model->getGoodsSkuList([['', 'exp', Db::raw("FIND_IN_SET('{$search_text}', sku_no)")]], 'goods_id')['data'];
            $goods_id_arr = array_unique(array_column($goods_sku_list, 'goods_id'));
            if (!empty($goods_id_arr)) {
                $condition[] = ['g.goods_id', 'in', $goods_id_arr];
            } else {
                $condition[] = ['g.goods_name', 'like', "%{$search_text}%"];
            }
        }

        if ($status !== 'all') {
            if ($status == '0') {
                $condition[] = ['', 'exp', Db::raw('sg1.status is null or sg1.status = 0')];
            } else {
                $condition[] = ['sg1.status', '=', $status];
            }
        }

        if ($goods_state !== 'all') {
            $condition[] = ['g.goods_state', '=', $goods_state];
        }

        if (!empty($start_price)) {
            $condition[] = ['g.price', '>=', $start_price];
        }
        if (!empty($end_price)) {
            $condition[] = ['g.price', '<=', $end_price];
        }

        if ($sku_no) {
            $sku_list = $model->getGoodsSkuList([['sku_no', 'like', '%' . $sku_no . '%']], 'goods_id')['data'];
            $goods_ids = array_column($sku_list, 'goods_id');
            $goods_ids = array_unique($goods_ids);
            $condition[] = ['g.goods_id', 'in', $goods_ids];
        }

        $field = 'g.goods_id,g.goods_name,g.goods_class,g.goods_class_name,g.introduction,g.goods_image,g.goods_state,g.sku_id,g.price,gs.discount_price,g.goods_spec_format,g.is_unify_price,g.pricing_type, 
        IFNULL(IF(g.is_unify_price = 1,g.price,sg1.price), g.price) as price, IFNULL(IF(g.is_unify_price = 1,gs.discount_price,sg1.price), gs.discount_price) as discount_price,g.unit,
    IFNULL(sg1.price, 0) as store_price,IFNULL(sg1.status, 0) as store_status';
        $join = [
            ['goods_sku gs', 'gs.sku_id = g.sku_id', 'left'],
            ['store_goods sg1', 'g.goods_id=sg1.goods_id and (sg1.store_id is null or sg1.store_id=' . $this->store_id . ')', 'left'],
        ];
        //todo  这部分可以封装
        $stock_store_id = (new \app\model\store\Store())->getStoreStockTypeStoreId(['store_id' => $this->store_id])['data'] ?? 0;
        if ($stock_store_id == $this->store_id) {
            $field .= ', IFNULL(sg1.stock, 0) as stock';
        } else {
            $join[] = ['store_goods sg2', 'g.goods_id = sg2.goods_id and sg2.store_id=' . $stock_store_id, 'left'];
            $field .= ', IFNULL(sg2.stock, 0) as stock';
        }
        $res = $model->getGoodsPageList($condition, $page_index, $page_size, 'g.sort asc,g.create_time desc', $field, 'g', $join);

        //开单页面库存转换处理
        if(addon_is_exit('stock')){
            if($scene == 'billing'){
                $goods_ids = array_column($res['data']['list'], 'goods_id');
                $field = 'gs.goods_id,gs.sku_id';
                $alias = 'gs';
                $join = [
                    ['goods g', 'g.goods_id = gs.goods_id', 'inner'],
                    [
                        'store_goods_sku sgs',
                        'sgs.sku_id = gs.sku_id and sgs.store_id = ' . $this->store_id,
                        'left'
                    ]
                ];
                //判断是统一库存还是独立库存
                $store_model = new \app\model\store\Store();
                $store_info = $store_model->getStoreInfo([['store_id', '=', $this->store_id]])['data'];
                if ($store_info[ 'stock_type' ] == 'store') {
                    $field .= ',IFNULL(sgs.stock, 0) as stock';
                }else{
                    $field .= ',gs.stock';
                }
                $condition = [
                    ['gs.is_delete', '=', 0],
                    ['sgs.status', '=', 1],
                    ['gs.goods_id', 'in', $goods_ids],
                    ['gs.site_id', '=', $this->site_id]
                ];
                $sku_list = $model->getGoodsSkuList($condition, $field, '', null, $alias, $join)['data'];
                $sku_list = $model->goodsStockTransform($sku_list, $this->store_id, 'store');
                $goods_stocks = [];
                foreach ($goods_ids as $goods_id){
                    $goods_stocks[$goods_id] = 0;
                }
                foreach($sku_list as $sku_info){
                    $goods_id = $sku_info['goods_id'];
                    $goods_stocks[$goods_id] += $sku_info['stock'];
                }
                foreach($res['data']['list'] as &$goods_info){
                    $goods_info['stock'] = $goods_stocks[$goods_info['goods_id']];
                }
            }
        }

        return $this->response($res);
    }

    /**
     * 商品详情
     * @return false|string
     */
    public function detail()
    {
        $goods_id = $this->params['goods_id'] ?? 0;
        $goods_model = new GoodsModel();
        $field = 'g.goods_id, g.goods_name, g.introduction,g.goods_class,g.is_virtual, g.goods_class_name, g.goods_image, g.goods_state, g.sku_id, g.price, g.unit, g.cost_price, g.category_id, g.brand_name,g.is_unify_price,g.pricing_type,
         sg1.price as store_price, sg1.cost_price as store_cost_price, sg1.status as store_status';

        $join = [
            ['store_goods sg1', 'g.goods_id=sg1.goods_id and sg1.store_id=' . $this->store_id, 'left'],
        ];

        //todo  这部分可以封装
        $stock_store_id = (new \app\model\store\Store())->getStoreStockTypeStoreId(['store_id' => $this->store_id])['data'] ?? 0;
        if ($stock_store_id == $this->store_id) {
            $field .= ',sg1.stock';
        } else {
            $join[] = ['store_goods sg2', 'g.goods_id = sg2.goods_id and sg2.store_id=' . $stock_store_id, 'left'];
            $field .= ', sg2.stock';
        }
        $condition = [
            ['g.site_id', '=', $this->site_id],
            ['g.is_delete', '=', 0],
            ['g.goods_id', '=', $goods_id],
            ['g.sale_store', 'like', ['%all%', '%,' . $this->store_id . ',%'], 'or'],
           // ['', 'exp', Db::raw("(g.sale_channel = 'all' OR g.sale_channel = 'offline')")]
        ];

        $goods_info = $goods_model->getGoodsInfo($condition, $field, 'g', $join)['data'];

        if (empty($goods_info)) return $this->response($goods_model->error(null, '商品信息缺失'));
        //查询商品规格
        $sku_filed = 'IFNULL(sgs1.is_delivery_restrictions, 1) as is_delivery_restrictions   ,sku.sku_id,sku.sku_name,sku.sku_no,sku.price,sku.discount_price,sku.cost_price,sku.sku_image,sku.sku_images,sku.spec_name,sku.unit,
            IF(sku.is_unify_price = 1,sku.discount_price,sgs1.price) as store_price, sgs1.cost_price as store_cost_price, sgs1.status as store_status';
        $join = [
            ['store_goods_sku sgs1', 'sku.sku_id=sgs1.sku_id and sgs1.store_id=' . $this->store_id, 'left'],
        ];
        if ($stock_store_id == $this->store_id) {
            $sku_filed .= ', sgs1.stock, IFNULL(sgs1.real_stock, 0) as real_stock';
        } else {
            $join[] = ['store_goods_sku sgs2', 'sku.sku_id = sgs2.sku_id and sgs2.store_id=' . $stock_store_id, 'left'];
            $sku_filed .= ', sgs2.stock, IFNULL(sgs2.real_stock, 0) as real_stock';
        }
        $goods_info['sku_list'] = $goods_model->getGoodsSkuList([['sku.goods_id', '=', $goods_id], ['sku.site_id', '=', $this->site_id]], $sku_filed, 'sku.sku_id asc', 0, 'sku', $join)['data'];

        return $this->response($goods_model->success($goods_info));
    }

    /**
     * 上下架
     */
    public function setStatus()
    {
        $goods_id = $this->params['goods_id'] ?? 0;
        $status = $this->params['status'] ?? 0;
        $model = new StoreGoodsModel();
        $res = $model->modifyGoodsState($goods_id, $status, $this->site_id, $this->store_id);
        return $this->response($res);
    }

    /**
     * 商品编辑
     */
    public function editGoods()
    {
        $goods_sku_array = isset($this->params['goods_sku_list']) ? json_decode($this->params['goods_sku_list'], true) : [];
        $model = new StoreGoodsModel();
        $res = $model->editStoreGoods($goods_sku_array, $this->site_id, $this->store_id, $this->uid);
        return $this->response($res);
    }


    /**
     * 获取商品规格
     */
    public function skuList()
    {
        $goods_id = $this->params['goods_id'] ?? 0;

        $sku_filed = 'sku.goods_id,sku.sku_id,sku.sku_name,sku.goods_name,sku.sku_no,sku.sku_image,sku.sku_images,sku.spec_name,sku.goods_spec_format,sku.unit,IFNULL(IF(g.is_unify_price = 1,sku.price,sgs.price), sku.price) as price,g.goods_class,sku.pricing_type';
        $join = [
            ['goods g', 'sku.goods_id=g.goods_id', 'inner'],
            ['store_goods_sku sgs', 'sku.sku_id=sgs.sku_id and sgs.store_id=' . $this->store_id, 'left'],
        ];
        $stock_store_id = (new \app\model\store\Store())->getStoreStockTypeStoreId(['store_id' => $this->store_id])['data'] ?? 0;
        if ($stock_store_id == $this->store_id) {
            $sku_filed .= ', IFNULL(sgs.stock, 0) as stock';
        } else {
            $join[] = ['store_goods_sku sgs2', 'sku.sku_id = sgs2.sku_id and sgs2.store_id=' . $stock_store_id, 'left'];
            $sku_filed .= ', IFNULL(sgs2.stock, 0) as stock';
        }
        $goods_model = new GoodsModel();
        $sku_list = $goods_model->getGoodsSkuList([['sku.goods_id', '=', $goods_id], ['sku.site_id', '=', $this->site_id]], $sku_filed, 'sku.sku_id asc', 0, 'sku', $join)['data'];
        //库存转换
        $sku_list = $goods_model->goodsStockTransform($sku_list, $this->store_id, 'store');
        return $this->response($this->success($sku_list));
    }

    /**
     * 查询sku信息
     * @return false|string
     */
    public function skuInfo()
    {
        $sku_id = $this->params['sku_id'] ?? 0;
        $sku_no = $this->params['sku_no'] ?? '';

        $condition = [
            ['sku.site_id', '=', $this->site_id],
            ['sku.is_delete', '=', 0],
        ];
        if ($sku_id) $condition[] = ['sku.sku_id', '=', $sku_id];

        // 检测是否存在称重商品插件
        if (!empty($sku_no)) {
            $condition[] = ['', 'exp', Db::raw("FIND_IN_SET('{$sku_no}', sku.sku_no)")];
        }

        $sku_filed = 'g.goods_class,sku.goods_id,sku.sku_id,sku.sku_name,sku.goods_name,sku.sku_no,sku.sku_image,sku.sku_images,sku.spec_name,sku.goods_spec_format,sku.unit,IFNULL(IF(g.is_unify_price = 1,sku.price,sgs.price), sku.price) as price,sku.cost_price,sku.pricing_type,sku.goods_state';
        $join = [
            ['goods g', 'sku.goods_id=g.goods_id', 'inner'],
            ['store_goods_sku sgs', 'sku.sku_id=sgs.sku_id and sgs.store_id=' . $this->store_id, 'inner'],
        ];
        $stock_store_id = (new \app\model\store\Store())->getStoreStockTypeStoreId(['store_id' => $this->store_id])['data'] ?? 0;
        if ($stock_store_id == $this->store_id) {
            $sku_filed .= ', IFNULL(sgs.stock, 0) as stock, IFNULL(sgs.real_stock, 0) as real_stock';
        } else {
            $join[] = ['store_goods_sku sgs2', 'sku.sku_id = sgs2.sku_id and sgs2.store_id=' . $stock_store_id, 'left'];
            $sku_filed .= ', IFNULL(sgs2.stock, 0) as stock, IFNULL(sgs2.real_stock, 0) as real_stock';
        }
        $goods_model = new GoodsModel();
        $sku_info = $goods_model->getGoodsSkuInfo($condition, $sku_filed, 'sku', $join);

        if (empty($sku_info['data']) && (strlen($sku_no) == 13 || strlen($sku_no) == 18)) {
            $plu = intval(substr($sku_no, 2, 5));
            array_pop($condition);
            $condition[] = ['sku.plu', '=', $plu];
            $sku_info = $goods_model->getGoodsSkuInfo($condition, $sku_filed, 'sku', $join);

            if (!empty($sku_info['data'])) {
                // 如果格式为 两位店号 + 五位plu码 + 五位重量 + 一位校验码
                if (strlen($sku_no) == 13 || strlen($sku_no) == 18) {
                    $weigh = intval(substr($sku_no, 7, 5));
                    $sku_info['data']['weigh'] = $sku_info['data']['pricing_type'] == 'weight' ? round($weigh / 1000, 3) : $weigh;
                }
                // 如果格式为 两位店号 + 五位plu码 + 五位重量 + 五位金额 + 一位校验码
                if (strlen($sku_no) == 18) {
                    $adjust_price = round(intval(substr($sku_no, 12, 5)) / 100, 2);
                    $sku_info['data']['goods_money'] = $adjust_price;
                }
            }
        }

        return $this->response($sku_info);
    }

    /**
     * 用于商品选择的商品
     */
    public function getGoodsListBySelect()
    {
        $page = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;
        $search_text = $this->params['search_text'] ?? '';
        $goods_ids = $this->params['goods_ids'] ?? '';
        $is_virtual = $this->params['is_virtual'] ?? '';// 是否虚拟类商品（0实物1.虚拟）
        $min_price = $this->params['min_price'] ?? '';
        $max_price = $this->params['max_price'] ?? '';
        $category_id = $this->params['category_id'] ?? '';// 商品分类id
        $promotion_type = $this->params['promotion_type'] ?? '';
        $label_id = $this->params['label_id'] ?? '';
        $select_type = $this->params['select_type'] ?? '';
        $sale_channel = $this->params['sale_channel'] ?? ''; // 销售渠道 all 线上线下销售 online 线上销售  offline线下销售
        $goods_class_all = $this->params['goods_class_all'] ?? '';
        $goods_class = $this->params['goods_class'] ?? '';
        $brand_id = $this->params['brand_id'] ?? '';
        $supplier_id = $this->params['supplier_id'] ?? '';

        $goods_model = new GoodsModel();
        $condition = [
            ['is_delete', '=', 0],
            ['goods_state', '=', 1],
            ['goods_stock', '>', 0],
            ['site_id', '=', $this->site_id]
        ];
        if(!empty($goods_class_all)){
            $goods_class_all = explode(',', $goods_class_all);
        }else{
            $goods_class_all = [ GoodsDict::real, GoodsDict::weigh, GoodsDict::service, GoodsDict::card ];
        }
        $condition[] = [ 'goods_class', 'in', $goods_class_all ];

        if (!empty($search_text)) {
            $search_text = paramFilter($search_text);
            $goods_sku_list = $goods_model->getGoodsSkuList([['sku_no', 'like', '%' . $search_text . '%']], 'goods_id')['data'];
            $goods_id_arr = array_unique(array_column($goods_sku_list, 'goods_id'));
            if (!empty($goods_id_arr)) {
                $goods_ids = join(',', $goods_id_arr);
                $condition[] = ['', 'exp', \think\facade\Db::raw("goods_name like '%{$search_text}%' or goods_id in ({$goods_ids})")];
            } else {
                $condition[] = ['goods_name', 'like', "%{$search_text}%"];
            }
        }
        if ($is_virtual !== '') {
            $condition[] = ['is_virtual', '=', $is_virtual];
        }
        if ($select_type == 'selected') {
            $condition[] = ['goods_id', 'in', $goods_ids];
        }

        if (!empty($category_id)) {
            if (!empty($goods_class) && $goods_class == GoodsDict::service) {
                $condition[] = ['service_category', 'like', '%,' . $category_id . ',%'];
            } else {
                $condition[] = ['category_id', 'like', '%,' . $category_id . ',%'];
            }
        }

        if (!empty($sale_channel)) {
            $condition[] = ['sale_channel', 'in', $sale_channel];
        }

        if (!empty($promotion_type)) {
            $condition[] = ['promotion_addon', 'like', "%{$promotion_type}%"];
        }

        if (!empty($label_id)) {
            $condition[] = ['label_id', '=', $label_id];
        }

        if ($min_price != '' && $max_price != '') {
            $condition[] = ['price', 'between', [$min_price, $max_price]];
        } elseif ($min_price != '') {
            $condition[] = ['price', '<=', $min_price];
        } elseif ($max_price != '') {
            $condition[] = ['price', '>=', $max_price];
        }
        if(!empty($goods_class)){
            $condition[] = ['goods_class', '=', $goods_class];
        }
        if(!empty($brand_id)){
            $condition[] = ['brand_id', '=', $brand_id];
        }
        if(!empty($supplier_id)){
            $condition[] = ['supplier_id', '=', $supplier_id];
        }

        $config_model = new ConfigModel();
        $sort_config = $config_model->getGoodsSort($this->site_id)['data']['value'];

        $order = 'sort ' . $sort_config['type'] . ',create_time desc';

        $field = 'goods_id,goods_name,goods_class_name,goods_image,price,goods_stock,is_virtual';
        $goods_list = $goods_model->getGoodsPageList($condition, $page, $page_size, $order, $field)['data'] ?? [];


        if (!empty($goods_list['list'])) {
            $temp_sku_list = $goods_model->getGoodsSkuList([['goods_id', 'in', array_column($goods_list['list'], 'goods_id')], ['site_id', '=', $this->site_id]], 'sku_id,sku_name,price,stock,sku_image,goods_id,goods_class_name', 'price asc')['data'] ?? [];
            $sku_column = [];
            if (!empty($temp_sku_list)) {
                foreach ($temp_sku_list as $item) {
                    $sku_column[$item['goods_id']][] = $item;
                }
            }
            foreach ($goods_list['list'] as &$v) {
                $v['sku_list'] = $sku_column[$v['goods_id']] ?? [];
            }
        }

        return $this->response($this->success($goods_list));
    }

    /**
     * 用于规格选择的
     */
    public function getSkuListBySelect()
    {
        $page = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;
        $goods_id = $this->params['goods_id'] ?? 0;
        $search = $this->params['search_text'] ?? '';
        $category_id = $this->params['category_id'] ?? '';
        $temp_store_id = $this->params[ 'temp_store_id' ] ?? 0;
        $store_id = $temp_store_id > 0 ? $temp_store_id : $this->store_id;
        $goods_class_all = $this->params['goods_class_all'] ?? '';
        $goods_class = $this->params['goods_class'] ?? '';
        $brand_id = $this->params['brand_id'] ?? '';
        $supplier_id = $this->params['supplier_id'] ?? '';
        $selected_sku_ids = $this->params['selected_sku_ids'] ?? '';
        $unselected_sku_ids = $this->params['unselected_sku_ids'] ?? '';

        $goods_model = new GoodsModel();

        $alias = 'gs';
        $join = [
            ['goods g', 'g.goods_id = gs.goods_id', 'left'],
            ['store_goods_sku sgs', 'sgs.sku_id = gs.sku_id and (sgs.store_id is null or sgs.store_id = ' . $store_id . ')', 'left'],
            ['supplier s', 'g.supplier_id = s.supplier_id', 'left'],
        ];
        $condition = [
            [ 'g.is_delete', '=', 0 ],
            [ 'g.goods_state', '=', 1 ],
            [ 'gs.site_id', '=', $this->site_id ],
            [ 'g.is_virtual', '=', 0]
        ];
        if(!$goods_class_all){
            $goods_class_all = explode(',', $goods_class_all);
        }else{
            $goods_class_all = [ GoodsDict::real, GoodsDict::weigh, GoodsDict::service, GoodsDict::card ];
        }
        $condition[] = [ 'g.goods_class', 'in', $goods_class_all ];

        if (!empty($search)) {
            $condition[] = [ 'gs.sku_name|gs.spec_name|g.goods_name|gs.sku_no', 'like', '%' . $search . '%' ];
        }
        if (!empty($goods_id)) {
            $condition[] = [ 'g.goods_id', '=', $goods_id ];
        }
        if(!empty($selected_sku_ids)){
            $condition[] = ['gs.sku_id', 'in', $selected_sku_ids];
        }
        if(!empty($unselected_sku_ids)){
            $condition[] = ['gs.sku_id', 'not in', $unselected_sku_ids];
        }
        if ($store_id > 0) {
            //查询商品支持门店(支持当前门店或全部)
            $condition[] = [ 'g.sale_store', 'like', [ '%,' . $store_id . ',%', '%all%'], 'or' ];
        }
        if (!empty($category_id)) {
            $condition[] = [ 'g.category_id', 'like', '%,' . $category_id . ',%' ];
        }
        if(!empty($goods_class)){
            $condition[] = ['g.goods_class', '=', $goods_class];
        }
        if(!empty($brand_id)){
            $condition[] = ['g.brand_id', '=', $brand_id];
        }
        if(!empty($supplier_id)){
            $condition[] = ['g.supplier_id', '=', $supplier_id];
        }

        $field = 'gs.sku_id, gs.goods_id, gs.sku_name, gs.sku_no, gs.market_price,gs.weight,gs.goods_class, gs.goods_name, gs.spec_name, gs.sku_image,gs.unit, sgs.stock,sgs.real_stock,sgs.store_id, sgs.price,sgs.cost_price,g.category_id,g.category_json,g.brand_id,g.brand_name,g.label_id,g.label_name';
        $field .= ',IFNULL(IF(g.is_unify_price = 1,gs.price,sgs.price), gs.price) as price';
        $field .= ',IFNULL(s.title,"") as supplier_name';

        $res = $goods_model->getGoodsSkuPageList($condition, $page, $page_size, 'gs.goods_id desc, gs.create_time desc, gs.sku_id desc', $field, $alias, $join);
        $res['data']['list'] = $goods_model->getCategoryNames($res['data']['list']);

        return $this->response($res);
    }


    /**
     * 设置不同门店 sku  同城配送模式 非起送 商品业务
     */
    public function setGoodsLocalRestrictions()
    {
        $goods_sku_array = isset($this->params['goods_sku_list']) ? json_decode($this->params['goods_sku_list'], true) : [];
        $model = new \app\model\goods\GoodsLocalRestrictions();
        $res = $model->setRestrictions($goods_sku_array, $this->site_id, $this->store_id);
        return $this->response($res);
    }

    /**
     * 搜索条件
     */
    public function screen()
    {
        $requires = $this->params['requires'] ?? 'all';
        if($requires == 'all') $requires = 'supplier,brand';
        $requires = explode(',', $requires);

        $res = [];
        foreach($requires as $require){
            switch($require){
                case 'supplier':
                    $res['is_install_supply'] = addon_is_exit('supply');;
                    if($res['is_install_supply']){
                        $supplier_model = new SupplierModel();
                        $res['supplier_list'] = $supplier_model->getSupplyList([['supplier_site_id', '=', $this->site_id]], 'supplier_id,title', 'supplier_id desc')['data'];
                    }
                    break;
                case 'brand':
                    $goods_brand_model = new GoodsBrandModel();
                    $res['brand_list'] = $goods_brand_model->getBrandList([['site_id', '=', $this->site_id]], 'brand_id,brand_name', 'sort asc')['data'];
                    break;
            }
        }

        return $this->response($this->success($res));
    }

    /**
     * 导出打印价格标签数据
     */
    public function exportPrintPriceTagData()
    {
        $json = $this->params['data'] ?? '';
        $data = json_decode($json, true);

        $param = [
            'site_id' => $this->site_id,
            'store_id' => $this->store_id,
            'from_type' => 'print_price_tag',
            'from_type_name' => '打印价格标签',
            'condition_desc' => [],
            'export_field' => [
                /*['field' => 'print_num', 'name' => '打印份数'],*/
                ['field' => 'goods_name', 'name' => '商品名称'],
                ['field' => 'spec_name', 'name' => '商品规格'],
                ['field' => 'sku_no', 'name' => '商品条码'],
                ['field' => 'market_price', 'name' => '划线价'],
                ['field' => 'price', 'name' => '零售价'],
                ['field' => 'unit', 'name' => '单位'],
                ['field' => 'weight', 'name' => '重量'],
                ['field' => 'category_names', 'name' => '商品分类'],
                ['field' => 'brand_name', 'name' => '品牌'],
                ['field' => 'supplier_name', 'name' => '供应商'],
                ['field' => 'label_name', 'name' => '标签'],
            ],
            'data' => $data,
        ];
        $export_model = new ExportModel();
        $res = $export_model->export($param);
        return $this->response($res);
    }
}
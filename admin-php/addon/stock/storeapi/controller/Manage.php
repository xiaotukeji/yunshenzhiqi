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

namespace addon\stock\storeapi\controller;

use addon\stock\model\stock\Document;
use addon\stock\model\stock\Stock as StockModel;
use app\dict\goods\GoodsDict;
use app\model\goods\Goods;
use app\model\goods\GoodsCategory;
use app\storeapi\controller\BaseStoreApi;

/**
 * 库存管理
 */
class Manage extends BaseStoreApi
{

    /**
     * 库存管理
     */
    public function lists()
    {
        $page = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;
        $search = $this->params['search'] ?? '';
        $category_id = $this->params['category_id'] ?? '';
        $min_stock = $this->params['min_stock'] ?? 0;
        $max_stock = $this->params['max_stock'] ?? 0;

        $store_id = $this->store_id;

        $condition = [];
        $condition[] = [ 'gs.site_id', '=', $this->site_id ];
        $condition[] = [ 'g.is_delete', '=', 0 ];

        $condition[] = [ 'g.goods_class', 'in', [ GoodsDict::real, GoodsDict::weigh ] ];
        if (!empty($search)) {
            $condition[] = [ 'gs.sku_name|sku_no', 'like', '%' . $search . '%' ];
        }

        if (!empty($category_id)) {
            $condition[] = [ 'g.category_id', 'like', '%,' . $category_id . ',%' ];
        }
        if ($min_stock > 0 && $max_stock > 0) {
            $condition[] = [ 'sgs.real_stock', 'between', [ $min_stock, $max_stock ] ];
        } else if ($min_stock > 0 && $max_stock == 0) {
            $condition[] = [ 'sgs.real_stock', '>', $min_stock ];
        } else if ($min_stock == 0 && $max_stock > 0) {
            $condition[] = [ 'sgs.real_stock', '<', $max_stock ];
        }

        $field = 'gs.stock,gs.*,g.unit';
        $join = [
            [ 'goods g', 'g.goods_id = gs.goods_id', 'left' ],
        ];

        if ($store_id > 0) {
            $join[] = [
                'store_goods_sku sgs',
                'sgs.sku_id = gs.sku_id and (sgs.store_id is null or sgs.store_id = ' . $store_id . ')',
                'left'
            ];
            $field .= ',sgs.stock, sgs.real_stock';
        }

        $goods_model = new \app\model\goods\Goods();
        $list = $goods_model->getGoodsSkuPageList($condition, $page, $page_size, 'g.create_time desc', $field, 'gs', $join);
        return $this->response($list);
    }

    /**
     * 库存流水
     */
    public function records()
    {
        $document_model = new Document();
        $sku_id = $this->params['sku_id'] ?? 0;
        $goods_id = $this->params['goods_id'] ?? 0;
        $page = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;
        $start_time = $this->params['start_time'] ?? 0;
        $end_time = $this->params['end_time'] ?? 0;
        $type = $this->params['type'] ?? '';

        $condition = [
            [ 'dg.site_id', '=', $this->site_id ],
            [ 'd.store_id', '=', $this->store_id ],
        ];
        if ($sku_id > 0) {
            $condition[] = [ 'dg.goods_sku_id', '=', $sku_id ];
        }
        if (!empty($type)) {
            $condition[] = [ 'dt.key', '=', $type ];
        }
        //注册时间
        if ($start_time != '' && $end_time != '') {
            $condition[] = [ 'dg.create_time', 'between', [ strtotime($start_time), strtotime($end_time) ] ];
        } else if ($start_time != '' && $end_time == '') {
            $condition[] = [ 'dg.create_time', '>=', strtotime($start_time) ];
        } else if ($start_time == '' && $end_time != '') {
            $condition[] = [ 'dg.create_time', '<=', strtotime($end_time) ];
        }

        if ($sku_id > 0) {
            $condition[] = [ 'dg.goods_sku_id', '=', $sku_id ];
        }
        if ($goods_id > 0) {
            $condition[] = [ 'dg.goods_id', '=', $goods_id ];
        }

        $result = $document_model->getDocumentGoodsPageList($condition, $page, $page_size, 'dg.create_time desc');
        return $this->response($result);
    }

    /**
     * 商品规格列表(仅作临时用)
     */
    public function getSkuList()
    {
        $page = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;
        $goods_id = $this->params['goods_id'] ?? 0;
        $search = $this->params['search'] ?? '';

        $temp_store_id = $this->params[ 'temp_store_id' ] ?? 0;

        $store_id = $temp_store_id > 0 ? $temp_store_id : $this->store_id;
        $stock_model = new StockModel();
        $condition = [
            [ 'gs.site_id', '=', $this->site_id ],
            [ 'g.goods_class', 'in', [ GoodsDict::real, GoodsDict::weigh ] ],
            [ 'g.is_delete', '=', 0 ]
        ];
        if (!empty($search)) {
            $condition[] = [ 'gs.sku_name|gs.spec_name|g.goods_name|gs.sku_no', 'like', '%' . $search . '%' ];
        }
        if (!empty($goods_id)) {
            $condition[] = [ 'g.goods_id', '=', $goods_id ];
        }
        if ($store_id > 0) {
            //查询商品支持门店(支持当前门店或全部)
            $condition[] = [ 'g.sale_store', 'like', [ '%,' . $store_id . ',%', '%all%'], 'or' ];
        }
        $field = 'gs.sku_id, gs.goods_id, gs.sku_name, gs.sku_no, gs.price, gs.discount_price,gs.goods_class, gs.goods_name, gs.spec_name, sgs.stock,sgs.real_stock,sgs.price,sgs.cost_price';
        $sku_list = $stock_model->getStoreGoodsSkuList($condition, $field, 'gs.create_time desc', $store_id, $page, $page_size);
        return $this->response($sku_list);
    }

    public function getDocumentType()
    {
        $document_model = new Document();
        $type_list = $document_model->getDocumentTypeList();
        return $this->response($type_list);
    }

    public function getGoodsCategory()
    {
        $goods_model = new Goods();
        $category_id_arr = $goods_model->getGoodsCategoryIds([
            [ 'is_delete', '=', 0 ],
            [ 'goods_state', '=', 1 ],
            [ 'goods_stock', '>', 0 ],
            [ 'site_id', '=', $this->site_id ],
        ])[ 'data' ];

        $goods_category_model = new GoodsCategory();
        $field = 'category_id,category_name as title,pid';
        $list = $goods_category_model->getCategoryList([
            [ 'site_id', '=', $this->site_id ],
            [ 'category_id', 'in', $category_id_arr ]
        ], $field)[ 'data' ];
        $tree['data'] = list_to_tree($list, 'category_id', 'pid', 'children', 0);
        return $this->response($tree);
    }

    public function getStoreGoods()
    {
        $page = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;
        $goods_id = $this->params['goods_id'] ?? 0;
        $search = $this->params['search_text'] ?? '';
        $category_id = $this->params['category_id'] ?? '';
        $temp_store_id = $this->params[ 'temp_store_id' ] ?? 0;
        $goods_class = $this->params['goods_class'] ?? '';
        $brand_id = $this->params['brand_id'] ?? '';
        $supplier_id = $this->params['supplier_id'] ?? '';

        $store_id = $temp_store_id > 0 ? $temp_store_id : $this->store_id;
        $stock_model = new StockModel();

        $condition = [
            [ 'g.is_delete', '=', 0 ],
            [ 'g.goods_state', '=', 1 ],
//            [ 'g.goods_stock', '>', 0 ],
            [ 'g.goods_class', 'in', [ GoodsDict::real, GoodsDict::weigh ] ],
            [ 'gs.site_id', '=', $this->site_id ],
            [ 'g.is_virtual', '=', 0]
        ];

        if (!empty($search)) {
            $condition[] = [ 'gs.sku_name|gs.spec_name|g.goods_name|gs.sku_no', 'like', '%' . $search . '%' ];
        }
        if (!empty($goods_id)) {
            $condition[] = [ 'g.goods_id', '=', $goods_id ];
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

        $field = 'gs.sku_id, gs.goods_id, gs.sku_name, gs.sku_no, gs.price, gs.discount_price,gs.goods_class, gs.goods_name, gs.spec_name, gs.sku_image,gs.unit, sgs.stock,sgs.real_stock,sgs.store_id, sgs.price,sgs.cost_price';
        $sku_list = $stock_model->getStoreGoodsSkuPage($condition, $field, 'gs.goods_id desc, gs.create_time desc, gs.sku_id desc', $store_id, $page, $page_size);
        return $this->response($sku_list);
    }
}
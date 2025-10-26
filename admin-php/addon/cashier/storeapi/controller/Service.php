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

use app\dict\goods\GoodsDict;
use app\model\goods\ServiceCategory;
use app\model\goods\Goods as GoodsModel;
use app\model\storegoods\StoreGoods as StoreGoodsModel;
use app\storeapi\controller\BaseStoreApi;
use think\facade\Db;

/**
 * Class Service
 * @package addon\cashier\storeapi\controller
 */
class Service extends BaseStoreApi
{
    /**
     * 获取商品分类的组织
     * @return false|string
     */
    public function category()
    {
        $level = $this->params[ 'level' ] ?? 1;
        $service_category_model = new ServiceCategory();
        $condition = [
            [ 'is_show', '=', 0 ],
            [ 'level', '<=', $level ],
            [ 'site_id', '=', $this->site_id ]
        ];
        $list = $service_category_model->getCategoryTree($condition, 'category_id,category_name,image,level', 'sort asc,category_id desc');

        return $this->response($list);
    }

    public function page()
    {
        $page_index = $this->params[ 'page' ] ?? 1;
        $page_size = $this->params[ 'page_size' ] ?? PAGE_LIST_ROWS;
        $goods_category = $this->params[ 'category' ] ?? 'all';
        $search_text = $this->params[ 'search_text' ] ?? '';
        $goods_state = $this->params[ 'goods_state' ] ?? 'all';
        $is_virtual = $this->params[ 'is_virtual' ] ?? 0;

        $model = new GoodsModel();
        $condition = [
            [ 'g.site_id', '=', $this->site_id ],
            [ 'g.is_delete', '=', 0 ],
            [ 'g.goods_class', '=', GoodsDict::service ],
            [ 'g.sale_store', 'like', [ '%all%', '%,' . $this->store_id . ',%' ], 'or' ],
            [ '', 'exp', Db::raw("(g.sale_channel = 'all' OR g.sale_channel = 'offline')") ]
        ];
        if ($goods_state !== 'all') {
            $condition[] = [ 'g.goods_state', '=', $goods_state ];
        }

        if ($goods_category != 'all') $condition[] = [ 'g.service_category', 'like', "%,{$goods_category},%" ];

        if ($search_text != '') {

            $goods_sku_list = $model->getGoodsSkuList([ [ 'sku_no', 'like', '%' . $search_text . '%' ] ], 'goods_id')[ 'data' ];
            $goods_id_arr = array_unique(array_column($goods_sku_list, 'goods_id'));
            if (!empty($goods_id_arr)) {
                $condition[] = [ 'g.goods_id', 'in', $goods_id_arr ];
            } else {
                $condition[] = [ 'g.goods_name', 'like', "%{$search_text}%" ];
            }

        }

        $status = $this->params[ 'status' ] ?? 1;
        if ($status !== 'all') {
            $condition[] = [ 'sg.status', '=', $status ];
        }
        $field = 'g.goods_id,g.goods_name,g.goods_class,g.introduction,g.goods_image,g.goods_state,g.sku_id,g.price,gs.discount_price,g.goods_spec_format,gs.service_length,
         IFNULL(IF(g.is_unify_price = 1,g.price,sg.price), g.price) as price, IFNULL(IF(g.is_unify_price = 1,gs.discount_price,sg.price), gs.discount_price) as discount_price,
        sg.price as store_price';

        $join = [
            [ 'goods_sku gs', 'gs.sku_id = g.sku_id', 'left' ],
            [ 'store_goods sg', 'g.goods_id=sg.goods_id and sg.store_id=' . $this->store_id, 'left' ],
            [ 'store s', 's.store_id = sg.store_id', 'left' ]
        ];
        $stock_store_id = ( new \app\model\store\Store() )->getStoreStockTypeStoreId([ 'store_id' => $this->store_id ])[ 'data' ] ?? 0;
        if ($stock_store_id == $this->store_id) {
            $field .= ', IFNULL(sg.stock, 0) as stock';
        } else {
            $join[] = [ 'store_goods sg2', 'g.goods_id=sg2.goods_id and sg2.store_id=' . $stock_store_id, 'left' ];
            $field .= ', IFNULL(sg2.stock, 0) as stock';
        }

        $data = $model->getGoodsPageList($condition, $page_index, $page_size, 'g.sort asc,g.create_time desc', $field, 'g', $join);

        return $this->response($data);
    }

    /**
     * 商品详情
     * @return false|string
     */
    public function detail()
    {
        $goods_id = $this->params[ 'goods_id' ] ?? 0;
        $goods_model = new GoodsModel();
        $field = 'g.goods_id, g.goods_name, g.introduction, g.goods_class_name, g.goods_image, g.goods_state, g.sku_id, g.price, g.unit, g.cost_price, g.category_id, g.brand_name,g.is_unify_price,
            sg.price as store_price, sg.cost_price as store_cost_price, sg.status as store_status';
        $goods_info = $goods_model->getGoodsInfo([ [ 'g.goods_id', '=', $goods_id ], [ 'g.site_id', '=', $this->site_id ] ], $field, 'g', [
            [ 'store_goods sg', 'g.goods_id=sg.goods_id and sg.store_id=' . $this->store_id, 'left' ]
        ])[ 'data' ];

        if (empty($goods_info)) return $this->response($goods_model->error(null, '商品信息缺失'));

        //查询商品规格
        $sku_filed = 'sku.sku_id,sku.sku_name,sku.sku_no,sku.price,sku.discount_price,sku.cost_price,sku.sku_image,sku.sku_images,sku.spec_name,
            sgs.price as store_stock, sgs.cost_price as store_cost_price, sgs.status as store_status';
        $join = [
            [ 'store_goods_sku sgs', 'sku.sku_id=sgs.sku_id and sgs.store_id=' . $this->store_id, 'left' ]
        ];
        $goods_info[ 'sku_list' ] = $goods_model->getGoodsSkuList([ [ 'sku.goods_id', '=', $goods_id ], [ 'sku.site_id', '=', $this->site_id ] ], $sku_filed, 'sku.sku_id asc', 0, 'sku', $join)[ 'data' ];

        return $this->response($goods_model->success($goods_info));
    }

    /**
     * 上下架
     */
    public function setStatus()
    {
        $goods_id = $this->params[ 'goods_id' ] ?? 0;
        $status = $this->params[ 'status' ] ?? 0;
        $model = new StoreGoodsModel();
        $res = $model->modifyGoodsState($goods_id, $status, $this->site_id, $this->store_id);
        return $this->response($res);
    }

    /**
     * 商品编辑
     */
    public function editGoods()
    {
        $goods_sku_array = isset($this->params[ 'goods_id' ]) ? json_decode($this->params[ 'goods_id' ]) : [];
        $model = new StoreGoodsModel();
        $res = $model->editStoreGoods($goods_sku_array, $this->site_id, $this->store_id, $this->uid);
        return $this->response($res);
    }

}
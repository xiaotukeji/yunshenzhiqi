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
use app\model\goods\Goods as GoodsModel;
use app\storeapi\controller\BaseStoreApi;
use think\facade\Db;

/**
 * 卡项控制器
 * Class Activity
 * @package addon\shop\storeapi\controller
 */
class Card extends BaseStoreApi
{

    public function page()
    {
        $page_index = $this->params[ 'page' ] ?? 1;
        $page_size = $this->params[ 'page_size' ] ?? PAGE_LIST_ROWS;
        $goods_category = $this->params[ 'category' ] ?? 'all';
        $search_text = $this->params[ 'search_text' ] ?? '';
        $goods_class = $this->params[ 'goods_class' ] ?? 'all';
        $card_type = $this->params[ 'card_type' ] ?? 'all';

        $model = new GoodsModel();
        $condition = [
            [ 'g.site_id', '=', $this->site_id ],
            [ 'g.is_delete', '=', 0 ],
            [ 'g.goods_state', '=', 1 ],
            [ 'g.sale_store', 'like', ['%all%', '%,'.$this->store_id.',%'], 'or' ],
            [ '', 'exp', Db::raw("(g.sale_channel = 'all' OR g.sale_channel = 'offline')") ]
        ];

        if ($goods_class !== 'all') {
            $condition[] = [ 'g.goods_class', '=', $goods_class ];
        }else{
            $condition[] = [ 'g.goods_class', 'in', [GoodsDict::real, GoodsDict::service, GoodsDict::card, GoodsDict::weigh] ];
        }
        if ($card_type !== 'all') {
            $condition[] = [ 'gc.card_type', '=', $card_type ];
        }
        if ($goods_category != 'all') $condition[] = [ 'g.category_id', 'like', "%,{$goods_category},%" ];
        if ($search_text != '') $condition[] = [ 'g.goods_name', 'like', "%{$search_text}%" ];
        if(addon_is_exit('store')){

            $status = $this->params[ 'status' ] ?? 1;
            if($status !== 'all'){
                $condition[] = [ 'sg.status', '=', $status ];
            }
            $field = 'g.goods_id,g.goods_name,g.introduction,g.goods_image,g.goods_state,g.sku_id,g.price,gs.discount_price,g.goods_spec_format,g.is_unify_price, 
            IFNULL(IF(g.is_unify_price = 1,g.price,sg.price), g.price) as price, IFNULL(IF(g.is_unify_price = 1,gs.discount_price,sg.price), gs.discount_price) as discount_price, 
        sg.price as store_price,gc.card_type,gc.card_type_name,gc.renew_price,gc.recharge_money,gc.common_num,gc.discount_goods_type,gc.discount,gc.validity_type,gc.validity_day,gc.validity_time';
            $join = [
                ['goods_sku gs', 'gs.sku_id = g.sku_id', 'left'],
                ['store_goods sg', 'g.goods_id=sg.goods_id and sg.store_id='.$this->store_id, 'left'],
                ['goods_card gc', 'gc.goods_id = g.goods_id', 'left'],
            ];
            $stock_store_id = (new \app\model\store\Store())->getStoreStockTypeStoreId(['store_id' => $this->store_id])['data'] ?? 0;
            if($stock_store_id == $this->store_id){
                $field .= ', IFNULL(sg.stock, 0) as stock';
            }else{
                $join[] = ['store_goods sg2', 'g.goods_id = sg2.goods_id and sg2.store_id='.$stock_store_id, 'left'];
                $field .= ', IFNULL(sg2.stock, 0) as stock';
            }
        }else{
            $field = 'g.goods_id,g.goods_name,g.introduction,g.goods_image,g.goods_state,g.sku_id,g.price,gs.discount_price,g.goods_spec_format,g.is_unify_price,gs.stock,
            gc.card_type,gc.card_type_name,gc.renew_price,gc.recharge_money,gc.common_num,gc.discount_goods_type,gc.discount,gc.validity_type,gc.validity_day,gc.validity_time';
            $join = [
                ['goods_sku gs', 'gs.sku_id = g.sku_id', 'left'],
                ['goods_card gc', 'gc.goods_id = g.goods_id', 'left'],
            ];
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
        sg.stock as store_stock, sg.price as store_price, sg.cost_price as store_cost_price, sg.status as store_status,
        gc.card_type,gc.card_type_name,gc.renew_price,gc.recharge_money,gc.common_num,gc.discount_goods_type,gc.discount,gc.validity_type,gc.validity_day,gc.validity_time';
        $join = [
            ['store_goods sg', 'g.goods_id=sg.goods_id and sg.store_id=' . $this->store_id, 'left'],
            ['goods_card gc', 'gc.goods_id = g.goods_id', 'left'],
        ];
        $goods_info = $goods_model->getGoodsInfo([ [ 'g.goods_id', '=', $goods_id ], [ 'g.site_id', '=', $this->site_id ] ], $field, 'g', $join)[ 'data' ];

        if (empty($goods_info)) return $this->response($goods_model->error(null, '商品信息缺失'));

        //查询商品规格
        $sku_filed = 'sku.sku_id,sku.sku_name,sku.sku_no,sku.price,sku.discount_price,sku.cost_price,sku.sku_image,sku.sku_images,sku.spec_name,
            sgs.price as store_price, sgs.cost_price as store_cost_price, sgs.status as store_status';
        $join = [
            ['store_goods_sku sgs', 'sku.sku_id=sgs.sku_id and sgs.store_id='.$this->store_id, 'left']
        ];
        $goods_info[ 'sku_list' ] = $goods_model->getGoodsSkuList([ [ 'sku.goods_id', '=', $goods_id ], [ 'sku.site_id', '=', $this->site_id ] ], $sku_filed, 'sku.sku_id asc', 0, 'sku', $join)[ 'data' ];

        return $this->response($goods_model->success($goods_info));
    }

}
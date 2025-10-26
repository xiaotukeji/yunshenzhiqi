<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\fenxiao\event;

use app\model\goods\Goods as GoodsModel;

/**
 * 商品营销活动信息
 */
class GoodsListPromotion
{

    /**
     * 商品营销活动信息
     * @param $param
     * @return array
     */
    public function handle($param)
    {
        if (empty($param[ 'promotion' ]) || $param[ 'promotion' ] != 'fenxiao') return [];

        $alias = 'g';
        $join = [];

        $condition = [
            [ 'g.is_delete', '=', 0 ],
            [ 'g.site_id', '=', $param[ 'site_id' ] ],
            [ 'g.is_fenxiao', '=', 1 ],
            [ 'g.goods_state', '=', 1 ]
        ];

        if (!empty($param[ 'goods_name' ])) {
            $condition[] = [ 'g.goods_name', 'like', '%' . $param[ 'goods_name' ] . '%' ];
        }
        if (!empty($param[ 'select_type' ]) && $param[ 'select_type' ] == 'selected' && isset($param[ 'goods_ids' ])) {
            $condition[] = [ 'g.goods_id', 'in', $param[ 'goods_ids' ] ];
        }
        if (!empty($param[ 'category_id' ])) {
            $condition[] = [ 'g.category_id', 'like', '%,' . $param[ 'category_id' ] . ',%' ];
        }
        if (!empty($param[ 'label_id' ])) {
            $condition[] = [ 'g.label_id', '=', $param[ 'label_id' ] ];
        }
        if (!empty($param[ 'goods_class' ])) {
            $condition[] = [ 'g.goods_class', '=', $param[ 'goods_class' ] ];
        }

        $model = new GoodsModel();
        $field = 'g.goods_id,g.goods_name,g.goods_class_name,g.goods_image,g.price,g.goods_stock,g.create_time,g.is_virtual,g.sku_id';
        $list = $model->getGoodsPageList($condition, $param[ 'page' ], $param[ 'page_size' ], 'g.create_time desc', $field, $alias, $join);
        return $list;
    }
}
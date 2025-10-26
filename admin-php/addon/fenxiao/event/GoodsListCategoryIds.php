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
 * 商品分类
 */
class GoodsListCategoryIds
{

    /**
     * 商品营销活动信息
     * @param $param
     * @return array
     */
    public function handle($param)
    {
        if (empty($param[ 'promotion' ]) || $param[ 'promotion' ] != 'fenxiao') return [];

        $condition = [
            [ 'is_delete', '=', 0 ],
            [ 'site_id', '=', $param[ 'site_id' ] ],
            [ 'is_fenxiao', '=', 1 ],
            [ 'goods_state', '=', 1 ]
        ];

        $model = new GoodsModel();
        $res = $model->getGoodsCategoryIds($condition);
        return $res;
    }
}
<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\discount\event;

use addon\discount\model\Discount;
use app\model\goods\Goods as GoodsModel;

/**
 * 商品营销活动信息
 */
class GoodsPromotion
{

    /**
     * 商品营销活动信息
     * @param $param
     * @return array
     */
    public function handle($param)
    {
        if (isset($param['goods_sku_detail'])) {
            $goods_info = $param['goods_sku_detail'];
            if (!empty($goods_info['promotion_addon'])) {
                $promotion_addon = json_decode($goods_info['promotion_addon'], true);
                if (!empty($promotion_addon['discount'])) {
                    return [
                        'promotion_type' => 'discount',
                        'promotion_name' => '限时折扣',
                        'goods_id' => $promotion_addon['discount']
                    ];
                }
            }

        } else {
            if (empty($param['goods_id'])) return [];
            $goods_model = new GoodsModel();
            $goods_info = $goods_model->getGoodsInfo([['goods_id', '=', $param['goods_id']]], 'promotion_addon')['data'];
            if (!empty($goods_info['promotion_addon'])) {
                $promotion_addon = json_decode($goods_info['promotion_addon'], true);
                if (!empty($promotion_addon['discount'])) {
                    $discount_model = new Discount();
                    $goods_detail = $discount_model->getDiscountGoodsDetail($promotion_addon['discount'], $param['goods_id'], $param['sku_id'])['data'];
                    if (!empty($goods_detail)) {
                        $goods_detail['promotion_type'] = 'discount';
                        $goods_detail['promotion_name'] = '限时折扣';
                        return $goods_detail;
                    }
                }
            }
            return [];
        }
    }
}
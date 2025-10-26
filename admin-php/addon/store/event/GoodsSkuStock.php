<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\store\event;

use addon\store\model\StoreGoodsSku;

class GoodsSkuStock
{

    public function handle($params)
    {
        //todo  优化
        $order_object = $params['order_object'];
        $store_goods_sku_model = new StoreGoodsSku();
        foreach ($params[ 'goods_list' ] as $v) {
            $condition = array (
                [ 'store_id', '=', $order_object->delivery[ 'store_id' ] ],
                [ 'sku_id', '=', $v[ 'sku_id' ] ]
            );
            $store_sku_info = model('store_goods_sku')->getInfo($condition, 'id, goods_id, stock');
            if (empty($store_sku_info)) {
                return $store_goods_sku_model->error([ 'error_code' => 11 ], '当前门店库存不足,请选择其他门店');
            }
            $store_sku_info[ 'stock' ] = numberFormat($store_sku_info[ 'stock' ]);
            if (( $store_sku_info[ 'stock' ] - $v[ 'num' ] ) < 0) {
                return $store_goods_sku_model->error([ 'error_code' => 11 ], '当前门店库存不足,请选择其他门店');
            }

        }
    }
}
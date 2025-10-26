<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\replacebuy\model;

use app\model\BaseModel;


class GoodsSku extends BaseModel
{
    /**
     * 获取商品分页列表
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     */
    public function getGoodsSkuPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'gs.create_time desc', $field = '*')
    {
        $alias = 'gs';
        $join = [
            [
                'goods g',
                'g.goods_id = gs.goods_id',
                'inner'
            ]
        ];

        $field = 'gs.*';
        $res = model('goods_sku')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        foreach ($res['list'] as $k => $v) {
            if (isset($v['stock'])) {
                $res['list'][$k]['stock'] = numberFormat($res['list'][$k]['stock']);
            }
            if (isset($v['sale_num'])) {
                $res['list'][$k]['sale_num'] = numberFormat($res['list'][$k]['sale_num']);
            }
            if (isset($v['virtual_sale'])) {
                $res['list'][$k]['virtual_sale'] = numberFormat($res['list'][$k]['virtual_sale']);
            }
            if (isset($v['real_stock'])) {
                $res['list'][$k]['real_stock'] = numberFormat($res['list'][$k]['real_stock']);
            }
        }
        return $this->success($res);
    }

}
<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\store\model;

use app\model\BaseModel;

/**
 * 门店订单与结算
 */
class Stat extends BaseModel
{

    /**
     * 统计
     * @param $params
     */
    public function getStoreAccountSum($condition, $field)
    {
        $sum = model('store')->getSum($condition, $field);
        return $this->success($sum);
    }

    /**
     * 门店订单的销售额和销售量排行
     * @param $params
     */
    public function getStoreOrderRank($params)
    {
        $site_id = $params[ 'site_id' ];
        $join = [
            [ 'order o', '(o.store_id = s.store_id and o.pay_status = 1 and o.is_delete = 0) || o.store_id is null ', 'left' ]
        ];
        $group = 's.store_id';
        $limit = 5;
        $order = $params[ 'order' ] == 'num' ? 'order_num desc' : 'order_money desc';
        $field = 'ifnull(count(o.order_id), 0) as order_num, ifnull(sum(o.order_money), 0) as order_money,s.store_name';
        $condition = array (
            [ 's.site_id', '=', $site_id ],
        );
        $list = model('store')->getList($condition, $field, $order, 's', $join, $group, $limit);
        return $this->success($list);
    }
}
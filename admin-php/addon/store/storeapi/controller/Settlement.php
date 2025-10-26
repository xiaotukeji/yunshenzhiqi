<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\store\storeapi\controller;

use addon\store\model\Settlement as SettlementModel;
use app\model\order\OrderCommon as OrderCommonModel;
use app\storeapi\controller\BaseStoreApi;

/**
 * 门店结算控制器
 */
class Settlement extends BaseStoreApi
{
    /**
     * 门店结算列表
     */
    public function records()
    {
        $model = new SettlementModel();
        $page = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;

        $condition[] = [ 'store_id', '=', $this->store_id ];

        $start_time = $this->params['start_time'] ?? '';
        $end_time = $this->params['end_time'] ?? '';
        if (!empty($start_time)) $start_time = strtotime($start_time);
        if (!empty($end_time)) $end_time = strtotime($end_time);

        if (!empty($start_time) && empty($end_time)) {
            $condition[] = [ 'start_time', '>=', $start_time ];
        } elseif (empty($start_time) && !empty($end_time)) {
            $condition[] = [ 'end_time', '<=', $end_time ];
        } elseif (!empty($start_time) && !empty($end_time)) {
            $condition[] = [ 'start_time', '>=', $start_time ];
            $condition[] = [ 'end_time', '<=', $end_time ];
        }
        $order = 'id desc';
        $field = 'id,settlement_no,site_id,site_name,store_name,order_money,shop_money,refund_platform_money,platform_money,refund_shop_money,
        refund_money,create_time,commission,is_settlement,offline_refund_money,offline_order_money,start_time,end_time';
        $list = $model->getStoreSettlementPageList($condition, $page, $page_size, $order, $field);

        return $this->response($list);
    }
}
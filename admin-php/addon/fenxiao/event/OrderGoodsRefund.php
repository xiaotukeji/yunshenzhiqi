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

use addon\fenxiao\model\FenxiaoOrder;

/**
 * 活动类型
 */
class OrderGoodsRefund
{

    /**
     * 活动类型
     * @param $data
     * @return array
     */
    public function handle($data)
    {
        $order_model = new FenxiaoOrder();
        $res = $order_model->refund($data);
        return $res;
    }
}
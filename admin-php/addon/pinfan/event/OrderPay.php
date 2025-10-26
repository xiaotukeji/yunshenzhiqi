<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\pinfan\event;

use addon\pinfan\model\PinfanOrder;

/**
 * 活动展示
 */
class OrderPay
{

    /**
     * 活动展示
     * @param $param
     * @return array|mixed
     */
    public function handle($param)
    {
        if ($param[ 'promotion_type' ] == 'pinfan') {
            $pinfan_order = new PinfanOrder();
            $res = $pinfan_order->orderPay($param);
            return $res;
        }
    }
}
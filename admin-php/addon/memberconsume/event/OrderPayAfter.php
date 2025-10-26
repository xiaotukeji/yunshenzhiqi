<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\memberconsume\event;

use addon\memberconsume\model\Consume as ConsumeModel;

/**
 * 订单支付事件
 */
class OrderPayAfter
{

    public function handle($data)
    {
        $consume_model = new ConsumeModel();
        $res = $consume_model->memberConsume([ 'out_trade_no' => $data[ 'out_trade_no' ]], $data[ 'order_id' ]);
        return $res;
    }
}
<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 */


namespace addon\cardservice\event;

use addon\cardservice\model\MemberCard;
use app\model\order\OrderCommon;

/**
 * 订单关闭后抵扣的卡项退还
 */
class OrderClose
{
    public function handle($param)
    {
        $order_info = $param;
        $order_common = new OrderCommon();
        $order_goods_list = $order_common->getOrderGoodsList([['order_id', '=', $order_info['order_id']]], 'order_goods_id,card_item_id')['data'];
        $card_refund_data = [];
        foreach($order_goods_list as $order_goods){
            if($order_info['pay_status'] == 0 && !empty($order_goods['card_item_id'])){
                $card_refund_data[] = ['type' => 'order', 'relation_id' => $order_goods['order_goods_id']];
            }
        }
        if(!empty($card_refund_data)){
            $member_card_model = new MemberCard();
            return $member_card_model->memberOncecardItemRefund($card_refund_data);
        }
    }
}
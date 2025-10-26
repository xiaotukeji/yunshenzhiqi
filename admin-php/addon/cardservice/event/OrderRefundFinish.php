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

/**
 *
 */
class OrderRefundFinish
{

    /**
     * 活动展示
     * @return array
     */
    public function handle($param)
    {
        $order_id = $param['order_info']['order_id'] ?? 0;
        $order_goods_id = $param['order_goods_info']['order_goods_id'] ?? 0;
        $member_card_model = new MemberCard();
        //两种业务
        //1、如果是卡项商品订单 退款结束后用户的卡包要作废
        //2、如果是卡包中的商品使用订单，则使用记录删除，恢复未使用状态
        $member_card_model->memberOncecardInvalid([['order_id', '=', $order_id]], 'refunded');
        $member_card_model->memberOncecardItemRefund([['type' => 'order', 'relation_id' => $order_goods_id]]);
        return $member_card_model->success();
    }
}
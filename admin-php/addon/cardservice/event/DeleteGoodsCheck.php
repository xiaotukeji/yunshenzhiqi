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

use addon\cardservice\model\MemberCard as MemberCardModel;
use app\model\order\Order as OrderModel;

class DeleteGoodsCheck
{

	public function handle($param)
	{
        if(in_array($param['field'], ['goods_id', 'sku_id'])){
            $cannot_delete_goods_list = array_merge($this->getInUseGoodsList($param), $this->getToBuyGoodsList($param));
            $cannot_delete_ids = array_unique(array_column($cannot_delete_goods_list, $param['field']));
            return [
                'reason' => '存在待付款或未兑换的卡包商品',
                'cannot_delete_ids' => $cannot_delete_ids,
            ];
        }
	}

    protected function getInUseGoodsList($param)
    {
        $alias = 'mgc';
        $join = [
            ['member_goods_card_item mgci', 'mgc.card_id = mgci.card_id', 'inner'],
        ];
        $field = 'mgci.'.$param['field'];
        $condition = [
            ['mgc.status', '=', MemberCardModel::STATUS_NORMAL],
            ['mgci.'.$param['field'], 'in', $param['ids']],
        ];
        $cannot_delete_goods_list = model('member_goods_card')->getList($condition, $field, '', $alias, $join);
        return $cannot_delete_goods_list;
    }

    protected function getToBuyGoodsList($param)
    {
        $alias = 'o';
        $join = [
            ['order_goods og', 'og.order_id = o.order_id', 'inner'],
            ['goods_card_item gci', 'gci.card_goods_id = og.goods_id', 'inner'],
        ];
        $condition = [
            ['gci.'.$param['field'], 'in', $param['ids']],
            ['o.order_status', '=', OrderModel::ORDER_CREATE],
        ];
        $field = 'gci.'.$param['field'];
        $cannot_delete_goods_list = model('order')->getList($condition, $field, '', $alias, $join);
        return $cannot_delete_goods_list;
    }
}
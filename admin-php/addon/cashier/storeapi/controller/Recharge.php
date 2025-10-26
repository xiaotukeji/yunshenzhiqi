<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 上海牛之云网络科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 */

namespace addon\cashier\storeapi\controller;

use addon\memberrecharge\model\Memberrecharge;
use addon\memberrecharge\model\MemberrechargeOrder;
use app\storeapi\controller\BaseStoreApi;

class Recharge extends BaseStoreApi
{
    /**
     * 会员充值活动
     * @return false|string
     */
    public function activity()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $member_recharge_model = new Memberrecharge();

        $list = $member_recharge_model->getMemberRechargeList([['site_id', '=', $this->site_id]]);
        return $this->response($list);
    }

    /**
     * 充值记录
     * @return false|string
     */
    public function orderPage()
    {

        $page = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;
        $search_text = $this->params['search_text'] ?? '';
        $search_mode = $this->params['search_mode'] ?? 'pay_time';
        $start_time = $this->params['start_time'] ?? '';
        $end_time = $this->params['end_time'] ?? '';

        $condition = [
            ['site_id', '=', $this->site_id],
            ['store_id', '=', $this->store_id],
            ['status', '=', 2],
        ];
        if (!empty($search_text)) {
            $condition[] = ['order_no|out_trade_no|nickname', 'like', '%' . $search_text . '%'];
        }
        if (!empty($search_mode)) {
            if (!empty($start_time) && empty($end_time)) {
                $condition[] = [$search_mode, '>=', date_to_time($start_time)];
            } elseif (empty($start_time) && !empty($end_time)) {
                $condition[] = [$search_mode, '<=', date_to_time($end_time)];
            } elseif (!empty($start_time) && !empty($end_time)) {
                $condition[] = [$search_mode, 'between', [date_to_time($start_time), date_to_time($end_time)]];
            }
        }

        $recharge_model = new MemberrechargeOrder();
        $list = $recharge_model->getMemberRechargeOrderPageList($condition, $page, $page_size, 'create_time desc');

        return $this->response($list);
    }

    /**
     * 充值详情
     * @return false|string
     */
    public function orderDetail()
    {
        $order_id = $this->params['order_id'] ?? 0;
        $recharge_model = new MemberrechargeOrder();
        $condition = [
            ['site_id', '=', $this->site_id],
            ['store_id', '=', $this->store_id],
            ['order_id', '=', $order_id],
        ];
        $detail = $recharge_model->getMemberRechargeOrderInfo($condition);
        return $this->response($detail);
    }
}

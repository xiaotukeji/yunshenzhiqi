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

use addon\cashier\model\order\CashierOrder as CashierOrderModel;
use addon\memberrecharge\model\MemberrechargeOrder;
use addon\printer\model\PrinterOrder;
use app\model\order\OrderCommon;
use app\storeapi\controller\BaseStoreApi;
use think\facade\Db;
use app\dict\order_refund\OrderRefundDict;

class Cashierorder extends BaseStoreApi
{
    /**
     * 收银订单列表
     */
    public function lists()
    {
        $page_index = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;
        $store_id = $this->store_id;
        $start_time = $this->params['start_time'] ?? '';
        $end_time = $this->params['end_time'] ?? '';
        $pay_type = $this->params['pay_type'] ?? '';
        $search_text = $this->params['search_text'] ?? '';
        $cashier_order_type = $this->params['cashier_order_type'] ?? '';
        $order_id = $this->params['order_id'] ?? 0;
        $order_type = $this->params[ 'order_type' ] ?? '';//订单类型
        $order_from = $this->params[ 'order_from' ] ?? '';//订单来源
		$order_status = $this->params[ 'order_status' ] ?? 'all';//订单状态

        $order_model = new OrderCommon();
        $condition = [
            ['site_id', '=', $this->site_id],
        ];
        $condition[] = ['store_id', '=', $store_id];
        if (!empty($start_time) && empty($end_time)) {
            $condition[] = ['create_time', '>=', date_to_time($start_time)];
        } elseif (empty($start_time) && !empty($end_time)) {
            $condition[] = ['create_time', '<=', date_to_time($end_time)];
        } elseif (!empty($start_time) && !empty($end_time)) {
            $condition[] = ['create_time', 'between', [date_to_time($start_time), date_to_time($end_time)]];
        }

        //订单状态
        if ($order_status !== 'all' && $order_status !== '') {
            if ($order_status == 'refunding') {
                $order_goods_list = $order_model->getOrderGoodsList([ [ 'refund_status', 'not in', [ OrderRefundDict::REFUND_NOT_APPLY, OrderRefundDict::REFUND_COMPLETE,OrderRefundDict::PARTIAL_REFUND ] ] ], 'order_id')[ 'data' ];
                $order_id_arr = array_unique(array_column($order_goods_list, 'order_id'));
                $condition[] = [ 'order_id', 'in', $order_id_arr ];
            }elseif ($order_status == 'refunded') {
                $order_goods_list = $order_model->getOrderGoodsList([ [ 'refund_status', 'in', [ OrderRefundDict::REFUND_COMPLETE,OrderRefundDict::PARTIAL_REFUND ] ] ], 'order_id')[ 'data' ];
                $order_id_arr = array_unique(array_column($order_goods_list, 'order_id'));
                $condition[] = [ 'order_id', 'in', $order_id_arr ];
            }else{
                $condition[] = [ 'order_status', '=', $order_status ];
            }
        }
        $order_scene = $this->params['order_scene'] ?? '';
        if ($order_scene !== 'all' && $order_scene !== '') {
            $condition[] = ['order_scene', '=', $order_scene];
        }
        //收货方式
        if ($order_type !== 'all' && $order_type !== '') {
            if($order_scene == 'online'){
                $condition[] = [ 'order_type', '=', $order_type ];
            }else{
                $condition[] = [ 'cashier_order_type', '=', $order_type ];
            }
        }
        if (!empty($cashier_order_type)) {
            $condition[] = ['cashier_order_type', '=', $cashier_order_type];
        }
        //订单来源
        if ($order_from !== 'all' && $order_from !== '') {
            $condition[] = [ 'order_from', '=', $order_from ];
        }

        //支付方式
        if ($pay_type !== 'all' && $pay_type !== '') {
            $condition[] = ['pay_type', '=', $pay_type];
        }

        if (!empty($search_text)) {
            $condition[] = ['order_no|order_name|mobile|name|buyer_message|remark', 'like', '%' . $search_text . '%'];
        }
        if ($order_id) {
            $condition[] = ['order_id', '=', $order_id];
        }

        $cashier_order = new CashierOrderModel();
        $cashier_order_type_list = $cashier_order->getCashierOrderType();
		
        $data = $order_model->getOrderPageList($condition, $page_index, $page_size, 'create_time desc')['data'];
		
        if (!empty($data['list'])) {
            foreach ($data['list'] as $k => $item) {
                $data['list'][$k]['cashier_order_type_name'] = $cashier_order_type_list[$item['cashier_order_type']] ?? '';
            }
        }

        return $this->response($this->success($data));
    }

    /**
     * 收银订单详情
     */
    public function detail()
    {
        $order_id = $this->params['order_id'] ?? '';

        $order_model = new OrderCommon();
        $detail = $order_model->getOrderDetail($order_id);
        if (empty($detail['data'])) return $this->response($this->error(null, '未获取到订单信息'));
        if ($detail['data']['site_id'] != $this->site_id) return $this->response($this->error(null, '未获取到订单信息'));
        if ($detail['data']['store_id'] != $this->store_id) return $this->response($this->error(null, '未获取到订单信息'));

        return $this->response($detail);
    }

    /**
     * 获取支付方式
     * @return false|string
     */
    public function getOrderPayType()
    {
        $order_model = new CashierOrderModel();
        $pay_type_list = $order_model->getPayType();
        return $this->response($this->success($pay_type_list));
    }

    /**
     * 删除订单
     * @return array|false|string
     */
    public function deleteOrder()
    {
        //订单关闭并删除
        $order_id = $this->params['order_id'] ?? 0;
        $order_common_model = new OrderCommon();

        //关闭检测
        $check_res = $order_common_model->activeOrderCloseCheck($order_id);
        if($check_res['code'] < 0) return $this->response($check_res);

        $close_result = $order_common_model->orderClose($order_id);
        if ($close_result['code'] < 0) {
            return $this->response($close_result);
        }
        $order_model = new CashierOrderModel();
        $condition = [
            ['site_id', '=', $this->site_id],
            ['store_id', '=', $this->store_id],
            ['order_id', '=', $this->params['order_id'] ?? 0],
//            [ 'order_status', '=', 0 ]
        ];

        $res = $order_model->deleteOrder($condition);
        return $this->response($res);
    }

    /**
     * 订单备注
     * @return false|string
     */
    public function orderRemark()
    {
        $order_id = $this->params['order_id'] ?? 0;
        $remark = $this->params['remark'] ?? '';

        $order_model = new CashierOrderModel();
        $res = $order_model->orderUpdate(['remark' => $remark], [
            ['site_id', '=', $this->site_id],
            ['store_id', '=', $this->store_id],
            ['order_id', '=', $order_id]
        ]);

        return $this->response($res);
    }

    /**
     * 打印订单小票
     */
    public function printTicket()
    {
        $order_id = $this->params['order_id'] ?? 0;
        $printer_type = $this->params['printer_type'] ?? 'order_pay'; // order_pay 支付 manual 手动
        $printer_ids = $this->params['printer_ids'] ?? 'all';

        $order_info = (new OrderCommon())->getOrderInfo([['order_id', '=', $order_id], ['store_id', '=', $this->store_id]], 'cashier_order_type')['data'];
        if (empty($order_info)) return $this->response($this->error('', '未获取到订单信息'));

        $printer_order_model = new PrinterOrder();
        // 如果是充值订单
        if ($order_info['cashier_order_type'] == 'recharge') {
            $recharge_order = (new MemberrechargeOrder())->getMemberRechargeOrderInfo([['relate_type', '=', 'order'], ['relate_id', '=', $order_id]], 'order_id')['data'];
            if (empty($recharge_order)) return $this->response($this->error('', '未获取到充值订单信息'));
            $res = $printer_order_model->printer([
                'order_id' => $recharge_order['order_id'],
                'type' => 'recharge',
                'printer_ids' => $printer_ids,
            ]);
        } else {
            $res = $printer_order_model->printer([
                'order_id' => $order_id,
                'type' => 'goodsorder',
                'printer_type' => $printer_type,
                'printer_ids' => $printer_ids,
            ]);
        }
        return $this->response($res);
    }

    /**
     * 获取订单信息
     */
    public function getOrderInfo()
    {
        $order_model = new OrderCommon();
        $condition = [
            ['site_id', '=', $this->site_id],
            ['store_id', '=', $this->store_id],
            ['order_id', '=', $this->params['order_id'] ?? 0]
        ];

        $res = $order_model->getOrderInfo($condition);
        return $this->response($res);
    }

    /**
     * 订单调价
     * @return mixed
     */
    public function adjustPrice()
    {
        $order_id = $this->params['order_id'] ?? 0;
        $adjust_money = $this->params['adjust_money'] ?? 0;
        $delivery_money = $this->params['delivery_money'] ?? 0;
        $order_common_model = new OrderCommon();
        $result = $order_common_model->orderAdjustMoney($order_id, $adjust_money, $delivery_money);
        return $this->response($result);
    }
}
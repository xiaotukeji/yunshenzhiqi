<?php

/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 上海牛之云网络科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\cashier\model\order;


use app\dict\order_refund\OrderRefundDict;
use app\model\BaseModel;
use app\model\order\OrderCommon;
use app\model\order\OrderRefund;
use Exception;

/**
 * 收银订单
 *
 * @author Administrator
 *
 */
class CashierOrderRefund extends BaseModel
{

    public function getRefundApplyData($params)
    {
        $store_id = $params['store_id'];
        $site_id = $params['site_id'];
        $order_id = $params['order_id'];
        $order_refund_model = new OrderRefund();
        $refund_array = empty($params['refund_array']) ? [] : json_decode($params['refund_array'], true);
        if (empty($refund_array))
            return $this->error([], '请选择要退款的订单项！');

        $refund_list = [];
        foreach ($refund_array as $k => $refund_v) {
            $item = $order_refund_model->getOrderGoodsRefundInfo($refund_v, $site_id, $store_id)['data'] ?? [];
            if (!empty($item)) {
                $order_info = $item['order_info'];
                $refund_list[] = $item;
            }
        }
        if (empty($refund_list))
            return $this->error([], '请选择要退款的订单项！');

        $refund_data = [];
        $refund_transfer_type = $this->getRefundTransferType($order_info);
        $refund_data['refund_transfer_type'] = $refund_transfer_type;
        $refund_data['refund_list'] = $refund_list;
        return $this->success($refund_data);

    }

    /**
     * 退款转账方式
     * @param array $params
     * @return array
     */
    public function getRefundTransferType($params = [])
    {
        $list = [
            '2' => ['name' => '线下退款', 'desc' => '与客户协商一致, 在线下以现金、微信或支付宝等形式退款给客户'],
        ];
        $list['1'] = ['name' => '原路退款', 'desc' => '与客户协商一致，原路退款给客户'];
        return $list;
    }

    public function refund($params)
    {
        //参数
        $order_id = $params['order_id'];
        $store_id = $params['store_id'];
        $site_id = $params['site_id'];
        $refund_transfer_type = $params['refund_transfer_type'];
        $refund_array = $params['refund_array'];
        $refund_remark = $params['refund_remark'];

        //订单基本检测
        $order_common_model = new OrderCommon();
        $order_condition = [
            ['order_id', '=', $order_id],
            ['site_id', '=', $site_id],
            ['store_id', '=', $store_id],
        ];
        $order_info = $order_common_model->getOrderInfo($order_condition)['data'] ?? [];
        if (empty($order_info)){
            return $this->error([], '订单不存在！');
        }
        if($order_info['is_settlement'] == 1 && $refund_transfer_type == OrderRefundDict::back){
            return $this->error([], '订单已结算，不支持原路退款！');
        }

        model('order_goods')->startTrans();
        try {
            $order_refund_model = new OrderRefund();
            foreach ($refund_array as $order_goods_id => $refund_money) {
                $res = $order_refund_model->shopActiveRefund([
                    'order_goods_id' => $order_goods_id,
                    'shop_active_refund_money' => $refund_money['refund_money'],
                    'shop_active_refund_remark' => $refund_remark,
                    'shop_active_refund_money_type' => $refund_transfer_type,
                    'user_info' => $params['operator'],
                    'refund_status' => $refund_money['refund_status'],
                    'is_refund_stock'=>$refund_money['is_refund_stock'] ?? 1, //新增是否返还库存
                    'refund_stock_num'=>$refund_money['refund_stock_num'] ?? 0 //返还库存数量
                ]);
                if($res['code'] < 0){
                    model('order_goods')->rollback();
                    return $res;
                }
            }
            model('order_goods')->commit();
            return $this->success();
        } catch ( Exception $e ) {
            model('order_goods')->rollback();
            return $this->error('', $e->getMessage());
        }
    }
}
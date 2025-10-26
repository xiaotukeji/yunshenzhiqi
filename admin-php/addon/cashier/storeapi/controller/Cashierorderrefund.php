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


use app\dict\order_refund\OrderRefundDict;
use app\storeapi\controller\BaseStoreApi;
use app\model\order\OrderRefund as OrderRefundModel;
use addon\cashier\model\order\CashierOrderRefund as CashierOrderRefundModel;

class Cashierorderrefund extends BaseStoreApi
{

    /**
     * 商品计算
     * @return false|string
     */
    public function getRefundApplyData()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $order_refund_model = new CashierOrderRefundModel();
        $params = [
            'site_id' => $this->site_id,
            'store_id' => $this->store_id,
            'order_id' => $this->params[ 'order_id' ] ?? 0,
            'refund_array' => $this->params[ 'refund_array' ] ?? '{}',// ['order_goods_id1','order_goods_id2','order_goods_id2']
        ];
        $res = $order_refund_model->getRefundApplyData($params);
        return $this->response($res);
    }

    public function refund()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $order_refund_model = new CashierOrderRefundModel();
        $data = [
            'site_id' => $this->site_id,
            'store_id' => $this->store_id,
            'order_id' => $this->params[ 'order_id' ] ?? 0,
            'refund_transfer_type' => $this->params[ 'refund_transfer_type' ] ?? '',
            'refund_array' => empty($this->params[ 'refund_array' ]) ? [] : json_decode($this->params[ 'refund_array' ], true),// {'order_goods_id1':{'refund_money':10}},
            'refund_reason' => $this->params[ 'refund_reason' ] ?? '',
            'refund_remark' => $this->params[ 'refund_remark' ] ?? '',
            'operator' => $this->user_info,
        ];
        $res = $order_refund_model->refund($data);
        return $this->response($res);
    }

    /**
     * 为维权列表
     * @return false|string
     */
    public function lists()
    {
        $page_index = $this->params[ 'page' ] ?? 1;
        $search = $this->params[ 'search' ] ?? '';
        $page_size = $this->params[ 'page_size' ] ?? PAGE_LIST_ROWS;
        $order_refund_model = new OrderRefundModel();

        $condition = [
            [ 'nop.site_id', '=', $this->site_id ],
            [ 'no.store_id', '=', $this->store_id ],
            [ 'nop.refund_status', '<>', OrderRefundDict::REFUND_NOT_APPLY ],
        ];

        //商品名称
        if (!empty($search)) {
            $condition[] = [ 'nop.sku_name|no.order_no', 'like', '%' . $search . '%' ];
        }
        $list = $order_refund_model->getRefundOrderGoodsPageList($condition, $page_index, $page_size, 'nop.refund_action_time desc');
        return $this->response($list);
    }

    /**
     * 详情
     * @return false|string
     */
    public function detail()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $order_goods_id = $this->params[ 'order_goods_id' ] ?? 0;
        $order_refund_model = new OrderRefundModel();
        $detail = $order_refund_model->getRefundDetail($order_goods_id, $this->site_id, $this->store_id);
        return $this->response($detail);
    }

    /**
     * 同意维权
     * @return false|string
     */
    public function agree()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $order_goods_id = $this->params[ 'order_goods_id' ] ?? 0;

        $order_refund_model = new OrderRefundModel();
        $data = [
            'order_goods_id' => $order_goods_id
        ];
        $res = $order_refund_model->orderRefundConfirm($data, $this->user_info);
        return $this->response($res);
    }

    /**
     * 拒绝维权
     * @return false|string
     */
    public function refuse()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $order_goods_id = $this->params[ 'order_goods_id' ] ?? 0;
        $refund_refuse_reason = $this->params[ 'refund_refuse_reason' ] ?? '';

        $order_refund_model = new OrderRefundModel();
        $data = [
            'order_goods_id' => $order_goods_id,
            'refund_refuse_reason' => $refund_refuse_reason
        ];
        $log_data = [
            'uid' => $this->user_info[ 'uid' ],
            'nick_name' => $this->user_info[ 'username' ],
            'action' => '商家拒绝了维权',
            'action_way' => 2
        ];
        $res = $order_refund_model->orderRefundRefuse($data, $this->user_info, $refund_refuse_reason, $log_data);
        return $this->response($res);
    }

    /**
     * 关闭维权
     * @return false|string
     */
    public function close()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $order_goods_id = $this->params[ 'order_goods_id' ] ?? 0;

        $order_refund_model = new OrderRefundModel();
        $res = $order_refund_model->orderRefundClose($order_goods_id, $this->site_id, $this->user_info);
        return $this->response($res);
    }

    /**
     * 获取订单项退款信息
     * @return false|string
     */
    public function refundInfo()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $order_goods_id = $this->params[ 'order_goods_id' ] ?? 0;
        $order_refund_model = new OrderRefundModel();
        $res = $order_refund_model->getOrderGoodsRefundInfo($order_goods_id, $this->site_id);
        return $this->response($res);
    }

    /**
     * 维权通过
     * @return false|string
     */
    public function complete()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $order_goods_id = $this->params[ 'order_goods_id' ] ?? 0;
        $refund_money_type = $this->params[ 'refund_money_type' ] ?? '';
        $shop_refund_remark = $this->params[ 'shop_refund_remark' ] ?? '';
        $refund_real_money = $this->params[ 'refund_real_money' ] ?? 0;
        $is_deposit_back = $this->params[ 'is_deposit_back' ] ?? 1;

        $order_refund_model = new OrderRefundModel();
        $data = [
            'order_goods_id' => $order_goods_id,
            'refund_money_type' => $refund_money_type,
            'shop_refund_remark' => $shop_refund_remark,
            'refund_real_money' => $refund_real_money,
            'is_deposit_back' => $is_deposit_back
        ];
        $log_data = [
            'uid' => $this->user_info[ 'uid' ],
            'nick_name' => $this->user_info[ 'username' ],
            'action' => '商家对维权进行了转账，维权结束',
            'action_way' => 2
        ];
        $res = $order_refund_model->orderRefundFinish($data, $this->user_info, $log_data);
        return $this->response($res);
    }

    /**
     * 维权收货
     * @return false|string
     */
    public function receive()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $order_goods_id = $this->params[ 'order_goods_id' ] ?? 0;
        $is_refund_stock = $this->params[ 'is_refund_stock' ] ?? 0; // 是否入库

        $order_refund_model = new OrderRefundModel();
        $data = [
            'order_goods_id' => $order_goods_id,
            'is_refund_stock' => $is_refund_stock
        ];
        $res = $order_refund_model->orderRefundTakeDelivery($data, $this->user_info);
        return $this->response($res);
    }

}
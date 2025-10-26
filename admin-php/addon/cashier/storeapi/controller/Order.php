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

use app\model\express\ExpressCompany;
use app\model\express\ExpressDeliver;
use app\model\order\LocalOrder as LocalOrderModel;
use app\model\order\Order as OrderModel;
use app\model\order\OrderCommon;
use app\model\order\OrderLog;
use app\model\verify\Verify;
use app\storeapi\controller\BaseStoreApi;
use think\facade\Config;

/**
 * 订单控制器
 * Class Order
 * @package addon\cashier\storeapi\controller
 */
class Order extends BaseStoreApi
{
    /**
     * 订单筛选条件
     */
    public function condition()
    {
        $data = [];

        //商城订单筛选数据
        $order_common_model = new OrderCommon();
        //订单类型
        $order_type_list = $order_common_model->getOrderTypeStatusList();
        $temp_order_type_list = [];
        foreach(['all', 1,2,3] as $type){
            $temp_order_type_list[] = $order_type_list[$type];
        }
        $order_type_list = $temp_order_type_list;
        unset($temp_order_type_list);
        $data[ 'order_type_list' ] = $order_type_list;
        //订单来源
        $order_from = Config::get("app_type");
        $data[ 'order_from_list' ] = $order_from;
        //支付方式
        $pay_type = $order_common_model->getPayType();
        $data[ 'pay_type_list' ] = $pay_type;

        //收银订单筛选数据
        $cashier_order_model = new \addon\cashier\model\order\CashierOrder();
        //收银台订单类型
        $data[ 'cashier_order_type_list' ] = $cashier_order_model->cashier_order_type;
        //收银台支付方式
        $data[ 'cashier_pay_type_list' ] = $cashier_order_model->pay_type;
        //收银台订单状态
        $data[ 'cashier_order_status_list' ] = $cashier_order_model->order_status;
        $data[ 'cashier_order_status_list' ]['refunded'] = ['status' => 'refunding', 'name' => '已退款'];

        return $this->response($this->success($data));
    }

    /**
     * 列表
     * @return false|string
     */
    public function lists()
    {
        $order_common_model = new OrderModel();

        $page = $this->params[ 'page' ] ?? 1;
        $page_size = $this->params[ 'page_size' ] ?? PAGE_LIST_ROWS;
        $search_text = $this->params[ 'search_text' ] ?? '';//关键词
        $order_status = $this->params[ 'order_status' ] ?? 'all';//订单状态
        $start_time = $this->params[ 'start_time' ] ?? '';//开始时间
        $end_time = $this->params[ 'end_time' ] ?? '';//结束时间
        $trade_type = $this->params[ 'trade_type' ] ?? '';//订单类型
        $order_from = $this->params[ 'order_from' ] ?? '';//订单来源
        $pay_type = $this->params[ 'pay_type' ] ?? '';//支付方式

        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'store_id', '=', $this->store_id ],
            [ 'is_delete', '=', 0 ],
            [ 'order_type', '=', 'o2o' ],
        ];

        //订单状态
        if ($order_status != 'all' && !empty($order_status)) {
            $condition[] = [ 'order_status', '=', $order_status ];
        }

        //订单时间
        if (!empty($start_time) && empty($end_time)) {
            $condition[] = [ 'create_time', '>=', date_to_time($start_time) ];
        } elseif (empty($start_time) && !empty($end_time)) {
            $condition[] = [ 'create_time', '<=', date_to_time($end_time) ];
        } elseif (!empty($start_time) && !empty($end_time)) {
            $condition[] = [ 'create_time', 'between', [ date_to_time($start_time), date_to_time($end_time) ] ];
        }
        //订单内容
        if ($search_text != '') {
            $condition[] = [ 'order_no|out_trade_no|order_name|nickname', 'like', '%' . $search_text . '%' ];
        }

        //收货方式
        if ($trade_type != '') {
            $condition[] = [ 'trade_type', '=', $trade_type ];
        }
        //订单来源
        if ($order_from != '') {
            $condition[] = [ 'order_from', '=', $order_from ];
        }
        //订单支付
        if ($pay_type != '') {
            $condition[] = [ 'pay_type', '=', $pay_type ];
        }

        $order = 'create_time desc';
        $field = '*';
        $list = $order_common_model->getOrderPageList($condition, $page, $page_size, $order, $field);
		$list['c'] = $condition;
        return $this->response($list);
    }

    /**
     * 获取支付方式
     */
    public function getOrderPayType()
    {
        $order_common_model = new OrderModel();
        $pay_type_list = $order_common_model->getPayType();
        return $this->response($this->success($pay_type_list));
    }

    /**
     * 获取订单来源
     */
    public function getOrderFromType()
    {
        $order_common_model = new OrderModel();
        $list = $order_common_model->getOrderFromList([ 'order_scene' => 'cashier' ]);
        return $this->response($this->success($list));
    }

    /**
     * 获取订单信息
     */
    public function info()
    {
        $order_id = $this->params[ 'order_id' ] ?? 0;

        //订单信息
        $order_common_model = new OrderModel();
        $condition = [
            [ 'order_id', '=', $order_id ],
            [ 'site_id', '=', $this->site_id ],
            [ 'store_id', '=', $this->store_id ],
        ];
        $res = $order_common_model->getOrderInfo($condition);

        return $this->response($res);
    }

    /**
     * 订单详情
     */
    public function detail()
    {
        $order_id = $this->params[ 'order_id' ] ?? 0;

        //订单详情
        $order_common_model = new OrderModel();
        $order_detail = $order_common_model->getOrderDetail($order_id)[ 'data' ];
        if (empty($order_detail)) return $this->response($this->error(null, '无法获取订单数据'));

        //查询订单日志
        $order_log_model = new OrderLog();
        $log_condition = [
            [ 'order_id', '=', $order_id ],
        ];
        $order_detail[ 'order_log_list' ] = OrderLog::getOrderLogList($log_condition, '*', 'action_time desc,id desc', $order_log_model)[ 'data' ];

        return $this->response($this->success($order_detail));
    }

    /**
     * 订单调价
     * @return false|string
     */
    public function adjustPrice()
    {
        $order_id = $this->params[ 'order_id' ] ?? 0;
        $adjust_money = $this->params[ 'adjust_money' ] ?? 0;
        $shipping_money = $this->params[ 'shipping_money' ] ?? 0;
        $order_common_model = new OrderModel();
        $result = $order_common_model->orderAdjustMoney($order_id, $adjust_money, $shipping_money);
        return $this->response($result);
    }

    /**
     * 订单关闭
     * @return false|string
     */
    public function close()
    {
        $order_id = $this->params[ 'order_id' ] ?? 0;

        $order_common_model = new OrderModel();

        //关闭检测
        $check_res = $order_common_model->activeOrderCloseCheck($order_id);
        if($check_res['code'] < 0) return $this->response($check_res);

        $log_data = [
            'uid' => $this->user_info[ 'uid' ],
            'nick_name' => $this->user_info[ 'username' ],
            'action_way' => 2
        ];
        $result = $order_common_model->orderClose($order_id, $log_data, '商家关闭了订单');
        return $this->response($result);
    }

    /**
     * 订单删除
     */
    public function delete()
    {
        $order_id = $this->params[ 'order_id' ] ?? 0;
        $order_common_model = new OrderModel();
        $result = $order_common_model->orderDelete($order_id, $this->site_id);
        return $this->response($result);
    }

    /**
     * 同城配送发货
     * @return false|string
     */
    public function localDelivery()
    {
        $order_id = $this->params[ 'order_id' ] ?? 0;
        $deliverer = $this->params[ 'deliverer' ] ?? '';
        $deliverer_mobile = $this->params[ 'deliverer_mobile' ] ?? '';
        $local_order_model = new LocalOrderModel();
        $data = [
            'order_id' => $order_id,
            'deliverer' => $deliverer,
            'deliverer_mobile' => $deliverer_mobile,
            'site_id' => $this->site_id,
            'store_id' => $this->store_id
        ];
        $result = $local_order_model->orderGoodsDelivery($data);
        return $this->response($result);
    }

    /**
     * 门店提货
     * @return false|string
     */
    public function storeDelivery()
    {
        $order_id = $this->params[ 'order_id' ] ?? 0;

        $order_common_model = new OrderCommon();
        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'store_id', '=', $this->store_id ],
            [ 'order_id', '=', $order_id ]
        ];
        $order_info = $order_common_model->getOrderInfo($condition, 'delivery_code')[ 'data' ] ?? [];
        if (empty($order_info)) return $this->response($this->error('', '订单不存在'));

        $verify_code = $order_info[ 'delivery_code' ];
        $info = [
            'verifier_id' => $this->uid,
            'verifier_name' => $this->user_info[ 'username' ],
            'verify_from' => 'shop',
            'store_id' => $this->store_id
        ];
        $verify_model = new Verify();
        $result = $verify_model->verify($info, $verify_code);

        return $this->response($result);
    }

    /**
     * 快递发货
     * @return false|string
     */
    public function expressDelivery()
    {
        $order_model = new OrderModel();
        $data = [
            'type' => 'manual',//发货方式（手动发货、电子面单）
            'order_goods_ids' => $this->params[ 'order_goods_ids' ] ?? '',//商品id
            'express_company_id' => $this->params[ 'express_company_id' ] ?? 0,//物流公司
            'delivery_no' => $this->params[ 'delivery_no' ] ?? '',//快递单号
            'order_id' => $this->params[ 'order_id' ] ?? 0,//订单id
            'delivery_type' => $this->params[ 'delivery_type' ] ?? 0,//是否需要物流
            'site_id' => $this->site_id,
            'store_id' => $this->store_id,
            'template_id' => 0,//电子面单模板id
            'user_info' => $this->user_info
        ];
        $log_data = [
            'uid' => $this->user_info[ 'uid' ],
            'nick_name' => $this->user_info[ 'username' ],
            'action' => '商家对订单进行了发货',
            'action_way' => 2,
        ];
        $result = $order_model->orderGoodsDelivery($data, 1, $log_data);
        return $this->response($result);
    }

    /**
     * 获取门店配送员
     */
    public function deliverList()
    {
        $deliver_model = new ExpressDeliver();
        $list = $deliver_model->getDeliverLists([ [ 'site_id', '=', $this->site_id ], [ 'store_id', '=', $this->store_id ] ], 'deliver_id,deliver_name,deliver_mobile');
        return $this->response($list);
    }

    /**
     * 获取物流公司
     * @return false|string
     */
    public function expressCompany()
    {
        $express_company_model = new ExpressCompany();
        //店铺物流公司
        $result = $express_company_model->getExpressCompanyList([ ['site_id', '=', $this->site_id ] ]);
        return $this->response($result);
    }
}
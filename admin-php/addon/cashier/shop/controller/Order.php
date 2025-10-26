<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\cashier\shop\controller;

use addon\cashier\model\order\CashierOrder as OrderModel;
use app\dict\order_refund\OrderRefundDict;
use app\model\order\Config as ConfigModel;
use app\model\order\OrderCommon as OrderCommonModel;
use app\model\store\Store;
use app\model\web\Config as WebConfig;
use app\shop\controller\BaseShop;
use think\App;
use think\facade\Db;

/**
 * 订单
 * Class Order
 * @package addon\cashier\shop\controller
 */
class Order extends BaseShop
{

    public function __construct(App $app = null)
    {
        $this->replace = [
            'CASHIER_CSS' => __ROOT__ . '/addon/cashier/shop/view/public/css',
            'CASHIER_JS' => __ROOT__ . '/addon/cashier/shop/view/public/js',
            'CASHIER_IMG' => __ROOT__ . '/addon/cashier/shop/view/public/img',
        ];
        parent::__construct($app);
    }

    /**
     *订单列表
     */
    public function lists()
    {
        $order_label_list = [
            'order_no' => '订单号',
            'out_trade_no' => '交易流水号',
            'remark' => '订单备注',
            'name' => '收货人姓名',
            'order_name' => '商品名称',
            'mobile' => '收货人电话',
            'nick_name' => '会员昵称',
            'sku_no' => '商品编码',
        ];

        $order_model = new OrderModel();
        $order_status = input('order_status', '');//订单状态
        $order_name = input('order_name', '');
        $pay_type = input('pay_type', '');
        $order_from = input('order_from', '');
        $start_time = input('start_time', '');
        $end_time = input('end_time', '');
        $delivery_start_time = input('delivery_start_time', '');
        $delivery_end_time = input('delivery_end_time', '');
        $order_label = !empty($order_label_list[input('order_label')]) ? input('order_label') : '';
        $search_text = input('search', '');
        $promotion_type = input('promotion_type', '');//订单类型
        $order_type = input('order_type', 'all');//营销类型
        $is_verify = input('is_verify', 'all');
        $cashier_order_type = input('cashier_order_type', 'all');
        $store_id = input('store_id', '');

        $cashier_order_type_list = $order_model->cashier_order_type;
        $order_common_model = new OrderCommonModel();
        if (request()->isJson()) {
            $page_index = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $condition = [
                ['a.site_id', '=', $this->site_id],
                ['a.is_delete', '=', 0],
                ['a.order_scene', '=', 'cashier'],
//                [ 'a.pay_status', '=', 1 ]
            ];
            //订单状态
            if ($order_status != '') {
                if ($order_status == 'refunding') {
                    $order_goods_list = $order_common_model->getOrderGoodsList([['refund_status', 'not in', [OrderRefundDict::REFUND_NOT_APPLY, OrderRefundDict::REFUND_COMPLETE]]], 'order_id')['data'];
                    $order_id_arr = array_unique(array_column($order_goods_list, 'order_id'));
                    $condition[] = ['a.order_id', 'in', $order_id_arr];
                } else {
                    $condition[] = ['a.order_status', '=', $order_status];
                }
            }
            if ($is_verify != 'all') {
                $join[] = [
                    'verify v',
                    'v.verify_code = a.virtual_code',
                    'left'
                ];
                $condition[] = ['v.is_verify', '=', $is_verify];
            }

            if ($store_id != '') {
                $condition[] = ['a.store_id', '=', $store_id];
            }
            //订单内容 模糊查询
            if ($order_name != '') {
                $condition[] = ['a.order_name', 'like', '%' . $order_name . '%'];
            }
            //订单来源
            if ($order_from != '') {
                $condition[] = ['a.order_from', '=', $order_from];
            }
            //订单支付
            if ($pay_type != '') {
                $condition[] = ['a.pay_type', '=', $pay_type];
            }
            //订单类型
            if ($order_type != 'all') {
                $condition[] = ['a.order_type', '=', $order_type];
            }

            if ($cashier_order_type != 'all') {
                $condition[] = ['a.cashier_order_type', '=', $cashier_order_type];
            }
            //营销类型
            if ($promotion_type != '') {
                if ($promotion_type == 'empty') {
                    $condition[] = ['a.promotion_type', '=', ''];
                } else {
                    $condition[] = ['a.promotion_type', '=', $promotion_type];
                }
            }
            if (!empty($start_time) && empty($end_time)) {
                $condition[] = ['a.create_time', '>=', date_to_time($start_time)];
            } elseif (empty($start_time) && !empty($end_time)) {
                $condition[] = ['a.create_time', '<=', date_to_time($end_time)];
            } elseif (!empty($start_time) && !empty($end_time)) {
                $condition[] = ['a.create_time', 'between', [date_to_time($start_time), date_to_time($end_time)]];
            }

            if (!empty($delivery_start_time) && empty($delivery_end_time)) {
                $condition[] = ['a.delivery_end_time', '>=', date_to_time($delivery_start_time)];
            } elseif (empty($delivery_start_time) && !empty($delivery_end_time)) {
                $condition[] = ['a.delivery_start_time', '<=', date_to_time($delivery_end_time)];
            } elseif (!empty($delivery_start_time) && !empty($delivery_end_time)) {
                $condition[] = [ '', 'exp', Db::raw("NOT(a.delivery_start_time > ".date_to_time($delivery_end_time)." or a.delivery_end_time < ".date_to_time($delivery_start_time).")") ];
            }

            if ($search_text != '') {
                switch ($order_label) {
                    case 'nick_name':
                        $join[] = [
                            'member m',
                            'm.member_id = a.member_id',
                            'left'
                        ];
                        $condition[] = ['m.nickname', 'like', '%' . $search_text . '%'];
                        break;
                    case 'sku_no':
                        $order_goods_list = $order_common_model->getOrderGoodsList([['sku_no', 'like', '%' . $search_text . '%']], 'order_id')['data'];
                        $order_id_arr = array_unique(array_column($order_goods_list, 'order_id'));
                        $condition[] = ['a.order_id', 'in', $order_id_arr];
                        break;
                    default:
                        $condition[] = ['a.' . $order_label, 'like', '%' . $search_text . '%'];
                }
            }

            $field = 'a.*,s.store_name';
            $order = 'a.create_time desc';
            $alias = 'a';
            $join[] = [
                'store s',
                's.store_id = a.store_id',
                'left'
            ];
            $list = $order_common_model->getOrderPageList($condition, $page_index, $page_size, $order, $field, $alias, $join);
            if (!empty($list['data']['list'])) {
                foreach ($list['data']['list'] as $k => $v) {
                    $list['data']['list'][$k]['cashier_order_type_name'] = $cashier_order_type_list[$v['cashier_order_type']];
                }
            }
            $list['data']['order_status'] = $order_status;

            $total_pay_money = $order_common_model->getOrderSum($condition, 'a.pay_money', $alias, $join)['data'];
            $list['data']['total_pay_money'] = $total_pay_money;

            return $list;
        } else {

            $order_type_list = $order_common_model->getOrderTypeStatusList();
            $this->assign('order_type_list', $order_type_list);
            $this->assign('order_label_list', $order_label_list);

            $this->assign('order_status_list', $order_type_list[1]['status']);//订单状态
            //订单来源 (支持端口)
            $this->assign('order_from_list', $order_common_model->getOrderFromList());

            $pay_type = $order_common_model->getPayType(['order_type' => 5]);
            $this->assign('pay_type_list', $pay_type);

            $this->assign('order_status', $order_status);
            $this->assign('cashier_order_type_list', $cashier_order_type_list);
            $this->assign('http_type', get_http_type());

            $config_model = new ConfigModel();
            $order_config = $config_model->getOrderEventTimeConfig($this->site_id, $this->app_module)['data']['value'] ?? [];
            $this->assign('order_config', $order_config);

            $config_model = new WebConfig();
            $mp_config = $config_model->getMapConfig($this->site_id);
            $this->assign('tencent_map_key', $mp_config['data']['value']['tencent_map_key']);

            $store_list = (new Store())->getStoreList([['site_id', '=', $this->site_id]], 'store_name,store_id')['data'];
            $this->assign('store_list', $store_list);

            return $this->fetch('order/lists');
        }

    }

    /**
     * 快递订单详情
     */
    public function detail()
    {
        $order_id = input('order_id', 0);
        $order_common_model = new OrderCommonModel();
        $order_detail = $order_common_model->getOrderDetail($order_id)['data'];

        if (empty($order_detail)) $this->error('未获取到订单数据', href_url('shop/order/lists'));

        $this->assign('order_detail', $order_detail);

        $this->assign('http_type', get_http_type());

        $config_model = new WebConfig();
        $mp_config = $config_model->getMapConfig($this->site_id);
        $this->assign('tencent_map_key', $mp_config['data']['value']['tencent_map_key']);

        return $this->fetch('order/detail');
    }
}

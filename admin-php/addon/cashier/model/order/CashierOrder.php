<?php

/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\cashier\model\order;


use addon\memberrecharge\model\Memberrecharge;
use addon\memberrecharge\model\MemberrechargeOrder;
use app\model\member\Member as MemberModel;
use app\model\order\OrderCommon;
use app\model\order\OrderCreate;
use app\model\order\OrderLog;
use app\model\order\OrderStock;
use app\model\system\Pay;

/**
 * 订单创建(收银订单)
 *
 * @author Administrator
 *
 */
class CashierOrder extends OrderCommon
{
    //待付款
    public const ORDER_CREATE = 0;

    // 订单已支付
    public const ORDER_PAY = 1;

    // 订单已完成
    public const ORDER_COMPLETE = 10;

    // 订单已关闭
    public const ORDER_CLOSE = -1;

    // 订单类型
    public $order_type = 5;

    // 订单来源
    public $order_from_list = [ 'cashier' => [ 'name' => '收银台' ] ];
    public $order_status = [
        self::ORDER_CREATE => [
            'status' => self::ORDER_CREATE,
            'name' => '待支付',
            'is_allow_refund' => 0,
            'icon' => 'public/uniapp/order/order-icon.png',
            'action' => [
                [
                    'action' => 'orderClose',
                    'title' => '关闭订单',
                    'color' => ''
                ]
            ],
            'member_action' => [
                [
                    'action' => 'orderClose',
                    'title' => '关闭订单',
                    'color' => ''
                ],

            ],
            'color' => ''
        ],
        self::ORDER_COMPLETE => [
            'status' => self::ORDER_COMPLETE,
            'name' => '已完成',
            'is_allow_refund' => 1,
            'icon' => 'public/uniapp/order/order-icon-received.png',
            'action' => [
            ],
            'member_action' => [

            ],
            'color' => ''
        ],
        self::ORDER_CLOSE => [
            'status' => self::ORDER_CLOSE,
            'name' => '已关闭',
            'is_allow_refund' => 0,
            'icon' => 'public/uniapp/order/order-icon-close.png',
            'action' => [

            ],
            'member_action' => [

            ],
            'color' => ''
        ],
    ];

    public $pay_type = [
        'cash' => '现金支付',
        'BALANCE' => '余额支付',
        'own_wechatpay' => '个人微信',
        'own_alipay' => '个人支付宝',
        'own_pos' => '个人pos刷卡',
        'ONLINE_PAY' => '在线支付',
    ];

    public $cashier_order_type = [
        'goods' => '消费',
        'card' => '售卡',
        'recharge' => '充值',
    ];

    //todo  切勿调用,占位
    public $delivery_status_list = [
        0 => '待发货',
        1 => '已发货',
        2 => '已收货'
    ];

    public function getPayType($params = [])
    {
        return $this->pay_type;
    }

    public function getCashierOrderType()
    {
        return $this->cashier_order_type;
    }

    public function refund($params)
    {
        if ($params[ 'is_refund_stock' ] == 1) {
            $order_stock_model = new OrderStock();
            $order_stock_model->incOrderStock($params);
        }
        return $this->success();
    }

    public function orderDetail($order_info)
    {
        return [];
    }

    /**
     * 订单完成
     * @param $params
     * @return array
     */
    public function complete($params)
    {
        $site_id = $params[ 'site_id' ];
        $order_id = $params[ 'order_id' ];
        $out_trade_no = $params['out_trade_no'];
        $cashier_order_model = new CashierOrder();
        $cashier_data = [
            'order_status' => 10,
            'finish_time' => time(),
            'order_status_name' => $cashier_order_model->order_status[ 10 ][ 'name' ],
            'order_status_action' => json_encode($cashier_order_model->order_status[ 10 ], JSON_UNESCAPED_UNICODE)
        ];
        $cashier_condition = [
            [ 'order_id', '=', $order_id ],
        ];
        model('order')->update($cashier_data, $cashier_condition);

        //订单支付完成后针对充值要增加充值订单支付
        if ($params[ 'cashier_order_type' ] == 'recharge') {
            $recharge_order_model = new MemberrechargeOrder();
            $recharge_model = new Memberrecharge();
            $member_model = new MemberModel();
            $pay = new Pay();

            $order_goods_list = model('order_goods')->getList([ [ 'order_id', '=', $params[ 'order_id' ] ] ]);
            foreach ($order_goods_list as $k => $v) {
                $recharge_id = $v[ 'sku_id' ];
                $goods_money = $v[ 'goods_money' ];

                //获取用户头像
                $member_info = $member_model->getMemberInfo([ [ 'member_id', '=', $params[ 'member_id' ] ] ], 'headimg,nickname')[ 'data' ];

                //获取套餐信息
                if ($recharge_id > 0) {
                    //套餐字段
                    $field = 'recharge_id,recharge_name,cover_img,face_value,buy_price,point,growth,coupon_id';
                    $recharge_info = $recharge_model->getMemberRechargeInfo([ [ 'recharge_id', '=', $recharge_id ] ], $field)[ 'data' ];
                    if (empty($recharge_info)) {
                        return $this->error('', '无效的充值套餐');
                    }
                } else {
                    $recharge_info = array (
                        "recharge_id" => 0,
                        "recharge_name" => '自定义面额充值',
                        "cover_img" => '',
                        "face_value" => $goods_money,
                        "buy_price" => $goods_money,
                        "point" => 0,
                        "growth" => 0,
                        "coupon_id" => 0,
                    );
                }

                $order_no = (new OrderCreate())->createOrderNo();
                $data = [
                    'recharge_id' => $recharge_id,//套餐id
                    'face_value' => $recharge_info['face_value'],//自定义充值面额
                    'member_id' => $params[ 'member_id' ],
                    'order_from' => $params[ 'order_from' ],
                    'order_from_name' => $params[ 'order_from_name' ],
                    'site_id' => $site_id,
                    'relate_id' => $params[ 'order_id' ],
                    'relate_type' => 'order',
                    'store_id' => $params[ 'store_id' ],
                    'order_no' => $order_no,
                    'out_trade_no' => $out_trade_no,
                    'recharge_name' => $recharge_info[ 'recharge_name' ],
                    'cover_img' => $recharge_info[ 'cover_img' ],
                    'buy_price' => $recharge_info[ 'buy_price' ],
                    'pay_money' => $recharge_info[ 'buy_price' ],
                    'point' => $recharge_info[ 'point' ],
                    'growth' => $recharge_info[ 'growth' ],
                    'coupon_id' => $recharge_info[ 'coupon_id' ],
                    'status' => 1,
                    'create_time' => time(),
                    'member_img' => $member_info[ 'headimg' ],
                    'nickname' => $member_info[ 'nickname' ],
                ];
                $recharge_order_model->addMemberRechargeOrder($data);

                //充值订单支付
                $result = $recharge_order_model->orderPay([
                    'out_trade_no' => $out_trade_no,
                    'pay_type' => $params['pay_type'],
                ]);
                if($result['code'] < 0){
                    return $result;
                }
            }
        }

        $log_data = [
            'order_id' => $order_id,
            'action' => 'complete',
            'site_id' => $site_id,
            'is_auto' => 1
        ];
        (new OrderLog())->addLog($log_data);
        return $this->success();

    }

    public function orderPay()
    {

    }
}
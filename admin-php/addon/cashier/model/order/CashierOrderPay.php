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


use addon\cardservice\model\MemberCard;
use addon\coupon\model\MemberCoupon;
use addon\memberconsume\model\Consume;
use addon\store\model\settlement\OrderSettlement;
use app\dict\member_account\AccountDict;
use app\model\BaseModel;
use app\model\member\Member;
use app\model\member\MemberAccount;
use app\model\order\OrderCommon;
use app\model\order\OrderLog;
use app\model\order\OrderRefund;
use app\model\order\OrderStock;
use app\model\store\Stat;
use app\model\system\Pay;
use Exception;
use think\facade\Log;
use think\facade\Queue;
use addon\cashier\model\Push as PushModel;

/**
 * 收银订单
 */
class CashierOrderPay extends BaseModel
{

    /**
     * 创建支付单据
     * @param $params
     * @return array
     */
    public function createPay($params)
    {
        $out_trade_no = $params['out_trade_no'];
        $cache = $this->getCache($out_trade_no)['data'] ?? [];
        $promotion = $cache['promotion'] ?? [];
        $member_id = $cache['member_id'] ?? 0;
        $site_id = $params['site_id'];
        $params['member_id'] = $member_id;
        $params['promotion'] = $promotion;
        $calculate_model = new CashierOrderCalculate();
        $calculate_result = $calculate_model->calculate($params);
        if ($calculate_result['code'] < 0) {
            return $calculate_result;
        }
        $calculate = $calculate_result['data'];
        $pay_model = new Pay();
        $pay_info = $pay_model->getPayInfo($out_trade_no)['data'] ?? [];
        if (empty($pay_info)) {
            return $this->error('', '未获取到支付信息！');
        }
        $pay_money = $pay_info['pay_money'];
        $surplus_money = $calculate['surplus_money'];
        if ($pay_money != $surplus_money || $pay_info['pay_status'] == -1) {
            $pay_result = $pay_model->rewritePay($out_trade_no, $surplus_money);
            if ($pay_result['code'] < 0) return $pay_result;
            $new_out_trade_no = $pay_result['data'];
            //如果金额与上次一样的话就不需要生成新的支付
            // 生成整体付费支付单据
//            $pay_model->addPay($site_id, $out_trade_no, 'ONLINE_PAY', $calculate[ 'order_name' ], $calculate[ 'order_name' ], $surplus_money, '', 'CashierOrderPayNotify', '');
            $this->setCache($new_out_trade_no, ['promotion' => $promotion, 'member_id' => $calculate['member_id'] ?? 0]);
            model('order')->update(
                ['out_trade_no' => $new_out_trade_no],
                [
                    ['out_trade_no', '=', $out_trade_no]
                ]
            );
            $out_trade_no = $new_out_trade_no;
        }
        return $this->success($out_trade_no);
    }

    public function getCache($out_trade_no)
    {
        $pay_model = new Pay();
        $pay_info = $pay_model->getPayInfo($out_trade_no)['data'] ?? [];
        $cache = !empty($pay_info['pay_json']) ? json_decode($pay_info['pay_json'], true) : [];
//        $cache = Cache::get('cashier_cache_' . $out_trade_no);
        return $this->success($cache);
    }

    public function setCache($out_trade_no, $json)
    {
        $pay_model = new Pay();
        $data = [
            'pay_json' => json_encode($json)
        ];
        $condition = [
            ['out_trade_no', '=', $out_trade_no]
        ];
        $pay_model->edit($data, $condition);
        //写入单据表
//        Cache::set('cashier_cache_' . $out_trade_no, $json);
        return $this->success();
    }

    /**
     * 去支付
     * @param $params
     * @return array
     */
    public function doPay($params)
    {
        $out_trade_no = $params['out_trade_no'];
        $cache = $this->getCache($out_trade_no)['data'] ?? [];
        $promotion = $cache['promotion'] ?? [];
        $member_id = $cache['member_id'] ?? 0;
        $condition = [
            ['out_trade_no', '=', $out_trade_no]
        ];
        $info = model('order')->getInfo($condition);
        $pay_type = $params['pay_type'];
        $calculate_params = [
            'site_id' => $info['site_id'],//站点id
            'out_trade_no' => $out_trade_no,
            'store_id' => $info['store_id'],
            'online_type' => $params['online_type'] ?? $pay_type,
            'pay_type' => $pay_type,
            'member_id' => $member_id,
        ];
        if (!empty($promotion)) {
            $calculate_params['promotion'] = $promotion;
        }
        return $this->confirm($calculate_params);
    }

    /**
     * 确认支付,生成订单
     * @param $params
     * @return array
     */
    public function confirm($params)
    {
        try {
            $calculate_model = new CashierOrderCalculate();
            $result = $calculate_model->calculate($params);
            if ($result['code'] < 0)
                return $result;
            $calculate = $result['data'];
            $is_bind_member = $calculate['is_bind_member'] ?? 0;//是否要绑定会员
            if ($is_bind_member > 0) {
                //订单绑定会员,修改订单的价格商品金额
                $this->bingMember($calculate, $calculate['member_id']);
            }
            $surplus_money = $calculate['surplus_money'];
            if ($surplus_money <= 0) {
                if(isset($calculate['is_use_balance']) && $calculate['is_use_balance'] == 1){
                    $calculate['pay_type'] = 'BALANCE';
                }
                $result = $this->orderPay($calculate);
                if ($result['code'] < 0)
                    return $result;
            } else {
                return $this->error([], '当前收银订单未支付！');
            }
            return $this->success();
        } catch ( Exception $e ) {
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 绑定会员
     * @param $cashier_order_info
     * @param $member_id
     * @return array
     */
    public function bingMember($cashier_order_info, $member_id)
    {
        $site_id = $cashier_order_info['site_id'];
        //重新计算订单
        $order_id = $cashier_order_info['order_id'];
        $goods_list = $cashier_order_info['goods_list'];
        foreach ($goods_list as $k => $v) {
            $item_order_goods_id = $v['order_goods_id'];
            $price = $v['price'];
            $item_goods_money = $v['goods_money'];
            $item_real_goods_money = $v['real_goods_money'];
            model('order_goods')->update(
                [
                    'price' => $price,
                    'goods_money' => $item_goods_money,
                    'real_goods_money' => $item_real_goods_money,
                    'member_id' => $member_id
                ],
                [
                    ['order_goods_id', '=', $item_order_goods_id],
                    ['site_id', '=', $site_id]
                ]
            );
        }
        $goods_money = $cashier_order_info['goods_money'];
        $real_goods_money = $cashier_order_info['real_goods_money'];
        $order_money = $cashier_order_info['order_money'];
        $pay_money = $cashier_order_info['pay_money'];
        model('order')->update(
            [
                'goods_money' => $goods_money,
                'real_goods_money' => $real_goods_money,
                'order_money' => $order_money,
                'pay_money' => $pay_money,
                'member_id' => $member_id
            ],
            [
                ['order_id', '=', $order_id],
                ['site_id', '=', $site_id]
            ]
        );
        return $this->success();
    }

    /**
     * 订单线上支付
     * @param $calculate
     * @return array
     */
    public function orderPay($calculate)
    {
        $out_trade_no = $calculate['out_trade_no'];
        $cashier_condition = [
            ['out_trade_no', '=', $out_trade_no],
        ];
        $cashier_order_info = model('order')->getInfo($cashier_condition, '*');
        if ($cashier_order_info['order_status'] != 0) return $this->error([], '订单未处于待支付状态！');

        model('order')->startTrans();
        try {
            $cashier_order_info['goods_num'] = numberFormat($cashier_order_info['goods_num']);
            $pay_type = empty($calculate['pay_type']) ? 'ONLINE_PAY' : $calculate['pay_type'];

            $order_common_model = new OrderCommon();
            $pat_type_list = $order_common_model->getPayType(['order_type' => $cashier_order_info['order_type']]);
            $pay_model = new Pay();
            $pay_model->edit(['pay_type' => $pay_type, 'pay_time' => time(), 'pay_status' => 2, 'pay_money' => $calculate['pay_money']], [['out_trade_no', '=', $out_trade_no]]);

            $cashier_order_type = $cashier_order_info['cashier_order_type'];
            switch ($cashier_order_type) {
                case 'goods':
                    $is_enable_refund = 1;
                    break;
                case 'recharge':
                    $is_enable_refund = 0;
                    break;
                case 'card':
                    $is_enable_refund = 1;
                    break;
            }

            $data = [
                'pay_money' => $calculate['pay_money'],
                'pay_type' => $pay_type,
                'pay_type_name' => $pat_type_list[$pay_type] ?? '',
                'pay_status' => 1,
                'pay_time' => time(),
                'reduction' => $calculate['reduction'] ?? 0,
                'round_money' => $calculate['round_money'],
                'order_money' => $calculate['order_money'],
                'promotion_money' => $calculate['promotion_money'] ?? 0,
                'balance_money' => $calculate['total_balance'],//总余额  todo  暂时先不区分可提现和不可提现余额
                'coupon_id' => $calculate['coupon_id'],
                'coupon_money' => $calculate['coupon_money'] ?? 0,
                'is_enable_refund' => $is_enable_refund,
                'point_money' => $calculate['point_money'] ?? 0,
                'point' => $calculate['point'] ?? 0
            ];
            $member_id = $params['member_id'] ?? 0;
            if ($member_id > 0) {
                $data['member_id'] = $member_id;
            }
            $out_trade_no = $calculate['out_trade_no'];
            $condition = [
                ['out_trade_no', '=', $out_trade_no],
            ];

            model('order')->update($data, $condition);

            $goods_list = $calculate['goods_list'];
            foreach ($goods_list as $v) {
                model('order_goods')->update(
                    [
                        'real_goods_money' => $v['real_goods_money'],
                        'coupon_money' => $v['coupon_money']
                    ],
                    [
                        ['order_goods_id', '=', $v['order_goods_id']]
                    ]
                );
            }
            //扣除抵扣项
            $offset_result = $this->deductOffset($calculate);
            if ($offset_result['code'] < 0) {
                model('order')->rollback();
                return $offset_result;
            }

            // 用户线上支付
            if (in_array($pay_type, ['wechatpay', 'alipay'])) {
                $log_data = [
                    'order_id' => $cashier_order_info['order_id'],
                    'action' => 'pay',
                    'site_id' => $cashier_order_info['site_id'],
                    'member_id' => $cashier_order_info['member_id'],
                ];
            } else {
                // 收银员收款
                $log_data = [
                    'order_id' => $cashier_order_info['order_id'],
                    'action' => 'pay',
                    'site_id' => $cashier_order_info['site_id'],
                    'member_id' => $cashier_order_info['member_id'],
                    'operater' => [
                        'uid' => $cashier_order_info['cashier_operator_id']
                    ]
                ];
            }
            (new OrderLog())->addLog($log_data);
            $cashier_order_model = new CashierOrder();
            $result = $cashier_order_model->complete(array_merge($cashier_order_info, [
                'pay_type' => $pay_type,
                'pay_type_name' => $pat_type_list[$pay_type] ?? ''
            ]));
            if($result['code'] < 0){
                model('order')->rollback();
                return $result;
            }

            model('order')->commit();
            Queue::push('addon\cashier\job\order\CashierOrderPayAfter', $cashier_order_info);
            return $this->success();
        } catch ( Exception $e ) {
            model('order')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 抵扣扣除
     * @param $calculate_data
     * @return array
     */
    public function deductOffset($calculate_data)
    {
        //使用优惠券
        $coupon_result = $this->useCoupon($calculate_data);
        if ($coupon_result['code'] < 0) {
            return $coupon_result;
        }
        //扣除积分
        $point_result = $this->deductPoint($calculate_data);
        if ($point_result['code'] < 0) {
            return $point_result;
        }
        //抵扣余额
        $balance_result = $this->deductBalance($calculate_data);
        if ($balance_result['code'] < 0) {
            return $balance_result;
        }
        return $this->success();
    }

    public function useCoupon($calculate_data)
    {
        $coupon_id = $calculate_data['coupon_id'] ?? 0;
        //只有开单和卡项可以用优惠券
        $cashier_order_type = $calculate_data['cashier_order_type'];
        if (in_array($cashier_order_type, ['goods', 'card'])) {
            if (!empty($coupon_id)) {
                $order_id = $calculate_data['order_id'];
                $member_coupon_model = new MemberCoupon();
                //扣除优惠券
                $result = $member_coupon_model->useMemberCoupon($coupon_id, $calculate_data['member_id'], $order_id);
                if ($result['code'] < 0) {
                    return $result;
                }
            }
        }
        return $this->success();
    }

    /**
     * 扣除积分
     * @param $calculate_data
     * @return array
     */
    public function deductPoint($calculate_data)
    {
        $member_id = $calculate_data['member_id'];
        $site_id = $calculate_data['site_id'];
        $point = $calculate_data['point'] ?? 0;
        $order_id = $calculate_data['order_id'] ?? 0;
        $point_money = $calculate_data['point_money'] ?? 0;
        $order_type = $calculate_data['cashier_order_type'];
        if (in_array($order_type, ['goods', 'card'])) {
            if ($point > 0) {
                $is_use_point = $calculate_data['is_use_point'] ?? 0;
                if ($is_use_point > 0) {
                    $from_type = 'order';
                    $relate_tag = '积分抵扣';
                    $remark = '收银订单积分抵扣,扣除积分:' . $point;
                    $member_account_model = new MemberAccount();
                    $result = $member_account_model->addMemberAccount($site_id, $member_id, AccountDict::point, -$point, $from_type, $relate_tag, $remark, $order_id);
                    if ($result['code'] < 0) {
                        return $result;
                    }
                }
            }
        }
        return $this->success();
    }

    /**
     * 扣除余额
     * @param $calculate_data
     * @return array
     */
    public function deductBalance($calculate_data)
    {
        $member_id = $calculate_data['member_id'];
        $site_id = $calculate_data['site_id'];
        $cashier_order_type = $calculate_data['cashier_order_type'];
        $order_id = $calculate_data['order_id'] ?? 0;
        $is_use_balance = $calculate_data['is_use_balance'] ?? 0;
        if (in_array($cashier_order_type, ['goods', 'card'])) {
            //判断是否已经扣除过余额
            if ($is_use_balance > 0) {
                $balance_money = $calculate_data['total_balance'] ?? 0;
                if ($balance_money > 0) {
                    $member_model = new Member();
                    $member_account_model = new MemberAccount();
                    $member_condition = [
                        ['member_id', '=', $member_id],
                        ['site_id', '=', $site_id]
                    ];
                    $member_info = $member_model->getMemberInfo($member_condition)['data'] ?? [];
                    $member_balance = $member_info['balance'];//储值余额
                    $member_balance_money = $member_info['balance_money'];//现金余额
                    $order_data = [];
                    //优先扣除可不提现余额
                    $surplus = $balance_money;
                    $temp_balance = $member_balance;
                    if ($member_balance > $surplus) {
                        $temp_balance = $surplus;
                    }
                    $surplus -= $temp_balance;
                    $from_type = 'order';
                    $relate_tag = '余额抵扣';
                    $remark = '订单名称：' . $calculate_data['order_name'] . '，订单编号：' . $calculate_data['out_trade_no'] ?? '，订单金额:' . $calculate_data['order_money'] . '，订单余额抵扣，扣除储值余额:' . $temp_balance;
                    $remark = '收银订单余额抵扣';
                    if ($temp_balance > 0) {
                        $result = $member_account_model->addMemberAccount($site_id, $member_id, AccountDict::balance, -$temp_balance, $from_type, $relate_tag, $remark, $order_id);
                        if ($result['code'] < 0) {
                            return $result;
                        }
                        $order_data['balance'] = $temp_balance;
                    }
                    //扣除现金余额
                    $temp_balance = $member_balance_money;


                    if ($member_balance_money > $surplus) {
                        $temp_balance = $surplus;
                    }
                    $from_type = 'order';
                    $relate_tag = '余额抵扣';
                    $remark = '订单名称：' . $calculate_data['order_name'] . '，订单编号：' . $calculate_data['out_trade_no'] . '，订单金额:' . $calculate_data['order_money'] . '，订单余额抵扣，扣除储值余额:' . $temp_balance;
                    $remark = '收银订单余额抵扣';
                    if ($temp_balance > 0) {
                        $result = $member_account_model->addMemberAccount($site_id, $member_id, AccountDict::balance_money, -$temp_balance, $from_type, $relate_tag, $remark, $order_id);
                        if ($result['code'] < 0) {
                            return $result;
                        }
                        $order_data['balance_money'] = $temp_balance;
                    }
                    if (!empty($order_data)) {
                        $order_condition = [
                            ['order_id', '=', $order_id]
                        ];
                        model('order')->update($order_data, $order_condition);
                    }
                }
            }
        }
        return $this->success();
    }

    /**
     * 订单创建后续
     * @param $cashier_order_info
     */
    public function cashierOrderPayAfter($cashier_order_info)
    {
        $order_id = $cashier_order_info['order_id'];
        //模拟订单支付后操作钩子
        if (addon_is_exit('memberconsume')) {
            $consume_model = new Consume();
            $consume_model->memberConsume(['out_trade_no' => $cashier_order_info['out_trade_no'], 'status' => 'pay'], $order_id);
        }
        //模拟订单完成后操作钩子
        if (addon_is_exit('memberconsume')) {
            $consume_model = new Consume();
            $consume_model->memberConsume(['out_trade_no' => $cashier_order_info['out_trade_no'], 'status' => 'complete'], $order_id);
        }
        //收银台的订单因部分不可抗力不能进行公共的订单完成事件,所以主动调用门店订单分成结算
        if (addon_is_exit('store')) {
            (new  OrderSettlement())->orderSettlementAccount(['order_id' => $order_id]);
        }
        $this->create($cashier_order_info);
        //自动库存转换
        if(in_array($cashier_order_info['cashier_order_type'], ['goods'])){
            $order_goods_list = model('order_goods')->getList([['order_id', '=', $cashier_order_info['order_id']]], 'order_goods_id,sku_id,store_id');
        }
        //订单支付后消息推送
        (new PushModel())->orderPay($cashier_order_info);
        return true;
    }

    /**
     * 订单创建
     * @param $params
     * @return array
     */
    public function create($params)
    {
        Log::write('收银订单创建' . json_encode($params));
        $out_trade_no = $params['out_trade_no'];
        $cashier_condition = [
            ['out_trade_no', '=', $out_trade_no],
        ];

        $cashier_order_info = model('order')->getInfo($cashier_condition, '*');
        model('order')->startTrans();
        try {
            $cashier_order_info['goods_num'] = numberFormat($cashier_order_info['goods_num']);
            $cashier_order_id = $cashier_order_info['order_id'];
            $store_id = $cashier_order_info['store_id'];
            //拆分订单
            $order_goods_condition = [
                ['order_id', '=', $cashier_order_id],
            ];
            $order_goods_list = model('order_goods')->getList($order_goods_condition);
            foreach ($order_goods_list as $k => $v) {
                $order_goods_list[$k]['num'] = numberFormat($v['num']);
            }
            $site_id = $cashier_order_info['site_id'];
            $cashier_order_data = $cashier_order_info;
            $cashier_order_data['goods_list'] = $order_goods_list;
            $member_id = $cashier_order_info['member_id'];
            $cashier_order_type = $cashier_order_info['cashier_order_type'];
            //根据商品类型合并订单组
            $cashier_order_refund_model = new CashierOrderRefund();
            $order_refund_model = new OrderRefund();
            $store_stat = new Stat();
            $stat_data = [];
            switch ($cashier_order_type) {
                case 'goods':
                    $cashier_trade_model = new CashierTrade();
                    $member_card_model = new MemberCard();
                    $item_list = [];
                    foreach ($order_goods_list as $k => $v) {
                        $card_item_id = $v['card_item_id'];
                        if ($card_item_id > 0) {
                            $num = $v['num'];
                            $order_goods_id = $v['order_goods_id'];
                            $type = 'order';
                            //订单使用此卡
                            $item_params = [
                                'item_id' => $card_item_id,
                                'num' => $num,
                                'type' => $type,
                                'relation_id' => $order_goods_id,
                                'store_id' => $store_id
                            ];
                            $item_list[] = $item_params;
                        }
                    }

                    if (!empty($item_list)) {
                        $user_card_result = $member_card_model->cardUse($item_list);
                        if ($user_card_result['code'] < 0) {
                            Log::write('使用此卡错误' . json_encode($user_card_result));
                            $cashier_order_data['refund_reason'] = $user_card_result['message'];

                            $refund_result = $order_refund_model->activeRefund($cashier_order_id, '', '订单退款');
                            //todo  理论上退款转账不会导致退失败
                            if ($refund_result['code'] < 0) {
                                Log::write('收银订单主动退款失败（卡项）：' . json_encode($refund_result));
                                model('order')->rollback();
                                return $refund_result;
                            }
                            model('order')->commit();
                            return $user_card_result;
                        }
                    }
                    foreach ($order_goods_list as $k => $v) {
                        $cashier_result = $cashier_trade_model->toSend($v, $cashier_order_info);
                        if ($cashier_result['code'] < 0) {
                            $cashier_order_data['refund_reason'] = $cashier_result['message'];
                            Log::write('收银订单退款失败：' . json_encode($cashier_result));

                            $refund_result = $order_refund_model->activeRefund($cashier_order_id, '', '订单退款');

                            //todo  理论上退款转账不会导致退失败
                            if ($refund_result['code'] < 0) {
                                Log::write('收银订单主动退款失败：' . json_encode($refund_result));
                                model('order')->rollback();
                                return $refund_result;
                            }
                            model('order')->commit();
                            return $cashier_result;
                        }
                    }
                    // 统计数据
                    $stat_data['billing_count'] = 1;
                    $stat_data['billing_money'] = $cashier_order_info['pay_money'];
                    break;
                case 'recharge':

                    // 统计数据
                    $stat_data['recharge_count'] = 1;
                    $stat_data['recharge_money'] = $cashier_order_info['pay_money'];
                    break;
                case 'card':
                    $cashier_trade_model = new CashierTrade();
                    foreach ($order_goods_list as $k => $v) {
                        $cashier_result = $cashier_trade_model->toSend($v, $cashier_order_info);
                        if ($cashier_result['code'] < 0) {
//                            $refund_res = $cashier_order_refund_model->orderRefund(['order_id' => $order_id]);
//                            model('order')->commit();
//                            return $refund_res;
                            $refund_result = $order_refund_model->activeRefund($cashier_order_id, '', '订单退款');
                            //todo  理论上退款转账不会导致退失败
                            if ($refund_result['code'] < 0) {
                                Log::write('收银订单主动退款失败（CARD）：' . json_encode($refund_result));
                                model('order')->rollback();
                                return $refund_result;
                            }
                            model('order')->commit();
                            return $cashier_result;
                        }
                    }
                    // 统计数据
                    $stat_data['buycard_count'] = 1;
                    $stat_data['buycard_money'] = $cashier_order_info['pay_money'];
                    break;
            }

            $order_stock_model = new OrderStock();
            $stock_params = [
                'store_id' => $store_id,
                'goods_sku_list' => $order_goods_list,
                'site_id' => $cashier_order_info['site_id'],
                'user_info' => [
                    'uid' => $cashier_order_info['cashier_operator_id'],
                    'username' => $cashier_order_info['cashier_operator_name'],
                ],
                'is_out_stock' => 1
            ];
            $result = $order_stock_model->decOrderStock($stock_params);
            if ($result['code'] < 0) {
//                $refund_result = $order_refund_model->activeRefund($cashier_order_id, '', '订单退款');
//                //todo  理论上退款转账不会导致退失败
//                if ($refund_result['code'] < 0) {
//                    Log::write('收银订单扣除库存失败（CARD）：' . json_encode($refund_result));
//                    model('order')->rollback();
//                    return $result;
//                }
//                model('order')->commit();
//                return $result;
            }
            // 统计下单人数
            if ($member_id) {
                $count = model('order')->getCount([['member_id', '=', $member_id], ['pay_status', '=', 1], ['store_id', '=', $store_id]]);
                if ($count == 1) $stat_data['order_member_count'] = 1;
                if ($cashier_order_info['balance_money']) $stat_data['balance_money'] = $cashier_order_info['balance_money'];
            }
            // 添加统计数据
            if (!empty($stat_data)) {
                $stat_data['site_id'] = $site_id;
                $stat_data['store_id'] = $store_id;
                $store_stat->addStoreStat($stat_data);
            }
            model('order')->commit();
            return $this->success();
        } catch ( Exception $e ) {
            model('order')->rollback();
            Log::write('收银单支付完成错误：' . $e->getMessage() . $e->getFile() . $e->getLine());
            return $this->error('', $e->getMessage());
        }
    }
}

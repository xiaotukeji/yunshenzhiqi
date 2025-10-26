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


use addon\cashier\model\Cashier;
use addon\coupon\dict\CouponDict;
use addon\coupon\model\Coupon;
use addon\pointcash\model\Config as PointConfig;
use app\model\BaseModel;
use app\model\goods\GoodsCategory as GoodsCategoryModel;
use app\model\member\Member;
use app\model\order\OrderCreateTool;


/**
 * 收银订单计算
 * Class CashierOrderCalculate
 * @package addon\cashier\model\order
 */
class CashierOrderCalculate extends BaseModel
{
    use OrderCreateTool;

    /**
     * 计算
     * @param $params
     * @return array
     */
    public function calculate($params)
    {
        $site_id = $params['site_id'];
        $out_trade_no = $params['out_trade_no'];
        $member_id = $params['member_id'] ?? 0;
        if ($member_id == 0) {
            unset($params['member_id']);
        }
        $this->member_id = $member_id;
        $this->initMemberAccount();
        $condition = [
            ['out_trade_no', '=', $out_trade_no]
        ];
        $cashier_order_info = model('order')->getInfo($condition);
        if (empty($cashier_order_info))
            return $this->error();

        $order_id = $cashier_order_info['order_id'];
        $alias = 'og';
        $join = [
            ['goods_sku gs', 'og.sku_id = gs.sku_id', 'inner'],
            ['goods g', 'og.goods_id = g.goods_id', 'inner'],
        ];
        $order_goods_condition = [
            ['og.order_id', '=', $order_id]
        ];
        $field = 'og.*, gs.is_consume_discount,gs.discount_config,gs.member_price,gs.discount_method, g.category_id';
        $cashier_order_goods_list = model('order_goods')->getList($order_goods_condition, $field, '', $alias, $join);
        $cashier_order_info['goods_list'] = $cashier_order_goods_list;
        //todo 判断当前订单是否绑定了会员

        $cashier_order_info = $this->calculateByMember($cashier_order_info, $member_id);
        $cashier_order_goods_list = $cashier_order_info['goods_list'];

        $cashier_order_info['collectmoney_config'] = (new Cashier())->getCashierCollectMoneyConfig($cashier_order_info['site_id'], $cashier_order_info['store_id'])['data'];
        $is_calculate = isset($params['promotion']);
        $promotion = $params['promotion'] ?? [];
        $order_money = $cashier_order_info['order_money'];
        $original_money = $order_money;
        $cashier_order_info['original_money'] = $original_money;
        $cashier_order_info['goods_num'] = numberFormat($cashier_order_info['goods_num']);
        $cashier_order_info = array_merge($cashier_order_info, $promotion, $params);

        $order_goods_id_map = [];
        foreach ($cashier_order_goods_list as $goods_k => $goods_info) {
            $item_order_goods_id = $goods_info['order_goods_id'];
            $order_goods_id_map[$goods_k] = $item_order_goods_id;
            $cashier_order_goods_list[$goods_k]['num'] = numberFormat($goods_info['num']);
        }
        $cashier_order_info['order_goods_id_map'] = $order_goods_id_map;
        $cashier_order_info['goods_list'] = $cashier_order_goods_list;
        if ($member_id > 0) {
            $member_model = new Member();
            $member_account_info = $member_model->getMemberDetail($member_id, $site_id)['data'] ?? [];
            $cashier_order_info['member_account'] = $member_account_info;
        }
        $result = $this->promotionCalculate($cashier_order_info, $is_calculate);
        if ($result['code'] < 0) {
            return $result;
        }
        $result_data = $result['data'];

        return $this->payCalculate($result_data);
    }

    /**
     * 绑定会员后,根据会员信息重新计算订单
     * @param $cashier_order_info
     * @param $member_id
     * @return mixed
     */
    public function calculateByMember($cashier_order_info, $member_id)
    {
        $order_member_id = $cashier_order_info['member_id'];
        if (!$order_member_id) {
            if ($member_id > 0) {
                $site_id = $cashier_order_info['site_id'];
                //重新计算订单
                $cashier_order_type = $cashier_order_info['cashier_order_type'];
                $goods_list = $cashier_order_info['goods_list'];

                $goods_money = 0;
                $real_goods_money = 0;

                foreach ($goods_list as &$v) {
                    $price = $v['price'];
                    $v['price'] = $price;
                    $item_goods_money = $v['goods_money'];
                    $item_real_goods_money = $v['real_goods_money'];
                    $item_num = $v['num'];
                    if (in_array($cashier_order_type, ['goods', 'card'])) {
                        $item_sku_id = $v['sku_id'] ?? 0;
                        if ($item_sku_id > 0) {
                            //无码商品不参与会员价
                            $item_is_adjust_price = $v['is_adjust_price'];
                            if (!$item_is_adjust_price) {
                                $member_price_result = $this->getGoodsMemberPrice($v);
                                if ($member_price_result['code'] >= 0) {
                                    $price = $member_price_result['data'];
                                    $v['is_member_price'] = true;
                                }
                                $v['price'] = $price;
                                $item_goods_money = $price * $item_num;
                                $item_real_goods_money = $price * $item_num;
                                $v['goods_money'] = $item_goods_money;
                                $v['real_goods_money'] = $item_real_goods_money;
                            }
                        }
                    }
                    $goods_money += $item_goods_money;
                    $real_goods_money += $item_real_goods_money;
                }
                $cashier_order_info['goods_money'] = $goods_money;
                $cashier_order_info['real_goods_money'] = $real_goods_money;
                $cashier_order_info['goods_list'] = $goods_list;
                $order_money = $real_goods_money;
                $cashier_order_info['order_money'] = $order_money;
                $pay_money = $order_money;
                $cashier_order_info['pay_money'] = $pay_money;
                $cashier_order_info['is_bind_member'] = 1;//是否绑定会员
                $cashier_order_info['member_id'] = $member_id;
            }
        }
        return $cashier_order_info;
    }

    /**
     * 活动计算
     * @param $calculate
     * @param bool $is_calculate
     * @return array
     */
    public function promotionCalculate($calculate, $is_calculate = true)
    {
        if ($is_calculate) {
            $member_id = $calculate['member_id'] ?? 0;
            if ($member_id > 0) {
                $coupon_calculate = $this->couponCalculate($calculate);
                if ($coupon_calculate['code'] < 0)
                    return $coupon_calculate;
                $calculate = $coupon_calculate['data'];
            }
            $reduction_calculate = $this->reductionCalculate($calculate);
            if ($reduction_calculate['code'] < 0)
                return $reduction_calculate;
            $calculate = $reduction_calculate['data'];
            if ($member_id > 0 && $calculate['collectmoney_config']['point'] > 0) {
                $point_calculate = $this->pointCalculate($calculate);
                if ($point_calculate['code'] < 0)
                    return $point_calculate;
                $calculate = $point_calculate['data'];
            }

            if ($member_id > 0 && $calculate['collectmoney_config']['balance'] > 0) {
                $balance_calculate = $this->balanceCalculate($calculate);
                if ($balance_calculate['code'] < 0)
                    return $balance_calculate;
                $calculate = $balance_calculate['data'];
            }
            $offset = $calculate['offset'] ?? [];
            $calculate['offset'] = $offset;
        }
        return $this->success($calculate);
    }

    /**
     * 优惠券计算
     * @param $calculate_data
     * @return array
     */
    public function couponCalculate($calculate_data)
    {
        $member_id = $calculate_data['member_id'] ?? 0;
        if ($member_id > 0) {
            $order_type = $calculate_data['cashier_order_type'];
            //只有开单和卡项可以用优惠券
            if (in_array($order_type, ['goods', 'card'])) {
                $pay_money = $calculate_data['pay_money'];
                $order_money = $calculate_data['order_money'];
                $site_id = $calculate_data['site_id'];
                $goods_list = $calculate_data['goods_list'] ?? [];
                $is_can_coupon = true;
                foreach ($goods_list as $v) {
                    $trade_type = $v['goods_class'];
                    if ($trade_type == 'money') {
                        $is_can_coupon = false;
                    }
                }
                $goods_ids = array_unique(array_column($goods_list, 'goods_id'));
                $goods_money = $calculate_data['goods_money'];//优惠券现在取用的是商品价用作门槛
                $real_goods_money = $goods_money;
                $offset = $calculate_data['offset'] ?? [];
                if ($order_money > 0 && $pay_money > 0) {
                    $condition = [
                        ['member_id', '=', $member_id],
                        ['state', '=', 1],
                        ['site_id', '=', $site_id],
                        ['use_channel', '<>', CouponDict::channel_online],
                        ['use_store', 'like', ['%'.$calculate_data['store_id'].'%', '%all%'], 'or'],
                    ];
                    $member_coupon_model = new Coupon();
                    $member_coupon_list = $member_coupon_model->getCouponList($condition)['data'];
                    $coupon_array = [];
                    $coupon_model = new Coupon();
                    foreach ($member_coupon_list as $k => $v) {
                        $is_available = $coupon_model->judgeCouponAvailable($v, $goods_list, $goods_money);
                        if($is_available){
                            $coupon_array[] = $v;
                        }
                    }

                    $member_coupon_list = $coupon_array;
                }

                $default_coupon_id = 0;
                $default_coupon_end_time = 0;
                $default_coupon_type = '';
                $default_coupon_discount = 0;
                $default_coupon_offset_money = 0;
                $default_coupon_money = 0;
                $temp_member_coupon_list = [];
                $temp_cache_coupon = [];//用以减轻I/O压力

                if (!empty($member_coupon_list)) {
                    $goods_category_model = new GoodsCategoryModel();
                    foreach ($member_coupon_list as $k => $v) {
//                        $parent_id = $v['coupon_type_id'];
                        $coupon_id = $v['coupon_id'];
                        $at_least = $v['at_least'];//最小条件
                        $item_type = $v['type'];//reward-满减 discount-折扣 random-随机

                        $goods_type = $v['goods_type'];
                        $intersect = [];
                        if ($goods_type == 1) {
                            // 全场
                            $intersect = $goods_ids;
                        } elseif ($goods_type == 2) {
                            // 指定商品
                            $item_goods_ids = explode(',', $v['goods_ids']);
                            $intersect = array_intersect($item_goods_ids, $goods_ids);
                        } elseif ($goods_type == 3) {
                            // 指定不参与商品
                            $item_goods_ids = explode(',', $v['goods_ids']);
                            $intersect = array_diff($goods_ids, $item_goods_ids);
                        } elseif ($goods_type == CouponDict::category_selected || $goods_type == CouponDict::category_selected_out) {
                            // 指定参与/不参与分类
                            $category_leaf_ids = $goods_category_model->getGoodsCategoryLeafIds($v['goods_ids'])['data'];
                            $item_goods_ids = [];
                            foreach ($goods_list as $v_goods) {
                                $goods_category_ids = explode(',', trim($v_goods['category_id'], ','));
                                $array_intersect = array_intersect($category_leaf_ids, $goods_category_ids);
                                if ($v['goods_type'] == CouponDict::category_selected) {
                                    $judge_res = count($array_intersect) > 0;
                                } else {
                                    $judge_res = count($array_intersect) == 0;
                                }
                                if ($judge_res) {
                                    $item_goods_ids[] = $v_goods['goods_id'];
                                }
                            }
                            $intersect = array_intersect($goods_ids, $item_goods_ids);
                        }
                        //计算这几个商品的商品总价
                        $goods_sum = 0;
                        $coupon_order_goods_ids = [];
                        $item_coupon_goods_list = [];
                        foreach ($goods_list as $goods_k => $goods_v) {
                            $item_id = $goods_v['goods_id'];
                            if (in_array($item_id, $intersect)) {
                                $goods_sum += $goods_v['real_goods_money'] ?? 0;//这儿用  商品价还是商品真实价格
                                $coupon_order_goods_ids[] = $goods_v['order_goods_id'];
                                $item_coupon_goods_list[] = $goods_v;
                            }
                        }

                        //判断它支持的商品的商品金额够不够最低金额
                        if ($goods_sum < $at_least) {
                            //移除会员优惠券
                            unset($member_coupon_list[$k]);
                            continue;
                        }
                        switch ($item_type) {
                            case 'reward'://满减
                                $item_coupon_money = $v['money'];
                                if ($item_coupon_money > $goods_sum) {
                                    $item_coupon_money = $goods_sum;
                                }
                                break;
                            case 'discount'://折扣
                                $item_discount = $v['discount'];//折扣
                                $item_discount_limit = $v['discount_limit'];//最多抵扣
                                //计算折扣优惠金额
                                $item_coupon_money = $goods_sum * (10 - $item_discount) / 10;
                                $item_coupon_money = $item_coupon_money > $item_discount_limit && $item_discount_limit != 0 ? $item_discount_limit : $item_coupon_money;
                                $item_coupon_money = min($item_coupon_money, $goods_sum);
                                $item_coupon_money = moneyFormat($item_coupon_money);
                                break;
                            case 'divideticket'://随机
                                $item_coupon_money = $v['money'];
                                if ($item_coupon_money > $goods_sum) {
                                    $item_coupon_money = $goods_sum;
                                }
                                break;
                        }
                        $member_coupon_list[$k]['coupon_goods_money'] = $goods_sum;
                        $member_coupon_list[$k]['coupon_money'] = $item_coupon_money;
                        $member_coupon_list[$k]['coupon_order_goods_ids'] = $coupon_order_goods_ids;
                        $member_coupon_list[$k]['coupon_goods_list'] = $item_coupon_goods_list;

                        //一个准则,折扣券不优先用
                        if ($item_coupon_money > $default_coupon_money) {
                            $default_coupon_id = $coupon_id;
                            $default_coupon_end_time = $v['end_time'];
                            $default_coupon_type = $item_type;
                            if ($item_type == 'discount') {
                                $default_coupon_discount = $v['discount'];
                            } else {
                                $default_coupon_offset_money = $v['money'];
                            }
                            $default_coupon_money = $item_coupon_money;
                        } else if ($item_coupon_money == $default_coupon_money) {
                            if ($item_type == 'discount') {
                                if ($default_coupon_type == $item_type) {
                                    if ($v['discount_limit'] < $default_coupon_discount) {
                                        $default_coupon_id = $coupon_id;
                                        $default_coupon_end_time = $v['end_time'];
                                        $default_coupon_type = $item_type;
                                        $default_coupon_discount = $v['discount'];
                                    } else if ($v['discount_limit'] == $default_coupon_discount) {
                                        if ($v['end_time'] < $default_coupon_end_time) {
                                            $default_coupon_id = $coupon_id;
                                            $default_coupon_end_time = $v['end_time'];
                                            $default_coupon_type = $item_type;
                                            $default_coupon_discount = $v['discount'];
                                        }
                                    }
                                }
                            } else {
                                if ($default_coupon_type == $item_type) {
                                    if ($v['money'] < $default_coupon_offset_money) {
                                        $default_coupon_id = $coupon_id;
                                        $default_coupon_end_time = $v['end_time'];
                                        $default_coupon_type = $item_type;
                                        $default_coupon_discount = $v['money'];
                                    } else if ($v['money'] == $default_coupon_offset_money) {
                                        if ($v['end_time'] < $default_coupon_end_time) {
                                            $default_coupon_id = $coupon_id;
                                            $default_coupon_end_time = $v['end_time'];
                                            $default_coupon_type = $item_type;
                                            $default_coupon_discount = $v['money'];
                                        }
                                    }
                                } else {
                                    $default_coupon_id = $coupon_id;
                                    $default_coupon_end_time = $v['end_time'];
                                    $default_coupon_type = $item_type;
                                    $default_coupon_offset_money = $v['money'];
                                }
                            }
                        }
                    }
                    $temp_member_coupon_list = array_column($member_coupon_list, null, 'coupon_id');
                }
                $coupon_id = $calculate_data['coupon_id'] ?? '';
                $coupon_money = 0;
                $coupon_order_goods_ids = [];
                //计算优惠券优惠
                if (!empty($coupon_id)) {
                    $item_coupon_info = $temp_member_coupon_list[$coupon_id] ?? [];
                    //剔除非法代金券
                    if (empty($item_coupon_info)) {
                        $coupon_id = 0;
                    } else {
                        $item_coupon_money = $item_coupon_info['coupon_money'];
                        $real_goods_money -= $item_coupon_money;
                        $coupon_money += $item_coupon_money;
                        $coupon_order_goods_ids = $item_coupon_info['coupon_order_goods_ids'];
                        $coupon_goods_list = $item_coupon_info['coupon_goods_list'];
                        $coupon_goods_money = $item_coupon_info['coupon_goods_money'];

                        if ($item_coupon_money > $coupon_goods_money) {
                            $item_coupon_money = $coupon_goods_money;
                        }
                        $coupon_goods_list = $this->goodsCouponCalculate($coupon_goods_list, $coupon_goods_money, $item_coupon_money);
                        $coupon_goods_column = array_column($coupon_goods_list, null, 'order_goods_id');
                        foreach ($goods_list as $k => $v) {
                            if (in_array($v['order_goods_id'], $coupon_order_goods_ids)) {
                                $goods_list[$k] = $coupon_goods_column[$v['order_goods_id']];
                            }
                        }

                    }
                }
                if ($is_can_coupon) {
                    $coupon_switch = !empty($member_coupon_list);
                } else {
                    $coupon_switch = false;
                }
                $coupon_array = [
                    'member_coupon_list' => $member_coupon_list ?? [],
                    'coupon_switch' => $coupon_switch
                ];
                $offset['coupon_array'] = $coupon_array;
                $calculate_data['offset'] = $offset;
                $calculate_data['real_goods_money'] = $real_goods_money;
                $calculate_data['coupon_money'] = $coupon_money;
                $calculate_data['goods_list'] = $goods_list;
                $calculate_data['coupon_id'] = $coupon_id;
                $calculate_data['coupon_order_goods_ids'] = $coupon_order_goods_ids;
                $pay_money -= $coupon_money;
                $order_money -= $coupon_money;

                $calculate_data['pay_money'] = $pay_money;
                $calculate_data['order_money'] = $order_money;
            }
        }
        return $this->success($calculate_data);
    }

    /**
     * 按比例摊派优惠券优惠
     * @param $goods_list
     * @param $goods_money
     * @param $coupon_money
     * @return mixed
     */
    public function goodsCouponCalculate($goods_list, $goods_money, $coupon_money)
    {
        $temp_coupon_money = $coupon_money;
        $last_key = count($goods_list) - 1;
        foreach ($goods_list as $k => $v) {
            if ($last_key != $k) {
                $item_coupon_money = moneyFormat($v['real_goods_money'] / $goods_money * $coupon_money);
            } else {
                $item_coupon_money = $temp_coupon_money;
            }
            $temp_coupon_money -= $item_coupon_money;
            $goods_list[$k]['coupon_money'] = $item_coupon_money;
            $real_goods_money = $v['real_goods_money'] - $item_coupon_money;
            $real_goods_money = max($real_goods_money, 0);
            $goods_list[$k]['real_goods_money'] = $real_goods_money; //真实订单项金额
        }
        return $goods_list;
    }

    /**
     * 调价计算
     * @param $cashier_order_info
     * @return array
     */
    public function reductionCalculate($cashier_order_info)
    {
        $offset = $cashier_order_info['offset'] ?? [];
        $reduction = $cashier_order_info['reduction'] ?? 0;//调整金额
        $order_money = $cashier_order_info['order_money'];
        $promotion_money = $cashier_order_info['promotion_money'];

        $pay_money = $cashier_order_info['pay_money'];
        if ($reduction > 0) {
            $offset_money = $cashier_order_info['offset_money'] ?? 0;
            if ($reduction > $order_money) {
                $reduction = $order_money;
            }
            $order_money -= $reduction;
            if ($reduction > $pay_money) {
                $reduction = $pay_money;
            }
            $pay_money -= $reduction;
            $offset['reduction'] = $reduction;
            $cashier_order_info['reduction'] = $reduction;
            $offset_money += $reduction;
            $promotion_money += $reduction;
            $calculate_data['offset_money'] = $offset_money;
        }
        $cashier_order_info['pay_money'] = $pay_money;
        $cashier_order_info['offset'] = $offset;
        $cashier_order_info['promotion_money'] = $promotion_money;
        $cashier_order_info['order_money'] = $order_money;
        //同步订单项列表real_goods_money
        $cashier_order_info = $this->calculateGoodsList($cashier_order_info, $reduction);
        return $this->success($cashier_order_info);
    }

    /**
     * 优惠金额
     * @param $cashier_order_info
     * @param $money
     * @return mixed
     */
    public function calculateGoodsList($cashier_order_info, $money)
    {
        if ($money > 0) {
            $goods_list = $cashier_order_info['goods_list'];
            $temp_money = $money;
            foreach ($goods_list as &$v) {
                $real_goods_money = $v['real_goods_money'];
                if ($real_goods_money >= $temp_money) {
                    $real_goods_money -= $temp_money;
                } else {
                    $real_goods_money = 0;
                }
                $item_money = $temp_money;
                $temp_money -= $item_money;
                $v['real_goods_money'] = $real_goods_money;
            }
            $cashier_order_info['goods_list'] = $goods_list;
        }
        return $cashier_order_info;
    }

    /**
     * 积分计算
     * @param $cashier_order_info
     * @return array
     */
    public function pointCalculate($cashier_order_info)
    {
        $member_account = $cashier_order_info['member_account'] ?? [];

        if (addon_is_exit('pointcash')) {
            if (!empty($member_account)) {
                $order_type = $cashier_order_info['cashier_order_type'];
                if (in_array($order_type, ['goods', 'card'])) {
                    $site_id = $cashier_order_info['site_id'];
                    $offset = $cashier_order_info['offset'] ?? [];
                    $pay_money = $cashier_order_info['pay_money'];
                    $offset_money = $cashier_order_info['offset_money'] ?? 0;
                    $order_money = $cashier_order_info['order_money'];
                    $site_type = $cashier_order_info['site_type'] ?? '';
                    $point = 0;
                    $point_money = 0;
                    $use_point_money = 0;
                    $use_point = 0;
                    $is_use_point = $cashier_order_info['is_use_point'] ?? 0;//使用积分
                    //积分
                    $point_config_model = new PointConfig();
                    $point_config = $point_config_model->getPointCashConfig($site_id)['data'];
                    $point_value = $point_config['value'];
                    $is_use = $point_config['is_use'];
                    if ($is_use > 0) {
                        $is_limit = $point_value['is_limit'];
                        if ($is_limit == 1) {
                            $limit = $point_value['limit'];
                            if ($order_money < $limit) {
                                return $this->success($cashier_order_info);
                            }
                        }
                        $max_point_money = 0;
                        if ($point_value['is_limit_use'] == 1) {
                            if ($point_value['type'] == 0) {
                                $max_point_money = $point_value['max_use'];
                            } else {
                                $ratio = $point_value['max_use'] / 100;
                                $max_point_money = round(($order_money * $ratio), 2);
                            }
                            if ($max_point_money > $order_money) {
                                $max_point_money = $order_money;
                            }
                        }
                        $point_exchange_rate = $point_value['cash_rate'] ?? 0;//积分兑换比率  为0的话认为没有开启积分兑换
                        if ($point_exchange_rate > 0) {

                            $member_account_point = $member_account['point'];//会员积分
                            if ($member_account_point > 0) {//拥有积分大于0
                                $point_money = round($member_account_point / $point_exchange_rate, 2);//积分抵扣金额
                                if ($point_money > $pay_money) {
                                    $point_money = $pay_money;
                                }
                                if ($max_point_money != 0 && $point_money > $max_point_money) {
                                    $point_money = $max_point_money;
                                }
                                $point = ceil($point_money * $point_exchange_rate);
                            }
                        }
                    }
                    $point_array = [
                        'point' => $point,
                        'point_money' => $point_money,
                        'point_switch' => false
                    ];
                    if ($pay_money > 0) {
                        if ($point > 0) {
                            $point_array['point_switch'] = true;
                            //存在可用积分且选用了积分抵扣
                            if ($is_use_point) {
                                $order_money -= $point_money;
                                $pay_money -= $point_money;
                                $use_point_money = $point_money;
                                $use_point = $point;
                            }
                        } else {
                            $is_use_point = 0;
                        }
                    } else {
                        $is_use_point = 0;
                    }
                    $offset['point_array'] = $point_array;
                    $cashier_order_info['offset'] = $offset;
                    $cashier_order_info['order_money'] = $order_money;
                    $cashier_order_info['is_use_point'] = $is_use_point;
                    $offset_money += $use_point_money;
                    $cashier_order_info['offset_money'] = $offset_money;
                    $cashier_order_info['point_money'] = $use_point_money;//积分抵扣多少金额
                    $cashier_order_info['point'] = $use_point;//使用多少个积分
                    $cashier_order_info['pay_money'] = $pay_money;
                    if ($is_use_point == 1) {
                        //同步订单项列表real_goods_money
                        $cashier_order_info = $this->calculateGoodsList($cashier_order_info, $point_money);
                    }

                }
            }
        }
        return $this->success($cashier_order_info);
    }

    /**
     * 余额计算
     * @param $cashier_order_info
     * @return array
     */
    public function balanceCalculate($cashier_order_info)
    {
        $offset = $cashier_order_info['offset'] ?? [];
        $pay_money = $cashier_order_info['pay_money'];
        $order_money = $cashier_order_info['order_money'];
//        $promotion = $cashier_order_info['promotion'] ?? [];
        $is_use_balance = $cashier_order_info['is_use_balance'] ?? 0;
        $member_account = $cashier_order_info['member_account'] ?? [];
        if (!empty($member_account)) {
            $order_type = $cashier_order_info['cashier_order_type'];
            if (in_array($order_type, ['goods', 'card'])) {
                $member_balance_total = $member_account['balance_total'] ?? 0;
                $offset_money = $cashier_order_info['offset_money'] ?? 0;
                $balance_money = 0;
                if ($member_balance_total > 0) {
                    $balance_money = min($member_balance_total, $pay_money);
                }
                $balance_array = [
                    'balance' => $balance_money,
                    'balance_money' => $balance_money,
                    'balance_switch' => false
                ];
                $total_balance = 0;
                if ($pay_money > 0) {
                    if ($balance_money > 0) {
                        $balance_array['balance_switch'] = true;
                        if ($is_use_balance > 0) {
                            $total_balance = $balance_money;
                            $pay_money -= $balance_money;
                        }
                    } else {
                        $is_use_balance = 0;
                    }
                } else {
                    $is_use_balance = 0;
                }
                $offset['balance'] = $balance_array;
                $cashier_order_info['is_use_balance'] = $is_use_balance;
                $cashier_order_info['order_money'] = $order_money;
                $cashier_order_info['pay_money'] = $pay_money;
                $cashier_order_info['offset'] = $offset;
                $cashier_order_info['total_balance'] = $total_balance ?? 0;
                $offset_money += $total_balance;
                $cashier_order_info['offset_money'] = $offset_money;
            }
        }
        return $this->success($cashier_order_info);
    }

    /**
     * 支付计算
     * @param $cashier_order_info
     * @return array
     */
    public function payCalculate($cashier_order_info)
    {
        $member_id = $cashier_order_info['member_id'] ?? 0;
        $pay_money = $cashier_order_info['pay_money'];
        $pay_type = $cashier_order_info['pay_type'] ?? '';
        if ($pay_type == 'third') {
            $pay_type = 'ONLINE_PAY';
        }
        $online_type = empty($cashier_order_info['online_type']) ? $pay_type : $cashier_order_info['online_type'];
        $paid_money = 0;
        $cash = $cashier_order_info['cash'] ?? 0;
        $balance = $cashier_order_info['total_balance'];
        $online_money = $cashier_order_info['online_money'] ?? 0;

        switch ($online_type) {
            case 'cash':
                $cash = $cashier_order_info['cash'];
                $paid_money += $cash;
                break;
            case 'online':
                $online_money = $pay_money;
                $paid_money += $online_money;
                break;
            case 'own_wechatpay':
                $own_wechatpay = $pay_money;
                $paid_money += $own_wechatpay;
                break;
            case 'own_alipay':
                $own_alipay = $pay_money;
                $paid_money += $own_alipay;
                break;
            case 'own_pos':
                $own_pos = $pay_money;
                $paid_money += $own_pos;
                break;
        }
        $surplus_money = $pay_money - $paid_money;
        if ($surplus_money < 0) {
            $cash_change = abs($surplus_money);
        }
        $data = [
            'pay_money' => $pay_money,
            'paid_money' => $paid_money,
            'surplus_money' => $surplus_money,
            'cash' => $cash,
            'cash_change' => $cash_change ?? 0,
            'total_balance' => $balance,//总余额
            'online_money' => $online_money,
            'online_type' => $online_type,
            'own_wechatpay' => $own_wechatpay ?? 0,
            'own_alipay' => $own_alipay ?? 0,
            'own_pos' => $own_pos ?? 0,
            'pay_type' => $pay_type,
        ];
        if ($member_id > 0) {
            $data['member_id'] = $member_id;
        }
        $data = array_merge($cashier_order_info, $data);
        return $this->success($data);
    }

    public function roundCalculate($cashier_order_info)
    {
        $offset = $cashier_order_info['offset'] ?? [];
        $reduction = $cashier_order_info['reduction'] ?? 0;//调整金额
        $order_money = $cashier_order_info['order_money'];
        $promotion_money = $cashier_order_info['promotion_money'];
        $round_type = $cashier_order_info['round_type'];//收银台  抹零方式   1抹分 2抹角
        $pay_money = $cashier_order_info['pay_money'];
        if (!empty($round_type)) {
            $offset_money = $cashier_order_info['offset_money'] ?? 0;
            $new_order_money = 0;
            switch ($round_type) {
                case 1:
                    $new_order_money = round(intval($order_money * 10) / 10, 2);
                    break;
                case 2:
                    $new_order_money = round(intval($order_money * 100) / 100, 2);
                    break;
            }
            $round_money = $order_money - $new_order_money;
//            if ($round_money > $order_money) {
//                $round_money = $pay_money;
//            }
            $order_money -= $round_money;
//            if ($round_money > $pay_money) {
//                $round_money = $pay_money;
//            }
            $pay_money -= $round_money;
            $offset['round'] = $round_money;
            $cashier_order_info['round_money'] = $round_money;
            $offset_money += $round_money;
            $promotion_money += $round_money;
            $calculate_data['offset_money'] = $offset_money;
        }
        $cashier_order_info['pay_money'] = $pay_money;
        $cashier_order_info['offset'] = $offset;
        $cashier_order_info['promotion_money'] = $promotion_money;
        $cashier_order_info['order_money'] = $order_money;
        return $this->success($cashier_order_info);
    }
}
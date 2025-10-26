<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\store\model\settlement;

use app\model\BaseModel;

use addon\store\model\Config;
use addon\store\model\StoreAccount;
use app\model\store\Store;
use think\facade\Log;

class OrderSettlement extends BaseModel
{
    const ACTION_EXECUTE = 1;
    const ACTION_RETURN_DATA = 2;

    /**
     * 门店订单分成计算
     * @param $data
     * @param $action
     */
    public function orderSettlementAccount($data, $action = self::ACTION_EXECUTE)
    {
        $order_id = $data[ 'order_id' ];
        $order_info = model('order')->getInfo([ [ 'order_id', '=', $order_id ] ]);
        if(empty($order_info)) return null;
        if(empty($order_info['store_id'])) return null;
        $store_id = $order_info[ 'store_id' ];
        $site_id = $order_info[ 'site_id' ];
        $config_model = new Config();
        $config = $config_model->getStoreWithdrawConfig($site_id)[ 'data' ][ 'value' ] ?? [];
        $is_settlement = $config[ 'is_settlement' ];//结算模式  todo 系统配置的门店计算开关是否会对门店结算开关造成影响
        if ($is_settlement > 0) {
            $store_model = new Store();
            $store_condition = array (
                [ 'site_id', '=', $site_id ],
                [ 'store_id', '=', $store_id ]
            );
            $store_info = $store_model->getStoreInfoByAccount($store_condition)[ 'data' ] ?? [];
            $is_settlement = $store_info[ 'is_settlement' ];
            if ($is_settlement > 0) {
                $settlement_pay_type = $config['settlement_pay_type'] ?? [];//参与结算的支付方式
                if(!empty($settlement_pay_type)){
                    if(in_array($order_info['pay_type'], explode(',', $settlement_pay_type))){
                        $store_settlement_rate = $store_info[ 'settlement_rate' ];
                        if ($store_settlement_rate > 0) {
                            $settlement_rate = $store_settlement_rate;
                        } else {
                            $settlement_rate = $config[ 'settlement_rate' ];
                        }

                        if ($settlement_rate > 0) {
                            $settlement_rate_calc = $settlement_rate / 100;//计算门店抽成比例
                            $order_money = $order_info[ 'order_money' ];
                            //todo  可能还有退款
                            $settlement_cost = $config[ 'settlement_cost' ];
                            $settlement_cost_array = explode(',', $settlement_cost);

                            //总金额
                            $base_money = $order_money;
                            $remark = '订单编号：'.$order_info['order_no'].'；结算佣金=([订单金额]'.$base_money;
                            //退款金额
                            if(!empty($order_info[ 'refund_money' ])){
                                $base_money -= $order_info[ 'refund_money' ];//todo  这儿涉及到一个问题,退款的金额里面包含一部分余额,可能结算金额不太一致
                                if($order_info['refund_money'] > 0){
                                    $remark .= '-[退款金额]'.$order_info['refund_money'];
                                }
                            }
                            //余额抵扣
                            if($order_info['balance_money'] > 0){
                                $balance_money = $order_info[ 'balance_money' ];
                                $base_money -= $balance_money;
                                $remark .= '-[余额抵扣]'.$order_info['balance_money'];
                            }
                            //分销佣金
                            if (in_array('fenxiao_commission', $settlement_cost_array)) {
                                $commission = $order_info[ 'commission' ];
                                $base_money -= $commission;
                                if($order_info['commission'] > 0){
                                    $remark .= '-[分销佣金]'.$order_info['commission'].')';
                                }
                            }
                            //积分抵扣
                            if (in_array('point', $settlement_cost_array)) {
                                $point_money = $order_info[ 'point_money' ];
                                $base_money += $point_money;
                                if($order_info['point_money'] > 0){
                                    $remark .= '+[积分抵扣]'.$order_info['point_money'];
                                }
                            }
                            //优惠券比较特殊, 不过不扣除要价格优惠券抵扣金额加回去
                            if (!in_array('coupon', $settlement_cost_array)) {
                                $coupon_money = $order_info[ 'coupon_money' ];
                                $base_money += $coupon_money;
                                if($order_info['coupon_money'] > 0){
                                    $remark .= '+[优惠券券金额]'.$order_info['coupon_money'];
                                }
                            }
                            if ($base_money > 0) {
                                $store_commission_rate = $settlement_rate;
                                $store_commission = round($base_money * $settlement_rate_calc, 2);
                                $remark .= ')*'.$store_commission_rate.'%='.$store_commission;
                                $order_data = array (
                                    'store_commission_rate' => $store_commission_rate,
                                    'store_commission' => $store_commission
                                );
                                $order_condition = array (
                                    [ 'order_id', '=', $order_id ]
                                );
                                model('order')->update($order_data, $order_condition);

                                $store_account_model = new StoreAccount();
                                //门店账户金额增加
                                $store_account_data = array (
                                    'account_data' => $store_commission,
                                    'site_id' => $site_id,
                                    'store_id' => $store_id,
                                    'from_type' => 'order',
                                    'remark' => $remark,
                                    'related_id' => $order_id
                                );
                                if($action == self::ACTION_EXECUTE){
                                    //防止重复添加
                                    $store_account_info = $store_account_model->getStoreAccountInfo([['from_type', '=', 'order'], ['related_id', '=', $order_id]])['data'];
                                    if(empty($store_account_info)){
                                        $store_account_model->addStoreAccount($store_account_data);
                                    }
                                }else{
                                    return $store_account_data;
                                }
                            }

                        }
                    }

                }

            }
        }
    }

    /**
     *
     * @param $data
     * @param $action
     */
    public function orderRefundSettlementAccount($data, $action = self::ACTION_EXECUTE)
    {
//        $order_goods_id = $data[ 'order_goods_id' ];
        $order_goods_info = $data['order_goods_info'];
        if (empty($order_goods_info))
            return null;

        $order_goods_id = $order_goods_info['order_goods_id'];
//        $order_id = $order_goods_info[ 'order_id' ];
        $order_info = $data['order_info'];
        if (empty($order_info))
            return null;

        $site_id = $order_info[ 'site_id' ];
        $store_id = $order_info[ 'store_id' ];
        $order_scene = $order_info[ 'order_scene' ];
        //只有收银台订单会有这种情况
        if ($store_id == 0 || $order_scene != 'cashier')
            return null;

        $refund_money = sprintf("%.2f", $data['refund_money']);
        $order_money = $order_info[ 'order_money' ];

        if($order_money <= 0)
            return null;
        if($refund_money <= 0){
            return null;
        }
        $store_commission = $order_info[ 'store_commission' ];
        $refund_store_commission = round($store_commission * ( $refund_money / $order_money ), 2);
        $store_account_model = new StoreAccount();
        //门店账户金额减少
        $store_account_data = array (
            'account_data' => -$refund_store_commission,
            'site_id' => $site_id,
            'store_id' => $store_id,
            'from_type' => 'refund',
            'remark' => "订单编号：{$order_info['order_no']}；退款编号：{$order_goods_info['refund_no']}；扣除结算佣金=[结算佣金]{$store_commission}*([退款金额]{$refund_money}/[订单金额]{$order_money})={$refund_store_commission}",
            'related_id' => $order_goods_id,
            'is_limit' => 0
        );
        if($action == self::ACTION_EXECUTE){
            $store_account_model->addStoreAccount($store_account_data);
        }else{
            return $store_account_data;
        }
    }

    /**
     * 矫正任务
     * @param array $param
     * @return array
     */
    public function correctStoreAccountTask($param = [])
    {
        $last_order_id = $param['last_order_id'] ?? 0;
        $store_id = $param['store_id'] ?? 0;
        $start_time = time();
        $max_exec_time = 50;
        $res_list = [];
        $is_end = false;
        while (true){
            if(time() - $start_time > $max_exec_time){
                break;
            }

            $order_info = model('order')->getFirstData([
                ['order_id', '>', $last_order_id],
                ['store_id', '=', $store_id],
            ]);

            if(empty($order_info)){
                $is_end = true;
                break;
            }

            $res = $this->correctStoreAccount($order_info);
            if(!empty($res)) $res_list[$order_info['order_id']] = $res;
            $last_order_id = $order_info['order_id'];
        }
        $account = model('store_account')->getSum([['store_id', '=', $store_id]], 'account_data');
        model('store')->update(['account' => $account], [['store_id', '=', $store_id]]);
        return [
            'res_list' => $res_list,
            'last_order_id' => $last_order_id,
            'is_end' => $is_end,
            'account' => $account,
        ];
    }

    /**
     * 矫正方法
     * @param $order_info
     * @return array
     */
    public function correctStoreAccount($order_info)
    {
        $deal_res = [];
        $store_account_model = new StoreAccount();
        $from_type_list = $store_account_model->from_type;

        //矫正退款总额
        $order_goods_list = model('order_goods')->getList([
            ['order_id', '=', $order_info['order_id']],
        ]);
        $refund_money = 0;
        foreach($order_goods_list as $order_goods){
            if(in_array($order_goods['refund_status'], [3,-3])){
                $refund_money += $order_goods['refund_real_money'] + $order_goods['shop_active_refund_money'];
            }
        }
        if($order_info['refund_money'] != $refund_money){
            model('order')->update(['refund_money' => $refund_money], [['order_id', '=', $order_info['order_id']]]);
            $deal_res[] = "矫正退款总额，{$order_info['refund_money']}=>$refund_money";
        }

        //清空数据
        model('store_account')->delete([['from_type', '=', 'order'], ['related_id', '=', $order_info['order_id']]]);
        if(!empty($order_goods_list)){
            model('store_account')->delete([['from_type', '=', 'refund'], ['related_id', 'in', array_column($order_goods_list, 'order_goods_id')]]);
        }

        //重新计算
        if($order_info['order_status'] == 10){
            $store_account_data = $this->orderSettlementAccount(['order_id' => $order_info['order_id']], self::ACTION_RETURN_DATA);
            if(!empty($store_account_data)){
                $store_account_data['from_type_name'] = $from_type_list['order']['type_name'];
                $store_account_data['create_time'] = time();
                model('store_account')->add($store_account_data);
                $deal_res[] = $store_account_data['remark'];
            }
        }

        if(!empty($deal_res)){
            Log::write('订单结算数据矫正:order_id=>'.$order_info['order_id']);
            Log::write($deal_res);
        }
        return $deal_res;
    }

    public function findStoreAccountErrorDataTask($param)
    {
        $last_order_id = $param['last_order_id'] ?? 0;
        $store_id = $param['store_id'] ?? 0;
        $start_time = time();
        $max_exec_time = 50;
        $res_list = [];
        $is_end = false;
        while (true){
            if(time() - $start_time > $max_exec_time){
                break;
            }

            $order_info = model('order')->getFirstData([
                ['order_id', '>', $last_order_id],
                ['store_id', '=', $store_id],
            ]);

            if(empty($order_info)){
                $is_end = true;
                break;
            }

            $res = $this->findStoreAccountErrorData($order_info);
            if(!empty($res)) $res_list[$order_info['order_id']] = $res;
            $last_order_id = $order_info['order_id'];
        }
        return [
            'res_list' => $res_list,
            'last_order_id' => $last_order_id,
            'is_end' => $is_end,
        ];
    }

    public function findStoreAccountErrorData($order_info)
    {
        $deal_res = [];

        //矫正退款总额
        $order_goods_list = model('order_goods')->getList([
            ['order_id', '=', $order_info['order_id']],
        ]);
        $refund_money = 0;
        foreach($order_goods_list as $order_goods){
            if(in_array($order_goods['refund_status'], [3,-3])){
                $refund_money += $order_goods['refund_real_money'] + $order_goods['shop_active_refund_money'];
            }
        }
        if($order_info['refund_money'] != $refund_money){
            model('order')->update(['refund_money' => $refund_money], [['order_id', '=', $order_info['order_id']]]);
            $deal_res[] = "矫正退款总额，{$order_info['refund_money']}=>$refund_money";
        }

        //比对数据库数据和计算数据
        $order_goods_ids = join(',', array_column($order_goods_list, 'order_goods_id'));
        if(empty($order_goods_ids)) $order_goods_ids = '0';
        $store_account_list = model('store_account')->getList([
            ['', 'exp', \think\facade\Db::raw("(from_type = 'order' and related_id = {$order_info['order_id']}) or (from_type = 'refund' and related_id in ({$order_goods_ids}))")],
        ]);
        $db_store_account = array_sum(array_column($store_account_list, 'account_data'));
        if($order_info['order_status'] == 10){
            $store_account_data = $this->orderSettlementAccount(['order_id' => $order_info['order_id']], self::ACTION_RETURN_DATA);
        }
        $calc_store_account = $store_account_data['account_data'] ?? 0;
        if($db_store_account <> $calc_store_account){
            $deal_res[] = [
                "数据库记录：{$db_store_account}，实时计算：{$calc_store_account}",
                $store_account_list,
            ];
        }

        return $deal_res;
    }
}
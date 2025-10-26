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

use addon\cashier\model\order\CashierOrderCalculate;
use addon\cashier\model\order\CashierOrderPay;
use app\model\system\Pay as PayModel;
use app\storeapi\controller\BaseStoreApi;

/**
 * 收银支付
 * Class Pay
 * @package app\shop\controller
 */
class Cashierpay extends BaseStoreApi
{

    /**
     * 计算
     * @return false|string
     */
    public function payCalculate()
    {

        $out_trade_no = $this->params[ 'out_trade_no' ] ?? '';
        $promotion = !empty($this->params[ 'promotion' ]) ? json_decode($this->params[ 'promotion' ], true) : [];
        $data = [
            'pay_type' => $this->params[ 'pay_type' ] ?? '',
            'site_id' => $this->site_id,
            'out_trade_no' => $out_trade_no,
            'store_id' => $this->store_id ?? 0,
            'promotion' => $promotion,
            'member_id' => $this->params[ 'member_id' ],
            'cash' => $this->params[ 'cash' ] ?? 0
        ];
        $cashier_order_calculate_model = new CashierOrderCalculate();
        $result = $cashier_order_calculate_model->calculate($data);
        //记录缓存(操作记录保存)
        $cashier_order_pay_model = new CashierOrderPay();
        $cashier_order_pay_model->setCache($out_trade_no, [ 'promotion' => $promotion, 'member_id' => $this->params[ 'member_id' ] ?? 0 ]);

        return $this->response($result);
    }

    /**
     * 订单支付
     */
    public function confirm()
    {
        $out_trade_no = $this->params[ 'out_trade_no' ] ?? '';
        $promotion = !empty($this->params[ 'promotion' ]) ? json_decode($this->params[ 'promotion' ], true) : [];
        $cashier_order_pay_model = new CashierOrderPay();
        $data = [
            'pay_type' => $this->params[ 'pay_type' ] ?? '',
            'site_id' => $this->site_id,//站点id
            'out_trade_no' => $out_trade_no,
            'store_id' => $this->store_id ?? 0,
            'promotion' => $promotion,
            'member_id' => $this->params[ 'member_id' ],
            'cash' => $this->params[ 'cash' ] ?? 0
        ];
        $result = $cashier_order_pay_model->confirm($data);

        return $this->response($result);
    }

    /**
     * 支付信息
     * @return false|string
     */
    public function info()
    {
        $out_trade_no = $this->params[ 'out_trade_no' ];
        $pay = new PayModel();
        $info = $pay->getPayInfo($out_trade_no);
        if (!empty($info[ 'data' ])) {
            $res = event('PayOrderQuery', [ 'relate_id' => $info[ 'data' ][ 'id' ] ]);
            $info['data']['event_res'] = $res;
        }
        return $this->response($info);
    }

    /**
     * 生成支付二维码
     */
    public function payQrcode()
    {
        $out_trade_no = $this->params[ 'out_trade_no' ];
        $cashier_order_pay_model = new CashierOrderPay();
        $data = [
            'site_id' => $this->site_id,//站点id
            'out_trade_no' => $this->params[ 'out_trade_no' ] ?? '',
            'store_id' => $this->store_id ?? 0,
        ];
        $result = $cashier_order_pay_model->createPay($data);
        if ($result[ 'code' ] < 0) {
            return $this->response($result);
        }
        if (!empty($result[ 'data' ])) $out_trade_no = $result[ 'data' ];
        $data = [];
        $pay = new PayModel();
        foreach (event('PayType', []) as $item) {
            $result = $pay->pay($item[ 'pay_type' ], $out_trade_no, 'cashier', 0);
            if ($result && $result[ 'code' ] >= 0 && !empty($result['data']['qrcode'])) {
                $data[] = [
                    'pay_type' => $item['pay_type'],
                    'pay_type_name' => $item['pay_type_name'],
                    'qrcode' => $result['data']['qrcode'],
                    'logo' => img($item['logo']),
                    'out_trade_no' => $out_trade_no
                ];
            }
        }
        return $this->response($this->success($data));
    }

    /**
     * 创建支付单据
     * @return false|string
     */
    public function createPay()
    {
        $cashier_order_pay_model = new CashierOrderPay();
        $data = [
            'site_id' => $this->site_id,//站点id
            'out_trade_no' => $this->params[ 'out_trade_no' ] ?? '',
            'store_id' => $this->store_id ?? 0,
        ];
        $result = $cashier_order_pay_model->createPay($data);
        return $this->response($result);
    }

    /**
     * 会员付款码支付 (已废弃)
     */
    public function paymentCodePay()
    {
        $out_trade_no = $this->params[ 'out_trade_no' ];
        $auth_code = $this->params[ 'auth_code' ];
        return $this->response($this->error());
    }

    /**
     * 支付类型
     */
    public function payType()
    {
        $pay = new PayModel();
        $info = $pay->getPayType($this->params);
        $temp = empty($info) ? [] : $info;
        $type = [];
        foreach ($temp[ 'data' ] as $k => $v) {
            $type[] = $v['pay_type'];
        }
        return $this->response($this->success($type));
    }
}
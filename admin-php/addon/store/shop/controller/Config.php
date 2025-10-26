<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\store\shop\controller;

use addon\store\model\Config as ConfigModel;
use app\model\order\OrderCommon as OrderCommonModel;
use app\shop\controller\BaseShop;
use think\App;

/**
 * 门店设置控制器
 */
class Config extends BaseShop
{

    public function __construct(App $app = null)
    {
        $this->replace = [
            'STORE_IMG' => __ROOT__ . '/addon/store/shop/view/public/img',
            'STORE_JS' => __ROOT__ . '/addon/store/shop/view/public/js',
            'STORE_CSS' => __ROOT__ . '/addon/store/shop/view/public/css',
        ];
        parent::__construct($app);
    }

    /**
     * 门店结算周期配置
     */
    public function index()
    {
        $config_model = new ConfigModel();
        if (request()->isJson()) {
            if(env('IS_RELEASE_WEAPP')){
                return error(-1, '发布小程序期间禁止操作');
            }
            //门店结算与提现设置
            $withdraw_config = [
                'is_settlement' => input('is_settlement', 0), // 是否进行门店结算
                'period_type' => input('period_type', 4), // 结算方式  1  按天  2. 按周  3. 按月  4. 门店申请结算
                'settlement_rate' => input('settlement_rate', 0), // 结算比率
                'settlement_pay_type' => input('settlement_pay_type', ''), // 结算付款控制
                'settlement_cost' => input('settlement_cost', ''), // 结算成本控制 coupon,point,balance,fenxiao_commission
                'is_withdraw' => input('is_withdraw', 0), // 是否允许提现
                'is_audit' => input('is_audit', 0), // 是否需要提现审核
                'is_auto_transfer' => input('is_auto_transfer', 0), // 是否自动转账
                'withdraw_type' => input('withdraw_type', ''), // 可提现账户类型 wechat，alipay， bank
                'withdraw_least' => input('withdraw_least', 0), // 提现最低金额
            ];
            $config_model->setStoreWithdrawConfig($this->site_id, $withdraw_config);
            $business_config = [
                'store_business' => input('store_business', 'shop'), // 门店运营模式 shop：店铺整体运营  store：连锁门店运营模式
                'is_allow_change' => input('is_allow_change', 1), // 是否允许切换门店
                'confirm_popup_control' => input('confirm_popup_control', 0), // 门店确认弹窗
                'store_auth' => input('store_auth', ''), // 门店控制权限
            ];
            $res = $config_model->setStoreBusinessConfig($this->site_id, $business_config);
            return $res;
        } else {

            $business_config = $config_model->getStoreBusinessConfig($this->site_id)[ 'data' ][ 'value' ];
            $this->assign('business_config', $business_config);

            $withdraw_config = $config_model->getStoreWithdrawConfig($this->site_id)[ 'data' ][ 'value' ];
            $this->assign('withdraw_config', $withdraw_config);

            if (addon_is_exit('cashier') == 1) {
                $order_common_model = new OrderCommonModel();
                $pay_type = $order_common_model->getPayType([ 'order_type' => 5 ]);
                if (isset($pay_type[ 'BALANCE' ])) unset($pay_type[ 'BALANCE' ]);
                if (isset($pay_type[ 'ONLINE_PAY' ])) unset($pay_type[ 'ONLINE_PAY' ]);
                $this->assign('pay_type_list', $pay_type);
            }

            return $this->fetch('config/index');
        }
    }
}
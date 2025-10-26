<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\store\model;

use app\model\order\OrderCommon as OrderCommonModel;
use app\model\system\Config as ConfigModel;
use app\model\BaseModel;
use Carbon\Carbon;
use app\model\system\Cron;

/**
 * 店铺设置信息
 */
class Config extends BaseModel
{

    /**
     * 门店结算相关设置
     * @param $site_id
     * @return array
     */
    public function getStoreWithdrawConfig($site_id)
    {
        $config = new ConfigModel();
        $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'SHOP_STORE_WITHDRAW' ] ]);
        //数据格式化

        if (addon_is_exit('cashier') == 1) {
            $order_common_model = new OrderCommonModel();
            $pay_type = $order_common_model->getPayType([ 'order_type' => 5 ]);
        } else {
            $pay_type = [];
        }
        $data = [
            //是否进行门店结算
            'is_settlement' => $res[ 'data' ][ 'value' ][ 'is_settlement' ] ?? 0,

            //结算周期  1  按天  2. 按周  3. 按月  4. 立即结算
            'period_type' => $res[ 'data' ][ 'value' ][ 'period_type' ] ?? 4,

            //结算比率
            'settlement_rate' => $res[ 'data' ][ 'value' ][ 'settlement_rate' ] ?? 0,

            // 结算付款控制
            'settlement_pay_type' => $res[ 'data' ][ 'value' ][ 'settlement_pay_type' ] ?? implode(',', array_keys($pay_type)),

            //结算成本控制 cuppon,point,balance,fenxiao_commission
            'settlement_cost' => $res[ 'data' ][ 'value' ][ 'settlement_cost' ] ?? '',//'cuppon,point,balance,fenxiao_commission'

            //是否允许提现
            'is_withdraw' => $res[ 'data' ][ 'value' ][ 'is_withdraw' ] ?? 0,

            //是否提现审核
            'is_audit' => $res[ 'data' ][ 'value' ][ 'is_audit' ] ?? 0,

            //是否自动转账
            'is_auto_transfer' => $res[ 'data' ][ 'value' ][ 'is_auto_transfer' ] ?? 0,

            //可提现账户类型 wechat，alipay， bank
            'withdraw_type' => $res[ 'data' ][ 'value' ][ 'withdraw_type' ] ?? '',
            'withdraw_least' => $res[ 'data' ][ 'value' ][ 'withdraw_least' ] ?? 0, // 提现最低金额
        ];
        $res[ 'data' ][ 'value' ] = $data;
        return $res;
    }

    /**
     * 门店结算相关设置
     */
    public function setStoreWithdrawConfig($site_id, $data)
    {
        $config = new ConfigModel();
        $res = $config->setConfig($data, '门店结算设置', 1, [ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'SHOP_STORE_WITHDRAW' ] ]);

        $cron = new Cron();
        $execute_time = 0;
        switch ( $data[ 'period_type' ] ) {
            case 1://天
                $date = strtotime(date('Y-m-d 00:00:00'));
                $execute_time = strtotime('+1day', $date);
                break;
            case 2://周
                $execute_time = Carbon::parse('next monday')->timestamp;
                break;
            case 3://月
                $execute_time = Carbon::now()->addMonth()->firstOfMonth()->timestamp;
                break;
        }
        if ($execute_time > 0) {
            $cron->deleteCron([ [ 'event', '=', 'StoreWithdrawPeriodCalc' ], [ 'relate_id', '=', $site_id ] ]);
            $cron->addCron('2', '1', '门店周期结算', 'StoreWithdrawPeriodCalc', $execute_time, $site_id, $data[ 'period_type' ]);
        }

        return $res;
    }

    /**
     * 获取门店运营相关设置
     * @param $site_id
     */
    public function getStoreBusinessConfig($site_id)
    {
        $config = new ConfigModel();
        $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'SHOP_STORE_BUSINESS' ] ]);
        //数据格式化
        $data = [
            //门店运营模式 shop：店铺整体运营  store：连锁门店运营模式
            'store_business' => $res[ 'data' ][ 'value' ][ 'store_business' ] ?? 'shop',
            //是否允许切换门店
            'is_allow_change' => $res[ 'data' ][ 'value' ][ 'is_allow_change' ] ?? 1,
            'confirm_popup_control' => $res[ 'data' ][ 'value' ][ 'confirm_popup_control' ] ?? 1, // 门店确认弹窗
            //门店控制权限
            'store_auth' => $res[ 'data' ][ 'value' ][ 'store_auth' ] ?? '',//'config,balance,point,coupon,adjust'
        ];
        $res[ 'data' ][ 'value' ] = $data;
        return $res;
    }

    /**
     * 门店结算相关设置
     */
    public function setStoreBusinessConfig($site_id, $data)
    {
        $config = new ConfigModel();
        $res = $config->setConfig($data, '门店功能设置', 1, [ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'SHOP_STORE_BUSINESS' ] ]);
        return $res;
    }

    /**
     * addSettlementCron 添加门店结算计划任务 默认为3 - 月
     */
    public function addSettlementCron($site_id)
    {
        $config = new ConfigModel();
        $config->setConfig([ 'period_type' => 3 ], '门店结算设置', 1, [ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'SHOP_STORE_WITHDRAW' ] ]);
        $cron = new Cron();
        $execute_time = Carbon::now()->addMonth()->firstOfMonth()->timestamp;
        $cron->deleteCron([ [ 'event', '=', 'StoreWithdrawPeriodCalc' ], [ 'relate_id', '=', $site_id ] ]);
        $res = $cron->addCron('2', '1', '门店周期结算', 'StoreWithdrawPeriodCalc', $execute_time, $site_id, 3);
        return $this->success($res);
    }
}
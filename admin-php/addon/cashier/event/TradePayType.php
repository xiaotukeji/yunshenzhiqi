<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace addon\cashier\event;


/**
 * 支付方式  (后台调用)
 */
class TradePayType
{
    /**
     * 支付方式及配置
     */
    public function handle($param)
    {

        $info = [
            'cash' => '现金支付',
            'own_wechatpay' => '个人微信',
            'own_alipay' => '个人支付宝',
            'own_pos' => '个人pos刷卡',
            'ONLINE_PAY' => '在线支付',
        ];
        return $info;
    }
}
<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */
return [
    'bind' => [

    ],

    'listen' => [
        'ShowPromotion' => [
            'addon\cashier\event\ShowPromotion'
        ],
        'CashierOrderPayNotify' => [
            'addon\cashier\event\CashierOrderPayNotify'
        ],
        'TradePayType' => [
            'addon\cashier\event\TradePayType'
        ],
        'PayRefund' => [
            'addon\cashier\event\PayRefund'
        ],
        'PrinterTemplateType' => [
            'addon\cashier\event\PrinterTemplateType',
        ],
        'PrinterHtml' => [
            'addon\cashier\event\PrinterHtml',
        ],
        'PrinterContent' => [
            'addon\cashier\event\PrinterContent',
        ],
        'GetOrderModel' => [
            'addon\cashier\event\GetOrderModel'
        ],
        'OrderGoodsRefund' => [
            'addon\cashier\event\OrderGoodsRefund'
        ],
        'OrderFromList' => [
            'addon\cashier\event\OrderFromList'
        ],
        'IncomeStatistics' => [
            'addon\cashier\event\IncomeStatistics'
        ],
        'CronOrderDelete' => [
            'addon\cashier\event\CronOrderDelete'
        ],
        'GetOrderType' => [
            'addon\cashier\event\GetOrderType'
        ],
        'OrderPay' => [
            'addon\cashier\event\OrderPay'
        ],
    ],

    'subscribe' => [
    ],
];

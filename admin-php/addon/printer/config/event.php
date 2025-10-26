<?php
// 事件定义文件
return [
    'bind' => [

    ],

    'listen' => [
        //展示活动
        'ShowPromotion' => [
            'addon\printer\event\ShowPromotion',
        ],

        'PromotionType' => [
            'addon\printer\event\PromotionType',
        ],

        //小票打印
        'PrintOrder' => [
            'addon\printer\event\PrintOrder',
        ],

        //模板
        'PrinterTemplate' => [
            'addon\printer\event\PrinterTemplate',
        ],

        //打印数据
        'PrinterContent' => [
            'addon\printer\event\PrinterContent',
        ],

        //订单支付
        'OrderPay' => [
            'addon\printer\event\OrderPay',
        ],
        //支付打印
        'OrderPayPrinter' => [
            'addon\printer\event\OrderPayPrinter',
        ],

        //订单收货
        'OrderTakeDeliveryAfter' => [
            'addon\printer\event\OrderTakeDeliveryAfter',
        ],
        //收货打印
        'OrderTakeDeliveryPrinter' => [
            'addon\printer\event\OrderTakeDeliveryPrinter',
        ],

        //充值
        'MemberRechargeOrderPay' => [
            'addon\printer\event\MemberRechargeOrderPay',
        ],
        'MemberRechargeOrderPayPrinter' => [
            'addon\printer\event\MemberRechargeOrderPayPrinter',
        ],

    ],

    'subscribe' => [
    ],
];

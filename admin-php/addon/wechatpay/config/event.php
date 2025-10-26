<?php
// 事件定义文件
return [
    'bind' => [

    ],

    'listen' => [
        //支付异步回调
        'PayNotify' => [
            'addon\wechatpay\event\PayNotify'
        ],
        //支付方式，后台查询
        'PayType' => [
            'addon\wechatpay\event\PayType'
        ],
        //支付，前台应用
        'Pay' => [
            'addon\wechatpay\event\Pay'
        ],
        'PayClose' => [
            'addon\wechatpay\event\PayClose'
        ],
        'PayRefund' => [
            'addon\wechatpay\event\PayRefund'
        ],
        'PayTransfer' => [
            'addon\wechatpay\event\PayTransfer'
        ],
        'TransferType' => [
            'addon\wechatpay\event\TransferType'
        ],
        'PayTransferResult' => [
            'addon\wechatpay\event\PayTransferResult'
        ],
        //付款码支付异步回调
        'AuthcodePay' => [
            'addon\wechatpay\event\AuthcodePay'
        ],
        'PayOrderQuery' => [
            'addon\wechatpay\event\PayOrderQuery'
        ],
    ],

    'subscribe' => [
    ],
];

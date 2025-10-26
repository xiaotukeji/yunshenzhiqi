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
            'addon\store\event\ShowPromotion'
        ],
        'OrderCreateAfter' => [
            'addon\store\event\OrderCreateAfter',
        ],
        //订单关闭
        'OrderClose' => [
            'addon\store\event\OrderClose',
        ],
        //订单完成
        'OrderComplete' => [
            'addon\store\event\OrderComplete',
            'addon\store\event\StoreOrderSettlementCalculate'
        ],
        //订单支付
        'OrderPayAfter' => [
            'addon\store\event\OrderPayAfter',
        ],
        //门店结算
        'StoreWithdrawPeriodCalc' => [
            'addon\store\event\StoreWithdrawPeriodCalc'
        ],
        //门店添加
        'AddStore' => [
            'addon\store\event\AddStore'
        ],
        /**
         * 商品编辑
         */
        'GoodsEdit' => [
            'addon\store\event\GoodsEdit'
        ],
        'PointExchangeOrderCreate' => [
            'addon\store\event\PointExchangeOrderCreate'
        ],
        'GoodsSkuStock' => [
            'addon\store\event\GoodsSkuStock'
        ],


        /**
         * 门店结算相关
         */
        //订单项计算
        'OrderGoodsCalculate' => [
            'addon\store\event\OrderGoodsCalculate'
        ],
        'OrderCreateCommonData' => [
            'addon\store\event\OrderCreateCommonData'
        ],
        'OrderRefundMoneyFinish' => [
            'addon\store\event\StoreOrderRefundSettlementCalculate'
        ],
        //转账结果通知
        'PayTransferNotify' => [
            'addon\store\event\WithdrawTransferNotify',
        ],
        'PayTransferCheck' => [
            'addon\store\event\WithdrawTransferCheck',
        ],
    ],

    'subscribe' => [
    ],
];

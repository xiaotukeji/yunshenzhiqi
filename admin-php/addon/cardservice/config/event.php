<?php
// 事件定义文件
return [
    'bind' => [

    ],
    'listen' => [
        //展示活动
        'ShowPromotion' => [
            'addon\cardservice\event\ShowPromotion',
        ],
        'GoodsClass' => [
            'addon\cardservice\event\ServiceGoodsClass',
            'addon\cardservice\event\CardGoodsClass'
        ],
        'Verify' => [
            'addon\cardservice\event\CardGoodsVerify',//卡项商品核销
        ],
        'VerifyType' => [
            'addon\cardservice\event\VerifyType',
        ],
        'CronMemberCardExpire' => [
            'addon\cardservice\event\CronMemberCardExpire'
        ],
        // 订单营销活动类型
        'OrderPromotionType' => [
            'addon\cardservice\event\OrderPromotionType',
        ],

        // 活动专区——秒杀页面配置
        'PromotionZoneConfig' => [
            'addon\cardservice\event\CardServiceZoneConfig',
        ],

        'MemberDetail' => [
            'addon\cardservice\event\MemberDetail',
        ],
        //退款完成
        'OrderRefundFinish' => [
            'addon\cardservice\event\OrderRefundFinish',
        ],
        //订单关闭
        'OrderClose' => [
            'addon\cardservice\event\OrderClose',
        ],
        'DeleteGoodsCheck' => [
            'addon\cardservice\event\DeleteGoodsCheck',
        ],
    ],
    'subscribe' => [
    ],
];

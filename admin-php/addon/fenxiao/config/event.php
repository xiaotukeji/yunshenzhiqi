<?php
// 事件定义文件
return [
    'bind' => [

    ],

    'listen' => [
        //展示活动
        'ShowPromotion' => [
            'addon\fenxiao\event\ShowPromotion',
        ],
        'PromotionType' => [
            'addon\fenxiao\event\PromotionType',
        ],
        'OrderComplete' => [
            'addon\fenxiao\event\OrderSettlement',
            'addon\fenxiao\event\OrderComplete'
        ],
        'OrderRefundFinish' => [
            'addon\fenxiao\event\OrderGoodsRefund',
        ],
        'AlterShareRelation' => [
            'addon\fenxiao\event\AlterShareRelation',
        ],
        'OrderCreateAfter' => [
            'addon\fenxiao\event\OrderCreateAfter',
        ],
        'PresaleOrderCreate' => [
            'addon\fenxiao\event\PresaleOrderCreate',
        ],
        'OrderPayAfter' => [
            'addon\fenxiao\event\OrderPayAfter',
        ],
        'MemberAccountFromType' => [
            'addon\fenxiao\event\MemberAccountFromType',
        ],

        'MemberRegister' => [
            'addon\fenxiao\event\MemberRegister',
        ],
        'FenxiaoUpgrade' => [
            'addon\fenxiao\event\FenxiaoUpgrade',
        ],
        'AddSite' => [
            'addon\fenxiao\event\AddSiteDiyView',//增加默认自定义数据：主页主页、商品分类、底部导航
            'addon\fenxiao\event\AddSiteFenxiaoLevel',//增加默认分销等级：普通分销商
        ],

        // 商品列表
        'GoodsListPromotion' => [
            'addon\fenxiao\event\GoodsListPromotion',
        ],
        // 商品分类
        'GoodsListCategoryIds' => [
            'addon\fenxiao\event\GoodsListCategoryIds',
        ],
        // 会员注销
        'MemberCancel' => [
            'addon\fenxiao\event\MemberCancel',
        ],

        //微信分享数据
        'WchatShareData' => [
            'addon\fenxiao\event\WchatShareData',
        ],
        //微信分享配置
        'WchatShareConfig' => [
            'addon\fenxiao\event\WchatShareConfig',
        ],
        //小程序分享数据
        'WeappShareData' => [
            'addon\fenxiao\event\WeappShareData',
        ],
        //小程序分享配置
        'WeappShareConfig' => [
            'addon\fenxiao\event\WeappShareConfig',
        ],

        //统计写入
        'AddStat' => [
            'addon\fenxiao\event\AddStat',
        ],
        //转账结果通知
        'PayTransferNotify' => [
            'addon\fenxiao\event\WithdrawTransferNotify',
        ],
        'PayTransferCheck' => [
            'addon\fenxiao\event\WithdrawTransferCheck',
        ],
    ],

    'subscribe' => [
    ],
];

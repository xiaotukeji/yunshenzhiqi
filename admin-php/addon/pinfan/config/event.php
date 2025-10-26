<?php
// 事件定义文件
return [
    'bind' => [

    ],

    'listen' => [
        //展示活动
        'ShowPromotion'      => [
            'addon\pinfan\event\ShowPromotion',
        ],
        //订单支付事件
        'OrderPay'           => [
            'addon\pinfan\event\OrderPay',
        ],
        'PromotionType'      => [
            'addon\pinfan\event\PromotionType',
        ],
        //开启拼团活动
        'OpenPinfan'        => [
            'addon\pinfan\event\OpenPinfan',
        ],
        //关闭拼团活动
        'ClosePinfan'       => [
            'addon\pinfan\event\ClosePinfan',
        ],
        //关闭拼团组
        'ClosePinfanGroup'  => [
            'addon\pinfan\event\ClosePinfanGroup',
        ],
        // 商品营销活动类型
        'GoodsPromotionType' => [
            'addon\pinfan\event\GoodsPromotionType',
        ],

        // 商品营销活动信息
        'GoodsPromotion'     => [
            'addon\pinfan\event\GoodsPromotion',
        ],

        // 商品列表
        'GoodsListPromotion' => [
            'addon\pinfan\event\GoodsListPromotion',
        ],
        // 商品分类
        'GoodsListCategoryIds' => [
            'addon\pinfan\event\GoodsListCategoryIds',
        ],

        // 订单营销活动类型
        'OrderPromotionType' => [
            'addon\pinfan\event\OrderPromotionType',
        ],
        #会员账户变化来源类型
        'MemberAccountFromType' => [
            'addon\pinfan\event\MemberAccountFromType',
        ]
    ],

    'subscribe' => [
    ],
];

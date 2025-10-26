<?php
// 事件定义文件
return [
    'bind' => [

    ],

    'listen' => [
        //展示活动
        'ShowPromotion'         => [
            'addon\replacebuy\event\ShowPromotion',
        ],
        // 订单营销活动类型
//        'OrderPromotionType' => [
//            'addon\replacebuy\event\OrderPromotionType',
//        ],
        'OrderFromList'         => [
            'addon\replacebuy\event\OrderFromList',
        ],
    ],

    'subscribe' => [
    ],
];

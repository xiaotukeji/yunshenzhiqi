<?php
// +----------------------------------------------------------------------
// | 平台端菜单设置
// +----------------------------------------------------------------------
return [
    [
        'name' => 'PROMOTION_REPLACEBUY',
        'title' => '代客下单',
        'url' => 'replacebuy://shop/replacebuy/index',
        'parent' => 'ORDER_ACTION',
        'is_show' => 1,
        'is_control' => 1,
        'is_icon' => 0,
        'picture' => '',
        'picture_select' => '',
        'sort' => 10,
        'child_list' => [
            [
                'name' => 'PROMOTION_REPLACEBUY_ORDER',
                'title' => '确认订单',
                'url' => 'replacebuy://shop/replacebuy/order',
                'sort' => 1,
                'is_show' => 0,
                'type' => 'button',
            ]
        ]
    ]

];

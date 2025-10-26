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
    [
        'name' => 'STORE_USER_GROUP',
        'title' => '门店角色',
        'url' => 'cashier://shop/user/group',
        'is_show' => 1,
        'is_control' => 1,
        'parent' => 'USER_AUTH',
        'is_icon' => 0,
        'picture' => '',
        'picture_selected' => '',
        'sort' => 3,
        'child_list' => [
            [
                'name' => 'STORE_USER_GROUP_ADD',
                'title' => '添加角色',
                'url' => 'cashier://shop/user/addgroup',
                'is_show' => 0,
                'is_control' => 1,
                'is_icon' => 0,
                'picture' => '',
                'picture_selected' => '',
                'sort' => 1,
                'type' => 'button',
            ],
            [
                'name' => 'STORE_USER_GROUP_EDIT',
                'title' => '角色编辑',
                'url' => 'cashier://shop/user/editgroup',
                'is_show' => 0,
                'is_control' => 1,
                'is_icon' => 0,
                'picture' => '',
                'picture_selected' => '',
                'sort' => 1,
                'type' => 'button',
            ],
            [
                'name' => 'STORE_USER_GROUP_DELETE',
                'title' => '角色删除',
                'url' => 'cashier://shop/user/deletegroup',
                'is_show' => 0,
                'is_control' => 1,
                'is_icon' => 0,
                'picture' => '',
                'picture_selected' => '',
                'sort' => 1,
                'type' => 'button',
            ],
            [
                'name' => 'STORE_USER_GROUP_MODIFY_STATUS',
                'title' => '调整角色状态',
                'url' => 'cashier://shop/user/modifygroupstatus',
                'is_show' => 0,
                'is_control' => 1,
                'is_icon' => 0,
                'picture' => '',
                'picture_selected' => '',
                'sort' => 1,
                'type' => 'button',
            ],
        ]
    ],
    [
        'name' => 'CASHIER',
        'title' => '收银',
        'url' => 'cashier://shop/index/cashier',
        'is_show' => 1,
        'is_control' => 0,
        'is_icon' => 0,
        'picture' => 'icon12',
        'picture_selected' => '',
        'sort' => 12
    ],
    [
        'name' => 'CASHIER_ORDER_LISTS',
        'title' => '收银订单',
        'url' => 'cashier://shop/order/lists',
        'parent' => 'ORDER_MANAGE',
        'is_show' => 1,
        'is_control' => 1,
        'sort' => 1,
        'is_icon' => 0,
        'picture' => '',
        'picture_selected' => '',
        'child_list' => [
            [
                'name' => 'CASHIER_ORDER_DETAIL',
                'title' => '订单详情',
                'url' => 'cashier://shop/order/detail',
                'sort' => 1,
                'is_show' => 0,
                'type' => 'button',
            ]
        ]
    ]
];

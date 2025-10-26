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
        'name' => 'store_settlement',
        'title' => '门店结算',
        'type' => 'page',
        'parent' => 'stat',
        'url' => 'pages/store/settlement',
        'children' => [
            [
                'name' => 'settlement_record',
                'title' => '结算记录',
                'type' => 'page',
                'url' => 'pages/store/settlement_record',
            ],
            [
                'name' => 'settlement_apply',
                'title' => '申请结算',
                'type' => 'api',
                'url' => 'store/storeapi/withdraw/apply',
            ]
        ]
    ],
    [
        'name' => 'reserve_manage',
        'title' => '预约',
        'type' => 'page',
        'url' => '',
        'children' => [
            [
                'name' => 'reserve_index',
                'title' => '查看',
                'type' => 'page',
                'url' => 'pages/reserve/index',
            ],
            [
                'name' => 'reserve_add',
                'title' => '添加预约',
                'type' => 'api',
                'url' => 'store/storeapi/reserve/add',
            ],
            [
                'name' => 'reserve_edit',
                'title' => '修改预约',
                'type' => 'api',
                'url' => 'store/storeapi/reserve/update',
            ],
            [
                 'name' => 'reserve_confirm',
                'title' => '确认预约',
                'type' => 'api',
                'url' => 'store/storeapi/reserve/confirm',
            ],
            [
                'name' => 'reserve_complete',
                'title' => '完成预约',
                'type' => 'api',
                'url' => 'store/storeapi/reserve/complete',
            ],
            [
                'name' => 'reserve_cancel',
                'title' => '取消预约',
                'type' => 'api',
                'url' => 'store/storeapi/reserve/cancel',
            ],
            [
                'name' => 'reserve_confirm_tostore',
                'title' => '确认到店',
                'type' => 'api',
                'url' => 'store/storeapi/reserve/confirmtostore',
            ]
        ]
    ],
    [
        'name' => 'store_config_root',
        'title' => '门店设置',
        'type' => 'page',
        'url' => 'pages/store/index',
        'parent' => 'config',
        'children' => [
            [
                'name' => 'store_config',
                'title' => '门店设置',
                'type' => 'page',
                'url' => 'pages/store/config',
            ],
            [
                'name' => 'store_operate_config',
                'title' => '运营设置',
                'type' => 'page',
                'url' => 'pages/store/operate',
            ]
        ]
    ],
    [
        'name' => 'reserve_config',
        'title' => '预约设置',
        'type' => 'page',
        'url' => 'pages/reserve/config',
        'parent' => 'config',
        'children' => [
            [
                'name' => 'set_reserve_config',
                'title' => '配置',
                'type' => 'api',
                'url' => 'store/storeapi/reserve/setconfig',
            ],
        ]
    ],
    [
        'name' => 'printer_config',
        'title' => '小票打印',
        'type' => 'page',
        'url' => 'pages/printer/list',
        'parent' => 'config',
        'children' => [
            [
                'name' => 'add_printer',
                'title' => '添加打印机',
                'type' => 'api',
                'url' => 'printer/storeapi/printer/add',
            ],
            [
                'name' => 'edit_printer',
                'title' => '编辑打印机',
                'type' => 'api',
                'url' => 'printer/storeapi/printer/edit',
            ],
            [
                'name' => 'delete_printer',
                'title' => '删除打印机',
                'type' => 'api',
                'url' => 'printer/storeapi/printer/deleteprinter',
            ],
        ]
    ],
    [
        'name' => 'store_deliver_config',
        'title' => '配送员',
        'type' => 'page',
        'url' => 'pages/store/deliver',
        'parent' => 'config',
        'children' => [
            [
                'name' => 'add_deliver',
                'title' => '添加配送员',
                'type' => 'api',
                'url' => 'printer/storeapi/printer/adddeliver',
            ],
            [
                'name' => 'edit_deliver',
                'title' => '编辑配送员',
                'type' => 'api',
                'url' => 'printer/storeapi/printer/editdeliver',
            ],
            [
                'name' => 'delete_deliver',
                'title' => '删除配送员',
                'type' => 'api',
                'url' => 'cashier/storeapi/store/deletedeliver',
            ],
        ]
    ],
];

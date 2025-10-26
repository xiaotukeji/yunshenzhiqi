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
        'name' => 'stock',
        'title' => '库存',
        'type' => 'page',
        'url' => '',
        'children' => [
            [
                'name' => 'stock_wastage',
                'title' => '出库单',
                'type' => 'page',
                'url' => 'pages/stock/wastage',
            ],
            [
                'name' => 'stock_storage',
                'title' => '入库单',
                'type' => 'page',
                'url' => 'pages/stock/storage',
            ],
            [
                'name' => 'stock_allocate',
                'title' => '调拨单',
                'type' => 'page',
                'url' => 'pages/stock/allocate',
            ],
            [
                'name' => 'stock_manage',
                'title' => '库存管理',
                'type' => 'page',
                'url' => 'pages/stock/manage',
            ],
            [
                'name' => 'stock_check',
                'title' => '库存盘点',
                'type' => 'page',
                'url' => 'pages/stock/check',
            ],
            [
                'name' => 'stock_records',
                'title' => '库存流水',
                'type' => 'page',
                'url' => 'pages/stock/records',
            ],
            [
                'name' => 'storage_add',
                'title' => '创建入库单',
                'type' => 'api',
                'url' => 'stock/shopapi/storage/stockin',
            ],
            [
                'name' => 'wastage_add',
                'title' => '创建出库单',
                'type' => 'api',
                'url' => 'stock/shopapi/wastage/stockout',
            ],
            [
                'name' => 'check_add',
                'title' => '创建盘点单',
                'type' => 'api',
                'url' => 'stock/shopapi/check/add',
            ],
            [
                'name' => 'stock_audit',
                'title' => '单据审核',
                'type' => 'api',
                'url' => 'stock/shopapi/manage/audit',
            ]
        ]
    ]
];

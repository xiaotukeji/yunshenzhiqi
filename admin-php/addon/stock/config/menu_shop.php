<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 上海牛之云网络科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */
return [
    [
        'name' => 'STOCK_LIST_INDEX',
        'title' => '库存管理',
        'url' => 'stock://shop/stock/manage',
        'is_show' => 1,
        'is_control' => 1,
        'parent' => 'GOODS_ROOT',
        'is_icon' => 0,
        'picture' => '',
        'picture_select' => '',
        'sort' => 3,
        'child_list' => [
            [
                'name' => 'STOCK_LIST',
                'title' => '库存管理',
                'url' => 'stock://shop/stock/manage',
                'is_show' => 1,
                'sort' => 1,
                'child_list' => [
                    [
                        'name' => 'STOCK_RECORDS',
                        'title' => '库存流水',
                        'url' => 'stock://shop/stock/records',
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'STOCK_GOODS_EXPORT_LIST',
                        'title' => '商品导出记录',
                        'url' => 'stock://shop/stock/export',
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                ]
            ],
            [
                'name' => 'STOCK_INPUT',
                'title' => '入库管理',
                'url' => 'stock://shop/stock/storage',
                'is_show' => 1,
                'sort' => 2,
                'child_list' => [
                    [
                        'name' => 'STOCK_INPUT_DETAIL',
                        'title' => '入库单详情',
                        'url' => 'stock://shop/stock/inputdetail',
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'STOCK_INPUT_ADD',
                        'title' => '添加入库单',
                        'url' => 'stock://shop/stock/stockin',
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                ]
            ],
            [
                'name' => 'STOCK_OUTPUT',
                'title' => '出库管理',
                'url' => 'stock://shop/stock/wastage',
                'is_show' => 1,
                'sort' => 3,
                'child_list' => [
                    [
                        'name' => 'STOCK_OUTPUT_ADD',
                        'title' => '添加出库单',
                        'url' => 'stock://shop/stock/stockout',
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'STOCK_OUTPUT_DETAIL',
                        'title' => '出库单详情',
                        'url' => 'stock://shop/stock/outputdetail',
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                ]
            ],
            [
                'name' => 'STOCK_ALLOT',
                'title' => '库存调拨',
                'url' => 'stock://shop/stock/allocate',
                'is_show' => 1,
                'sort' => 4,
                'child_list' => [
                    [
                        'name' => 'STOCK_ALLOT_RECORDS',
                        'title' => '调拨流水',
                        'url' => 'stock://shop/stock/allotrecords',
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'STOCK_ALLOT_ADD',
                        'title' => '调拨',
                        'url' => 'stock://shop/stock/editallocate',
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                ]
            ],
            [
                'name' => 'STOCK_INVENTORY',
                'title' => '库存盘点',
                'url' => 'stock://shop/stock/check',
                'is_show' => 1,
                'sort' => 5,
                'child_list' => [
                    [
                        'name' => 'STOCK_INVENTORY_EDIT',
                        'title' => '添加盘点单',
                        'url' => 'stock://shop/stock/editcheck',
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'STOCK_INVENTORY_DETAIL',
                        'title' => '盘点单详情',
                        'url' => 'stock://shop/stock/inventorydetail',
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'STOCK_INVENTORY_AGREE',
                        'title' => '盘点单审核通过',
                        'url' => 'stock://shop/stock/agree',
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'STOCK_INVENTORY_REFUSE',
                        'title' => '盘点单审核拒绝',
                        'url' => 'stock://shop/stock/refuse',
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'STOCK_INVENTORY_DELETE',
                        'title' => '删除盘点单',
                        'url' => 'stock://shop/stock/delete',
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                ]
            ],
            [
                'name' => 'STOCK_SET',
                'title' => '库存设置',
                'url' => 'stock://shop/stock/config',
                'is_show' => 1,
                'sort' => 6,
            ],
            [
                'name' => 'STOCK_INVENTORY_DOCUMENT_AGREE',
                'title' => '单据审核通过',
                'url' => 'stock://shop/stock/agree',
                'is_show' => 0,
                'sort' => 7,
                'type' => 'button',
            ],
            [
                'name' => 'STOCK_INVENTORY_DOCUMENT_REFUSE',
                'title' => '单据审核拒绝',
                'url' => 'stock://shop/stock/refuse',
                'is_show' => 0,
                'sort' => 8,
                'type' => 'button',
            ],
            [
                'name' => 'STOCK_INVENTORY_DOCUMENT_DELETE',
                'title' => '删除单据',
                'url' => 'stock://shop/stock/delete',
                'is_show' => 0,
                'sort' => 9,
                'type' => 'button',
            ],
            [
                'name' => 'STOCK_TRANSFORM',
                'title' => '库存转换',
                'url' => 'stock://shop/transform/lists',
                'is_show' => 1,
                'sort' => 7,
                'child_list' => [
                    [
                        'name' => 'STOCK_TRANSFORM_ADD',
                        'title' => '添加',
                        'url' => 'stock://shop/transform/add',
                        'is_show' => 0,
                        'sort' => 1,
                    ],
                    [
                        'name' => 'STOCK_TRANSFORM_EDIT',
                        'title' => '编辑',
                        'url' => 'stock://shop/transform/edit',
                        'is_show' => 0,
                        'sort' => 2,
                    ],
                    [
                        'name' => 'STOCK_TRANSFORM_DELETE',
                        'title' => '删除',
                        'url' => 'stock://shop/transform/delete',
                        'is_show' => 0,
                        'sort' => 3,
                    ],
                ]
            ],
        ]
    ],
];

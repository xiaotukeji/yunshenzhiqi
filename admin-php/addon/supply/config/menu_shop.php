<?php
// +----------------------------------------------------------------------
// | 平台端菜单设置
// +----------------------------------------------------------------------
return [
    [
        'name' => 'SUPPLY_MANAGE',
        'title' => '供应商',
        'url' => 'supply://shop/supplier/lists',
        'parent' => 'GOODS_MANAGE',
        'is_show' => 1,
        'is_control' => 1,
        'picture' => '',
        'picture_selected' => '',
        'sort' => 12,
        'child_list' => [
            [
                'name' => 'ADDON_SUPPLY_ADD',
                'title' => '添加供应商',
                'url' => 'supply://shop/supplier/add',
                'is_show' => 0,
                'type' => 'button',
            ],
            [
                'name' => 'ADDON_SUPPLY_EDIT',
                'title' => '编辑供应商',
                'url' => 'supply://shop/supplier/edit',
                'is_show' => 0,
                'type' => 'button',
            ],
            [
                'name' => 'ADDON_SUPPLY_DELETE',
                'title' => '删除供应商',
                'url' => 'supply://shop/supplier/delete',
                'is_show' => 0,
                'type' => 'button',
            ],
        ],
    ],
];

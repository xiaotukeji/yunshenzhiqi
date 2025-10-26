<?php
// +----------------------------------------------------------------------
// | 店铺端菜单设置
// +----------------------------------------------------------------------
return [
    [
        'name' => 'PROMOTION_PRINTER',
        'title' => '小票打印',
        'url' => 'printer://shop/printer/lists',
        'parent' => 'PROMOTION_TOOL',
        'picture' => 'addon/printer/shop/view/public/img/distribution_new.png',
        'picture_selected' => 'addon/printer/shop/view/public/img/distribution_select.png',
        'is_show' => 1,
        'sort' => 1,
        'child_list' => [
            [
                'name' => 'PROMOTION_PRINTER_LIST',
                'title' => '打印机管理',
                'url' => 'printer://shop/printer/lists',
                'is_show' => 1,
                'sort' => 2,
                'child_list' => [
                    [
                        'name' => 'PROMOTION_PRINTER_ADD',
                        'title' => '添加打印机',
                        'url' => 'printer://shop/printer/add',
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PROMOTION_PRINTER_EDIT',
                        'title' => '编辑打印机',
                        'url' => 'printer://shop/printer/edit',
                        'sort' => 1,
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PROMOTION_PRINTER_DELETE',
                        'title' => '删除打印机',
                        'url' => 'printer://shop/printer/delete',
                        'sort' => 1,
                        'is_show' => 0,
                        'type' => 'button',
                    ]
                ]
            ],
            [
                'name' => 'PROMOTION_TEMPLATE_LIST',
                'title' => '打印模板',
                'url' => 'printer://shop/template/lists',
                'is_show' => 1,
                'sort' => 2,
                'child_list' => [
                    [
                        'name' => 'PROMOTION_TEMPLATE_ADD',
                        'title' => '添加模板',
                        'url' => 'printer://shop/template/add',
                        'sort' => 1,
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PROMOTION_TEMPLATE_EDIT',
                        'title' => '编辑模板',
                        'url' => 'printer://shop/template/edit',
                        'sort' => 2,
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PROMOTION_TEMPLATE_DELETE',
                        'title' => '删除模板',
                        'url' => 'printer://shop/template/delete',
                        'sort' => 3,
                        'is_show' => 0,
                        'type' => 'button',
                    ]
                ]
            ],
        ]
    ],

];
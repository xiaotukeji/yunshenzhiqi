<?php
// +----------------------------------------------------------------------
// | 店铺端菜单设置
// +----------------------------------------------------------------------
return [

    [
        'name' => 'PROMOTION_PINFAN',
        'title' => '拼团返利',
        'url' => 'pinfan://shop/pinfan/lists',
        'parent' => 'PROMOTION_CENTER',
        'is_show' => 1,
        'sort' => 100,
        'child_list' => [
            [
                'name' => 'PROMOTION_PINFAN_LIST',
                'title' => '拼团返利商品',
                'url' => 'pinfan://shop/pinfan/lists',
                'parent' => 'PROMOTION_PINFAN',
                'is_show' => 1,
                'child_list' => [
                    [
                        'name' => 'PROMOTION_PINFAN_ADD',
                        'title' => '添加商品',
                        'url' => 'pinfan://shop/pinfan/add',
                        'sort' => 1,
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PROMOTION_PINFAN_EDIT',
                        'title' => '编辑商品',
                        'url' => 'pinfan://shop/pinfan/edit',
                        'sort' => 1,
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PROMOTION_PINFAN_DETAIL',
                        'title' => '商品详情',
                        'url' => 'pinfan://shop/pinfan/detail',
                        'sort' => 1,
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PROMOTION_PINFAN_DELETE',
                        'title' => '删除商品',
                        'url' => 'pinfan://shop/pinfan/delete',
                        'sort' => 1,
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PROMOTION_PINFAN_INVALID',
                        'title' => '结束活动',
                        'url' => 'pinfan://shop/pinfan/invalid',
                        'sort' => 1,
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                ]
            ],
            [
                'name' => 'PROMOTION_PINFAN_GROUP',
                'title' => '拼团返利列表',
                'url' => 'pinfan://shop/pinfan/group',
                'parent' => 'PROMOTION_PINFAN',
                'is_show' => 1,
                'child_list' => [
                    [
                        'name' => 'PROMOTION_PINFAN_GROUP_ORDER',
                        'title' => '拼团返利组订单列表',
                        'url' => 'pinfan://shop/pinfan/groupOrder',
                        'sort' => 1,
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                ]
            ],
        ]
    ],

];
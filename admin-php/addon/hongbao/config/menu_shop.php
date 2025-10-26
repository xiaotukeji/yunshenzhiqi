<?php
// +----------------------------------------------------------------------
// | 平台端菜单设置
// +----------------------------------------------------------------------
return [
    [
        'name' => 'PROMOTION_HONGBAO',
        'title' => '裂变红包',
        'url' => 'hongbao://shop/hongbao/lists',
        'parent' => 'PROMOTION_CENTER',
        'is_show' => 1,
        'is_control' => 1,
        'is_icon' => 0,
        'picture' => '',
        'picture_select' => '',
        'sort' => 100,
        'child_list' => [
            [
                'name' => 'PROMOTION_HONGBAO_OPERATE',
                'title' => '运营',
                'url' => 'hongbao://shop/hongbao/operate',
                'sort' => 1,
                'is_show' => 0,
                'type' => 'button',
                'child_list' => [
                    [
                        'name' => 'HONGBAO_GROUP_PREFECTURE',
                        'title' => '邀请记录',
                        'is_show' => '',
                        'url' => 'hongbao://shop/hongbao/groupmember',
                        'sort' => 0,
                        'type' => 'button',
                    ],
                ]
            ],
            [
                'name' => 'PROMOTION_HONGBAO_ADD',
                'title' => '添加活动',
                'url' => 'hongbao://shop/hongbao/add',
                'sort' => 2,
                'is_show' => 0,
                'type' => 'button',
            ],
            [
                'name' => 'PROMOTION_HONGBAO_EDIT',
                'title' => '编辑活动',
                'url' => 'hongbao://shop/hongbao/edit',
                'sort' => 3,
                'is_show' => 0,
                'type' => 'button',
            ],
            [
                'name' => 'PROMOTION_HONGBAO_CLOSE',
                'title' => '关闭活动',
                'url' => 'hongbao://shop/hongbao/close',
                'sort' => 4,
                'is_show' => 0,
                'type' => 'button',
            ],
            [
                'name' => 'PROMOTION_HONGBAO_DELETE',
                'title' => '删除活动',
                'url' => 'hongbao://shop/hongbao/delete',
                'sort' => 5,
                'is_show' => 0,
                'type' => 'button',
            ],
            [
                'name' => 'PROMOTION_HONGBAO_DETAIL',
                'title' => '详情',
                'url' => 'hongbao://shop/hongbao/detail',
                'sort' => 6,
                'is_show' => 0
            ]
        ]
    ]
];

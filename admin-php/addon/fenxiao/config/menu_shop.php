<?php
// +----------------------------------------------------------------------
// | 店铺端菜单设置
// +----------------------------------------------------------------------
return [
    [
        'name' => 'PROMOTION_FENXIAO',
        'title' => '分销管理',
        'url' => 'fenxiao://shop/fenxiao/index',
        'parent' => 'PROMOTION_CENTER',
        'picture' => 'addon/fenxiao/shop/view/public/img/distribution_new.png',
        'picture_selected' => 'addon/fenxiao/shop/view/public/img/distribution_select.png',
        'is_show' => 1,
        'sort' => 100,
        'child_list' => [
            [
                'name' => 'PROMOTION_FENXIAO_INDEX',
                'title' => '分销概况',
                'url' => 'fenxiao://shop/fenxiao/index',
                'is_show' => 1,
                'sort' => 1,
                'child_list' => []
            ],
            [
                'name' => 'PROMOTION_FENXIAO_ROOT',
                'title' => '分销商',
                'url' => 'fenxiao://shop/fenxiao/lists',
                'is_show' => 1,
                'is_control' => 1,
                'sort' => 2,
                'child_list' => [
                    [
                        'name' => 'PROMOTION_FENXIAO_DETAIL',
                        'title' => '分销商信息',
                        'url' => 'fenxiao://shop/fenxiao/detail',
                        'is_show' => 0,
                        'is_control' => 1,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PROMOTION_FENXIAO_TEAM',
                        'title' => '分销商团队',
                        'url' => 'fenxiao://shop/fenxiao/team',
                        'is_show' => 0,
                        'is_control' => 1,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PROMOTION_FENXIAO_ACCOUNT',
                        'title' => '账户明细',
                        'url' => 'fenxiao://shop/fenxiao/account',
                        'is_show' => 0,
                        'is_control' => 1,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PROMOTION_FENXIAO_ORDERMANAGE',
                        'title' => '订单管理',
                        'url' => 'fenxiao://shop/fenxiao/order',
                        'is_show' => 0,
                        'is_control' => 1,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PROMOTION_FENXIAO_ORDERMANAGEDETAIL',
                        'title' => '订单详情',
                        'url' => 'fenxiao://shop/fenxiao/orderdetail',
                        'is_show' => 0,
                        'is_control' => 1,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PROMOTION_FENXIAO_ADD',
                        'title' => '添加分销商',
                        'url' => 'fenxiao://shop/fenxiao/add',
                        'is_show' => 0,
                        'is_control' => 1,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PROMOTION_FENXIAO_FROZEN',
                        'title' => '冻结',
                        'url' => 'fenxiao://shop/fenxiao/frozen',
                        'is_show' => 0,
                        'is_control' => 1,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PROMOTION_FENXIAO_UNFROZEN',
                        'title' => '恢复正常',
                        'url' => 'fenxiao://shop/fenxiao/unfrozen',
                        'is_show' => 0,
                        'is_control' => 1,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PROMOTION_FENXIAO_CHANGE_LEVEL',
                        'title' => '变更上级分销商',
                        'url' => 'fenxiao://shop/fenxiao/confirmChange',
                        'is_show' => 0,
                        'is_control' => 1,
                        'type' => 'button',
                    ],
                ],
            ],
            [
                'name' => 'PROMOTION_FENXIAO_APPLY',
                'title' => '分销商申请',
                'url' => 'fenxiao://shop/fenxiao/apply',
                'is_show' => 1,
                'sort' => 3,
                'child_list' => [
                    [
                        'name' => 'PROMOTION_FENXIAO_PASS',
                        'title' => '审核通过',
                        'url' => 'fenxiao://shop/fenxiao/pass',
                        'is_show' => 0,
                        'is_control' => 1,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PROMOTION_FENXIAO_REFUSE',
                        'title' => '审核拒绝',
                        'url' => 'fenxiao://shop/fenxiao/refuse',
                        'is_show' => 0,
                        'is_control' => 1,
                        'type' => 'button',
                    ],
                ],
            ],
            [
                'name' => 'PROMOTION_FENXIAO_GOODS_LIST',
                'title' => '分销商品',
                'url' => 'fenxiao://shop/goods/lists',
                'is_show' => 1,
                'sort' => 4,
                'child_list' => [
                    [
                        'name' => 'PROMOTION_FENXIAO_GOODS_DETAIL',
                        'title' => '商品详情',
                        'url' => 'fenxiao://shop/goods/detail',
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PROMOTION_FENXIAO_GOODS_CONFIG',
                        'title' => '商品设置',
                        'url' => 'fenxiao://shop/goods/config',
                        'sort' => 1,
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PROMOTION_FENXIAO_GOODS_MODIFY',
                        'title' => '状态设置',
                        'url' => 'fenxiao://shop/goods/modify',
                        'sort' => 1,
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PROMOTION_FENXIAO_SET_GOODS_IS_FENXIAO',
                        'title' => '是否参与分销',
                        'url' => 'fenxiao://shop/goods/setGoodsIsFenxiao',
                        'sort' => 1,
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                ]
            ],
            [
                'name' => 'PROMOTION_FENXIAO_ORDER',
                'title' => '分销订单',
                'url' => 'fenxiao://shop/order/lists',
                'is_show' => 1,
                'sort' => 5,
                'child_list' => [
                    [
                        'name' => 'PROMOTION_FENXIAO_ORDER_DETAIL',
                        'title' => '订单详情',
                        'url' => 'fenxiao://shop/order/detail',
                        'sort' => 1,
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                ]
            ],
            [
                'name' => 'PROMOTION_FENXIAO_LEVEL',
                'title' => '分销等级',
                'url' => 'fenxiao://shop/level/lists',
                'is_show' => 1,
                'is_control' => 1,
                'sort' => 6,
                'child_list' => [
                    [
                        'name' => 'PROMOTION_FENXIAO_LEVEL_ADD',
                        'title' => '添加等级',
                        'url' => 'fenxiao://shop/level/add',
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PROMOTION_FENXIAO_LEVEL_EDIT',
                        'title' => '编辑等级',
                        'url' => 'fenxiao://shop/level/edit',
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PROMOTION_FENXIAO_LEVEL_STATUS',
                        'title' => '等级状态设置',
                        'url' => 'fenxiao://shop/level/status',
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PROMOTION_FENXIAO_LEVEL_DELETE',
                        'title' => '删除等级',
                        'url' => 'fenxiao://shop/level/delete',
                        'is_show' => 0,
                        'type' => 'button',
                    ]
                ]
            ],
            [
                'name' => 'PROMOTION_FENXIAO_CONFIG',
                'title' => '分销设置',
                'url' => 'fenxiao://shop/config/basics',
                'is_show' => 1,
                'is_control' => 1,
                'sort' => 7,
                'child_list' => [
                    [
                        'name' => 'PROMOTION_FENXIAO_BASICS',
                        'title' => '基础设置',
                        'url' => 'fenxiao://shop/config/basics',
                        'is_show' => 1,
                    ],
                    [
                        'name' => 'PROMOTION_FENXIAO_AGREEMENT',
                        'title' => '申请协议',
                        'url' => 'fenxiao://shop/config/agreement',
                        'is_show' => 1,
                    ],
                    [
                        'name' => 'PROMOTION_FENXIAO_SETTLEMENT',
                        'title' => '提现设置',
                        'url' => 'fenxiao://shop/config/settlement',
                        'is_show' => 1,
                    ],
                    [
                        'name' => 'PROMOTION_FENXIAO_WORDS',
                        'title' => '文字设置',
                        'url' => 'fenxiao://shop/config/words',
                        'is_show' => 1,
                    ],
                    [
                        'name' => 'PROMOTION_FENXIAO_PEOMOTE_RULE',
                        'title' => '推广活动',
                        'url' => 'fenxiao://shop/config/promoterule',
                        'is_show' => 1,
                    ]
                ]
            ],
            [
                'name' => 'PROMOTION_FENXIAO_POSTER_TEMPLATE',
                'title' => '分销海报',
                'url' => 'fenxiao://shop/postertemplate/lists',
                'is_show' => 1,
                'is_control' => 1,
                'sort' => 11,
                'child_list' => [
                    [
                        'name' => 'PROMOTION_FENXIAO_POSTER_TEMPLATE_ADD',
                        'title' => '添加海报',
                        'url' => 'fenxiao://shop/postertemplate/addpostertemplate',
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PROMOTION_FENXIAO_POSTER_TEMPLATE_EDIT',
                        'title' => '编辑海报',
                        'url' => 'fenxiao://shop/postertemplate/editpostertemplate',
                        'is_show' => 0,
                        'type' => 'button',
                    ]
                ]
            ],

        ]
    ],
    [
        'name' => 'PROMOTION_FENXIAO_WITHDRAW',
        'title' => '分销提现',
        'url' => 'fenxiao://shop/withdraw/lists',
        'parent' => 'ACCOUNT_MANAGE',
        'picture' => 'app/shop/view/public/img/icon_new/member_withdraw_new.png',
        'picture_selected' => 'app/shop/view/public/img/icon_new/member_withdraw_select.png',
        'is_show' => 1,
        'is_control' => 1,
        'sort' => 4,
        'child_list' => [
            [
                'name' => 'PROMOTION_FENXIAO_WITHDRAW_DETAIL',
                'title' => '佣金提现详情',
                'url' => 'fenxiao://shop/withdraw/detail',
                'is_show' => 0,
                'type' => 'button',
            ],
            [
                'name' => 'PROMOTION_FENXIAO_WITHDRAW_PASS',
                'title' => '审核通过',
                'url' => 'fenxiao://shop/withdraw/withdrawpass',
                'is_show' => 0,
                'type' => 'button',
            ],
            [
                'name' => 'PROMOTION_FENXIAO_WITHDRAW_REFUSE',
                'title' => '审核拒绝',
                'url' => 'fenxiao://shop/withdraw/withdrawrefuse',
                'is_show' => 0,
                'type' => 'button',
            ],

        ],

    ],

];
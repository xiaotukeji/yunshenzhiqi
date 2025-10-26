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
        'name' => 'cashier',
        'title' => '收银',
        'type' => 'page',
        'url' => '',
        'children' => [
            [
                'name' => 'billing',
                'title' => '开单',
                'type' => 'page',
                'url' => 'pages/billing/index',
            ],
            [
                'name' => 'buycard',
                'title' => '售卡',
                'type' => 'page',
                'url' => 'pages/buycard/index',
            ],
            [
                'name' => 'recharge',
                'title' => '充值',
                'type' => 'page',
                'url' => 'pages/recharge/index',
            ]
        ]
    ],
    [
        'name' => 'goods_manage',
        'title' => '商品管理',
        'type' => 'page',
        'url' => '',
        'children' => [
            [
                'name' => 'goods_list',
                'title' => '查看',
                'type' => 'page',
                'url' => 'pages/goods/goodslist',
            ],
            [
                'name' => 'goods_set_status',
                'title' => '上下架',
                'type' => 'api',
                'url' => 'cashier/storeapi/goods/setstatus',
            ],
            [
                'name' => 'goods_edit',
                'title' => '库存价格修改',
                'type' => 'api',
                'url' => 'cashier/storeapi/goods/editgoods',
            ]
        ]
    ],
    [
        'name' => 'member_manage',
        'title' => '会员管理',
        'type' => 'page',
        'url' => '',
        'children' => [
            [
                'name' => 'member_list',
                'title' => '查看',
                'type' => 'page',
                'url' => 'pages/member/list',
            ],
            [
                'name' => 'member_add',
                'title' => '添加客户',
                'type' => 'api',
                'url' => 'cashier/storeapi/member/addmember',
            ],
            [
                'name' => 'member_edit',
                'title' => '客户信息编辑',
                'type' => 'api',
                'url' => 'cashier/storeapi/member/editmember',
            ],
            [
                'name' => 'member_send_coupon',
                'title' => '发放优惠券',
                'type' => 'api',
                'url' => 'cashier/storeapi/member/sendcoupon',
            ],
            [
                'name' => 'member_modify_point',
                'title' => '调整积分',
                'type' => 'api',
                'url' => 'cashier/storeapi/member/modifypoint',
            ],
            [
                'name' => 'member_modify_balance',
                'title' => '调整余额',
                'type' => 'api',
                'url' => 'cashier/storeapi/member/modifybalance',
            ],
            [
                'name' => 'member_modify_growth',
                'title' => '调整成长值',
                'type' => 'api',
                'url' => 'cashier/storeapi/member/modifygrowth',
            ],
            [
                'name' => 'member_handle',
                'title' => '办理会员',
                'type' => 'api',
                'url' => 'cashier/storeapi/member/handlemember',
            ],
        ]
    ],
    [
        'name' => 'order_manage',
        'title' => '订单管理',
        'type' => 'page',
        'url' => '',
        'children' => [
            [
                'name' => 'order_list',
                'title' => '查看',
                'type' => 'page',
                'url' => 'pages/order/orderlist',
            ],
            [
                'name' => 'order_store_delivery',
                'title' => '订单自提',
                'type' => 'api',
                'url' => '/cashier/storeapi/order/storedelivery',
            ],
            [
                'name' => 'order_local_delivery',
                'title' => '同城配送',
                'type' => 'api',
                'url' => '/cashier/storeapi/order/localdelivery',
            ],
            [
                'name' => 'order_refund',
                'title' => '退款',
                'type' => 'api',
                'url' => '/cashier/storeapi/cashierorderrefund/refund',
            ],
        ]
    ],
    [
        'name' => 'refund_manage',
        'title' => '退款维权',
        'type' => 'page',
        'url' => '',
        'children' => [
            [
                'name' => 'order_refund_list',
                'title' => '查看',
                'type' => 'page',
                'url' => 'pages/order/orderrefund',
            ]
        ]
    ],
    [
        'name' => 'verify_manage',
        'title' => '核销',
        'type' => 'page',
        'url' => '',
        'children' => [
            [
                'name' => 'verify_index',
                'title' => '核销台',
                'type' => 'page',
                'url' => 'pages/verify/index',
            ],
            [
                'name' => 'verify_record',
                'title' => '核销记录',
                'type' => 'page',
                'url' => 'pages/verify/list',
            ],
            [
                'name' => 'verify_code_info',
                'title' => '查询核销码',
                'type' => 'api',
                'url' => 'cashier/storeapi/verify/info',
            ],
            [
                'name' => 'verify',
                'title' => '核销',
                'type' => 'api',
                'url' => 'cashier/storeapi/verify/verify',
            ]
        ]
    ],
    [
        'name' => 'change_shifts_record',
        'title' => '交班记录',
        'type' => 'page',
        'url' => '',
        'children' => [
            [
                'name' => 'change_shifts_record_list',
                'title' => '查看',
                'type' => 'page',
                'url' => 'pages/index/change_shiftsrecord',
            ]
        ]
    ],
    [
        'name' => 'user_manage',
        'title' => '员工管理',
        'type' => 'page',
        'url' => '',
        'children' => [
            [
                'name' => 'user_list',
                'title' => '查看',
                'type' => 'page',
                'url' => 'pages/user/list',
            ],
            [
                'name' => 'user_add',
                'title' => '添加员工',
                'type' => 'api',
                'url' => 'cashier/shopapi/user/adduser',
            ],
            [
                'name' => 'user_edit',
                'title' => '编辑员工',
                'type' => 'api',
                'url' => 'cashier/shopapi/user/edituser',
            ],
            [
                'name' => 'user_delete',
                'title' => '删除员工',
                'type' => 'api',
                'url' => 'cashier/shopapi/user/deleteuser',
            ]
        ]
    ],
    [
        'name' => 'stat',
        'title' => '数据',
        'type' => 'page',
        'url' => '',
        'children' => []
    ],
    [
        'name' => 'config',
        'title' => '设置',
        'type' => 'page',
        'url' => '',
        'children' => [
            [
                'name' => 'collectmoney_config_root',
                'title' => '收款设置',
                'type' => '',
                'url' => '',
                'children' => [
                    [
                        'name' => 'collectmoney_config',
                        'title' => '查看',
                        'type' => 'page',
                        'url' => 'pages/collectmoney/config',
                    ],
                    [
                        'name' => 'set_collectmoney_config',
                        'title' => '配置收款设置',
                        'type' => 'api',
                        'url' => 'cashier/storeapi/cashier/setcashiercollectmoneyconfig',
                    ],
                ]
            ],
        ]
    ],
    [
        'name' => 'promotion',
        'title' => '营销',
        'type' => 'page',
        'url' => '',
        'children' => [
            [
                'name' => 'coupon_list',
                'title' => '优惠券',
                'type' => '',
                'url' => 'pages/marketing/coupon_list',
            ],
        ]
    ]
];

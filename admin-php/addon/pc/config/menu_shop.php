<?php
// +----------------------------------------------------------------------
// | 平台端菜单设置
// +----------------------------------------------------------------------
return [
    [
        'name' => 'PC_CONFIG',
        'title' => '电脑端',
        'url' => 'pc://shop/pc/floor',
        'is_show' => 1,
        'is_control' => 1,
        'is_icon' => 0,
        'picture' => 'app/shop/view/public/img/icon_new/diy_web_new.png',
        'picture_selected' => 'app/shop/view/public/img/icon_new/diy_web_select.png',
        'parent' => 'SHOP_ROOT',
        'sort' => 3,
        'child_list' => [
            [
                'name' => 'PC_INDEX_FLOOR',
                'title' => '首页楼层',
                'url' => 'pc://shop/pc/floor',
                'is_show' => 1,
                'sort' => 1,
                'child_list' => [
                    [
                        'name' => 'PC_INDEX_FLOOR_EDIT',
                        'title' => '楼层编辑',
                        'url' => 'pc://shop/pc/editfloor',
                        'is_show' => 0,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PC_INDEX_FLOOR_DELETE',
                        'title' => '楼层删除',
                        'url' => 'pc://shop/pc/deleteFloor',
                        'is_show' => 0,
                        'type' => 'button',
                        'type' => 'button',
                    ]
                ],
            ],
            [
                'name' => 'PC_FLOAT_LAYER',
                'title' => '首页浮层',
                'url' => 'pc://shop/pc/floatlayer',
                'is_show' => 1,
                'sort' => 2,
                'child_list' => [],
            ],
            [
                'name' => 'PC_NAV_LIST',
                'title' => '导航设置',
                'url' => 'pc://shop/pc/navlist',
                'is_show' => 1,
                'sort' => 3,
                'child_list' => [
                    [
                        'name' => 'PC_NAV_ADD',
                        'title' => '添加导航',
                        'url' => 'pc://shop/pc/addnav',
                        'is_show' => 0,
                        'sort' => 1,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PC_NAV_EDIT',
                        'title' => '编辑导航',
                        'url' => 'pc://shop/pc/editnav',
                        'is_show' => 0,
                        'sort' => 2,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PC_NAV_DELETE',
                        'title' => '删除导航',
                        'url' => 'pc://shop/pc/deletenav',
                        'is_show' => 0,
                        'sort' => 3,
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PC_NAV_MODIFY_IS_SHOW',
                        'title' => '导航是否显示',
                        'url' => 'pc://shop/pc/modifynavisshow',
                        'is_show' => 0,
                        'sort' => 4,
                        'type' => 'button',
                    ],
                ],
            ],
            [
                'name' => 'PC_LINK_LIST',
                'title' => '友情链接',
                'url' => 'pc://shop/pc/linklist',
                'is_show' => 1,
                'sort' => 4,
                'child_list' => [
                    [
                        'name' => 'PC_LINK_ADD',
                        'title' => '添加友情链接',
                        'url' => 'pc://shop/pc/addlink',
                        'is_show' => 0,
                        'sort' => 1,
                        'type' => 'button',
                        'type' => 'button',
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PC_LINK_EDIT',
                        'title' => '编辑友情链接',
                        'url' => 'pc://shop/pc/editlink',
                        'is_show' => 0,
                        'sort' => 2,
                        'type' => 'button',
                        'type' => 'button',
                        'type' => 'button',
                    ],
                    [
                        'name' => 'PC_LINK_DELETE',
                        'title' => '删除友情链接',
                        'url' => 'pc://shop/pc/deletelink',
                        'is_show' => 0,
                        'sort' => 3,
                        'type' => 'button',
                        'type' => 'button',
                        'type' => 'button',
                    ],
                ],
            ],
            [
                'name' => 'WEBSITE_ADV',
                'title' => '广告管理',
                'url' => 'pc://shop/adv/index',
                'is_show' => 1,
                'sort' => 5,
                'child_list' => [
                    [
                        'name' => 'WEBSITE_ADV_POSITION',
                        'title' => '广告位管理',
                        'url' => 'pc://shop/adv/index',
                        'is_show' => 1,
                        'child_list' => [
                            [
                                'name' => 'WEBSITE_ADV_POSITION_ADD',
                                'title' => '添加广告位',
                                'url' => 'pc://shop/adv/addposition',
                                'is_show' => 0,
                                'type' => 'button',
                                'type' => 'button',
                            ],
                            [
                                'name' => 'WEBSITE_ADV_POSITION_EDIT',
                                'title' => '编辑广告位',
                                'url' => 'pc://shop/adv/editposition',
                                'is_show' => 0,
                                'type' => 'button',
                                'type' => 'button',
                            ],
                            [
                                'name' => 'WEBSITE_ADV_POSITION_DELETE',
                                'title' => '删除广告位',
                                'url' => 'pc://shop/adv/deleteposition',
                                'is_show' => 0,
                                'type' => 'button',
                                'type' => 'button',
                            ],
                            [
                                'name' => 'WEBSITE_ADV_POSITION_STATE_ALTER',
                                'title' => '更改状态',
                                'url' => 'pc://shop/adv/alteradvpositionstate',
                                'is_show' => 0,
                                'type' => 'button',
                                'type' => 'button',
                            ],
                        ]
                    ],
                    [
                        'name' => 'WEBSITE_ADV_LISTS',
                        'title' => '广告管理',
                        'url' => 'pc://shop/adv/lists',
                        'is_show' => 1,
                        'child_list' => [
                            [
                                'name' => 'WEBSITE_ADV_ADD',
                                'title' => '添加广告',
                                'url' => 'pc://shop/adv/addadv',
                                'is_show' => 0,
                                'type' => 'button',
                            ],
                            [
                                'name' => 'WEBSITE_ADV_EDIT',
                                'title' => '编辑广告',
                                'url' => 'pc://shop/adv/editadv',
                                'is_show' => 0,
                                'type' => 'button',
                            ],
                            [
                                'name' => 'WEBSITE_ADV_DELETE',
                                'title' => '删除广告',
                                'url' => 'pc://shop/adv/deleteadv',
                                'is_show' => 0,
                                'type' => 'button',
                            ],
                            [
                                'name' => 'WEBSITE_ADV_STATE_ALTER',
                                'title' => '更改状态',
                                'url' => 'pc://shop/adv/alteradvstate',
                                'is_show' => 0,
                                'type' => 'button',
                            ],
                        ]
                    ]
                ]
            ],
            [
                'name' => 'PC_CATEGORY_CONFIG',
                'title' => '首页分类',
                'url' => 'pc://shop/pc/category',
                'is_show' => 1,
                'sort' => 8,
                'child_list' => []
            ]
        ]
    ]
];

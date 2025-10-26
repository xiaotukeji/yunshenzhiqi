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

    // 自定义模板页面类型，格式：[ 'title' => '页面类型名称', 'name' => '页面标识', 'path' => '页面路径', 'value' => '页面数据，json格式' ]
    'template' => [],

    // 后台自定义组件——装修
    'util' => [],

    // 自定义页面路径
    'link' => [
        [
            'name' => 'CARDS_SERVICE',
            'title' => '项目管理',
            'parent' => '',
            'wap_url' => '',
            'web_url' => '',
            'sort' => 2,
            'child_list' => [
                [
                    'name' => 'CARDS_SERVICE_LINK',
                    'title' => '服务项目',
                    'wap_url' => '',
                    'web_url' => '',
                    'sort' => 1,
                    'child_list' => [
                        [
                            'name' => 'CARDS_SERVICE_BASIC',
                            'title' => '基础链接',
                            'wap_url' => '',
                            'web_url' => '',
                            'sort' => 1,
                            'child_list' => [
                                [
                                    'name' => 'CARDS_ERVICE_SERVICE',
                                    'title' => '项目专区',
                                    'wap_url' => '/pages_promotion/cardservice/service_goods/service_list',
                                    'web_url' => '',
                                ],
                                [
                                    'name' => 'CARDS_ERVICE_CARD',
                                    'title' => '卡项专区',
                                    'wap_url' => '/pages_promotion/cardservice/card/list',
                                    'web_url' => '',
                                ],
                                [
                                    'name' => 'CARDS_ERVICE_RESERVE',
                                    'title' => '项目预约',
                                    'wap_url' => '/pages_promotion/cardservice/service_goods/reserve_list',
                                    'web_url' => '',
                                ],
                            ]
                        ],
                        [
                            'name' => 'CARDS_SERVICE_MEMBER',
                            'title' => '会员链接',
                            'wap_url' => '',
                            'web_url' => '',
                            'sort' => 2,
                            'child_list' => [
                                [
                                    'name' => 'MY_RESERVE',
                                    'title' => '我的预约',
                                    'wap_url' => '/pages_promotion/cardservice/service_goods/my_reserve_list',
                                    'web_url' => '',
                                    'child_list' => []
                                ],
                                [
                                    'name' => 'MY_CARD',
                                    'title' => '我的卡包',
                                    'wap_url' => '/pages_promotion/cardservice/card/my_card',
                                    'web_url' => '',
                                    'child_list' => []
                                ],
                            ]
                        ]
                    ]
                ],
                [
                    'name' => 'CARDS_SERVICE_CATEGORY_LINK',
                    'title' => '项目分类',
                    'wap_url' => '/pages_promotion/cardservice/service_goods/service_list',
                    'web_url' => '',
                    'sort' => 2,
                ]
            ]
        ]

    ],

    // 自定义图标库
    'icon_library' => [],

    // uni-app 组件，格式：[ 'name' => '组件名称/文件夹名称', 'path' => '文件路径/目录路径' ]，多个逗号隔开，自定义组件名称前缀必须是diy-，也可以引用第三方组件
    'component' => [],

    // uni-app 页面，多个逗号隔开
    'pages' => [],

    // 模板信息，格式：'title' => '模板名称', 'name' => '模板标识', 'cover' => '模板封面图', 'preview' => '模板预览图', 'desc' => '模板描述'
    'info' => [],

    // 主题风格配色，格式可以自由定义扩展，【在uni-app中通过：this.themeStyle... 获取定义的颜色字段，例如：this.themeStyle.main_color】
    'theme' => [],

    // 自定义页面数据，格式：[ 'title' => '页面名称', 'name' => "页面标识", 'value' => [页面数据，json格式] ]
    'data' => []
];
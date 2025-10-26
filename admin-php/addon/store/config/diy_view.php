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
    'template' => [
        [
            'title' => '门店主页',
            'name' => 'DIY_STORE',
            'path' => '/pages/index/index',
            'value' => '',
            'rule' => [
                'support' => [ '', 'DIY_STORE' ],
                'util_type' => [ 'SYSTEM' ] // 组件类型
            ],
            'sort' => 4
        ],
    ],

    // 后台自定义组件——装修
    'util' => [
        [
            'name' => 'StoreShow',
            'title' => '门店展示',
            'type' => 'SYSTEM',
            'value' => '{"style":1, "styleName": "风格一"}',
            'sort' => '10061',
            'support_diy_view' => '',
            'max_count' => 1,
            'icon' => 'iconfont icondianpu'
        ],
        [
            'name' => 'StoreLabel',
            'title' => '门店标签',
            'type' => 'SYSTEM',
            'value' => '{"style":{"fontSize":"60","iconBgColor":[],"iconBgColorDeg":0,"iconBgImg":"","bgRadius":0,"iconColor":["#000000"],"iconColorDeg":0},"sources":"initial","labelIds":[],"icon":"","contentStyle":"style-1","previewList":{},"fontSize":14,"fontWeight":"normal","count":3}',
            'sort' => '10062',
            'support_diy_view' => '',
            'max_count' => 0,
            'icon' => 'iconfont iconmendianbiaoqian'
        ],
    ],

    // 自定义页面路径
    'link' => [
        [
            'name' => 'STORE_LIST',
            'title' => '门店列表',
            'wap_url' => '/pages_tool/store/list',
            'parent' => 'BASICS_LINK',
            'web_url' => '',
            'sort' => 0
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
    'data' => [
    ]
];
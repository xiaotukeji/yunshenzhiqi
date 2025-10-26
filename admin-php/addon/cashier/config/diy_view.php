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
    'util' => [
        [
            'name' => 'PaymentQrcode',
            'title' => '付款码',
            'type' => 'SYSTEM',
            'value' => '{"control":true}',
            'sort' => '10030',
            'support_diy_view' => 'DIY_VIEW_MEMBER_INDEX',
            'max_count' => 1,
            'icon' => 'iconfont iconfukuanma1',
        ],
    ],

    // 自定义页面路径
    'link' => [
//        [
//            'name' => 'PAYMENT_QRCODE',
//            'title' => '付款码',
//            'wap_url' => '/pages_tool/store/payment_qrcode',
//            'parent' => 'MEMBER',
//            'web_url' => '',
//            'sort' => 0
//        ],
        [
            'name' => 'PAYMENT_PAY',
            'title' => '门店支付',
            'wap_url' => '/pages_tool/store/store_payment',
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
    'data' => []
];
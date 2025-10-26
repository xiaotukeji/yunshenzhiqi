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
//        [
//            'title' => '分销市场',
//            'name' => 'DIY_FENXIAO_MARKET',
//            'path' => '/pages_tool/index/diy?name=DIY_FENXIAO_MARKET',
//            'value' => '',
//        ]
    ],

    // 后台自定义组件——装修
    'util' => [
//        [
//            'name' => 'FenxiaoGoodsList',
//            'title' => '分销商品',
//            'type' => 'PROMOTION',
//            'value' => '{"style":"style-1","sources":"initial","count":6,"goodsId":[],"nameLineMode":"single","template":"row1-of2","categoryId":0,"categoryName":"请选择","goodsNameStyle":{"color":"#303133","control":true,"fontWeight":false},"imgAroundRadius":0,"btnStyle":{"text":"关注","textColor":"#FFFFFF","theme":"default","aroundRadius":25,"control":true,"support":true,"bgColorStart":"#FC6731","bgColorEnd":"#FF4444"},"priceStyle":{"mainColor":"#FF4444","mainControl":true,"lineColor":"#999CA7","lineControl":true,"lineSupport":true},"ornament":{"type":"default","color":"#EDEDED"},"theme":"default"}',
//            'sort' => '30008',
//            'support_diy_view' => 'DIY_FENXIAO_MARKET',
//            'max_count' => 0,
//            'icon' => 'iconfont iconfenxiaoshangpin'
//        ]
    ],

    // 自定义页面路径
    'link' => [
        [
            'name' => 'DISTRIBUTION',
            'title' => '分销',
            'parent' => 'MARKETING_LINK',
            'wap_url' => '',
            'web_url' => '',
            'sort' => 2,
            'child_list' => [
                [
                    'name' => 'DISTRIBUTION_CENTRE',
                    'title' => '分销中心',
                    'wap_url' => '/pages_promotion/fenxiao/index',
                    'web_url' => '',
                    'sort' => 0
                ],
                [
                    'name' => 'WITHDRAWAL_SUBSIDIARY',
                    'title' => '提现明细',
                    'wap_url' => '/pages_promotion/fenxiao/withdraw_list',
                    'web_url' => '',
                    'sort' => 0
                ],
                [
                    'name' => 'DISTRIBUTION_ORDER',
                    'title' => '分销订单',
                    'wap_url' => '/pages_promotion/fenxiao/order',
                    'web_url' => '',
                    'sort' => 0
                ],
//                [
//                    'name' => 'DISTRIBUTION_MARKET',
//                    'title' => '分销市场',
//                    'wap_url' => '/pages_tool/index/diy?name=DIY_FENXIAO_MARKET',
//                    'web_url' => '',
//                    'sort' => 0
//                ],
                [
                    'name' => 'DISTRIBUTION_GOODS',
                    'title' => '分销商品',
                    'wap_url' => '/pages_promotion/fenxiao/goods_list',
                    'web_url' => '',
                    'sort' => 0
                ],
                [
                    'name' => 'DISTRIBUTION_TEAM',
                    'title' => '分销团队',
                    'wap_url' => '/pages_promotion/fenxiao/team',
                    'web_url' => '',
                    'sort' => 0
                ],
                [
                    'name' => 'PROMOTION_POSTER',
                    'title' => '推广海报',
                    'wap_url' => '/pages_promotion/fenxiao/promote_code',
                    'web_url' => '',
                    'sort' => 0
                ],
            ]
        ],
        [
            'name' => 'DISTRIBUTION_GOODS',
            'title' => '分销商品',
            'parent' => 'COMMODITY',
            'wap_url' => '',
            'web_url' => '',
            'child_list' => []
        ],
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
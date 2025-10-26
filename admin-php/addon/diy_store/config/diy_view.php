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
    'link' => [],

    // 自定义图标库
    'icon_library' => [],

    // uni-app 组件，格式：[ 'name' => '组件名称/文件夹名称', 'path' => '文件路径/目录路径' ]，多个逗号隔开，自定义组件名称前缀必须是diy-，也可以引用第三方组件
    'component' => [],

    // uni-app 页面，多个逗号隔开
    'pages' => [],

    // 模板信息，格式：'title' => '模板名称', 'name' => '模板标识', 'cover' => '模板封面图', 'preview' => '模板预览图', 'desc' => '模板描述'
    'info' => [
        'title' => '多门店新零售模板', // 模板名称
        'name' => 'official_diy_store', // 模板标识
        'cover' => 'addon/diy_store/shop/view/public/img/cover.png', // 模板封面图
        'preview' => 'addon/diy_store/shop/view/public/img/preview.png', // 模板预览图
        'desc' => '官方推出多门店新零售模板', // 模板描述
    ],

    // 主题风格配色，格式可以自由定义扩展，【在uni-app中通过：this.themeStyle... 获取定义的颜色字段，例如：this.themeStyle.main_color】
    'theme' => [],

    // 自定义页面数据，格式：[ 'title' => '页面名称', 'name' => "页面标识", 'value' => [页面数据，json格式] ]
    'data' => [
        [
            'title' => '多门店新零售模板',
            'name' => "DIY_VIEW_INDEX",
            'value' => [
                "global" => [
                    "title" => "多门店新零售模板",
                    "pageBgColor" => "#F5F6FA",
                    "topNavColor" => "#04C561",
                    "topNavBg" => true,
                    "navBarSwitch" => true,
                    "navStyle" => "4",
                    "textNavColor" => "#FFFFFF",
                    "topNavImg" => "",
                    "moreLink" => [
                        "name" => ""
                    ],
                    "openBottomNav" => true,
                    "textImgPosLink" => "center",
                    "mpCollect" => false,
                    "popWindow" => [
                        "imageUrl" => "",
                        "count" => -1,
                        "show" => 0,
                        "link" => [
                            "name" => ""
                        ],
                        "imgWidth" => "",
                        "imgHeight" => ""
                    ],
                    "bgUrl" => "addon/diy_store/shop/view/public/img/bg.png",
                    "imgWidth" => "375",
                    "imgHeight" => "247",
                    "template" => [
                        "pageBgColor" => "",
                        "textColor" => "#303133",
                        "componentBgColor" => "",
                        "componentAngle" => "round",
                        "topAroundRadius" => 0,
                        "bottomAroundRadius" => 0,
                        "elementBgColor" => "",
                        "elementAngle" => "round",
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 0,
                            "bottom" => 0,
                            "both" => 12
                        ]
                    ]
                ],
                "value" => [
                    [
                        "searchStyle" => 2,
                        "title" => "请输入搜索关键词",
                        "textAlign" => "left",
                        "borderType" => 2,
                        "iconType" => "img",
                        "icon" => "",
                        "style" => [
                            "fontSize" => "60",
                            "iconBgColor" => [],
                            "iconBgColorDeg" => 0,
                            "iconBgImg" => "",
                            "bgRadius" => 0,
                            "iconColor" => [
                                "#000000"
                            ],
                            "iconColorDeg" => 0
                        ],
                        "imageUrl" => "addon/diy_store/shop/view/public/img/search.png",
                        "id" => "4v0q7l4eyhs0",
                        "addonName" => "",
                        "componentName" => "Search",
                        "componentTitle" => "搜索框",
                        "searchLink" => [
                            "name" => ""
                        ],
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "textColor" => "#303133",
                        "componentBgColor" => "",
                        "topAroundRadius" => 0,
                        "bottomAroundRadius" => 0,
                        "elementBgColor" => "#FFFFFF",
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 6,
                            "bottom" => 6,
                            "both" => 12
                        ],
                        "imgWidth" => "60",
                        "imgHeight" => "60",
                        "positionWay" => "fixed"
                    ],
                    [
                        "mode" => "graphic",
                        "type" => "img",
                        "showStyle" => "fixed",
                        "ornament" => [
                            "type" => "default",
                            "color" => "#EDEDED"
                        ],
                        "rowCount" => 5,
                        "pageCount" => 2,
                        "carousel" => [
                            "type" => "circle",
                            "color" => "#FFFFFF"
                        ],
                        "imageSize" => 50,
                        "aroundRadius" => 25,
                        "font" => [
                            "size" => 14,
                            "weight" => "normal",
                            "color" => "#303133"
                        ],
                        "list" => [
                            [
                                "title" => "新鲜蔬菜",
                                "imageUrl" => "addon/diy_store/shop/view/public/img/nav_1.png",
                                "iconType" => "img",
                                "style" => [
                                    "fontSize" => "60",
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "bgRadius" => 0,
                                    "iconColor" => [
                                        "#000000"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => ""
                                ],
                                "label" => [
                                    "control" => true,
                                    "text" => "热卖",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#FE8681",
                                    "bgColorEnd" => "#FF5754"
                                ],
                                "icon" => "",
                                "id" => "1v99h14wqhr40",
                                "imgWidth" => "147",
                                "imgHeight" => "147"
                            ],
                            [
                                "title" => "鲜肉蛋禽",
                                "imageUrl" => "addon/diy_store/shop/view/public/img/nav_2.png",
                                "iconType" => "img",
                                "style" => [
                                    "fontSize" => "60",
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "bgRadius" => 0,
                                    "iconColor" => [
                                        "#000000"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => ""
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "icon" => "",
                                "id" => "1s5oxgjxss5c0",
                                "imgWidth" => "147",
                                "imgHeight" => "147"
                            ],
                            [
                                "title" => "优选水果",
                                "imageUrl" => "addon/diy_store/shop/view/public/img/nav_3.png",
                                "iconType" => "img",
                                "style" => [
                                    "fontSize" => "60",
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "bgRadius" => 0,
                                    "iconColor" => [
                                        "#000000"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => ""
                                ],
                                "label" => [
                                    "control" => true,
                                    "text" => "草莓",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#FE8681",
                                    "bgColorEnd" => "#FF5754"
                                ],
                                "icon" => "",
                                "id" => "1z76ythx14sg0",
                                "imgWidth" => "147",
                                "imgHeight" => "147"
                            ],
                            [
                                "title" => "海鲜水产",
                                "imageUrl" => "addon/diy_store/shop/view/public/img/nav_4.png",
                                "iconType" => "img",
                                "style" => "",
                                "link" => [
                                    "name" => ""
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "iconfont" => [
                                    "value" => "",
                                    "color" => ""
                                ],
                                "id" => "77stelj6ykc0",
                                "imgWidth" => "147",
                                "imgHeight" => "147"
                            ],
                            [
                                "title" => "蛋糕甜点",
                                "imageUrl" => "addon/diy_store/shop/view/public/img/nav_5.png",
                                "iconType" => "img",
                                "style" => "",
                                "link" => [
                                    "name" => ""
                                ],
                                "label" => [
                                    "control" => true,
                                    "text" => "蛋糕",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#FE8681",
                                    "bgColorEnd" => "#FF5754"
                                ],
                                "iconfont" => [
                                    "value" => "",
                                    "color" => ""
                                ],
                                "id" => "1zmlt86jn8xs0",
                                "imgWidth" => "147",
                                "imgHeight" => "147"
                            ],
                            [
                                "title" => "牛奶乳品",
                                "imageUrl" => "addon/diy_store/shop/view/public/img/nav_6.png",
                                "iconType" => "img",
                                "style" => "",
                                "link" => [
                                    "name" => ""
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "iconfont" => [
                                    "value" => "",
                                    "color" => ""
                                ],
                                "id" => "ld0uf9zrh2o0",
                                "imgWidth" => "147",
                                "imgHeight" => "147"
                            ],
                            [
                                "title" => "厨房调料",
                                "imageUrl" => "addon/diy_store/shop/view/public/img/nav_7.png",
                                "iconType" => "img",
                                "style" => "",
                                "link" => [
                                    "name" => ""
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "iconfont" => [
                                    "value" => "",
                                    "color" => ""
                                ],
                                "id" => "fruwfv8bads",
                                "imgWidth" => "147",
                                "imgHeight" => "147"
                            ],
                            [
                                "title" => "米面粮油",
                                "imageUrl" => "addon/diy_store/shop/view/public/img/nav_8.png",
                                "iconType" => "img",
                                "style" => "",
                                "link" => [
                                    "name" => ""
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "iconfont" => [
                                    "value" => "",
                                    "color" => ""
                                ],
                                "id" => "70g58o07zrk0",
                                "imgWidth" => "147",
                                "imgHeight" => "147"
                            ],
                            [
                                "title" => "卤味蔬食",
                                "imageUrl" => "addon/diy_store/shop/view/public/img/nav_9.png",
                                "iconType" => "img",
                                "style" => "",
                                "link" => [
                                    "name" => ""
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "iconfont" => [
                                    "value" => "",
                                    "color" => ""
                                ],
                                "id" => "186cp6fj4vr40",
                                "imgWidth" => "147",
                                "imgHeight" => "147"
                            ],
                            [
                                "title" => "饮料酒水",
                                "imageUrl" => "addon/diy_store/shop/view/public/img/nav_10.png",
                                "iconType" => "img",
                                "style" => "",
                                "link" => [
                                    "name" => ""
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "iconfont" => [
                                    "value" => "",
                                    "color" => ""
                                ],
                                "id" => "1ivu0ox8nmxs0",
                                "imgWidth" => "147",
                                "imgHeight" => "147"
                            ]
                        ],
                        "id" => "49qvi2em5is0",
                        "addonName" => "",
                        "componentName" => "GraphicNav",
                        "componentTitle" => "图文导航",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "componentBgColor" => "#FFFFFF",
                        "componentAngle" => "round",
                        "topAroundRadius" => 12,
                        "bottomAroundRadius" => 12,
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 6,
                            "bottom" => 6,
                            "both" => 12
                        ]
                    ],
                    [
                        "indicatorColor" => "#ffffff",
                        "carouselStyle" => "circle",
                        "indicatorLocation" => "center",
                        "list" => [
                            [
                                "imageUrl" => "addon/diy_store/shop/view/public/img/point.png",
                                "link" => [
                                    "name" => "INTEGRAL_STORE",
                                    "title" => "积分商城",
                                    "wap_url" => "/pages_promotion/point/list",
                                    "parent" => "MARKETING_LINK"
                                ],
                                "imgWidth" => "702",
                                "imgHeight" => "180",
                                "id" => "1z69zcd5vkio0"
                            ]
                        ],
                        "id" => "1vtq5yokj44g",
                        "addonName" => "",
                        "componentName" => "ImageAds",
                        "componentTitle" => "图片广告",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "componentBgColor" => "",
                        "componentAngle" => "round",
                        "topAroundRadius" => 0,
                        "bottomAroundRadius" => 0,
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 6,
                            "bottom" => 6,
                            "both" => 12
                        ]
                    ],
                    [
                        "mode" => "row1-lt-of2-rt",
                        "imageGap" => 10,
                        "list" => [
                            [
                                "imageUrl" => "addon/diy_store/shop/view/public/img/rubik_cube_5.png",
                                "imgWidth" => "342",
                                "imgHeight" => "350",
                                "previewWidth" => 163.5,
                                "previewHeight" => "177.32px",
                                "link" => [
                                    "name" => ""
                                ]
                            ],
                            [
                                "imageUrl" => "addon/diy_store/shop/view/public/img/rubik_cube_6.png",
                                "imgWidth" => "342",
                                "imgHeight" => "166",
                                "previewWidth" => 163.5,
                                "previewHeight" => "83.66px",
                                "link" => [
                                    "name" => ""
                                ]
                            ],
                            [
                                "imageUrl" => "addon/diy_store/shop/view/public/img/rubik_cube_7.png",
                                "imgWidth" => "342",
                                "imgHeight" => "166",
                                "previewWidth" => 163.5,
                                "previewHeight" => "83.66px",
                                "link" => [
                                    "name" => ""
                                ]
                            ]
                        ],
                        "id" => "47v1783p3oo0",
                        "addonName" => "",
                        "componentName" => "RubikCube",
                        "componentTitle" => "魔方",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "componentBgColor" => "",
                        "componentAngle" => "round",
                        "topAroundRadius" => 0,
                        "bottomAroundRadius" => 0,
                        "elementAngle" => "round",
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 6,
                            "bottom" => 6,
                            "both" => 12
                        ]
                    ],
                    [
                        "indicatorColor" => "#ffffff",
                        "carouselStyle" => "circle",
                        "indicatorLocation" => "center",
                        "list" => [
                            [
                                "imageUrl" => "addon/diy_store/shop/view/public/img/baozhang.png",
                                "link" => [
                                    "name" => ""
                                ],
                                "imgWidth" => "702",
                                "imgHeight" => "66",
                                "id" => "1glfoxntyc1s0"
                            ]
                        ],
                        "id" => "68xzp1ipgkw0",
                        "addonName" => "",
                        "componentName" => "ImageAds",
                        "componentTitle" => "图片广告",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "componentBgColor" => "",
                        "componentAngle" => "round",
                        "topAroundRadius" => 0,
                        "bottomAroundRadius" => 0,
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 6,
                            "bottom" => 6,
                            "both" => 12
                        ]
                    ],
                    [
                        "ornament" => [
                            "type" => "default",
                            "color" => "#EDEDED"
                        ],
                        "style" => "style-3",
                        "count" => 6,
                        "nameLineMode" => "single",
                        "sortWay" => "default",
                        "imgAroundRadius" => 10,
                        "goodsNameStyle" => [
                            "color" => "#303133",
                            "control" => false,
                            "fontWeight" => false,
                            "support" => false
                        ],
                        "saleStyle" => [
                            "color" => "#999CA7",
                            "control" => false,
                            "support" => false
                        ],
                        "theme" => "default",
                        "priceStyle" => [
                            "mainColor" => "#FF1544",
                            "mainControl" => true,
                            "lineColor" => "#999CA7",
                            "lineControl" => false,
                            "lineSupport" => false
                        ],
                        "sources" => "initial",
                        "goodsId" => [],
                        "categoryId" => 0,
                        "categoryName" => "请选择",
                        "topStyle" => [
                            "title" => "今日推荐",
                            "subTitle" => "大家都在买",
                            "icon" => [
                                "value" => "icondiy icon-system-tuijian",
                                "color" => "#FF3D3D",
                                "bgColor" => ""
                            ],
                            "color" => "#303133",
                            "subColor" => "#999CA7",
                            "support" => false
                        ],
                        "bgUrl" => "app/component/view/goods_recommend/img/style3_bg.png",
                        "styleName" => "风格3",
                        "labelStyle" => [
                            "support" => true,
                            "bgColor" => "#FF504D",
                            "title" => "新人专享",
                            "color" => "#FFFFFF"
                        ],
                        "id" => "16uuk7vmsq5c",
                        "addonName" => "",
                        "componentName" => "GoodsRecommend",
                        "componentTitle" => "商品推荐",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "componentBgColor" => "",
                        "componentAngle" => "round",
                        "topAroundRadius" => 8,
                        "bottomAroundRadius" => 8,
                        "elementBgColor" => "",
                        "elementAngle" => "round",
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 6,
                            "bottom" => 6,
                            "both" => 12
                        ]
                    ],
                    [
                        "style" => "style-2",
                        "sources" => "initial",
                        "count" => 6,
                        "goodsId" => [],
                        "goodsMarginType" => "default",
                        "goodsMarginNum" => 10,
                        "ornament" => [
                            "type" => "default",
                            "color" => "#EDEDED"
                        ],
                        "nameLineMode" => "single",
                        "template" => "horizontal-slide",
                        "btnStyle" => [
                            "text" => "去秒杀",
                            "textColor" => "#FFFFFF",
                            "theme" => "default",
                            "aroundRadius" => 25,
                            "control" => false,
                            "support" => false,
                            "bgColorStart" => "#FF7B1D",
                            "bgColorEnd" => "#FF1544"
                        ],
                        "imgAroundRadius" => 5,
                        "saleStyle" => [
                            "color" => "#999CA7",
                            "control" => false,
                            "support" => false
                        ],
                        "progressStyle" => [
                            "control" => false,
                            "support" => false,
                            "currColor" => "#FDBE6C",
                            "bgColor" => "#FCECD7"
                        ],
                        "titleStyle" => [
                            "backgroundImage" => "addon/seckill/component/view/seckill/img/style_title_4_bg.png",
                            "isShow" => true,
                            "leftStyle" => "img",
                            "leftImg" => "addon/seckill/component/view/seckill/img/style_title_4_name.png",
                            "style" => "style-4",
                            "styleName" => "风格4",
                            "leftText" => "",
                            "fontSize" => 16,
                            "fontWeight" => true,
                            "textColor" => "#666666",
                            "bgColorStart" => "#FFFFFF",
                            "bgColorEnd" => "#FFFFFF",
                            "more" => "",
                            "moreColor" => "",
                            "moreFontSize" => 12,
                            "timeTextColor" => "#FFFFFF",
                            "timeBgColor" => "",
                            "timeImageUrl" => "",
                            "moreSupport" => false,
                            "colonColor" => "#FE3718",
                            "numBgColorStart" => "#FF5F17",
                            "numBgColorEnd" => "#FE2F18",
                            "numTextColor" => "#FFFFFF"
                        ],
                        "slideMode" => "slide",
                        "theme" => "default",
                        "priceStyle" => [
                            "mainColor" => "#FFFFFF",
                            "mainControl" => true,
                            "lineColor" => "#999CA7",
                            "lineControl" => true,
                            "lineSupport" => true
                        ],
                        "goodsNameStyle" => [
                            "color" => "#303133",
                            "control" => false,
                            "fontWeight" => false
                        ],
                        "id" => "2qfjelhllvs0",
                        "addonName" => "seckill",
                        "componentName" => "Seckill",
                        "componentTitle" => "秒杀",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "componentBgColor" => "#FFFFFF",
                        "componentAngle" => "round",
                        "topAroundRadius" => 8,
                        "bottomAroundRadius" => 8,
                        "elementBgColor" => "",
                        "elementAngle" => "round",
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 6,
                            "bottom" => 6,
                            "both" => 12
                        ]
                    ],
                    [
                        "ornament" => [
                            "type" => "default",
                            "color" => "#EDEDED"
                        ],
                        "id" => "2i5zb56h2dy0",
                        "addonName" => "pintuan",
                        "componentName" => "Pintuan",
                        "componentTitle" => "拼团",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "componentBgColor" => "#FFFFFF",
                        "componentAngle" => "round",
                        "topAroundRadius" => 8,
                        "bottomAroundRadius" => 8,
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 6,
                            "bottom" => 6,
                            "both" => 12
                        ],
                        "style" => "style-3",
                        "sources" => "initial",
                        "count" => 6,
                        "goodsId" => [],
                        "nameLineMode" => "single",
                        "template" => "horizontal-slide",
                        "btnStyle" => [
                            "text" => "去拼团",
                            "textColor" => "#FFFFFF",
                            "theme" => "default",
                            "aroundRadius" => 25,
                            "control" => false,
                            "support" => false,
                            "bgColorStart" => "#FF1544",
                            "bgColorEnd" => "#FF1544"
                        ],
                        "imgAroundRadius" => 10,
                        "saleStyle" => [
                            "color" => "#FF1544",
                            "control" => false,
                            "support" => false
                        ],
                        "slideMode" => "scroll",
                        "theme" => "default",
                        "goodsNameStyle" => [
                            "color" => "#303133",
                            "control" => true,
                            "fontWeight" => false
                        ],
                        "priceStyle" => [
                            "mainColor" => "#FF1544",
                            "mainControl" => true,
                            "lineColor" => "#999CA7",
                            "lineControl" => false,
                            "lineSupport" => false
                        ],
                        "titleStyle" => [
                            "virtualNum" => 999,
                            "bgColorStart" => "#FFFFFF",
                            "bgColorEnd" => "#FFFFFF",
                            "isShow" => true,
                            "leftStyle" => "img",
                            "leftImg" => "addon/pintuan/component/view/pintuan/img/style_2_title.png",
                            "style" => "style-2",
                            "styleName" => "风格2",
                            "leftText" => "超值拼团",
                            "fontSize" => 16,
                            "fontWeight" => true,
                            "textColor" => "#888888",
                            "more" => "更多",
                            "moreColor" => "#FFFFFF",
                            "moreFontSize" => 12,
                            "backgroundImage" => ""
                        ],
                        "goodsMarginType" => "default",
                        "goodsMarginNum" => 10,
                        "groupStyle" => [
                            "color" => "#FFFFFF",
                            "control" => true,
                            "support" => true,
                            "bgColorStart" => "#FA2379",
                            "bgColorEnd" => "#FF4F61"
                        ],
                        "elementBgColor" => "",
                        "elementAngle" => "round"
                    ],
                    [
                        "style" => "style-3",
                        "sources" => "initial",
                        "count" => 6,
                        "goodsId" => [],
                        "ornament" => [
                            "type" => "default",
                            "color" => "#EDEDED"
                        ],
                        "nameLineMode" => "single",
                        "template" => "row1-of1",
                        "btnStyle" => [
                            "text" => "去砍价",
                            "textColor" => "#FFFFFF",
                            "theme" => "default",
                            "aroundRadius" => "4",
                            "control" => true,
                            "support" => true,
                            "bgColorStart" => "#3EDB73",
                            "bgColorEnd" => "#1DB576"
                        ],
                        "imgAroundRadius" => 5,
                        "saleStyle" => [
                            "color" => "#FFFFFF",
                            "control" => false,
                            "support" => false
                        ],
                        "slideMode" => "scroll",
                        "theme" => "default",
                        "goodsNameStyle" => [
                            "color" => "#303133",
                            "control" => true,
                            "fontWeight" => false
                        ],
                        "priceStyle" => [
                            "mainColor" => "#FF1745",
                            "mainControl" => true,
                            "lineColor" => "#999CA7",
                            "lineControl" => false,
                            "lineSupport" => false
                        ],
                        "titleStyle" => [
                            "bgColorStart" => "#FFFFFF",
                            "bgColorEnd" => "#FFFFFF",
                            "isShow" => true,
                            "leftStyle" => "img",
                            "leftImg" => "addon/bargain/component/view/bargain/img/row1_of1_style_3_name.png",
                            "style" => "style-2",
                            "styleName" => "风格2",
                            "leftText" => "疯狂砍价",
                            "fontSize" => 16,
                            "fontWeight" => true,
                            "textColor" => "#FFFFFF",
                            "more" => "更多",
                            "moreColor" => "#FFFFFF",
                            "moreFontSize" => 12,
                            "backgroundImage" => ""
                        ],
                        "goodsMarginType" => "default",
                        "goodsMarginNum" => 10,
                        "id" => "4x7liou5lm20",
                        "addonName" => "bargain",
                        "componentName" => "Bargain",
                        "componentTitle" => "砍价",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "componentBgColor" => "#FFFFFF",
                        "componentAngle" => "round",
                        "topAroundRadius" => 8,
                        "bottomAroundRadius" => 8,
                        "elementBgColor" => "",
                        "elementAngle" => "round",
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 6,
                            "bottom" => 6,
                            "both" => 12
                        ]
                    ],
                    [
                        "ornament" => [
                            "type" => "default",
                            "color" => "#EDEDED"
                        ],
                        "template" => "row1-of2",
                        "goodsMarginType" => "default",
                        "goodsMarginNum" => 10,
                        "style" => "style-2",
                        "sources" => "initial",
                        "count" => 6,
                        "goodsId" => [],
                        "categoryId" => 0,
                        "categoryName" => "请选择",
                        "sortWay" => "default",
                        "nameLineMode" => "single",
                        "imgAroundRadius" => 0,
                        "slideMode" => "scroll",
                        "theme" => "default",
                        "btnStyle" => [
                            "fontWeight" => false,
                            "padding" => 0,
                            "cartEvent" => "cart",
                            "text" => "购买",
                            "textColor" => "#FFFFFF",
                            "theme" => "default",
                            "aroundRadius" => 25,
                            "control" => true,
                            "support" => true,
                            "bgColor" => "#FF6A00",
                            "style" => "button",
                            "iconDiy" => [
                                "iconType" => "icon",
                                "icon" => "",
                                "style" => [
                                    "fontSize" => "60",
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "bgRadius" => 0,
                                    "iconColor" => [
                                        "#000000"
                                    ],
                                    "iconColorDeg" => 0
                                ]
                            ]
                        ],
                        "tag" => [
                            "text" => "隐藏",
                            "value" => "hidden"
                        ],
                        "goodsNameStyle" => [
                            "color" => "#303133",
                            "control" => true,
                            "fontWeight" => false
                        ],
                        "saleStyle" => [
                            "color" => "#999CA7",
                            "control" => true,
                            "support" => true
                        ],
                        "priceStyle" => [
                            "mainColor" => "#FF6A00",
                            "mainControl" => true,
                            "lineColor" => "#999CA7",
                            "lineControl" => true,
                            "lineSupport" => true
                        ],
                        "id" => "4dd123y6pco",
                        "addonName" => "",
                        "componentName" => "GoodsList",
                        "componentTitle" => "商品列表",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "componentBgColor" => "",
                        "componentAngle" => "round",
                        "topAroundRadius" => 8,
                        "bottomAroundRadius" => 8,
                        "elementBgColor" => "#FFFFFF",
                        "elementAngle" => "round",
                        "topElementAroundRadius" => 8,
                        "bottomElementAroundRadius" => 8,
                        "margin" => [
                            "top" => 6,
                            "bottom" => 0,
                            "both" => 12
                        ]
                    ]
                ]
            ]
        ],
        [
            'title' => '会员中心',
            'name' => "DIY_VIEW_MEMBER_INDEX",
            'value' => [
                "global" => [
                    "title" => "会员中心",
                    "pageBgColor" => "#F8F8F8",
                    "topNavColor" => "#FFFFFF",
                    "topNavBg" => true,
                    "navBarSwitch" => false,
                    "navStyle" => 1,
                    "textNavColor" => "#333333",
                    "topNavImg" => "",
                    "moreLink" => [
                        "name" => ""
                    ],
                    "openBottomNav" => true,
                    "textImgPosLink" => "center",
                    "mpCollect" => false,
                    "popWindow" => [
                        "imageUrl" => "",
                        "count" => -1,
                        "show" => 0,
                        "link" => [
                            "name" => ""
                        ],
                        "imgWidth" => "",
                        "imgHeight" => ""
                    ],
                    "bgUrl" => "",
                    "imgWidth" => "",
                    "imgHeight" => "",
                    "template" => [
                        "pageBgColor" => "",
                        "textColor" => "#303133",
                        "componentBgColor" => "",
                        "componentAngle" => "round",
                        "topAroundRadius" => 0,
                        "bottomAroundRadius" => 0,
                        "elementBgColor" => "",
                        "elementAngle" => "round",
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 0,
                            "bottom" => 0,
                            "both" => 0
                        ]
                    ]
                ],
                "value" => [
                    [
                        "style" => 4,
                        "theme" => "default",
                        "bgColorStart" => "#FF7230",
                        "bgColorEnd" => "#FF1544",
                        "gradientAngle" => "129",
                        "infoMargin" => 12,
                        "id" => "1tkaoxbhavj4",
                        "addonName" => "",
                        "componentName" => "MemberInfo",
                        "componentTitle" => "会员信息",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "textColor" => "#303133",
                        "componentBgColor" => "",
                        "componentAngle" => "round",
                        "topAroundRadius" => 0,
                        "bottomAroundRadius" => 0,
                        "elementBgColor" => "",
                        "elementAngle" => "round",
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 0,
                            "bottom" => 0,
                            "both" => 0
                        ]
                    ],
                    [
                        "style" => "style-12",
                        "styleName" => "风格12",
                        "text" => "我的订单",
                        "link" => [
                            "name" => ""
                        ],
                        "fontSize" => 15,
                        "fontWeight" => "bold",
                        "subTitle" => [
                            "fontSize" => 14,
                            "text" => "",
                            "isElementShow" => true,
                            "color" => "#999999",
                            "bgColor" => "#303133"
                        ],
                        "more" => [
                            "text" => "查看全部",
                            "link" => [
                                "name" => "ALL_ORDER",
                                "title" => "全部订单",
                                "wap_url" => "/pages/order/list",
                                "parent" => "MALL_LINK"
                            ],
                            "isShow" => 1,
                            "isElementShow" => true,
                            "color" => "#999999"
                        ],
                        "id" => "2txcvx3d5u6",
                        "addonName" => "",
                        "componentName" => "Text",
                        "componentTitle" => "标题",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "textColor" => "#121836",
                        "componentBgColor" => "#FFFFFF",
                        "componentAngle" => "round",
                        "topAroundRadius" => 9,
                        "bottomAroundRadius" => 0,
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 12,
                            "bottom" => 0,
                            "both" => 12
                        ]
                    ],
                    [
                        "icon" => [
                            "waitPay" => [
                                "title" => "待支付",
                                "icon" => "icondiy icon-system-daizhifu",
                                "style" => [
                                    "bgRadius" => 0,
                                    "fontSize" => 90,
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "iconColor" => [ "#20DA86", "#03B352" ],
                                    "iconColorDeg" => 0
                                ]
                            ],
                            "waitSend" => [
                                "title" => "备货中",
                                "icon" => "icondiy icon-system-beihuozhong",
                                "style" => [
                                    "bgRadius" => 0,
                                    "fontSize" => 90,
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "iconColor" => [ "#20DA86", "#03B352" ],
                                    "iconColorDeg" => 0
                                ]
                            ],
                            "waitConfirm" => [
                                "title" => "配送中",
                                "icon" => "icondiy icon-system-paisongzhong",
                                "style" => [
                                    "bgRadius" => 0,
                                    "fontSize" => 90,
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "iconColor" => [ "#20DA86", "#03B352" ],
                                    "iconColorDeg" => 0
                                ]
                            ],
                            "waitUse" => [
                                "title" => "待使用",
                                "icon" => "icondiy icon-system-daishiyong2",
                                "style" => [
                                    "bgRadius" => 0,
                                    "fontSize" => 75,
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "iconColor" => [ "#20DA86", "#03B352" ],
                                    "iconColorDeg" => 0
                                ]
                            ],
                            "refunding" => [
                                "title" => "退换货",
                                "icon" => "icondiy icon-system-tuihuoguanli",
                                "style" => [
                                    "bgRadius" => 0,
                                    "fontSize" => 90,
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "iconColor" => [ "#20DA86", "#03B352" ],
                                    "iconColorDeg" => 0
                                ]
                            ]
                        ],
                        "style" => 4,
                        "id" => "2bplt2x9n0bo",
                        "addonName" => "",
                        "componentName" => "MemberMyOrder",
                        "componentTitle" => "我的订单",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "textColor" => "#303133",
                        "componentBgColor" => "#FFFFFF",
                        "componentAngle" => "round",
                        "topAroundRadius" => 0,
                        "bottomAroundRadius" => 9,
                        "elementBgColor" => "",
                        "elementAngle" => "round",
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 0,
                            "bottom" => 0,
                            "both" => 12
                        ]
                    ],
                    [
                        "style" => "style-12",
                        "styleName" => "风格12",
                        "text" => "我的服务",
                        "link" => [
                            "name" => ""
                        ],
                        "fontSize" => 15,
                        "fontWeight" => "bold",
                        "subTitle" => [
                            "fontSize" => 14,
                            "text" => "",
                            "isElementShow" => true,
                            "color" => "#999999",
                            "bgColor" => "#303133"
                        ],
                        "more" => [
                            "text" => "",
                            "link" => [
                                "name" => ""
                            ],
                            "isShow" => 0,
                            "isElementShow" => true,
                            "color" => "#999999"
                        ],
                        "id" => "405rb6vv3rq0",
                        "addonName" => "",
                        "componentName" => "Text",
                        "componentTitle" => "标题",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "textColor" => "#121836",
                        "componentBgColor" => "#FFFFFF",
                        "componentAngle" => "round",
                        "topAroundRadius" => 9,
                        "bottomAroundRadius" => 0,
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 12,
                            "bottom" => 0,
                            "both" => 12
                        ]
                    ],
                    [
                        "mode" => "graphic",
                        "type" => "img",
                        "showStyle" => "fixed",
                        "ornament" => [
                            "type" => "default",
                            "color" => "#EDEDED"
                        ],
                        "rowCount" => 5,
                        "pageCount" => 2,
                        "carousel" => [
                            "type" => "circle",
                            "color" => "#FFFFFF"
                        ],
                        "imageSize" => 30,
                        "aroundRadius" => 0,
                        "font" => [
                            "size" => 12,
                            "weight" => "normal",
                            "color" => "#666666"
                        ],
                        "list" => [
                            [
                                "title" => "我的资料",
                                "imageUrl" => "public/uniapp/member/index/menu/new_persion.png",
                                "iconType" => "img",
                                "style" => [
                                    "fontSize" => "60",
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "bgRadius" => 0,
                                    "iconColor" => [
                                        "#000000"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => "MEMBER_INFO",
                                    "title" => "个人资料",
                                    "wap_url" => "/pages_tool/member/info",
                                    "parent" => "MALL_LINK"
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "icon" => "",
                                "id" => "10rhv0x6phhc0",
                                "imgWidth" => "60",
                                "imgHeight" => "60"
                            ],
                            [
                                "title" => "签到",
                                "imageUrl" => "public/uniapp/member/index/menu/new_sign.png",
                                "iconType" => "img",
                                "style" => "",
                                "link" => [
                                    "name" => "SIGN_IN",
                                    "title" => "签到",
                                    "wap_url" => "/pages_tool/member/signin",
                                    "parent" => "MARKETING_LINK"
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "iconfont" => [
                                    "value" => "",
                                    "color" => ""
                                ],
                                "id" => "hodjcxowf8g0",
                                "imgWidth" => "60",
                                "imgHeight" => "60"
                            ],
                            [
                                "title" => "收货地址",
                                "imageUrl" => "public/uniapp/member/index/menu/new_address.png",
                                "iconType" => "img",
                                "style" => [
                                    "fontSize" => "60",
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "bgRadius" => 0,
                                    "iconColor" => [
                                        "#000000"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => "SHIPPING_ADDRESS",
                                    "title" => "收货地址",
                                    "wap_url" => "/pages_tool/member/address",
                                    "parent" => "MALL_LINK"
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "icon" => "",
                                "id" => "1n8gycn6xqe80",
                                "imgWidth" => "60",
                                "imgHeight" => "60"
                            ],
                            [
                                "title" => "优惠券",
                                "imageUrl" => "public/uniapp/member/index/menu/new_coupon.png",
                                "iconType" => "img",
                                "style" => "",
                                "link" => [
                                    "name" => "COUPON",
                                    "title" => "优惠券",
                                    "wap_url" => "/pages_tool/member/coupon",
                                    "parent" => "MALL_LINK"
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "iconfont" => [
                                    "value" => "",
                                    "color" => ""
                                ],
                                "id" => "1tnu0vihrnq80",
                                "imgWidth" => "60",
                                "imgHeight" => "60"
                            ],
                            [
                                "title" => "我的拼单",
                                "imageUrl" => "public/uniapp/member/index/menu/new_pindan.png",
                                "iconType" => "img",
                                "style" => "",
                                "link" => [
                                    "name" => "MY_PINTUAN",
                                    "title" => "我的拼团",
                                    "wap_url" => "/pages_promotion/pintuan/my_spell",
                                    "parent" => "MARKETING_LINK"
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "iconfont" => [
                                    "value" => "",
                                    "color" => ""
                                ],
                                "id" => "uoarcfsleio0",
                                "imgWidth" => "60",
                                "imgHeight" => "60"
                            ],
                            [
                                "title" => "我的礼品",
                                "imageUrl" => "public/uniapp/member/index/menu/new_gift.png",
                                "iconType" => "img",
                                "style" => "",
                                "link" => [
                                    "name" => "MEMBER_RECOMMEND",
                                    "title" => "邀请有礼",
                                    "wap_url" => "/pages_tool/member/invite_friends",
                                    "parent" => "MARKETING_LINK"
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "iconfont" => [
                                    "value" => "",
                                    "color" => ""
                                ],
                                "id" => "1h34nmfisge80",
                                "imgWidth" => "60",
                                "imgHeight" => "60"
                            ],
                            [
                                "title" => "我的关注",
                                "imageUrl" => "public/uniapp/member/index/menu/new_like.png",
                                "iconType" => "img",
                                "style" => [
                                    "fontSize" => "60",
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "bgRadius" => 0,
                                    "iconColor" => [
                                        "#000000"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => "ATTENTION",
                                    "title" => "我的关注",
                                    "wap_url" => "/pages_tool/member/collection",
                                    "parent" => "MALL_LINK"
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "icon" => "",
                                "id" => "cnamoch6cvk0",
                                "imgWidth" => "60",
                                "imgHeight" => "60"
                            ],
                            [
                                "title" => "我的足迹",
                                "imageUrl" => "public/uniapp/member/index/menu/new_foot.png",
                                "iconType" => "img",
                                "style" => [
                                    "fontSize" => "60",
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "bgRadius" => 0,
                                    "iconColor" => [
                                        "#000000"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => "FOOTPRINT",
                                    "title" => "我的足迹",
                                    "wap_url" => "/pages_tool/member/footprint",
                                    "parent" => "MALL_LINK"
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "icon" => "",
                                "id" => "drf3hi3slo00",
                                "imgWidth" => "60",
                                "imgHeight" => "60"
                            ],
                            [
                                "title" => "我的砍价",
                                "imageUrl" => "public/uniapp/member/index/menu/new_bargain.png",
                                "iconType" => "img",
                                "style" => "",
                                "link" => [
                                    "name" => "MY_BARGAIN",
                                    "title" => "我的砍价",
                                    "wap_url" => "/pages_promotion/bargain/my_bargain",
                                    "parent" => "MARKETING_LINK"
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "iconfont" => [
                                    "value" => "",
                                    "color" => ""
                                ],
                                "id" => "13uz22sbag000",
                                "imgWidth" => "60",
                                "imgHeight" => "60"
                            ],
                            [
                                "title" => "积分兑换",
                                "imageUrl" => "public/uniapp/member/index/menu/new_point_change.png",
                                "iconType" => "img",
                                "style" => "",
                                "link" => [
                                    "name" => "INTEGRAL_CONVERSION",
                                    "title" => "积分兑换",
                                    "wap_url" => "/pages_promotion/point/order_list",
                                    "parent" => "MARKETING_LINK"
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "iconfont" => [
                                    "value" => "",
                                    "color" => ""
                                ],
                                "id" => "rnyw8xo5rdc0",
                                "imgWidth" => "60",
                                "imgHeight" => "60"
                            ]
                        ],
                        "id" => "5ywbzsnigpw0",
                        "addonName" => "",
                        "componentName" => "GraphicNav",
                        "componentTitle" => "图文导航",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "componentBgColor" => "#FFFFFF",
                        "componentAngle" => "round",
                        "topAroundRadius" => 0,
                        "bottomAroundRadius" => 9,
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 0,
                            "bottom" => 12,
                            "both" => 12
                        ]
                    ],
                    [
                        "style" => "style-12",
                        "styleName" => "风格12",
                        "text" => "我的工具",
                        "link" => [
                            "name" => ""
                        ],
                        "fontSize" => 15,
                        "fontWeight" => "bold",
                        "subTitle" => [
                            "fontSize" => 14,
                            "text" => "",
                            "isElementShow" => true,
                            "color" => "#999999",
                            "bgColor" => "#303133"
                        ],
                        "more" => [
                            "text" => "",
                            "link" => [
                                "name" => ""
                            ],
                            "isShow" => 0,
                            "isElementShow" => true,
                            "color" => "#999999"
                        ],
                        "id" => "1dbblwhsuwg0",
                        "addonName" => "",
                        "componentName" => "Text",
                        "componentTitle" => "标题",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "textColor" => "#121836",
                        "componentBgColor" => "#FFFFFF",
                        "componentAngle" => "round",
                        "topAroundRadius" => 9,
                        "bottomAroundRadius" => 0,
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 0,
                            "bottom" => 0,
                            "both" => 12
                        ]
                    ],
                    [
                        "ornament" => [
                            "type" => "default",
                            "color" => "#EDEDED"
                        ],
                        "list" => [
                            [
                                "title" => "分销中心",
                                "style" => [
                                    "fontSize" => "60",
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "bgRadius" => 0,
                                    "iconColor" => [
                                        "#000000"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => "DISTRIBUTION_CENTRE",
                                    "title" => "分销中心",
                                    "wap_url" => "/pages_promotion/fenxiao/index",
                                    "parent" => "MARKETING_LINK"
                                ],
                                "icon" => "",
                                "iconType" => "img",
                                "imageUrl" => "public/uniapp/member/index/menu/new_fenxiao.png",
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "id" => "cox48f75shs0",
                                "imgWidth" => "60",
                                "imgHeight" => "60"
                            ],
                            [
                                "title" => "账户列表",
                                "style" => [
                                    "fontSize" => "60",
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "bgRadius" => 0,
                                    "iconColor" => [
                                        "#000000"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => "ACCOUNT",
                                    "title" => "账户列表",
                                    "wap_url" => "/pages_tool/member/account",
                                    "parent" => "MALL_LINK"
                                ],
                                "icon" => "",
                                "iconType" => "img",
                                "imageUrl" => "public/uniapp/member/index/menu/new_account.png",
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "id" => "5q0mccyypjo0",
                                "imgWidth" => "60",
                                "imgHeight" => "60"
                            ],
                            [
                                "title" => "红包列表",
                                "style" => [
                                    "fontSize" => "60",
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "bgRadius" => 0,
                                    "iconColor" => [
                                        "#000000"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => ""
                                ],
                                "icon" => "",
                                "iconType" => "img",
                                "imageUrl" => "public/uniapp/member/index/menu/new_red_package.png",
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "id" => "1jyhbd1gn5j40",
                                "imgWidth" => "60",
                                "imgHeight" => "60"
                            ],
                            [
                                "title" => "我的预售",
                                "style" => [
                                    "fontSize" => "60",
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "bgRadius" => 0,
                                    "iconColor" => [
                                        "#000000"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => "MAPRESALE_PREFECTURE",
                                    "title" => "我的预售",
                                    "wap_url" => "/pages_promotion/presale/order_list",
                                    "parent" => "MARKETING_LINK"
                                ],
                                "icon" => "",
                                "iconType" => "img",
                                "imageUrl" => "public/uniapp/member/index/menu/new_presale.png",
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "id" => "r18ye6w6l000",
                                "imgWidth" => "60",
                                "imgHeight" => "60"
                            ],
                            [
                                "title" => "单位采购",
                                "icon" => "",
                                "imageUrl" => "public/uniapp/member/index/menu/new_unit_purchase.png",
                                "iconType" => "img",
                                "style" => [
                                    "fontSize" => "60",
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "bgRadius" => 0,
                                    "iconColor" => [
                                        "#000000"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => ""
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "id" => "et2y2d7do6w0",
                                "imgWidth" => "60",
                                "imgHeight" => "60"
                            ],
                            [
                                "title" => "生活订水",
                                "icon" => "",
                                "imageUrl" => "public/uniapp/member/index/menu/new_life_water.png",
                                "iconType" => "img",
                                "style" => [
                                    "fontSize" => "60",
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "bgRadius" => 0,
                                    "iconColor" => [
                                        "#000000"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => ""
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "id" => "11a3m3u72ukw0",
                                "imgWidth" => "60",
                                "imgHeight" => "60"
                            ],
                            [
                                "title" => "投诉建议",
                                "icon" => "",
                                "imageUrl" => "public/uniapp/member/index/menu/new_advice.png",
                                "iconType" => "img",
                                "style" => [
                                    "fontSize" => "60",
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "bgRadius" => 0,
                                    "iconColor" => [
                                        "#000000"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => ""
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "id" => "1ydxsnwlrxmo0",
                                "imgWidth" => "60",
                                "imgHeight" => "60"
                            ],
                            [
                                "title" => "客服入口",
                                "icon" => "",
                                "imageUrl" => "public/uniapp/member/index/menu/new_service.png",
                                "iconType" => "img",
                                "style" => [
                                    "fontSize" => "60",
                                    "iconBgColor" => [],
                                    "iconBgColorDeg" => 0,
                                    "iconBgImg" => "",
                                    "bgRadius" => 0,
                                    "iconColor" => [
                                        "#000000"
                                    ],
                                    "iconColorDeg" => 0
                                ],
                                "link" => [
                                    "name" => ""
                                ],
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "id" => "1jb54egmii1s0",
                                "imgWidth" => "60",
                                "imgHeight" => "60"
                            ]
                        ],
                        "mode" => "graphic",
                        "type" => "img",
                        "showStyle" => "fixed",
                        "rowCount" => 5,
                        "pageCount" => 2,
                        "carousel" => [
                            "type" => "circle",
                            "color" => "#FFFFFF"
                        ],
                        "imageSize" => 30,
                        "aroundRadius" => 25,
                        "font" => [
                            "size" => 12,
                            "weight" => "normal",
                            "color" => "#666666"
                        ],
                        "id" => "cdv0gsi1cw0",
                        "addonName" => "",
                        "componentName" => "GraphicNav",
                        "componentTitle" => "图文导航",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "componentBgColor" => "#FFFFFF",
                        "componentAngle" => "round",
                        "topAroundRadius" => 0,
                        "bottomAroundRadius" => 9,
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 0,
                            "bottom" => 12,
                            "both" => 12
                        ]
                    ]
                ]
            ]
        ]

    ]
];
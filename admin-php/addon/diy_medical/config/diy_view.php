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
        'title' => '医药商城模板', // 模板名称
        'name' => 'official_default_medical', // 模板标识
        'cover' => 'addon/diy_medical/shop/view/public/img/cover.png', // 模板封面图
        'preview' => 'addon/diy_medical/shop/view/public/img/preview.png', // 模板预览图
        'desc' => '医药商城模板', // 模板描述
    ],

    // 主题风格配色，格式可以自由定义扩展，【在uni-app中通过：this.themeStyle... 获取定义的颜色字段，例如：this.themeStyle.main_color】
    'theme' => [],

    // 自定义页面数据，格式：[ 'title' => '页面名称', 'name' => "页面标识", 'value' => [页面数据，json格式] ]
    'data' => [
        [
            'title' => '医药商城模板',
            'name' => "DIY_VIEW_INDEX",
            'value' => [
                "global" => [
                    "title" => "医药商城模板",
                    "pageBgColor" => "#F8F8F8",
                    "topNavColor" => "#1F7CFE",
                    "topNavBg" => true,
                    "navBarSwitch" => true,
                    "navStyle" => "1",
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
                    "bgUrl" => "addon/diy_medical/shop/view/public/img/bg.png",
                    "imgWidth" => "750",
                    "imgHeight" => "486",
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
                        "searchStyle" => 3,
                        "searchLink" => [
                            "name" => ""
                        ],
                        "title" => "搜索 药物 症状 功效",
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
                        "imageUrl" => "",
                        "positionWay" => "fixed",
                        "id" => "5i5xi1gnfps0",
                        "addonName" => "",
                        "componentName" => "Search",
                        "componentTitle" => "搜索框",
                        "isDelete" => 0,
                        "pageBgColor" => "#1F7CFE",
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
                        ]
                    ],
                    [
                        "ornament" => [
                            "type" => "default",
                            "color" => "#EDEDED"
                        ],
                        "list" => [
                            [
                                "title" => "附近门店",
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
                                "imageUrl" => "",
                                "id" => "toebkd1onv40",
                                "bgColorStart" => "#FF6363",
                                "bgColorEnd" => "#FF6363",
                                "textColor" => "#FFFFFF"
                            ],
                            [
                                "title" => "蒙脱石散",
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
                                "imageUrl" => "",
                                "id" => "15izcw5ogtc00",
                                "bgColorStart" => "#F9FEE8",
                                "bgColorEnd" => "#F9FEE8",
                                "textColor" => "#2EB44B"
                            ],
                            [
                                "title" => "感冒灵颗粒",
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
                                "imageUrl" => "",
                                "id" => "1ikprh1sqv6o0",
                                "bgColorStart" => "#FF7011",
                                "bgColorEnd" => "#FF7011",
                                "textColor" => "#FFFFFF"
                            ],
                            [
                                "title" => "奥利司他胶囊",
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
                                "imageUrl" => "",
                                "id" => "225lbcxkjwow0",
                                "bgColorStart" => "#EAFFF7",
                                "bgColorEnd" => "#EAFFF7",
                                "textColor" => "#0DB0F0"
                            ]
                        ],
                        "id" => "5cvp4rzn1yg0",
                        "addonName" => "",
                        "componentName" => "QuickNav",
                        "componentTitle" => "快捷导航",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "componentBgColor" => "",
                        "componentAngle" => "round",
                        "topAroundRadius" => 0,
                        "bottomAroundRadius" => 0,
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 12,
                            "bottom" => 6,
                            "both" => 12
                        ]
                    ],
                    [
                        "list" => [
                            [
                                "imageUrl" => "addon/diy_medical/shop/view/public/img/nav_1.png",
                                "link" => [
                                    "name" => ""
                                ],
                                "imgWidth" => "176",
                                "imgHeight" => "176",
                                "id" => "1ziu868l9sjk0",
                                "title" => "感冒发烧",
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
                                "icon" => "",
                                "iconType" => "img",
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ]
                            ],
                            [
                                "title" => "清热解毒",
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
                                "imageUrl" => "addon/diy_medical/shop/view/public/img/nav_2.png",
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "id" => "1hzfbhvf4ghs0",
                                "imgWidth" => "176",
                                "imgHeight" => "176"
                            ],
                            [
                                "title" => "慢病用药",
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
                                "imageUrl" => "addon/diy_medical/shop/view/public/img/nav_3.png",
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "id" => "7lkvmmqll680",
                                "imgWidth" => "176",
                                "imgHeight" => "176"
                            ],
                            [
                                "title" => "儿童用药",
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
                                "imageUrl" => "addon/diy_medical/shop/view/public/img/nav_4.png",
                                "label" => [
                                    "control" => false,
                                    "text" => "热门",
                                    "textColor" => "#FFFFFF",
                                    "bgColorStart" => "#F83287",
                                    "bgColorEnd" => "#FE3423"
                                ],
                                "id" => "nretwpl5tj40",
                                "imgWidth" => "176",
                                "imgHeight" => "176"
                            ],
                            [
                                "title" => "肠胃用药",
                                "icon" => "",
                                "imageUrl" => "addon/diy_medical/shop/view/public/img/nav_5.png",
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
                                "id" => "12334vufumog0",
                                "imgWidth" => "176",
                                "imgHeight" => "176"
                            ],
                            [
                                "title" => "五官用药",
                                "icon" => "",
                                "imageUrl" => "addon/diy_medical/shop/view/public/img/nav_6.png",
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
                                "id" => "w2ogswcxnsw0",
                                "imgWidth" => "176",
                                "imgHeight" => "176"
                            ],
                            [
                                "title" => "皮肤用药",
                                "icon" => "",
                                "imageUrl" => "addon/diy_medical/shop/view/public/img/nav_7.png",
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
                                "id" => "60znp6wdci80",
                                "imgWidth" => "176",
                                "imgHeight" => "176"
                            ],
                            [
                                "title" => "心脑血管",
                                "icon" => "",
                                "imageUrl" => "addon/diy_medical/shop/view/public/img/nav_8.png",
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
                                "id" => "1fshjsfs9wf40",
                                "imgWidth" => "176",
                                "imgHeight" => "176"
                            ],
                            [
                                "title" => "风湿骨科",
                                "icon" => "",
                                "imageUrl" => "addon/diy_medical/shop/view/public/img/nav_9.png",
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
                                "id" => "203zga4agt6o0",
                                "imgWidth" => "176",
                                "imgHeight" => "176"
                            ],
                            [
                                "title" => "抗菌消炎",
                                "icon" => "",
                                "imageUrl" => "addon/diy_medical/shop/view/public/img/nav_10.png",
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
                                "id" => "1xtwjpjix7280",
                                "imgWidth" => "176",
                                "imgHeight" => "176"
                            ]
                        ],
                        "id" => "3pz6dtsxh5k0",
                        "addonName" => "",
                        "componentName" => "GraphicNav",
                        "componentTitle" => "图文导航",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "componentBgColor" => "#FFFFFF",
                        "componentAngle" => "round",
                        "topAroundRadius" => 20,
                        "bottomAroundRadius" => 0,
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 6,
                            "bottom" => 6,
                            "both" => 0
                        ],
                        "ornament" => [
                            "type" => "default",
                            "color" => "#EDEDED"
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
                        "imageSize" => 40,
                        "aroundRadius" => 25,
                        "font" => [
                            "size" => 14,
                            "weight" => "normal",
                            "color" => "#303133"
                        ]
                    ],
                    [
                        "list" => [
                            [
                                "imageUrl" => "addon/diy_medical/shop/view/public/img/banner.png",
                                "imgWidth" => "1404",
                                "imgHeight" => "560",
                                "link" => [
                                    "name" => ""
                                ],
                                "imageMode" => "scaleToFill",
                                "id" => "7evcgwb7vj80"
                            ]
                        ],
                        "id" => "z5rrn3sudfk",
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
                        ],
                        "indicatorIsShow" => true,
                        "indicatorColor" => "#ffffff",
                        "carouselStyle" => "circle",
                        "indicatorLocation" => "center"
                    ],
                    [
                        "id" => "31gjnmatouw0",
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
                        ],
                        "mode" => "row1-lt-of2-rt",
                        "imageGap" => 10,
                        "list" => [
                            [
                                "imageUrl" => 'addon/diy_medical/shop/view/public/img/mf_left.png',
                                "imgWidth" => "684",
                                "imgHeight" => "748",
                                "previewWidth" => 163.5,
                                "previewHeight" => "188.80px",
                                "link" => [
                                    "name" => ""
                                ],
                                "imageMode" => "scaleToFill"
                            ],
                            [
                                "imageUrl" => 'addon/diy_medical/shop/view/public/img/mf_right1.png',
                                "imgWidth" => "684",
                                "imgHeight" => "356",
                                "previewWidth" => 163.5,
                                "previewHeight" => "89.40px",
                                "link" => [
                                    "name" => ""
                                ],
                                "imageMode" => "scaleToFill"
                            ],
                            [
                                "imageUrl" => 'addon/diy_medical/shop/view/public/img/mf_right2.png',
                                "imgWidth" => "684",
                                "imgHeight" => "356",
                                "previewWidth" => 163.5,
                                "previewHeight" => "89.40px",
                                "link" => [
                                    "name" => ""
                                ],
                                "imageMode" => "scaleToFill"
                            ]
                        ]
                    ],
                    [
                        "style" => "style-2",
                        "sources" => "initial",
                        "ornament" => [
                            "type" => "default",
                            "color" => "#EDEDED"
                        ],
                        "template" => "horizontal-slide",
                        "goodsMarginType" => "default",
                        "goodsMarginNum" => 10,
                        "count" => 6,
                        "goodsId" => [],
                        "nameLineMode" => "single",
                        "imgAroundRadius" => 5,
                        "slideMode" => "scroll",
                        "theme" => "default",
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
                        "goodsNameStyle" => [
                            "color" => "#303133",
                            "control" => true,
                            "fontWeight" => false
                        ],
                        "saleStyle" => [
                            "color" => "#999CA7",
                            "control" => false,
                            "support" => false
                        ],
                        "priceStyle" => [
                            "mainColor" => "#FFFFFF",
                            "mainControl" => true,
                            "lineColor" => "#999CA7",
                            "lineControl" => true,
                            "lineSupport" => true
                        ],
                        "id" => "4v68pukkbiq0",
                        "addonName" => "seckill",
                        "componentName" => "Seckill",
                        "componentTitle" => "秒杀",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "componentBgColor" => "#FFFFFF",
                        "componentAngle" => "round",
                        "topAroundRadius" => 10,
                        "bottomAroundRadius" => 10,
                        "elementBgColor" => "",
                        "elementAngle" => "round",
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 6,
                            "bottom" => 6,
                            "both" => 12
                        ],
                        "progressStyle" => [
                            "control" => false,
                            "support" => false,
                            "currColor" => "#FDBE6C",
                            "bgColor" => "#FCECD7"
                        ],
                        "titleStyle" => [
                            "backgroundImage" => "addon/seckill/component/view/seckill/img/style_title_3_bg.png",
                            "isShow" => true,
                            "leftStyle" => "img",
                            "leftImg" => "addon/seckill/component/view/seckill/img/style_title_3_name.png",
                            "style" => "style-3",
                            "styleName" => "风格3",
                            "leftText" => "",
                            "fontSize" => 16,
                            "fontWeight" => true,
                            "textColor" => "#FFFFFF",
                            "bgColorStart" => "#FA6400",
                            "bgColorEnd" => "#FF287A",
                            "more" => "更多",
                            "moreColor" => "#FFFFFF",
                            "moreFontSize" => 12,
                            "timeBgColor" => "",
                            "timeImageUrl" => "",
                            "moreSupport" => true,
                            "colonColor" => "#FFFFFF",
                            "numBgColorStart" => "#FFFFFF",
                            "numBgColorEnd" => "#FFFFFF",
                            "numTextColor" => "#FD3B54"
                        ]
                    ],
                    [
                        "style" => "style-2",
                        "sources" => "initial",
                        "ornament" => [
                            "type" => "default",
                            "color" => "#EDEDED"
                        ],
                        "count" => 6,
                        "goodsId" => [],
                        "categoryId" => 0,
                        "categoryName" => "请选择",
                        "sortWay" => "default",
                        "nameLineMode" => "single",
                        "imgAroundRadius" => 10,
                        "theme" => "default",
                        "goodsNameStyle" => [
                            "color" => "#303133",
                            "control" => true,
                            "fontWeight" => false,
                            "support" => true
                        ],
                        "saleStyle" => [
                            "color" => "#999CA7",
                            "control" => true,
                            "support" => true
                        ],
                        "priceStyle" => [
                            "mainColor" => "#FF1544",
                            "mainControl" => true,
                            "lineColor" => "#999CA7",
                            "lineControl" => false,
                            "lineSupport" => false
                        ],
                        "id" => "5oaajrk7e4c0",
                        "addonName" => "",
                        "componentName" => "GoodsRecommend",
                        "componentTitle" => "商品推荐",
                        "isDelete" => 0,
                        "pageBgColor" => "",
                        "componentBgColor" => "#1278FE",
                        "componentAngle" => "round",
                        "topAroundRadius" => 10,
                        "bottomAroundRadius" => 10,
                        "elementBgColor" => "#FFFFFF",
                        "elementAngle" => "round",
                        "topElementAroundRadius" => 0,
                        "bottomElementAroundRadius" => 0,
                        "margin" => [
                            "top" => 6,
                            "bottom" => 6,
                            "both" => 12
                        ],
                        "topStyle" => [
                            "title" => "今日推荐",
                            "subTitle" => "大家都在买",
                            "icon" => [
                                "value" => "icondiy icon-system-tuijian",
                                "color" => "#1278FE",
                                "bgColor" => "#FFFFFF"
                            ],
                            "color" => "#FFFFFF",
                            "subColor" => "#FFFFFF",
                            "support" => true
                        ],
                        "bgUrl" => "app/component/view/goods_recommend/img/bg.png",
                        "styleName" => "风格2",
                        "labelStyle" => [
                            "support" => false,
                            "bgColor" => "#FF504D",
                            "title" => "新人专享",
                            "color" => "#FFFFFF"
                        ]
                    ],
                    [
                        "style" => "style-2",
                        "sources" => "initial",
                        "ornament" => [
                            "type" => "default",
                            "color" => "#EDEDED"
                        ],
                        "template" => "row1-of2",
                        "goodsMarginType" => "default",
                        "goodsMarginNum" => 10,
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
                        "id" => "1r8b2e84ro8w",
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
        ]
    ]
];
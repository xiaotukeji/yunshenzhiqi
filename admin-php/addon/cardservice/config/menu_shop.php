<?php
// +----------------------------------------------------------------------
// | 店铺端菜单设置
// +----------------------------------------------------------------------
return [
    [
        'name' => 'SERVICE_GOODS_ADD',
        'title' => '发布项目商品',
        'parent' => 'GOODS_LIST',
        'url' => 'cardservice://shop/service/addgoods',
        'sort' => 13,
        'is_show' => 0,
        'type' => 'button',
    ],
    [
        'name' => 'SERVICE_GOODS_EDIT',
        'title' => '编辑项目商品',
        'parent' => 'GOODS_LIST',
        'url' => 'cardservice://shop/service/editgoods',
        'sort' => 14,
        'is_show' => 0,
        'type' => 'button',
    ],
    [
        'name' => 'CARD_GOODS_ADD',
        'title' => '发布卡项商品',
        'parent' => 'GOODS_LIST',
        'url' => 'cardservice://shop/card/addgoods',
        'sort' => 13,
        'is_show' => 0,
        'type' => 'button',
    ],
    [
        'name' => 'CARD_GOODS_EDIT',
        'title' => '编辑卡项商品',
        'parent' => 'GOODS_LIST',
        'url' => 'cardservice://shop/card/editgoods',
        'sort' => 14,
        'is_show' => 0,
        'type' => 'button',
    ],
    [
        'name' => 'SERVICE_CATEGORY',
        'title' => '项目分类',
        'parent' => 'GOODS_MANAGE',
        'url' => 'cardservice://shop/servicecategory/lists',
        'is_show' => 1,
        'is_control' => 1,
        'is_icon' => 0,
        'sort' => 3,
        'picture' => 'app/shop/view/public/img/icon_new/category_new.png',
        'picture_selected' => 'app/shop/view/public/img/icon_new/category_select.png',
        'child_list' => [
            [
                'name' => 'SERVICE_CATEGORY_ADD',
                'title' => '项目分类添加',
                'url' => 'cardservice://shop/servicecategory/addcategory',
                'is_show' => 0,
                'type' => 'button',
            ],
            [
                'name' => 'SERVICE_CATEGORY_EDIT',
                'title' => '项目分类编辑',
                'url' => 'cardservice://shop/servicecategory/editcategory',
                'is_show' => 0,
                'type' => 'button',
            ],
            [
                'name' => 'SERVICE_CATEGORY_DELETE',
                'title' => '项目分类删除',
                'url' => 'cardservice://shop/servicecategory/deletecategory',
                'is_show' => 0,
                'type' => 'button',
            ],
            [
                'name' => 'SERVICE_CATEGORY_MODIFY_SORT',
                'title' => '项目分类排序',
                'url' => 'cardservice://shop/servicecategory/modifySort',
                'is_show' => 0,
                'type' => 'button',
            ],
        ]
    ],
    /*[
        'name' => 'GOODS_CARD',
        'title' => '卡项',
        'parent' => 'GOODS_LIST',
        'url' => 'cardservice://shop/card/goodscard',
        'sort' => 15,
        'is_show' => 0
    ],
    [
        'name' => 'MEMBER_GOODS_CARD',
        'title' => '会员卡项',
        'parent' => 'MEMBER_INDEX',
        'url' => 'cardservice://shop/card/membergoodscard',
        'sort' => 14,
        'is_show' => 0
    ],
    [
        'name' => 'MEMBER_CARD_GOODS_DETAIL',
        'title' => '卡项详情',
        'parent' => 'GOODS_LIST',
        'url' => 'cardservice://shop/card/detail',
        'sort' => 16,
        'is_show' => 0
    ],*/
];
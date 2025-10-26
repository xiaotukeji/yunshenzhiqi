<?php
// +----------------------------------------------------------------------
// | 平台端菜单设置
// +----------------------------------------------------------------------
return [
    [
        'name' => 'VIRTUALCARD_GOODS_ADD',
        'title' => '发布卡密商品',
        'parent' => 'GOODS_MANAGE',
        'url' => 'virtualcard://shop/goods/addgoods',
        'sort' => 13,
        'is_show' => 0,
        'type' => 'button',
    ],
    [
        'name' => 'VIRTUALCARD_GOODS_EDIT',
        'title' => '编辑卡密商品',
        'parent' => 'GOODS_MANAGE',
        'url' => 'virtualcard://shop/goods/editgoods',
        'sort' => 14,
        'is_show' => 0,
        'type' => 'button',
    ],
    [
        'name' => 'CARMICHAEL_MANAGE',
        'title' => '卡密管理',
        'parent' => 'GOODS_MANAGE',
        'url' => 'virtualcard://shop/goods/carmichael',
        'sort' => 15,
        'is_show' => 0,
        'type' => 'button',
    ],
    [
        'name' => 'ADD_CARMICHAEL',
        'title' => '添加卡密数据',
        'parent' => 'GOODS_MANAGE',
        'url' => 'virtualcard://shop/goods/addCarmichael',
        'sort' => 16,
        'is_show' => 0,
        'type' => 'button',
    ],
    [
        'name' => 'DELETE_CARMICHAEL',
        'title' => '删除卡密',
        'parent' => 'GOODS_MANAGE',
        'url' => 'virtualcard://shop/goods/deleteGoodsVirtual',
        'sort' => 17,
        'is_show' => 0,
        'type' => 'button',
    ],
    [
        'name' => 'EDIT_CARMICHAEL',
        'title' => '添加卡密',
        'parent' => 'GOODS_MANAGE',
        'url' => 'virtualcard://shop/goods/editGoodsVirtual',
        'sort' => 18,
        'is_show' => 0,
        'type' => 'button',
    ],
];

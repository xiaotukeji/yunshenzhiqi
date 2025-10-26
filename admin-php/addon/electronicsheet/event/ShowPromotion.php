<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\electronicsheet\event;

/**
 * 活动展示
 */
class ShowPromotion
{

    /**
     * 活动展示
     * @return array
     */
    public function handle()
    {
        $data = [
            'shop' => [
                [
                    //插件名称
                    'name' => 'electronicsheet',
                    //店铺端展示分类  shop:营销活动   member:互动营销
                    'show_type' => 'tool',
                    //展示主题
                    'title' => '电子面单',
                    //展示介绍
                    'description' => '商家发货主动打印快递面单',
                    //展示图标
                    'icon' => 'addon/electronicsheet/icon.png',
                    //跳转链接
                    'url' => 'electronicsheet://shop/electronicsheet/lists',
                ]
            ]

        ];
        return $data;
    }
}
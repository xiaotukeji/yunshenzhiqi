<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\replacebuy\event;

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

            'admin' => [

            ],
            'shop' => [
                [
                    //插件名称
                    'name' => 'replacebuy',
                    //展示分类（根据平台端设置，admin（平台营销），shop：店铺营销，member:会员营销, tool:应用工具）
                    'show_type' => 'tool',
                    //展示主题
                    'title' => '代客下单',
                    //展示介绍
                    'description' => '帮助客户提交订单',
                    //展示图标
                    'icon' => 'addon/replacebuy/icon.png',
                    //跳转链接
                    'url' => 'replacebuy://shop/replacebuy/index',
                ],

            ],

        ];
        return $data;
    }
}
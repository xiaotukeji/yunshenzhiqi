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
 * 订单来源
 */
class OrderFromList
{

    public function handle()
    {
        $data = [
            'replace' => ['name' => '代客下单']
        ];
        return $data;
    }
}
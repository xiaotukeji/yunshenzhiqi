<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\fenxiao\shop\controller;

use app\shop\controller\BaseShop;

/**
 * 分销市场
 */
class Market extends BaseShop
{
    /**
     * 分销市场
     */
    public function index()
    {

        $data = [
            'site_id' => $this->site_id,
            'name' => 'DIY_FENXIAO_MARKET',
            'support_diy_view' => [ '', 'DIY_FENXIAO_MARKET' ],
        ];
        $edit_view = event('DiyViewEdit', $data, true);

        return $edit_view;
    }

}
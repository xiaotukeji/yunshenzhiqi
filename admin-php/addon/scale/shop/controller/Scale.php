<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\scale\shop\controller;

use app\shop\controller\BaseShop;
use think\App;

/**
 * 电子秤
 */
class Scale extends BaseShop
{

    public function __construct(App $app = null)
    {
        $this->replace = [
            'SCALE_CSS' => __ROOT__ . '/addon/scale/shop/view/public/css',
            'SCALE_JS' => __ROOT__ . '/addon/scale/shop/view/public/js',
            'SCALE_IMG' => __ROOT__ . '/addon/scale/shop/view/public/img',
        ];
        parent::__construct($app);
    }

}
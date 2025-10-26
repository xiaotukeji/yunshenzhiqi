<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace addon\pinfan\event;

use addon\pinfan\model\Pinfan;

/**
 * 启动活动
 */
class OpenPinfan
{

    public function handle($params)
    {
        $pinfan = new Pinfan();
        $res     = $pinfan->cronOpenPinfan($params['relate_id']);
        return $res;
    }
}
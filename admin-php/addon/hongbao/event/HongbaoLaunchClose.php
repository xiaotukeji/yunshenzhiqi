<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\hongbao\event;

use addon\hongbao\model\HongbaoGroup;

/**
 * 关闭瓜分活动
 * Class hongbaoLaunchClose
 * @package addon\hongbao\event
 */
class HongbaoLaunchClose
{
    public function handle($params)
    {
        $hongbao_group = new HongbaoGroup();
        $res = $hongbao_group->cronClosehongbaoLaunch($params['relate_id']);
        return $res;
    }
}
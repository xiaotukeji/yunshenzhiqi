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

use addon\hongbao\model\Hongbao;

/**
 * 关闭瓜分活动  到时模拟瓜分
 * Class hongbaoLaunchClose
 * @package addon\hongbao\event
 */
class HongbaoSimulation
{
    public function handle($params)
    {
        $hongbao = new Hongbao();
        $res = $hongbao->cronHongbaoSimulation($params['relate_id']);
        return $res;
    }
}
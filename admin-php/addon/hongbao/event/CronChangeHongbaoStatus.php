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
 * 修改活动状态
 */
class CronChangeHongbaoStatus
{

    public function handle($params = [])
    {
        $hongbao = new Hongbao();
        $res    = $hongbao->changeHongbaoStatus($params['relate_id']);
        return $res;
    }
}
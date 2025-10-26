<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\fenxiao\event;

use addon\fenxiao\model\Fenxiao;

/**
 * 会员注销
 */
class MemberCancel
{

    /**
     * @param $param
     * @return array
     */
    public function handle($param)
    {
        $fenxiao_model = new Fenxiao();
        $res = $fenxiao_model->CronMemberCancel($param[ 'member_id' ], $param[ 'site_id' ]);
        return $res;
    }
}
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
 * 活动展示
 */
class MemberRegister
{
    /**
     * 会员注册
     * @param $param
     */
    public function handle($param)
    {
        if (isset($param['member_id']) && !empty($param['member_id'])) {
            $fenxiao = new Fenxiao();
            $fenxiao->memberRegister($param['member_id'], $param['site_id']);
        }
    }
}
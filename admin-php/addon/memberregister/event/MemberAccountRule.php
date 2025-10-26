<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace addon\memberregister\event;

use addon\memberregister\model\Register;

/**
 * 会员账户变化规则
 */
class MemberAccountRule
{

    public function handle($data)
    {
        $config = new Register();
        $info   = $config->getConfig($data['site_id']);
        $return = [];
            if ($info['data']['is_use'] == 1) {
                $return['point'] = "会员注册，赠送" . $info['data']['value']['point'] . "积分";
                $return['growth'] = "会员注册，赠送" . $info['data']['value']['growth'] . "成长值";
                $return['balance'] = "会员注册，赠送" . $info['data']['value']['balance'] . "余额";
            }

        return $return;

    }
}
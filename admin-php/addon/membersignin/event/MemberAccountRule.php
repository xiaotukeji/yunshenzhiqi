<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace addon\membersignin\event;

use addon\membersignin\model\Signin;

/**
 * 会员账户变化规则
 */
class MemberAccountRule
{

    public function handle($data)
    {
        $config = new Signin();
        $info   = $config->getConfig($data['site_id']);
        $return['point'] = '';
        $return['growth'] = '';
        if ($info['data']['is_use'] == 1) {
            foreach ($info['data']['value']['reward'] as $v) {
                $return['point'] .= "会员签到" . $v['day'] . "天，赠送" . $v['point'] . "积分；";
                $return['growth'] .= "会员签到" . $v['day'] . "天，赠送" . $v['growth'] . "成长值；";
            }
        }
        return $return;

    }
}
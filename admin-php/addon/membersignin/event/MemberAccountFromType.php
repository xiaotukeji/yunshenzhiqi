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

/**
 * 会员账户变化来源类型
 */
class MemberAccountFromType
{

    public function handle($data)
    {
        $from_type = [
            'point'  => [
                'signin' => [
                    'type_name' => '签到',
                    'type_url'  => '',
                ],
            ],
            'growth' => [
                'signin' => [
                    'type_name' => '签到',
                    'type_url'  => '',
                ],
            ],
        ];
        if ($data == '') {
            return $from_type;
        } else {
            return $from_type[$data];
        }

    }
}
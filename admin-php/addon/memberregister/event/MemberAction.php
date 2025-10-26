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

/**
 * 会员操作
 */
class MemberAction
{
    /**
     * 会员操作
     */
    public function handle($data)
    {
        if ($data['member_action'] == 'memberregister') {
            return success();
        }
        return '';

    }
}
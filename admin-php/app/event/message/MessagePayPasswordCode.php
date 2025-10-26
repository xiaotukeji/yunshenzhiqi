<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\event\message;

use app\model\member\Member;

/**
 * 绑定发送验证码
 */
class MessagePayPasswordCode
{

    public function handle($param)
    {
        //发送订单消息
        if ($param[ "keywords" ] == "MEMBER_PAY_PASSWORD") {
            $member_model = new Member();
            $result = $member_model->paypasswordCode($param);
            return $result;
        }
    }

}
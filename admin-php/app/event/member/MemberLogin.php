<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace app\event\member;

use app\model\message\Message;

/**
 * 登录成功发送通知
 */
class MemberLogin
{

    public function handle($param)
    {
        // 发送通知
        $message_model = new Message();
        $message_model->sendMessage(["keywords" => "LOGIN", "member_id" => $param["member_id"], "site_id" => $param["site_id"]]);
    }

}
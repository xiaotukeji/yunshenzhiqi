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

use addon\membercancel\model\MemberCancel;

/**
 *  会员注销失败通知
 */
class MessageCancelFail
{
    /**
     * @param $param
     * @return array
     */
    public function handle($param)
    {
        if ($param[ "keywords" ] == "USER_CANCEL_FAIL") {
            $model = new MemberCancel();
            return $model->memberCancelFail($param);
        }
    }

}
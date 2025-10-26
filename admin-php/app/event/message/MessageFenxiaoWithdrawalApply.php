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

use addon\fenxiao\model\FenxiaoWithdraw;
use GuzzleHttp\Exception\GuzzleException;

/**
 *  分销提现申请发送消息
 */
class MessageFenxiaoWithdrawalApply
{
    /**
     * @param $param
     * @return void|null
     * @throws GuzzleException
     */
    public function handle($param)
    {
        //发送订单消息
        if ($param[ "keywords" ] == "FENXIAO_WITHDRAWAL_APPLY") {
            $model = new FenxiaoWithdraw();
            return $model->messageFenxiaoWithdrawalApply($param);
        }
    }

}
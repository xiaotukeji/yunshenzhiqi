<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */


namespace addon\cardservice\event;

use addon\cardservice\model\MemberCard;

/**
 * 卡密商品核销
 */
class CardGoodsVerify
{
    /**
     * 执行安装
     */
    public function handle($data)
    {
        if ($data[ 'verify_type' ] == 'cardgoods') {
            return ( new MemberCard() )->verify($data);
        }
    }
}
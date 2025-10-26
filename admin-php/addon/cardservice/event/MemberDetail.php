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

use app\Controller;

/**
 * Class MemberDetail
 * @package addon\cardservice\event
 */
class MemberDetail extends Controller
{

    public function handle($params)
    {
        if ($params[ 'type' ] == 'member_goods_card') {
            $this->assign('member_id', $params['member_id']);
            $template = dirname(realpath(__DIR__)) . '/shop/view/card/member_goods_card.html';
            return $this->fetch($template);
        }
    }

}
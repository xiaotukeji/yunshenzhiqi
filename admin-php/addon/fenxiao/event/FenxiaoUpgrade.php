<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\fenxiao\event;

use addon\fenxiao\model\Fenxiao;

/**
 * 分销商升级
 */
class FenxiaoUpgrade
{
    /**
     * 分销商升级
     * @param $fenxiao_id
     */
    public function handle($fenxiao_id)
    {
        if (!empty($fenxiao_id)) {
            $fenxiao = new Fenxiao();
            $fenxiao->fenxiaoUpgrade($fenxiao_id);
        }
    }
}
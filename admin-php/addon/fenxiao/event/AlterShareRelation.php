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

use addon\fenxiao\model\Fenxiao as FenxiaoModel;

/**
 * 活动展示
 */
class AlterShareRelation
{
    /**
     * 用户分销上下级关系
     * @param $param
     * @return array|void
     */
    public function handle($param)
    {
        $fenxiao_model = new FenxiaoModel();
        return $fenxiao_model->bindRelation([
            'site_id' => $param[ 'site_id' ],
            'member_id' => $param[ 'member_id' ],
            'action' => 'alter_share_relation',
        ]);
    }
}
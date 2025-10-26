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

use addon\fenxiao\model\FenxiaoLevel as FenxiaoLevelModel;

/**
 * 应用安装
 */
class Install
{
    /**
     * 执行安装
     */
    public function handle()
    {
        $site_id = request()->siteid();
        $model = new FenxiaoLevelModel();
        $default_level = $model->getLevelInfo([ [ 'site_id', '=', $site_id ], [ 'is_default', '=', 1 ] ], 'level_id');

        if (empty($default_level[ 'data' ])) {
            $data = [
                'site_id' => $site_id,
                'level_name' => '默认等级',
                'level_num' => 0,
                'one_rate' => '',
                'two_rate' => '',
                'three_rate' => '',
                'upgrade_type' => '2',
                'fenxiao_order_num' => '',
                'fenxiao_order_meney' => '',
                'one_fenxiao_order_num' => '',
                'one_fenxiao_order_money' => '',
                'one_fenxiao_total_order' => '',
                'order_num' => '',
                'order_money' => '',
                'child_num' => '',
                'child_fenxiao_num' => '',
                'one_child_num' => '',
                'one_child_fenxiao_num' => '',
                'is_default' => 1
            ];
            $res = $model->addLevel($data);
            return $res;
        }

        return success();
    }
}
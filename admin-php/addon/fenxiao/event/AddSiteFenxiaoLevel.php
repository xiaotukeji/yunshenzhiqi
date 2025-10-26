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
 * 增加默认分销商等级
 */
class AddSiteFenxiaoLevel
{

    public function handle($param)
    {
        if (!empty($param[ 'site_id' ])) {

            $model = new FenxiaoLevelModel();
            $default_level = $model->getLevelInfo([ [ 'site_id', '=', $param[ 'site_id' ] ], [ 'is_default', '=', 1 ] ], 'level_id');

            if (empty($default_level[ 'data' ])) {
                $data = [
                    'site_id' => $param[ 'site_id' ],
                    'level_name' => '默认等级',
                    'level_num' => 0,
                    'one_rate' => 10.00,
                    'two_rate' => 5.00,
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
        }
    }

}
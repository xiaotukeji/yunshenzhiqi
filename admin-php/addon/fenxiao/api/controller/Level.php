<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\fenxiao\api\controller;

use addon\fenxiao\model\FenxiaoLevel;
use app\api\controller\BaseApi;


/**
 * 分销等级
 */
class Level extends BaseApi
{

    /**
     * 分销商等级列表
     */
    public function lists()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $condition = [
            [ 'status', '=', 1 ],
            [ 'site_id', '=', $this->site_id ],
            [ 'is_default', '=', 0 ]
        ];
        $model = new FenxiaoLevel();
        $info = $model->getLevelList($condition, 'level_id,level_num,level_name,one_rate,two_rate,three_rate,upgrade_type,fenxiao_order_num,fenxiao_order_meney,one_fenxiao_order_num,one_fenxiao_order_money,order_num,order_money,child_num,child_fenxiao_num,one_child_num,one_child_fenxiao_num,one_fenxiao_total_order', 'level_num asc,one_rate asc');

        return $this->response($info);
    }

}
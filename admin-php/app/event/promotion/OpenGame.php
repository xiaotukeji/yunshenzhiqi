<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace app\event\promotion;

use app\model\games\Games;

/**
 * 开启小游戏
 * @author Administrator
 *
 */
class OpenGame
{
    public function handle($param)
    {
        $model  = new Games();
        $result = $model->cronOpenGames($param['relate_id']);
        return $result;
    }
}

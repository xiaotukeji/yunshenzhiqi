<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2015-2025 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 * @author : niuteam
 */

namespace app\api\controller;

use app\model\games\Games;
use app\model\games\Record;

/**
 * 小游戏
 * @author Administrator
 *
 */
class Game extends BaseApi
{
    /**
     * 会员中奖纪录分页列表信息
     */
    public function recordPage()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $page = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;
        $id = $this->params['id'] ?? 0;

        $condition = [
            [ 'game_id', '=', $id ],
            [ 'is_winning', '=', 1 ],
            [ 'member_id', '=', $this->member_id ]
        ];
        $field = 'member_nick_name,points,is_winning,award_name,award_type,relate_id,relate_name,point,balance,create_time';
        $record = new Record();
        $list = $record->getGamesDrawRecordPageList($condition, $page, $page_size, 'create_time desc', $field);
        return $this->response($list);
    }

    /**
     * 最新一条正在进行的活动
     * @return false|string
     */
    public function newestGame()
    {
        $res = ( new Games() )->getFirstInfo([ [ 'site_id', '=', $this->site_id ], [ 'status', '=', 1 ] ], 'game_id,game_type', 'game_id desc');
        return $this->response($res);
    }
}
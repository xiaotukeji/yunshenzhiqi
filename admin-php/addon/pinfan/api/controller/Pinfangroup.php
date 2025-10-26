<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\pinfan\api\controller;

use addon\pinfan\model\PinfanGroup as PinfanGroupModel;
use app\api\controller\BaseApi;

/**
 * 拼团组
 */
class Pinfangroup extends BaseApi
{

    /**
     * 列表信息
     */
    public function lists()
    {
        $goods_id = $this->params['goods_id'] ?? 0;
        if (empty($goods_id)) {
            return $this->response($this->error('', 'REQUEST_GOODS_ID'));
        }

        $pinfan_group_model = new PinfanGroupModel();
        $condition = [
            [ 'ppg.goods_id', '=', $goods_id ],
            [ 'ppg.status', '=', 2 ],// 当前状态:0未支付 1拼团失败 2.组团中3.拼团成功
            [ 'ppg.site_id', '=', $this->site_id ]
        ];
        $list = $pinfan_group_model->getPinfanGoodsGroupList($condition);
        return $this->response($list);
    }

    /**
     * 获取开团信息
     * @return false|string
     */
    public function info()
    {
        $group_id = input('group_id', 0);
        $condition = [
            [ 'pg.group_id', '=', $group_id ],
            [ 'pg.site_id', '=', $this->site_id ]
        ];
        $pinfan_group_model = new PinfanGroupModel();
        $info = $pinfan_group_model->getPinfanGroupDetail($condition);
        if (!empty($info)) {
            $info[ 'data' ][ 'is_self' ] = 0;
            $token = $this->checkToken();
            if ($token[ 'code' ] == 0 && $info[ 'data' ][ 'head_id' ] == $this->member_id) $info[ 'data' ][ 'is_self' ] = 1;
        }
        return $this->response($info);
    }
}
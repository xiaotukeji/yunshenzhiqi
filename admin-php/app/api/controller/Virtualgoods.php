<?php
/**
 * Index.php
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2015-2025 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 * @author : niuteam
 * @date : 2022.8.8
 * @version : v5.0.0.1
 */

namespace app\api\controller;

use app\model\goods\VirtualGoods as VirtualGoodsModel;

class Virtualgoods extends BaseApi
{

    /**
     * 我的虚拟商品
     */
    public function lists()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $virtual_goods_model = new VirtualGoodsModel();
        $condition = [
            ['member_id', '=', $this->member_id ],
        ];
        $is_verify = $this->params['is_verify'] ?? 'all';//是否已核销
        if ($is_verify != 'all') {
            $condition[] = ['is_verify', '=', $is_verify ];
        }

        $page_index = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;
        $res = $virtual_goods_model->getVirtualGoodsPageList($condition, $page_index, $page_size, 'id desc');
        return $this->response($res);
    }

}
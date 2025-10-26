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

namespace addon\pinfan\api\controller;

use addon\pinfan\model\PinfanOrder as PinfanOrderModel;
use app\api\controller\BaseApi;

/**
 * 拼团订单
 * @author Administrator
 *
 */
class Order extends BaseApi
{

    /**
     * 拼团订单详情
     * @return string
     */
    public function detail()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $id = $this->params['id'] ?? 0;//拼团订单主键id
        if (empty($id)) {
            return $this->response($this->error('', 'REQUEST_ID'));
        }
        $pinfan_order_model = new PinfanOrderModel();
        $res = $pinfan_order_model->getPinfanOrderDetail($id, $this->member_id, $this->site_id);
        return $this->response($res);
    }

    /**
     * 拼团列表
     * @return string
     */
    public function page()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $pinfan_order_model = new PinfanOrderModel();
        $condition = array (
            [ "ppo.member_id", "=", $this->member_id ],
            [ "ppo.site_id", "=", $this->site_id ]
        );
        $pintuan_status = $this->params['pintuan_status'] ?? 'all';
        if ($pintuan_status != "all") {
            $condition[] = [ "ppg.status", "=", $pintuan_status ];
        } else {
            $condition[] = [ "ppg.status", "<>", "0" ];//不查询未支付的拼团
        }

        $page_index = $this->params[ 'page' ] ?? 1;
        $page_size = $this->params[ 'page_size' ] ?? PAGE_LIST_ROWS;
        $res = $pinfan_order_model->getPinfanOrderPageList($condition, $page_index, $page_size, "o.pay_time desc");
        if (!empty($res[ 'data' ][ 'list' ])) {
            foreach ($res[ 'data' ][ 'list' ] as $k => $v) {
                $member_list = $pinfan_order_model->getPinfanOrderList([ [ "group_id", "=", $v[ "group_id" ] ] ], "member_img,nickname");
                $res[ 'data' ][ 'list' ][ $k ][ 'member_list' ] = $member_list[ 'data' ];
            }

        }
        return $this->response($res);
    }
}
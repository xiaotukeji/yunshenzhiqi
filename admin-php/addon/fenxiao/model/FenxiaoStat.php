<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\fenxiao\model;

use app\model\BaseModel;
use app\model\system\Stat;

/**
 * 分销订单统计
 */
class FenxiaoStat extends BaseModel
{

    /**
     * 写入新增分销商统计数据
     * @param $params
     * @return array
     */
    public function addFenxiaoMemberStat($params)
    {
        $site_id = $params[ 'site_id' ] ?? 0;
        $stat_data = array (
            'site_id' => $site_id,
            'add_fenxiao_member_count' => 1
        );

        $stat_model = new Stat();

        $result = $stat_model->addShopStat($stat_data);
        return $result;
    }

    /**
     * 分销订单总额统计
     * @param $params
     * @return array
     */
    public function addFenxiaoOrderStat($params)
    {
        $order_id = $params[ 'order_id' ];
        $site_id = $params[ 'site_id' ] ?? 0;
        $order_condition = array (
            [ 'order_id', '=', $order_id ],
            [ 'site_id', '=', $site_id ]
        );
        $order_info = model('order')->getInfo($order_condition);
        if (empty($order_info))
            return $this->error();

        $order_money = $order_info[ 'order_money' ];
        $refund_money = $order_info[ 'refund_money' ];
        $stat_data = array (
            'site_id' => $site_id,
            'fenxiao_order_count' => 1,
            'fenxiao_order_total_money' => $order_money - $refund_money,
        );
        $stat_model = new Stat();

        $result = $stat_model->addShopStat($stat_data);
        return $result;
    }


    /**
     * 分销佣金账户
     * @param int $site_id
     * @return array
     */
    public function getFenxiaoAccountSum($site_id = 0)
    {
        $field = '
                sum(account) as account, 
                sum(account_withdraw_apply) as account_withdraw_apply, 
                sum(account_withdraw) as account_withdraw
                ';
        $info = model('fenxiao')->getInfo([ [ 'site_id', '=', $site_id ] ], $field);
        if ($info[ 'account' ] == null) {
            $info[ 'account' ] = '0.00';
        }
        if ($info[ 'account_withdraw_apply' ] == null) {
            $info[ 'account_withdraw_apply' ] = '0.00';
        }
        if ($info[ 'account_withdraw' ] == null) {
            $info[ 'account_withdraw' ] = '0.00';
        }
        return $this->success($info);
    }
}
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


/**
 * 分销数据
 */
class FenxiaoData extends BaseModel
{

    /**
     * 分销商账户统计
     * @param $site_id
     * @return mixed
     */
    public function getFenxiaoAccountData($site_id)
    {
        $field = 'sum(account) as account,sum(account_withdraw) as account_withdraw,sum(account_withdraw_apply) as account_withdraw_apply';
        $res = model('fenxiao')->getInfo([ [ 'status', 'in', '1,-1' ], [ 'site_id', '=', $site_id ], [ 'is_delete', '=', 0 ] ], $field);

        if (empty($res) || $res[ 'account' ] == null) {
            $res[ 'account' ] = '0.00';
        }

        if (empty($res) || $res[ 'account_withdraw' ] == null) {
            $res[ 'account_withdraw' ] = '0.00';
        }
        if (empty($res) || $res[ 'account_withdraw_apply' ] == null) {
            $res[ 'account_withdraw_apply' ] = '0.00';
        }

        return $res;
    }

    /**
     * 获取分销商申请人数
     * @param $site_id
     * @return int|mixed
     */
    public function getFenxiaoApplyCount($site_id)
    {
        $count = model('fenxiao_apply')->getCount([ [ 'status', '=', 1 ], [ 'site_id', '=', $site_id ] ]);
        return $count;
    }

    /**
     * 获取分销商人数
     * @param $site_id
     * @return int|mixed
     */
    public function getFenxiaoCount($site_id)
    {
        $count = model('fenxiao')->getCount([ [ 'site_id', '=', $site_id ], [ 'is_delete', '=', 0 ] ]);
        return $count;
    }

    /**
     * 统计分销订单总金额
     * @param $site_id
     * @return mixed
     */
    public function getFenxiaoOrderSum($site_id)
    {
        $field = 'sum(real_goods_money) as real_goods_money';
        $res = model('fenxiao_order')->getInfo([ [ 'site_id', '=', $site_id ] ], $field);
        if ($res[ 'real_goods_money' ] == null) {
            $res[ 'real_goods_money' ] = '0.00';
        }
        return $res;
    }
}
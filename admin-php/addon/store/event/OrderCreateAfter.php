<?php

/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\store\event;

use addon\store\model\StoreMember as StoreMemberModel;
use app\model\order\OrderCommon;

/**
 * 添加下单用户为门店用户
 */
class OrderCreateAfter
{
    public function handle($data)
    {
        $order_data = $data['create_data'] ?? [];
        $delivery_store_id = $order_data['delivery_store_id'] ?? 0;
        $member_id = $order_data['member_id'] ?? 0;
        if ($delivery_store_id > 0 && $member_id > 0) {
            //添加门店关注记录
            $shop_member_model = new StoreMemberModel();
            $res = $shop_member_model->addStoreMember($delivery_store_id, $member_id);
            if ($res["code"] < 0) {
                return $res;
            }
        }
        return true;
    }
}

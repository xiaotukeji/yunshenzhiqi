<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace app\event\order;

use app\dict\order\OrderDict;
use app\model\member\Member;
use app\model\member\MemberAccount;
use app\model\member\MemberLevel;
use app\model\order\OrderCommon;
use app\model\verify\Verify;

/**
 * 订单项退款完成后
 */
class OrderRefundFinish
{

    // 行为扩展的执行入口必须是run
    public function handle($data)
    {
        $order_info        = $data['order_info'];
        if (!empty($order_info) && in_array($order_info['order_type'], [OrderDict::store, OrderDict::virtual])) {
            $order_goods_info = $data['order_goods_info'];
            //核销商品操作
            if ($order_info['order_type'] == OrderDict::store) {//自提订单
                $verify_code = $order_info['delivery_code'];
            } else //虚拟订单
                $verify_code = $order_info['virtual_code'];

            $verify_model       = new Verify();
            $verify_condition   = [
                ['verify_code', '=', $verify_code]
            ];
            $verify_info = $verify_model->getVerifyInfo($verify_condition)['data'] ?? [];
            if (!empty($verify_info)) {
                $json_data  = $verify_info['data'];
                $item_array = $json_data['item_array'];
                foreach ($item_array as $k => $v) {
                    if ($v['order_goods_id'] == $order_goods_info['order_goods_id']) {
                        unset($item_array[$k]);
                    }
                }
                sort($item_array);
                $json_data['item_array'] = $item_array;

                $json_string   = json_encode($json_data, JSON_UNESCAPED_UNICODE);
                $verify_data   = [
                    'verify_content_json' => $json_string
                ];
                $verify_result = $verify_model->editVerify($verify_data, $verify_condition);
                if ($verify_result['code'] < 0) {
                    return $verify_result;
                }
            }
        }

    }

}
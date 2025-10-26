<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\weapp\event;

use addon\weapp\model\Weapp;
use app\model\member\Member;
use app\model\system\Cron;

/**
 * 充值订单完成后
 */
class MemberRechargeOrderPayWeappDelivery
{
    public function handle($param)
    {
        try{
            $order_model = new \addon\memberrecharge\model\MemberrechargeOrder();
            $order_info = $order_model->getMemberRechargeOrderInfo([['order_id', '=', $param['relate_id']]])['data'];

            $weapp_model = new Weapp($order_info[ 'site_id' ]);

            // 检测微信小程序是否已开通发货信息管理服务
            $is_trade_managed = $weapp_model->orderShippingIsTradeManaged()['data'];
            if (!$is_trade_managed) {
                return $weapp_model->success();
            }

            //用户信息
            $member_service = new Member();
            $member_info = $member_service->getMemberInfo([
                [ 'site_id', '=', $order_info[ 'site_id' ] ],
                [ 'member_id', '=', $order_info[ 'member_id' ] ]
            ], 'weapp_openid')[ 'data' ];

            // 上传发货信息
            $shipping_list = [
                [
                    'tracking_no' => '', // 物流单号，物流快递发货时必填，示例值: 323244567777 字符字节限制: [1, 128]
                    'express_company' => '', // 物流公司编码，快递公司ID，参见「查询物流公司编码列表」，物流快递发货时必填， 示例值: DHL 字符字节限制: [1, 128]
                    'item_desc' => $weapp_model->handleOrderShippingItemDesc([$order_info['recharge_name']]), // 商品信息，例如：微信红包抱枕*1个，限120个字以内
                    'contact' => [
                        'consignor_contact' => '',
                        'receiver_contact' => ''
                    ]
                ]
            ];
            $data = [
                'site_id' => $order_info[ 'site_id' ],
                'out_trade_no' => $order_info[ 'out_trade_no' ],
                'logistics_type' => Weapp::LOGISTICS_TYPE_VIRTUAL, // 3、虚拟商品，虚拟商品，例如话费充值，点卡等，无实体配送形式
                'delivery_mode' => Weapp::UNIFIED_DELIVERY, // 发货模式，发货模式枚举值：1、UNIFIED_DELIVERY（统一发货）
                'shipping_list' => $shipping_list,
                'weapp_openid' => $member_info[ 'weapp_openid' ], // 用户标识，用户在小程序appid下的唯一标识。 下单前需获取到用户的Openid 示例值: oUpF8uMuAJO_M2pxb1Q9zNjWeS6o 字符字节限制: [1, 128]
                'is_all_delivered' => true
            ];
            $res = $weapp_model->orderShippingUploadShippingInfo($data);
            //dd($data, $res);
            return $res;
        }catch(\Exception $e){
            //dd($e->getFile(), $e->getLine(), $e->getMessage());
            return error(-1, '小程序上传发货信息错误，'.$e->getMessage());
        }
    }
}
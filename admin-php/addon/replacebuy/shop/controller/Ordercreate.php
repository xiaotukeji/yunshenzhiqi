<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\replacebuy\shop\controller;

use addon\replacebuy\model\ReplacebuyOrderCreate as OrderCreateModel;
use app\model\member\MemberAddress as MemberAddressModel;

/**
 * 订单
 * Class Order
 * @package app\shop\controller
 */
class Ordercreate extends Replacebuy
{

    /**
     * 创建订单
     */
    public function create()
    {
        //通过传递的sku_id数据,模拟操作购物车
        $order_create = new OrderCreateModel();

        $buyer_member_id = $this->buyer();
        if ($buyer_member_id <= 0)
            return $order_create->error([], "当前没有登录会员");

//        $address_id = $this->address_id;
//        if ($address_id <= 0)
//            return $order_create->error([], "当前没有选择地址");

        $delivery = input('delivery', '');
        $data = [
            'site_id' => $this->site_id,
            'order_key' => input('order_key', 0),//订单缓存
            'member_id' => $buyer_member_id,
            'is_balance' => input('is_balance', 0),//是否使用余额
            'order_from' => "replace",
            'order_from_name' => "代客下单",
            "buyer_message" => '',
//           'delivery' => [
//                'member_address' => $this->getMemberAddressInfo(),
//           ],
            'delivery' => $delivery ? json_decode($delivery, true) : [],
            'member_address' => $this->getMemberAddressInfo(),
            'app_module' => $this->app_module
        ];

        $res = $order_create->setParam($data)->create();

        return $res;
    }

    /**
     * 计算信息
     */
    public function calculate()
    {
        //通过传递的sku_id数据,模拟操作购物车
        $order_create = new OrderCreateModel();
        $cart = input("cart", "");
        $coupon_id = input("coupon_id", 0);
        $cart_data_result = $this->tranSkuData($cart);
        if ($cart_data_result[ "code" ] < 0) {
            return $cart_data_result;
        }
        $cart_data = $cart_data_result[ "data" ];
        $sku_ids = $cart_data[ "sku_ids" ];
        $nums = $cart_data[ "nums" ];
        $buyer_member_id = $this->buyer();
        if ($buyer_member_id <= 0)
            return $order_create->error([], "当前没有登录会员");

        $data = [
            'sku_ids' => $sku_ids,
            'nums' => $nums,
            'site_id' => $this->site_id,
            'member_id' => $buyer_member_id,
            'is_balance' => input('is_balance', 0),//是否使用余额
            'order_from' => "pc",
            'order_from_name' => "PC",
            'coupon' => [ "coupon_id" => $coupon_id ],
            'delivery' => [
                'member_address' => $this->getMemberAddressInfo(),
            ],
            'is_invoice' => input("is_invoice", 0),
            'invoice_type' => input("invoice_type", 0),
            'invoice_title' => input("invoice_title", ''),
            'taxpayer_number' => input("taxpayer_number", ''),
            'invoice_content' => input("invoice_content", ''),
            'invoice_full_address' => input("invoice_full_address", ''),
            'is_tax_invoice' => input("is_tax_invoice", 0),
            'invoice_email' => input("invoice_email", '')
        ];
        $res = $order_create->calculate($data);
        return $order_create->success($res);
    }

    /**
     * 待支付订单 数据初始化
     * @return \addon\replacebuy\model\unknown|\app\model\order\unknown|array|mixed
     */
    public function payment()
    {
        if (request()->isJson()) {
            //通过传递的sku_id数据,模拟操作购物车
            $order_create = new OrderCreateModel();
            $cart = input("cart", "");
            $cart_data_result = $this->tranSkuData($cart);
            if ($cart_data_result[ "code" ] < 0) {
                return $cart_data_result;
            }

            $cart_data = $cart_data_result[ "data" ];
            $sku_ids = $cart_data[ "sku_ids" ];
            $nums = $cart_data[ "nums" ];

            //查看是否登陆了会员
            $buyer_member_id = $this->buyer();
            if ($buyer_member_id <= 0)
                return $order_create->error([], "当前没有选择会员");

            $delivery = input('delivery', '');
            $data = [
                'sku_ids' => $sku_ids,
                'nums' => $nums,
                'site_id' => $this->site_id,
                'member_id' => $buyer_member_id,
                'is_balance' => input('is_balance', 0),//是否使用余额
                'is_point' => input('is_point', 0),//是否使用积分
                'is_open_card' => input('is_open_card', 0),
                'order_from' => "pc",
                'order_from_name' => "PC",
                'address_id' => $this->address_id,//收货地址ID
                'store_id' => input('store_id', 0),
                'delivery' => $delivery ? json_decode($delivery, true) : [],
            ];

            $res = $order_create->setParam($data)->setStoreId($data['store_id'])->orderPayment();
            return $order_create->success($res);
        }
    }

    /**
     * "格式化"  购物车数据
     * @param $cart
     * @return array
     */
    public function tranSkuData($cart)
    {
        $order_create = new OrderCreateModel();
        if (empty($cart)) {
            return $order_create->error([], "购物车中还没有商品！");
        }
        $cart = json_decode($cart, true);
        $sku_ids = [];
        $nums = [];
        foreach ($cart as $k => $v) {
            $sku_ids[] = $v[ "sku_id" ];
            $nums[ $v[ "sku_id" ] ] = $v[ "num" ];
        }
        return $order_create->success([ "sku_ids" => $sku_ids, "nums" => $nums ]);
    }

    public function getMemberAddressInfo()
    {
        $member_address_model = new MemberAddressModel();
        $address_info = $member_address_model->getMemberAddressInfo([['member_id', '=', $this->buyer()], ['id', '=', $this->address_id]], '*')['data'];
        return $address_info;
    }
}
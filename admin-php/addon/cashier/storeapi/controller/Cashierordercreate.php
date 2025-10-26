<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 上海牛之云网络科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 */

namespace addon\cashier\storeapi\controller;

use addon\cashier\model\order\CashierOrderCreate as CashierOrderCreateModel;
use app\storeapi\controller\BaseStoreApi;

class Cashierordercreate extends BaseStoreApi
{

    /**
     * 商品计算
     * @return false|string
     */
    public function calculate()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $order_create_model = new CashierOrderCreateModel();
        $data = [
            'site_id' => $this->site_id,
            'sku_array' => !empty($this->params[ 'sku_array' ]) ? json_decode($this->params[ 'sku_array' ], true) : [],
            'member_id' => $this->params[ 'member_id' ] ?? 0,//购买会员(可有可无)

            'store_id' => $this->store_id,
            'mobile' => $this->params[ 'mobile' ] ?? '',

            'order_from' => $this->params[ 'app_type' ],
            'order_from_name' => $this->params[ 'app_type_name' ],

            'type' => 'goods',
            'source' => $this->params[ 'source' ] ?? '',//  is_buy 普通购买  cart  购物车   ‘’  参与活动,

            'cashier_type' => 'cashier',
            'create_time' => $this->params[ 'create_time' ] ?? 0,
        ];
        $res = $order_create_model->setParam($data)->calculate();
        return $this->response($this->success($res));
    }

    /**
     * 创建 收银单据
     */
    public function create()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $order_id = $this->params[ 'order_id' ] ?? 0;
        $order_create_model = new CashierOrderCreateModel();
        $data = [
            'site_id' => $this->site_id,//站点id
            'order_key' => $this->params['order_key'] ?? '',
            'order_id' => $order_id,
            'member_id' => $this->params[ 'member_id' ] ?? 0,//购买会员(可有可无)
            'store_id' => $this->store_id ?? 0,

            'order_from' => $this->params[ 'app_type' ],
            'order_from_name' => $this->params[ 'app_type_name' ],
            'type' => 'goods',
            'source' => $this->params[ 'source' ] ?? '',//  is_buy 普通购买  cart  购物车   ‘’  参与活动,
            'remark' => $this->params[ 'remark' ] ?? '',
            'operator' => $this->user_info,//操作人员,
            'cashier_type' => 'cashier',
            'create_time' => $this->params[ 'create_time' ] ?? 0,
        ];
        $res = $order_create_model->setParam($data)->create();
        return $this->response($res);
    }

    /**
     * 会员卡订单
     * @return false|string
     */
//    public function levelCreate()
//    {
//        $token = $this->checkToken();
//        if ($token[ 'code' ] < 0) return $this->response($token);
//        $order_create_model = new CashierOrderCreateModel();
//        $data = [
//            'site_id' => $this->site_id,//站点id
//            'order_key' => $this->params['order_key'] ?? '',
//            'member_id' => $this->params[ 'member_id' ] ?? 0,//购买会员(可有可无)
//
//            'store_id' => $this->store_id ?? 0,
//
//            'remark' => $this->params[ 'remark' ] ?? '',
//            'order_from' => $this->params[ 'app_type' ],
//            'order_from_name' => $this->params[ 'app_type_name' ],
//            'type' => 'level',
//
//            'cashier_type' => 'cashier',
//            'operator' => $this->user_info,//操作人员,
//        ];
//        if (empty($data[ 'sku_array' ])) {
//            return $this->response($this->error('', '缺少必填参数商品数据'));
//        }
//        $res = $order_create_model->create($data);
//        return $this->response($res);
//    }

    /**
     * 充值订单
     * @return false|string
     */
    public function rechargeCreate()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $order_create_model = new CashierOrderCreateModel();
        $data = [
            'site_id' => $this->site_id,
            'sku_array' => !empty($this->params[ 'sku_array' ]) ? json_decode($this->params[ 'sku_array' ], true) : [],//[{'recharge_id':10}, {'money':20}]
            'member_id' => $this->params[ 'member_id' ] ?? 0,//购买会员(可有可无)
            'store_id' => $this->store_id ?? 0,
            'remark' => $this->params[ 'remark' ] ?? '',
            'order_from' => $this->params[ 'app_type' ],
            'order_from_name' => $this->params[ 'app_type_name' ],
            'type' => 'recharge',

            'cashier_type' => 'cashier',
            'create_time' => $this->params[ 'create_time' ] ?? 0,
            'operator' => $this->user_info, // 操作人员
        ];
        $res = $order_create_model->setParam($data)->calculate();
        $data['order_key'] = $res['order_key'];
        $res = $order_create_model->setParam($data)->create();
        return $this->response($res);
    }

    /**
     * 卡项订单
     * @return false|string
     */
    public function cardCreate()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $order_create_model = new CashierOrderCreateModel();
        $data = [
            'site_id' => $this->site_id,//站点id
            'order_key' => $this->params['order_key'] ?? '',
            'member_id' => $this->params[ 'member_id' ] ?? 0,//购买会员(可有可无)
            'store_id' => $this->store_id ?? 0,
            'remark' => $this->params[ 'remark' ] ?? '',
            'order_from' => $this->params[ 'app_type' ],
            'order_from_name' => $this->params[ 'app_type_name' ],
            'type' => 'card',
            'cashier_type' => 'cashier',
            'create_time' => $this->params[ 'create_time' ] ?? 0,
            'operator' => $this->user_info,//操作人员,
        ];
        $res = $order_create_model->setParam($data)->create();
        return $this->response($res);
    }

    /**
     * 卡项订单计算
     * @return false|string
     */
    public function cardCalculate()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $order_create_model = new CashierOrderCreateModel();
        $data = [
            'site_id' => $this->site_id,//站点id
            'sku_array' => !empty($this->params[ 'sku_array' ]) ? json_decode($this->params[ 'sku_array' ], true) : [],
            'member_id' => $this->params[ 'member_id' ] ?? 0,//购买会员(可有可无)
            'store_id' => $this->params[ 'store_id' ] ?? 0,
            'mobile' => $this->params[ 'mobile' ] ?? '',
            'order_from' => $this->params[ 'app_type' ],
            'order_from_name' => $this->params[ 'app_type_name' ],
            'type' => 'card',
            'cashier_type' => 'cashier',

        ];
        $res = $order_create_model->setParam($data)->calculate();
        return $this->response($this->success($res));
    }
}
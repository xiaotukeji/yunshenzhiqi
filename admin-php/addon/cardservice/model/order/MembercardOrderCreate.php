<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\cardservice\model\order;

use addon\cardservice\model\MemberCard;
use app\model\BaseModel;
use app\model\order\Config;
use app\model\order\OrderCreate;
use app\model\order\OrderCreateTool;
use app\model\store\Store;
use app\model\express\Express;
use app\model\system\Pay;
use app\model\express\Config as ExpressConfig;
use app\model\express\Local;
use app\model\goods\Goods as GoodsModel;
use extend\exception\OrderException;

/**
 * 订单创建(会员卡项提货)
 * @author Administrator
 *
 */
class MembercardOrderCreate extends BaseModel
{

    use OrderCreateTool;
    public $member_card_id = 0;
    public $card_id = 0;
    public $card_info = [];

    public function __construct()
    {
        $this->promotion_type = 'cardservice';
        $this->promotion_type_name = '卡项提货';
    }

    /**
     * 订单创建
     */
    public function create()
    {
        //计算
        $this->confirm();
        if ($this->error > 0) {
            return $this->error([ 'error_code' => $this->error ], $this->error_msg);
        }
        //订单创建数据
        $order_insert_data = $this->getOrderInsertData([ 'discount' ], 'invert');
        $order_insert_data[ 'store_id' ] = $this->store_id;
        $order_insert_data[ 'create_time' ] = time();
        $order_insert_data[ 'is_enable_refund' ] = 0;
        //订单类型以及状态
        $this->orderType();
        $order_insert_data[ 'order_type' ] = $this->order_type[ 'order_type_id' ];
        $order_insert_data[ 'order_type_name' ] = $this->order_type[ 'order_type_name' ];
        $order_insert_data[ 'order_status_name' ] = $this->order_type[ 'order_status' ][ 'name' ];
        $order_insert_data[ 'order_status_action' ] = json_encode($this->order_type[ 'order_status' ], JSON_UNESCAPED_UNICODE);

        model('order')->startTrans();
        //循环生成多个订单
        try {
            $this->order_id = model('order')->add($order_insert_data);

            $order_goods_insert_data = [];
            //订单项目表
            foreach ($this->goods_list as &$order_goods_v) {
                $item_order_goods_insert_data = $this->getOrderGoodsInsertData($order_goods_v);
                $item_order_goods_insert_data[ 'card_item_id' ] = $order_goods_v[ 'item_id' ];
                $order_goods_insert_data[] = $item_order_goods_insert_data;
            }
            model('order_goods')->addList($order_goods_insert_data);

            //卡项使用记录
            $card_use_params = [];
            foreach ($this->getOrderGoodsList() as $k => $v) {
                $goods_item = $this->goods_list[ $k ];
                $card_use_params[] = [
                    'item_id' => $goods_item[ 'item_id' ],
                    'num' => $goods_item[ 'num' ],
                    'type' => 'order',
                    'relation_id' => $v[ 'order_goods_id' ],
                    'store_id' => $this->store_id
                ];
            }
            $member_card = new MemberCard();
            $use_result = $member_card->cardUse($card_use_params);
            if ($use_result[ 'code' ] != 0) {
                model('order')->rollback();
                return $use_result;
            }
            //批量库存处理(卡密商品支付后在扣出库存)//todo  可以再商品中设置扣除库存步骤
            $this->batchDecOrderGoodsStock();
            model('order')->commit();
            //订单创建后事件
            $this->orderCreateAfter();
            //生成整体支付单据
            $pay = new Pay();
            $pay->addPay($this->site_id, $this->out_trade_no, $this->pay_type, $this->order_name, $this->order_name, $this->pay_money, '', 'OrderPayNotify', '/pages/order/detail?order_id=' . $this->order_id, $this->order_id, $this->member_id);

            return $this->success($this->out_trade_no);

        } catch (\Exception $e) {
            model('order')->rollback();
            return $this->error('', $e->getMessage());
        }

    }

    /**
     * 计算后的进一步计算(不存缓存,每次都是重新计算)
     * @return array
     */
    public function confirm()
    {
        $order_key = $this->param[ 'order_key' ];
        $this->getOrderCache($order_key);
        //初始化地址
        $this->initMemberAddress();
        //初始化门店信息
        $this->initStore();
        //配送计算
        $this->calculateDelivery();
        //批量校验配送方式
        $this->batchCheckDeliveryType();
        //计算发票相关
        $this->calculateInvoice();
        $this->pay_money = $this->order_money - $this->balance_money;
        //设置过的商品项信息
        return get_object_vars($this);
    }

    /**
     * 订单计算
     */
    public function calculate()
    {
        //初始化会员收货地址
        $this->initMemberAddress();
        //初始化会员账户
        $this->initMemberAccount();
        //初始化门店信息
        $this->initStore();
        $this->member_card_id = $this->param[ 'member_card_id' ];
        $member_card_model = new MemberCard();

        $condition = array (
            [ 'site_id', '=', $this->site_id ],
            [ 'card_id', '=', $this->member_card_id ],
            [ 'member_id', '=', $this->member_id ],
            [ 'status', '=', 1 ]
        );
        $this->card_info = $member_card_model->getCardInfo($condition)[ 'data' ] ?? [];
        if (empty($this->card_info)) throw new OrderException('该卡项不存在！');
        $this->card_id = $this->card_info[ 'card_id' ];
        //销售门店信息
        $goods_model = new GoodsModel();
        $card_goods_info = $goods_model->getGoodsInfo([['goods_id', '=', $this->card_info['goods_id']]], 'sale_store')['data'];
        if(empty($card_goods_info)) throw new OrderException('该卡项商品不存在！');
        if($card_goods_info['sale_store'] != 'all'){
            $this->available_store_ids = trim($card_goods_info['sale_store'], ',');
        }
        //商品列表信息
        $this->getOrderGoodsCalculate();
        //订单计算
        $this->shopOrderCalculate();

        //获取发票相关
        $this->getInovice();
        $this->order_key = create_no();
        $this->setOrderCache(get_object_vars($this), $this->order_key);
        return true;
    }

    /**
     * 待付款订单
     */
    public function orderPayment()
    {
        $this->calculate();
        //查询配送信息
        $this->getDeliveryData();
        //订单初始项
        event('OrderPayment', [ 'order_object' => $this ]);
        return get_object_vars($this);
    }

    /**
     * 获取商品的计算信息
     */
    public function getOrderGoodsCalculate()
    {
        $this->getCardGoodsList();
        return true;
    }

    /**
     * 获取组合套餐商品列表信息
     */
    public function getCardGoodsList()
    {
        $card_info = $this->card_info;
        $card_type = $this->card_info[ 'card_type' ];
        $member_card_item = $this->param[ 'member_card_item' ] ?? [];
        if (!$member_card_item) throw new OrderException('商品不存在');
        foreach ($member_card_item as $item) {
            $join = [
                [ 'goods_sku ngs', 'mgci.sku_id = ngs.sku_id', 'inner' ],
                [ 'site ns', 'mgci.site_id = ns.site_id', 'inner' ]
            ];
            $condition = [
                [ 'mgci.item_id', '=', $item[ 'item_id' ] ],
                [ 'mgci.site_id', '=', $this->site_id ],
                [ 'mgci.card_id', '=', $this->member_card_id ],
                [ 'ngs.goods_state', '=', 1 ],
                [ 'ngs.is_virtual', '=', 0 ]
            ];
            $field = 'mgci.item_id,mgci.num as total_num,mgci.use_num,ngs.sku_id,ngs.sku_name, ngs.sku_no,ngs.price, ngs.discount_price, ngs.cost_price, ngs.stock, ngs.weight, ngs.volume, ngs.sku_image,ngs.site_id, ngs.goods_state, ngs.is_virtual,ngs.is_free_shipping, ngs.shipping_template, ngs.goods_class, ngs.goods_class_name, ngs.goods_id,ngs.sku_spec_format,ngs.goods_name,ns.site_name,ngs.support_trade_type,ngs.supplier_id';
            $goods_info = model('member_goods_card_item')->getInfo($condition, $field, 'mgci', $join);
            if (!empty($goods_info)) {
                $this->is_virtual = $goods_info[ 'is_virtual' ];
                if ($card_type == 'oncecard') {
                    $item[ 'num' ] = min($item[ 'num' ], $goods_info[ 'total_num' ] - $goods_info[ 'use_num' ]);
                } else if ($card_type == 'commoncard') {
                    $item[ 'num' ] = min($item[ 'num' ], $card_info[ 'total_num' ] - $card_info[ 'total_use_num' ]);
                    $card_info[ 'total_use_num' ] += $item[ 'num' ];
                }
                if ($item[ 'num' ] < 1) continue;
                $goods_info[ 'num' ] = $item[ 'num' ];
                $price = $goods_info[ 'discount_price' ];
                $goods_info[ 'price' ] = $price;
                $goods_money = $price * $goods_info[ 'num' ];
                $goods_info[ 'goods_money' ] = $goods_money;
                $promotion_money = 0;
                $goods_info[ 'real_goods_money' ] = $goods_money;
                $goods_info[ 'coupon_money' ] = 0;//优惠券金额
                $goods_info[ 'promotion_money' ] = $promotion_money;//优惠金额

                $this->site_name = $goods_info[ 'site_name' ];
                $this->goods_list[] = $goods_info;
                $order_name = $this->order_name ?? '';
                if ($order_name) {
                    $len = strlen_mb($order_name);
                    if ($len > 200) {
                        $this->order_name = str_sub($order_name, 200);
                    } else {
                        $this->order_name = string_split($order_name, ',', $goods_info[ 'sku_name' ]);
                    }
                } else {
                    $this->order_name = string_split('', ',', $goods_info[ 'sku_name' ]);
                }
                $this->goods_num += $goods_info[ 'num' ];
                $this->goods_money += $goods_info[ 'goods_money' ];
                //以;隔开的商品项
                $goods_list_str = $this->goods_list_str ?? '';
                if ($goods_list_str) {
                    $this->goods_list_str = $goods_list_str . ';' . $goods_info[ 'sku_id' ] . ':' . $goods_info[ 'num' ];
                } else {
                    $this->goods_list_str = $goods_info[ 'sku_id' ] . ':' . $goods_info[ 'num' ];
                }
            }
        }
        if (!$this->goods_list) throw new OrderException('卡项中没有可以提货的商品！');

        return true;
    }

    /**
     * 获取订单计算
     */
    public function shopOrderCalculate()
    {
        $promotion_money = 0;
        foreach ($this->goods_list as &$v) {
            $item_goods_money = $v[ 'goods_money' ];
            $item_promotion_money = $item_goods_money;
            $real_goods_money = $item_goods_money - $item_promotion_money;
            $v[ 'real_goods_money' ] = $real_goods_money;
            $v[ 'promotion_money' ] = $item_promotion_money;
            $promotion_money += $item_promotion_money;
        }
        $this->promotion_money = $promotion_money;
        $this->is_free_delivery = false;//
        //重新计算订单总额
        $this->getOrderMoney();
        //理论上是多余的操作
        if ($this->order_money < 0) {
            $this->order_money = 0;
        }
        //总结计算
        $this->pay_money = $this->order_money;

        return true;
    }

}
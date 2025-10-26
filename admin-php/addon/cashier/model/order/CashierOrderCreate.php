<?php

/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\cashier\model\order;


use addon\cardservice\model\MemberCard;
use app\dict\goods\GoodsDict;
use app\model\BaseModel;
use app\model\order\OrderCreateTool;
use app\model\system\Cron;
use app\model\system\Pay;
use Exception;
use extend\exception\OrderException;
use think\facade\Queue;

/**
 * 订单创建(收银台订单)
 * Class CashierOrderCreate
 * @package addon\cashier\model\order
 */
class CashierOrderCreate extends BaseModel
{
    use OrderCreateTool;

    public $error_printf = [];
    public $real_goods_money = 0;
    public $cashier_order_type;

    public function create()
    {
        $order_key = $this->param['order_key'] ?? '';
        //获取订单缓存
        $this->getOrderCache($order_key);
        $check_error = $this->checkError();
        if ($check_error['code'] < 0) {
            return $check_error;
        }
        model('order')->startTrans();
        try {
            $pay_model = new Pay();
            $this->out_trade_no = $pay_model->createOutTradeNo($this->member_id);
            $this->order_no = $this->createOrderNo();
            $order_type = 5;//收银订单

            //买家信息
            $member_info = $this->member_account ?? [];
            $nickname = $member_info['nickname'] ?? '';//会员昵称
            $mobile = $member_info['mobile'] ?? '';
            $nickname = !empty($nickname) ? $nickname : $mobile;

            $store_name = $this->store_info['store_name'] ?? '';
            $extend = $this->param['extend'] ?? [];
            $sell_time = $this->param['create_time'] ?? 0;
            if ($sell_time == 0) {
                $sell_time = time();
            } else {
                $sell_time = strtotime($sell_time);
            }
            //操作人
            $operator = $this->param['operator'] ?? [];
            $operator_id = $operator['uid'] ?? 0;
            $operator_name = $operator['username'] ?? '';
            //订单来源
            $order_from = 'cashier';
            $order_from_name = (new CashierOrder())->order_from_list[$order_from]['name'] ?? '';
            $cashier_order_model = new CashierOrder();
            //创建订单
            $data_order = [
                'order_no' => $this->order_no,
                'site_id' => $this->site_id,
                'site_name' => $this->site_info['site_name'],
                'order_name' => $this->order_name,
                'out_trade_no' => $this->out_trade_no,

                'member_id' => $this->member_id,
                'name' => $nickname,
                'mobile' => $mobile,

                'pay_money' => $this->pay_money,
                'goods_money' => $this->goods_money,
                'real_goods_money' => $this->real_goods_money,
                'order_money' => $this->order_money,
                'store_id' => $this->store_id,
                'create_time' => time(),
                'order_from' => $order_from,
                'order_from_name' => $order_from_name,
                'order_type' => $order_type,
                'order_type_name' => '收银订单',
                'order_status' => 0,
                'order_status_name' => '待支付',
                'order_status_action' => json_encode($cashier_order_model->order_status[0], JSON_UNESCAPED_UNICODE),
                'cashier_sell_time' => $sell_time,
                'cashier_order_type' => $this->cashier_order_type,
                'cashier_operator_id' => $operator_id,
                'cashier_operator_name' => $operator_name,
                'order_scene' => 'cashier',
                'goods_num' => $this->goods_num ?? 1,
                'remark' => $this->param['remark'] ?? ''
            ];
            $this->order_id = model('order')->add($data_order);

            $insert_order_goods_list = [];
            foreach ($this->goods_list as $goods_v) {
                $card_item_id = $goods_v['card_item_id'] ?? 0;
                //订单项目表
                $insert_order_goods_list[] = [
                    'order_id' => $this->order_id,
                    'site_id' => $this->site_id,
                    'member_id' => $this->member_id,
                    'goods_id' => $goods_v['goods_id'],
                    'sku_id' => $goods_v['sku_id'],
                    'goods_name' => $goods_v['goods_name'],
                    'sku_name' => $goods_v['sku_name'],
                    'sku_no' => $goods_v['sku_no'] ?? '',
                    'sku_image' => $goods_v['goods_image'] ?? '',
                    'spec_name' => $goods_v['spec_name'] ?? '',
                    'price' => $goods_v['price'],
                    'cost_price' => $goods_v['cost_price'] ?? 0,
                    'num' => $goods_v['num'],
                    'goods_money' => $goods_v['goods_money'],
                    'is_virtual' => $goods_v['is_virtual'] ?? 1,
                    'goods_class' => $goods_v['goods_class'],
                    'goods_class_name' => $goods_v['goods_class_name'],
                    'store_id' => $this->store_id,
                    'extend' => json_encode($extend),
                    'real_goods_money' => $goods_v['real_goods_money'],

                    'card_item_id' => $card_item_id,
                    'card_promotion_money' => $goods_v['card_promotion_money'] ?? 0,//次卡抵扣优惠
                    'supplier_id' => $goods_v['supplier_id'] ?? 0,
                    'is_adjust_price' => $goods_v['is_adjust_price'] ?? 0,//是否调整过价格
                ];
            }
            model('order_goods')->addList($insert_order_goods_list);
            //批量扣除库存
//            $this->batchDecOrderGoodsStock();
            //库存转换
            $this->batchGoodsStockTransform();
            //校验库存是否足够
            $this->checkStock();
            model('order')->commit();
            //订单后续任务
            $this->cashierOrderCreateAfter();
            $res = [
                'order_id' => $this->order_id,
                'out_trade_no' => $this->out_trade_no
            ];
            // 生成整体付费支付单据
            $pay_model->addPay($this->site_id, $this->out_trade_no, $this->pay_type, $this->order_name, $this->order_name, $this->pay_money, '', 'CashierOrderPayNotify', '', $this->order_id, $this->member_id);
            return $this->success($res);
        } catch ( Exception $e ) {
            model('order')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 校验错误
     * @return array
     */
    public function checkError()
    {
        if (!empty($this->error)) {
            $error_msg = $this->getError();
            return $this->error(['error_code' => $this->error, 'error_msg' => $error_msg], $error_msg);
        } else {
            return $this->success();
        }
    }

    /**
     * 获取错误
     * @return string
     */
    protected function getError()
    {
        $error_list = [
            'GOODS_STOCK_EMPTY' => '%s库存不足%s',
            'ADDRESS_EMPTY' => '%s收货地址必须选择!%s',
            'TRADE_TYPE_EMPTY' => '%s配送方式必须选择!%s',
            'GOODS_LESS_MIN_NUM' => '%s商品数量不能小于最小购买量%s',
            'GOODS_OUT_MAX_NUM' => '%s商品数量不能超出最大购买量%s',
            'MOBILE_EMPTY' => '%s联系人手机号不能为空%s',
            'NAME_EMPTY' => '%s联系人名称不能为空%s',
            'YUEYUE_ERROR' => '%s %s',
        ];
        $error_msg = $error_list[$this->error] ?? '';
        return sprintf($error_msg, $this->error_printf[0] ?? '', $this->error_printf[1] ?? '');
    }

    /**
     * 订单创建后续事件(收银台专用)
     * @return true
     */
    public function cashierOrderCreateAfter()
    {
//        $log_data = array(
//            'order_id' => $this->order_id,
//            'action' => 'create',
//            'site_id' => $this->site_id,
//            'member_id' => $this->member_id
//        );
//        (new OrderLog())->addLog($log_data);
//        //执行自动关闭
//        $this->addOrderCronClose(); //增加关闭订单自动事件
//        //自动删除时间
//        $this->addOrderCronDelete(); // 增加订单自动删除事件（5分钟内未支付）

        Queue::push('addon\cashier\job\order\CashierOrderCreateAfter', ['create_data' => get_object_vars($this), 'order_object' => $this]);
        return true;
    }

    /**
     * 计算
     * @return array
     */
    public function calculate()
    {
        //初始化仓库门店
        $this->initStore();
        //初始化会员
        $this->initMemberAccount();
        //初始化站点信息
        $this->initSiteData();
        //计算产品项目
        $this->getItemList();
        $this->order_money = $this->real_goods_money;
        $this->pay_money = $this->order_money;
        $this->order_key = create_no();
        $order_cache = get_object_vars($this);
        $this->setOrderCache(get_object_vars($this), $this->order_key);
        //库存转换
        $goods_model = new \app\model\goods\Goods();
        $order_cache['goods_list'] = $goods_model->goodsStockTransform($order_cache['goods_list'], $this->store_id, 'store');
        return $order_cache;
    }

    /**
     * 购买项列表
     * @return true
     * @throws Exception
     */
    public function getItemList()
    {
        $type = $this->param['type'] ?? '';
        $data_result = event('CashierCalculate', ['order_object' => $this], true);
        if (empty($data_result)) {
            $sku_array = $this->param['sku_array'] ?? [];
            if (!$sku_array) throw new OrderException('缺少必要的商品参数！');
            switch ($type) {
                case 'goods':
                    $this->cashier_order_type = 'goods';
                    //处理产品数据

                    //消费分为产品和买单(暂)
                    $product_id_array = [];
                    $money_array = [];//买单
                    foreach ($sku_array as $v) {
                        $money = $v['money'] ?? 0;
                        $sku_id = $v['sku_id'] ?? 0;
                        $num = $v['num'] ?? 1;
                        if ($money > 0) {
                            $money_array[] = ['money' => $money, 'sku_id' => $sku_id, 'num' => $num];
                        } elseif ($sku_id > 0) {
                            $product_id_array[] = $v;
                        }
                    }
                    $this->toCalculate($product_id_array);
                    //无码商品
                    $this->moneyCalculate($money_array);
                    if (empty($this->goods_list)) throw new OrderException('缺少必填参数商品数据');
//                    $this->goods_list = array_reverse($this->goods_list);
                    break;
                case 'card'://卡项
                    $this->cashier_order_type = 'card';
                    $this->cardCalculate();
                    break;
            }
        }
        return true;
    }

    /**
     * 产品计算(主要用于商品)计算
     * @param $product_array
     * @return true
     */
    public function toCalculate($product_array)
    {
        if (!empty($product_array)) {
            //查询商品
            $this->getShopGoodsList($product_array);
            //商品部分的计算
            $this->goodsCalculate();
            $this->order_money = moneyFormat($this->real_goods_money + $this->delivery_money);
            $this->pay_money = $this->order_money;
        }
        return true;
    }

    /**
     * 获取立即购买商品信息
     * @param $sku_array
     * @return true
     */
    public function getShopGoodsList($sku_array)
    {
        $sku_ids = array_column($sku_array, 'sku_id');
        $field = 'gs.site_id,gs.goods_id,gs.sku_id,gs.goods_name,gs.sku_name,gs.spec_name,gs.sku_image,g.goods_image,gs.price as sku_price,gs.sku_no,
        g.is_virtual,g.min_buy,g.max_buy,g.unit,g.goods_spec_format,g.unit,gs.supplier_id,
        g.goods_class,g.goods_class_name,g.is_unify_price,g.pricing_type,
        gs.member_price, gs.is_consume_discount,  gs.discount_config, gs.discount_method, g.category_id,
        (IFNULL(sgs.sale_num, 0)+g.virtual_sale) as sale_num,
        IFNULL(IF(g.is_unify_price = 1,gs.price,sgs.price), gs.price) as price';
        $alias = 'gs';
        $join = [
            ['goods g', 'g.goods_id = gs.goods_id', 'inner'],
            [
                'store_goods_sku sgs',
                'sgs.sku_id = gs.sku_id and sgs.store_id = ' . $this->store_id,
                'left'
            ]
        ];

        //判断是统一库存还是独立库存
        $store_model = new \app\model\store\Store();
        $store_info = $store_model->getStoreInfo([['store_id', '=', $this->store_id]])['data'];
        if ($store_info[ 'stock_type' ] == 'store') {
            $field .= ',IFNULL(sgs.stock, 0) as stock,IFNULL(sgs.real_stock, 0) as real_stock';
            $field .= ',IFNULL(sgs.cost_price, gs.cost_price) as cost_price';
        }else{
            $field .= ',gs.stock,gs.real_stock';
            $field .= ',gs.cost_price';
        }

        $goods_condition = [
            ['gs.is_delete', '=', 0],
            ['sgs.status', '=', 1],
//            [ 'gs.goods_state', '=', 1 ],
            ['gs.sku_id', 'in', $sku_ids],
            ['gs.site_id', '=', $this->site_id]
        ];
        $temp_goods_list = model('goods_sku')->getList($goods_condition, $field, '', $alias, $join);
        if (empty($temp_goods_list)) throw new OrderException('商品不存在！');
        foreach ($temp_goods_list as $k => &$v) {
            $v['sale_num'] = numberFormat($v['sale_num']);
            $v['stock'] = numberFormat($v['stock']);
        }
        $column_goods_list = array_column($temp_goods_list, null, 'sku_id');
        $order_name = '';
        $goods_num = 0;
        $goods_list = [];
        //分配关联商品数据
        unset($v);
        foreach ($sku_array as $k => $v) {
            $sku_id = $v['sku_id'];
            $goods_info = $column_goods_list[$sku_id] ?? 0;
            if (empty($goods_info)) throw new OrderException('存在无法购买的商品！');
            $order_name = string_split($order_name, ',', $goods_info['goods_name']);
            $item_num = $v['num'] ?? 1;
            if (isset($v['price'])) {
                $goods_info['price'] = $v['price'];
                $goods_info['is_adjust_price'] = 1;
            }
            if ($goods_info['goods_class'] != GoodsDict::weigh) {
                if ($item_num < 1) throw new OrderException('商品数量不能小于1！');
            } else {
                if ($item_num < 0) throw new OrderException('称重重量不能小于0！');
                //只有称重商品会只传商品总价
                if (isset($v['goods_money'])) {
                    $goods_info['goods_money'] = $v['goods_money'];
                    if ($v['goods_money'] > 0) {
                        $goods_info['price'] = round($v['goods_money'] / $item_num, 2);
                    } else {
                        $goods_info['price'] = 0;
                    }
                    $goods_info['is_adjust_price'] = 1;
                }
            }
            $goods_info['card_item_id'] = $v['card_item_id'] ?? 0;
            $goods_num += $item_num;
//            $goods_info[ 'trade_type' ] = $goods_info['goods_class'];//业务类型
            $goods_info['num'] = $item_num;
            $goods_info['sku_image'] = explode(',', $goods_info['sku_image'])[0] ?? '';
            $goods_info['goods_image'] = explode(',', $goods_info['goods_image'])[0] ?? '';
            $goods_list[] = $goods_info;
        }
        $this->goods_list = $goods_list;
        $this->order_name = $order_name;
        $this->goods_num = $goods_num;
        return true;
    }

    /**
     * 商品计算
     * @return true
     */
    public function goodsCalculate()
    {
        //具备某个参数(控制是否是单品活动)
        //计算订单总额  ,订单总优惠  ,
        $goods_money = 0;
        $real_goods_money = 0;
        foreach ($this->goods_list as $k => $v) {
            // 计算单价  可能情况 (折扣价  会员价)
            $goods_item = $v;
            $num = $goods_item['num'];//购买数量
            //商品类主体中应该封装一个函数用于获取商品价格(可能还会有关联会员价  满减  折扣....)
            $is_adjust_price = $v['is_adjust_price'] ?? 0;
            if (!$is_adjust_price) {
                //自定义价格的话,不再参与会员价
                if ($v['is_unify_price'] == 1) {
                    $price = moneyFormat($v['sku_price']);
                } else {
                    $price = moneyFormat($v['price']);
                }
                $v['price'] = $price;
                $member_price_result = $this->getGoodsMemberPrice($v);
                if ($member_price_result['code'] >= 0) {
                    $price = $member_price_result['data'];
                    $goods_item['is_member_price'] = true;
                }
                $goods_item['price'] = $price;
            } else {
                $price = $goods_item['price'];
            }
            //称重商品已经拥有了商品总价
            $item_goods_money = $goods_item['goods_money'] ?? moneyFormat($price * $num);
            $goods_item['goods_money'] = $item_goods_money;
            $goods_item['site_id'] = $this->site_id;
//            $min_buy = $goods_item[ 'min_buy' ];
//            $max_buy = $goods_item[ 'max_buy' ];
//            if ($min_buy > 0 && $min_buy > $num)
//                $this->setError('GOODS_LESS_MIN_NUM');
//
//            if ($max_buy > 0 && $max_buy < $num)
//                $this->setError('GOODS_OUT_MAX_NUM');

            $item_real_goods_money = $goods_item['goods_money'];
            $goods_item['real_goods_money'] = $item_real_goods_money;
            //卡项
            $goods_item = $this->itemCardCalculate($goods_item);
            if (empty($goods_item)) {
                unset($this->goods_list[$k]);
                continue;
            } else {
                $item_goods_money = $goods_item['goods_money'];
                $item_real_goods_money = $goods_item['real_goods_money'];
            }
            $goods_money += $item_goods_money;
            $real_goods_money += $item_real_goods_money;
            $goods_item['real_goods_money'] = $item_real_goods_money;
            $this->goods_list[$k] = $goods_item;
        }
        $this->goods_money = $goods_money;
        $this->real_goods_money = $real_goods_money;
        return true;
    }

    /**
     * 商品卡项抵扣(抵扣会将商品单价视为0)
     * @param $goods_item
     * @return array
     */
    public function itemCardCalculate($goods_item)
    {
        $card_item_id = $goods_item['card_item_id'] ?? 0;
        $sku_id = $goods_item['sku_id'];
        if ($card_item_id > 0) {
            $num = $goods_item['num'];
            $member_card_model = new MemberCard();
            $card_item_params = [
                'member_id' => $this->member_id,
                'sku_id' => $sku_id,
                'item_id' => $card_item_id
            ];
            $item_card_result = $member_card_model->getUseCardNum($card_item_params);
            if ($item_card_result['code'] < 0) {
                return [];
            }
            $item_card_data = $item_card_result['data'];
            $card_item_info = $item_card_data['card_item_info'];
            $card_info = $item_card_data['card_info'];
            $goods_item['card_item_info'] = $card_item_info;
            $goods_item['card_info'] = $card_info;
            $card_num = $item_card_data['card_num'];
            $goods_item['card_num'] = $card_num;
            if ($card_num > 0) {
                if ($num > $card_num) {
                    $num = $card_num;
                }
            }
            $goods_item['num'] = $num;
            $price = $goods_item['price'];
            $card_promotion_money = moneyFormat($price * $num);
            $goods_money = moneyFormat($price * $num);
            $goods_item['price'] = $price;
            $goods_item['goods_money'] = $goods_money;
            $real_goods_money = moneyFormat($goods_money - $card_promotion_money);
            $real_goods_money = max($real_goods_money, 0);
            $goods_item['card_promotion_money'] = $card_promotion_money;
            $goods_item['real_goods_money'] = $real_goods_money;
        }
        return $goods_item;
    }

    /**
     * 买单计算
     * @param $money_array
     * @return true
     */
    public function moneyCalculate($money_array)
    {
        if (!empty($money_array)) {
            $goods_image = 'public/uniapp/cashier/cashier-order-money.png';
            foreach ($money_array as $k => $v) {
                $num = $v['num'] ?? 1;
                $item_price = $v['money'];
                if ($item_price > 0) {
                    $sku_id = $v['sku_id'];
                    $item_goods_money = moneyFormat($item_price * $num);
                    $item_order_name = $item_goods_money . '元买单';
                    $sku_name = '无码商品';
                    $this->goods_list[] = [
                        'goods_id' => 0,
                        'sku_id' => $sku_id,
                        'price' => $item_price,
                        'num' => $num,
                        'goods_money' => $item_goods_money,
                        'goods_name' => $item_order_name,
                        'sku_name' => $sku_name,
                        'goods_image' => $goods_image,
                        'spec_name' => '',
                        'goods_class' => 'money',
                        'goods_class_name' => '无码商品',
                        'real_goods_money' => $item_goods_money
                    ];
                    $this->goods_money += $item_goods_money;
                    $this->real_goods_money += $item_goods_money;

                    $this->order_name = string_split($this->order_name, ',', $item_order_name);

                    $this->goods_num += $num;
                }
            }
        }
        return true;
    }

    /**
     * 卡项的计算
     * @return true
     */
    public function cardCalculate()
    {

        $sku_array = $this->param['sku_array'];
        $sku_ids = array_column($sku_array, 'sku_id');
        $sku_num_array = array_column($sku_array, 'num', 'sku_id');
//        $sku_ids = $data['sku_ids'];//sku_id数组weight

        $field = 'gs.site_id,gs.goods_id,gs.sku_id,gs.goods_name,gs.sku_name,gs.spec_name,gs.sku_image,g.goods_image,sgs.price,gs.price as sku_price,gs.sku_no,
        g.is_virtual,g.unit,sgs.stock,g.min_buy,g.max_buy,g.goods_spec_format,g.unit,gs.supplier_id,
        g.goods_class,g.goods_class_name,(sgs.sale_num + g.virtual_sale) as sale_num,g.is_unify_price,
         gs.member_price, gs.is_consume_discount,  gs.discount_config, gs.discount_method, g.category_id';
        $alias = 'gs';
        $join = [
            ['goods g', 'g.goods_id = gs.goods_id', 'inner'],
            [
                'store_goods_sku sgs',
                'sgs.sku_id = gs.sku_id and sgs.store_id = ' . $this->store_id,
                'left'
            ]
        ];
        $goods_condition = [
            ['gs.is_delete', '=', 0],
            ['sgs.status', '=', 1],
            ['gs.sku_id', 'in', $sku_ids],
            ['gs.site_id', '=', $this->site_id],
        ];
        $goods_list = model('goods_sku')->getList($goods_condition, $field, '', $alias, $join);
        $order_name = '';
        $goods_num = 0;
        foreach ($goods_list as $k => $v) {
            $sku_id = $v['sku_id'];
            $order_name = string_split($order_name, ',', $v['goods_name']);
            $item_num = $sku_num_array[$sku_id] ?? 1;

            $goods_num += $item_num;
            $goods_info = $v;
            //自定义价格
            if (isset($item_num['price'])) {
                $goods_info['price'] = $item_num['price'];
                $goods_info['is_adjust_price'] = 1;
            }
            $is_virtual = $is_virtual ?? $v['is_virtual'];
            $goods_info['num'] = $item_num;
            $goods_info['sale_num'] = numberFormat($goods_info['sale_num']);
            $goods_info['stock'] = numberFormat($goods_info['stock']);
            $goods_list[$k] = $goods_info;
        }

        $this->order_name = $order_name;
        $this->goods_num = $goods_num;

        //具备某个参数(控制是否是单品活动)
        //计算订单总额  ,订单总优惠  ,
        $goods_money = 0;
        $real_goods_money = 0;
        foreach ($goods_list as $k => $v) {
            // 计算单价  可能情况 (折扣价  会员价)
            $goods_item = $v;
            //自定义价格的话,不再参与会员价
            $is_adjust_price = $v['is_adjust_price'] ?? 0;

            if (!$is_adjust_price) {
                //商品类主体中应该封装一个函数用于获取商品价格(可能还会有关联会员价  满减  折扣....)
                if ($v['is_unify_price'] == 1) {
                    $price = moneyFormat($v['sku_price']);
                } else {
                    $price = moneyFormat($v['price']);
                }
                $v['price'] = $price;
                $member_price_result = $this->getGoodsMemberPrice($v);
                if ($member_price_result['code'] >= 0) {
                    $price = $member_price_result['data'];
                    $goods_item['is_member_price'] = true;
                }
                $goods_item['price'] = $price;
            } else {
                $price = $goods_item['price'];
            }

            $num = $goods_item['num'];//购买数量
            $item_goods_money = moneyFormat($price * $num);//商品总额()
//            $min_buy = $goods_item[ 'min_buy' ];
//            $max_buy = $goods_item[ 'max_buy' ];
//            if ($min_buy > 0 && $min_buy > $num) {
//                $this->setError('GOODS_LESS_MIN_NUM');
//            }
//            if ($max_buy > 0 && $max_buy < $num) {
//                $this->setError('GOODS_OUT_MAX_NUM');
//            }
            $goods_money += $item_goods_money;
            $item_real_goods_money = $item_goods_money;
            $real_goods_money += $item_real_goods_money;
            $goods_item['goods_money'] = $item_goods_money;
            $goods_item['real_goods_money'] = $item_real_goods_money;

            $goods_item['sku_image'] = explode(',', $goods_item['sku_image'])[0] ?? '';
            $goods_item['goods_image'] = explode(',', $goods_item['goods_image'])[0] ?? '';
            $goods_list[$k] = $goods_item;
        }
        $this->goods_list = $goods_list;
        $this->goods_money = moneyFormat($goods_money);
        $this->real_goods_money = moneyFormat($real_goods_money);
        return true;
    }

    /**
     * 订单自动删除事件（5分钟内未支付）
     * @return true
     */
    public function addOrderCronDelete()
    {
        //计算订单自动关闭时间
        $now_time = time();
        $execute_time = $now_time + 5 * 60; // 5分钟自动删除时间
        $cron_model = new Cron();
        $cron_model->addCron(1, 0, '订单自动删除（5分钟内未支付）', 'CronOrderDelete', $execute_time, $this->order_id);
        return true;
    }

    /**
     * 设置错误
     * @param $error_code
     * @param array $error_printf
     */
    protected function setError($error_code, $error_printf = [])
    {
        $this->error = $error_code;
        $this->error_printf = $error_printf;
    }
}

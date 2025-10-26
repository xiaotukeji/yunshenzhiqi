<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\replacebuy\model;

use app\model\BaseModel;
use app\model\express\Local as LocalModel;
use app\model\goods\Goods;
use app\model\member\MemberAddress;
use app\model\order\OrderCreate;
use app\model\order\OrderCreateTool;
use app\model\store\Store;
use app\model\system\Pay;
use app\model\system\User;
use Exception;

/**
 * 订单创建
 * @author Administrator
 */
class ReplacebuyOrderCreate extends BaseModel
{

    use OrderCreateTool;


    public function __construct()
    {
        $this->promotion_type = 'replacebuy';
        $this->promotion_type_name = '代客下单';
    }

    /**
     * 订单创建
     */
    public function create()
    {
        //读取之前的标识缓存
        $this->confirm();
        $error_result = $this->checkError();
        if ($error_result !== true) {
            return $error_result;
        }
        model('order')->startTrans();
        //循环生成多个订单
        try {
            //订单创建数据
            $order_insert_data = $this->getOrderInsertData([ 'promotion' ], 'invert');
            $order_insert_data[ 'store_id' ] = $this->store_id;
            $order_insert_data[ 'create_time' ] = time();
            $order_insert_data[ 'is_enable_refund' ] = 0;
            //订单类型以及状态
            $this->orderType();
            $order_insert_data[ 'order_type' ] = $this->order_type[ 'order_type_id' ];
            $order_insert_data[ 'order_type_name' ] = $this->order_type[ 'order_type_name' ];
            $order_insert_data[ 'order_status_name' ] = $this->order_type[ 'order_status' ][ 'name' ];
            $order_insert_data[ 'order_status_action' ] = json_encode($this->order_type[ 'order_status' ], JSON_UNESCAPED_UNICODE);

            $this->order_id = model('order')->add($order_insert_data);
            //订单项目表
            $order_goods_insert_data = [];
            foreach ($this->goods_list as &$order_goods_v) {
                $order_goods_insert_data[] = $this->getOrderGoodsInsertData($order_goods_v);
            }
            model('order_goods')->addList($order_goods_insert_data);

            //todo  满减送
            $this->createManjian();
            //扣除余额
            $this->useBalance();
            //库存处理(卡密商品支付后在扣出库存)//todo  可以再商品中设置扣除库存步骤
            $this->batchDecOrderGoodsStock();
            model('order')->commit();

            //日志
            $user_info = (new User)->userInfo($this->param[ 'app_module' ], $this->site_id);
            $this->log = [
                'order_id' => $this->order_id,
                'action' => '商家通过【代客下单】创建订单,管理员账号：' . $user_info[ 'username' ],
                'uid' => $this->member_id,
                'nick_name' => $this->member_account[ 'nickname' ],
                'action_way' => 2,
                'order_status' => 0,
                'order_status_name' => $this->order_type[ 'order_status' ][ 'name' ]
            ];
            //订单创建后事件
            $this->orderCreateAfter();

            //生成支付单据
            $pay = new Pay();
            $pay->addPay($this->site_id, $this->out_trade_no, $this->pay_type, $this->order_name, $this->order_name, $this->pay_money, '', 'OrderPayNotify', '', $this->order_id, $this->member_id);
            //记录订单日志 end
            return $this->success($this->order_id);
        } catch (Exception $e) {
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
        $this->error = 0 ;//清空错误信息
        //初始化地址
        $this->initMemberAddress();
        //初始化门店信息
        $this->initStore();
        //批量校验配送方式
        $this->batchCheckDeliveryType();
        //配送计算
        $this->calculateDelivery();
        //计算余额
        $this->calculateBalcnce();
        $this->pay_money = $this->order_money - $this->balance_money;
        //设置过的商品项信息
        return get_object_vars($this);
    }

    /**
     * 待付款订单
     * @param unknown $data
     */
    public function orderPayment()
    {
        $this->calculate();
        $this->getDeliveryData();
        $this->getAvailableTimeSlots();
        return get_object_vars($this);
    }

    /**
     * 订单计算
     * @param unknown $data
     */
    public function calculate()
    {
        $this->initMemberAddressInfo();
        $this->initMemberAccount();//初始化会员账户

        //商品列表信息
        $this->getOrderGoodsCalculate();
        //订单计算
        $this->shopOrderCalculate();

        //配送计算
        $this->calculateDelivery();
        //批量校验配送方式
        $this->batchCheckDeliveryType();

        $this->order_key = create_no();
        $this->setOrderCache(get_object_vars($this), $this->order_key);
        return true;
    }

    /**
     * 初始化收货地址
     * @param unknown $data
     */
    public function initMemberAddressInfo()
    {
        $delivery_type = $this->param[ 'delivery' ][ 'delivery_type' ] ?? '';
        if (empty($this->param[ 'delivery' ][ 'member_address' ])) {
            $member_address = new MemberAddress();
            $type = 1;
            if ($delivery_type == 'local') {
                $type = 2;
            }
            $this->delivery[ 'member_address' ] = $member_address->getMemberAddressInfo([ [ 'is_default', '=', 1 ], [ 'type', '=', $type ], [ 'member_id', '=', $this->member_id ] ])[ 'data' ] ?? [];
        }
        if (!empty($this->delivery[ 'member_address' ])) {
            if ($delivery_type == 'local') {
                //外卖订单 如果收货地址没有定位的话,就不取用地址
                $type = $this->delivery[ 'member_address' ][ 'type' ] ?? 1;
                if ($type == 1) {
                    $this->delivery[ 'member_address' ] = '';
                }
            }
        }
        return true;
    }

    /**
     * 获取商品的计算信息
     * @param unknown $data
     */
    public function getOrderGoodsCalculate()
    {
        $this->getStoreGoodsList();
        //满减优惠
        $this->manjianPromotion();
        return true;
    }

    /**
     * 获取商品列表信息
     * @param $sku_ids
     * @param $nums
     * @param $member_id
     * @param $site_id
     * @return mixed
     */
    public function getStoreGoodsList()
    {
        $sku_ids = $this->param[ 'sku_ids' ];
        $nums = $this->param[ 'nums' ];
        //组装商品列表
        $alias = 'gs';
        $join = [
            ['site s', 'gs.site_id = s.site_id', 'inner'],
        ];
        $field = 'gs.sku_name,gs.sku_id,gs.sku_no,
            gs.price,gs.discount_price,gs.cost_price,gs.stock,gs.weight,gs.volume,gs.sku_image, 
             gs.member_price,gs.is_consume_discount,gs.discount_config,gs.discount_method,
            gs.site_id,gs.goods_state,gs.is_virtual,gs.supplier_id,
            gs.is_free_shipping,gs.shipping_template,gs.goods_class,gs.goods_class_name,gs.goods_id,gs.sku_spec_format,gs.goods_name,gs.support_trade_type,
            s.site_name';
        $goods_list = model('goods_sku')->getList([ [ 'gs.sku_id', 'in', $sku_ids ], [ 'gs.site_id', '=', $this->site_id ] ], $field, '', $alias, $join);
        if (!empty($goods_list)) {
            foreach ($goods_list as $v) {
                $num = $nums[ $v[ "sku_id" ] ] ?? 0;
                $v[ "num" ] = $num;
                $this->is_virtual = $v[ 'is_virtual' ];
                $price = $this->getGoodsPrice($v)[ 'data' ] ?? 0;
                $v[ 'price' ] = $price;
                $v[ 'goods_money' ] = $price * $v[ 'num' ];
                $v[ 'real_goods_money' ] = $v[ 'goods_money' ];
                $v[ 'coupon_money' ] = 0;//优惠券金额
                $v[ 'promotion_money' ] = 0;//优惠金额
                $v[ 'stock' ] = numberFormat($v[ 'stock' ]);

                $this->site_name = $v[ 'site_name' ];
                $this->goods_list[] = $v;
                $order_name = $this->order_name ?? '';
                if ($order_name) {
                    $len = strlen_mb($order_name);
                    if ($len > 200) {
                        $this->order_name = str_sub($order_name, 200);
                    } else {
                        $this->order_name = string_split($order_name, ',', $v[ 'sku_name' ]);
                    }
                } else {
                    $this->order_name = string_split('', ',', $v[ 'sku_name' ]);
                }
                $this->goods_num += $v[ 'num' ];
                $this->goods_money += $v[ 'goods_money' ];
                //以;隔开的商品项
                $goods_list_str = $this->goods_list_str ?? '';
                if ($goods_list_str) {
                    $this->goods_list_str = $goods_list_str . ';' . $v[ 'sku_id' ] . ':' . $v[ 'num' ];
                } else {
                    $this->goods_list_str = $v[ 'sku_id' ] . ':' . $v[ 'num' ];
                }
            }
        }else{
            $this->setError(1, '您要购买的商品已删除或已下架');
        }

        return true;
    }

    /**
     * 获取店铺订单计算
     */
    public function shopOrderCalculate()
    {
        //满额包邮插件
        $this->freeShippingCalculate();
        //会员等级包邮权益
        $this->memberLevelCalculate();

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

    public function getAvailableTimeSlots() {
        $delivery_type = $this->param[ 'delivery' ][ 'delivery_type' ] ?? '';
        if($delivery_type == 'local'){
            $local_model = new LocalModel();
            $store_info = $local_model->getInfo([[ 'store_id', '=', $this->store_id ] ])['data'];
        }elseif($delivery_type == 'store'){
            $store_model = new Store();
            $store_info = $store_model->getStoreInfo([[ 'store_id', '=', $this->store_id ] ])['data'];
        }else{
            $store_info = [];
        }
        if(empty($store_info)){
            $this->delivery_time =[];
            return false;
        }
        $time_type = $store_info['time_type'];
        $time_week = $store_info['time_week'];
        $delivery_time = $store_info['delivery_time'];
        $intervalMinutes = $store_info['time_interval']; //细分时段
        $advanceDays = $store_info['advance_day'] ; // 提前预约天数
        $maxReservationDays = $store_info['most_day']; // 最大预约天数
        $selectedDays =  ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];; // 列出全部星期
         $timeRanges =  [];
        if ($time_type == 1){
              $time_week_arr = explode(',',$time_week);
              foreach ($selectedDays as $key => $value){
                  if(!in_array($key,$time_week_arr)){
                      unset($selectedDays[$key]);
                  }
              }
        }
        $delivery_time = json_decode($delivery_time,true);
        foreach ($delivery_time as $time_range){
            $timeRanges[] = [secondsToTime($time_range['start_time']),secondsToTime($time_range['end_time'])];
        }
        //初始化结果数组
        $result = [];
        $now = time(); // 获取当前时间戳
        // 计算可预约日期范围
        $startDate = date('Y-m-d', strtotime("+{$advanceDays} days", $now));
        $endDate = date('Y-m-d', strtotime("+{$maxReservationDays} days", strtotime($startDate)));

        $currentDate = $startDate;
        while (strtotime($currentDate) < strtotime($endDate)) {
            $dayOfWeekEn = date('l', strtotime($currentDate));
            if (!in_array($dayOfWeekEn, $selectedDays)) {
                $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
                continue;
            }

            // 转换中文星期
            $dayNumber = date('w', strtotime($currentDate));
            $daysOfWeekZh = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];
            $dayName = $daysOfWeekZh[$dayNumber];

            $timeSlots = [];
            foreach ($timeRanges as $range) {
                list($startTimeStr, $endTimeStr) = $range;

                // 转换为当天时间戳
                $startTimestamp = strtotime("$currentDate $startTimeStr");
                $endTimestamp = strtotime("$currentDate $endTimeStr");
                if ($endTimestamp <= $startTimestamp) continue; // 无效时段

                // 生成有效时间段
                $currentSlot = $startTimestamp;
                while ($currentSlot < $endTimestamp) {
                    $slotEnd = $currentSlot + $intervalMinutes * 60;

                    // 关键过滤：时间段需在当前时间之后
                    if ($currentSlot > $now) {
                        $timeSlots[] = [
                            'start' => date('H:i', $currentSlot),
                            'end' => date('H:i', $slotEnd)
                        ];
                    }
                    $currentSlot = $slotEnd;
                }
            }
            if (!empty($timeSlots)) {
                $result[] = [
                    'date'=> $currentDate,
                    'day' => $dayName,
                    'time_slots' => $timeSlots
                ];
            }
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        }
        ksort($result);
        $this->delivery_time =  $result;
    }

}
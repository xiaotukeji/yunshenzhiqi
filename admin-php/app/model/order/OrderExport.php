<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\order;

use app\model\BaseModel;
use Exception;
use think\facade\Db;

/**
 * 订单导出
 * @author Administrator
 */
class OrderExport extends BaseModel
{
    public $order_field = [
        'order_no' => '订单编号',
        //单商户店铺名称没有必要导出
        //'site_name' => '店铺名称',
        'order_name' => '订单内容',
        'order_from_name' => '订单来源',
        'order_type_name' => '订单类型',
        'order_promotion_name' => '营销活动类型',
        'out_trade_no' => '支付流水号',
        'out_trade_no_2' => '支付流水号（多次支付）',
        'delivery_code' => '整体提货编码',
        'order_status_name' => '订单状态',
        'pay_status' => '支付状态',
        'delivery_status' => '配送状态',
        'pay_type_name' => '支付方式',
        'delivery_type_name' => '配送方式',
        'nickname' => '购买人',
        'name' => '客户姓名',
        'mobile' => '客户手机',
        'telephone' => '客户固定电话',
        'province_name' => '省',
        'city_name' => '市',
        'district_name' => '县',
        'full_address' => '详细地址',
        'buyer_ip' => '客户ip',
        'buyer_ask_delivery_time' => '客户要求配送时间',
        'buyer_message' => '客户留言信息',
        'goods_money' => '商品总金额',
        'delivery_money' => '配送费用',
        'promotion_money' => '订单优惠金额',
        'coupon_money' => '优惠券金额',
        'order_money' => '订单合计金额',
        'adjust_money' => '订单调整金额',
        'balance_money' => '余额支付金额',
        'pay_money' => '抵扣之后应付金额',
        'refund_money' => '订单退款金额',
        'create_time' => '下单时间',
        'pay_time' => '支付时间',
        'delivery_time' => '配送时间',
        'sign_time' => '签收时间',
        'finish_time' => '完成时间',
        'remark' => '卖家留言',
        'goods_num' => '商品件数',
        'delivery_status_name' => '发货状态',
        'is_settlement' => '是否进行结算',
        'delivery_store_name' => '门店名称',
        'promotion_type_name' => '营销类型',
        'form_data' => '表单数据'
    ];

    public $order_field_sample = [
        //单商户店铺名称没有必要导出
        //'site_name' => '店铺名称',
        'order_id' => '订单ID',
        'order_no' => '订单编号',
        'out_trade_no' => '支付流水号',
        'delivery_store_name' => '门店名称',
        'order_from_name' => '订单来源',
        'order_type_name' => '订单类型',
        'promotion_type_name' => '营销类型',
        'order_status_name' => '订单状态',
        'nickname' => '买家昵称',
        'name' => '收货人姓名',
        'mobile' => '收货人电话',
        'full_address' => '收货地址',
        'buyer_message' => '买家留言',
        'remark' => '商家备注',
        'goods_money' => '商品总金额',
        'goods_num' => '商品总件数',
    ];

    //订单商品信息
    public $order_goods_field = [
        'create_time' => '下单时间',
        'sku_name' => '商品名称',
        'sku_no' => '商品编码',
        'goods_class_name' => '商品类型',
        'price' => '商品卖价',
        'cost_price' => '成本价',
        'num' => '购买数量',
        'goods_money' => '商品总价',
        'cost_money' => '成本总价',
        'delivery_status_name' => '配送状态',
        'delivery_no' => '配送单号',
        'refund_status_name' => '退款状态',
        'refund_no' => '退款编号',
        'refund_type' => '退货方式',
        'refund_apply_money' => '退款申请金额',
        'refund_reason' => '退款原因',
        'refund_real_money' => '实际退款金额',
        'refund_delivery_name' => '退款公司名称',
        'refund_delivery_no' => '退款单号',
        'refund_time' => '实际退款时间',
        'refund_refuse_reason' => '退款拒绝原因',
        'refund_action_time' => '申请退款时间',
        'real_goods_money' => '实际商品购买价',
        'refund_remark' => '退款说明',
        'refund_delivery_remark' => '买家退货说明',
        'refund_address' => '退货地址',
        'is_refund_stock' => '是否返还库存',
        'form_data' => '表单数据'
    ];


    public $define_data = [
        'pay_status' => ['type' => 2, 'data' => ['未支付', '已支付']],//支付状态
        'delivery_status' => ['type' => 2, 'data' => ['待发货', '已发货', '已收货']],//配送状态
        'refund_status' => ['type' => 2, 'data' => ['未退款', '已退款']],//退款状态
//        'buyer_ask_delivery_time' => [ 'type' => 1 ],//购买人要求配送时间
        'create_time' => ['type' => 1],//支付时间
        'pay_time' => ['type' => 1],//支付时间
        'delivery_time' => ['type' => 1],//订单配送时间
        'sign_time' => ['type' => 1],//订单签收时间
        'finish_time' => ['type' => 1],//订单完成时间
        'refund_time' => ['type' => 1],//退款到账时间
        'refund_action_time' => ['type' => 1],//实际退款时间
        'is_settlement' => ['type' => 2, 'data' => ['否', '是']],//是否进行结算
        'refund_type' => ['type' => 2, 'data' => [1 => '仅退款', 2 => '退款退货']],//退货方式
        'is_refund_stock' => ['type' => 2, 'data' => ['否', '是']],//是否返还库存
        'form_data' => ['type' => 3],//表单数据
    ];

    /**
     * 查询订单项数据并导出
     * @param $condition
     * @param $condition_desc
     * @param $site_id
     * @param $join
     * @param $is_verify
     * @param $order_label
     * @return array
     */
    public function orderExport($condition, $condition_desc, $site_id, $join, $is_verify, $order_label)
    {
        set_time_limit(0);
        try {
            //预先创建导出的记录
            $data = array(
                'condition' => json_encode($condition_desc),
                'create_time' => time(),
                'type' => 1,//订单
                'status' => 0,
                'site_id' => $site_id
            );
            $records_result = $this->addExport($data);
            $export_id = $records_result['data'];
            if (empty($export_id)) return $this->error(null, '创建导出记录失败');

            //导出字段预处理
            $export_field = $this->order_field;
            $field_value = [];
            $field_key = [];
            $field_key_array = [];
            foreach ($export_field as $k => $v) {
                $field_value[] = $v;
                $field_key[] = "{\$$k}";
                $field_key_array[] = $k;
            }

            //创建目录
            $file_path = 'upload/order_csv/';
            if (!dir_mkdir($file_path)) return $this->error(null, '导出目录创建失败');

            //创建并打开文件
            $file_name = date('YmdHis');
            $file_path = $file_path . $file_name . '.csv';
            $fp = fopen($file_path, 'w');
            fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));

            //写入第一行
            $first_line = implode(',', $field_value);
            fwrite($fp, $first_line . "\n");

            //导出数据
            $alias = 'o';
            $join = [
                ['member m', 'm.member_id = o.member_id', 'left'],
                ['form_data fm', "fm.relation_id = o.order_id and scene = 'order'", 'left'],
            ];
            if ($is_verify != 'all') {
                $join[] = ['verify v', 'v.verify_code = o.virtual_code', 'left'];
            }
            $order_table = Db::name('order')->where($condition)->alias($alias);
            $order_table = $this->parseJoin($order_table, $join);
            $temp_line = implode(',', $field_key) . "\n";
            $table_field = 'o.*,m.nickname,fm.form_data';
            $order_table->field($table_field)->chunk(5000, function ($item_list) use ($fp, $temp_line, $field_key_array) {
                //写入导出信息
                $this->itemExport($item_list, $field_key_array, $temp_line, $fp);
                unset($item_list);
            }, 'o.order_id');
            $order_table->removeOption();
            fclose($fp);  //每生成一个文件关闭
            unset($order_table);

            //更改导出记录
            $records_data = array(
                'path' => $file_path,
                'status' => 1
            );
            $records_condition = array(
                ['export_id', '=', $export_id]
            );
            $this->editExport($records_data, $records_condition);

            return $this->success();
        } catch ( Exception $e ) {
            return $this->error([], $e->getMessage() . $e->getFile() . $e->getLine());
        }
    }

    /**
     * 查询订单项数据并导出
     * @param $condition
     * @param $condition_desc
     * @param $site_id
     * @param $is_verify
     * @param $order_label
     * @return array
     */
    public function orderGoodsExport($condition, $condition_desc, $site_id, $is_verify, $order_label)
    {
        set_time_limit(0);
        $is_install_supply = addon_is_exit('supply');
        try {
            //预先创建导出的记录
            $data = array(
                'condition' => json_encode($condition_desc),
                'create_time' => time(),
                'type' => 2,//订单项
                'status' => 0,
                'site_id' => $site_id
            );
            $records_result = $this->addExport($data);
            $export_id = $records_result['data'];
            if (empty($export_id)) return $this->error(null, '创建导出记录失败');

            //导出字段预处理
            $export_field = array_merge($this->order_field_sample, $this->order_goods_field);
            if($is_install_supply) $export_field['supplier_name'] = '供应商';
            $field_value = [];
            $field_key = [];
            $field_key_array = [];
            foreach ($export_field as $k => $v) {
                $field_value[] = $v;
                $field_key[] = "{\$$k}"; //为了防止部分代码被筛选中替换, 给变量前后两边增加字符串
                $field_key_array[] = $k;
            }

            //创建目录
            $file_path = 'upload/order_csv/';
            if (!dir_mkdir($file_path)) return $this->error(null, '导出目录创建失败');

            //创建并打开文件
            $file_name = date('YmdHis');//csv文件名
            $file_path = $file_path . $file_name . '.csv';
            $fp = fopen($file_path, 'w');
            fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));

            //写入第一行表头
            $first_line = implode(',', $field_value);
            fwrite($fp, $first_line . "\n");

            //导出数据
            $alias = 'og';
            $join = [
                ['order o', 'o.order_id = og.order_id', 'left'],
                ['member m', 'm.member_id = og.member_id', 'left'],
                ['form_data fm', "fm.relation_id = og.order_goods_id and scene = 'goods'", 'left'],
            ];
            if ($is_verify != 'all') {
                $join[] = ['verify v', 'v.verify_code = o.virtual_code', 'left'];
            }
            if($is_install_supply){
                $join[] = ['supplier s', 'og.supplier_id = s.supplier_id', 'left'];
            }
            //查询字段
            $order_field = 'o.create_time,o.order_id,o.order_no,o.site_name,o.order_name,o.order_from_name,o.order_type_name,o.order_promotion_name,o.out_trade_no,o.out_trade_no_2,o.delivery_code,o.order_status_name,o.pay_status,o.delivery_status,o.refund_status,o.pay_type_name,o.delivery_type_name,o.name,o.mobile,o.telephone,o.full_address,o.buyer_ip,o.buyer_ask_delivery_time,o.buyer_message,o.goods_money,o.delivery_money,o.promotion_money,o.coupon_money,o.order_money,o.adjust_money,o.balance_money,o.pay_money,o.refund_money,o.pay_time,o.delivery_time,o.sign_time,o.finish_time,o.remark,o.goods_num,o.delivery_status_name,o.is_settlement,o.delivery_store_name,o.promotion_type_name,o.address,m.nickname';
            $order_goods_field = 'og.order_goods_id,og.sku_name,og.sku_no,og.is_virtual,og.goods_class_name,og.price,og.cost_price,og.num,og.goods_money,og.cost_money,og.delivery_no,og.refund_no,og.refund_type,og.refund_apply_money,og.refund_reason,og.refund_real_money,og.refund_delivery_name,og.refund_delivery_no,og.refund_time,og.refund_refuse_reason,og.refund_action_time,og.real_goods_money,og.refund_remark,og.refund_delivery_remark,og.refund_address,og.is_refund_stock,og.refund_status_name,fm.form_data';
            if($is_install_supply){
                $order_goods_field .= ',IF(s.title is null, "", s.title) as supplier_name';
            }
            $table_field = $order_field . ',' . $order_goods_field;
            $order_table = Db::name('order_goods')->where($condition)->alias($alias);
            $order_table = $this->parseJoin($order_table, $join);
            $temp_line = implode(',', $field_key) . "\n";
            $export_order_field = $this->order_field_sample;
            $export_order_id = 0;
            $order_table->field($table_field)->chunk(5, function ($item_list) use ($fp, $temp_line, $field_key_array, $export_order_field, &$export_order_id) {
                $item_list = $item_list->toArray();
                //数据合并处理
                foreach($item_list as &$val){
                    if($val['order_id'] != $export_order_id){
                        $export_order_id = $val['order_id'];
                    }else{
                        foreach($export_order_field as $field=>$field_name){
                            $val[$field] = '';
                        }
                    }
                }
                //写入导出信息
                $this->itemExport($item_list, $field_key_array, $temp_line, $fp);
                unset($item_list);
            });
            $order_table->removeOption();
            fclose($fp);  //每生成一个文件关闭
            unset($order_table);

            //更新导出记录
            $records_data = array(
                'path' => $file_path,
                'status' => 1
            );
            $records_condition = array(
                ['export_id', '=', $export_id]
            );
            $this->editExport($records_data, $records_condition);

            return $this->success();
        } catch ( Exception $e ) {
            return $this->error([], $e->getMessage() . $e->getLine());
        }
    }

    /**
     * 查询订单项数据并导出
     * @param $condition
     * @param $condition_desc
     * @param int $site_id
     * @return array
     */
    public function orderRefundExport($condition, $condition_desc, $site_id = 0)
    {
        set_time_limit(0);
        try {
            //预先创建导出的记录
            $data = array(
                'condition' => json_encode($condition_desc),
                'create_time' => time(),
                'status' => 0,
                'site_id' => $site_id
            );
            $records_result = $this->addRefundExport($data);
            $export_id = $records_result['data'];
            if (empty($export_id)) return $this->error(null, '创建导出记录失败');

            //创建目录
            $file_path = 'upload/order_csv/';
            if (!dir_mkdir($file_path)) return $this->error(null, '导出目录创建失败');

            //创建并打开文件
            $file_name = date('YmdHis');
            $file_path = $file_path . $file_name . '.csv';
            $fp = fopen($file_path, 'w'); //生成临时文件
            fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));

            //导出字段预处理
            $export_field = array_merge($this->order_goods_field, $this->order_field);
            $field_value = [];
            $field_key = [];
            $field_key_array = [];
            foreach ($export_field as $k => $v) {
                $field_value[] = $v;
                $field_key[] = "{\$$k}";
                $field_key_array[] = $k;
            }

            //写入第一行表头
            $first_line = implode(',', $field_value);
            fwrite($fp, $first_line . "\n");

            //导出数据
            $alias = 'og';
            $join = [
                ['order o', 'o.order_id = og.order_id', 'left']
            ];
            $order_field = 'o.order_no,o.site_name,o.order_name,o.order_from_name,o.order_type_name,o.order_promotion_name,o.out_trade_no,o.out_trade_no_2,o.delivery_code,o.order_status_name,o.pay_status,o.delivery_status,o.refund_status,o.pay_type_name,o.delivery_type_name,o.name,o.mobile,o.telephone,o.full_address,o.buyer_ip,o.buyer_ask_delivery_time,o.buyer_message,o.goods_money,o.delivery_money,o.promotion_money,o.coupon_money,o.order_money,o.adjust_money,o.balance_money,o.pay_money,o.refund_money,o.pay_time,o.delivery_time,o.sign_time,o.finish_time,o.remark,o.goods_num,o.delivery_status_name,o.is_settlement,o.delivery_store_name,o.promotion_type_name,o.address';
            $order_goods_field = 'og.order_goods_id,og.sku_name,og.sku_no,og.is_virtual,og.goods_class_name,og.price,og.cost_price,og.num,og.goods_money,og.cost_money,og.delivery_no,og.refund_no,og.refund_type,og.refund_apply_money,og.refund_reason,og.refund_real_money,og.refund_delivery_name,og.refund_delivery_no,og.refund_time,og.refund_refuse_reason,og.refund_action_time,og.real_goods_money,og.refund_remark,og.refund_delivery_remark,og.refund_address,og.is_refund_stock,og.refund_status_name';
            $table_field = $order_field . ',' . $order_goods_field;
            $order_table = Db::name('order_goods')->where($condition)->alias($alias);
            $order_table = $this->parseJoin($order_table, $join);
            $temp_line = implode(',', $field_key) . "\n";
            $order_table->field($table_field)->chunk(5000, function ($item_list) use ($fp, $temp_line, $field_key_array) {
                //写入导出信息
                $this->itemExport($item_list, $field_key_array, $temp_line, $fp);
                unset($item_list);
            });
            $order_table->removeOption();
            fclose($fp);
            unset($order_table);

            //将同步导出记录状态
            $records_data = array(
                'path' => $file_path,
                'status' => 1
            );
            $records_condition = array(
                ['export_id', '=', $export_id]
            );
            $this->editRefundExport($records_data, $records_condition);

            return $this->success();
        } catch ( Exception $e ) {
            return $this->error([], $e->getMessage() . $e->getLine());
        }
    }

    /**
     *
     * @param $db_obj
     * @param $join
     * @return mixed
     */
    public function parseJoin($db_obj, $join)
    {
        foreach ($join as $item) {
            list($table, $on, $type) = $item;
            $type = strtolower($type);
            switch ($type) {
                case 'left':
                    $db_obj = $db_obj->leftJoin($table, $on);
                    break;
                case 'inner':
                    $db_obj = $db_obj->join($table, $on);
                    break;
                case 'right':
                    $db_obj = $db_obj->rightjoin($table, $on);
                    break;
                case 'full':
                    $db_obj = $db_obj->fulljoin($table, $on);
                    break;
                default:
                    break;
            }
        }
        return $db_obj;
    }

    /**
     * 给csv写入新的数据
     * @param $item_list
     * @param $field_key
     * @param $temp_line
     * @param $fp
     */
    public function itemExport($item_list, $field_key, $temp_line, $fp)
    {
        if(method_exists($item_list, 'toArray')){
            $item_list = $item_list->toArray();
        }
        $item_list = $this->handleData($item_list, $field_key);
        foreach ($item_list as $k => $item_v) {
            $new_line_value = $temp_line;

            if (isset($item_v['goods_num']) && $item_v['goods_num'] !== '') {
                $item_v['goods_num'] = numberFormat($item_v['goods_num']);
            }
            if (isset($item_v['num']) && $item_v['num'] !== '') {
                $item_v['num'] = numberFormat($item_v['num']);
            }

            //省市县
            if($item_v['full_address'] !== ''){
                $address_arr = explode('-', $item_v['full_address']);
                $item_v['province_name'] = !empty($address_arr[0]) ? $address_arr[0] : '';
                $item_v['city_name'] = !empty($address_arr[1]) ? $address_arr[1] : '';
                $item_v['district_name'] = !empty($address_arr[2]) ? $address_arr[2] : '';
            }else{
                $item_v['province_name'] = '';
                $item_v['city_name'] = '';
                $item_v['district_name'] = '';
            }

            foreach ($item_v as $key => $value) {
                $value = trim($value);

                if ($key == 'full_address' && !empty($value)) {
                    $address = $item_v['address'] ?? '';
                    $value = $value . $address;
                }
                //CSV比较简单，记得转义 逗号就好
                $values = str_replace(',', '\\', $value . "\t");
                $values = str_replace("\n", '', $values);
                $values = str_replace("\r", '', $values);
                $new_line_value = str_replace("{\$$key}", $values, $new_line_value);
            }
            //写入第一行表头
            fwrite($fp, $new_line_value);
            //销毁变量, 防止内存溢出
            unset($new_line_value);
        }
    }

    /**
     *  数据处理
     * @param $data
     * @param $field
     * @return array
     */
    public function handleData($data, $field)
    {
        $define_data = $this->define_data;
        foreach ($data as $k => $v) {
            //获取键
            $keys = array_keys($v);
            foreach ($keys as $key) {
                if($v[$key] === '') continue;
                if (in_array($key, $field)) {
                    if (array_key_exists($key, $define_data)) {
                        $type = $define_data[$key]['type'];
                        switch ($type) {
                            case 1:
                                $data[$k][$key] = time_to_date((int)$v[$key]);
                                break;
                            case 2:
                                $define_data_data = $define_data[$key]['data'];
                                $data[$k][$key] = !empty($v[$key]) ? $define_data_data[$v[$key]] : '';
                                break;
                            case 3:
                                if (!empty($v[$key])) {
                                    $form_data = json_decode($v[$key], true);
                                    $form_content = '';
                                    if (is_array($form_data)) {
                                        foreach ($form_data as $item) {
                                            $form_content .= $item['value']['title'] . '：' . $item['val'] . '；';
                                        }
                                    }
                                    $data[$k][$key] = $form_content;
                                }
                                break;
                        }
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 添加导出记录
     * @param $data
     * @return array
     */
    public function addExport($data)
    {
        $res = model('order_export')->add($data);
        return $this->success($res);
    }

    /**
     * 更新导出记录
     * @param $data
     * @param $condition
     * @return array
     */
    public function editExport($data, $condition)
    {
        $res = model('order_export')->update($data, $condition);
        return $this->success($res);
    }

    /**
     * 删除导出记录
     * @param $condition
     * @return array
     */
    public function deleteExport($condition)
    {
        //先查询数据
        $list = model('order_export')->getList($condition, '*');
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                if (file_exists($v['path'])) {
                    //删除物理文件路径
                    if (!unlink($v['path'])) {
                        //失败
                    } else {
                        //成功
                    }
                }
            }
            $res = model('order_export')->delete($condition);
        }

        return $this->success($res ?? '');
    }

    /**
     * 获取导出记录
     * @param $condition
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getExport($condition, $field = '*', $order = '')
    {
        $list = model('order_export')->getList($condition, $field, $order);
        return $this->success($list);
    }

    /**
     * 导出记录
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getExportPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*')
    {
        $list = model('order_export')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }

    /**
     * 删除导出记录
     * @param $condition
     * @return array
     */
    public function deleteRefundExport($condition)
    {
        $res = model('order_refund_export')->delete($condition);
        return $this->success($res);
    }

    /**
     * 添加导出记录
     * @param $data
     * @return array
     */
    public function addRefundExport($data)
    {
        $res = model('order_refund_export')->add($data);
        return $this->success($res);
    }

    /**
     * 更新导出记录
     * @param $data
     * @param $condition
     * @return array
     */
    public function editRefundExport($data, $condition)
    {
        $res = model('order_refund_export')->update($data, $condition);
        return $this->success($res);
    }

    /**
     * 获取导出记录
     * @param $condition
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getRefundExport($condition, $field = '*', $order = '')
    {
        $list = model('order_refund_export')->getList($condition, $field, $order);
        return $this->success($list);
    }

    /**
     * 导出记录
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getRefundExportPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*')
    {
        $list = model('order_refund_export')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }
}

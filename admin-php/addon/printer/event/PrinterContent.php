<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\printer\event;

use addon\printer\model\Printer;
use addon\printer\model\PrinterTemplate;
use app\model\order\OrderCommon as OrderCommonModel;
use app\model\shop\Shop;
use app\Controller;
use app\model\store\Store;
use app\model\system\Site;

/**
 * 打印内容
 */
class PrinterContent extends Controller
{

    public function handle($params)
    {

        if ($params[ 'type' ] == 'goodsorder') {
            $stage = $params[ 'printer_type' ];//打印类型，手动打印、支付、收货
            $order_id = $params[ 'order_id' ];

            $order_common_model = new OrderCommonModel();
            $order_info = $order_common_model->getOrderDetail($order_id)[ 'data' ];
            $site_id = $order_info[ 'site_id' ];
            $store_id = $order_info[ 'store_id' ];

            //获取店铺信息
            $shop_model = new Shop();
            $shop_info = $shop_model->getShopInfo([ [ 'site_id', '=', $site_id ] ])[ 'data' ];

            $site_name = ( new Site() )->getSiteInfo([ [ 'site_id', '=', $site_id ] ], 'site_name')[ 'data' ][ 'site_name' ] ?? '';

            //获取打印机列表
            $print_model = new Printer();
            $printer_condition = [
                [ 'site_id', '=', $site_id ]
            ];
            if (addon_is_exit('store', $site_id)) $printer_condition[] = [ 'store_id', '=', $store_id ];
            //指定打印机
            if(isset($params['printer_ids']) && $params['printer_ids'] != 'all'){
                if(empty($params['printer_ids'])) $params['printer_ids'] = '0';
                $printer_condition[] = ['printer_id', 'in', $params['printer_ids']];
            }

            switch ( $stage ) {
                case 'order_pay':
                    $printer_condition[] = [ 'order_pay_open', '=', 1 ];
                    $printer_condition[] = [ 'order_pay_order_type', 'like', '%,' . $order_info[ 'order_type' ] . ',%' ];
                    break;
                case 'take_delivery':
                    $printer_condition[] = [ 'take_delivery_open', '=', 1 ];
                    $printer_condition[] = [ 'take_delivery_order_type', 'like', '%,' . $order_info[ 'order_type' ] . ',%' ];
                    break;
                case 'manual':
                    $printer_condition[] = [ 'manual_open', '=', 1 ];
                    break;
            }

            $printer_data = $print_model->getPrinterList($printer_condition)[ 'data' ] ?? [];
            $res_data = [];

            foreach ($printer_data as $k => $v) {
                //此处应该根据打印机不同分别设置返回不同的数据。当前为易联云
                $array = [];
                $array[ 'printer_info' ] = $v;
                $print_template_model = new PrinterTemplate();

                $template_id = 0;
                $print_num = 1;
                switch ( $stage ) {
                    case 'order_pay':
                        //支付
                        $template_id = $v[ 'order_pay_template_id' ];
                        $print_num = $v[ 'order_pay_print_num' ];
                        break;
                    case 'take_delivery':
                        //收货
                        $template_id = $v[ 'take_delivery_template_id' ];
                        $print_num = $v[ 'take_delivery_print_num' ];
                        break;
                    case 'manual':
                        //手动打印
                        $template_id = $v[ 'template_id' ];
                        $print_num = $v[ 'print_num' ];
                        break;
                }

                $print_template = $print_template_model->getPrinterTemplateInfo([ [ 'template_id', '=', $template_id ] ])[ 'data' ];

                $array[ 'printer_code' ] = $v[ 'printer_code' ];    //商户授权机器码
                $array[ 'origin_id' ] = $order_info[ 'order_no' ];        //内部订单号(32位以内)
                /**文本接口开始**/

                $content = "<MN>" . $print_num . "</MN>";

                //小票名称
                if ($print_template[ 'title' ] != '') {
                    $content .= "<center>" . $print_template[ 'title' ] . "</center>";
                    $content .= str_repeat('.', 32);
                }
                //商城名称
                if ($print_template[ 'head' ] == 1) {
                    $content .= "<FH2><FS><center>" . $site_name . "</center></FS></FH2>";
                    $content .= str_repeat('.', 32);
                }

                if (!empty($order_info[ 'pay_time' ])) {
                    $content .= "订单时间:" . date("Y-m-d H:i", $order_info[ 'pay_time' ]) . "\n";
                } else {
                    $content .= "订单时间:" . date("Y-m-d H:i", time()) . "\n";
                }
                $content .= "订单编号:" . $order_info[ 'order_no' ] . "\n";
                $content .= "支付方式:" . $order_info[ 'pay_type_name' ] . "\n";

                switch ( $order_info[ 'order_type' ] ) {
                    case 1 :
                        // 物流订单
                        break;
                    case 2 :
                        // 自提订单
//                        if ($order_info[ 'buyer_ask_delivery_time' ] == 0) {
//                            $buyer_ask_delivery_time_str = '立即自提';
//                        } elseif (strpos($order_info[ 'buyer_ask_delivery_time' ], '-') !== false) {
//                            $buyer_ask_delivery_time_str = $order_info[ 'buyer_ask_delivery_time' ];
//                        } else {
//                            $buyer_ask_delivery_time_str = date("H:i:s", $order_info[ 'buyer_ask_delivery_time' ]);
//                        }
                        $buyer_ask_delivery_time_str = $order_info[ 'buyer_ask_delivery_time' ];
                        $content .= "要求自提时间:" . $buyer_ask_delivery_time_str . "\n";
                        break;
                    case 3 :
                        // 外卖订单
//                        if ($order_info[ 'buyer_ask_delivery_time' ] == 0) {
//                            $buyer_ask_delivery_time_str = '立即送达';
//                        } elseif (strpos($order_info[ 'buyer_ask_delivery_time' ], '-') !== false) {
//                            $buyer_ask_delivery_time_str = $order_info[ 'buyer_ask_delivery_time' ];
//                        } else {
//                            $buyer_ask_delivery_time_str = date("H:i:s", $order_info[ 'buyer_ask_delivery_time' ]);
//                        }
                        $buyer_ask_delivery_time_str = $order_info[ 'buyer_ask_delivery_time' ];
                        $content .= "要求送达时间:" . $buyer_ask_delivery_time_str . "\n";
                        break;
                    case 4 :
                        // 虚拟订单
                        break;
                    case 5 :
                        // 收银订单
                        break;
                }

                $content .= str_repeat('.', 32);
                $content .= "<table>";
                $content .= "<tr>";
                if ($print_template[ 'goods_price_show' ]) {
                    $content .= "<td>商品名称</td><td></td><td>数量</td><td>金额</td>";
                } else {
                    $content .= "<td>商品名称</td><td></td><td></td><td>数量</td>";
                }
                $content .= "</tr>";
                $content .= "</table>";
                $content .= str_repeat('.', 32);
                $content .= "<table>";
                foreach ($order_info[ 'order_goods' ] as $goods) {
                    //显示售价或卖价
                    $price = $print_template[ 'goods_price_type' ] == 'price' ? $goods[ 'price' ] : $goods[ 'real_goods_money' ];
                    if ($print_template[ 'goods_price_show' ]) {
                        $content .= "<tr><td>" . $goods[ 'sku_name' ] . "</td><td></td><td>x" . $goods[ 'num' ] . "</td><td>￥" . $price . "</td></tr>";
                    } else {
                        $content .= "<tr><td>" . $goods[ 'sku_name' ] . "</td><td></td><td></td><td>x" . $goods[ 'num' ] . "</td></tr>";
                    }

                    //商品编码
                    if ($print_template[ 'goods_code_show' ] && !empty($goods[ 'sku_no' ])) {
                        $content .= "<tr><td>[" . $goods[ 'sku_no' ] . "]</td><td></td><td></td><td></td></tr>";
                    }

                }
                $content .= "</table>";
                $content .= str_repeat('.', 32);
                if ($order_info[ "goods_money" ] > 0) {
                    $content .= "商品总额：￥" . $order_info[ "goods_money" ] . "\n";
                }
                if ($order_info[ "coupon_money" ] > 0) {
                    $content .= "店铺优惠券：￥" . $order_info[ "coupon_money" ] . "\n";
                }
                if ($order_info[ "promotion_money" ] > 0) {
                    $content .= "店铺优惠：￥" . $order_info[ "promotion_money" ] . "\n";
                }
                if ($order_info[ "point_money" ] > 0) {
                    $content .= "积分抵扣：￥" . $order_info[ "point_money" ] . "\n";
                }
                if ($order_info[ "adjust_money" ] > 0) {
                    $content .= "订单调价：￥" . $order_info[ "adjust_money" ] . "\n";
                }
                if ($order_info[ 'reduction' ] > 0) {
                    $content .= "订单减免：￥" . $order_info[ "reduction" ] . "\n";
                }
                if ($order_info[ "balance_money" ] > 0) {
                    $content .= "余额抵扣：￥" . $order_info[ "balance_money" ] . "\n";
                }
                if ($order_info[ "delivery_money" ] > 0) {
                    $content .= "配送费用：￥" . $order_info[ "delivery_money" ] . "\n";
                }
                if ($order_info[ "invoice_money" ] > 0) {
                    $content .= "发票费用：￥" . $order_info[ "invoice_money" ] . "\n";
                }
                if ($order_info[ "invoice_delivery_money" ] > 0) {
                    $content .= "发票邮寄费用：￥" . $order_info[ "invoice_delivery_money" ] . "\n";
                }
                if ($order_info[ "goods_num" ] > 0) {
                    $content .= "订单共" . $order_info[ 'goods_num' ] . "件商品，总计: ￥" . $order_info[ 'order_money' ] . " \n";
                }
                $content .= str_repeat('.', 32);

                /******************** 备注信息 **************************/
                //买家留言
                if ($print_template[ 'buy_notes' ] == 1) {
                    $order_info[ "buyer_message" ] = $order_info[ "buyer_message" ] ? $order_info[ "buyer_message" ] : '无';
                    $content .= "<FH2>买家留言：" . $order_info[ "buyer_message" ] . "</FH2>\n";
                    $content .= str_repeat('.', 32);
                }
                //卖家留言
                if ($print_template[ 'seller_notes' ] == 1) {
                    $order_info[ "remark" ] = $order_info[ "remark" ] ? $order_info[ "remark" ] : '无';
                    $content .= "<FH2>卖家留言：" . $order_info[ "remark" ] . "</FH2>\n";
                    $content .= str_repeat('.', 32);
                }

                //表单
                if ($print_template[ 'form_show' ] == 1 && addon_is_exit('form')) {
                    $form_info = model('form_data')->getInfo([ [ 'site_id', '=', $site_id ], [ 'scene', '=', 'order' ], [ 'relation_id', '=', $order_id ] ]);
                    if (!empty($form_info) && !empty($form_info[ 'form_data' ])) {
                        $form_data = json_decode($form_info[ 'form_data' ], true);
                        foreach ($form_data as $item) {
                            $content .= "<FH2>" . $item['value'][ 'title' ] . "：" . $item[ "val" ] . "</FH2>\n";
                            $content .= str_repeat('.', 32);
                        }
                    }
                }

                /******************** 买家信息 **************************/
                if ($order_info[ 'member_id' ]) {
                    $member_info = model('member')->getInfo([['member_id', '=', $order_info[ 'member_id' ]]]);
                    //买家姓名
                    if ($print_template[ 'buy_name' ] == 1) {
                        if($order_info[ 'order_type' ] == 2){
                            $content .= "" . $order_info[ "name" ] ?: $member_info[ "nickname" ] . "\n";
                        }else{
                            $content .= "" . $order_info[ "name" ] . "\n";
                        }

                    }
                    //联系方式
                    if ($print_template[ 'buy_mobile' ] == 1) {
                        if($order_info[ 'order_type' ] == 2){
                            $content .= "" . $order_info[ "mobile" ] ?: $member_info[ "mobile" ] . "\n";
                        }else{
                            $content .= "" . $order_info[ "mobile" ] . "\n";
                        }

                    }
                    //地址
                    if ($print_template[ 'buy_address' ] == 1) {
                        if($order_info[ 'order_type' ] == 2){
                            $content .= "自提门店：" . $order_info[ 'delivery_store_name' ] . "\n";
                        }else{
                            $content .= "" . $order_info[ 'full_address' ] . "-" . $order_info[ 'address' ] . "\n";
                        }

                    }
                    if ($print_template[ 'buy_name' ] == 1 || $print_template[ 'buy_mobile' ] == 1 || $print_template[ 'buy_address' ] == 1) {
                        $content .= str_repeat('.', 32);
                    }
                }
                /******************** 商城信息 **************************/
                //联系方式
                if ($print_template[ 'shop_mobile' ] == 1) {
                    $content .= "" . $shop_info[ "mobile" ] . "\n";
                }

                //地址
                if ($print_template[ 'shop_address' ] == 1) {
                    $content .= "" . $shop_info[ 'province_name' ] . $shop_info[ 'city_name' ] . $shop_info[ 'district_name' ] . $shop_info[ 'address' ] . "\n";
                }

                if ($print_template[ 'shop_mobile' ] == 1 || $print_template[ 'shop_address' ] == 1) {
                    $content .= str_repeat('.', 32);
                }

                //二维码
                if ($print_template[ 'shop_qrcode' ] == 1) {
                    $content .= "<QR>" . $print_template[ 'qrcode_url' ] . "</QR>";
                    $content .= str_repeat('.', 32);
                }

                /******************** 门店信息 **************************/
                if ($order_info[ 'store_id' ] > 0) {
                    $store_info = ( new Store() )->getStoreInfo([ [ 'store_id', '=', $order_info[ 'store_id' ] ] ], 'store_name,telphone,full_address')[ 'data' ];
                    $content .= "" . $order_info[ "store_name" ] . "\n";//门店名称
                    $content .= "" . $store_info[ "telphone" ] . "\n";//门店电话
                    $content .= "" . $store_info[ "full_address" ] . "\n";//门店地址
                    $content .= str_repeat('.', 32);
                }

                //底部内容
                if (!empty($print_template[ 'bottom' ])) {
                    $content .= "<center>" . $print_template[ 'bottom' ] . "</center>";
                }
                $array[ 'content' ] = $content;

                $res_data[] = $array;
            }
            return $res_data;

        }
    }
}
<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\cashier\event;

use addon\cashier\model\Cashier;
use addon\printer\model\Printer;
use addon\printer\model\PrinterTemplate;
use app\model\system\Site;

/**
 * 打印内容
 */
class PrinterContent
{
    public function handle($params)
    {
        if ($params[ 'type' ] == 'change_shifts') {
            $print_model = new Printer();
            $printer_condition = [
                [ 'site_id', '=', $params[ 'site_id' ] ],
                [ 'store_id', '=', $params[ 'store_id' ] ],
                [ 'change_shifts_open', '=', 1 ],
            ];
            //指定打印机
            if(isset($params['printer_ids']) && $params['printer_ids'] != 'all'){
                if(empty($params['printer_ids'])) $params['printer_ids'] = '0';
                $printer_condition[] = ['printer_id', 'in', $params['printer_ids']];
            }
            $printer_data = $print_model->getPrinterList($printer_condition)[ 'data' ] ?? [];
            if (empty($printer_data)) return error(-1, '未找到可用的打印机');

            if (isset($params[ 'record_id' ]) && !empty($params[ 'record_id' ])) {
                $shifts_data = ( new Cashier() )->getChangeShiftsRecordInfo([
                    [ 'r.site_id', '=', $params[ 'site_id' ] ], [ 'r.store_id', '=', $params[ 'store_id' ] ], [ 'r.id', '=', $params[ 'record_id' ] ]
                ], 'r.*,u.username', 'r', [ [ 'user u', 'r.uid = u.uid', 'inner' ] ])[ 'data' ];
                if (empty($shifts_data)) return error(-1, '未查询到交班记录');
                $user_info = [
                    'username' => $shifts_data[ 'username' ]
                ];
                $shifts_data['sale_goods_count'] = ( new Cashier() )->getSaleGoodsCount([
                    ['o.store_id', '=', $shifts_data['store_id']],
                    ['o.pay_time', '>', $shifts_data['start_time']],
                    ['o.pay_time', '<=', $shifts_data['end_time']],
                ])['data'];
            } else {
                $shifts_data = ( new Cashier() )->getShiftsData($params[ 'site_id' ], $params[ 'store_id' ]);
                $user_info = $params[ 'userinfo' ];
            }

            $res_data = [];
            foreach ($printer_data as $k => $v) {
                //此处应该根据打印机不同分别设置返回不同的数据。当前为易联云
                $array = [];
                $array[ 'printer_info' ] = $v;

                $template_id = $v[ 'change_shifts_template_id' ];
                $print_num = $v[ 'change_shifts_print_num' ];

                $print_template_model = new PrinterTemplate();
                $print_template = $print_template_model->getPrinterTemplateInfo([ [ 'template_id', '=', $template_id ] ])[ 'data' ];
                if (empty($print_template)) continue;

                $array[ 'printer_code' ] = $v[ 'printer_code' ];    //商户授权机器码
                $array[ 'origin_id' ] = time();
                $array[ 'content' ] = $this->handleChangeShiftsPrintContent($print_num, $print_template, $user_info, $shifts_data);

                $res_data[] = $array;
            }
            return $res_data;
        }
    }

    /**
     * 获取收银交班打印内容
     * @param $print_num
     * @param $print_template
     * @param $user_info
     * @param $shifts_data
     * @return string
     */
    private function handleChangeShiftsPrintContent($print_num, $print_template, $user_info, $shifts_data)
    {
        $content = "<MN>" . $print_num . "</MN>";
        //小票名称
        if ($print_template[ 'title' ] != '') {
            $content .= "<center>" . $print_template[ 'title' ] . "</center>";
            $content .= str_repeat('.', 32);
        }
        //商城名称
        if ($print_template[ 'head' ] == 1) {
            $site_name = ( new Site() )->getSiteInfo([ [ 'site_id', '=', request()->siteid() ] ], 'site_name')[ 'data' ][ 'site_name' ] ?? '';
            $content .= "<FH2><FS><center>" . $site_name . "</center></FS></FH2>";
            $content .= str_repeat('.', 32);
        }
        $content .= "交班员工:" . $user_info[ 'username' ] . "\n";
        $content .= "上班时间:" . ( $shifts_data[ 'start_time' ] ? date("Y-m-d H:i:s", $shifts_data[ 'start_time' ]) : '初始化' ) . "\n";
        $content .= "交班时间:" . date("Y-m-d H:i:s", $shifts_data[ 'end_time' ]) . "\n";
        $content .= str_repeat('.', 32);

        $content .= "<FH2><FS>总销售</FS></FH2>\n";
        $content .= "开单销售：￥" . $shifts_data[ "billing_money" ] . "\n";
        $content .= "售卡销售：￥" . $shifts_data[ "buycard_money" ] . "\n";
        $content .= str_repeat('.', 32);

        $content .= "<FH2><FS>会员充值</FS></FH2>\n";
        $content .= "会员充值：￥" . $shifts_data[ "recharge_money" ] . "\n";
        $content .= str_repeat('.', 32);

        $content .= "<FH2><FS>应收金额</FS></FH2>\n";
        $content .= "开单销售：￥" . $shifts_data[ "billing_money" ] . "\n";
        $content .= "售卡销售：￥" . $shifts_data[ "buycard_money" ] . "\n";
        $content .= "会员充值：￥" . $shifts_data[ "recharge_money" ] . "\n";
        $content .= "订单退款：￥" . $shifts_data[ "refund_money" ] . "\n";
        $content .= str_repeat('.', 32);

        $pay_arr = [];
        if ($shifts_data[ "cash" ] > 0) $pay_arr[] = "现金收款：￥" . $shifts_data[ "cash" ] . "\n";
        if ($shifts_data[ "wechatpay" ] > 0) $pay_arr[] = "微信收款：￥" . $shifts_data[ "wechatpay" ] . "\n";
        if ($shifts_data[ "alipay" ] > 0) $pay_arr[] = "支付宝收款：￥" . $shifts_data[ "alipay" ] . "\n";
        if ($shifts_data[ "own_wechatpay" ] > 0) $pay_arr[] = "个人微信收款：￥" . $shifts_data[ "own_wechatpay" ] . "\n";
        if ($shifts_data[ "own_alipay" ] > 0) $pay_arr[] = "个人支付宝收款：￥" . $shifts_data[ "own_alipay" ] . "\n";
        if ($shifts_data[ "own_pos" ] > 0) $pay_arr[] = "个人POS收款：￥" . $shifts_data[ "own_pos" ] . "\n";
        if(!empty($pay_arr)){
            $content .= "<FH2><FS>支付统计</FS></FH2>\n";
            $content .= join('', $pay_arr);
        }

        if($shifts_data['sale_goods_count']['num'] > 0){
            $content .= "<FH2><FS>商品销售</FS></FH2>\n";
            $content .= "总计：".$shifts_data['sale_goods_count']['class_num']."种".$shifts_data['sale_goods_count']['num']."件\n";
            $content .= "线上：".$shifts_data['sale_goods_count']['online_class_num']."种".$shifts_data['sale_goods_count']['online_num']."件\n";
            $content .= "线下：".$shifts_data['sale_goods_count']['offline_class_num']."种".$shifts_data['sale_goods_count']['offline_num']."件\n";
        }

        return $content;
    }
}
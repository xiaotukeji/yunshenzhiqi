<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\printer\shop\controller;

use addon\printer\model\PrinterOrder;
use app\model\store\Store;
use addon\printer\model\PrinterTemplate;
use addon\printer\model\Printer as PrinterModel;
use app\model\order\OrderCommon as OrderCommonModel;

class Printer extends BaseController
{

    /*
     *  小票打印列表
     */
    public function lists()
    {
        $model = new PrinterModel();

        if (request()->isJson()) {
            $condition[] = [ 'site_id', '=', $this->site_id ];
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $list = $model->getPrinterPageList($condition, $page, $page_size, 'printer_id desc');
            return $list;
        }
        return $this->fetch("printer/lists");
    }

    /**
     * 添加小票打印
     */
    public function add()
    {
        $model = new PrinterModel();
        if (request()->isJson()) {
            $data = [
                'site_id' => $this->site_id,
                'printer_name' => input('printer_name', ''),
                'brand' => input('brand', ''),
                'printer_code' => input('printer_code', ''),
                'printer_key' => input('printer_key', ''),
                'open_id' => input('open_id', ''),
                'apikey' => input('apikey', ''),
                'store_id' => input('store_id', ''),
                'printer_type' => input('printer_type', ''),

                //订单
                'order_pay_open' => input('order_pay_open', 0),
                'order_pay_template_id' => input('order_pay_template_id', 0),
                'order_pay_print_num' => input('order_pay_print_num', 1),
                'order_pay_order_type' => input('order_pay_order_type', ''),

                'take_delivery_open' => input('take_delivery_open', 0),
                'take_delivery_template_id' => input('take_delivery_template_id', 0),
                'take_delivery_print_num' => input('take_delivery_print_num', 1),
                'take_delivery_order_type' => input('take_delivery_order_type', ''),

                'manual_open' => input('manual_open', 0),
                'template_id' => input('template_id', 0),
                'print_num' => input('print_num', 1),

                //充值
                'recharge_open' => input('recharge_open', 0),
                'recharge_template_id' => input('recharge_template_id', 0),
                'recharge_print_num' => input('recharge_print_num', 1),

                'change_shifts_open' => input('change_shifts_open', 0),
                'change_shifts_template_id' => input('change_shifts_template_id', 0),
                'change_shifts_print_num' => input('change_shifts_print_num', 1),

                'host' => input('host', ''),
                'ip' => input('ip', ''),
                'port' => input('port', ''),
                'print_width' => input('print_width', '58mm')

            ];
            if ($data[ 'order_pay_order_type' ]) $data[ 'order_pay_order_type' ] = ',' . $data[ 'order_pay_order_type' ] . ',';
            if ($data[ 'take_delivery_order_type' ]) $data[ 'take_delivery_order_type' ] = ',' . $data[ 'take_delivery_order_type' ] . ',';
            return $model->addPrinter($data);

        } else {
            //模板列表
            $template_model = new PrinterTemplate();
            $condition = [
                [ 'site_id', '=', $this->site_id ],
            ];
            $template_list = $template_model->getPrinterTemplateList($condition, 'template_id,template_name,type', 'template_id desc');
            $this->assign('template_list', $template_list[ 'data' ]);

            //打印机品牌
            $brand = $model->getPrinterBrand();
            $this->assign('brand', $brand);

            //订单类型
            $order_common_model = new OrderCommonModel();
            $order_type_list = $order_common_model->getOrderTypeStatusList();
            unset($order_type_list[ 'all' ]);
            $this->assign("order_type_list", $order_type_list);

            $is_exit_store = addon_is_exit('store');
            if ($is_exit_store == 1) {
                $store_model = new Store();
                $store_field = 'store_id,store_name';
                $store_list = $store_model->getStoreList([ [ 'site_id', '=', $this->site_id ] ], $store_field);
                $this->assign('store_list', $store_list[ 'data' ]);
            }
            $this->assign('is_exit_store', $is_exit_store);
            return $this->fetch("printer/add");
        }
    }

    /**
     * 编辑小票打印
     */
    public function edit()
    {
        $model = new PrinterModel();
        $printer_id = input('printer_id', 0);
        if (request()->isJson()) {
            $data = [
                'printer_id' => $printer_id,

                'site_id' => $this->site_id,
                'printer_name' => input('printer_name', ''),
                'brand' => input('brand', ''),
                'printer_code' => input('printer_code', ''),
                'printer_key' => input('printer_key', ''),
                'open_id' => input('open_id', ''),
                'apikey' => input('apikey', ''),
                'store_id' => input('store_id', ''),
                'printer_type' => input('printer_type', ''),

                //订单
                'order_pay_open' => input('order_pay_open', 0),
                'order_pay_template_id' => input('order_pay_template_id', 0),
                'order_pay_print_num' => input('order_pay_print_num', 1),
                'order_pay_order_type' => input('order_pay_order_type', ''),

                'take_delivery_open' => input('take_delivery_open', 0),
                'take_delivery_template_id' => input('take_delivery_template_id', 0),
                'take_delivery_print_num' => input('take_delivery_print_num', 1),
                'take_delivery_order_type' => input('take_delivery_order_type', ''),

                'manual_open' => input('manual_open', 0),
                'template_id' => input('template_id', 0),
                'print_num' => input('print_num', 1),

                //充值
                'recharge_open' => input('recharge_open', 0),
                'recharge_template_id' => input('recharge_template_id', 0),
                'recharge_print_num' => input('recharge_print_num', 1),

                'change_shifts_open' => input('change_shifts_open', 0),
                'change_shifts_template_id' => input('change_shifts_template_id', 0),
                'change_shifts_print_num' => input('change_shifts_print_num', 1),

                'host' => input('host', ''),
                'ip' => input('ip', ''),
                'port' => input('port', ''),
                'print_width' => input('print_width', '58mm')
            ];

            if ($data[ 'order_pay_order_type' ]) $data[ 'order_pay_order_type' ] = ',' . $data[ 'order_pay_order_type' ] . ',';
            if ($data[ 'take_delivery_order_type' ]) $data[ 'take_delivery_order_type' ] = ',' . $data[ 'take_delivery_order_type' ] . ',';

            return $model->editPrinter($data);
        } else {

            $info = $model->getPrinterInfo([ [ 'printer_id', '=', $printer_id ], [ 'site_id', '=', $this->site_id ] ]);
            $info[ 'data' ][ 'take_delivery_order_type' ] = explode(',', $info[ 'data' ][ 'take_delivery_order_type' ]);
            $info[ 'data' ][ 'order_pay_order_type' ] = explode(',', $info[ 'data' ][ 'order_pay_order_type' ]);
            $this->assign('printer_info', $info[ 'data' ]);

            //模板列表
            $template_model = new PrinterTemplate();
            $condition = [
                [ 'site_id', '=', $this->site_id ],
            ];
            $template_list = $template_model->getPrinterTemplateList($condition, 'template_id,template_name,type', 'template_id desc');
            $this->assign('template_list', $template_list[ 'data' ]);

            //打印机品牌
            $brand = $model->getPrinterBrand();
            $this->assign('brand', $brand);

            //订单类型
            $order_common_model = new OrderCommonModel();
            $order_type_list = $order_common_model->getOrderTypeStatusList();
            unset($order_type_list[ 'all' ]);
            $this->assign("order_type_list", $order_type_list);

            //是否存在门店
            $is_exit_store = addon_is_exit('store');
            if ($is_exit_store == 1) {
                $store_model = new Store();
                $store_field = 'store_id,store_name';
                $store_list = $store_model->getStoreList([ [ 'site_id', '=', $this->site_id ] ], $store_field);
                $this->assign('store_list', $store_list[ 'data' ]);
            }
            $this->assign('is_exit_store', $is_exit_store);
            return $this->fetch("printer/edit");
        }
    }

    /*
     *  删除
     */
    public function delete()
    {
        $printer_id = input('printer_id', '');

        $printer_model = new PrinterModel();
        return $printer_model->deletePrinter([ [ 'printer_id', '=', $printer_id ], [ 'site_id', '=', $this->site_id ] ]);
    }

    /**
     * 测试打印
     */
    public function testPrint()
    {
        $printer_id = input('printer_id', '');
        $print_model = new PrinterOrder();
        $res = $print_model->testPrint($printer_id, $this->site_id);
        return $res;
    }

    /**
     * 刷新token
     */
    public function refreshToken()
    {
        $printer_id = input('printer_id', '');
        $print_model = new PrinterModel();
        $res = $print_model->refreshToken($printer_id, $this->site_id);
        return $res;
    }

    /**
     * 测试打印
     */
    public function test()
    {
//        $print_model = new PrinterOrder();
//        $res         = $print_model->printer([
////            'order_id' => '5630',
////            'type' => 'goodsorder',
////            'printer_type' => 'order_pay',
////            'site_id' => $this->site_id
//            'order_id' => '32',
//            'type' => 'recharge',
//            'site_id' => $this->site_id
//        ]);
//        return $res;
        $res = event('MemberRechargeOrderPayPrinter', [
            'relate_id' => 66
        ]);
    }

}
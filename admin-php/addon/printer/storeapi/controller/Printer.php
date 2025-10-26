<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\printer\storeapi\controller;

use addon\printer\model\PrinterOrder;
use app\model\order\OrderCommon as OrderCommonModel;
use addon\printer\model\PrinterTemplate;
use addon\printer\model\Printer as PrinterModel;
use app\storeapi\controller\BaseStoreApi;

class Printer extends BaseStoreApi
{
    /*
     *  小票打印列表
     */
    public function lists()
    {
        $model = new PrinterModel();
        $site_id = $this->params[ 'site_id' ] ?? 1;
        $store_id = $this->params[ 'store_id' ] ?? 1;
        $page = $this->params[ 'page' ] ?? 1;
        $page_size = $this->params[ 'page_size' ] ?? PAGE_LIST_ROWS;
        $condition[] = [ 'site_id', '=', $site_id ];
        $condition[] = [ 'store_id', '=', $store_id ];
        $list = $model->getPrinterPageList($condition, $page, $page_size, 'printer_id desc');
        return $this->response($list);
    }

    /**
     * 打印机信息
     * @return false|string
     */
    public function info()
    {
        $site_id = $this->params[ 'site_id' ] ?? 1;
        $store_id = $this->params[ 'store_id' ] ?? 1;
        $printer_id = $this->params[ 'printer_id' ] ?? 1;
        $model = new PrinterModel();
        $info = $model->getPrinterInfo([ [ 'printer_id', '=', $printer_id ], [ 'site_id', '=', $site_id ], [ 'store_id', '=', $store_id ] ]);
        $info[ 'data' ][ 'take_delivery_order_type' ] = explode(',', $info[ 'data' ][ 'take_delivery_order_type' ]);
        $info[ 'data' ][ 'order_pay_order_type' ] = explode(',', $info[ 'data' ][ 'order_pay_order_type' ]);

        $order_common_model = new OrderCommonModel();
        $order_type_list = $order_common_model->getOrderTypeStatusList();
        unset($order_type_list[ 'all' ]);
        $info[ 'data' ][ 'order_type_list' ] = $order_type_list;
        return $this->response($info);
    }

    public function getOrderType()
    {
        $order_common_model = new OrderCommonModel();
        $order_type_list = $order_common_model->getOrderTypeStatusList();
        unset($order_type_list[ 'all' ]);
        return $this->response($this->success($order_type_list));
    }

    /**
     * 打印机品牌
     * @return false|string
     */
    public function brand()
    {
        $model = new PrinterModel();
        $brand = $model->getPrinterBrand();
        return $this->response($brand);
    }

    /**
     * 打印模板
     */
    public function template()
    {
        $site_id = $this->params[ 'site_id' ] ?? 1;
        $template_model = new PrinterTemplate();
        $condition = [
            [ 'site_id', '=', $site_id ],
        ];
        $template_list = $template_model->getPrinterTemplateList($condition, 'template_id,template_name,type', 'template_id desc');
        return $this->response($template_list);
    }

    /**
     * 添加小票打印
     */
    public function add()
    {
        $model = new PrinterModel();
        $data = [
            'site_id' => $this->params[ 'site_id' ] ?? 1,
            'printer_name' => $this->params[ 'printer_name' ] ?? '',
            'brand' => $this->params[ 'brand' ] ?? '',
            'printer_code' => $this->params[ 'printer_code' ] ?? '',
            'printer_key' => $this->params[ 'printer_key' ] ?? '',
            'open_id' => $this->params[ 'open_id' ] ?? '',
            'apikey' => $this->params[ 'apikey' ] ?? '',
            'store_id' => $this->params[ 'store_id' ] ?? 1,
            'printer_type' => $this->params[ 'printer_type' ] ?? 'cloud',

            //订单
            'order_pay_open' => $this->params[ 'order_pay_open' ] ?? 0,
            'order_pay_template_id' => $this->params[ 'order_pay_template_id' ] ?? 0,
            'order_pay_print_num' => $this->params[ 'order_pay_print_num' ] ?? 1,
            'order_pay_order_type' => $this->params[ 'order_pay_order_type' ] ?? '',

            'take_delivery_open' => $this->params[ 'take_delivery_open' ] ?? 0,
            'take_delivery_template_id' => $this->params[ 'take_delivery_template_id' ] ?? 0,
            'take_delivery_print_num' => $this->params[ 'take_delivery_print_num' ] ?? 1,
            'take_delivery_order_type' => $this->params[ 'take_delivery_order_type' ] ?? '',

            'manual_open' => $this->params[ 'manual_open' ] ?? 0,
            'template_id' => $this->params[ 'template_id' ] ?? 0,
            'print_num' => $this->params[ 'print_num' ] ?? 1,

            //充值
            'recharge_open' => $this->params[ 'recharge_open' ] ?? 0,
            'recharge_template_id' => $this->params[ 'recharge_template_id' ] ?? 0,
            'recharge_print_num' => $this->params[ 'recharge_print_num' ] ?? 1,

            'change_shifts_open' => $this->params[ 'change_shifts_open' ] ?? 0,
            'change_shifts_template_id' => $this->params[ 'change_shifts_template_id' ] ?? 0,
            'change_shifts_print_num' => $this->params[ 'change_shifts_print_num' ] ?? 1,

            'host' => $this->params[ 'host' ] ?? '',
            'ip' => $this->params[ 'ip' ] ?? '',
            'port' => $this->params[ 'port' ] ?? '',
            'print_width' => $this->params[ 'print_width' ] ?? '58mm'
        ];
        if ($data[ 'order_pay_order_type' ]) $data[ 'order_pay_order_type' ] = ',' . $data[ 'order_pay_order_type' ] . ',';
        if ($data[ 'take_delivery_order_type' ]) $data[ 'take_delivery_order_type' ] = ',' . $data[ 'take_delivery_order_type' ] . ',';
        return $this->response($model->addPrinter($data));

    }

    /**
     * 编辑小票打印
     */
    public function edit()
    {
        $model = new PrinterModel();
        $data = [
            'printer_id' => $this->params[ 'printer_id' ] ?? 1,
            'site_id' => $this->params[ 'site_id' ] ?? 1,
            'printer_name' => $this->params[ 'printer_name' ] ?? '',
            'brand' => $this->params[ 'brand' ] ?? '',
            'printer_code' => $this->params[ 'printer_code' ] ?? '',
            'printer_key' => $this->params[ 'printer_key' ] ?? '',
            'open_id' => $this->params[ 'open_id' ] ?? '',
            'apikey' => $this->params[ 'apikey' ] ?? '',
            'store_id' => $this->params[ 'store_id' ] ?? 1,
            'printer_type' => $this->params[ 'printer_type' ] ?? 'cloud',

            //订单
            'order_pay_open' => $this->params[ 'order_pay_open' ] ?? 0,
            'order_pay_template_id' => $this->params[ 'order_pay_template_id' ] ?? 0,
            'order_pay_print_num' => $this->params[ 'order_pay_print_num' ] ?? 1,
            'order_pay_order_type' => $this->params[ 'order_pay_order_type' ] ?? '',

            'take_delivery_open' => $this->params[ 'take_delivery_open' ] ?? 0,
            'take_delivery_template_id' => $this->params[ 'take_delivery_template_id' ] ?? 0,
            'take_delivery_print_num' => $this->params[ 'take_delivery_print_num' ] ?? 1,
            'take_delivery_order_type' => $this->params[ 'take_delivery_order_type' ] ?? '',

            'manual_open' => $this->params[ 'manual_open' ] ?? 0,
            'template_id' => $this->params[ 'template_id' ] ?? 0,
            'print_num' => $this->params[ 'print_num' ] ?? 1,

            //充值
            'recharge_open' => $this->params[ 'recharge_open' ] ?? 0,
            'recharge_template_id' => $this->params[ 'recharge_template_id' ] ?? 0,
            'recharge_print_num' => $this->params[ 'recharge_print_num' ] ?? 1,

            'change_shifts_open' => $this->params[ 'change_shifts_open' ] ?? 0,
            'change_shifts_template_id' => $this->params[ 'change_shifts_template_id' ] ?? 0,
            'change_shifts_print_num' => $this->params[ 'change_shifts_print_num' ] ?? 1,

            'host' => $this->params[ 'host' ] ?? '',
            'ip' => $this->params[ 'ip' ] ?? '',
            'port' => $this->params[ 'port' ] ?? '',
            'print_width' => $this->params[ 'print_width' ] ?? '58mm'
        ];
        if ($data[ 'order_pay_order_type' ]) {
            $data[ 'order_pay_order_type' ] = ',' . $data[ 'order_pay_order_type' ] . ',';
            $data[ 'order_pay_order_type' ] = str_replace(',,', ',', $data[ 'order_pay_order_type' ]);
        }
        if ($data[ 'take_delivery_order_type' ]) {
            $data[ 'take_delivery_order_type' ] = ',' . $data[ 'take_delivery_order_type' ] . ',';
            $data[ 'take_delivery_order_type' ] = str_replace(',,', ',', $data[ 'take_delivery_order_type' ]);
        }
        return $this->response($model->editPrinter($data));
    }

    /*
     *  删除
     */
    public function deletePrinter()
    {
        $printer_id = $this->params[ 'printer_id' ] ?? 0;
        $store_id = $this->params[ 'store_id' ] ?? 0;

        $printer_model = new PrinterModel();
        $res = $printer_model->deletePrinter([ [ 'printer_id', '=', $printer_id ], [ 'store_id', '=', $store_id ] ]);
        return $this->response($res);
    }

    /**
     * 测试打印
     */
    public function testPrint()
    {
        $printer_id = $this->params[ 'printer_id' ] ?? 0;
        $site_id = $this->params[ 'site_id' ] ?? 1;
        $print_model = new PrinterOrder();
        $res = $print_model->testPrint($printer_id, $site_id);
        return $this->response($res);
    }

    /**
     * 刷新token
     */
    public function refreshToken()
    {
        $printer_id = $this->params[ 'printer_id' ] ?? 0;
        $site_id = $this->params[ 'site_id' ] ?? 1;
        $print_model = new PrinterModel();
        $res = $print_model->refreshToken($printer_id, $site_id);
        return $this->response($res);
    }

}
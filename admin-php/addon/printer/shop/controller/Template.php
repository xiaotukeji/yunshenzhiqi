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

use app\shop\controller\BaseShop;
use addon\printer\model\PrinterTemplate;

class Template extends BaseShop
{

    /*
     *  模板管理列表
     */
    public function lists()
    {
        $model = new PrinterTemplate();

        if (request()->isJson()) {
            $condition[] = [ 'site_id', '=', $this->site_id ];
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $list = $model->getPrinterTemplatePageList($condition, $page, $page_size, 'template_id desc');
            return $list;
        }
        return $this->fetch("template/lists");
    }

    /**
     * 添加模板管理
     */
    public function add()
    {
        $model = new PrinterTemplate();

        $type = input('type', 'goodsorder');
        if (request()->isJson()) {
            $data = [
                'site_id' => $this->site_id,
                'site_name' => $this->shop_info[ 'site_name' ],
                'template_type' => input('template_type', ''),
                'template_name' => input('template_name', ''),

                'title' => input('title', ''),
                'head' => input('head', ''),
                'buy_notes' => input('buy_notes', ''),
                'seller_notes' => input('seller_notes', ''),
                'buy_name' => input('buy_name', ''),
                'buy_mobile' => input('buy_mobile', ''),
                'buy_address' => input('buy_address', ''),
                'shop_mobile' => input('shop_mobile', ''),
                'shop_address' => input('shop_address', ''),
                'shop_qrcode' => input('shop_qrcode', ''),
                'qrcode_url' => input('qrcode_url', ''),
                'bottom' => input('bottom', ''),

                'type' => input('type', ''),
                'type_name' => input('type_name', ''),
                'goods_price_show' => input('goods_price_show', 0),
                'goods_code_show' => input('goods_code_show', 0),
                'form_show' => input('form_show', 0),
                'goods_price_type' => input('goods_price_type', ''),
            ];

            return $model->addPrinterTemplate($data);
        } else {
            $this->assign('template_type', $model->getTemplateType());
            $this->assign('type', $type);
            return event('PrinterTemplate', [ 'type' => $type, 'action' => 'add' ], true);
        }
    }

    /**
     * 编辑模板管理
     */
    public function edit()
    {
        $model = new PrinterTemplate();
        $template_id = input('template_id', 0);
        if (request()->isJson()) {
            $data = [
                'template_id' => $template_id,
                'site_id' => $this->site_id,
                'template_type' => input('template_type', ''),
                'template_name' => input('template_name', ''),

                'title' => input('title', ''),
                'head' => input('head', ''),
                'buy_notes' => input('buy_notes', ''),
                'seller_notes' => input('seller_notes', ''),
                'buy_name' => input('buy_name', ''),
                'buy_mobile' => input('buy_mobile', ''),
                'buy_address' => input('buy_address', ''),
                'shop_mobile' => input('shop_mobile', ''),
                'shop_address' => input('shop_address', ''),
                'shop_qrcode' => input('shop_qrcode', ''),
                'qrcode_url' => input('qrcode_url', ''),
                'bottom' => input('bottom', ''),

                'goods_price_show' => input('goods_price_show', 0),
                'goods_code_show' => input('goods_code_show', 0),
                'form_show' => input('form_show', 0),
                'goods_price_type' => input('goods_price_type', ''),

            ];
            return $model->editPrinterTemplate($data);

        } else {
            $info = $model->getPrinterTemplateInfo([ [ 'template_id', '=', $template_id ], [ 'site_id', '=', $this->site_id ] ]);
            $this->assign('info', $info[ 'data' ]);

            return event('PrinterTemplate', [ 'type' => $info[ 'data' ][ 'type' ], 'action' => 'edit' ], true);
        }
    }

    /*
     *  删除
     */
    public function delete()
    {
        $template_id = input('template_id', '');

        $printer_model = new PrinterTemplate();
        return $printer_model->deletePrinterTemplate([ [ 'template_id', '=', $template_id ], [ 'site_id', '=', $this->site_id ] ]);
    }

}
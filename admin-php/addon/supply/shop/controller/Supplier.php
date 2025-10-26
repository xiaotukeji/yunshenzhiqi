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

namespace addon\supply\shop\controller;

use addon\supply\model\Supplier as SupplierModel;
use app\shop\controller\BaseShop;

/**
 * 供应商管理
 */
class Supplier extends BaseShop
{
    /**
     * 供应商列表
     */
    public function lists()
    {
        if (request()->isJson()) {
            $supplier_model = new SupplierModel();
            $page_index = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $search_text = input('search_text', '');
            $condition = [];
            if (!empty($search_text)) {
                $condition[] = [ 'title|desc|keywords|supplier_phone', 'LIKE', '%' . $search_text . '%' ];
            }
            $res = $supplier_model->getSupplierPageList($condition, $page_index, $page_size, 'supplier_id DESC');
            return $res;
        }
        return $this->fetch('supplier/lists');
    }

    /**
     * 添加供应商
     */
    public function add()
    {
        if (request()->isJson()) {
            $supplier_model = new SupplierModel();
            $data = [
                'supplier_site_id' => $this->site_id,
                'title' => input('title', ''),
                'logo' => input('logo', ''),
                'supplier_phone' => input('supplier_phone', ''),
                'desc' => input('desc', ''),
                'keywords' => input('keywords', ''),
                'supplier_address' => input('supplier_address', ''),
                'supplier_email' => input('supplier_email', ''),
                'supplier_qq' => input('supplier_qq', ''),
                'supplier_weixin' => input('supplier_weixin', ''),
                'settlement_bank_account_name' => input('settlement_bank_account_name', ''),
                'settlement_bank_account_number' => input('settlement_bank_account_number', ''),
                'settlement_bank_name' => input('settlement_bank_name', ''),
                'settlement_bank_address' => input('settlement_bank_address', '')
            ];
            $res = $supplier_model->addSupplier($data);
            return $res;
        } else {

            return $this->fetch('supplier/edit');
        }
    }

    /**
     * 修改供应商
     */
    public function edit()
    {
        $supplier_id = input('supplier_id', 0);
        $condition = [
            [ 'supplier_id', '=', $supplier_id ],
            [ 'supplier_site_id', '=', $this->site_id ]
        ];
        $supplier_model = new SupplierModel();
        if (request()->isJson()) {
            $data = [
                'supplier_id' => $supplier_id,
                'supplier_site_id' => $this->site_id,
                'title' => input('title', ''),
                'logo' => input('logo', ''),
                'supplier_phone' => input('supplier_phone', ''),
                'desc' => input('desc', ''),
                'keywords' => input('keywords', ''),
                'supplier_address' => input('supplier_address', ''),
                'supplier_email' => input('supplier_email', ''),
                'supplier_qq' => input('supplier_qq', ''),
                'supplier_weixin' => input('supplier_weixin', ''),
                'settlement_bank_account_name' => input('settlement_bank_account_name', ''),
                'settlement_bank_account_number' => input('settlement_bank_account_number', ''),
                'settlement_bank_name' => input('settlement_bank_name', ''),
                'settlement_bank_address' => input('settlement_bank_address', '')
            ];
            $res = $supplier_model->editSupplier($condition, $data);
            return $res;
        } else {


            $supplier_info = $supplier_model->getSupplierInfo($condition)[ 'data' ];
            $this->assign('info', $supplier_info);

            return $this->fetch('supplier/edit');
        }
    }

    /**
     * 删除供应商
     * @return array
     */
    public function delete()
    {
        if (request()->isJson()) {
            $supplier_id = input('supplier_id', 0);
            if (empty($supplier_id)) {
                return error(-1, '参数错误！');
            }
            $supplier_model = new SupplierModel();
            $res = $supplier_model->deleteSupplier($supplier_id);
            return $res;
        }
    }

}

<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 */

namespace addon\supply\shopapi\controller;

use addon\supply\model\Supplier as SupplierModel;
use app\shopapi\controller\BaseApi;

class Supply extends BaseApi
{
    public function __construct()
    {
        //执行父类构造函数
        parent::__construct();

        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) {
            echo $this->response($token);
            exit;
        }
    }

    /**
     * 供应商列表
     */
    public function lists()
    {
        $supplier_model = new SupplierModel();
        $list = $supplier_model->getSupplyList([ [ 'supplier_site_id', '=', $this->site_id ] ], 'supplier_id,title', 'supplier_id desc');
        return $this->response($list);
    }

}
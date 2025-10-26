<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\freeshipping\shop\controller;

use app\shop\controller\BaseShop;
use addon\freeshipping\model\Freeshipping as FreeshippingModel;

class Freeshipping extends BaseShop
{

    public function lists()
    {
        $model = new FreeshippingModel();

        $condition[] = [ 'site_id', '=', $this->site_id ];
        if (request()->isJson()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $list = $model->getFreeshippingPageList($condition, $page, $page_size, 'price asc');
            return $list;
        } else {
            return $this->fetch('freeshipping/lists');
        }

    }

    /**
     * 添加活动
     */
    public function add()
    {
        if (request()->isJson()) {

            $price = input('price', '');
            $json = input('json', '');
            $surplus_area_ids = input('surplus_area_ids', '');

            $json_data = json_decode($json, true);
            $data = $json_data[ '1' ];
            $data[ 'price' ] = $price;
            $data[ 'site_id' ] = $this->site_id;
            $data[ 'surplus_area_ids' ] = $surplus_area_ids;
            $model = new FreeshippingModel();
            $result = $model->addFreeshipping($data);
            return $result;

        } else {

            // 地区等级设置 将来从配置中查询数据
            $area_level = 3;
            $this->assign('area_level', $area_level);//地址级别
            return $this->fetch('freeshipping/add');
        }
    }

    /**
     * 编辑活动
     */
    public function edit()
    {
        $model = new FreeshippingModel();
        $freeshipping_id = input('freeshipping_id', 0);
        if (request()->isJson()) {

            $price = input('price', '');
            $json = input('json', '');
            $surplus_area_ids = input('surplus_area_ids', '');

            $json_data = json_decode($json, true);
            $data = $json_data[ '1' ];
            $data[ 'price' ] = $price;
            $data[ 'site_id' ] = $this->site_id;
            $data[ 'surplus_area_ids' ] = $surplus_area_ids;
            $data[ 'freeshipping_id' ] = $freeshipping_id;

            $result = $model->editFreeshipping($data);
            return $result;

        } else {
            $this->assign('freeshipping_id', $freeshipping_id);
            // 地区等级设置 将来从配置中查询数据
            $area_level = 3;
            $this->assign('area_level', $area_level);//地址级别

            $info = $model->getFreeshippingInfo([ [ 'freeshipping_id', '=', $freeshipping_id ], [ 'site_id', '=', $this->site_id ] ])[ 'data' ];
            $this->assign('info', $info);
            return $this->fetch('freeshipping/edit');
        }
    }

    /*
     *  删除
     */
    public function delete()
    {
        $freeshipping_id = input('freeshipping_id', '');
        $site_id = $this->site_id;

        $model = new FreeshippingModel();
        return $model->deleteFreeshipping($freeshipping_id, $site_id);
    }

}
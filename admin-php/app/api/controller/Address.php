<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2015-2025 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 * @author : niuteam
 */

namespace app\api\controller;

use app\model\system\Address as AddressModel;

/**
 * 地址管理
 * @author Administrator
 *
 */
class Address extends BaseApi
{

    /**
     * 基础信息
     */
    public function info()
    {
        $id      = $this->params['id'];
        $address = new AddressModel();
        $info    = $address->getAreaInfo($id);
        return $this->response($info);
    }

    /**
     * 列表信息
     */
    public function lists()
    {
        $pid     = $this->params['pid'] ?? 0;
        $address = new AddressModel();
        $list    = $address->getAreas($pid);
        return $this->response($list);
    }

    /**
     * 树状结构信息
     */
    public function tree()
    {
        $id      = $this->params['id'];
        $address = new AddressModel();
        $tree    = $address->getAreas($id);
        return $this->response($tree);
    }

    /**
     * 解析地址
     */
    public function analysesAddress()
    {
        $address = input('address', '');
        $address_model = new AddressModel();
        return $this->response($address_model->analysesAddress($address));
    }
}
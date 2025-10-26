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

namespace addon\stock\storeapi\controller;

use addon\stock\model\Store as StoreModel;
use app\storeapi\controller\BaseStoreApi;


/**
 * 库存调拨
 */
class Store extends BaseStoreApi
{

    /**
     * 获取门店
     * @return false|string
     */
    public function lists()
    {
        $store_list = ( new StoreModel )->getStoreList($this->site_id);
        return $this->response($store_list);
    }

}
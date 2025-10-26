<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 上海牛之云网络科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\stock\model;


use app\model\store\Store as StoreCommonModel;
use app\model\BaseModel;

/**
 * 库存model
 *
 * @author Administrator
 *
 */
class Store extends BaseModel
{
    /**
     * 库存用门店列表
     * @param $site_id
     * @return mixed
     */
    public function getStoreList($site_id)
    {
        $condition = [
            [ 'site_id', '=', $site_id ],
            [ 'stock_type', '=', 'store' ]
        ];
        $store_model = new StoreCommonModel();
        return $store_model->getStoreList($condition);
    }
}

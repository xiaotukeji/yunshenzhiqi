<?php

/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\store\model;

use app\model\BaseModel;

class StoreStockImport extends BaseModel
{

    /**
     * 详情
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getStoreStockImportInfo($condition = [], $field = '*')
    {
        $info = model('store_stock_import')->getInfo($condition, $field);
        if (!empty($info)) {
            if (isset($info[ 'stock' ])) {
                $info[ 'stock' ] = numberFormat($info[ 'stock' ]);
            }
            if (isset($info[ 'error_num' ])) {
                $info[ 'error_num' ] = numberFormat($info[ 'error_num' ]);
            }
        }
        return $this->success($info);
    }

    /**
     * 删除
     * @param $id
     * @param $store_id
     * @return array
     */
    public function deleteStoreStockImport($id, $store_id)
    {
        model('store_stock_import')->startTrans();
        try {
            model('store_stock_import')->delete([ [ 'id', '=', $id ], [ 'store_id', '=', $store_id ] ]);
            model('store_stock_import_log')->delete([ [ 'file_id', '=', $id ] ]);

            model('store_stock_import')->commit();
            return $this->success();
        } catch (\Exception $e) {

            model('store_stock_import')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 获取导入文件列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getStoreStockImportPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'id desc', $field = '*')
    {
        $list = model('store_stock_import')->pageList($condition, $field, $order, $page, $page_size);
        foreach ($list as $k => $v) {
            if (isset($v[ 'stock' ])) {
                $list[ $k ][ 'stock' ] = numberFormat($list[ $k ][ 'stock' ]);
            }
            if (isset($v[ 'error_num' ])) {
                $list[ $k ][ 'error_num' ] = numberFormat($list[ $k ][ 'error_num' ]);
            }
        }
        return $this->success($list);
    }

    /**
     * 获取导入文件列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getStoreStockImportPageLogList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'id desc', $field = '*')
    {
        $list = model('store_stock_import_log')->pageList($condition, $field, $order, $page, $page_size);
        foreach ($list as $k => $v) {
            if (isset($v[ 'stock' ])) {
                $list[ $k ][ 'stock' ] = numberFormat($list[ $k ][ 'stock' ]);
            }
        }
        return $this->success($list);
    }

}

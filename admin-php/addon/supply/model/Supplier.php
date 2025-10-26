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

namespace addon\supply\model;

use app\model\BaseModel;
use think\facade\Cache;

/**
 * 供应商表
 */
class Supplier extends BaseModel
{
    /**
     * 添加供应商
     * @param $data
     * @return array
     */
    public function addSupplier($data)
    {
        $count = model('supplier')->getCount([ [ 'title', '=', $data[ 'title' ] ] ]);
        if ($count > 0) {
            return $this->error('', '该供应商已经存在！');
        }
        $data[ 'create_time' ] = time();
        model('supplier')->add($data);
        return $this->success();
    }

    /**
     * 修改供应商
     * @param $condition
     * @param $data
     * @return array|mixed|string
     */
    public function editSupplier($condition, $data)
    {
        $res = model('supplier')->update($data, $condition);
        return $this->success($res);
    }

    /**
     * 删除供货商
     * @param $supplier_id
     * @return array
     */
    public function deleteSupplier($supplier_id)
    {
        $res = model('supplier')->delete([ [ 'supplier_id', 'in', $supplier_id ] ]);
        return $this->success($res);
    }

    /**
     * 获取供应商分页列表
     * @param array $where
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getSupplierPageList($where = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*')
    {

        $list = model("supplier")->pageList($where, $field, $order, $page, $page_size);
        return $this->success($list);
    }

    /**
     * 获取供应商信息
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getSupplierInfo($condition = [], $field = '*')
    {
        $info = model("supplier")->getInfo($condition, $field);
        return $this->success($info);
    }

    /**
     * 获取列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param null $limit
     * @return array
     */
    public function getSupplyList($condition = [], $field = '*', $order = '', $limit = null)
    {
        $list = model('supplier')->getList($condition, $field, $order, '', '', '', $limit);
        return $this->success($list);
    }

}

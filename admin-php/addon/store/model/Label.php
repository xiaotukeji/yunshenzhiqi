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

use think\facade\Db;
use think\facade\Cache;
use app\model\BaseModel;

/**
 * 门店标签
 */
class Label extends BaseModel
{

    /**
     * 添加门店标签
     *
     * @param array $data
     */
    public function addStoreLabel($data)
    {
        $count = model('store_label')->getCount([ [ 'label_name', '=', $data[ 'label_name' ] ], [ 'site_id', '=', $data[ 'site_id' ] ] ]);
        if ($count) return $this->error('', '该标签名称已存在');

        $res = model('store_label')->add($data);
        Cache::tag("store_label")->clear();
        return $this->success($res);
    }

    /**
     * 修改门店标签
     *
     * @param array $data
     * @param array $condition
     */
    public function editStoreLabel($data, $condition)
    {
        $check_condition = array_column($condition, 2, 0);
        $label_id = $check_condition['label_id'] ?? 0;
        $site_id = $check_condition['site_id'] ?? 0;
        if (empty($label_id)) return $this->error('', 'label_id不能为空');
        if (empty($site_id)) return $this->error('', 'site_id不能为空');

        $count = model('store_label')->getCount([ [ 'label_id', '<>', $label_id ], [ 'label_name', '=', $data[ 'label_name' ] ], [ 'site_id', '=', $site_id ] ]);
        if ($count) return $this->error('', '该标签名称已存在');

        $old_label_name = model('store_label')->getValue($condition, 'label_name');
        $res = model('store_label')->update($data, $condition);
        model('store')->update([ 'label_name' => Db::raw("REPLACE(label_name, ',{$old_label_name},', ',{$data['label_name']},')") ], [ [ 'site_id', '=', $site_id ], [ 'label_id', 'like', "%,$label_id,%" ] ]);

        Cache::tag("store_label")->clear();
        return $this->success($res);
    }

    /**
     * 修改排序
     * @param $sort
     * @param $label_id
     * @param $site_id
     * @return array
     */
    public function modifySort($sort, $label_id, $site_id)
    {
        $site_id = $site_id ?? '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }

        $res = model('store_label')->update([ 'sort' => $sort ], [ [ 'label_id', '=', $label_id ], [ 'site_id', '=', $site_id ] ]);

        Cache::tag("store_label")->clear();
        return $this->success($res);
    }

    /**
     * 删除门店标签
     * @param $condition
     * @return array
     */
    public function deleteStoreLabel($condition)
    {
        $check_condition = array_column($condition, 2, 0);
        $label_id = $check_condition['label_id'] ?? 0;
        $site_id = $check_condition['site_id'] ?? 0;
        if (empty($label_id)) return $this->error('', 'label_id不能为空');
        if (empty($site_id)) return $this->error('', 'site_id不能为空');

        $res = model('store_label')->delete($condition);

        $old_label_name = model('store_label')->getValue($condition, 'label_name');
        model('store')->update([ 'label_name' => Db::raw("REPLACE(label_name, ',{$old_label_name},', '')") ], [ [ 'site_id', '=', $site_id ], [ 'label_id', 'like', "%,$label_id,%" ] ]);

        Cache::tag("store_label")->clear();
        return $this->success($res);
    }

    /**
     * 获取门店标签信息
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getStoreLabelInfo($condition = [], $field = '*')
    {
        $info = model('store_label')->getInfo($condition, $field);
        return $this->success($info);
    }

    /**
     * 获取门店标签列表
     *
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     */
    public function getStoreLabelList($condition = [], $field = '*', $order = 'sort asc,label_id desc', $limit = null)
    {
        $list = model('store_label')->getList($condition, $field, $order, '', '', '', $limit);
        return $this->success($list);
    }

    /**
     * 获取门店标签分页列表
     *
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     */
    public function getStoreLabelPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'sort asc,label_id desc', $field = '*')
    {
        $list = model('store_label')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }
}
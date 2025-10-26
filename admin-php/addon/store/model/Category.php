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

use think\facade\Cache;
use app\model\BaseModel;
use app\model\system\Config;

/**
 * 门店分类
 */
class Category extends BaseModel
{
    /**
     * 门店分类设置
     * @param $data
     * @param $site_id
     * @return array
     */
    public function setCategoryConfig($data, $site_id)
    {
        return ( new Config() )->setConfig($data, '门店分类设置', 1, [ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'STORE_CATEGORY' ] ]);
    }

    /**
     * 获取门店分类设置
     * @param $site_id
     * @return array
     */
    public function getCategoryConfig($site_id)
    {
        $data = ( new Config() )->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'STORE_CATEGORY' ] ]);
        if (empty($data[ 'data' ][ 'value' ])) {
            $data[ 'data' ][ 'value' ] = [
                'status' => 0
            ];
        }
        return $data;
    }

    /**
     * 添加门店分类
     *
     * @param array $data
     */
    public function addStoreCategory($data)
    {
        $res = model('store_category')->add($data);
        Cache::tag('store_category')->clear();
        return $this->success($res);
    }

    /**
     * 修改门店分类
     *
     * @param array $data
     * @param array $condition
     */
    public function editStoreCategory($data, $condition)
    {
        $check_condition = array_column($condition, 2, 0);
        $category_id = $check_condition['category_id'] ?? 0;
        $site_id = $check_condition['site_id'] ?? 0;
        if (empty($category_id)) return $this->error('', 'category_id不能为空');
        if (empty($site_id)) return $this->error('', 'site_id不能为空');

        $res = model('store_category')->update($data, $condition);
        if (isset($data[ 'category_name' ]) && !empty($data[ 'category_name' ])) model('store')->update([ 'category_name' => $data[ 'category_name' ] ], [ [ 'category_id', '=', $category_id ], [ 'site_id', '=', $site_id ] ]);
        Cache::tag('store_category')->clear();
        return $this->success($res);
    }

    /**
     * 删除门店分类
     * @param array $condition
     */
    public function deleteStoreCategory($condition)
    {
        $check_condition = array_column($condition, 2, 0);
        $category_id = isset($check_condition[ 'category_id' ]) ? array_filter(explode(',', $check_condition[ 'category_id' ])) : [];
        $site_id = $check_condition['site_id'] ?? 0;
        if (empty($category_id)) return $this->error('', 'category_id不能为空');
        if (empty($site_id)) return $this->error('', 'site_id不能为空');

        $res = model('store_category')->delete($condition);
        model('store')->update([ 'category_name' => '', 'category_id' => '' ], [ [ 'category_id', 'in', $category_id ], [ 'site_id', '=', $site_id ] ]);

        Cache::tag('store_category')->clear();
        return $this->success($res);
    }

    /**
     * 获取门店分类信息
     *
     * @param array $condition
     * @param string $field
     */
    public function getStoreCategoryInfo($condition = [], $field = '*')
    {
        $info = model('store_category')->getInfo($condition, $field);
        return $this->success($info);
    }

    /**
     * 获取门店分类列表
     *
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     */
    public function getStoreCategoryList($condition = [], $field = '*', $order = 'sort asc, category_id asc', $limit = null)
    {
        $list = model('store_category')->getList($condition, $field, $order, '', '', '', $limit);
        return $this->success($list);
    }

    /**
     * 获取门店分类分页列表
     *
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     */
    public function getStoreCategoryPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'category_id desc', $field = '*')
    {
        $list = model('store_category')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }
}
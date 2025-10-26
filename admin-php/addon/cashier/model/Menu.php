<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\cashier\model;

use app\model\BaseModel;
use think\facade\Cache;

/**
 * @author Administrator
 */
class Menu extends BaseModel
{
    /**
     * 获取权限列表
     * @param $condition
     * @param $field
     * @return array
     */
    public function getMenuList($condition, $field)
    {
        $data = model('cashier_auth')->getList($condition, $field);
        return $this->success($data);
    }

    /**
     * 获取权限信息
     * @param $condition
     * @param $field
     * @return array
     */
    public function getMenuValue($condition, $field)
    {
        $key = json_encode([$condition, $field]);
        if (Cache::has('getMenuValue' . $key)) {
            return $this->success(Cache::get('getMenuValue' . $key));
        }
        $value = model('cashier_auth')->getValue($condition, $field);
        Cache::tag('cashier_menu')->set('getMenuValue' . $key, $value);
        return $this->success($value);
    }
}
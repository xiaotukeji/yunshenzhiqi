<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\system;

use think\facade\Cache;
use app\model\BaseModel;

/**
 * 用户组
 * @author Administrator
 *
 */
class Group extends BaseModel
{
    public $cache_model = "cache_model_group";
    /*****************************************用户组管理开始******************************************************************************/

    /**
     * 添加用户组
     * @param $data
     * @return array
     */
    public function addGroup($data)
    {
        $site_id = $data['site_id'] ?? '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }
        $app_module = $data['app_module'] ?? '';
        if ($app_module === '') {
            return $this->error('', 'REQUEST_APP_MODULE');
        }

        //创建者数据
        if(isset($data['create_uid'])){
            $user_info = model('user')->getInfo([['uid', '=', $data['create_uid']]], 'uid,username,create_user_data');
            $create_user_data = json_decode($user_info['create_user_data'], true);
            $create_user_data[] = ['id' => (string)$user_info['uid'], 'name' => $user_info['username']];
            $data['create_user_data'] = json_encode($create_user_data, JSON_UNESCAPED_UNICODE);
        }

        //预留验证
        $res = model('group')->add($data);
        Cache::tag($this->cache_model)->clear();
        if ($res === false) {
            return $this->error('', 'UNKNOW_ERROR');
        }

        return $this->success($res);
    }

    /**
     * 修改用户组
     * @param $data
     * @param $condition
     * @return array
     */
    public function editGroup($data, $condition)
    {
        $check_condition = array_column($condition, 2, 0);
        $site_id = $check_condition['site_id'] ?? '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }
        $app_module = $check_condition['app_module'] ?? '';
        if ($app_module === '') {
            return $this->error('', 'REQUEST_APP_MODULE');
        }
        $condition[] = [ "is_system", "=", 0 ];//只能删除非系统用户组

        $res = model('group')->update($data, $condition);
        Cache::tag($this->cache_model)->clear();
        if ($res === false) {
            return $this->error('', 'UNKNOW_ERROR');
        }
        return $this->success($res);

    }

    /**
     * 删除用户组(不能批量)
     * @param $condition
     * @return array
     */
    public function deleteGroup($condition)
    {
        $check_condition = array_column($condition, 2, 0);
        $site_id = $check_condition['site_id'] ?? '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }
        $app_module = $check_condition['app_module'] ?? '';
        if ($app_module === '') {
            return $this->error('', 'REQUEST_APP_MODULE');
        }
        $group_id = $check_condition['group_id'] ?? 0;
        if (!is_int($group_id) && $group_id <= 0) {
            return $this->error('', 'USER_GROUP_NOT_ALL_DELETE');
        }
        $temp_count = model('user')->getCount([ [ "group_id", "=", $group_id ], [ "app_module", "=", $app_module ], [ "site_id", "=", $site_id ] ], "uid");
        if ($temp_count > 0)
            return $this->error('', 'USER_GROUP_USED');

        $condition[] = [ "is_system", "=", 0 ];//只能删除非系统用户组

        $res = model('group')->delete($condition);
        Cache::tag($this->cache_model)->clear();
        if ($res === false) {
            return $this->error('', 'UNKNOW_ERROR');
        }
        return $this->success($res);
    }

    /**
     * 修改用户组状态
     * @param $group_status
     * @param $condition
     * @return array
     */
    public function modifyGroupStatus($group_status, $condition)
    {
        $check_condition = array_column($condition, 2, 0);
        $site_id = $check_condition['site_id'] ?? '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }
        $app_module = $check_condition['app_module'] ?? '';
        if ($app_module === '') {
            return $this->error('', 'REQUEST_APP_MODULE');
        }
        $condition[ "is_system" ] = 0;//只能删除非系统用户组
        $res = model('group')->update([ 'group_status' => $group_status ], $condition);
        Cache::tag($this->cache_model)->clear();
        if ($res === false) {
            return $this->error('', 'UNKNOW_ERROR');
        }
        return $this->success($res);
    }

    /**
     * 获取门店分组列信息
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getStoreGroupColumn($condition = [], $field = '')
    {
        $res = model('group')->getColumn($condition, $field);
        return $this->success($res);
    }

    /**
     * 获取用户组详情
     * @param $condition
     * @param string $field
     * @return array
     */
    public function getGroupInfo($condition, $field = '*')
    {
        $check_condition = array_column($condition, 2, 0);
        $app_module = $check_condition['app_module'] ?? '';
        if ($app_module === '') {
            return $this->error('', 'REQUEST_APP_MODULE');
        }
        $info = model('group')->getInfo($condition, $field);
        return $this->success($info);
    }

    /**
     * 通过groupid获取相应用户组数据，应用在门店，供应商等不创建站点
     * @param $group_id
     * @param $site_id
     * @param $app_module
     * @param string $field
     * @return array
     */
    public function getGroupInfoById($group_id, $site_id, $app_module, $field = '*')
    {
        if ($app_module === '') {
            return $this->error('', 'REQUEST_APP_MODULE');
        }

        $info = model('group')->getInfo([ [ 'group_id', '=', $group_id ], [ 'app_module', '=', $app_module ] ], $field);
        return $this->success($info);
    }

    /**
     * 获取用户组列表
     * @param array $condition
     * @param bool $field
     * @param string $order
     * @param null $limit
     * @return array
     */
    public function getGroupList($condition = [], $field = true, $order = 'create_time desc, group_id desc', $limit = null)
    {
        $check_condition = array_column($condition, 2, 0);
        $site_id = $check_condition['site_id'] ?? '';
        $app_module = $check_condition['app_module'] ?? '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }
        if ($app_module === '') {
            return $this->error('', 'REQUEST_APP_MODULE');
        }

        $list = model('group')->getList($condition, $field, $order, '', '', '', $limit);
        return $this->success($list);
    }

    /**
     * 获取管理组分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getGroupPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'create_time desc, group_id desc', $field = '*')
    {
        $check_condition = array_column($condition, 2, 0);
        $site_id = $check_condition['site_id'] ?? '';
        $app_module = $check_condition['app_module'] ?? '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }
        if ($app_module === '') {
            return $this->error('', 'REQUEST_APP_MODULE');
        }

        $list = model('group')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }

    /*****************************************用户组管理结束****************************************************************************/

}
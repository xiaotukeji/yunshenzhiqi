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

/**
 * 用户组
 * @author Administrator
 *
 */
class Group extends BaseModel
{
    /**
     * 添加用户组
     * @param array $data
     * @return array:string mixed
     */
    public function addGroup($data)
    {
        //创建者数据
        if(isset($data['create_uid'])){
            $user_info = model('user')->getInfo([['uid', '=', $data['create_uid']]], 'uid,username,create_user_data');
            $create_user_data = json_decode($user_info['create_user_data'], true);
            $create_user_data[] = ['id' => (string)$user_info['uid'], 'name' => $user_info['username']];
            $data['create_user_data'] = json_encode($create_user_data, JSON_UNESCAPED_UNICODE);
        }
        $res = model('cashier_auth_group')->add($data);
        if ($res === false) {
            return $this->error('', 'UNKNOW_ERROR');
        }
        return $this->success($res);
    }

    /**
     * 修改用户组
     * @param array $data
     * @param array $condition
     * @return array:string mixed
     */
    public function editGroup($data, $condition)
    {
        $condition[] = ['keyword', '=', ''];//只能删除非系统用户组
        $res = model('cashier_auth_group')->update($data, $condition);
        if ($res === false) {
            return $this->error('', 'UNKNOW_ERROR');
        }
        return $this->success($res);
    }

    /**
     * 删除用户组(不能批量)
     * @param array $group_id
     * @param array $condition
     * @return array:string mixed
     */
    public function deleteGroup($condition)
    {
        $check_condition = array_column($condition, 2, 0);
        $site_id = $check_condition['site_id'] ?? '';
        if ($site_id === '') {
            return $this->error('', 'REQUEST_SITE_ID');
        }
        $group_id = $check_condition['group_id'] ?? 0;
        if (!is_int($group_id) && $group_id <= 0) {
            return $this->error('', 'USER_GROUP_NOT_ALL_DELETE');
        }
        $temp_count = model('user_group')->getCount([['group_id', '=', $group_id], ['app_module', '=', 'store'], ['site_id', '=', $site_id]], 'uid');
        if ($temp_count > 0)
            return $this->error('', 'USER_GROUP_USED');

        $condition[] = ['keyword', '=', ''];//只能删除非系统用户组
        $res = model('cashier_auth_group')->delete($condition);
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
        $res = model('cashier_auth_group')->getColumn($condition, $field);
        return $this->success($res);
    }

    /**
     * 获取用户组详情
     * @param array $condition
     * @return array:string mixed
     */
    public function getGroupInfo($condition, $field = '*')
    {
        $info = model('cashier_auth_group')->getInfo($condition, $field);
        return $this->success($info);
    }

    /**
     * 获取用户组列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     * @return array:string mixed
     */
    public function getGroupList($condition = [], $field = true, $order = 'group_id asc', $limit = null)
    {
        $list = model('cashier_auth_group')->getList($condition, $field, $order, '', '', '', $limit);
        return $this->success($list);
    }

    /**
     * 获取管理组分页列表
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     * @return array:string mixed
     */
    public function getGroupPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'group_id asc', $field = '*')
    {
        $list = model('cashier_auth_group')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }
}
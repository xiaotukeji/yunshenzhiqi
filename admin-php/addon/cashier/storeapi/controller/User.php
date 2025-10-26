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

namespace addon\cashier\storeapi\controller;

use addon\cashier\model\Group;
use app\model\system\User as UserModel;
use app\model\system\UserGroup;
use app\storeapi\controller\BaseStoreApi;


/**
 * 用户控制器
 * Class User
 * @package addon\shop\siteapi\controller
 */
class User extends BaseStoreApi
{
    /**
     * 用户列表
     * @return false|string
     */
    public function lists()
    {
        $page = $this->params[ 'page' ] ?? 1;
        $page_size = $this->params[ 'page_size' ] ?? PAGE_LIST_ROWS;
        $status = $this->params[ 'status' ] ?? '';
        $username = $this->params[ 'username' ] ?? '';

        $condition = [
            [ 'ug.site_id', '=', $this->site_id ],
            [ 'ug.store_id', '=', $this->store_id ],
            [ 'ug.app_module', '=', 'store' ]
        ];
        $curr_user_info = $this->getUserInfo();
        if($curr_user_info['create_uid'] > 0){
            $condition[] = ['u.create_user_data', 'like', '%"'.$this->uid.'"%'];
        }
        if (!empty($username)) {
            $condition[] = [ 'u.username', 'like', '%' . $username . '%' ];
        }
        if ($status != '') {
            $condition[ 'u.status' ] = [ 'status', '=', $status ];
        }
        $join = [
            [ 'user u', 'u.uid = ug.uid', 'inner' ],
            [ 'cashier_auth_group cag', 'cag.group_id = ug.group_id', 'inner' ]
        ];
        $field = 'u.uid,u.username,u.is_admin,u.status,u.create_time,cag.group_id,cag.group_name,u.login_time,u.create_uid,u.create_user_data';
        $user_model = new UserGroup();
        $list = $user_model->getUserPageList($condition, $page, $page_size, 'u.is_admin desc,u.create_time desc', $field, 'ug', $join);
        return $this->response($list);
    }

    /**
     * 添加用户
     * @return false|string
     */
    public function addUser()
    {
        $username = $this->params[ 'username' ] ?? '';
        $password = $this->params[ 'password' ] ?? '';
        $group_id = $this->params[ 'group_id' ] ?? '';

        $user_model = new UserModel();
        $data = [
            'username' => $username,
            'password' => $password,
            'group_id' => $group_id,
            'app_module' => 'shop',
            'site_id' => $this->site_id,
            'store' => [
                [ 'store_id' => $this->store_id, 'group_id' => $group_id ]
            ],
            'create_uid' => $this->uid,
        ];
        $result = $user_model->addUser($data, $this->store_id, 'add');
        return $this->response($result);
    }

    /**
     * 用户详情
     */
    public function userInfo()
    {
        $uid = $this->params[ 'uid' ] ?? 0;
        if (!$uid) {
            $user_info = $this->success($this->user_info);
        }else{
            $condition = [
                [ 'ug.site_id', '=', $this->site_id ],
                [ 'ug.store_id', '=', $this->store_id ],
                [ 'ug.uid', '=', $uid ],
                [ 'ug.app_module', '=', 'store' ]
            ];
            $join = [
                [ 'user u', 'u.uid = ug.uid', 'inner' ],
                [ 'group g', 'g.group_id = u.group_id', 'left' ],
                [ 'cashier_auth_group cag', 'cag.group_id = ug.group_id', 'inner' ]
            ];
            $field = 'u.uid,u.username,u.is_admin,u.status,u.create_time,u.login_time,u.login_ip,cag.group_id,cag.group_name,g.is_system,u.create_uid,u.create_user_data';
            $user_model = new UserGroup();
            $user_info = $user_model->getUserInfo($condition, $field, 'ug', $join);
        }
        if(!empty($user_info['data']['uid'])){
            $user_model = new UserModel();
            $user_info['data']['user_group_list'] = $user_model->getUserInfo([['uid', '=', $user_info['data']['uid']]], 'uid')['data']['user_group_list'] ?? [];
        }
        if(!empty($user_info['data']['create_uid'])){
            $user_model = new UserModel();
            $user_info['data']['create_user_info'] = $user_model->getUserInfo([['uid', '=', $user_info['data']['create_uid']]], '*')['data'];
        }

        return $this->response($user_info);
    }

    /**
     * 删除用户
     */
    public function deleteUser()
    {
        $uid = $this->params[ 'uid' ] ?? 0;
        if ($uid == $this->user_info[ 'uid' ]) return $this->response($this->error('', '自己不能删除自己'));
        $user_model = new UserGroup();
        $condition = [
            [ 'uid', '=', $uid ],
            [ 'site_id', '=', $this->site_id ],
            [ 'store_id', '=', $this->store_id ],
        ];
        $result = $user_model->deleteUser($condition);
        return $this->response($result);
    }

    /**
     * 管理组列表
     * @return false|string
     */
    public function group()
    {
        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'store_id', 'in', [0,$this->store_id]],
        ];
        $group_model = new Group();
        $list = $group_model->getGroupList($condition, 'group_id,group_name,create_uid,create_user_data,store_id,store_name');
        return $this->response($list);
    }

    /**
     * 用户日志
     */
    public function userLog()
    {
        $user_model = new UserModel();

        $page = $this->params[ 'page' ] ?? 1;
        $page_size = $this->params[ 'page_size' ] ?? PAGE_LIST_ROWS;
        $uid = $this->params[ 'uid' ] ?? 0;
        $search_keys = $this->params[ 'search_keys' ] ?? '';

        $condition = [];
        $condition[] = [ 'site_id', '=', $this->site_id ];
        if (!empty($search_keys)) {
            $condition[] = [ 'action_name', 'like', '%' . $search_keys . '%' ];
        }
        if ($uid > 0) {
            $condition[] = [ 'uid', '=', $uid ];
        }

        $list = $user_model->getUserLogPageList($condition, $page, $page_size, 'create_time desc');
        return $this->response($list);
    }

    /**
     * 编辑用户
     * @return false|string
     */
    public function editUser()
    {
        $user_model = new UserModel();

        $group_id = $this->params[ 'group_id' ] ?? '';
        $status = $this->params[ 'status' ] ?? '';
        $uid = $this->params[ 'uid' ] ?? 0;

        $condition = [
            [ 'uid', '=', $uid ],
            [ 'site_id', '=', $this->site_id ],
            [ 'app_module', '=', $this->app_module ],
        ];
        $data = [
            'group_id' => $group_id,
            'status' => $status,
            'store' => [
                [ 'store_id' => $this->store_id, 'group_id' => $group_id ]
            ]
        ];

        $this->addLog('编辑用户:' . $uid);

        $result = $user_model->editUser($data, $condition);
        return $this->response($result);
    }

    /**
     * 修改密码
     * */
    public function modifyPassword()
    {
        $site_id = $this->site_id;
        $user_model = new UserModel();
        $uid = $this->uid;

        $old_pass = $this->params[ 'old_pass' ] ?? '';
        $new_pass = $this->params[ 'new_pass' ] ?? '123456';

        $condition = [
            [ 'uid', '=', $uid ],
            [ 'password', '=', data_md5($old_pass) ],
            [ 'site_id', '=', $site_id ]
        ];

        $res = $user_model->modifyAdminUserPassword($condition, $new_pass);
        return $this->response($res);
    }

    /**
     * 获取门店用户权限
     */
    public function userGroupAuth()
    {
        $data = [
            'is_admin' => $this->user_info[ 'is_admin' ],
            'menu_array' => $this->store_list[ $this->store_id ][ 'menu_array' ] ?? ''
        ];
        return $this->response($this->success($data));
    }
}
<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\cashier\shop\controller;

use addon\cashier\model\Group;
use addon\cashier\model\Menu;
use app\shop\controller\BaseShop;
use think\facade\Db;
use app\model\store\Store as StoreModel;

/**
 * Class User
 * @package app\shop\controller
 */
class User extends BaseShop
{
    /**
     * 用户列表
     * @return mixed
     */
    public function group()
    {
        $curr_user_info = $this->getUserInfo();
        if(empty($curr_user_info)){
            echo '用户信息有误';exit;
        }
        $curr_user_group_ids = array_column($curr_user_info['user_group_list'], 'group_id');
        if (request()->isJson()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $search_keys = input('search_keys', '');

            $condition = [];
            if($curr_user_info['is_admin'] == 0){
                if(empty($curr_user_group_ids)) $curr_user_group_ids = [-1];
                $condition[] = ['', 'exp', Db::raw("group_id in (".join(',', $curr_user_group_ids).") or create_user_data like '%\"".$this->uid."\"%'")];
            }
            if (!empty($search_keys)) {
                $condition[] = [ 'group_name', 'like', '%' . $search_keys . '%' ];
            }
            $condition[] = [ '', 'exp', Db::raw("keyword = '' OR site_id = {$this->site_id}") ];

            $group_model = new Group();
            return $group_model->getGroupPageList($condition, $page, $page_size, 'group_id desc', '*');
        } else {

            $this->assign('curr_user_group_ids', $curr_user_info['is_admin'] == 0 ? $curr_user_group_ids : []);
            return $this->fetch('user/group_list');
        }
    }

    /**
     * 添加用户组
     * @return mixed
     */
    public function addGroup()
    {
        if (request()->isJson()) {
            $group_name = input('group_name', '');
            $menu_array = input('menu_array', '');
            $desc = input('desc', '');
            $store_id = input('store_id', 0);
            $store_name = input('store_name', '');
            $group_model = new Group();
            $data = [
                'group_name' => $group_name,
                'site_id' => $this->site_id,
                'menu_array' => $menu_array,
                'desc' => $desc,
                'create_time' => time(),
                'store_id' => $store_id,
                'store_name' => $store_name,
                'create_uid' => $this->uid,
            ];
            return $group_model->addGroup($data);
        } else {
            $menu_model = new Menu();
            $menu_list = $menu_model->getMenuList([], '*');
            $menu_tree = list_to_tree($menu_list[ 'data' ], 'name', 'parent', 'child_list', '');
            $this->assign('tree_data', $menu_tree);

            //可以选择的门店
            $curr_user_info = $this->getUserInfo();
            if($curr_user_info['create_uid'] == 0){
                $store_model = new StoreModel();
                $store_list = $store_model->getStoreList([], 'store_id,store_name', 'is_default desc,store_id desc')['data'];
                array_unshift($store_list, [
                    'store_id' => 0,
                    'store_name' => '全部门店',
                ]);
            }else{
                $store_list = $curr_user_info['user_group_list'];
                foreach($store_list as &$store_info){
                    if(!empty($store_info['menu_array'])){
                        $menu_list = $menu_model->getMenuList([['name', 'in', $store_info['menu_array']]], '*');
                        $store_info['tree_data'] = list_to_tree($menu_list[ 'data' ], 'name', 'parent', 'child_list', '');
                    }
                }
            }
            $this->assign('store_list', $store_list);

            return $this->fetch('user/add_group');
        }
    }

    /**
     * 编辑用户组
     * @return mixed
     */
    public function editGroup()
    {
        $group_model = new Group();
        if (request()->isJson()) {
            $group_name = input('group_name', '');
            $menu_array = input('menu_array', '');
            $group_id = input('group_id', 0);
            $desc = input('desc', '');

            $data = [
                'group_name' => $group_name,
                'menu_array' => $menu_array,
                'desc' => $desc,
            ];
            $condition = [
                ['group_id', '=', $group_id ],
                ['site_id', '=', $this->site_id ],
            ];
            return $group_model->editGroup($data, $condition);
        } else {
            $group_id = input('group_id', 0);
            $condition = [
                ['group_id', '=', $group_id ],
                ['site_id', '=', $this->site_id ],
            ];
            $group_info_result = $group_model->getGroupInfo($condition);
            $group_info = $group_info_result['data'];
            if (empty($group_info)) $this->error('未获取到用户组数据', href_url('shop/user/group'));
            $this->assign('group_info', $group_info);
            $this->assign('group_id', $group_id);
            //获取菜单权限
            $menu_model = new Menu();
            $menu_list = $menu_model->getMenuList([], '*');

            //编辑权限是可以显示的是创建者权限的合集
            $create_user_info = $this->getUserInfo($group_info['create_uid']);
            if( !empty($create_user_info)  && $create_user_info['create_uid'] > 0){
                $store_list = $create_user_info['user_group_list'];
                $menu_array = '';
                foreach($store_list as $store_info){
                    if($store_info['store_id'] == $group_info['store_id']){
                        $menu_array = $store_info['menu_array'];
                    }
                }
                if(!empty($menu_array)){
                    $menu_array .= ','.$group_info['menu_array'];
                    $menu_array = array_unique(explode(',', $menu_array));
                    $menu_list = $menu_model->getMenuList([['name', 'in', $menu_array]], '*');
                }
            }
            //处理选中数据
            $menu_array = $group_info[ 'menu_array' ];
            $menu_array = explode(',', $menu_array);
            foreach ($menu_list[ 'data' ] as $key => $val) {
                if (in_array($val[ 'name' ], $menu_array)) {
                    $menu_list[ 'data' ][ $key ][ 'checked' ] = true;
                } else {
                    $menu_list[ 'data' ][ $key ][ 'checked' ] = false;
                }
            }
            $menu_tree = list_to_tree($menu_list[ 'data' ], 'name', 'parent', 'child_list', '');
            $this->assign('tree_data', $menu_tree);

            //门店选择
            $store_model = new StoreModel();
            $store_list = $store_model->getStoreList([], 'store_id,store_name', 'is_default desc,store_id desc')['data'];
            $this->assign('store_list', $store_list);
            return $this->fetch('user/edit_group');
        }
    }

    /**
     * 删除用户组
     */
    public function deleteGroup()
    {
        if (request()->isJson()) {
            $group_id = input('group_id', '');
            $condition = [
                ['group_id', '=', $group_id ],
                ['site_id', '=', $this->site_id ],
            ];
            $group_model = new Group();
            return $group_model->deleteGroup($condition);
        }
    }
}
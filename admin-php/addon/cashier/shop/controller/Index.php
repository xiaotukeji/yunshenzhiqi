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

use addon\cashier\model\Cashier;
use app\model\store\Store;
use app\model\system\Api;
use app\shop\controller\BaseShop;
use app\model\system\User;

/**
 * Class Index
 * @package addon\cashier\shop\controller
 */
class Index extends BaseShop
{
    public function cashier()
    {
        $store_id = input('store_id', 0);

        $user_model = new User();
        $user_info = $user_model->getUserInfo([['uid', '=', $this->user_info[ 'uid' ]]], 'uid,app_module,site_id,group_id,group_name,username,status,is_admin,password')[ 'data' ];
        if ($user_info[ 'is_admin' ]) {
            $store_info = (new Store())->getDefaultStore($user_info[ 'site_id' ])[ 'data' ] ?? [];
            if (empty($user_info[ 'user_group_list' ])) {
                $user_info[ 'user_group_list' ] = [$store_info];
            } else {
                $store_list = array_column($user_info[ 'user_group_list' ], null, 'store_id');
                if (!isset($store_list[ $store_info[ 'store_id' ] ])) $user_info[ 'user_group_list' ][] = $store_info;
            }
        }
        if (!$store_id && isset($user_info[ 'user_group_list' ][ 0 ])) $store_id = $user_info[ 'user_group_list' ][ 0 ][ 'store_id' ];
        $store_ids = array_column($user_info[ 'user_group_list' ], 'store_id');
        $token = $this->createToken($user_info, (86400 * 7));
        $this->assign('store_id', in_array($store_id, $store_ids) ? $store_id : 0);
        $this->assign('token', $token);
        $this->assign('root',__ROOT__);
        $this->assign('url', ROOT_URL . '/cashregister');
        return $this->fetch('index/cashier');
    }

    /**
     * 创建token
     * @param $user_info
     * @return string
     */
    private function createToken($user_info)
    {
        $api_config = (new Api())->getApiConfig()[ 'data' ];
        $data = [
            'user_info' => $user_info,
            'expire_time' => $api_config[ 'value' ][ 'long_time' ] * 3600
        ];

        if ($api_config[ 'is_use' ] && isset($api_config[ 'value' ][ 'private_key' ]) && !empty($api_config[ 'value' ][ 'private_key' ])) {
            $token = encrypt(json_encode($data), $api_config[ 'value' ][ 'private_key' ]);
        } else {
            $token = encrypt(json_encode($data));
        }
        return $token;
    }

    /**
     * 刷新收银端
     */
    public function refreshCashier()
    {
        return (new Cashier())->refreshCashier();
    }

    /**
     * 下载收银端uniapp源码
     */
    public function downloadCashier()
    {
        $res = (new Cashier())->downloadOs();
        echo $res[ 'message' ];
    }

    /**
     * 设置收银台主题风格配置
     */
    public function setThemeConfig()
    {
        if (request()->isJson()) {
            $cashier_model = new Cashier();
            $data = [
                'title' => input('title', ''),
                'name' => input('name', ''),
                'color' => input('color', '')
            ];
            $res = $cashier_model->setThemeConfig($data, $this->site_id);
            return $res;
        }
    }

    /**
     * 获取收银台主题风格配置
     */
    public function getThemeConfig()
    {
        if (request()->isJson()) {
            $cashier_model = new Cashier();
            $res = $cashier_model->getThemeConfig($this->site_id);
            return $res;
        }
    }

    /**
     * 获取收银台主题风格列表
     */
    public function getThemeList()
    {
        if (request()->isJson()) {
            $cashier_model = new Cashier();
            $res = $cashier_model->getThemeList();
            return $res;
        }
    }
}
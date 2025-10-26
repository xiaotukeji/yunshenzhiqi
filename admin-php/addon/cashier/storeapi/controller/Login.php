<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 上海牛之云网络科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\cashier\storeapi\controller;

use app\model\system\User as UserModel;
use app\storeapi\controller\Captcha;
use app\storeapi\controller\BaseStoreApi;

/**
 * 门店登录控制器
 */
class Login extends BaseStoreApi
{
    public function __construct()
    {
        $this->params = input();
        $this->getApiConfig();
    }

    public function login()
    {
        if (empty($this->params['username'])) return $this->response($this->error([], '账号不能为空！'));
        if (empty($this->params['password'])) return $this->response($this->error([], '密码不可为空！'));

        $captcha = new Captcha();
        $check_res = $captcha->checkCaptcha();
        if ($check_res[ 'code' ] < 0) return $this->response($check_res);

        // 登录
        $login = new UserModel();
        $res = $login->uniAppLogin($this->params[ 'username' ], $this->params['password'], 'store');

        //生成access_token
        if ($res[ 'code' ] >= 0) {
            if (empty($res[ 'data' ][ 'user_group_list' ])) return $this->response($this->error('', '没有可管理的门店'));

            $token = $this->createToken($res[ 'data' ]);
            return $this->response($this->success([
                'token' => $token,
                'site_id' => $res[ 'data' ][ 'site_id' ],
                'store_id' => $res[ 'data' ][ 'user_group_list' ][ 0 ][ 'store_id' ]
            ]));
        }
        return $this->response($res);
    }
}
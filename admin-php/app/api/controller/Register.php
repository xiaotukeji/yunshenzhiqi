<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2015-2025 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 * @author : niuteam
 * @date : 2022.8.8
 * @version : v5.0.0.1
 */

namespace app\api\controller;

use app\exception\ApiException;
use app\model\member\Config;
use app\model\member\Register as RegisterModel;
use app\model\message\Message;
use think\facade\Cache;

class Register extends BaseApi
{
    /**
     * 注册设置
     */
    public function config()
    {
        $register = new Config();
        $info = $register->getRegisterConfig($this->site_id, 'shop');
        return $this->response($info);
    }

    /**
     * 相关协议
     */
    public function aggrement()
    {
        $type = $this->params['type'] ?? 'SERVICE';
        $register = new Config();
        switch($type){
            case 'SERVICE':
                $res = $register->getRegisterDocument($this->site_id, 'shop');
                break;
            case 'PRIVACY':
                $res = $register->getPrivacyDocument($this->site_id, 'shop');
                break;
            default:
                $res = $register->error(null, '不支持的类型');
        }
        return $this->response($res);
    }

    /**
     * 用户名密码注册
     */
    public function username()
    {
        $config = new Config();
        $config_info = $config->getRegisterConfig($this->site_id);
        if (strstr($config_info[ 'data' ][ 'value' ][ 'register' ], 'username') === false) return $this->response($this->error('', 'REGISTER_REFUND'));

        $register = new RegisterModel();
        $this->params[ 'username' ] = str_replace(' ', '', $this->params[ 'username' ]);
        if (empty($this->params[ 'username' ])) return $this->response($this->error('', '用户名已存在'));
        $exist = $register->usernameExist($this->params[ 'username' ], $this->site_id);
        if ($exist) {
            return $this->response($this->error('', '用户名已存在'));
        } else {
            // 校验验证码
            $config_model = new \app\model\web\Config();
            $info = $config_model->getCaptchaConfig();
            $captcha = new Captcha();
            if ($info[ 'data' ][ 'value' ][ 'shop_reception_register' ] == 1) {
                $check_res = $captcha->checkCaptcha();
                if ($check_res[ 'code' ] < 0) return $this->response($check_res);
            }

            $res = $register->usernameRegister($this->params);
            //生成access_token
            if ($res[ 'code' ] >= 0) {
                $token = $this->createToken($res[ 'data' ]);
                return $this->response($this->success(['token' => $token]));
            }
            return $this->response($res);
        }
    }

    /**
     * 手机号注册
     * @return false|string
     */
    public function mobile()
    {
        $config = new Config();
        $config_info = $config->getRegisterConfig($this->site_id);
        if (strstr($config_info[ 'data' ][ 'value' ][ 'register' ], 'mobile') === false) return $this->response($this->error('', 'REGISTER_REFUND'));

        $register = new RegisterModel();
        $exist = $register->mobileExist($this->params[ 'mobile' ], $this->site_id);
        if ($exist) {
            return $this->response($this->error('', '手机号已存在'));
        } else {
            $key = $this->params[ 'key' ];
            $verify_data = Cache::get($key);
            if (!empty($verify_data) && $verify_data[ 'mobile' ] == $this->params[ 'mobile' ] && $verify_data[ 'code' ] == $this->params[ 'code' ]) {
                $res = $register->mobileRegister($this->params);
                if ($res[ 'code' ] >= 0) {
                    $token = $this->createToken($res[ 'data' ]);
                    $res = $this->success(['token' => $token]);
                }
            } else {
                $res = $this->error('', '手机动态码不正确');
            }
            return $this->response($res);
        }
    }

    /**
     * 检测用户名称或者手机是否存在
     */
    public function exist()
    {
        $type = $this->params[ 'type' ];
        $register = new RegisterModel();
        switch ($type) {
            case 'username' :
                $res = $register->usernameExist($this->params[ 'username' ], $this->site_id);
                break;
            case 'mobile' :
                $res = $register->mobileExist($this->params[ 'mobile' ], $this->site_id);
                break;
            default:
                $res = 0;
                break;
        }
        if ($res) {
            return $this->response($this->error('', '账户已存在'));
        } else {
            return $this->response($this->success());
        }
    }

    /**
     * 短信验证码
     * @return false|string
     * @throws ApiException
     */
    public function mobileCode()
    {
        // 校验验证码
        $config_model = new \app\model\web\Config();
        $info = $config_model->getCaptchaConfig();
        if ($info[ 'data' ][ 'value' ][ 'shop_reception_register' ] == 1) {
            $captcha = new Captcha();
            $check_res = $captcha->checkCaptcha(false);
            if ($check_res[ 'code' ] < 0) return $this->response($check_res);
        }

        $mobile = $this->params[ 'mobile' ];//注册手机号
        $register = new RegisterModel();
        $exist = $register->mobileExist($mobile, $this->site_id);
        if ($exist) {
            return $this->response($this->error('', '手机号已存在'));
        } else {
            $code = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT);// 生成4位随机数，左侧补0
            $message_model = new Message();
            $res = $message_model->sendMessage(['type' => 'code', 'mobile' => $mobile, 'site_id' => $this->site_id, 'code' => $code, 'support_type' => ['sms'], 'keywords' => 'REGISTER_CODE']);
            if ($res[ 'code' ] >= 0) {
                //将验证码存入缓存
                $key = 'register_mobile_code_' . md5(uniqid(null, true));
                Cache::tag('register_mobile_code')->set($key, ['mobile' => $mobile, 'code' => $code], 600);
                return $this->response($this->success(['key' => $key]));
            } else {
                return $this->response($res);
            }
        }
    }
}
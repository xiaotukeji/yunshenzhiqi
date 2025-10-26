<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\alisms\shop\controller;

use addon\alisms\model\Config as ConfigModel;
use app\shop\controller\BaseShop;

/**
 * 阿里云短信 控制器
 */
class Sms extends BaseShop
{
    public function config()
    {
        $config_model = new ConfigModel();
        if (request()->isJson()) {
            $access_key_id = input("access_key_id", "");
            $access_key_secret = input("access_key_secret", "");
            $smssign = input("smssign", '');//短信签名

            $status = input("status", 0);//启用状态
            $data = array (
                "access_key_id" => $access_key_id,
                "access_key_secret" => $access_key_secret,
                "smssign" => $smssign
            );
            $result = $config_model->setSmsConfig($data, $status, $this->site_id, $this->app_module);
            return $result;
        } else {
            $info_result = $config_model->getSmsConfig($this->site_id, $this->app_module);
            $info = $info_result[ "data" ];
            $this->assign("info", $info);
            return $this->fetch("sms/config");
        }
    }
}
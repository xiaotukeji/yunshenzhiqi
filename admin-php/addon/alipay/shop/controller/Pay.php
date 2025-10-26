<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\alipay\shop\controller;

use addon\alipay\model\Config as ConfigModel;
use app\shop\controller\BaseShop;
use think\facade\Config;
use app\model\upload\Upload;

/**
 * 支付宝 控制器
 */
class Pay extends BaseShop
{
    public function config()
    {
        $config_model = new ConfigModel();
        if (request()->isJson()) {
            $app_id = input("app_id", "");//支付宝应用ID (支付宝分配给开发者的应用ID)
            $private_key = input("private_key", "");//应用私钥
            $public_key = input("public_key", "");//应用公钥
            $alipay_public_key = input("alipay_public_key", "");//支付宝公钥
            $app_type = input("app_type", "");//支持端口 如web app
            $pay_status = input("pay_status", 0);//支付启用状态
            $refund_status = input("refund_status", 0);//退款启用状态
            $transfer_status = input("transfer_status", 0);//转账启用状态
            $public_key_crt = input("public_key_crt", "");
            $alipay_public_key_crt = input("alipay_public_key_crt", "");
            $alipay_with_crt = input("alipay_with_crt", "");
            $countersign_type = input("countersign_type", 0);//加签模式

            $data = array (
                "app_id" => $app_id,
                "private_key" => $private_key,
                "public_key" => $public_key,
                "alipay_public_key" => $alipay_public_key,
                "refund_status" => $refund_status,
                "pay_status" => $pay_status,
                "transfer_status" => $transfer_status,
                "app_type" => $app_type,
                "public_key_crt" => $public_key_crt,
                "alipay_public_key_crt" => $alipay_public_key_crt,
                "alipay_with_crt" => $alipay_with_crt,
                "countersign_type" => $countersign_type
            );
            $result = $config_model->setPayConfig($data, $this->site_id, $this->app_module);
            return $result;
        } else {
            $info = $config_model->getPayConfig($this->site_id, $this->app_module, true)[ 'data' ][ 'value' ];

            if (!empty($info)) {
                $app_type_arr = [];
                if (!empty($info[ 'app_type' ])) {
                    $app_type_arr = explode(',', $info[ 'app_type' ]);
                }
                $info[ 'app_type_arr' ] = $app_type_arr;
                if (empty($info[ 'countersign_type' ])) {
                    $info[ 'countersign_type' ] = 0;
                }
            }
            $this->assign("info", $info);
            $this->assign("app_type", Config::get("app_type"));

            return $this->fetch("pay/config");
        }
    }

    /**
     * 上传微信支付证书
     */
    public function uploadAlipayCrt()
    {
        $upload_model = new Upload();
        $site_id = request()->siteid();
        $name = input("name", "");
        $extend_type = [ 'crt' ];
        $param = array (
            "name" => "file",
            "extend_type" => $extend_type
        );

        $site_id = max($site_id, 0);
        $result = $upload_model->setPath("common/alipay/crt/" . $site_id . "/")->file($param);
        return $result;
    }
}
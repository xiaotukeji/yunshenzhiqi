<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace addon\alisms\event;

use addon\alisms\model\Config as ConfigModel;

/**
 * 短信模板  (后台调用)
 */
class DoEditSmsMessage
{
    /**
     * 短信发送方式方式及配置
     */
    public function handle()
    {
        $config_model  = new ConfigModel();
        $config_result = $config_model->getSmsConfig();
        $config        = $config_result["data"];
        if ($config["is_use"] == 1) {
            return ["edit_url" => "alisms://shop/message/edit", "shop_url" => "alisms://shop/message/edit"];
        }

    }
}
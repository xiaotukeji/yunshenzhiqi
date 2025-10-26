<?php
/**
 * NiuShop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 */

namespace addon\niusms\shop\controller;

use addon\niusms\model\Config as ConfigModel;
use app\shop\controller\BaseShop;
use addon\niusms\model\Sms as SmsModel;

class Template extends BaseShop
{

    /**
     * 启用短信模板
     */
    public function enableTemplate()
    {
        if (request()->isJson()) {
            //获取账户名
            $config_model = new ConfigModel();
            $sms_config = $config_model->getSmsConfig($this->site_id, $this->app_module)[ 'data' ][ 'value' ];
            $sms_model = new SmsModel();

            $template_id = input('template_id', '');
            $status = input('status', '');

            $res = $sms_model->enableTemplate($template_id, $status, $sms_config, $this->site_id, $this->app_module);
            return $res;
        }

    }

    /**
     * 关闭短信模板
     * @return array|mixed
     */
    public function disableTemplate()
    {
        if (request()->isJson()) {
            //获取账户名
            $config_model = new ConfigModel();
            $sms_config = $config_model->getSmsConfig($this->site_id, $this->app_module)[ 'data' ][ 'value' ];
            $sms_model = new SmsModel();

            $template_ids = input('template_ids', '');
            $status = input('status', '');

            $res = $sms_model->disableTemplate($template_ids, $status, $sms_config);
            return $res;
        }

    }

}
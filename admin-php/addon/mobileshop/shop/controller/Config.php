<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 */

namespace addon\mobileshop\shop\controller;

use addon\mobileshop\model\Config as ConfigModel;
use app\model\system\Upgrade;
use app\shop\controller\BaseShop;
use think\App;

/**
 * 配置
 */
class Config extends BaseShop
{

    private $config_model;

    public function __construct(App $app = null)
    {
        $this->config_model = new ConfigModel();
        parent::__construct($app);
    }

    /**
     * 获取手机商家管理端部署信息
     * @return array
     */
    public function getDeploy()
    {
        if (request()->isJson()) {

            $this->assign('root_url', __ROOT__);

            $config_model = new ConfigModel();
            $config = $config_model->getMShopDomainName($this->site_id)[ 'data' ][ 'value' ];

            $res = [
                'root_url' => __ROOT__,
                'config' => $config
            ];
            return success('', '', $res);
        }
    }

    /**
     * 设置移动版商家端域名配置
     * @return array
     */
    public function setMShopDomainName()
    {
        $config_model = new ConfigModel();
        $domain_name = input('domain', '');
        $deploy_way = input('deploy_way', 'default');

        if ($deploy_way == 'default') $domain_name = __ROOT__ . '/mshop';

        $result = $config_model->setMShopDomainName([
            'domain_name_mobileshop' => $domain_name,
            'deploy_way' => $deploy_way
        ], $this->site_id);
        return $result;
    }

    /**
     * 默认部署：无需下载，一键刷新，API接口请求地址为当前域名，编译代码存放到mobileshop文件夹中
     */
    public function downloadCsDefault()
    {
        $this->setMShopDomainName();
        return $this->config_model->downloadCsDefault();
    }

    /**
     * 独立部署：下载编译代码包后，放到网站根目录下运行
     */
    public function downloadCsSeparate()
    {
        $domain = input('domain', ROOT_URL);
        $res = $this->config_model->downloadCsSeparate($domain);
        if ($res[ 'code' ] >= 0) {
            $config_model = new ConfigModel();
            $config_model->setMShopDomainName([
                'domain_name_mobileshop' => $domain,
                'deploy_way' => 'separate'
            ], $this->site_id);
        }
        echo $res[ 'message' ];
    }

    /**
     * 源码下载：下载uni-app代码包，可进行二次开发
     */
    public function downloadOs()
    {
        $res = $this->config_model->downloadOs();
        echo $res[ 'message' ];
    }

    /**
     * 小程序配置
     * @return array|mixed
     */
    public function weapp()
    {
        $weapp_model = new ConfigModel();
        if (request()->isJson()) {
            $weapp_name = input('weapp_name', '');
            $weapp_original = input('weapp_original', '');
            $appid = input('appid', '');
            $appsecret = input('appsecret', '');
            $token = input('token', 'TOKEN');
            $encodingaeskey = input('encodingaeskey', '');
            $is_use = input('is_use', 0);
            $qrcode = input('qrcode', '');
            $data = array (
                'appid' => $appid,
                'appsecret' => $appsecret,
                'token' => $token,
                'weapp_name' => $weapp_name,
                'weapp_original' => $weapp_original,
                'encodingaeskey' => $encodingaeskey,
                'qrcode' => $qrcode
            );
            $res = $weapp_model->setWeappConfig($data, $is_use, $this->site_id, $this->app_module);
            return $res;
        } else {
            $weapp_config_result = $weapp_model->getWeappConfig($this->site_id, $this->app_module);
            $config_info = $weapp_config_result[ 'data' ]['value'];
            $this->assign('config_info', $config_info);
            // 获取当前域名
            $url = __ROOT__;
            // 去除链接的http://头部
            $url_top = str_replace('https://', '', $url);
            $url_top = str_replace('http://', '', $url_top);
            // 去除链接的尾部/?s=
            $url_top = str_replace('/?s=', '', $url_top);
            $this->assign('url', $url_top);

            return $this->fetch('config/weapp');
        }

    }
}
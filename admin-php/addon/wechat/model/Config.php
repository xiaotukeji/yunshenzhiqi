<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\wechat\model;

use app\model\system\Config as ConfigModel;
use app\model\BaseModel;
use app\model\upload\Upload;

/**
 * 微信公众号配置
 */
class Config extends BaseModel
{
    /******************************************************************** 微信公众号配置 start ****************************************************************************/
    /**
     * 设置微信公众号配置
     * @param $data
     * @param $is_use
     * @param $site_id
     * @return array
     */
    public function setWechatConfig($data, $is_use, $site_id)
    {
        $config_info = $this->getWechatConfig($site_id);
        if (!empty($config_info[ 'data' ][ 'value' ][ 'qrcode' ]) && !empty($data[ 'qrcode' ]) && $config_info[ 'data' ][ 'value' ][ 'qrcode' ] != $data[ 'qrcode' ]) {
            $upload_model = new Upload();
            $upload_model->deletePic($config_info[ 'data' ][ 'value' ][ 'qrcode' ], $site_id);
        }

        $config = new ConfigModel();
        $res = $config->setConfig($data, '微信公众号设置', $is_use, [ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'WECHAT_CONFIG' ] ]);
        return $res;
    }

    /**
     * 获取微信公众号配置信息
     * @param $site_id
     * @return array
     */
    public function getWechatConfig($site_id)
    {
        $config = new ConfigModel();
        $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'WECHAT_CONFIG' ] ]);
        if (empty($res[ 'data' ][ 'value' ])) {
            $res[ 'data' ][ 'value' ] = [
                'wechat_name' => '',
                'wechat_original' => '',
                'appid' => '',
                'appsecret' => '',
                'token' => '',
                'encodingaeskey' => '',
                'qrcode' => '',
                'headimg' => ''
            ];
        }
        return $res;
    }
    /******************************************************************** 微信公众号配置 end ****************************************************************************/


    /******************************************************************** 分享内容配置 start ****************************************************************************/
    /**
     * 设置分享内容
     * @param $data
     * @param $is_use
     * @param $site_id
     * @return array
     */
    public function setShareConfig($data, $is_use, $site_id)
    {
        $config = new ConfigModel();
        $res = $config->setConfig($data, '分享内容设置', $is_use, [ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'WECHAT_SHARE_CONFIG' ] ]);
        return $res;
    }

    /**
     * 获取分享内容
     * @param $site_id
     * @return array
     */
    public function getShareConfig($site_id)
    {
        $config = new ConfigModel();
        $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'WECHAT_SHARE_CONFIG' ] ]);
        if (empty($res[ 'data' ][ 'value' ])) {
            $res[ 'data' ][ 'value' ] = [
                'goods_param_1' => '优惠价',
                'goods_param_2' => '全场正品',
                'qrcode_param_1' => '向您推荐',
                'qrcode_param_2' => '注册有优惠',
                'shop_param_1' => '欢迎打开',
                'shop_param_2' => '向您推荐',
                'shop_param_3' => '注册有优惠'
            ];
        }
        return $res;
    }
    /******************************************************************** 分享内容配置 end ****************************************************************************/

    /**
     * 设置模板消息配置
     * @param $data
     * @param $is_use
     * @param $site_id
     * @return array
     */
    public function setTemplateMessageConfig($data, $is_use, $site_id)
    {
        $config = new ConfigModel();
        $res = $config->setConfig($data, '微信模板消息设置', $is_use, [ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'WECHAT_TEMPLATE_MESSAGE_CONFIG' ] ]);
        return $res;
    }

    /**
     * 获取模板消息配置
     * @param $site_id
     * @return array
     */
    public function getTemplateMessageConfig($site_id)
    {
        $config = new ConfigModel();
        $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'WECHAT_TEMPLATE_MESSAGE_CONFIG' ] ]);
        if (empty($res[ 'data' ][ 'value' ])) {
            $res[ 'data' ][ 'value' ] = [ 'is_jump_weapp' => 0 ];
        }
        return $res;
    }
}
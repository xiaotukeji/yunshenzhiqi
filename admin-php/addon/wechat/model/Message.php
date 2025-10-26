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

use app\model\BaseModel;
use addon\weapp\model\Config as WeappConfig;
use addon\mobileshop\model\Config as MobileShopConfig;


/**
 * 微信消息模板
 */
class Message extends BaseModel
{
    /**
     * 发送模板消息
     * @param array $param
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendMessage(array $param)
    {
        try {
            $site_id = $param[ 'site_id' ];
            $support_type = $param[ "support_type" ] ?? [];

            if (!empty($support_type) && !in_array("wechat", $support_type)) return $this->success();

            if (empty($param[ 'openid' ])) return $this->success('缺少必需参数openid');

            $message_info = $param[ 'message_info' ];
            if ($message_info[ 'wechat_is_open' ] == 0) return $this->error('未启用模板消息');

            $wechat_info = json_decode($message_info[ 'wechat_json' ], true);
            if (empty($message_info[ 'wechat_template_id' ])) return $this->error('未配置模板消息');

            $template_data = [
//                'first' => [
//                    'value' => $wechat_info[ 'headtext' ],
//                    'color' => !empty($wechat_info[ 'headtextcolor' ]) ? $wechat_info[ 'headtextcolor' ] : '#f00'
//                ],
//                'remark' => [
//                    'value' => $wechat_info[ 'bottomtext' ],
//                    'color' => !empty($wechat_info[ 'bottomtextcolor' ]) ? $wechat_info[ 'bottomtextcolor' ] : '#333'
//                ]
            ];
            if (!empty($param[ 'template_data' ])) $template_data = array_merge($template_data, $param[ 'template_data' ]);


            $data = [
                'openid' => $param[ 'openid' ],
                'template_id' => $message_info[ 'wechat_template_id' ],
                'data' => $template_data,
                'miniprogram' => [],
                'url' => ""
            ];

            if (!empty($param[ 'page' ])) {
                // 商家消息
                if ($message_info[ 'message_type' ] == 2 && addon_is_exit('mobileshop', $site_id)) {
                    $config = new MobileShopConfig();
                    $weapp_config = $config->getWeappConfig($site_id)[ 'data' ][ "value" ];
                    if (!empty($weapp_config[ 'appid' ])) {
                        $data[ 'miniprogram' ] = [
                            'appid' => $weapp_config[ 'appid' ],
                            'pagepath' => $param[ 'page' ]
                        ];
                    }
                    $mshop_config = $config->getMShopDomainName($site_id)[ 'data' ][ 'value' ];
                    $data[ 'url' ] = $mshop_config[ 'domain_name_mobileshop' ] . '/' . $param[ 'page' ];
                } else {
                    $template_config_model = new Config();
                    $template_config = $template_config_model->getTemplateMessageConfig($site_id)[ 'data' ][ 'value' ];

                    if ($template_config[ 'is_jump_weapp' ]) {
                        // 小程序配置
                        $weapp_config = new WeappConfig();
                        $weapp_config = $weapp_config->getWeappConfig($site_id)[ 'data' ][ "value" ];

                        if (!empty($weapp_config[ 'appid' ])) {
                            $data[ 'miniprogram' ] = [
                                'appid' => $weapp_config[ 'appid' ],
                                'pagepath' => $param[ 'page' ]
                            ];
                        }
                    }
                    $data[ 'url' ] = getH5Domain() . '/' . $param[ 'page' ];
                }
            }
            $wechat = new Wechat($site_id);
            $res = $wechat->sendTemplateMessage($data);
            return $res;
        } catch (\Exception $e) {
            return $this->error('', "模板消息发送失败：" . $e->getMessage() . ",File：" . $e->getFile() . "，line：" . $e->getLine());
        }
    }
}
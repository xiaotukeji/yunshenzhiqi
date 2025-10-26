<?php

/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\fenxiao\model\share;

use app\model\member\Member as MemberModel;
use app\model\share\WeappShareBase as BaseModel;
use app\model\system\Config as ConfigModel;

/**
 * 分享
 */
class WeappShare extends BaseModel
{
    protected $config = [
        [
            'title' => '分销推广',
            'config_key' => 'WEAPP_SHARE_CONFIG_FENXIAO_PROMOTE',
            'path' => [ '/pages_promotion/fenxiao/promote_code' ],
            'method_prefix' => 'promote',
        ],
    ];

    protected $sort = 2;

    /**
     * 首页分享数据
     * @param $param
     * @return array
     */
    protected function promoteShareData($param)
    {
        $member_id = $param[ 'member_id' ] ?? 0;
        $param[ 'path' ] = str_replace("/pages_promotion/fenxiao/promote_code", "/pages/index/index", $param[ 'path' ]);
        if (strpos($param[ 'path' ], '?')) $param[ 'path' ] = explode('?', $param[ 'path' ])[ 0 ];

        //会员信息
        $member_model = new MemberModel();
        $member_info = $member_model->getMemberInfo([ [ 'member_id', '=', $member_id ] ], 'nickname, headimg')[ 'data' ];

        //获取和替换配置数据
        $config_data = $this->promoteShareConfig($param);
        $title = str_replace('{nickname}', $member_info[ 'nickname' ], $config_data[ 'value' ][ 'title' ]);
        $image_url = $config_data[ 'value' ][ 'imageUrl' ] ? img($config_data[ 'value' ][ 'imageUrl' ]) : '';
        $path = $this->getSharePath($param);

        $data = [
            'title' => $title,
            'path' => $path,
            'imageUrl' => $image_url,
        ];
        return [
            'permission' => [
                'onShareAppMessage' => true,
                'onShareTimeline' => true,
            ],
            'data' => $data,//分享内容
        ];
    }

    /**
     * 首页分享配置
     * @param $param
     * @return array
     */
    protected function promoteShareConfig($param)
    {
        $site_id = $param[ 'site_id' ];
        $config = $param[ 'config' ];

        $config_model = new ConfigModel();
        $data = $config_model->getConfig([
            [ 'site_id', '=', $site_id ],
            [ 'app_module', '=', 'shop' ],
            [ 'config_key', '=', $config[ 'config_key' ] ],
        ])[ 'data' ];
        if (empty($data[ 'value' ])) {
            $data[ 'value' ] = [
                'title' => '快来加入{nickname}的团队吧，一起赚有佣金哦',
                'imageUrl' => '',
            ];
        }
        $variable = [
            [ 'title' => '用户昵称', 'name' => '{nickname}' ],
        ];

        return [
            'value' => $data[ 'value' ],
            'variable' => $variable,
        ];
    }
}

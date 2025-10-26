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

use app\model\share\WchatShareBase as BaseModel;
use app\model\member\Member as MemberModel;
use app\model\system\Site as SiteModel;
use app\model\system\Config as ConfigModel;

/**
 * 分享
 */
class WchatShare extends BaseModel
{
    protected $config = [
        [
            'title' => '分销推广',
            'config_key' => 'WCHAT_SHARE_CONFIG_FENXIAO_PROMOTE',
            'path' => [ '/pages_promotion/fenxiao/promote_code' ],
            'method_prefix' => 'promote',
        ],
    ];

    protected $sort = 3;

    /**
     * 推广分享数据
     * @param $param
     * @return array
     */
    protected function promoteShareData($param)
    {
        $site_id = $param[ 'site_id' ] ?? 0;
        $member_id = $param[ 'member_id' ] ?? 0;
        $param[ 'url' ] = str_replace("/pages_promotion/fenxiao/promote_code", "/pages/index/index", $param[ 'url' ]);
        if (strpos($param[ 'url' ], '?')) $param[ 'url' ] = explode('?', $param[ 'url' ])[ 0 ];

        //站点设置
        $site_model = new SiteModel();
        $site_info = $site_model->getSiteInfo([ [ 'site_id', '=', $site_id ] ])[ 'data' ];

        //跳转路径
        $link = $this->getShareLink($param);

        //会员信息
        $member_model = new MemberModel();
        $member_info = $member_model->getMemberInfo([ [ 'member_id', '=', $member_id ] ], 'nickname, headimg')[ 'data' ];

        //获取和替换配置数据
        $config_data = $this->promoteShareConfig($param);
        $title = str_replace('{nickname}', $member_info[ 'nickname' ], $config_data[ 'value' ][ 'title' ]);
        $desc = str_replace('{nickname}', $member_info[ 'nickname' ], $config_data[ 'value' ][ 'desc' ]);
        $image_url = $config_data[ 'value' ][ 'imgUrl' ] ?: $site_info[ 'logo_square' ];

        $data = [
            'title' => $title,
            'desc' => $desc,
            'link' => $link,
            'imgUrl' => $image_url,
        ];
        return [
            'permission' => [
                'hideOptionMenu' => false,
                'hideMenuItems' => [],
            ],
            'data' => $data,//分享内容
        ];
    }

    /**
     * 推广分享配置
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
                'title' => "快来加入{nickname}的团队吧，一起赚佣金哦",
                'desc' => "好物精选\n向您推荐",
                'imgUrl' => '',
            ];
        }
        if (empty($data[ 'value' ][ 'imgUrl' ])) {
            $data[ 'value' ][ 'imgUrl' ] = img('addon/fenxiao/icon.png');
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

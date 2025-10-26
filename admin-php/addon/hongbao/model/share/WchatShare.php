<?php

/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\hongbao\model\share;

use app\model\share\WchatShareBase as BaseModel;
use addon\hongbao\model\Hongbao as HongbaoModel;
use addon\hongbao\model\HongbaoGroup as HongbaoGroupModel;
use app\model\system\Config as ConfigModel;

/**
 * 分享
 */
class WchatShare extends BaseModel
{
    protected $config = [
        [
            'title' => '红包裂变列表',
            'config_key' => 'WCHAT_SHARE_CONFIG_HONAGBAO_LIST',
            'path' => [ '/pages_tool/hongbao/list' ],
            'method_prefix' => 'hongbaoList',
        ],
        [
            'title' => '红包裂变分享',
            'config_key' => 'WCHAT_SHARE_CONFIG_HONGBAO_DETAIL',
            'path' => [ '/pages_tool/hongbao/index' ],
            'method_prefix' => 'hongbaoDetail',
        ],
    ];

    protected $sort = 4;

    /**
     * 红包裂变列表
     * @param $param
     * @return array
     */
    protected function hongbaoListShareData($param)
    {
        //跳转路径
        $link = $this->getShareLink($param);
        $config_data = $this->hongbaoListShareConfig($param)[ 'value' ];

        $data = [
            'link' => $link,
            'desc' => $config_data[ 'desc' ],
            'imgUrl' => $config_data[ 'imgUrl' ],
            'title' => $config_data[ 'title' ]
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
     * 红包裂变列表分享配置
     * @param $param
     * @return array
     */
    public function hongbaoListShareConfig($param)
    {
        $site_id = $param[ 'site_id' ];
        $config = $param[ 'config' ];

        $config_model = new ConfigModel();
        $data = $config_model->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', $config[ 'config_key' ] ] ])[ 'data' ];
        if (empty($data[ 'value' ])) {
            $data[ 'value' ] = [
                'title' => "红包裂变列表",
                'desc' => "红包抢得好\n生活没烦恼",
                'imgUrl' => ''
            ];
        }
        if (empty($data[ 'value' ][ 'imgUrl' ])) {
            $data[ 'value' ][ 'imgUrl' ] = img('addon/hongbao/icon.png');
        }
        return [
            'value' => $data[ 'value' ],
        ];
    }

    /**
     * 红包裂变分享数据
     * @param $param
     * @return array
     */
    protected function hongbaoDetailShareData($param)
    {
        $site_id = $param[ 'site_id' ];
        $member_id = $param[ 'member_id' ];
        //链接参数
        parse_str(parse_url($param[ 'url' ])[ 'query' ] ?? '', $query);
        if (isset($query[ 'hongbao_id' ])) {
            $hongbao_id = $query[ 'hongbao_id' ];
            $hongbao_model = new HongbaoModel();
            $condition = [
                [ 'hongbao_id', '=', $hongbao_id ],
                [ 'site_id', '=', $site_id ],
            ];
            $hongbao_info = $hongbao_model->getHongbaoInfo($condition)[ 'data' ];
            if (!empty($hongbao_info)) {
                $link = $this->getShareLink($param);
                if (!empty($member_id)) {
                    $group_model = new HongbaoGroupModel();
                    $group_info = $group_model->getHongbaoGroupInfo([
                        [ 'hongbao_id', '=', $hongbao_id ],
                        [ '', 'exp', \think\facade\Db::raw("FIND_IN_SET({$member_id}, group_member_ids)") ],
                    ], 'group_id')[ 'data' ];
                    if (!empty($group_info)) {
                        $group_id = $group_info[ 'group_id' ];
                        $page_path = explode('?', $param[ 'url' ])[ 0 ];
                        $link = "{$page_path}?hongbao_id={$hongbao_id}&group_id={$group_id}&inviter_id={$member_id}";
                    }
                }

                $imgUrl = $hongbao_info[ 'image' ];
                if (empty($imgUrl)) $imgUrl = $this->getDefaultShareIcon();
                $data = [
                    'link' => $link,
                    'desc' => "仅差一人，即可瓜分{$hongbao_info['money']}元红包",
                    'imgUrl' => $imgUrl,
                    'title' => $hongbao_info[ 'name' ],
                ];
                return [
                    'permission' => [
                        'hideOptionMenu' => false,
                        'hideMenuItems' => [],
                    ],
                    'data' => $data,//分享内容
                ];
            }
        }
    }
}

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

namespace addon\fenxiao\model;

use app\model\BaseModel;

/**
 * 海报模板
 */
class PosterTemplate extends BaseModel
{
    //默认模板数据
    const DEFAULT_TEMPLATE = [
        'template_id' => 0,
        'template_type' => 'fenxiao',
        'poster_name' => '',
        'background' => '',
        //二维码
        'qrcode_type' => '',
        'qrcode_width' => 80,
        'qrcode_height' => 80,
        'qrcode_top' => 540,
        'qrcode_left' => 260,
        //json数据
        'template_json' => [
            //头像
            'headimg_is_show' => 1,
            'headimg_shape' => 'circle',
            'headimg_width' => 56,
            'headimg_height' => 56,
            'headimg_top' => 426,
            'headimg_left' => 41,
            //昵称
            'nickname_is_show' => 1,
            'nickname_font_size' => 22,
            'nickname_color' => '#faa87a',
            'nickname_width' => 150,
            'nickname_height' => 30,
            'nickname_top' => 515,
            'nickname_left' => 20,
            //分享语
            'share_content' => '邀您一起分享赚佣金',
            'share_content_is_show' => 1,
            'share_content_font_size' => 14,
            'share_content_color' => '#8D8D8D',
            'share_content_width' => 130,
            'share_content_height' => 30,
            'share_content_top' => 550,
            'share_content_left' => 20,
        ]
    ];

    //默认模板数据
    const DEFAULT_CREATE_TEMPLATE = [
        'template_id' => 0,
        'template_type' => 'fenxiao',
        'poster_name' => '',
        'background' => 'upload/poster/bg/fenxiao_2.png',
        'background_width' => 720,
        'background_height' => 1280,
        //二维码
        'qrcode_type' => '',
        'qrcode_width' => 75,
        'qrcode_height' => 75,
        'qrcode_top' => 517.5,
        'qrcode_left' => 246,
        //json数据
        'template_json' => [
            //头像
            'headimg_is_show' => 1,
            'headimg_shape' => 'circle',
            'headimg_width' => 50,
            'headimg_height' => 50,
            'headimg_top' => 510,
            'headimg_left' => 32.5,
            //昵称
            'nickname_is_show' => 1,
            'nickname_font_size' => 11 / 0.725,
            'nickname_color' => [255, 129, 61],
            'nickname_width' => 150,
            'nickname_height' => 30,
            'nickname_top' => 530 - 11 / 0.725,
            'nickname_left' => 90,
            //分享语
            'share_content' => '',
            'share_content_is_show' => 1,
            'share_content_font_size' => 14,
            'share_content_color' => '#8D8D8D',
            'share_content_width' => 130,
            'share_content_height' => 30,
            'share_content_top' => 550,
            'share_content_left' => 20,
        ]
    ];

    /**
     * 添加海报模板
     * @param $data
     * @return array
     */
    public function addPosterTemplate($data)
    {
        $res = model('poster_template')->add($data);
        if ($res === false) {
            return $this->error('', 'RESULT_ERROR');
        }
        return $this->success($res);
    }

    /**
     * 编辑海报模板
     * @param $data
     * @param $condition
     * @return array
     */
    public function editPosterTemplate($data, $condition)
    {
        $res = model('poster_template')->update($data, $condition);
        if ($res === false) {
            return $this->error('', 'SAVE_FAIL');
        }
        return $this->success($res);
    }

    /**
     * 删除海报模板
     * @param $condition
     * @return array
     */
    public function deletePosterTemplate($condition)
    {
        $res = model('poster_template')->delete($condition);
        if ($res === false) {
            return $this->error('', 'RESULT_ERROR');
        }
        return $this->success($res);
    }

    /**
     * 获取海报模板信息
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getPosterTemplateInfo($condition = [], $field = '*')
    {
        $info = model('poster_template')->getInfo($condition, $field);
        return $this->success($info);
    }

    /**
     * 获取海报模板列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param null $limit
     * @return array
     */
    public function getPosterTemplateList($condition = [], $field = '*', $order = 'create_time desc')
    {
        $list = model('poster_template')->getList($condition, $field, $order);
        return $this->success($list);
    }

    /**
     * 获取海报模板分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getPosterTemplatePageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $field = '*', $order = 'create_time desc')
    {
        $list = model('poster_template')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }

    /**
     * 对之前的数据做兼容处理
     * @param $template_json
     * @return array
     */
    public function correctTemplateJsonData($template_json)
    {
        //兼容处理
        if (!isset($template_json[ 'share_content_is_show' ])) $template_json[ 'share_content_is_show' ] = 1;
        if (!isset($template_json[ 'share_content_font_size' ])) $template_json[ 'share_content_font_size' ] = 14;
        if (!isset($template_json[ 'share_content_color' ])) $template_json[ 'share_content_color' ] = '#8D8D8D';
        return $this->success($template_json);
    }

    /*************************** 模板默认数据 ********************************/

    public function getMubanInfo($condition = [], $field = '*', $alias = 'a', $join = [])
    {
        $list = model('poster_muban')->getInfo($condition, $field, $alias, $join);
        return $this->success($list);
    }

    public function getMubanList($condition = [], $field = '*')
    {
        $list = model('poster_muban')->getList($condition, $field);
        return $this->success($list);
    }

}
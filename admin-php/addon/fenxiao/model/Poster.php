<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\fenxiao\model;

use app\model\BaseModel;
use extend\Poster as PosterExtend;
use app\model\upload\Upload;

/**
 * 海报生成类
 */
class Poster extends BaseModel
{
    /**
     * 获取分销海报
     * @param $param
     * @return array|\extend\multitype|PosterExtend|mixed|string|void
     */
    public function getFenxiaoPoster($param)
    {
        $app_type = $param['app_type'] ?? 'h5';
        $qrcode_param = $param['qrcode_param'] ?? [];
        $site_id = $param['site_id'] ?? 0;
        $page = $param['page'] ?? '';
        $template_id = $param['template_id'] ?? 'default';

        //海报信息
        if ($template_id == 'default') {
            $template_info = PosterTemplate::DEFAULT_CREATE_TEMPLATE;
        } else {
            $template_info = model('poster_template')->getInfo([
                ['site_id', '=', $site_id],
                ['template_type', '=', 'fenxiao'],
                ['template_status', '=', 1],
                ['template_id', '=', $template_id],
            ]);
            if (empty($template_info)) return $this->error(null, '模板信息有误');
            $template_info['template_json'] = json_decode($template_info['template_json'], true);
        }
        //二维码信息
        $qrcode_info = $this->getQrcode($app_type, $page, $qrcode_param, $site_id);
        if ($qrcode_info['code'] < 0) return $qrcode_info;
        //会员信息
        $member_info = $this->getMemberInfo($qrcode_param['source_member']);
        if (empty($member_info)) return $this->error('未获取到会员信息');

        $res = $this->createPoster([
            'qrcode_path' => $qrcode_info['data']['path'],
            'member_info' => $member_info,
            'template_info' => $template_info,
            'site_id' => $site_id,
        ]);
        return $res;
    }

    /**
     * 生成海报
     */
    public function createPoster($param)
    {
        $qrcode_path = $param['qrcode_path'];
        $template_info = $param['template_info'];
        $member_info = $param['member_info'];
        $site_id = $param['site_id'];

        //如果有对应的数据则直接返回
        $param_md5 = $this->getPosterParamMd5($param);
        $poster_condition = [
            ['site_id', '=', $site_id],
            ['template_id', '=', $template_info['template_id'] ?? 0],
            ['type', '=', 'fenxiao'],
            ['param_md5', '=', $param_md5],
        ];
        $poster_info = model('poster')->getInfo($poster_condition, 'file_path');
        if(!empty($poster_info)){
            $upload_model = new \app\model\upload\Upload();
            if($upload_model->isImageLinkValid($poster_info['file_path'])){
                return $this->success([
                    'path' => $poster_info['file_path'],
                ]);
            }
        }

        try {
            $params = $template_info;
            $params['background_width'] = $params['background_width'] ?? 740;
            $params['background_height'] = $params['background_height'] ?? 1250;
            $poster = new PosterExtend($params['background_width'], $params['background_height']);
            $fontRate = 0.725;
            $nickname_color = is_array($params['template_json']['nickname_color']) ? $params['template_json']['nickname_color'] : hex2rgb($params['template_json']['nickname_color']);
            //外网图片无法制作海报 本地化处理
            if (strpos($params['background'], "http") !== false) {
                $params['background'] = $this->createTempImage($params['background']);
                $temp_background = $params['background'];
            }
            if (strpos($member_info['headimg'], "http") !== false) {
                $member_info['headimg'] = $this->createTempImage($member_info['headimg']);
                $temp_headimg = $member_info['headimg'];
            }
            $option = [
                [
                    'action' => 'imageCopy', // 写入背景图
                    'data' => [
                        img($params['background']),
                        0,
                        0,
                        $params['background_width'],
                        $params['background_height'],
                        'square',
                        0,
                        1
                    ]
                ],
                [
                    'action' => 'imageCopy', // 写入二维码
                    'data' => [
                        $qrcode_path,
                        (int)$params['qrcode_left'] * 2,
                        (int)$params['qrcode_top'] * 2,
                        (int)$params['qrcode_width'] * 2,
                        (int)$params['qrcode_height'] * 2,
                        'square',
                        0,
                        1
                    ]
                ],
                [
                    'action' => 'imageText', // 写入分享语
                    'data' => [
                        $params['template_json']['share_content'],
                        $params['template_json']['share_content_font_size'] * $fontRate * 2,
                        is_array($params['template_json']['share_content_color']) ? $params['template_json']['share_content_color'] : hex2rgb($params['template_json']['share_content_color']),
                        $params['template_json']['share_content_left'] * 2,
                        ($params['template_json']['share_content_top'] + $params['template_json']['share_content_font_size']) * 2,
                        $params['template_json']['share_content_width'] * 2,
                        1
                    ]
                ],
                [
                    'action' => 'imageCopy', // 写入用户头像
                    'data' => [
                        !empty($member_info['headimg']) ? img($member_info['headimg']) : img('public/static/img/default_img/head.png'),
                        $params['template_json']['headimg_left'] * 2,
                        $params['template_json']['headimg_top'] * 2,
                        $params['template_json']['headimg_width'] * 2,
                        $params['template_json']['headimg_height'] * 2,
                        !empty($params['template_json']['headimg_shape']) ? $params['template_json']['headimg_shape'] : 'square',
                        0,
                        $params['template_json']['headimg_is_show']
                    ]
                ],
                [
                    'action' => 'imageText', // 写入分享人昵称
                    'data' => [
                        $member_info['nickname'],
                        $params['template_json']['nickname_font_size'] * $fontRate * 2,
                        $nickname_color,
                        $params['template_json']['nickname_left'] * 2,
                        ($params['template_json']['nickname_top'] + $params['template_json']['nickname_font_size']) * 2,
                        $params['template_json']['nickname_width'] * 2,
                        1,
                        true,
                        $params['template_json']['nickname_is_show']
                    ]
                ],
            ];
            $option_res = $poster->create($option);
            if (is_array($option_res)) return $option_res;

            $poster_dir = 'upload/poster/distribution';
            $res = $option_res->jpeg($poster_dir, $param_md5);

            //删除本地临时生成文件。
            if (isset($temp_background)) unlink($temp_background);
            if (isset($temp_headimg)) unlink($temp_headimg);

            $file_path = $res['data']['path'];
            if ($res['code'] == 0) {
                $upload = new Upload($site_id);
                $cloud_res = $upload->fileCloud($file_path);
                if ($cloud_res['code'] >= 0) {
                    $file_path = $cloud_res['data'];
                }
            }

            //添加或更新记录
            if(empty($poster_info)){
                model('poster')->add([
                    'site_id' => $site_id,
                    'template_id' => $template_info['template_id'] ?? 0,
                    'type' => 'fenxiao',
                    'file_path' => $file_path,
                    'param_md5' => $param_md5,
                ]);
            }else{
                model('poster')->update([
                    'file_path' => $file_path,
                ], $poster_condition);
            }

            return $this->success(["path" => $file_path]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage() . $e->getFile() . $e->getLine());
        }
    }

    /**
     * 生成临时图片
     * @param $file_path
     * @return string
     */
    public function createTempImage($file_path)
    {
        $temp_dir = "upload/fenxiao_poster/temp/";
        if (!is_dir($temp_dir)) mkdir($temp_dir, 0777, true);
        $upload_model = new \app\model\upload\Upload();
        $image_content = $upload_model->curlGetFile($file_path);
        $image_ext = $upload_model->getFileExt($file_path, 'png');
        $temp_file = $temp_dir . uniqid() . ".".$image_ext;
        file_put_contents($temp_file, $image_content);
        return $temp_file;
    }

    /**
     * 获取海报名称
     * @param $param
     * @return string
     */
    public function getPosterParamMd5($param)
    {
        $qrcode_path = $param['qrcode_path'];
        $template_info = $param['template_info'];
        $member_info = $param['member_info'];

        $params = $template_info;
        $data = [
            $qrcode_path,
            $params['background'],
            $params['qrcode_left'],
            $params['qrcode_left'],
            $params['qrcode_top'],
            $params['qrcode_width'],
            $params['qrcode_height'],
            $params['template_json'],
            $member_info['headimg'],
            $member_info['nickname'],
        ];
        return md5(json_encode($data));
    }

    /**
     * 获取用户信息
     * @param $member_id
     * @return mixed
     */
    private function getMemberInfo($member_id)
    {
        $info = model('member')->getInfo(['member_id' => $member_id], 'nickname,headimg');
        return $info;
    }

    /**
     * 获取二维码
     * @param unknown $app_type 请求类型
     * @param unknown $page uniapp页面路径
     * @param unknown $qrcode_param 二维码携带参数
     * @param string $promotion_type 活动类型 null为无活动
     */
    private function getQrcode($app_type, $page, $qrcode_param, $site_id)
    {
        $res = event('Qrcode', [
            'site_id' => $site_id,
            'app_type' => $app_type,
            'type' => 'get',
            'data' => $qrcode_param,
            'page' => $page,
            'qrcode_path' => 'upload/qrcode/distribution',
            'qrcode_name' => 'distribution' . '_' . $qrcode_param['source_member'] . '_' . $site_id,
        ], true);
        return $res;
    }
}
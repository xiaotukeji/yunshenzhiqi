<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 上海牛之云网络科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 */

namespace addon\cashier\storeapi\controller;

use app\model\upload\Upload as UploadModel;
use app\storeapi\controller\BaseStoreApi;

/**
 * Class Upload
 * @package addon\shop\siteapi\controller
 */
class Upload extends BaseStoreApi
{

    /**
     * 上传(不存入相册)
     * @return false|string
     */
    public function image()
    {
        $site_id = $this->site_id;
        $upload_model = new UploadModel($site_id, $this->app_module);

        $thumb_type = $this->params['thumb'] ?? '';
        $name = $this->params['name'] ?? '';
        $watermark = $this->params['watermark'] ?? 0;// 是否需生成水印
        $cloud = $this->params['cloud'] ?? 1;// 是否需上传到云存储
        $width = $this->params['width'] ?? 0;// 限制宽度
        $height = $this->params['height'] ?? 0;// 限制高度
        $from = $this->params['from'] ?? '';// 位置

        $param = [
            'thumb_type' => '',
            'name' => 'file',
            'watermark' => $watermark,
            'cloud' => $cloud,
            'width' => $width,
            'height' => $height,
            'from' => $from
        ];
        $result = $upload_model->setPath('common/images/' . date('Ymd') . '/')->image($param);
        return $this->response($result);
    }

    /**
     * 上传 存入相册
     * @return false|string
     */
    public function album()
    {
        $upload_model = new UploadModel($this->site_id);
        $album_id = $this->params['album_id'] ?? 0;
        $name = $this->params['name'] ?? '';

        $param = [
            'thumb_type' => ['BIG', 'MID', 'SMALL'],
            'name' => 'file',
            'album_id' => $album_id
        ];
        $result = $upload_model->setPath('common/images/' . date('Ymd') . '/')->imageToAlbum($param);
        return $this->response($result);
    }

    /**
     * 视频上传
     * @return false|string
     */
    public function video()
    {
        $upload_model = new UploadModel($this->site_id);
        $name = $this->params['name'] ?? '';
        $param = [
            'name' => 'file'
        ];
        $result = $upload_model->setPath('common/video/' . date('Ymd') . '/')->video($param);
        return $this->response($this->success($result));
    }

    /**
     * 上传(不存入相册)
     * @return false|string
     */
    public function upload()
    {
        $upload_model = new UploadModel();
        $name = $this->params['name'] ?? '';
        $thumb_type = $this->params['thumb'] ?? '';
        $param = [
            'thumb_type' => '',
            'name' => 'file'
        ];
        $result = $upload_model->setPath('common/images/' . date('Ymd') . '/')->image($param);
        return $this->response($this->success($result));
    }

    /**
     *  校验文件
     */
    public function checkfile()
    {
        $upload_model = new UploadModel();
        $result = $upload_model->domainCheckFile([ 'name' => 'file']);
        return $this->response($result);
    }

    /**
     * 上传文件
     */
    public function file()
    {
        $upload_model = new UploadModel($this->site_id);

        $param = [
            'name' => 'file',
            'extend_type' => [ 'xlsx', 'pdf' ]
        ];

        $result = $upload_model->setPath('common/file/' . date('Ymd') . '/')->file($param);
        return $this->response($this->success($result));
    }

    /**
     * 删除文件
     */
    public function deleteFile()
    {
        if (request()->isJson()) {
            $path = $this->params['path'] ?? '';
            $res = false;
            if (!empty($path)) {
                $res = delFile($path);
            }
            return $this->response($this->success($res));
        }
    }

    /**
     * 上传微信支付证书
     */
    public function uploadWechatCert()
    {
        $upload_model = new UploadModel();
        $site_id = $this->site_id;
        $name = $this->params['name'] ?? '';
        $extend_type = [ 'pem' ];
        $param = [
            'name' => 'file',
            'extend_type' => $extend_type
        ];

        $site_id = max($site_id, 0);
        $result = $upload_model->setPath('common/wechat/cert/' . $site_id . '/')->file($param);
        return $this->response($this->success($result));
    }

    /**
     * 退款退货凭证上传
     */
    public function refundMessageImg()
    {
        $upload_model = new UploadModel($this->site_id);
        $param = [
            'thumb_type' => '',
            'name' => 'file',
            'watermark' => 0,
            'cloud' => 1
        ];
        $result = $upload_model->setPath('refund/refund_message/' . date('Ymd') . '/')->image($param);
        return $this->response($result);
    }

}
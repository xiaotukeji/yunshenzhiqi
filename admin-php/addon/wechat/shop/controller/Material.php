<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\wechat\shop\controller;

use addon\wechat\model\Material as MaterialModel;
use addon\wechat\model\Wechat as WechatModel;

/**
 * 微信素材控制器
 */
class Material extends BaseWechat
{

    /**
     * 素材列表--图文消息
     */
    public function lists()
    {
        if (request()->isJson()) {
            $type = input('type', '');
            $name = input('name', '');
            $page_index = input('page', 1);
            $page_size = input('limit', 9);
            if (!empty($type)) {
                $condition[] = [ 'type', "=", $type ];
            }
            if (!empty($name)) {
                $condition[] = array (
                    'value', 'like', '%"name":"%' . $name . '%","url"%'
                );
            }
            $condition[] = [ 'site_id', '=', $this->site_id ];
            $material_model = new MaterialModel();
            $material_list = $material_model->getMaterialPageList($condition, $page_index, $page_size, 'type asc,create_time desc');
            if (!empty($material_list[ 'data' ][ 'list' ]) && is_array($material_list[ 'data' ][ 'list' ])) {
                foreach ($material_list[ 'data' ][ 'list' ] as $k => $v) {
                    if (!empty($v[ 'value' ]) && json_decode($v[ 'value' ])) {
                        $material_list[ 'data' ][ 'list' ][ $k ][ 'value' ] = json_decode($v[ 'value' ], true);
                    }
                }
            }
            return $material_list;
        } else {
            return $this->fetch('material/lists');
        }

    }

    /**
     * 添加图文消息
     */
    public function add()
    {
        if (request()->isJson()) {
            $type = input('type', 1);
            $param[ 'value' ] = input('value', '');

            if ($type != 1) {
                // 图片、音频、视频、缩略图素材
                $file_path = input('path', '');

                $res = $this->uploadApi($type, [ 'path' => $file_path ]);
                if ($res[ 'code' ] != 0) {
                    return $res;
                }

                $value[ 'file_path' ] = $file_path;
                $value[ 'url' ] = $res[ 'data' ][ 'url' ];
                $param[ 'value' ] = json_encode($value);
                $param[ 'media_id' ] = $res[ 'data' ][ 'media_id' ];

            } else {
                $param[ 'media_id' ] = time() . 'GRAPHIC' . 'MESSAGE' . rand(1, 1000);
            }
            $param[ 'type' ] = $type;

            $param[ 'create_time' ] = time();
            $param[ 'update_time' ] = time();
            $param[ 'site_id' ] = $this->site_id;
            $material_model = new MaterialModel();
            $res = $material_model->addMaterial($param);

            return $res;
        } else {
            $this->assign('material_id', '0');
            $this->assign('flag', false);
            return $this->fetch('material/edit');
        }

    }

    /**
     * 修改图文消息
     */
    public function edit()
    {
        if (request()->isJson()) {
            $condition = [];
            $condition[ 'id' ] = input('id', '');

            $data[ 'value' ] = input('value', '');
            $data[ 'update_time' ] = time();

            $material_model = new MaterialModel();
            $res = $material_model->editMaterial($data, $condition);

            return $res;
        } else {
            $material_id = input('id', '');
            $this->assign('material_id', $material_id);
            $this->assign('flag', true);
            return $this->fetch('material/edit');
        }

    }

    /**
     * 添加文本素材
     */
    public function addTextMaterial()
    {
        if (request()->isJson()) {
            $type = input('type', 1);
            $param[ 'value' ] = input('value', '');

            $param[ 'type' ] = $type;

            $param[ 'create_time' ] = time();
            $param[ 'update_time' ] = time();
            $param[ 'site_id' ] = $this->site_id;
            $material_model = new MaterialModel();
            $res = $material_model->addMaterial($param);

            return $res;
        } else {
            return $this->fetch('material/add_text');
        }
    }

    /**
     * 修改文本素材
     */
    public function editTextMaterial()
    {
        $material_model = new MaterialModel();
        if (request()->isJson()) {
            $media_id = input("media_id", "");
            $media_id = str_replace("MATERIAL_TEXT_MESSAGE_", "", $media_id);

            $content = input("content", "");
            $content = [ "content" => $content ];
            $content = json_encode($content, JSON_UNESCAPED_UNICODE);

            $condition[ 'id' ] = $media_id;
            $data[ 'value' ] = $content;
            $data[ 'update_time' ] = time();

            $res = $material_model->editMaterial($data, $condition);

            return $res;
        } else {
            $material_id = input('id', '');
            $data = $material_model->getMaterialInfo([ [ 'id', '=', $material_id ], [ 'site_id', '=', $this->site_id ] ]);
            if (!empty($data[ 'data' ])) {
                $data[ 'data' ][ 'value' ] = json_decode($data[ 'data' ][ 'value' ], true);
            }
            $this->assign('material_data', $data[ 'data' ]);
            return $this->fetch('material/edit_text');
        }
    }

    /**
     * 预览图文
     */
    public function previewGraphicMessage()
    {
        $id = input('id', '');
        $index = input('i', '');
        $material_model = new MaterialModel();
        $info = $material_model->getMaterialInfo([ 'id' => $id ]);
        if (!empty($info[ 'data' ][ 'value' ]) && json_decode($info[ 'data' ][ 'value' ], true)) {
            $info[ 'data' ][ 'value' ] = json_decode($info[ 'data' ][ 'value' ], true);
        }
        $this->assign('info', $info[ 'data' ]);
        $this->assign('index', $index);
        return $this->fetch('material/preview_material');
    }

    /**
     * 预览文本
     */
    public function previewTextMessage()
    {
        $id = input('id', '');
        $material_model = new MaterialModel();
        $info = $material_model->getMaterialInfo([ 'id' => $id ]);
        if (!empty($info[ 'data' ][ 'value' ]) && json_decode($info[ 'data' ][ 'value' ], true)) {
            $info[ 'data' ][ 'value' ] = json_decode($info[ 'data' ][ 'value' ], true);
        }
        $this->assign('info', $info[ 'data' ]);
        return $this->fetch('material/preview_text');
    }

    /**
     * 上传永久素材
     * @param $type
     * @param $data
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function uploadApi($type, $data)
    {
        $wechat_model = new WechatModel();

        if ($type == 1) {
            $res = $wechat_model->uploadArticle($data);
        } else {
            if ($type == 2) {
                $type = 'image';
                $res = $wechat_model->uploadImage($data[ 'path' ]);
            } else if ($type == 3) {
                $type = 'voice';
                $res = $wechat_model->uploadVoice($data[ 'path' ]);
            } else if ($type == 4) {
                $type = 'video';
                $res = $wechat_model->uploadVideo($data[ 'path' ]);
            } else if ($type == 6) {
                $res = $wechat_model->uploadVideo($data[ 'path' ]);
                $type = 'thumb';
            }
        }
        return $res;
    }

    /**
     * 删除微信素材
     */
    public function delete()
    {
        if (request()->isJson()) {
            $id = input('id', 0);
            $material_model = new MaterialModel();
            $condition = array (
                [ "id", "=", $id ]
            );
            $res = $material_model->deleteMaterial($condition);
            return $res;
        }
    }

    /**
     * 获取素材详情
     * @return mixed
     */
    public function getMaterialInfo()
    {
        if (request()->isJson()) {
            $material_model = new MaterialModel();
            $condition = array (
                [ 'id', '=', input('id', '') ]
            );
            $material_info = $material_model->getMaterialInfo($condition);
            if (json_decode($material_info[ 'data' ][ 'value' ])) {
                $material_info[ 'data' ][ 'value' ] = json_decode($material_info[ 'data' ][ 'value' ]);
            }
            return $material_info;
        }
    }

    /**
     * 图文素材
     */
    public function articleList()
    {
        $material_model = new MaterialModel();
        $condition = array (
            [ 'type', '=', 1 ]
        );
        $material_list = $material_model->getMaterialList($condition, '*', 'update_time desc');
        if (!empty($material_list[ 'data' ]) && is_array($material_list[ 'data' ])) {
            foreach ($material_list[ 'data' ] as $k => $v) {
                if (!empty($v[ 'value' ]) && json_decode($v[ 'value' ])) {
                    $material_list[ 'data' ][ $k ][ 'value' ] = json_decode($v[ 'value' ], true);
                }
            }
        }
        $this->assign('material_list', $material_list);
        return $this->fetch('material/index');
    }

    /**
     * 素材管理
     */
    public function material()
    {
        //这这里的常量要与base中的区分，如果一致界面将无法渲染
        $type = input("type", 1);
        $this->assign("type", $type);
//        return array( 'shop/material/material' );
        return $this->fetch('material/material');
    }
}
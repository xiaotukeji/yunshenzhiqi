<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\goodsgrab\shop\controller;

use addon\goodsgrab\model\GoodsGrab as GoodsGrabModel;
use app\shop\controller\BaseShop;

/**
 * 商品采集
 */
class Goodsgrab extends BaseShop
{

    /*
     *  采集记录
     */
    public function lists()
    {
        $model = new GoodsGrabModel();
        //获取续签信息
        if (request()->isJson()) {

            $condition = [
                ['site_id', '=', $this->site_id]
            ];

            //商品类型
            $is_virtual = input('is_virtual', '');
            if ($is_virtual !== '') {
                $condition[] = ['is_virtual', '=', $is_virtual];
            }

            //商品分类
            $goods_class = input('goods_class', '');
            if ($goods_class !== '') {
                $condition[] = ['goods_class', '=', $goods_class];
            }

            $start_time = input('start_time', '');
            $end_time = input('end_time', '');
            if ($start_time && !$end_time) {
                $condition[] = ['create_time', '>=', date_to_time($start_time)];
            } elseif (!$start_time && $end_time) {
                $condition[] = ['create_time', '<=', date_to_time($end_time)];
            } elseif ($start_time && $end_time) {
                $condition[] = ['create_time', '>=', date_to_time($start_time)];
                $condition[] = ['create_time', '<=', date_to_time($end_time)];
            }

            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);

            $list = $model->getGoodsGrabPageList($condition, $page, $page_size);
            return $list;
        } else {
            //采集配置
            $config = $model->getGoodsGrabConfig($this->site_id);
            $this->assign('config', $config['data']['value']);
            return $this->fetch("goodsgrab/lists");
        }
    }

    /**
     * 采集配置
     * @return array|mixed
     */
    public function config()
    {
        $model = new GoodsGrabModel();
        if (request()->isJson()) {

            $data = [
                'type' => input('type', '99api'),
                'key' => input('key', '')
            ];
            $res = $model->setGoodsGrabConfig($data, $this->site_id);
            return $res;
        } else {
            //采集配置
            $config = $model->getGoodsGrabConfig($this->site_id);
            $this->assign('config', $config['data']['value']);
            return $this->fetch("goodsgrab/config");
        }
    }

    /*
     *  采集明细
     */
    public function detail()
    {
        $grab_id = input('grab_id', '');

        $model = new GoodsGrabModel();
        if (request()->isJson()) {

            $condition = [
                ['site_id', '=', $this->site_id],
                ['grab_id', '=', $grab_id]
            ];
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);

            $list = $model->getGoodsGrabDetailPageList($condition, $page, $page_size);
            return $list;

        } else {

            $info = $model->getGoodsGrabInfo([['site_id', '=', $this->site_id], ['grab_id', '=', $grab_id]]);
            $this->assign('info', $info['data']);

            return $this->fetch("goodsgrab/detail");
        }
    }

    /*
     *  删除
     */
    public function del()
    {
        $grab_id = input('grab_id', '');

        $model = new GoodsGrabModel();
        if (request()->isJson()) {

            $condition = [
                ['site_id', '=', $this->site_id],
                ['grab_id', '=', $grab_id]
            ];

            $res = $model->delGoodsGrab($condition);
            return $res;

        }

    }
}
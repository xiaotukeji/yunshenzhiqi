<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace app\shop\controller;

use app\model\goods\GoodsLabel as GoodsLabelModel;

class GoodsLabel extends BaseShop
{

    /**
     * 商品分组列表
     */
    public function lists()
    {
        if (request()->isJson()) {
            $page_index  = input('page', 1);
            $page_size   = input('page_size', PAGE_LIST_ROWS);
            $search_keys = input('search_keys', '');
            $condition   = [];
            $condition[] = ['site_id', '=', $this->site_id];
            if (!empty($search_keys)) {
                $condition[] = ['label_name', 'like', '%' . $search_keys . '%'];
            }

            //排序
            $link_sort = input('order', 'sort');
            $sort = input('sort', 'desc');
            if($link_sort == 'sort'){
                $order_by = $link_sort . ' ' . $sort;
            }else{
                $order_by = $link_sort . ' ' . $sort.',sort desc';
            }

            $goods_attr_model = new GoodsLabelModel();
            $list             = $goods_attr_model->getLabelPageList($condition, $page_index, $page_size, $order_by);
            return $list;
        } else {

            return $this->fetch('goodslabel/lists');
        }
    }

    /**
     * 商品分组添加
     */
    public function add()
    {
        if (request()->isJson()) {
            $label_name = input('label_name', '');
            $data       = [
                'site_id'    => $this->site_id,
                'label_name' => $label_name,
                'desc'       => input('desc', 0)
            ];
            $model      = new GoodsLabelModel();
            $res        = $model->addLabel($data);
            return $res;
        }
    }

    /**
     * 商品分组编辑
     */
    public function edit()
    {
        if (request()->isJson()) {
            $data  = [
                'id'         => input('id', ''),
                'site_id'    => $this->site_id,
                'label_name' => input('label_name', ''),
                'desc'       => input('desc', 0)
            ];
            $model = new GoodsLabelModel();
            $res   = $model->editLabel($data);
            return $res;
        }
    }

    /**
     * 商品分组删除
     */
    public function delete()
    {
        if (request()->isJson()) {
            $id     = input('id', 0);
            $model  = new GoodsLabelModel();
            $result = $model->deleteLabel([['id', '=', $id], ['site_id', '=', $this->site_id]]);
            return $result;
        }
    }

    /**
     * 修改排序
     */
    public function modifySort()
    {
        if (request()->isJson()) {
            $sort   = input('sort', 0);
            $id     = input('id', 0);
            $model  = new GoodsLabelModel();
            $result = $model->modifySort($sort, $id, $this->site_id);
            return $result;
        }
    }

}
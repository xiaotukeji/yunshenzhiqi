<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\cardservice\shop\controller;

use app\model\goods\ServiceCategory as ServiceCategoryModel;
use app\shop\controller\BaseShop;
use think\App;

/**
 * 项目分类管理 控制器
 */
class Servicecategory extends BaseShop
{
    public function __construct(App $app = null)
    {
        $this->replace = [
            'ADDON_CARDSERVICE_CSS' => __ROOT__ . '/addon/cardservice/shop/view/public/css',
            'ADDON_CARDSERVICE_JS' => __ROOT__ . '/addon/cardservice/shop/view/public/js',
            'ADDON_CARDSERVICE_IMG' => __ROOT__ . '/addon/cardservice/shop/view/public/img',
        ];
        parent::__construct($app);
    }

    /**
     * 服务分类列表
     */
    public function lists()
    {
        $goods_category_model = new ServiceCategoryModel();
        $condition[] = [ 'site_id', '=', $this->site_id ];
        $field = 'category_id,category_name,short_name,pid,level,is_show,sort,image,category_id_1,category_id_2,category_id_3';
        $order = 'pid asc,category_id asc';
        $list = $goods_category_model->getCategoryTree($condition, $field);
        if (request()->isJson()) return $list;
        $list = $list[ 'data' ];
        $this->assign("list", $list);
        return $this->fetch('category/lists');
    }

    /**
     * 服务分类添加
     */
    public function addCategory()
    {
        $goods_category_model = new ServiceCategoryModel();
        if (request()->isJson()) {

            $category_name = input('category_name', ''); // 分类名称
            $short_name = input('short_name', ''); // 简称
            $pid = input('pid', 0); //默认添加的服务分类为顶级
            $level = input('level', 1); // 层级
            $is_show = input('is_show', ''); // 是否显示
            $image = input('image', ''); // 分类图片
            $image_adv = input('image_adv', ''); // 分类广告图片
            $category_id_1 = input('category_id_1', 0); // 一级分类id
            $category_id_2 = input('category_id_2', 0); // 二级分类id
            $category_full_name = input('category_full_name', ''); // 组装名称
            $link_url = input('link_url', '');// 广告链接

            $data = [
                'site_id' => $this->site_id,
                'category_name' => $category_name,
                'short_name' => $short_name,
                'pid' => $pid,
                'level' => $level,
                'is_show' => $is_show,
                'image' => $image,
                'image_adv' => $image_adv,
                'category_id_1' => $category_id_1,
                'category_id_2' => $category_id_2,
                'category_full_name' => $category_full_name,
                'link_url' => $link_url
            ];
            $res = $goods_category_model->addCategory($data);
            if (!empty($res[ 'data' ])) {

                //修改category_id_
                $update_data = [
                    'category_id' => $res[ 'data' ],
                    'category_id_' . $level => $res[ 'data' ],
                    'site_id' => $this->site_id
                ];
                $goods_category_model->editCategory($update_data);

            }
            return $res;
        } else {
            return $this->fetch('category/add_category');
        }
    }

    /**
     * 服务分类编辑
     */
    public function editCategory()
    {
        $goods_category_model = new ServiceCategoryModel();
        if (request()->isJson()) {
            $category_id = input('category_id', '');// 分类id
            $category_name = input('category_name', '');// 分类名称
            $short_name = input('short_name', '');// 简称
            $pid = input('pid', 0);//默认添加的服务分类为顶级
            $level = input('level', 1);// 层级
            $is_show = input('is_show', 0);// 是否显示
            $image = input('image', '');// 分类图片
            $image_adv = input('image_adv', '');// 分类广告图片
            $link_url = input('link_url', '');// 广告链接
            $data = [
                'site_id' => $this->site_id,
                'category_id' => $category_id,
                'category_name' => $category_name,
                'short_name' => $short_name,
                'pid' => $pid,
                'level' => $level,
                'is_show' => $is_show,
                'image' => $image,
                'image_adv' => $image_adv,
                'link_url' => $link_url
            ];
            $this->addLog("编辑服务分类:" . $category_name);
            $res = $goods_category_model->editCategory($data);

            return $res;
        } else {

            $category_id = input('category_id', '');// 分类id

            if (empty($category_id)) {
                $this->error("缺少参数category_id");
            }

            $goods_category_info = $goods_category_model->getCategoryInfo([ [ 'category_id', '=', $category_id ], [ 'site_id', '=', $this->site_id ] ])[ 'data' ];
            if (empty($goods_category_info)) $this->error('未获取到分类数据', href_url('shop/category/lists'));

            $this->assign("goods_category_info", $goods_category_info);

            //父级
            $goods_category_parent_info = $goods_category_model->getCategoryInfo([ [ 'category_id', '=', $goods_category_info[ 'pid' ] ], [ 'site_id', '=', $this->site_id ] ], 'category_name');
            $this->assign("goods_category_parent_info", $goods_category_parent_info[ 'data' ]);

            $condition = [];
            $condition[] = [ 'site_id', '=', $this->site_id ];
            $condition[] = [ 'category_id', '<>', $category_id ];
            $field = 'category_id,category_name,short_name,pid,level,is_show,sort,image,category_id_1,category_id_2,category_id_3';
            $list = $goods_category_model->getCategoryTree($condition, $field);
            $this->assign("list", $list[ 'data' ]);
            return $this->fetch('category/edit_category');
        }
    }

    /**
     * 服务分类删除
     */
    public function deleteCategory()
    {
        if (request()->isJson()) {
            $category_id = input('category_id', '');// 分类id
            $goods_category_model = new ServiceCategoryModel();
            $res = $goods_category_model->deleteCategory($category_id, $this->site_id);
            $this->addLog("删除服务分类id:" . $category_id);
            return $res;
        }
    }

    /**
     * 获取服务分类列表
     * @return \multitype
     */
    public function getCategoryList()
    {
        $pid = input('pid', 0);// 上级id
        $level = input('level', 0);// 层级
        $goods_category_model = new ServiceCategoryModel();
        if (!empty($level)) {
            $condition = [
                [ 'level', '=', $level ]
            ];
        } else {
            $condition = [
                [ 'pid', '=', $pid ]
            ];
        }
        $condition[] = [ 'site_id', '=', $this->site_id ];
        $list = $goods_category_list = $goods_category_model->getCategoryList($condition, 'category_id,category_name,pid,level,category_id_1,category_id_2,category_id_3', 'sort asc,category_id desc');
        return $list;
    }

    /**
     * 获取服务分类信息
     * @return \multitype
     */
    public function getCategoryInfo()
    {
        $category_id = input('category_id', '');// 分类id
        $goods_category_model = new ServiceCategoryModel();
        $condition = [
            [ 'category_id', '=', $category_id ]
        ];
        $res = $goods_category_model->getCategoryInfo($condition, 'category_name');
        return $res;
    }


    /**
     * 获取服务分类
     * @return \multitype
     */
    public function getCategoryByParent()
    {
        $pid = input('pid', 0);// 上级id
        $level = input('level', 0);// 层级
        $goods_category_model = new ServiceCategoryModel();
        if (!empty($level)) {
            $condition[] = [ 'level', '=', $level ];
        }
        if (!empty($pid)) {
            $condition[] = [ 'pid', '=', $pid ];
        }
        $condition[] = [ 'site_id', '=', $this->site_id ];
        $list = $goods_category_list = $goods_category_model->getCategoryByParent($condition, 'category_id,category_name,pid,level,category_id_1,category_id_2,category_id_3');
        return $list;
    }

    /**
     * 修改服务分类排序
     */
    public function modifySort()
    {
        if (request()->isJson()) {
            $sort = input('sort', 0);
            $category_id = input('category_id', 0);
            $category_sort_array = input('category_sort_array', '');
            $goods_category_model = new ServiceCategoryModel();
            if (!empty($category_sort_array)) {
                $category_sort_array = json_decode($category_sort_array, true);
                foreach ($category_sort_array as $k => $v) {
                    $res = $goods_category_model->modifyGoodsCategorySort($v[ 'sort' ], $v[ 'category_id' ], $this->site_id);
                }
            } else {
                $res = $goods_category_model->modifyGoodsCategorySort($sort, $category_id, $this->site_id);
            }
            return $res;
        }
    }

    public function checkEditCategory()
    {
        if (request()->isJson()) {
            $pid = input('pid', 0);
            $category_id = input('category_id', 0);
            $goods_category_model = new ServiceCategoryModel();
            $res = $goods_category_model->checkEditCategory([
                'pid' => $pid,
                'category_id' => $category_id,
                'site_id' => $this->site_id
            ]);
            return $res;
        }
    }

    /**
     * 显示/隐藏
     * @return array
     */
    public function modifyShow(){
        $category_id = input('id','');
        $is_show = input('is_show',0);

        $goods_category_model = new ServiceCategoryModel();
        return $goods_category_model->modifyCategoryShow($category_id,$is_show);
    }


}
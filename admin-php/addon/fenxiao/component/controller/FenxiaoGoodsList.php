<?php

namespace addon\fenxiao\component\controller;

use app\component\controller\BaseDiyView;
use app\model\goods\GoodsCategory;

/**
 * 分销商品·组件
 *
 */
class FenxiaoGoodsList extends BaseDiyView
{

    /**
     * 设计界面
     */
    public function design()
    {
        $site_id = request()->siteid();
        $goods_category_model = new GoodsCategory();
        $category_condition[] = [ 'site_id', '=', $site_id ];
        $category_list = $goods_category_model->getCategoryTree($category_condition)[ 'data' ];
        $this->assign('category_list', $category_list);

        return $this->fetch('goods_list/design.html');
    }
}
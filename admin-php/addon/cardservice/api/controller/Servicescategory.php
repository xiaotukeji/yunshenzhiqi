<?php

namespace addon\cardservice\api\controller;

use app\model\goods\ServiceCategory as ServiceCategoryModel;
use app\api\controller\BaseApi;

/**
 * 分类
 */
class Servicescategory extends BaseApi
{

    public function lists()
    {
        $goods_category_model = new ServiceCategoryModel();
        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'is_show', '=', 0 ],//是否显示（0显示  -1不显示）
        ];
        $data = $goods_category_model->getCategoryList($condition);
        return $this->response($data);
    }
}
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

namespace addon\store\api\controller;

use addon\store\model\Label;
use app\api\controller\BaseApi;


class Store extends BaseApi
{

    /**
     * 门店标签
     * @return false|string
     */
    public function labelPage()
    {
        $label_id_arr = $this->params['label_id_arr'] ?? ''; // 门店标签id集合
        $page = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;

        $condition = [
            [ 'site_id', '=', $this->site_id ]
        ];

        if (!empty($label_id_arr)) {
            $condition[] = [ 'label_id', 'in', $label_id_arr ];
        }

        $label_model = new Label();
        $res = $label_model->getStoreLabelPageList($condition, $page, $page_size, 'sort asc,label_id desc', 'label_id,label_name');
        return $this->response($res);
    }

}
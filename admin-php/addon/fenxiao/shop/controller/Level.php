<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\fenxiao\shop\controller;

use addon\fenxiao\model\Config as ConfigModel;
use addon\fenxiao\model\FenxiaoLevel as FenxiaoLevelModel;
use app\shop\controller\BaseShop;
use addon\fenxiao\model\Fenxiao;

/**
 *  分销等级管理
 */
class Level extends BaseShop
{

    /**
     * 分销等级列表
     */
    public function lists()
    {
        $model = new FenxiaoLevelModel();
        $field = 'level_id,level_num,level_name,one_rate,two_rate,three_rate,status,create_time,is_default,one_fenxiao_order_num,one_fenxiao_order_money,one_fenxiao_total_order,order_num,order_money,one_child_num,one_child_fenxiao_num,upgrade_type';
        if (request()->isJson()) {

            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $list = $model->getLevelPageListInAdmin([ [ 'site_id', '=', $this->site_id ], ['is_default', '=', 0] ], $page, $page_size, 'level_num asc,one_rate asc', $field);

            return $list;

        } else {

            //获取系统配置
            $config_model = new ConfigModel();
            $basics = $config_model->getFenxiaoBasicsConfig($this->site_id);
            $this->assign("basics_info", $basics[ 'data' ][ 'value' ]);
            return $this->fetch('level/lists');
        }

    }

    /**
     * 添加分销等级
     */
    public function add()
    {
        $model = new FenxiaoLevelModel();

        if (request()->isJson()) {

            $data = [
                'site_id' => $this->site_id,
                'level_name' => input('level_name', ''),
                'one_rate' => input('one_rate', ''),
                'two_rate' => input('two_rate', ''),
                'three_rate' => input('three_rate', ''),
                'upgrade_type' => input('upgrade_type', ''),
                'level_num' => input('level_num', 1),
                'fenxiao_order_num' => input('fenxiao_order_num', ''),
                'fenxiao_order_meney' => input('fenxiao_order_meney', ''),
                'one_fenxiao_order_num' => input('one_fenxiao_order_num', ''),
                'one_fenxiao_order_money' => input('one_fenxiao_order_money', ''),
                'one_fenxiao_total_order' => input('one_fenxiao_total_order', ''),
                'order_num' => input('order_num', ''),
                'order_money' => input('order_money', ''),
                'child_num' => input('child_num', ''),
                'child_fenxiao_num' => input('child_fenxiao_num', ''),
                'one_child_num' => input('one_child_num', ''),
                'one_child_fenxiao_num' => input('one_child_fenxiao_num', ''),
            ];
            $res = $model->addLevel($data);
            return $res;
        } else {
            //获取系统配置
            $config_model = new ConfigModel();
            $basics = $config_model->getFenxiaoBasicsConfig($this->site_id);
            $this->assign("basics_info", $basics[ 'data' ][ 'value' ]);

            $level_weight = $model->getLevelList([ [ 'level_num', '<>', '' ], [ 'site_id', '=', $this->site_id ] ], 'level_num');
            $level_weight = $level_weight[ 'data' ];
            if (!empty($level_weight)) $level_weight = array_column($level_weight, 'level_num');
            $this->assign('level_weight', $level_weight);

            return $this->fetch('level/add');
        }

    }

    /**
     * 编辑分销等级
     */
    public function edit()
    {
        $model = new FenxiaoLevelModel();

        if (request()->isJson()) {

            $data = [
                'level_name' => input('level_name', ''),
                'one_rate' => input('one_rate', ''),
                'two_rate' => input('two_rate', ''),
                'three_rate' => input('three_rate', ''),
                'upgrade_type' => input('upgrade_type', ''),
                'level_num' => input('level_num', 0),
                'fenxiao_order_num' => input('fenxiao_order_num', ''),
                'fenxiao_order_meney' => input('fenxiao_order_meney', ''),
                'one_fenxiao_order_num' => input('one_fenxiao_order_num', ''),
                'one_fenxiao_order_money' => input('one_fenxiao_order_money', ''),
                'one_fenxiao_total_order' => input('one_fenxiao_total_order', ''),
                'order_num' => input('order_num', ''),
                'order_money' => input('order_money', ''),
                'child_num' => input('child_num', ''),
                'child_fenxiao_num' => input('child_fenxiao_num', ''),
                'one_child_num' => input('one_child_num', ''),
                'one_child_fenxiao_num' => input('one_child_fenxiao_num', ''),
            ];
            $level_id = input('level_id', '');

            $res = $model->editLevel($data, [ [ 'level_id', '=', $level_id ], [ 'site_id', '=', $this->site_id ] ]);
            return $res;
        } else {
            $level_id = input('level_id', '');
            $info = $model->getLevelInfo([ [ 'level_id', '=', $level_id ], [ 'site_id', '=', $this->site_id ] ]);
            if (empty($info[ 'data' ])) $this->error('未获取到等级数据', href_url('fenxiao://shop/level/lists'));
            $this->assign('info', $info[ 'data' ]);

            //获取系统配置
            $config_model = new ConfigModel();
            $basics = $config_model->getFenxiaoBasicsConfig($this->site_id);
            $this->assign("basics_info", $basics[ 'data' ][ 'value' ]);

            $level_weight = $model->getLevelList([ [ 'level_num', '<>', '' ], [ 'level_id', '<>', $level_id ], [ 'site_id', '=', $this->site_id ] ], 'level_num');
            $level_weight = $level_weight[ 'data' ];
            if (!empty($level_weight)) $level_weight = array_column($level_weight, 'level_num');
            $this->assign('level_weight', $level_weight);
        }

        return $this->fetch('level/edit');
    }

    /**
     * 删除分销等级
     */
    public function delete()
    {
        $model = new FenxiaoLevelModel();

        $level_id = input('level_id', '');
        $res = $model->deleteLevel($level_id, $this->site_id);
        return $res;
    }
}
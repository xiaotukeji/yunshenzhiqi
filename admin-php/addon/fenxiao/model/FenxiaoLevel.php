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


/**
 * 分销
 */
class FenxiaoLevel extends BaseModel
{

    /**
     * 添加分销等级
     * @param $data
     * @return array
     */
    public function addLevel($data)
    {
        $data[ 'create_time' ] = time();
        $data[ 'status' ] = 1;

        $res = model('fenxiao_level')->add($data);
        return $this->success($res);
    }

    /**
     * 编辑分销等级
     * @param $data
     * @param array $condition
     * @return array
     */
    public function editLevel($data, $condition = [])
    {
        $data[ 'update_time' ] = time();

        $res = model('fenxiao_level')->update($data, $condition);
        if ($res) {
            if (isset($data[ 'level_name' ]) && $data[ 'level_name' ] != '') {
                model('fenxiao')->update([ 'level_name' => $data[ 'level_name' ] ], $condition);
            }
        }

        return $this->success($res);
    }

    /**
     * 删除分销等级
     * @param $level_id
     * @param $site_id
     * @return array
     */
    public function deleteLevel($level_id, $site_id)
    {
        $fenxiao_model = new Fenxiao();
        $fenxiao_list = $fenxiao_model->getFenxiaoList([ [ 'level_id', '=', $level_id ] ], 'fenxiao_id');
        if (empty($fenxiao_list[ 'data' ])) {
            $res = model('fenxiao_level')->delete([ [ 'level_id', '=', $level_id ], [ 'site_id', '=', $site_id ] ]);
            return $this->success($res);
        } else {
            return $this->error('', '该分销等级存在其他分销商，无法删除');
        }
    }

    /**
     * 获取分销等级信息
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getLevelInfo($condition = [], $field = '*')
    {
        $res = model('fenxiao_level')->getInfo($condition, $field);
        return $this->success($res);
    }

    /**
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getLevelColumn($condition = [], $field = 'level_id')
    {
        $list = model('fenxiao_level')->getColumn($condition, $field);
        return $this->success($list);
    }

    /**
     * 获取分销商等级列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param null $limit
     * @return array
     */
    public function getLevelList($condition = [], $field = '*', $order = '', $limit = null)
    {
        $list = model('fenxiao_level')->getList($condition, $field, $order, '', '', '', $limit);
        return $this->success($list);
    }

    /**
     * 获取分销商等级分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getLevelPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*')
    {
        $list = model('fenxiao_level')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }

    /**
     * 获取最低的分销商等级
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getMinLevel($condition = [], $field = '*')
    {
        $info = model('fenxiao_level')->getFirstData($condition, $field, 'level_num asc,one_rate asc');
        return $this->success($info);
    }

    /**
     * 某项排序的第一个
     * @param $condition
     * @param $field
     * @param $order
     * @return array
     */
    public function getLevelFirst($condition, $field, $order)
    {
        $first = model('fenxiao_level')->getFirstData($condition, $field, $order);
        return $this->success($first);
    }

    /**
     * 平台端获取分销商列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     */
    public function getLevelPageListInAdmin($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*')
    {
        $list = $this->getLevelPageList($condition, $page, $page_size, $order, $field);
        if (!empty($list[ 'data' ][ 'list' ])) {
            $level_id_arr = [];
            foreach ($list[ 'data' ][ 'list' ] as $key => $val) {
                $level_id_arr[] = $val['level_id'];
            }
            $level_ids = implode(',', $level_id_arr);

            $fenxiao_list = model('fenxiao')->getList([['level_id', 'in', $level_ids]], 'level_id, count(*) as count', '', '', '', 'level_id');

            if(!empty($fenxiao_list)) {
                $key = array_column($fenxiao_list, 'level_id');
                $fenxiao_list = array_combine($key, $fenxiao_list);
            }
            foreach ($list[ 'data' ][ 'list' ] as $key => $val) {
                $list[ 'data' ][ 'list' ][ $key ][ 'fenxiao_num' ] = $fenxiao_list['level_id'] ?? 0;
            }
        }
        return $list;
    }

}
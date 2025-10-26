<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\hongbao\model;

use app\model\BaseModel;

/**
 * 好友瓜分券参与活动组
 * Class hongbao
 * @package addon\hongbao\model
 */
class HongbaoGroup extends BaseModel
{
    private $status = [
        0 => '未开始',
        1 => '进行中',
        2 => '已结束',
        -1 => '已关闭'
    ];

    /**
     * 获取好友瓜分券参与活动组状态
     * @return array
     */
    public function getHongbaoGroupStatus()
    {
        return $this->success($this->status);
    }

    /**
     * 获取分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @param string $alias
     * @param array $join
     * @return array
     */
    public function getHongbaoGroupPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'group_id desc', $field = '*', $alias = 'a', $join = [])
    {
        $list = model('promotion_hongbao_group')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        if ($list[ 'list' ]) {
            foreach ($list[ 'list' ] as $k => $v) {
                $group_member_list = [];
                $group_member_arr = [];
                if ($v[ 'group_member_ids' ]) {
                    $group_member_arr = explode(',', $v[ 'group_member_ids' ]);
                    $group_member_list = model('member')->getList([ [ 'member_id', 'in', $group_member_arr ], [ 'site_id', '=', $v[ 'site_id' ] ] ], 'member_id,headimg');
                }
                $list[ 'list' ][ $k ][ 'group_member_list' ] = $group_member_list ?? [];
                $list[ 'list' ][ $k ][ 'exist_num' ] = count($group_member_arr);
            }
        }
        return $this->success($list);
    }

    /**
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $alias
     * @param array $join
     * @return array
     * 获取列表
     */
    public function getHongbaoGroupList($condition = [], $field = '*', $order = '', $alias = 'a', $join = [])
    {
        $list = model('promotion_hongbao_group')->getList($condition, $field, $order, $alias, $join);
        return $this->success($list);
    }

    /**
     * 好友瓜分券参与活动组详情
     * @param array $condition
     * @param bool $field
     * @param string $alias
     * @param null $join
     * @param null $data
     */
    public function getHongbaoGroupInfo($condition = [], $field = true, $alias = 'a', $join = null, $data = null)
    {
        $data = model('promotion_hongbao_group')->getInfo($condition, $field, $alias, $join, $data);
        if ($data) {
            $data[ 'member_list' ] = [];
            if (!empty($data[ 'group_member_ids' ])) {
                $member_arr = explode(',', $data[ 'group_member_ids' ]);
                $data[ 'member_list' ] = model('member')->getList([ [ 'member_id', 'in', $member_arr ] ], 'member_id,username,nickname,headimg');
                if (!empty($data[ 'balance_data' ])) {
                    $balance_data = explode(',', $data[ 'balance_data' ]);
                    foreach ($data[ 'member_list' ] as $k => $v) {
                        $key = array_search($v[ 'member_id' ], $member_arr);
                        $data[ 'member_list' ][ $k ][ 'money' ] = $balance_data[ $key ];
                    }
                }
            }

        }
        return $this->success($data);
    }

    /**
     * 关闭到了时间的瓜分邀请
     * @param $launch_id
     */
    public function cronClosehongbaoLaunch($launch_id)
    {
        $launch_info = model('promotion_hongbao_group')->getInfo([ [ 'group_id', '=', $launch_id ] ]);

        if (!empty($launch_info)) {
            model('promotion_hongbao')->setInc([ [ 'hongbao_id', '=', $launch_info[ 'hongbao_id' ] ] ], 'inventory');
            model('promotion_hongbao_group')->update([ 'status' => 2 ], [ [ 'group_id', '=', $launch_id ] ]);
        }
    }
}
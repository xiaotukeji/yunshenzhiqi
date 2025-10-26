<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace app\model\member;

use think\facade\Cache;
use app\model\BaseModel;

/**
 * 会员标签
 */
class MemberLabel extends BaseModel
{

    /**
     * 添加会员标签
     *
     * @param array $data
     */
    public function addMemberLabel($data)
    {

        $res = model('member_label')->add($data);
        Cache::tag("member_label")->clear();
        return $this->success($res);
    }

    /**
     * 修改会员标签
     *
     * @param array $data
     * @param array $condition
     */
    public function editMemberLabel($data, $condition)
    {

        $res = model('member_label')->update($data, $condition);
        Cache::tag("member_label")->clear();
        return $this->success($res);
    }

    /**
     * 删除会员标签
     * @param array $condition
     */
    public function deleteMemberLabel($condition)
    {
        $res = model('member_label')->delete($condition);
        Cache::tag("member_label")->clear();
        return $this->success($res);
    }

    /**
     * 修改标签排序
     * @param int $sort
     * @param int $label_id
     */
    public function modifyMemberLabelSort($sort, $label_id)
    {
       //判断标签下有没有会员
        if(!empty($label_ids)){
            $label_id_array = explode(',',$label_ids);
            foreach($label_id_array as $val){
                $label_is_has_member = model('member')->getList([['member_label','like','%'.$val.'%'],['is_delete','=','0']],'member_id');
                if(!empty($label_is_has_member))return $this->error('','标签下有会员不能删除！');
            }
        }
        $res = model('member_label')->update(['sort' => $sort], [['label_id', '=', $label_id]]);
        Cache::tag("member_label")->clear();
        return $this->success($res);
    }

    /**
     * 获取会员标签信息
     *
     * @param array $condition
     * @param string $field
     */
    public function getMemberLabelInfo($condition = [], $field = '*')
    {
        $info = model('member_label')->getInfo($condition, $field);
        return $this->success($info);
    }

    /**
     * 获取会员标签列表
     *
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     */
    public function getMemberLabelList($condition = [], $field = '*', $order = 'sort asc, label_id asc', $limit = null)
    {
        $list = model('member_label')->getList($condition, $field, $order, '', '', '', $limit);
        return $this->success($list);
    }

    /**
     * 获取会员标签分页列表
     *
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     */
    public function getMemberLabelPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'sort asc, level_id asc', $field = '*')
    {
        $list = model('member_label')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }
}
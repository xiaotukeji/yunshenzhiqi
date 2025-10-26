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
 * 会员地址
 */
class MemberAddress extends BaseModel
{

    /**
     * 添加会员地址
     * @param array $data
     */
    public function addMemberAddress($data)
    {
        if (!empty($data[ 'longitude' ]) && !empty($data[ 'latitude' ])) {
            $data[ 'type' ] = 2;
        } else {
            $data[ 'type' ] = 1;
        }
        if ($data[ 'is_default' ] == 1) {
            model('member_address')->update([ 'is_default' => 0 ],  [ [ 'member_id', '=', $data[ 'member_id' ] ], [ 'type', '=', $data[ 'type' ] ] ]);
        }
        $res = model('member_address')->add($data);
        $count = model('member_address')->getCount([ 'member_id' => $data[ 'member_id' ] ]);
        if ($count == 1) {
            model('member_address')->update([ 'is_default' => 1 ], [ [ 'member_id', '=', $data[ 'member_id' ] ], [ 'id', '=', $res ], [ 'type', '=', $data[ 'type' ] ] ]);
            // 设置会员地址
            model('member')->update([
                'province_id' => $data[ 'province_id' ],
                'city_id' => $data[ 'city_id' ],
                'district_id' => $data[ 'district_id' ],
                'community_id' => $data[ 'community_id' ],
                'address' => $data[ 'address' ],
                'full_address' => $data[ 'full_address' ],
                'longitude' => $data[ 'longitude' ],
                'latitude' => $data[ 'latitude' ],
            ], [ [ 'member_id', '=', $data[ 'member_id' ] ] ]);
        }
        Cache::tag("member_address_" . $data[ 'member_id' ])->clear();
        return $this->success($res);
    }

    /**
     * 修改会员地址
     * @param $data
     * @return array
     */
    public function editMemberAddress($data)
    {
        $info = model('member_address')->getInfo([ 'id' => $data[ 'id' ], 'member_id' => $data[ 'member_id' ] ], 'type');
        $type = $info[ 'type' ] ?? 1;
        if (!empty($data[ 'longitude' ]) && !empty($data[ 'latitude' ])) {
            $data[ 'type' ] = 2;
        } else {
            $data[ 'type' ] = 1;
            if ($type == 2) {
                //定位地址不能修改为,非定位地址
                return $this->error([], '');
            }
        }
        if ($data[ 'is_default' ] == 1) {
            model('member_address')->update([ 'is_default' => 0 ],  [ [ 'member_id', '=', $data[ 'member_id' ] ], [ 'type', '=', $data[ 'type' ] ] ]);
        }
        $res = model('member_address')->update($data, [ 'id' => $data[ 'id' ], 'member_id' => $data[ 'member_id' ] ]);
        Cache::tag("member_address_" . $data[ 'member_id' ])->clear();
        return $this->success($res);
    }

    /**
     * 删除收获地址
     * @param array $condition
     */
    public function deleteMemberAddress($condition)
    {
        $check_condition = array_column($condition, 2, 0);
        $member_id = $check_condition['member_id'] ?? 0;
        if (empty($member_id)) {
            return $this->error("", "缺少必填参数会员id");
        }
        $res = model('member_address')->delete($condition);
        Cache::tag("member_address_" . $member_id)->clear();
        if ($res === false) {
            return $this->error('', 'RESULT_ERROR');
        }

        return $this->success($res);
    }

    /**
     * 设置默认收货地址
     * @param $id
     * @param $member_id
     * @return \multitype
     */
    public function setMemberDefaultAddress($id, $member_id)
    {
        model('member_address')->startTrans();
        try {
            $info = model('member_address')->getInfo([ 'id' => $id, 'member_id' => $member_id ], 'type');
            model('member_address')->update([ 'is_default' => 0 ], [ [ 'member_id', '=', $member_id ], [ 'type', '=', $info[ 'type' ] ] ]);
            $res = model('member_address')->update([ 'is_default' => 1 ], [ 'member_id' => $member_id, 'id' => $id ]);
            model('member_address')->commit();
            Cache::tag("member_address_" . $member_id)->clear();
            return $this->success($res);
        } catch (\Exception $e) {
            model('member_address')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 获取详情收获地址
     * @param $condition
     * @param string $field
     * @return array
     */
    public function getMemberAddressInfo($condition, $field = '*')
    {
        $res = model('member_address')->getInfo($condition, $field);
        return $this->success($res);
    }

    /**
     * 获取收获地址列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     * @return multitype:string mixed
     */
    public function getMemberAddressList($condition = [], $field = '*', $order = 'is_default desc', $limit = null)
    {
        $list = model('member_address')->getList($condition, $field, $order, '', '', '', $limit);
        return $this->success($list);

    }

    /**
     * 获取收获地址分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return \multitype
     */
    public function getMemberAddressPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'is_default desc,id desc', $field = 'id, member_id, name, mobile, telephone, address, full_address, is_default, type')
    {
        $list = model('member_address')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }

    //获取默认地址
    public function getMemberDefault($member_id)
    {
        return model('member_address')->getInfo(['member_id'=>$member_id,'is_default'=>1,'type'=>1], 'id');
    }
}
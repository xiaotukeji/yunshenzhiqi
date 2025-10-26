<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\pinfan\model;

use app\model\BaseModel;
use app\model\member\Member as MemberModel;
use app\model\order\OrderCommon;

/**
 * 拼团订单
 */
class PinfanOrder extends BaseModel
{

    /**
     * 开团/参团
     * @param $order
     * @param int $group_id
     * @param $pintuan_id
     * @return array|\multitype
     */
    public function addPinfanOrder($order, $group_id, $pintuan_id)
    {
        //获取用户头像
        $member_info = $order['member_account'];
        $order_type = $order['order_type']['order_type_id'];
        //获取拼团信息
        $pintuan_info = $order['pintuan_info'];
        //判断拼团活动状态
        if ($pintuan_info[ 'status' ] != 1) {
            return $this->error('', '该拼团活动已结束');//该拼团活动已结束
        }
        //判断是开团还是拼团
        if ($group_id) {//拼团
            //拼团组信息
            $pintuan_group_info = $order['pintuan_group_info'];

            $result = $this->isCanJoinGroup($group_id, $order[ 'member_id' ]);
            if ($result[ 'code' ] < 0) {
                return $result;
            }
            $pintuan_order_data = [
                'pintuan_id' => $pintuan_id,
                'order_id' => $order[ 'order_id' ],
                'order_no' => $order[ 'order_no' ],
                'group_id' => $pintuan_group_info[ 'group_id' ],
                'order_type' => $order_type,
                'head_id' => $pintuan_group_info[ 'head_id' ],
                'member_id' => $order[ 'member_id' ],
                'member_img' => $member_info[ 'headimg' ],
                'nickname' => $member_info[ 'nickname' ],
                'pintuan_status' => 0,
                'site_id' => $order[ 'site_id' ]
            ];
            $res = model('promotion_pinfan_order')->add($pintuan_order_data);
        } else {
            //开团
            $pintuan_order_data = [
                'pintuan_id' => $pintuan_id,
                'order_id' => $order[ 'order_id' ],
                'order_no' => $order[ 'order_no' ],
                'group_id' => 0,
                'order_type' => $order_type,
                'head_id' => $order[ 'member_id' ],
                'member_id' => $order[ 'member_id' ],
                'member_img' => $member_info[ 'headimg' ],
                'nickname' => $member_info[ 'nickname' ],
                'pintuan_status' => 0,
                'site_id' => $order[ 'site_id' ]
            ];
            $res = model('promotion_pinfan_order')->add($pintuan_order_data);

        }
        return $this->success($res);
    }

    /**
     * 判断是否可以参团
     * @param $group_id
     * @param $member_id
     * @return array
     */
    public function isCanJoinGroup($group_id, $member_id)
    {
        if ($group_id > 0) {
            $pintuan_group_model = new PinfanGroup();
            $pintuan_group = $pintuan_group_model->getPinfanGroupInfo(
                [ [ 'group_id', '=', $group_id ] ], 'group_id,head_id,pintuan_num,pintuan_count,status'
            );
            $pintuan_group_info = $pintuan_group[ 'data' ];

            if ($pintuan_group_info[ 'head_id' ] == $member_id) {
                return $this->error('', '抱歉，您不能参与自己的团');
            }
            if ($pintuan_group_info[ 'status' ] != 2) {
                return $this->error('', '该拼团组已失效');
            }
            if ($pintuan_group_info[ 'pintuan_num' ] == $pintuan_group_info[ 'pintuan_count' ]) {
                return $this->error('', '该拼团组已满员，请参加别的拼团或自己开团');
            }
            //判断是否已参团
            $count = model('promotion_pinfan_order')->getCount(
                [
                    [ 'group_id', '=', $group_id ],
                    [ 'pintuan_status', 'in', '0,2' ],
                    [ 'member_id', '=', $member_id ]
                ]
            );
            if ($count > 0) {
                return $this->error('', '请不要重复参团');
            }
        }

        return $this->success();
    }

    /**
     * @param unknown $data
     */
    public function orderPay($order)
    {
        model('promotion_pinfan_order')->startTrans();
        try {
            //禁止拼团订单在未成团中申请退款
            model('order')->update([ 'is_enable_refund' => 0 ], [ [ 'order_id', '=', $order[ 'order_id' ] ] ]);
            //支付操作查询拼团订单，如果group_id=0,创建组，else，检测成团
            //获取拼团订单信息
            $pintuan_order_info = $this->getPinfanOrderInfo([ [ 'order_id', '=', $order[ 'order_id' ] ] ])['data'] ?? [];

            $order_common_model = new OrderCommon();
            $local_result = $order_common_model->orderLock($order[ 'order_id' ]);
            if (!$local_result) return $this->error();

            $pintuan_group_model = new PinfanGroup();
            if ($pintuan_order_info[ 'group_id' ] == 0) {
                //开团
                //创建组
                $group_id = $pintuan_group_model->addPinfanGroup($pintuan_order_info);

                //更新拼团订单组信息
                $pintuan_order_data[ 'group_id' ] = $group_id[ 'data' ];
                $pintuan_order_data[ 'pintuan_status' ] = 2;
                $res = model('promotion_pinfan_order')->update($pintuan_order_data, [ [ 'order_id', '=', $order[ 'order_id' ] ] ]);
                //更新订单营销状态名称
                model('order')->update([ 'promotion_status_name' => '拼团中' ], [ [ 'order_id', '=', $order[ 'order_id' ] ] ]);

            } else {//参团

                //更新拼团订单信息
                $pintuan_order_data[ 'pintuan_status' ] = 2;
                $res = model('promotion_pinfan_order')->update($pintuan_order_data, [ [ 'order_id', '=', $order[ 'order_id' ] ] ]);
                //更新订单营销状态名称
                model('order')->update([ 'promotion_status_name' => '拼团中' ], [ [ 'order_id', '=', $order[ 'order_id' ] ] ]);

                //加入组
                $pintuan_group_model->joinPinfanGroup($pintuan_order_info);
            }

            model('promotion_pinfan_order')->commit();
            return $this->success($res);
        } catch (\Exception $e) {
            model('promotion_pinfan_order')->rollback();
            return $this->error('', $e->getMessage());
        }

    }

    /**
     * 获取拼团订单信息
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getPinfanOrderInfo($condition = [], $field = '*')
    {
        $order_info = model('promotion_pinfan_order')->getInfo($condition, $field);
        return $this->success($order_info);
    }

    /**
     * 获取订单信息
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param null $limit
     * @return array
     */
    public function getPinfanOrderList($condition = [], $field = '*', $order = '', $limit = null)
    {
        $list = model('promotion_pinfan_order')->getList($condition, $field, $order, '', '', '', $limit);
        return $this->success($list);
    }

    /**
     * 获取订单分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @return array
     */
    public function getPinfanOrderPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '')
    {
        $field = 'ppo.*,ppgs.id as pintuan_goods_id,
        ppg.pintuan_num,ppg.pintuan_count,ppg.is_promotion,ppg.end_time as group_end_time,ppg.status as group_status,
        ppf.reward_type,
        o.site_name,o.pay_time,o.pay_money,o.order_status_name,o.name,o.order_money,o.mobile,o.address,o.full_address,o.order_from_name,o.pay_type_name,o.delivery_type,o.delivery_type_name,
        og.sku_name,og.sku_image,og.is_virtual,
        ma.related_id as m_related_id,
        pc.related_id as c_related_id';
        $alias = 'ppo';
        $join = [
            [ 'order o', 'o.order_id = ppo.order_id', 'left' ],
            [ 'order_goods og', 'og.order_id = ppo.order_id', 'left' ],
            [ 'promotion_pinfan_group ppg', 'ppo.group_id = ppg.group_id', 'left' ],
            [ 'promotion_pinfan ppf', 'ppo.pintuan_id = ppf.pintuan_id', 'left' ],
            [ 'promotion_pinfan_goods ppgs', 'og.sku_id = ppgs.sku_id and ppgs.pintuan_id=ppo.pintuan_id', 'left' ],
            [ 'member_account ma', 'ma.related_id=ppo.order_id', 'left' ],
            [ 'promotion_coupon pc', 'pc.related_id=ppo.order_id', 'left' ],
        ];
        $list = model('promotion_pinfan_order')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        return $this->success($list);
    }

    /**
     * 拼团订单详情
     * @param $order_id
     * @param $member_id
     */
    public function getPinfanOrderDetail($id, $member_id, $site_id)
    {
        $field = 'ppo.*,ppgs.id as pintuan_goods_id,
        ppg.pintuan_num,ppg.pintuan_count,ppg.is_promotion,ppg.end_time as group_end_time,ppgs.pintuan_price,
        pp.group_num,pp.order_num,
        gs.discount_price,
        o.site_name,o.pay_time,o.pay_money,o.order_status_name,o.name,o.mobile,o.address,o.full_address,o.order_from_name,o.pay_type_name,o.order_type,
        og.sku_name,og.sku_image';
        $alias = 'ppo';
        $join = [
            [ 'order o', 'o.order_id = ppo.order_id', 'left' ],
            [ 'order_goods og', 'og.order_id = ppo.order_id', 'left' ],
            [ 'promotion_pinfan_group ppg', 'ppo.group_id = ppg.group_id', 'left' ],
            [ 'promotion_pinfan pp', 'pp.pintuan_id = ppo.pintuan_id', 'left' ],
            [ 'goods_sku gs', 'gs.sku_id = og.sku_id', 'left' ],
            [ 'promotion_pinfan_goods ppgs', 'og.sku_id = ppgs.sku_id and ppgs.pintuan_id=ppo.pintuan_id', 'left' ]
        ];
        $condition = array (
            [ "ppo.id", "=", $id ],
            [ "ppo.member_id", "=", $member_id ],
            [ "ppo.site_id", "=", $site_id ],
        );
        $info = model('promotion_pinfan_order')->getInfo($condition, $field, $alias, $join);
        //查询参与拼单的会员
        if (!empty($info)) {
            $member_list = model('promotion_pinfan_order')->getList([ [ "group_id", "=", $info[ "group_id" ] ] ], "member_img,nickname,member_id");
            $info[ "member_list" ] = $member_list;
        }
        return $this->success($info);
    }

}
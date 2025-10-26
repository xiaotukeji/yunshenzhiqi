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

use addon\coupon\model\Coupon as CouponModel;
use addon\coupon\model\MemberCoupon;
use app\dict\member_account\AccountDict;
use app\model\BaseModel;
use app\model\member\MemberAccount as MemberAccountModel;
use app\model\order\OrderCommon;
use app\model\order\OrderRefund;
use app\model\order\VirtualOrder;
use app\model\system\Cron;
use app\model\system\User;

/**
 * 拼团返现组
 */
class PinfanGroup extends BaseModel
{

    /**
     * 创建拼团返现组
     * @param $pintuan_order_info
     * @return array|\multitype
     */
    public function addPinfanGroup($pintuan_order_info)
    {
        model('promotion_pinfan_group')->startTrans();
        //获取拼团信息
        $pintuan_model = new Pinfan();
        $pintuan_id = $pintuan_order_info[ 'pintuan_id' ];
        $pintuan = $pintuan_model->getPinfanInfo([ [ 'pintuan_id', '=', $pintuan_id ] ]);
        $pintuan_info = $pintuan[ 'data' ];

        try {

            $data = [
                'site_id' => $pintuan_info[ 'site_id' ],
                'goods_id' => $pintuan_info[ 'goods_id' ],
                'is_virtual_goods' => $pintuan_info[ 'is_virtual_goods' ],
                'pintuan_id' => $pintuan_order_info[ 'pintuan_id' ],
                'head_id' => $pintuan_order_info[ 'head_id' ],
                'pintuan_num' => $pintuan_info[ 'pintuan_num' ],
                'pintuan_count' => 1,
                'create_time' => time(),
                'end_time' => time() + ( $pintuan_info[ 'pintuan_time' ] * 60 ),
                'status' => 2,
                'is_virtual_buy' => $pintuan_info[ 'is_virtual_buy' ],
                'is_single_buy' => $pintuan_info[ 'is_single_buy' ],
                'is_promotion' => $pintuan_info[ 'is_promotion' ],
                'buy_num' => $pintuan_info[ 'buy_num' ],
            ];
            $res = model('promotion_pinfan_group')->add($data);

            //添加拼团返现组关闭事件
            $cron = new Cron();
            $cron->addCron(1, 0, "拼团返现组关闭", "ClosePinfanGroup", $data[ 'end_time' ], $res);

            //更新拼团开组人数及购买人数
            $pintua_data = [
                'group_num' => $pintuan_info[ 'group_num' ] + 1,
                'order_num' => $pintuan_info[ 'order_num' ] + 1,
            ];
            $pintuan_model->editPinfanNum($pintua_data, [ [ 'pintuan_id', '=', $pintuan_order_info[ 'pintuan_id' ] ] ]);

            model('promotion_pinfan_group')->commit();
            return $this->success($res);
        } catch (\Exception $e) {
            model('promotion_pinfan_group')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 编辑组信息
     * @param array $condition
     * @param array $data
     * @return array
     */
    public function editPinfanGroup($condition = [], $data = [])
    {
        $res = model('promotion_pinfan_group')->update($data, $condition);
        return $this->success($res);
    }

    /**
     * 加入拼团返现组
     * @param $pintuan_order_info
     * @return array|\multitype
     */
    public function joinPinfanGroup($pintuan_order_info)
    {
        model('promotion_pinfan_group')->startTrans();
        //获取拼团信息
        $pintuan_model = new Pinfan();
        $pintuan_id = $pintuan_order_info[ 'pintuan_id' ];
        $pintuan = $pintuan_model->getPinfanInfo([ [ 'pintuan_id', '=', $pintuan_id ] ]);
        $pintuan_info = $pintuan[ 'data' ];

        try {

            $order_num = $pintuan_info[ 'order_num' ] + 1;
            $success_group_num = $pintuan_info[ 'success_group_num' ];

            //获取拼团返现组信息
            $group = $this->getPinfanGroupInfo([ [ 'group_id', '=', $pintuan_order_info[ 'group_id' ] ] ]);
            $group_info = $group[ 'data' ];
            //更新拼团返现组当前数量及状态
            $pintuan_count = $group_info[ 'pintuan_count' ] + 1;
            $res = $this->editPinfanGroup([ [ 'group_id', '=', $pintuan_order_info[ 'group_id' ] ] ], [ 'pintuan_count' => $pintuan_count ]);

            if ($pintuan_count == $group_info[ 'pintuan_num' ]) {//已成团

                $success_group_num += 1;
                //修改拼团返现组状态
                model('promotion_pinfan_group')->update([ 'status' => 3 ], [ [ 'group_id', '=', $pintuan_order_info[ 'group_id' ] ] ]);

                //查询该组所有订单
                $pintuan_order_model = new PinfanOrder();
                $pintuan_order = $pintuan_order_model->getPinfanOrderList([ [ 'group_id', '=', $pintuan_order_info[ 'group_id' ] ] ], 'order_id,pintuan_status,member_id');
                $pintuan_order_list = $pintuan_order[ 'data' ];

                #获取拼团组中支付的订单
                $pintuan_bidding_arr = [];
                foreach ($pintuan_order_list as $key => $value) {
                    if ($value[ 'pintuan_status' ] == 2) {
                        $pintuan_bidding_arr[ $key ] = $value;
                    }
                }
                $pintuan_bidding_arr = array_column($pintuan_bidding_arr, 'order_id');
                shuffle($pintuan_bidding_arr);
                #随机筛选拼中人Id组
                $pintuan_success_arr = array_slice($pintuan_bidding_arr, 0, $pintuan_info[ 'chengtuan_num' ]);

                $order_model = new OrderCommon();
                $user_model = new User();
                $member_account_model = new MemberAccountModel();
                $user_admin_info = $user_model->getUserInfo([ [ 'app_module', '=', 'shop' ], [ 'is_admin', '=', 1 ], [ 'site_id', '=', $pintuan_info[ 'site_id' ] ] ])[ 'data' ];
                if (!empty($pintuan_order_list)) {
                    foreach ($pintuan_order_list as $v) {
                        switch ( $v[ 'pintuan_status' ] ) {

                            case 0:
                                //将未支付的修改为失败
                                model('promotion_pinfan_order')->update([ 'pintuan_status' => 1 ], [ [ 'order_id', '=', $v[ 'order_id' ] ] ]);
                                //开放订单
                                $order_model->orderUnlock($v[ 'order_id' ]);
                                //关闭订单
                                $result = $order_model->orderClose($v[ 'order_id' ], [], '拼团组人数已满,订单自动关闭');
                                if ($result[ "code" ] < 0) {
                                    model('promotion_pinfan_group')->rollback();
                                    return $result;
                                }
                                //更新订单营销状态名称
                                model('order')->update([ 'promotion_status_name' => '拼团失败' ], [ [ 'order_id', '=', $v[ 'order_id' ] ] ]);
                                break;
                            case 2://已支付
                                //开放订单
                                $order_model->orderUnlock($v[ 'order_id' ]);
                                //更新订单营销状态名称
                                if (in_array($v[ 'order_id' ], $pintuan_success_arr)) {
                                    //将已支付的修改为成功
                                    model('promotion_pinfan_order')->update([ 'pintuan_status' => 3 ], [ [ 'order_id', '=', $v[ 'order_id' ] ] ]);
                                    model('order')->update([ 'promotion_status_name' => '拼团成功', 'is_enable_refund' => 1 ], [ [ 'order_id', '=', $v[ 'order_id' ] ] ]);

                                    #拼中发货
                                    //针对虚拟订单执行收发货操作
                                    if ($group_info[ 'is_virtual_goods' ] == 1) {
                                        $order_model->orderCommonTakeDelivery($v[ 'order_id' ]);
                                    }
                                } else {
                                    model('promotion_pinfan_order')->update([ 'pintuan_status' => 1 ], [ [ 'order_id', '=', $v[ 'order_id' ] ] ]);

                                    #未拼中订单
                                    //主动退款
                                    $order_refund_model = new OrderRefund();
                                    $refund_result = $order_refund_model->activeRefund($v[ 'order_id' ], "拼团订单关闭", '拼团订单关闭');
                                    if ($refund_result[ "code" ] < 0) {
                                        model('promotion_pinfan_group')->rollback();
                                        return $refund_result;
                                    }

                                    //关闭订单 todo  退款完毕后会自动关闭订单,理论上不需要在这里调用退款
//                                    $result = $order_model->orderClose($v[ 'order_id' ]);
//                                    if ($result[ "code" ] < 0) {
//                                        model('promotion_pinfan_group')->rollback();
//                                        return $result;
//                                    }
                                    //更新订单营销状态名称
                                    model('order')->update([ 'promotion_status_name' => '拼团失败' ], [ [ 'order_id', '=', $v[ 'order_id' ] ] ]);
                                    #发放奖励
                                    if ($pintuan_info[ 'reward_type' ] == 1) {
                                        #奖励储值余额
                                        $member_account_model->addMemberAccount($pintuan_info[ 'site_id' ], $v[ 'member_id' ], AccountDict::balance, $pintuan_info[ 'reward_type_num' ], 'pinfan', 0, '活动奖励发放', $v[ 'order_id' ]);
                                        $user_model->addUserLog($user_admin_info[ 'uid' ], $user_admin_info[ 'username' ], $pintuan_info[ 'site_id' ], "拼团返利：会员余额调整id:" . $v[ 'member_id' ] . "金额" . $pintuan_info[ 'reward_type_num' ]);
                                    } else if ($pintuan_info[ 'reward_type' ] == 2) {
                                        #奖励现金余额
                                        $member_account_model->addMemberAccount($pintuan_info[ 'site_id' ], $v[ 'member_id' ], 'balance_money', $pintuan_info[ 'reward_type_num' ], 'pinfan', 0, '活动奖励发放', $v[ 'order_id' ]);
                                        $user_model->addUserLog($user_admin_info[ 'uid' ], $user_admin_info[ 'username' ], $pintuan_info[ 'site_id' ], "拼团返利：会员余额调整id:" . $v[ 'member_id' ] . "金额" . $pintuan_info[ 'reward_type_num' ]);
                                    } else if ($pintuan_info[ 'reward_type' ] == 4) {
                                        #奖励积分
                                        $member_account_model->addMemberAccount($pintuan_info[ 'site_id' ], $v[ 'member_id' ], 'point', $pintuan_info[ 'reward_type_num' ], 'pinfan', 0, '活动奖励发放', $v[ 'order_id' ]);
                                        $user_model->addUserLog($user_admin_info[ 'uid' ], $user_admin_info[ 'username' ], $pintuan_info[ 'site_id' ], "拼团返利：会员积分调整id:" . $v[ 'member_id' ] . "积分数量" . $pintuan_info[ 'reward_type_num' ]);
                                    } else if ($pintuan_info[ 'reward_type' ] == 3) {
                                        #发放优惠券
                                        $coupon_type_ids = explode(',', $pintuan_info[ 'reward_type_num' ]);
                                        $coupon_data = [];
                                        foreach($coupon_type_ids as $coupon_type_id){
                                            $coupon_data[] = ['coupon_type_id' => $coupon_type_id, 'num' => 1];
                                        }
                                        $coupon_model = new CouponModel();
                                        $coupon_model->giveCoupon($coupon_data, $pintuan_info[ 'site_id' ], $v[ 'member_id' ], CouponModel::GET_TYPE_ACTIVITY_GIVE, $v[ 'order_id' ]);
                                    }
                                }
                                break;
                        }

                    }
                }

            }

            //更新拼团 购买人数
            $pintuan_model->editPinfanNum([ 'order_num' => $order_num, 'success_group_num' => $success_group_num ], [ [ 'pintuan_id', '=', $pintuan_order_info[ 'pintuan_id' ] ] ]);

            model('promotion_pinfan_group')->commit();
            return $this->success($res);
        } catch (\Exception $e) {
            model('promotion_pinfan_group')->rollback();
            return $this->error('', $e->getMessage());
        }

    }

    /**
     * 查询拼团返现组信息
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getPinfanGroupInfo($condition = [], $field = '*')
    {
        $group_info = model('promotion_pinfan_group')->getInfo($condition, $field);
        return $this->success($group_info);
    }

    /**
     *  获取拼团返现组详情
     * @param $condition
     * @return array
     */
    public function getPinfanGroupDetail($condition)
    {
        $field = 'pg.*,m.nickname,m.headimg,og.sku_name,og.sku_image,pp.pintuan_price';
        $alias = 'pg';
        $join = [
            [ 'promotion_pinfan_order ppo', 'ppo.group_id = pg.group_id and ppo.member_id = pg.head_id', 'inner' ],
            [ 'promotion_pinfan pp', 'pp.pintuan_id = pg.pintuan_id', 'inner' ],
            [ 'order_goods og', 'og.order_id = ppo.order_id', 'inner' ],
            [ 'member m', 'm.member_id = pg.head_id', 'inner' ]
        ];
        $info = model('promotion_pinfan_group')->getInfo($condition, $field, $alias, $join);
        //查询参与拼单的会员
        if (!empty($info)) {
            $member_list = model('promotion_pinfan_order')->getList([ [ "group_id", "=", $info[ "group_id" ] ] ], "member_img,nickname,member_id");
            $info[ "member_list" ] = $member_list;
        }
        return $this->success($info);
    }

    /**
     * 获取组列表
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getPinfanGroupList($condition = [], $field = '*')
    {
        $list = model('promotion_pinfan_group')->getList($condition, $field);
        return $this->success($list);
    }

    /**
     * 获取拼团返现组商品列表
     * @param array $condition
     * @return array
     */
    public function getPinfanGoodsGroupList($condition = [])
    {
        $field = 'ppg.group_id,ppg.goods_id,ppg.pintuan_id,ppg.head_id,ppg.pintuan_num,ppg.pintuan_count,ppg.create_time,ppg.end_time,ppg.status,ppg.is_single_buy,ppg.is_promotion,ppg.buy_num,m.member_id,m.nickname,m.headimg';
        $alias = 'ppg';
        $join = [
            [
                'member m',
                'ppg.head_id = m.member_id',
                'inner'
            ]
        ];
        $list = model('promotion_pinfan_group')->getList($condition, $field, 'ppg.create_time desc', $alias, $join);
        return $this->success($list);
    }

    /**
     * 获取拼团返现组分页列表
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     */
    public function getPinfanGroupPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '')
    {
        $field = 'pg.*,g.goods_name,g.goods_image,m.nickname,m.headimg';
        $alias = 'pg';
        $join = [
            [
                'goods g',
                'pg.goods_id = g.goods_id',
                'inner'
            ],
            [
                'member m',
                'm.member_id = pg.head_id',
                'inner'
            ]
        ];
        $list = model('promotion_pinfan_group')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        return $this->success($list);
    }

    /**
     * 关闭拼团返现组
     * @param $group_id
     * @return array|\multitype
     */
    public function cronClosePinfanGroup($group_id)
    {
        model('promotion_pinfan_group')->startTrans();
        try {

            //获取拼团返现组信息
            $pintuan_group = model('promotion_pinfan_group')->getInfo([ [ 'group_id', '=', $group_id ] ], 'status,is_virtual_buy,is_virtual_goods');
            if (!empty($pintuan_group)) {

                if ($pintuan_group[ 'status' ] == 2) {
                    //关闭所有已支付的订单
                    $res = $this->closePaidGroupOrder($group_id, $pintuan_group[ 'is_virtual_buy' ], $pintuan_group[ 'is_virtual_goods' ]);
                    if ($res[ 'code' ] < 0) {
                        model('promotion_pinfan_group')->rollback();
                        return $res;
                    }

                }
            }

            model('promotion_pinfan_group')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('promotion_pinfan_group')->rollback();
            return $this->error('', $e->getMessage());
        }

    }

    /**
     * 关闭拼团返现组已支付订单
     * @param $group_id
     * @param $is_virtual_buy
     * @param $is_virtual_goods
     * @return array|\multitype
     */
    public function closePaidGroupOrder($group_id, $is_virtual_buy, $is_virtual_goods)
    {
        //获取所有该组的订单
        $pintuan_order_model = new PinfanOrder();
        $paid_order = $pintuan_order_model->getPinfanOrderList([ [ 'group_id', '=', $group_id ] ], 'order_id,pintuan_status,member_id,pintuan_id');
        $paid_order_list = $paid_order[ 'data' ];

        if (!empty($paid_order_list)) {
            #获取拼团组中支付的订单
            $pintuan_bidding_arr = [];
            foreach ($paid_order_list as $key => $value) {
                if ($value[ 'pintuan_status' ] == 2) {
                    $pintuan_bidding_arr[ $key ] = $value;
                }
            }
            $pintuan_bidding_arr = array_column($pintuan_bidding_arr, 'order_id');
            shuffle($pintuan_bidding_arr);
            $pinfan_model = new Pinfan();
            $pintuan_info = $pinfan_model->getPinfanInfo([ [ 'pintuan_id', '=', $paid_order_list[ 0 ][ 'pintuan_id' ] ] ])[ 'data' ];
            if ($pintuan_info[ 'chengtuan_num' ] >= count($pintuan_bidding_arr)) {
                $pintuan_success_arr = $pintuan_bidding_arr;
            } else {
                #随机筛选拼中人Id组
                $pintuan_success_arr = array_slice($pintuan_bidding_arr, 0, $pintuan_info[ 'chengtuan_num' ]);
            }
        }

        $order_model = new OrderCommon();
        model('promotion_pinfan_group')->startTrans();

        try {
            if ($is_virtual_buy == 1) {//虚拟成团
                //修改拼团返现组状态（成功）
                $res = model('promotion_pinfan_group')->update([ 'status' => 3 ], [ [ 'group_id', '=', $group_id ] ]);

                //获取拼团返现组信息
                $pintuan_id = model('promotion_pinfan_group')->getValue([ [ 'group_id', '=', $group_id ] ], 'pintuan_id');
                //更新拼团 成团组数
                model('promotion_pinfan')->setInc([ [ 'pintuan_id', '=', $pintuan_id ] ], 'success_group_num');
                $user_model = new User();
                $member_account_model = new MemberAccountModel();
                $user_admin_info = $user_model->getUserInfo([ [ 'app_module', '=', 'shop' ], [ 'is_admin', '=', 1 ], [ 'site_id', '=', $pintuan_info[ 'site_id' ] ] ])[ 'data' ];

                if (!empty($paid_order_list)) {

                    foreach ($paid_order_list as $v) {
                        switch ( $v[ 'pintuan_status' ] ) {
                            case 0:
                                //将未支付的修改为失败
                                model('promotion_pinfan_order')->update([ 'pintuan_status' => 1 ], [ [ 'order_id', '=', $v[ 'order_id' ] ] ]);
                                //解除锁定
                                $order_model->orderUnlock($v[ 'order_id' ]);
                                //关闭订单
                                $result = $order_model->orderClose($v[ 'order_id' ], [], '拼团组关闭,订单自动关闭');
                                if ($result[ "code" ] < 0) {
                                    model('promotion_pinfan_group')->rollback();
                                    return $result;
                                }
                                //更新订单营销状态名称
                                model('order')->update([ 'promotion_status_name' => '拼团失败' ], [ [ 'order_id', '=', $v[ 'order_id' ] ] ]);
                                break;
                            case 2://已支付

                                //解除锁定
                                $order_model->orderUnlock($v[ 'order_id' ]);

                                //更新订单营销状态名称
                                if (in_array($v[ 'order_id' ], $pintuan_success_arr)) {
                                    //将已支付的修改为成功
                                    model('promotion_pinfan_order')->update([ 'pintuan_status' => 3 ], [ [ 'order_id', '=', $v[ 'order_id' ] ] ]);
                                    //更新订单营销状态名称
                                    model('order')->update([ 'promotion_status_name' => '拼团成功', 'is_enable_refund' => 1 ], [ [ 'order_id', '=', $v[ 'order_id' ] ] ]);
                                    #拼中发货
                                    //针对虚拟订单执行收发货操作
                                    if ($is_virtual_goods == 1) {
                                        $Virtual_model = new VirtualOrder();
                                        $Virtual_model->orderTakeDelivery($v[ 'order_id' ]);
                                    }
                                } else {
                                    #未拼中订单
                                    //主动退款
                                    $order_refund_model = new OrderRefund();
                                    $refund_result = $order_refund_model->activeRefund($v[ 'order_id' ], "拼团订单关闭", '拼团订单关闭');
                                    if ($refund_result[ "code" ] < 0) {
                                        model('promotion_pinfan_group')->rollback();
                                        return $refund_result;
                                    }

                                    //关闭订单  todo  主动退款后应该不需要再退款了
//                                    $result = $order_model->orderClose($v[ 'order_id' ]);
//                                    if ($result[ "code" ] < 0) {
//                                        model('promotion_pinfan_group')->rollback();
//                                        return $result;
//                                    }
                                    model('promotion_pinfan_order')->update([ 'pintuan_status' => 1 ], [ [ 'order_id', '=', $v[ 'order_id' ] ] ]);
                                    //更新订单营销状态名称
                                    model('order')->update([ 'promotion_status_name' => '拼团失败' ], [ [ 'order_id', '=', $v[ 'order_id' ] ] ]);
                                    #发放奖励
                                    if ($pintuan_info[ 'reward_type' ] == 1) {
                                        #奖励储值余额
                                        $member_account_model->addMemberAccount($pintuan_info[ 'site_id' ], $v[ 'member_id' ], AccountDict::balance, $pintuan_info[ 'reward_type_num' ], 'pinfan', 0, '活动奖励发放', $v[ 'order_id' ]);
                                        $user_model->addUserLog($user_admin_info[ 'uid' ], $user_admin_info[ 'username' ], $pintuan_info[ 'site_id' ], "拼团返利：会员余额调整id:" . $v[ 'member_id' ] . "金额" . $pintuan_info[ 'reward_type_num' ]);
                                    } else if ($pintuan_info[ 'reward_type' ] == 2) {
                                        #奖励现金余额
                                        $member_account_model->addMemberAccount($pintuan_info[ 'site_id' ], $v[ 'member_id' ], 'balance_money', $pintuan_info[ 'reward_type_num' ], 'pinfan', 0, '活动奖励发放', $v[ 'order_id' ]);
                                        $user_model->addUserLog($user_admin_info[ 'uid' ], $user_admin_info[ 'username' ], $pintuan_info[ 'site_id' ], "拼团返利：会员余额调整id:" . $v[ 'member_id' ] . "金额" . $pintuan_info[ 'reward_type_num' ]);
                                    } else if ($pintuan_info[ 'reward_type' ] == 4) {
                                        #奖励积分
                                        $member_account_model->addMemberAccount($pintuan_info[ 'site_id' ], $v[ 'member_id' ], 'point', $pintuan_info[ 'reward_type_num' ], 'pinfan', 0, '活动奖励发放', $v[ 'order_id' ]);
                                        $user_model->addUserLog($user_admin_info[ 'uid' ], $user_admin_info[ 'username' ], $pintuan_info[ 'site_id' ], "拼团返利：会员积分调整id:" . $v[ 'member_id' ] . "积分数量" . $pintuan_info[ 'reward_type_num' ]);
                                    } else if ($pintuan_info[ 'reward_type' ] == 3) {
                                        #发放优惠券
                                        $coupon_type_ids = explode(',', $pintuan_info[ 'reward_type_num' ]);
                                        $coupon_data = [];
                                        foreach($coupon_type_ids as $coupon_type_id){
                                            $coupon_data[] = ['coupon_type_id' => $coupon_type_id, 'num' => 1];
                                        }
                                        $coupon_model = new CouponModel();
                                        $coupon_model->giveCoupon($coupon_data, $pintuan_info[ 'site_id' ], $v[ 'member_id' ], CouponModel::GET_TYPE_ACTIVITY_GIVE);
                                    }
                                }
                                break;
                        }
                    }
                }

            } else {//未开启虚拟成团

                //修改拼团返现组状态为失败
                $res = model('promotion_pinfan_group')->update([ 'status' => 1 ], [ [ 'group_id', '=', $group_id ] ]);
                if (!empty($paid_order_list)) {
                    foreach ($paid_order_list as $v) {
                        switch ( $v[ 'pintuan_status' ] ) {
                            case 0:
                                //将未支付的修改为失败
                                model('promotion_pinfan_order')->update([ 'pintuan_status' => 1 ], [ [ 'order_id', '=', $v[ 'order_id' ] ] ]);
                                //解除锁定
                                $order_model->orderUnlock($v[ 'order_id' ]);
                                //关闭订单
                                $result = $order_model->orderClose($v[ 'order_id' ], [] , '拼团组关闭,订单自动关闭');
                                if ($result[ 'code' ] < 0) {
                                    model('promotion_pinfan_group')->rollback();
                                    return $result;
                                }
                                //更新订单营销状态名称
                                model('order')->update([ 'promotion_status_name' => '拼团失败' ], [ [ 'order_id', '=', $v[ 'order_id' ] ] ]);
                                break;
                            case 2:
                                //解除锁定
                                $order_model->orderUnlock($v[ 'order_id' ]);
                                //关闭拼团订单
                                model('promotion_pinfan_order')->update([ 'pintuan_status' => 1 ], [ [ 'order_id', '=', $v[ 'order_id' ] ] ]);

                                //主动退款
                                $order_refund_model = new OrderRefund();
                                $refund_result = $order_refund_model->activeRefund($v[ 'order_id' ], '拼团订单关闭,订单自动退款', '拼团订单关闭,订单自动退款');
                                if ($refund_result[ 'code' ] < 0) {
                                    model('promotion_pinfan_group')->rollback();
                                    return $refund_result;
                                }

                                //关闭订单  todo  其实可以删掉
//                                $result = $order_model->orderClose($v[ 'order_id' ]);
//                                if ($result[ "code" ] < 0) {
//                                    model('promotion_pinfan_group')->rollback();
//                                    return $result;
//                                }
                                //更新订单营销状态名称
                                model('order')->update([ 'promotion_status_name' => '拼团失败' ], [ [ 'order_id', '=', $v[ 'order_id' ] ] ]);
                                break;
                        }

                    }
                }
            }

            model('promotion_pinfan_group')->commit();
            return $this->success($res);
        } catch (\Exception $e) {
            model('promotion_pinfan_group')->rollback();
            return $this->error('', $e->getMessage());
        }

    }
}
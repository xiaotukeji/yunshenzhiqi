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

use app\dict\order_refund\OrderRefundDict;
use app\model\BaseModel;
use app\model\message\Message;
use app\model\system\Stat;
use Exception;

/**
 * 分销商品
 */
class FenxiaoOrder extends BaseModel
{

    /**
     * 分销订单计算
     * @param $order
     * @return array
     */
    public function calculate($order)
    {
        //获取分销基础配置
        $config_model = new Config();
        $fenxiao_basic_config = $config_model->getFenxiaoBasicsConfig($order[ 'site_id' ])['data'] ?? [];
        $level_config = $fenxiao_basic_config[ 'value' ][ 'level' ];
        if (empty($level_config)) {
            return $this->success();
        }
        //检测分销商上级关系
        $member_info = model('member')->getInfo([ [ 'member_id', '=', $order[ 'member_id' ] ] ], 'fenxiao_id,is_fenxiao');
        //如果没有分销商直接返回不计算,没有考虑首次付款上下级绑定
        if (empty($member_info)) {
            return $this->success();
        }
        if ($member_info[ 'fenxiao_id' ] == 0) {
            return $this->success();
        }

        $fenxiao_id = $member_info[ 'fenxiao_id' ];
        $fenxiao_info = model('fenxiao')->getInfo([ [ 'fenxiao_id', '=', $fenxiao_id ], [ 'is_delete', '=', 0 ] ]);
        if (empty($fenxiao_info)) {
            return $this->success();
        }
        // 如果购买人是分销商 并且未开启分销商自购
        if ($member_info[ 'is_fenxiao' ] && $fenxiao_basic_config[ 'value' ][ 'self_purchase_rebate' ] == 0) {
            if (empty($fenxiao_info[ 'parent' ])) return $this->success();
            $fenxiao_info = model('fenxiao')->getInfo([ [ 'fenxiao_id', '=', $fenxiao_info[ 'parent' ] ], [ 'is_delete', '=', 0 ] ]);
            if (empty($fenxiao_info)) return $this->success();
        }

        //判断几级分销
        $parent_fenxiao_info = $level_config >= 2 ? model('fenxiao')->getInfo([ [ 'fenxiao_id', '=', $fenxiao_info[ 'parent' ] ], [ 'is_delete', '=', 0 ] ], 'fenxiao_id, fenxiao_name, status, parent') : [];
        $grand_parent_fenxiao_info = $level_config >= 3 && !empty($parent_fenxiao_info[ 'parent' ]) ? model('fenxiao')->getInfo([ [ 'fenxiao_id', '=', $parent_fenxiao_info[ 'parent' ] ], [ 'is_delete', '=', 0 ] ], 'fenxiao_id, fenxiao_name, status') : [];
        $order_goods = model('order_goods')->getList([ [ 'order_id', '=', $order[ 'order_id' ] ], [ 'is_fenxiao', '=', 1 ] ], 'order_goods_id, goods_id, sku_id, sku_name, sku_image, sku_no, is_virtual, price, cost_price, num, goods_money, cost_money, delivery_no, delivery_status, real_goods_money');
        if (empty($order_goods)) return $this->success();

        model('fenxiao_order')->delete([ [ 'order_id', '=', $order[ 'order_id' ] ] ]);
        //获取分销等级
        foreach ($order_goods as $k => $v) {
            $v[ 'num' ] = numberFormat($v[ 'num' ]);

            //商品信息管理
            $goods_info = model('goods')->getInfo([ [ 'goods_id', '=', $v[ 'goods_id' ] ] ], 'is_fenxiao, fenxiao_type');
            if ($goods_info[ 'is_fenxiao' ] != 1) {
                continue;
            }

            $sku_info = model('goods_sku')->getInfo([ [ 'sku_id', '=', $v[ 'sku_id' ] ] ], 'fenxiao_price');
            if (!empty($sku_info) && $sku_info[ 'fenxiao_price' ] > 0) $v[ 'real_goods_money' ] = $sku_info[ 'fenxiao_price' ] * $v[ 'num' ];

            $commission = 0;
            $commission_rate = 0;
            $order_fenxiao_data = [
                'one_rate' => 0,
                'one_commission' => 0,
                'two_rate' => 0,
                'two_commission' => 0,
                'three_rate' => 0,
            ];

            if ($goods_info[ 'fenxiao_type' ] == 2) {
                // 自定义分销规则
                $fenxiao_level = model('fenxiao_goods_sku')->getInfo([ [ 'goods_id', '=', $v[ 'goods_id' ] ], [ 'sku_id', '=', $v[ 'sku_id' ] ], [ 'level_id', '=', $fenxiao_info[ 'level_id' ] ] ]);
                if (empty($fenxiao_level)) continue;

                if ($fenxiao_info[ 'status' ] == 1) {
                    if ($fenxiao_level[ 'one_rate' ] > 0) {
                        $commission_rate += $order_fenxiao_data[ 'one_rate' ] = $fenxiao_level[ 'one_rate' ];
                        $commission += $order_fenxiao_data[ 'one_commission' ] = $fenxiao_level[ 'one_rate' ] * $v[ 'real_goods_money' ] / 100;
                    } else {
                        $commission_rate += $order_fenxiao_data[ 'one_rate' ] = round($fenxiao_level[ 'one_money' ] * $v[ 'num' ] / $v[ 'real_goods_money' ], 2);
                        $commission += $order_fenxiao_data[ 'one_commission' ] = $fenxiao_level[ 'one_money' ] * $v[ 'num' ];
                    }
                }
                if (!empty($parent_fenxiao_info) && $parent_fenxiao_info[ 'status' ] == 1) {
                    if ($fenxiao_level[ 'two_rate' ] > 0) {
                        $commission_rate += $order_fenxiao_data[ 'two_rate' ] = $fenxiao_level[ 'two_rate' ];
                        $commission += $order_fenxiao_data[ 'two_commission' ] = $fenxiao_level[ 'two_rate' ] * $v[ 'real_goods_money' ] / 100;
                    } else {
                        $commission_rate += $order_fenxiao_data[ 'two_rate' ] = round($fenxiao_level[ 'two_money' ] * $v[ 'num' ] / $v[ 'real_goods_money' ], 2);
                        $commission += $order_fenxiao_data[ 'two_commission' ] = $fenxiao_level[ 'two_money' ] * $v[ 'num' ];
                    }
                }
                if (!empty($grand_parent_fenxiao_info) && $grand_parent_fenxiao_info[ 'status' ] == 1) {
                    if ($fenxiao_level[ 'three_rate' ] > 0) {
                        $commission_rate += $order_fenxiao_data[ 'three_rate' ] = $fenxiao_level[ 'three_rate' ];
                        $commission += $order_fenxiao_data[ 'three_commission' ] = $fenxiao_level[ 'three_rate' ] * $v[ 'real_goods_money' ] / 100;
                    } else {
                        $commission_rate += $order_fenxiao_data[ 'three_rate' ] = round($fenxiao_level[ 'three_money' ] * $v[ 'num' ] / $v[ 'real_goods_money' ], 2);
                        $commission += $order_fenxiao_data[ 'three_commission' ] = $fenxiao_level[ 'three_money' ] * $v[ 'num' ];
                    }
                }
            } else {
                // 默认规则
                $fenxiao_level = model('fenxiao_level')->getInfo([ [ 'level_id', '=', $fenxiao_info[ 'level_id' ] ] ]);
                if ($fenxiao_info[ 'status' ] == 1) {
                    if ($fenxiao_level[ 'one_rate' ] > 0) {
                        $commission_rate += $order_fenxiao_data[ 'one_rate' ] = $fenxiao_level[ 'one_rate' ];
                        $commission += $order_fenxiao_data[ 'one_commission' ] = $fenxiao_level[ 'one_rate' ] * $v[ 'real_goods_money' ] / 100;
                    } else {
                        $order_fenxiao_data[ 'one_rate' ] = 0;
                        $order_fenxiao_data[ 'one_commission' ] = 0;
                    }
                }
                if (!empty($parent_fenxiao_info) && $parent_fenxiao_info[ 'status' ] == 1) {
                    if ($fenxiao_level[ 'two_rate' ] > 0) {
                        $commission_rate += $order_fenxiao_data[ 'two_rate' ] = $fenxiao_level[ 'two_rate' ];
                        $commission += $order_fenxiao_data[ 'two_commission' ] = $fenxiao_level[ 'two_rate' ] * $v[ 'real_goods_money' ] / 100;
                    } else {
                        $order_fenxiao_data[ 'two_rate' ] = 0;
                        $order_fenxiao_data[ 'two_commission' ] = 0;
                    }
                }
                if (!empty($grand_parent_fenxiao_info) && $grand_parent_fenxiao_info[ 'status' ] == 1) {
                    if ($fenxiao_level[ 'three_rate' ] > 0) {
                        $commission_rate += $order_fenxiao_data[ 'three_rate' ] = $fenxiao_level[ 'three_rate' ];
                        $commission += $order_fenxiao_data[ 'three_commission' ] = $fenxiao_level[ 'three_rate' ] * $v[ 'real_goods_money' ] / 100;
                    } else {
                        $order_fenxiao_data[ 'three_rate' ] = 0;
                        $order_fenxiao_data[ 'three_commission' ] = 0;
                    }
                }
            }
            //启动分销
            $data = [
                'order_id' => $order[ 'order_id' ],
                'order_no' => $order[ 'order_no' ],
                'order_goods_id' => $v[ 'order_goods_id' ],
                'site_id' => $order[ 'site_id' ],
                'site_name' => $order[ 'site_name' ],
                'goods_id' => $v[ 'goods_id' ],
                'sku_id' => $v[ 'sku_id' ],
                'sku_name' => $v[ 'sku_name' ],
                'sku_image' => $v[ 'sku_image' ],
                'price' => $v[ 'price' ],
                'num' => $v[ 'num' ],
                'real_goods_money' => $v[ 'real_goods_money' ],
                'member_id' => $order[ 'member_id' ],
                'member_name' => $order[ 'name' ] ?? '',
                'member_mobile' => $order[ 'mobile' ] ?? '',
                'full_address' => $order[ 'full_address' ] ?? '' . $order[ 'address' ] ?? '',
                'commission' => $commission,
                'commission_rate' => $commission_rate,
                'one_fenxiao_id' => $fenxiao_info[ 'fenxiao_id' ],
                'one_rate' => empty($order_fenxiao_data[ 'one_rate' ]) ? 0 : $order_fenxiao_data[ 'one_rate' ],
                'one_commission' => empty($order_fenxiao_data[ 'one_commission' ]) ? 0 : $order_fenxiao_data[ 'one_commission' ],
                'one_fenxiao_name' => $fenxiao_info[ 'fenxiao_name' ],
                'two_fenxiao_id' => empty($parent_fenxiao_info) ? 0 : $parent_fenxiao_info[ 'fenxiao_id' ],
                'two_rate' => empty($order_fenxiao_data[ 'two_rate' ]) ? 0 : $order_fenxiao_data[ 'two_rate' ],
                'two_commission' => empty($order_fenxiao_data[ 'two_commission' ]) ? 0 : $order_fenxiao_data[ 'two_commission' ],
                'two_fenxiao_name' => empty($parent_fenxiao_info) ? '' : $parent_fenxiao_info[ 'fenxiao_name' ],
                'three_fenxiao_id' => empty($grand_parent_fenxiao_info) ? '' : $grand_parent_fenxiao_info[ 'fenxiao_id' ],
                'three_rate' => empty($order_fenxiao_data[ 'three_rate' ]) ? 0 : $order_fenxiao_data[ 'three_rate' ],
                'three_commission' => empty($order_fenxiao_data[ 'three_commission' ]) ? 0 : $order_fenxiao_data[ 'three_commission' ],
                'three_fenxiao_name' => empty($grand_parent_fenxiao_info) ? '' : $grand_parent_fenxiao_info[ 'fenxiao_name' ],
                'create_time' => time()
            ];
            model('fenxiao_order')->add($data);
        }
        // 分销商检测升级
        event('FenxiaoUpgrade', $member_info[ 'fenxiao_id' ]);
        return $this->success();
    }

    /**
     * 订单退款
     * @param $order_goods_id
     * @return array
     */
    public function refund($data)
    {
        $order_goods_info = $data['order_goods_info'];
        if ($order_goods_info[ 'refund_mode' ] == OrderRefundDict::refund) {
            $res = model('fenxiao_order')->update([ 'is_refund' => 1 ], [ [ 'order_goods_id', '=', $order_goods_info['order_goods_id'] ] ]);
            return $this->success($res);
        }
    }

    /**
     * 订单结算
     * @param $order_id
     * @return array
     */
    public function settlement($order_id)
    {
        //获取未退款的和未结算的分销订单
        $fenxiao_orders = model('fenxiao_order')->getList([ [ 'order_id', '=', $order_id ], [ 'is_settlement', '=', 0 ], [ 'is_refund', '=', 0 ] ], '*');
        //同时修改分销订单状态为已结算
        model('fenxiao_order')->startTrans();
        try {
            model('fenxiao_order')->update([ 'is_settlement' => 1 ], [ [ 'order_id', '=', $order_id ] ]);
            $commission = 0;
            $fenxiao_account = new FenxiaoAccount();
            $site_id = 0;
            foreach ($fenxiao_orders as $fenxiao_order) {
                $site_id = $fenxiao_order[ 'site_id' ];
                $commission += $fenxiao_order[ 'one_commission' ];
                $fenxiao_account->addAccount($fenxiao_order[ 'one_fenxiao_id' ], $fenxiao_order[ 'one_fenxiao_name' ], 'order', $fenxiao_order[ 'one_commission' ], $fenxiao_order[ 'fenxiao_order_id' ]);

                // 分销佣金发放通知
                ( new Message() )->sendMessage([
                    'keywords' => 'COMMISSION_GRANT',
                    'order_id' => $fenxiao_order[ 'fenxiao_order_id' ],
                    'site_id' => $fenxiao_order[ 'site_id' ],
                    'level' => 'one',
                ]);

                model('fenxiao')->setInc([ [ 'fenxiao_id', '=', $fenxiao_order[ 'one_fenxiao_id' ] ] ], 'total_commission', $fenxiao_order[ 'one_commission' ]);

                if ($fenxiao_order[ 'two_commission' ] > 0) {
                    $commission += $fenxiao_order[ 'two_commission' ];
                    $fenxiao_account->addAccount($fenxiao_order[ 'two_fenxiao_id' ], $fenxiao_order[ 'two_fenxiao_name' ], 'order', $fenxiao_order[ 'two_commission' ], $fenxiao_order[ 'fenxiao_order_id' ]);

                    // 分销佣金发放通知
                    ( new Message() )->sendMessage([
                        'keywords' => 'COMMISSION_GRANT',
                        'order_id' => $fenxiao_order[ 'fenxiao_order_id' ],
                        'site_id' => $fenxiao_order[ 'site_id' ],
                        'level' => 'two',
                    ]);

                    model('fenxiao')->setInc([ [ 'fenxiao_id', '=', $fenxiao_order[ 'two_fenxiao_id' ] ] ], 'total_commission', $fenxiao_order[ 'two_commission' ]);
                }
                if ($fenxiao_order[ 'three_commission' ] > 0) {
                    $commission += $fenxiao_order[ 'three_commission' ];
                    $fenxiao_account->addAccount($fenxiao_order[ 'three_fenxiao_id' ], $fenxiao_order[ 'three_fenxiao_name' ], 'order', $fenxiao_order[ 'three_commission' ], $fenxiao_order[ 'fenxiao_order_id' ]);

                    // 分销佣金发放通知
                    ( new Message() )->sendMessage([
                        'keywords' => 'COMMISSION_GRANT',
                        'order_id' => $fenxiao_order[ 'fenxiao_order_id' ],
                        'site_id' => $fenxiao_order[ 'site_id' ],
                        'level' => 'three',
                    ]);

                    model('fenxiao')->setInc([ [ 'fenxiao_id', '=', $fenxiao_order[ 'three_fenxiao_id' ] ] ], 'total_commission', $fenxiao_order[ 'three_commission' ]);
                }

            }
            $stat_model = new Stat();
            $stat_model->switchStat([ 'type' => 'fenxiao_order', 'data' => [ 'order_id' => $order_id, 'site_id' => $site_id ] ]);
            //增加订单佣金结算
            model('order')->setInc([ [ 'order_id', '=', $order_id ] ], 'commission', $commission);
            model('fenxiao_order')->commit();
            return $this->success();
        } catch ( Exception $e) {
            model('fenxiao_order')->rollback();
            return $this->error($e->getMessage());
        }
    }

    /**
     * 计算对应分销商的第一次订单数量&&金额
     * @param $order_id
     * @return array
     */
    public function calculateOrder($order_id)
    {
        $fenxiao_order_info = model('fenxiao_order')->getFirstData([ [ 'order_id', '=', $order_id ] ], 'one_fenxiao_id');
        if (!empty($fenxiao_order_info)) {
            $one_commission_sum = model('fenxiao_order')->getSum([ [ 'order_id', '=', $order_id ] ], 'one_commission');
            $one_fenxiao_total_order = model('fenxiao_order')->getSum([ [ 'order_id', '=', $order_id ] ], 'real_goods_money');
            model('fenxiao')->setInc([ [ 'fenxiao_id', '=', $fenxiao_order_info[ 'one_fenxiao_id' ] ] ], 'one_fenxiao_order_num');
            model('fenxiao')->setInc([ [ 'fenxiao_id', '=', $fenxiao_order_info[ 'one_fenxiao_id' ] ] ], 'one_fenxiao_order_money', $one_commission_sum);
            model('fenxiao')->setInc([ [ 'fenxiao_id', '=', $fenxiao_order_info[ 'one_fenxiao_id' ] ] ], 'one_fenxiao_total_order', $one_fenxiao_total_order);
            // 分销商检测升级
            event('FenxiaoUpgrade', $fenxiao_order_info[ 'one_fenxiao_id' ]);
        }
        return $this->success();
    }

    /**
     * 获取分销订单列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @return array
     */
    public function getFenxiaoOrderPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'fo.order_id DESC')
    {
        $field = '
        fo.fenxiao_order_id,fo.order_no,fo.site_id,fo.site_name,fo.sku_id,fo.sku_name,fo.sku_image,fo.price,fo.num,fo.real_goods_money,fo.member_name,
        fo.member_mobile,fo.one_fenxiao_name,fo.is_settlement,fo.commission,fo.is_refund,
        o.order_status_name,o.create_time,fo.one_fenxiao_id,fo.two_fenxiao_id,fo.three_fenxiao_id,fo.one_commission,fo.two_commission,fo.three_commission';

        $alias = 'fo';
        $join = [
            [
                'order o',
                'fo.order_id = o.order_id',
                'inner'
            ]
        ];
        $list = model('fenxiao_order')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        return $this->success($list);
    }

    /**
     * 查询订单信息
     * @param $condition
     * @param string $field
     * @return array
     */
    public function getFenxiaoOrderInfo($condition, $field = '*')
    {
        $fenxiao_order_info = model('fenxiao_order')->getInfo($condition, $field);
        return $this->success($fenxiao_order_info);
    }

    /**
     * 查询订单信息
     * @param $condition
     * @param string $field
     * @return array
     */
    public function getFenxiaoOrderInfoNew($condition, $field = '*')
    {
        $alias = 'fo';
        $join = [
            [
                'order o',
                'o.order_id = fo.order_id',
                'left'
            ]
        ];
        $fenxiao_order_info = model('fenxiao_order')->getInfo($condition, $field, $alias, $join);
        return $this->success($fenxiao_order_info);
    }

    /**
     * 查询分销订单列表(管理端调用)
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @return array
     */
    public function getFenxiaoOrderPage($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'order_id DESC')
    {
        $field = 'order_id,order_no,site_name,member_name,create_time,is_settlement,fenxiao_order_id';
        $list = model('fenxiao_order')->pageList($condition, $field, $order, $page, $page_size, 'fo', [], 'order_id');
        if (!empty($list[ 'list' ])) {
            $order_id_arr = [];
            foreach ($list['list'] as $k => $v)
            {
                $order_id_arr[] = $v['order_id'];
            }
            $order_ids = implode(',', $order_id_arr);
            $order_list = model('order')->getList([ [ 'order_id', 'in', $order_ids ] ], 'order_id,name,full_address,mobile,order_status_name');
            $order_goods_list = model('fenxiao_order')->getList([ [ 'order_id', 'in', $order_ids ] ]);
            foreach ($list[ 'list' ] as $k => $item) {
                foreach ($order_list as $k_order => $v_order)
                {
                    if($item['order_id'] == $v_order['order_id'])
                    {
                        $list[ 'list' ][ $k ] = array_merge($list[ 'list' ][ $k ], $v_order);
                    }
                }
                $list[ 'list' ][ $k ][ 'order_goods' ] = [];
                foreach ($order_goods_list as $k_order_goods => $v_order_goods)
                {
                    if($item['order_id'] == $v_order_goods['order_id'])
                    {
                        $list[ 'list' ][ $k ][ 'order_goods' ][] = $v_order_goods;
                    }
                }
            }
        }
        return $this->success($list);
    }

    /**
     * 获取分销订单详情
     * @param $condition
     * @param string $field
     * @return array
     */
    public function getFenxiaoOrderDetail($condition, $field = '*')
    {
        $order_info = model('order')->getInfo($condition, $field);
        if (!empty($order_info)) {
            $order_goods = model('fenxiao_order')->getList([ [ 'order_id', '=', $order_info[ 'order_id' ] ] ]);
            $member_info = model('member')->getInfo([ 'member_id' => $order_info[ 'member_id' ] ], 'nickname');
            $order_info[ 'order_goods' ] = $order_goods;
            $order_info[ 'nickname' ] = $member_info[ 'nickname' ];
        }
        return $this->success($order_info);
    }

    /**
     * 获取分销订单数量
     * @param $condition
     * @return array
     */
    public function getFenxiaoOrderCount($condition)
    {
        $count = model('fenxiao_order')->getCount($condition);
        return $this->success($count);
    }

    /**
     * 获取分销订单总和
     * @param array $condition
     * @param string $field
     * @param string $alias
     * @param null $join
     * @return array
     */
    public function getFenxiaoOrderSum(array $condition, $field = '', $alias = 'a', $join = null)
    {
        $data = model('fenxiao_order')->getSum($condition, $field, $alias, $join);
        return $this->success($data);
    }

    /**
     * 查询订单数据并导出
     * @param $condition
     * @param int $site_id
     */
    public function orderExport($condition, $site_id = 0)
    {
        $field = [
            'order_no' => '订单编号',
            'site_name' => '站点名称',
            'sku_name' => '商品名称',
            'member_name' => '购买人',
            'member_mobile' => '购买人电话',
            'commission' => '总佣金',
            'one_rate' => '一级分销比例',
            'one_commission' => '一级分销佣金',
            'one_fenxiao_name' => '一级分销商名',
            'two_rate' => '二级分销比例',
            'two_commission' => '二级分销佣金',
            'two_fenxiao_name' => '二级分销商名',
            'three_rate' => '三级分销比例',
            'three_commission' => '三级分销佣金',
            'three_fenxiao_name' => '三级分销商名',
            'is_settlement' => '是否结算',
            'is_refund' => '是否退款',
            'province_name' => '省',
            'city_name' => '市',
            'district_name' => '县',
            'full_address' => '详细地址',
            'order_status_name' => '订单状态'
        ];

        $fields = 'fo.*,o.address,o.full_address,o.order_status_name';
        $alias = 'fo';
        $join = [
            [
                'order o',
                'o.order_id = fo.order_id',
                'left'
            ]
        ];

        $list = model('fenxiao_order')->getList($condition, $fields, '', $alias, $join);
        $head_html = [];
        $line_html = [];
        foreach ($field as $k => $v) {
            $head_html[] = $v;
            $line_html[] = $k;
        }

        $temp_line = implode(',', $head_html) . "\n";
        $html = str_replace("\n", '', $temp_line) . "\n";
        $line = implode(',', $line_html);
        $html .= str_replace("\n", '', $line) . "\n";

        $temp = [];
        foreach ($line_html as $temp_line_k => $temp_line_v) {
            $temp[] = "{\$$temp_line_v}";
        }

        $tempLine = implode(',', $temp) . "\n";

        foreach ($list as $item) {
            $new_line_value = $tempLine;
            $address_arr = explode('-', $item[ 'full_address' ]);
            $item[ 'province_name' ] = !empty($address_arr[ 0 ]) ? $address_arr[ 0 ] : '';
            $item[ 'city_name' ] = !empty($address_arr[ 1 ]) ? $address_arr[ 1 ] : '';
            $item[ 'district_name' ] = !empty($address_arr[ 2 ]) ? $address_arr[ 2 ] : '';
            $item[ 'is_settlement' ] = $item[ 'is_settlement' ] == 0 ? '否' : '是';
            $item[ 'is_refund' ] = $item[ 'is_refund' ] == 0 ? '否' : '是';

            foreach ($item as $key => $val) {
                if ($key == 'full_address') {
                    $address = $item[ 'address' ] ?? '';
                    $val = $val . $address;
                }

                //CSV比较简单，记得转义 逗号就好
                $values = str_replace(',', '\\', $val . "\t");
                $values = str_replace("\n", '', $values);
                $new_line_value = str_replace("{\$$key}", $values, $new_line_value);

            }

            $html .= $new_line_value;
        }

        $filename = date('YmdHis') . '.csv'; //设置文件名
        header('Content-type:text/csv');
        header('Content-Disposition:attachment;filename=' . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        exit(mb_convert_encoding($html, 'GBK', 'UTF-8'));
    }

    /**
     * 给csv写入新的数据
     * @param $item_list
     * @param $field_key
     * @param $temp_line
     * @param $fp
     */
    public function itemExport($item_list, $field_key, $temp_line, $fp)
    {
        $item_list = $item_list->toArray();
        foreach ($item_list as $k => $item_v) {
            $new_line_value = $temp_line;
            //省市县
            $address_arr = explode('-', $item_v[ 'full_address' ]);

            $item_v[ 'province_name' ] = $address_arr[ 0 ] ?: '';
            $item_v[ 'city_name' ] = $address_arr[ 1 ] ?: '';
            $item_v[ 'district_name' ] = $address_arr[ 2 ] ?: '';

            foreach ($item_v as $key => $value) {

                if ($key == 'full_address') {
                    $address = $item_v[ 'address' ] ?? '';
                    $value = $value . $address;
                }
                //CSV比较简单，记得转义 逗号就好
                $values = str_replace(',', '\\', $value . "\t");
                $values = str_replace("\n", '', $values);
                $new_line_value = str_replace("{\$$key}", $values, $new_line_value);
            }
            //写入第一行表头
            fwrite($fp, $new_line_value);
            dump($new_line_value);
            exit;
            //销毁变量, 防止内存溢出
            unset($new_line_value);
        }
    }
}
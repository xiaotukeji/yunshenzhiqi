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

use addon\coupon\model\CouponType;
use app\model\BaseModel;
use app\model\goods\Goods;
use app\model\system\Config as ConfigModel;
use app\model\system\Cron;
use think\facade\Cache;
use think\facade\Db;

/**
 * 拼团返现活动
 */
class Pinfan extends BaseModel
{
    /**
     * 添加拼团返现
     * @param $pintuan_data
     * @param $goods
     * @param $sku_list
     * @return array
     */
    public function addPinfan($pintuan_data, $goods, $sku_list)
    {
        if (empty($goods[ 'sku_ids' ])) {
            return $this->error('', '该活动至少需要一个商品参与');
        }
        $pintuan_data[ 'create_time' ] = time();
        if ($pintuan_data[ 'pintuan_time' ] == 0) {
            return $this->error('', "拼团返现有效期时长不能为0");
        }

        //查询该商品是否存在拼团返现
        $pintuan_info = model('promotion_pinfan_goods')->getInfo(
            [
                [ 'ppg.site_id', '=', $pintuan_data[ 'site_id' ] ],
                [ 'pp.status', 'in', '0,1' ],
                [ 'ppg.goods_id', 'in', $goods[ 'goods_ids' ] ],
                [ '', 'exp', Db::raw('not ( (`start_time` > ' . $pintuan_data[ 'end_time' ] . ' and `start_time` > ' . $pintuan_data[ 'start_time' ] . ' )  or (`end_time` < ' . $pintuan_data[ 'start_time' ] . ' and `end_time` < ' . $pintuan_data[ 'end_time' ] . '))') ]
            ], 'ppg.id', 'ppg', [ [ 'promotion_pinfan pp', 'pp.pintuan_id = ppg.pintuan_id', 'left' ] ]
        );
        if (!empty($pintuan_info)) {
            return $this->error('', "当前商品在当前时间段内已经存在拼团返现活动");
        }

        if (time() > $pintuan_data[ 'end_time' ]) {
            return $this->error('', '当前时间不能大于结束时间');
        }
        if ($pintuan_data[ 'start_time' ] <= time()) {
            $pintuan_data[ 'status' ] = 1;
        } else {
            $pintuan_data[ 'status' ] = 0;
        }
        model("promotion_pinfan")->startTrans();
        try {

            foreach ($goods[ 'goods_ids' ] as $goods_id) {

                //添加拼团返现活动
                $pintuan_data[ 'goods_id' ] = $goods_id;

                $pintuan_id = model("promotion_pinfan")->add($pintuan_data);

                $sku_list_data = [];
                foreach ($sku_list as $k => $sku) {
                    if ($sku[ 'goods_id' ] == $goods_id) {

                        $promotion_price = $sku[ 'pintuan_price' ];
                        if (isset($pintuan_data[ 'is_promotion' ]) && $pintuan_data[ 'is_promotion' ] == 1) {
                            $promotion_price = $sku[ 'promotion_price' ];
                        }
                        $sku_list_data[] = [
                            'site_id' => $pintuan_data[ 'site_id' ],
                            'pintuan_id' => $pintuan_id,
                            'goods_id' => $pintuan_data[ 'goods_id' ],
                            'sku_id' => $sku[ 'sku_id' ],
                            'pintuan_price' => $sku[ 'pintuan_price' ],
                            'promotion_price' => $promotion_price
                        ];
                    }
                }
                array_multisort(array_column($sku_list_data, 'pintuan_price'), SORT_ASC, $sku_list_data);
                model('promotion_pinfan_goods')->addList($sku_list_data);

                model('promotion_pinfan')->update([ 'pintuan_price' => $sku_list_data[ 0 ][ 'pintuan_price' ] ], [ [ 'pintuan_id', '=', $pintuan_id ] ]);

                $cron = new Cron();
                if ($pintuan_data[ 'status' ] == 1) {
                    $goods = new Goods();
                    $goods->modifyPromotionAddon($goods_id, [ 'pinfan' => $pintuan_id ]);
                    $cron->addCron(1, 0, "拼团返现活动关闭", "ClosePinfan", $pintuan_data[ 'end_time' ], $pintuan_id);
                } else {
                    $cron->addCron(1, 0, "拼团返现活动开启", "OpenPinfan", $pintuan_data[ 'start_time' ], $pintuan_id);
                    $cron->addCron(1, 0, "拼团返现活动关闭", "ClosePinfan", $pintuan_data[ 'end_time' ], $pintuan_id);
                }
            }

            model('promotion_fan')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('promotion_pinfan')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 编辑拼团返现
     * @param $pintuan_data
     * @param $goods
     * @param $sku_list
     * @return array
     */
    public function editPinfan($pintuan_data, $goods, $sku_list)
    {
        if (empty($goods[ 'sku_ids' ])) {
            return $this->error('', '该活动至少需要一个商品参与');
        }
        //查询该商品是否存在拼团返现
        $pintuan_info = model('promotion_pinfan_goods')->getInfo(
            [
                [ 'ppg.site_id', '=', $pintuan_data[ 'site_id' ] ],
                [ 'pp.status', 'in', '0,1' ],
                [ 'pp.pintuan_id', '<>', $pintuan_data[ 'pintuan_id' ] ],
                [ 'ppg.sku_id', 'in', $goods[ 'sku_ids' ] ],
                [ '', 'exp', Db::raw('not ( (`start_time` > ' . $pintuan_data[ 'end_time' ] . ' and `start_time` > ' . $pintuan_data[ 'start_time' ] . ' )  or (`end_time` < ' . $pintuan_data[ 'start_time' ] . ' and `end_time` < ' . $pintuan_data[ 'end_time' ] . '))') ]
            ], 'ppg.id', 'ppg', [ [ 'promotion_pinfan pp', 'pp.pintuan_id = ppg.pintuan_id', 'left' ] ]
        );
        if (!empty($pintuan_info)) {
            return $this->error('', "当前商品在当前时间段内已经存在拼团返现返现活动");
        }

        $pintuan_count = model("promotion_pinfan")->getCount([ [ 'pintuan_id', '=', $pintuan_data[ 'pintuan_id' ] ], [ 'site_id', '=', $pintuan_data[ 'site_id' ] ] ]);
        if ($pintuan_count == 0) {
            return $this->error('', '该拼团返现返现活动不存在');
        }

        $cron = new Cron();
        if (time() > $pintuan_data[ 'end_time' ]) {
            return $this->error('', '当前时间不能大于结束时间');
        }
        if ($pintuan_data[ 'start_time' ] <= time()) {
            $pintuan_data[ 'status' ] = 1;
        } else {
            $pintuan_data[ 'status' ] = 0;
        }

        $pintuan_data[ 'modify_time' ] = time();

        model('promotion_pinfan')->startTrans();
        try {
            $sku_list_data = [];
            foreach ($sku_list as $k => $sku) {
                $count = model('promotion_pinfan_goods')->getCount([ [ 'sku_id', '=', $sku[ 'sku_id' ] ], [ 'pintuan_id', '=', $pintuan_data[ 'pintuan_id' ] ] ]);
                $is_delete = $sku[ 'is_delete' ];
                unset($sku[ 'is_delete' ]);
                if ($is_delete == 2) {//是否参与  1参与  2不参与
                    if ($count) {
                        model('promotion_pinfan_goods')->delete([ [ 'sku_id', '=', $sku[ 'sku_id' ] ], [ 'pintuan_id', '=', $pintuan_data[ 'pintuan_id' ] ] ]);
                    }
                } else {

                    $promotion_price = $sku[ 'pintuan_price' ];
                    if (isset($pintuan_data[ 'is_promotion' ]) && $pintuan_data[ 'is_promotion' ] == 1) {
                        $promotion_price = $sku[ 'promotion_price' ];
                    }
                    $sku_data = [
                        'site_id' => $pintuan_data[ 'site_id' ],
                        'pintuan_id' => $pintuan_data[ 'pintuan_id' ],
                        'goods_id' => $sku[ 'goods_id' ],
                        'sku_id' => $sku[ 'sku_id' ],
                        'pintuan_price' => $sku[ 'pintuan_price' ],
                        'promotion_price' => $promotion_price
                    ];
                    $sku_list_data[] = $sku_data;
                    if ($count > 0) {
                        model('promotion_pinfan_goods')->update($sku_data, [ [ 'sku_id', '=', $sku[ 'sku_id' ] ], [ 'pintuan_id', '=', $pintuan_data[ 'pintuan_id' ] ] ]);
                    } else {
                        model('promotion_pinfan_goods')->add($sku_data);
                    }
                }
            }

            array_multisort(array_column($sku_list_data, 'pintuan_price'), SORT_ASC, $sku_list_data);
            model("promotion_pinfan")->update(
                array_merge($pintuan_data, [ 'pintuan_price' => $sku_list_data[ 0 ][ 'pintuan_price' ] ]),
                [ [ 'pintuan_id', '=', $pintuan_data[ 'pintuan_id' ] ] ]
            );

            if ($pintuan_data[ 'start_time' ] <= time()) {
                $goods = new Goods();
                $goods->modifyPromotionAddon($pintuan_data[ 'goods_id' ], [ 'pinfan' => $pintuan_data[ 'pintuan_id' ] ]);
                //活动商品启动
                $this->cronOpenPinfan($pintuan_data[ 'pintuan_id' ]);
                $cron->deleteCron([ [ 'event', '=', 'OpenPinfan' ], [ 'relate_id', '=', $pintuan_data[ 'pintuan_id' ] ] ]);
                $cron->deleteCron([ [ 'event', '=', 'ClosePinfan' ], [ 'relate_id', '=', $pintuan_data[ 'pintuan_id' ] ] ]);

                $cron->addCron(1, 0, "拼团返现活动关闭", "ClosePintuan", $pintuan_data[ 'end_time' ], $pintuan_data[ 'pintuan_id' ]);
            } else {
                $cron->deleteCron([ [ 'event', '=', 'OpenPinfan' ], [ 'relate_id', '=', $pintuan_data[ 'pintuan_id' ] ] ]);
                $cron->deleteCron([ [ 'event', '=', 'ClosePinfan' ], [ 'relate_id', '=', $pintuan_data[ 'pintuan_id' ] ] ]);

                $cron->addCron(1, 0, "拼团返现活动开启", "OpenPinfan", $pintuan_data[ 'start_time' ], $pintuan_data[ 'pintuan_id' ]);
                $cron->addCron(1, 0, "拼团返现活动关闭", "ClosePinfan", $pintuan_data[ 'end_time' ], $pintuan_data[ 'pintuan_id' ]);
            }

            model('promotion_pinfan')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('promotion_pinfan')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 增加拼团返现组人数及购买人数
     * @param array $data
     * @param array $condition
     * @return array
     */
    public function editPinfanNum($data = [], $condition = [])
    {
        $res = model('promotion_pinfan')->update($data, $condition);
        return $this->success($res);
    }

    /**
     * 删除拼团返现
     * @param unknown $pintuan_id
     * @param unknown $site_id
     */
    public function deletePinfan($pintuan_id, $site_id)
    {
        $pintuan_info = model("promotion_pinfan")->getInfo([ [ 'pintuan_id', '=', $pintuan_id ], [ 'site_id', '=', $site_id ] ]);
        if ($pintuan_info[ 'status' ] == 1) {
            return $this->error('', "当前活动再进行中，不能删除");
        }
        $res = model("promotion_pinfan")->delete([ [ 'pintuan_id', '=', $pintuan_id ], [ 'site_id', '=', $site_id ] ]);
        if ($res) {
            //删除拼团返现商品
            model("promotion_pinfan_goods")->delete([ [ 'pintuan_id', '=', $pintuan_id ] ]);
            //删除拼团返现组
            model('promotion_pinfan_group')->delete([ [ 'pintuan_id', '=', $pintuan_id ] ]);
            $goods = new Goods();
            $goods->modifyPromotionAddon($pintuan_info[ 'goods_id' ], [ 'pintuan' => $pintuan_id ], true);
            $cron = new Cron();
            $cron->deleteCron([ [ 'event', '=', 'OpenPinfan' ], [ 'relate_id', '=', $pintuan_id ] ]);
            $cron->deleteCron([ [ 'event', '=', 'ClosePinfan' ], [ 'relate_id', '=', $pintuan_id ] ]);
        }
        return $this->success($res);
    }

    /**
     * 拼团返现失效
     * @param unknown $pintuan_id
     * @param unknown $site_id
     */
    public function invalidPinfan($pintuan_id, $site_id)
    {
        model('promotion_pinfan')->startTrans();
        try {
            $pintuan_info = model("promotion_pinfan")->getInfo([ [ 'pintuan_id', '=', $pintuan_id ], [ 'site_id', '=', $site_id ] ]);

            $res = model("promotion_pinfan")->update(
                [ 'status' => 3, 'modify_time' => time() ],
                [ [ 'pintuan_id', '=', $pintuan_id ], [ 'site_id', '=', $site_id ] ]
            );

            if ($pintuan_info[ 'group_num' ] > 0) {//有人拼团返现
                //查询所有拼团返现组
                $group_model = new PinfanGroup();
                //$group_info = $group_model->getPintuanGroupList([ [ 'pintuan_id', '=', $pintuan_id ] ], 'group_id');
                $group_list = model("promotion_pinfan_group")->pageList([ [ 'pintuan_id', '=', $pintuan_id, 'status', '<>', 1 ] ], 'group_id', "", 1, 50);
                if ((int) $group_list[ 'page_count' ] > 1) {
                    //新增事件
                    $cron = new Cron();
                    $cron->addCron(1, 0, "拼团返现活动关闭", "ClosePinfan", $pintuan_info[ 'end_time' ], $pintuan_id);
                }
                $group = $group_list[ 'list' ];

                if (!empty($group)) {
                    foreach ($group as $v) {

                        $result = $group_model->cronClosePinfanGroup($v[ 'group_id' ]);
                        if ($result[ 'code' ] < 0) {
                            model('promotion_pinfan')->rollback();
                            return $result;
                        }
                    }
                }
            }

            $goods = new Goods();
            $goods->modifyPromotionAddon($pintuan_info[ 'goods_id' ], [ 'pinfan' => $pintuan_id ], true);

            model('promotion_pinfan')->commit();
            return $this->success($res);
        } catch (\Exception $e) {
            model('promotion_pinfan')->rollback();
            return $this->error($e->getMessage());
        }

    }

    /**
     * 获取拼团返现信息
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getPinfanInfo($condition = [], $field = '*')
    {
        $pintuan_info = model("promotion_pinfan")->getInfo($condition, $field);
        return $this->success($pintuan_info);
    }

    /**
     * 获取拼团返现详细信息
     * @param $pintuan_id
     * @param $site_id
     * @return array
     */
    public function getPinfanDetail($pintuan_id, $site_id)
    {
        //拼团返现信息
        $alias = 'p';
        $join = [
            [
                'goods g',
                'g.goods_id = p.goods_id',
                'inner'
            ]
        ];
        $pintuan_info = model("promotion_pinfan")->getInfo(
            [
                [ 'p.pintuan_id', '=', $pintuan_id ], [ 'p.site_id', '=', $site_id ],
                [ 'g.goods_state', '=', 1 ], [ 'g.is_delete', '=', 0 ]
            ], 'p.*', $alias, $join
        );
        if (!empty($pintuan_info)) {
            //商品sku信息
            $goods_list = model('goods_sku')->getList(
                [ [ 'goods_id', '=', $pintuan_info[ 'goods_id' ] ] ],
                'goods_id,sku_id,sku_name,price,sku_images,stock,sku_image'
            );
            foreach ($goods_list as $k => $v) {
                $v[ 'stock' ] = numberFormat($v[ 'stock' ]);

                $pintuan_goods = model('promotion_pinfan_goods')->getInfo(
                    [ [ 'pintuan_id', '=', $pintuan_id ], [ 'sku_id', '=', $v[ 'sku_id' ] ] ],
                    'pintuan_price,promotion_price'
                );
                if (empty($pintuan_goods)) {
                    $pintuan_goods = [
                        'pintuan_price' => 0,
                        'promotion_price' => 0
                    ];
                }
                $goods_list[ $k ] = array_merge($v, $pintuan_goods);
            }
            array_multisort(array_column($goods_list, 'pintuan_price'), SORT_DESC, $goods_list);
            $pintuan_info[ 'sku_list' ] = $goods_list;
        }
        $coupon_list = [];
        if ($pintuan_info[ 'reward_type' ] == 3 && $pintuan_info[ 'reward_type_num' ] != '') {
            $coupon = new CouponType();
            $condition = [
                [ 'site_id', '=', $site_id ],
                [ 'status', '=', 1 ],
                [ 'coupon_type_id', 'in', $pintuan_info[ 'reward_type_num' ] ],
                [ '', "exp", Db::raw("(lead_count < count && count != -1) OR count = -1") ]
            ];
            $coupon_list = $coupon->getCouponTypeList($condition)[ 'data' ];
        }
        $pintuan_info[ 'coupon_list' ] = $coupon_list;
        return $this->success($pintuan_info);
    }

    /**
     * 获取拼团返现详细信息
     * @param $pintuan_id
     * @param $site_id
     * @return array
     */
    public function getPinfanJoinGoodsList($pintuan_id, $site_id)
    {
        //拼团返现信息
        $alias = 'p';
        $join = [
            [ 'goods g', 'g.goods_id = p.goods_id', 'inner' ]
        ];
        $pintuan_info = model("promotion_pinfan")->getInfo(
            [
                [ 'p.pintuan_id', '=', $pintuan_id ], [ 'p.site_id', '=', $site_id ],
                [ 'g.goods_state', '=', 1 ], [ 'g.is_delete', '=', 0 ]
            ], 'p.*', $alias, $join
        );
        if (!empty($pintuan_info)) {

            $goods_list = model('promotion_pinfan_goods')->getList(
                [ [ 'ppg.pintuan_id', '=', $pintuan_info[ 'pintuan_id' ] ] ],
                'ppg.pintuan_price,ppg.promotion_price,sku.sku_id,sku.sku_name,sku.price,sku.sku_image,sku.stock',
                '', 'ppg', [ [ 'goods_sku sku', 'sku.sku_id = ppg.sku_id', 'inner' ] ]
            );
            foreach ($goods_list as $k => $v) {
                $goods_list[ $k ][ 'stock' ] = numberFormat($goods_list[ $k ][ 'stock' ]);
            }

            $pintuan_info[ 'sku_list' ] = $goods_list;

            $coupon_list = [];
            if ($pintuan_info[ 'reward_type' ] == 3 && $pintuan_info[ 'reward_type_num' ] != '') {
                $coupon = new CouponType();
                $condition = [
                    [ 'site_id', '=', $site_id ],
                    [ 'status', '=', 1 ],
                    [ 'coupon_type_id', 'in', $pintuan_info[ 'reward_type_num' ] ],
                    [ '', "exp", Db::raw("(lead_count < count && count != -1) OR count = -1") ]
                ];
                $coupon_list = $coupon->getCouponTypeList($condition)[ 'data' ];
            }
            $pintuan_info[ 'coupon_list' ] = $coupon_list;
        }
        return $this->success($pintuan_info);
    }

    /**
     * 拼团返现商品详情
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getPinfanGoodsDetail($condition = [], $field = '')
    {
        $alias = 'ppg';

        if (empty($field)) {
            $field = 'pp.order_num,ppg.id,ppg.pintuan_id,ppg.goods_id,ppg.sku_id,ppg.pintuan_price,ppg.promotion_price,pp.chengtuan_num,pp.reward_type,pp.reward_type_num,pp.pintuan_name,pp.pintuan_num,pp.start_time,pp.end_time,pp.buy_num,pp.is_single_buy,pp.is_promotion,pp.group_num,pp.order_num,sku.site_id,sku.sku_name,sku.sku_spec_format,sku.price,sku.promotion_type,sku.stock,sku.click_num,(g.sale_num + g.virtual_sale) as sale_num,sku.collect_num,sku.sku_image,sku.sku_images,sku.site_id,sku.goods_content,sku.goods_state,sku.is_virtual,sku.is_free_shipping,sku.goods_spec_format,sku.goods_attr_format,sku.introduction,sku.unit,sku.video_url,g.evaluate,sku.goods_service_ids,sku.support_trade_type,g.goods_image,g.goods_stock,g.goods_name,sku.qr_id,g.stock_show,g.sale_show,g.label_name,pp.remark';
        }
        $join = [
            [ 'goods_sku sku', 'ppg.sku_id = sku.sku_id', 'inner' ],
            [ 'goods g', 'g.goods_id = sku.goods_id', 'inner' ],
            [ 'promotion_pinfan pp', 'ppg.pintuan_id = pp.pintuan_id', 'inner' ],
        ];

        $pintuan_goods_info = model('promotion_pinfan_goods')->getInfo($condition, $field, $alias, $join);
        if (!empty($pintuan_goods_info)) {
            if (isset($pintuan_goods_info[ 'goods_stock' ])) {
                $pintuan_goods_info[ 'goods_stock' ] = numberFormat($pintuan_goods_info[ 'goods_stock' ]);
            }
            if (isset($pintuan_goods_info[ 'sale_num' ])) {
                $pintuan_goods_info[ 'sale_num' ] = numberFormat($pintuan_goods_info[ 'sale_num' ]);
            }

            if (isset($pintuan_goods_info[ 'virtual_sale' ])) {
                $pintuan_goods_info[ 'virtual_sale' ] = numberFormat($pintuan_goods_info[ 'virtual_sale' ]);
            }
            if (isset($pintuan_goods_info[ 'stock' ])) {
                $pintuan_goods_info[ 'stock' ] = numberFormat($pintuan_goods_info[ 'stock' ]);
            }
            if (isset($pintuan_goods_info[ 'real_stock' ])) {
                $pintuan_goods_info[ 'real_stock' ] = numberFormat($pintuan_goods_info[ 'real_stock' ]);
            }
        }
        return $this->success($pintuan_goods_info);
    }

    /**
     * 拼团返现商品详情
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getPinfanGoodsSkuList($condition = [])
    {
        $alias = 'ppg';
        $field = 'pp.order_num,ppg.id,ppg.pintuan_id,ppg.goods_id,ppg.sku_id,ppg.pintuan_price,ppg.promotion_price
        ,pp.chengtuan_num,pp.reward_type,pp.reward_type_num,pp.pintuan_name,pp.pintuan_num,pp.start_time,pp.end_time,pp.buy_num
        ,pp.is_single_buy,pp.is_promotion,pp.group_num,pp.order_num
        ,sku.sku_name,sku.sku_spec_format,sku.price
        ,sku.stock,sku.sku_image,sku.sku_images,sku.goods_spec_format,g.goods_image';
        $join = [
            [ 'goods_sku sku', 'ppg.sku_id = sku.sku_id', 'inner' ],
            [ 'goods g', 'g.goods_id = sku.goods_id', 'inner' ],
            [ 'promotion_pinfan pp', 'ppg.pintuan_id = pp.pintuan_id', 'inner' ],
        ];
        $list = model('promotion_pinfan_goods')->getList($condition, $field, 'ppg.id asc', $alias, $join);
        foreach ($list as $k => $v) {
            $list[ $k ][ 'stock' ] = numberFormat($list[ $k ][ 'stock' ]);
        }
        return $this->success($list);
    }

    /**
     * 获取拼团返现列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $limit
     */
    public function getPinfanList($condition = [], $field = '', $order = '', $limit = null)
    {
        $alias = 'pp';

        if (empty($field)) {
            $field = 'pp.order_num,pp.pintuan_id,pp.site_id,pp.pintuan_name,pp.is_virtual_goods,pp.pintuan_num,pp.chengtuan_num,pp.reward_type,pp.reward_type_num,pp.pintuan_price,pp.pintuan_time,pp.is_single_buy,pp.is_recommend,pp.group_num,pp.order_num,g.price,g.goods_id,g.goods_name,g.goods_image,(g.sale_num + g.virtual_sale) as sale_num,g.unit,g.goods_stock,g.recommend_way';
        }
        $join = [
            [ 'goods g', 'pp.goods_id = g.goods_id', 'inner' ]
        ];
        $list = model('promotion_pinfan')->getList($condition, $field, $order, $alias, $join, '', $limit);
        foreach ($list as $k => $v) {
            if (isset($v[ 'goods_stock' ])) {
                $list[ $k ][ 'goods_stock' ] = numberFormat($list[ $k ][ 'goods_stock' ]);
            }
            if (isset($v[ 'sale_num' ])) {
                $list[ $k ][ 'sale_num' ] = numberFormat($list[ $k ][ 'sale_num' ]);
            }
        }
        return $this->success($list);
    }

    /**
     * 获取拼团返现分页列表
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     */
    public function getPinfanPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '')
    {
        $field = 'p.*,g.goods_name,g.goods_image,g.price';
        $alias = 'p';
        $join = [
            [
                'goods g',
                'p.goods_id = g.goods_id',
                'inner'
            ]
        ];
        $list = model('promotion_pinfan')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        return $this->success($list);
    }

    /**
     * 获取拼团返现商品列表
     * @param $bargain_id
     * @param $site_id
     * @return array
     */
    public function getPinfanGoodsList($pintuan_id, $site_id)
    {
        $field = 'pbg.*,sku.sku_name,sku.price,sku.sku_image,sku.stock';
        $alias = 'pbg';
        $join = [
            [
                'goods g',
                'g.goods_id = pbg.goods_id',
                'inner'
            ],
            [
                'goods_sku sku',
                'sku.sku_id = pbg.sku_id',
                'inner'
            ]
        ];
        $condition = [
            [ 'pbg.pintuan_id', '=', $pintuan_id ], [ 'pbg.site_id', '=', $site_id ],
            [ 'g.is_delete', '=', 0 ], [ 'g.goods_state', '=', 1 ]
        ];

        $list = model('promotion_pinfan_goods')->getList($condition, $field, '', $alias, $join);
        foreach ($list as $k => $v) {
            $list[ $k ][ 'stock' ] = numberFormat($list[ $k ][ 'stock' ]);
        }
        return $this->success($list);
    }

    /**
     * 获取拼团商品数量
     * @param array $where
     * @param string $field
     * @param string $alias
     * @param null $join
     * @return array
     */
    public function getPinfanGoodsCount($where = [], $field = '*', $alias = 'a', $join = null, $group = null)
    {
        $count = model('promotion_pinfan_goods')->getCount($where, $field, $alias, $join, $group);
        return $this->success($count);
    }

    /**
     * 获取拼团返现商品分页列表
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     */
    public function getPinfanGoodsPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '')
    {
        $alias = 'pp';

        if (empty($field)) {
            $field = 'pp.order_num,pp.pintuan_id,pp.site_id,pp.pintuan_name,pp.is_virtual_goods,pp.pintuan_num,pp.chengtuan_num,pp.reward_type,pp.reward_type_num,pp.pintuan_price,pp.pintuan_time,pp.is_single_buy,pp.is_recommend,pp.group_num,pp.order_num,g.price,g.goods_id,g.goods_name,g.goods_image,(g.sale_num + g.virtual_sale) as sale_num,g.unit,g.goods_stock,g.recommend_way';
        }
        $join = [
            [ 'goods g', 'pp.goods_id = g.goods_id', 'inner' ]
        ];
        $res = model('promotion_pinfan')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        foreach ($res[ 'list' ] as $k => $v) {
            if (isset($v[ 'goods_stock' ])) {
                $res[ 'list' ][ $k ][ 'goods_stock' ] = numberFormat($res[ 'list' ][ $k ][ 'goods_stock' ]);
            }
            if (isset($v[ 'sale_num' ])) {
                $res[ 'list' ][ $k ][ 'sale_num' ] = numberFormat($res[ 'list' ][ $k ][ 'sale_num' ]);
            }
        }
        return $this->success($res);
    }

    /**
     * 开启拼团返现活动
     * @param $pintuan_id
     * @return array|\multitype
     */
    public function cronOpenPinfan($pintuan_id)
    {
        $pintuan_info = model('promotion_pinfan')->getInfo([ [ 'pintuan_id', '=', $pintuan_id ] ], 'start_time,status,goods_id');
        if (!empty($pintuan_info)) {
            $goods = new Goods();
            $goods->modifyPromotionAddon($pintuan_info[ 'goods_id' ], [ 'pinfan' => $pintuan_id ]);
            if ($pintuan_info[ 'start_time' ] <= time() && $pintuan_info[ 'status' ] == 0) {
                $res = model('promotion_pinfan')->update([ 'status' => 1 ], [ [ 'pintuan_id', '=', $pintuan_id ] ]);
                return $this->success($res);
            } else {
                return $this->error("", "拼团返现活动已开启或者关闭");
            }
        } else {
            return $this->error("", "拼团返现活动不存在");
        }
    }

    /**
     * 关闭拼团返现活动
     * @param $pintuan_id
     * @return array|\multitype
     */
    public function cronClosePinfan($pintuan_id)
    {
        $pintuan_info = model('promotion_pinfan')->getInfo([ [ 'pintuan_id', '=', $pintuan_id ] ], 'site_id,start_time,status');
        if (!empty($pintuan_info)) {
            return $this->invalidPinfan($pintuan_id, $pintuan_info[ 'site_id' ]);
        } else {
            return $this->error("", "拼团返现活动不存在");
        }
    }

    /**
     * 判断规格值是否禁用
     * @param $pintuan_id
     * @param $site_id
     * @param string $goods_spec_format
     * @return int|mixed
     */
    public function getGoodsSpecFormat($pintuan_id, $site_id, $goods_spec_format = '')
    {
        //获取活动参与的商品sku_ids
        $sku_ids = model('promotion_pinfan_goods')->getColumn([ [ 'pintuan_id', '=', $pintuan_id ], [ 'site_id', '=', $site_id ] ], 'sku_id');
        $goods_model = new Goods();
            $res = $goods_model->getGoodsSpecFormat($sku_ids, $goods_spec_format);
        return $res;
    }

    /**
     * 拼团返现失效
     * @param unknown $pintuan_id
     * @param unknown $site_id
     */
    public function invalidPinfanTo($pintuan_id, $site_id)
    {
        model('promotion_pinfan')->startTrans();
        try {
            $pintuan_info = model("promotion_pinfan")->getInfo([ [ 'pintuan_id', '=', $pintuan_id ], [ 'site_id', '=', $site_id ] ]);

            $res = model("promotion_pinfan")->update(
                [ 'status' => 3, 'modify_time' => time() ],
                [ [ 'pintuan_id', '=', $pintuan_id ], [ 'site_id', '=', $site_id ] ]
            );

            if ($pintuan_info[ 'group_num' ] > 0) {//有人拼团返现
                //查询所有拼团返现组
                $group_model = new PinfanGroup();
                $group_info = $group_model->getPinfanGroupList([ [ 'pintuan_id', '=', $pintuan_id ] ], 'group_id');
//                $group_list = model("promotion_pinfan_group")->pageList([ [ 'pintuan_id', '=', $pintuan_id, 'status', '<>', 1 ] ], 'group_id', "",1, 50);
//                if($group_list['count'] > 1){
//                    //新增事件
//                    $cron = new Cron();
//                    $cron->addCron(1, 0, "拼团返现活动关闭", "ClosePintuan", $pintuan_info[ 'end_time' ], $pintuan_id);
//                }
                $group = $group_info[ 'data' ];

                if (!empty($group)) {
                    foreach ($group as $v) {

                        $result = $group_model->cronClosePinfanGroup($v[ 'group_id' ]);
                        if ($result[ 'code' ] < 0) {
                            model('promotion_pinfan')->rollback();
                            return $result;
                        }
                    }
                }
            }

            $goods = new Goods();
            $goods->modifyPromotionAddon($pintuan_info[ 'goods_id' ], [ 'pinfan' => $pintuan_id ], true);
            model('promotion_pinfan')->commit();
            return $this->success($res);
        } catch (\Exception $e) {
            model('promotion_pinfan')->rollback();
            return $this->error($e->getMessage());
        }

    }

    /**
     * @param $pintuan_id
     * @param $name
     * @param $site_id
     * @param string $type
     * @return array
     * shop端推广
     */
    public function qrcode($pintuan_id, $name, $site_id, $type = "create")
    {
        $data = [
            'site_id' => $site_id,
            'app_type' => "all", // all为全部
            'type' => $type, // 类型 create创建 get获取
            'data' => [
                'pinfan_id' => $pintuan_id
            ],
            'page' => '/pages_promotion/pinfan/detail',
            'qrcode_path' => 'upload/qrcode/pinfan',
            'qrcode_name' => 'pinfan_code_' . $pintuan_id . '_' . $site_id,
        ];

        event('Qrcode', $data, true);
        $app_type_list = config('app_type');
        $path = [];
        foreach ($app_type_list as $k => $v) {
            switch ( $k ) {
                case 'h5':
                    $wap_domain = getH5Domain();
                    $path[ $k ][ 'status' ] = 1;
                    $path[ $k ][ 'url' ] = $wap_domain . $data[ 'page' ] . '?pinfan_id=' . $pintuan_id;
                    $path[ $k ][ 'img' ] = "upload/qrcode/pinfan/pinfan_code_" . $pintuan_id . "_" . $site_id . "_" . $k . ".png";
                    break;
                case 'weapp' :
                    $config = new ConfigModel();
                    $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'WEAPP_CONFIG' ] ]);
                    if (!empty($res[ 'data' ])) {
                        if (empty($res[ 'data' ][ 'value' ][ 'qrcode' ])) {
                            $path[ $k ][ 'status' ] = 2;
                            $path[ $k ][ 'message' ] = '未配置微信小程序';
                        } else {
                            $path[ $k ][ 'status' ] = 1;
                            $path[ $k ][ 'img' ] = $res[ 'data' ][ 'value' ][ 'qrcode' ];
                        }

                    } else {
                        $path[ $k ][ 'status' ] = 2;
                        $path[ $k ][ 'message' ] = '未配置微信小程序';
                    }
                    break;

                case 'wechat' :
                    $config = new ConfigModel();
                    $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'WECHAT_CONFIG' ] ]);
                    if (!empty($res[ 'data' ])) {
                        if (empty($res[ 'data' ][ 'value' ][ 'qrcode' ])) {
                            $path[ $k ][ 'status' ] = 2;
                            $path[ $k ][ 'message' ] = '未配置微信公众号';
                        } else {
                            $path[ $k ][ 'status' ] = 1;
                            $path[ $k ][ 'img' ] = $res[ 'data' ][ 'value' ][ 'qrcode' ];
                        }
                    } else {
                        $path[ $k ][ 'status' ] = 2;
                        $path[ $k ][ 'message' ] = '未配置微信公众号';
                    }
                    break;
            }

        }

        $return = [
            'path' => $path,
            'name' => $name,
        ];

        return $this->success($return);
    }

    /**
     * 商品用到的分类
     * @param $condition
     * @return array
     */
    public function getGoodsCategoryIds($condition)
    {
        $cache_name = "shop_pinfan_goods_category_" . md5(json_encode($condition));
        $cache_time = 60;
        $cache_res = Cache::get($cache_name);
        if (empty($cache_res) || time() - $cache_res[ 'time' ] > $cache_time) {
            $list = Db::name('promotion_pinfan')
                ->alias('pp')
                ->join('goods g', 'pp.goods_id = g.goods_id', 'inner')
                ->where($condition)
                ->group('g.category_id')
                ->column('g.category_id');
            $category_ids = trim(join('0', $list), ',');
            $category_id_arr = array_unique(explode(',', $category_ids));
            Cache::set($cache_name, [ 'time' => time(), 'data' => $category_id_arr ]);
        } else {
            $category_id_arr = $cache_res[ 'data' ];
        }
        return $this->success($category_id_arr);
    }

    public function urlQrcode($page, $qrcode_param, $promotion_type, $app_type, $site_id)
    {
        $params = [
            'site_id' => $site_id,
            'data' => $qrcode_param,
            'page' => $page,
            'promotion_type' => $promotion_type,
            'app_type' => $app_type,
            'h5_path' => $page . '?id=' . $qrcode_param[ 'id' ],
            'qrcode_path' => 'upload/qrcode/pinfan',
            'qrcode_name' => 'pinfan_code_' . $promotion_type . '_' . $qrcode_param[ 'id' ] . '_' . $site_id,
        ];

        $solitaire = event('PromotionQrcode', $params, true);
        return $this->success($solitaire);
    }
}
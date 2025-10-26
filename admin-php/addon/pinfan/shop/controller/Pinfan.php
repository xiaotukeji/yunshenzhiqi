<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\pinfan\shop\controller;

use addon\pinfan\model\PinfanOrder;
use app\shop\controller\BaseShop;
use addon\pinfan\model\Pinfan as PinfanModel;
use addon\pinfan\model\PinfanGroup as PinfanGroupModel;

/**
 * 拼团控制器
 */
class Pinfan extends BaseShop
{

    /*
     *  拼团活动列表
     */
    public function lists()
    {
        if (request()->isJson()) {

            $model = new PinfanModel();
            $condition = [
                [ 'p.site_id', '=', $this->site_id ],
                [ 'g.goods_state', '=', 1 ],
                [ 'g.is_delete', '=', 0 ]
            ];

            $status = input('status', '');//拼团状态
            if ($status) {
                if ($status == 6) {
                    $condition[] = [ 'p.status', '=', 0 ];
                } else {
                    $condition[] = [ 'p.status', '=', $status ];
                }
            }
            $goods_name = input('goods_name', '');
            if ($goods_name) {
                $condition[] = [ 'g.goods_name', 'like', '%' . $goods_name . '%' ];
            }
            $start_time = input('start_time', '');
            $end_time = input('end_time', '');
            if ($start_time && !$end_time) {
                $condition[] = [ 'p.start_time', '>=', date_to_time($start_time) ];
            } elseif (!$start_time && $end_time) {
                $condition[] = [ 'p.end_time', '<=', date_to_time($end_time) ];
            } elseif ($start_time && $end_time) {
                $condition[] = [ 'p.start_time', '>=', date_to_time($start_time) ];
                $condition[] = [ 'p.end_time', '<=', date_to_time($end_time) ];
            }

            //排序
            $order = input('order', 'create_time');
            $sort = input('sort', 'desc');
            if ($order == 'create_time') {
                $order_by = 'p.' . $order . ' ' . $sort;
            } else {
                $order_by = 'p.' . $order . ' ' . $sort . ',p.create_time desc';
            }

            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);

            $list = $model->getPinfanPageList($condition, $page, $page_size, $order_by);
            return $list;
        } else {

            return $this->fetch("pinfan/lists");
        }
    }

    /**
     * 添加活动
     */
    public function add()
    {
        if (request()->isJson()) {
            $pintuan_data = [
                'site_id' => $this->site_id,
                'site_name' => '',
                'pintuan_name' => input('pintuan_name', ''),//活动名称
                'is_virtual_goods' => input('is_virtual_goods', ''),//是否是虚拟商品
                'pintuan_num' => input('pintuan_num', ''),//参团人数
                'chengtuan_num' => input('chengtuan_num', ''),//实际成功人数
                'reward_type' => input('reward_type', ''),//拼团成功但是未发货奖励 类型
                'reward_type_num' => input('reward_type_num', ''),//奖励类型数量
                'pintuan_time' => input('pintuan_time', ''),//拼团有效期
                'remark' => input('remark', ''),//活动规则
                'create_time' => time(),//
                'is_recommend' => input('is_recommend', ''),//是否推荐
                'start_time' => date_to_time(input('start_time', '')),//开始时间
                'end_time' => date_to_time(input('end_time', '')),//结束时间
                'pintuan_price' => input('pintuan_price', ''),//拼团价
                'buy_num' => input('buy_num', ''),//拼团限制购买
                'is_single_buy' => input('is_single_buy', ''),//是否单独购买
                'is_virtual_buy' => input('is_virtual_buy', ''),//是否虚拟成团
                'is_promotion' => input('is_promotion', ''),//是否团长优惠
            ];
            $goods = [
                'goods_ids' => input('goods_ids', ''),
                'sku_ids' => input('sku_ids', ''),
            ];
            $sku_list = input('sku_list', '');
            $pinfan_model = new PinfanModel();
            return $pinfan_model->addPinfan($pintuan_data, $goods, $sku_list);
        } else {
            $pintuan_name = '拼团返利 ' . date('Y-m-d');
            $this->assign('pintuan_name', $pintuan_name);
            return $this->fetch("pinfan/add");
        }
    }

    /**
     * 编辑活动
     */
    public function edit()
    {
        $pinfan_model = new PinfanModel();
        if (request()->isJson()) {
            $pintuan_data = [
                'pintuan_id' => input('pintuan_id', ''),
                'site_id' => $this->site_id,
                'pintuan_name' => input('pintuan_name', ''),//活动名称
                'goods_id' => input('goods_id', ''),//商品ID
                'is_virtual_goods' => input('is_virtual_goods', ''),//是否是虚拟商品
                'pintuan_num' => input('pintuan_num', ''),//参团人数
                'chengtuan_num' => input('chengtuan_num', ''),//实际成功人数
                'reward_type' => input('reward_type', ''),//拼团成功但是未发货奖励 类型
                'reward_type_num' => input('reward_type_num', ''),//奖励类型数量
                'modify_time' => time(),
                'pintuan_time' => input('pintuan_time', ''),//拼团有效期
                'remark' => input('remark', ''),//活动规则
                'is_recommend' => input('is_recommend', ''),//是否推荐
                'start_time' => date_to_time(input('start_time', '')),//开始时间
                'end_time' => date_to_time(input('end_time', '')),//结束时间
                'buy_num' => input('buy_num', ''),//拼团限制购买
                'is_single_buy' => input('is_single_buy', ''),//是否单独购买
                'is_virtual_buy' => input('is_virtual_buy', ''),//是否虚拟成团
                'is_promotion' => input('is_promotion', ''),//是否团长优惠
            ];

            $sku_list = input('sku_list', '');
            $goods = [
                'goods_id' => input('goods_id', ''),
                'sku_ids' => input('sku_ids', ''),
            ];
            return $pinfan_model->editPinfan($pintuan_data, $goods, $sku_list);
        } else {
            $pintuan_id = input('pintuan_id', '');
            //获取拼团信息
            $pintuan_info = $pinfan_model->getPinfanDetail($pintuan_id, $this->site_id);
            if (empty($pintuan_info[ 'data' ])) $this->error('未获取到活动数据', href_url('pinfan://shop/pinfan/lists'));

            $this->assign('pintuan_info', $pintuan_info);
            return $this->fetch("pinfan/edit");
        }
    }

    /*
     *  拼团详情
     */
    public function detail()
    {
        $pinfan_model = new PinfanModel();

        $pintuan_id = input('pintuan_id', '');
        //获取拼团信息
        $pintuan_info = $pinfan_model->getPinfanJoinGoodsList($pintuan_id, $this->site_id)[ 'data' ] ?? [];
        if (empty($pintuan_info)) $this->error('未获取到活动数据', href_url('pintuan://shop/pintuan/lists'));
        $this->assign('info', $pintuan_info);
        return $this->fetch("pinfan/detail");
    }

    /*
     *  删除拼团活动
     */
    public function delete()
    {
        $pintuan_id = input('pintuan_id', '');
        $site_id = $this->site_id;

        $pinfan_model = new PinfanModel();
        return $pinfan_model->deletePinfan($pintuan_id, $site_id);
    }

    /*
     *  拼团活动失效
     */
    public function invalid()
    {
        $pintuan_id = input('pintuan_id', '');
        $site_id = $this->site_id;

        $pinfan_model = new PinfanModel();
        return $pinfan_model->invalidPinfanTo($pintuan_id, $site_id);
    }

    /**********************************  开团团队    ******************************************************/

    /*
     *  开团团队列表
     */
    public function group()
    {
        $model = new PinfanGroupModel();

        $condition[] = [ 'pg.site_id', '=', $this->site_id ];
        $pintuan_id = input('pintuan_id', '');
        if ($pintuan_id) {
            $condition[] = [ 'pg.pintuan_id', '=', $pintuan_id ];
        }
        //获取续签信息
        if (request()->isJson()) {
            $goods_name = input('goods_name', '');
            $nickname = input('nickname', '');
            $start_time = input('start_time', '');
            $end_time = input('end_time', '');
            $status = input('status', '');//拼团状态
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);

            if ($goods_name) {
                $condition[] = [ 'g.goods_name', 'like', '%' . $goods_name . '%' ];
            }

            if ($nickname) {
                $condition[] = [ 'm.nickname', 'like', '%' . $nickname . '%' ];
            }

            if ($start_time && !$end_time) {
                $condition[] = [ 'pg.create_time', '>=', date_to_time($start_time) ];
            } elseif (!$start_time && $end_time) {
                $condition[] = [ 'pg.create_time', '<=', date_to_time($end_time) ];
            } elseif ($start_time && $end_time) {
                $condition[] = [ 'pg.create_time', 'between', [ date_to_time($start_time), date_to_time($end_time) ] ];
            }

            if ($status) {
                if ($status == 6) {
                    $condition[] = [ 'pg.status', '=', 0 ];
                } else {
                    $condition[] = [ 'pg.status', '=', $status ];
                }
            }

            $list = $model->getPinfanGroupPageList($condition, $page, $page_size, 'pg.group_id desc');
            return $list;
        } else {
            $this->assign('pintuan_id', $pintuan_id);

            return $this->fetch("pinfan/group");
        }

    }

    /*
     *  拼团组成员订单列表
     */
    public function groupOrder()
    {
        $model = new PinfanOrder();

        $condition = [];
//        $condition[] = ['ppo.pintuan_status', 'in', '2,3'];
        $group_id = input('group_id', '');
        if ($group_id) {
            $condition[] = [ 'ppo.group_id', '=', $group_id ];
        }
        //获取续签信息
        if (request()->isJson()) {

            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $list = $model->getPinfanOrderPageList($condition, $page, $page_size, 'ppo.id desc');
            return $list;

        } else {
            $this->assign('group_id', $group_id);
            //获取团长信息
            $pinfan_group_model = new PinfanGroupModel();
            $info = $pinfan_group_model->getPinfanGroupDetail([ [ 'pg.group_id', '=', $group_id ], [ 'pg.site_id', '=', $this->site_id ] ]);

            if (empty($info[ 'data' ])) $this->error('未获取到成团数据', href_url('pinfan://shop/pinfan/group'));
            $this->assign('info', $info[ 'data' ]);
            return $this->fetch("pinfan/group_order");
        }
    }

    /**
     * 获取商品列表
     * @return array
     */
    public function getSkuList()
    {
        if (request()->isJson()) {
            $pintuan_id = input('pintuan_id', '');
            $pinfan_model = new PinfanModel();
            $pintuan_info = $pinfan_model->getPinfanGoodsList($pintuan_id, $this->site_id);
            return $pintuan_info;
        }
    }

    /**
     * 拼团推广
     */
    public function pintuanUrl()
    {
        $pinfan_id = input('pintuan_id', '');
        $app_type = input('app_type', 'all');
        $pinfan_model = new PinfanModel();

        $res = $pinfan_model->urlQrcode('/pages_promotion/pinfan/detail', [ 'id' => $pinfan_id ], 'pinfan', $app_type, $this->site_id);
        return $res;
    }

    /*
     *  删除拼团活动
     */
    public function deleteAll()
    {
        if (request()->isJson()) {
            $pintuan_id = input('pinfan_id', '');
            $pinfan_model = new PinfanModel();
            foreach ($pintuan_id as $k => $v){
                $res = $pinfan_model->deletePinfan($v, $this->site_id);
            }
            return $res;
        }


    }

    /*
     *  拼团活动失效
     */
    public function closeAll()
    {
        if (request()->isJson()) {
            $pintuan_id = input('pinfan_id', '');
            $pinfan_model = new PinfanModel();
            foreach ($pintuan_id as $k => $v){
                $res = $pinfan_model->invalidPinfanTo($v, $this->site_id);
            }
            return $res;
        }
    }
}
<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 上海牛之云网络科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 */

namespace addon\cashier\storeapi\controller;

use addon\cashier\model\order\PendOrder as PendOrderModel;
use app\storeapi\controller\BaseStoreApi;

class Pendorder extends BaseStoreApi
{

    public function add()
    {
        $goods = $this->params[ 'goods' ] ?? '[]';
        $discount = $this->params[ 'discount' ] ?? '{}';

        $data = [
            'site_id' => $this->site_id,
            'member_id' => $this->params[ 'member_id' ] ?? 0,
            'store_id' => $this->store_id,
            'goods' => json_decode($goods, true),
            'discount_money' => $this->params[ 'discount_money' ] ?? 0,
            'discount' => $discount,
            'remark' => $this->params[ 'remark' ] ?? ''
        ];
        $res = ( new PendOrderModel() )->add($data);
        return $this->response($res);
    }

    public function edit()
    {
        $goods = $this->params[ 'goods' ] ?? '[]';
        $discount = $this->params[ 'discount' ] ?? '{}';

        $data = [
            'site_id' => $this->site_id,
            'order_id' => $this->params[ 'order_id' ],
            'member_id' => $this->params[ 'member_id' ] ?? 0,
            'store_id' => $this->store_id,
            'goods' => json_decode($goods, true),
            'discount_money' => $this->params[ 'discount_money' ] ?? 0,
            'discount' => $discount,
            'remark' => $this->params[ 'remark' ] ?? ''
        ];
        $res = ( new PendOrderModel() )->edit($data);
        return $this->response($res);
    }

    public function page()
    {
        $page_index = $this->params[ 'page' ] ?? 1;
        $page_size = $this->params[ 'page_size' ] ?? PAGE_LIST_ROWS;

        $condition = [
            [ 'o.site_id', '=', $this->site_id ],
            [ 'o.store_id', '=', $this->store_id ]
        ];
        $model = new PendOrderModel();
        $data = $model->getOrderPageList($condition, 'o.*,m.nickname', 'o.create_time desc', $page_index, $page_size, 'o', [ [ 'member m', 'm.member_id = o.member_id', 'left' ] ]);

        if (!empty($data[ 'data' ][ 'list' ])) {
            $order_id_array = [];
            foreach ($data[ 'data' ][ 'list' ] as $k => $v) {
                $order_id_array[] = $v[ 'order_id' ];
            }
            $order_ids = implode(',', $order_id_array);
            $field = 'og.*, g.goods_name, gs.spec_name, g.goods_image';
            $list = $model->getOrderGoodsList([ [ 'og.order_id', 'in', $order_ids ] ], $field, '', 'og', [
                [ 'goods g', 'g.goods_id = og.goods_id', 'left' ],
                [ 'goods_sku gs', 'gs.sku_id = og.sku_id', 'left' ]
            ])[ 'data' ];
            foreach ($data[ 'data' ][ 'list' ] as $k => $v) {
                foreach ($list as $k_order_goods => $v_order_goods) {
                    if ($v[ 'order_id' ] == $v_order_goods[ 'order_id' ]) {
                        $data[ 'data' ][ 'list' ][ $k ][ 'order_goods' ][] = $v_order_goods;
                    }
                }
            }
        }

        return $this->response($data);
    }

    public function delete()
    {
        $order_id = $this->params[ 'order_id' ];

        $res = ( new PendOrderModel() )->delete([
            'site_id' => $this->site_id,
            'store_id' => $this->store_id,
            'order_id' => $order_id
        ]);

        return $this->response($res);
    }

    public function updateRemark()
    {
        $order_id = $this->params[ 'order_id' ];
        $remark = $this->params[ 'remark' ];

        $res = ( new PendOrderModel() )->update([ 'remark' => $remark ], [
            [ 'site_id', '=', $this->site_id ],
            [ 'store_id', '=', $this->store_id ],
            [ 'order_id', '=', $order_id ]
        ]);
        return $this->response($res);
    }

}
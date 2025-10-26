<?php

/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 上海牛之云网络科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\cashier\model\order;

use app\model\BaseModel;
use Exception;
use think\facade\Log;


/**
 * 挂单
 *
 * @author Administrator
 *
 */
class PendOrder extends BaseModel
{
    /**
     * 添加
     * @param $param
     * @return array
     */
    public function add($param)
    {
        $res = $this->handleData($param);
        if ($res['code'] != 0) return $res;
        $param = $res['data'];

        model('cashier_pendorder')->startTrans();
        try {
            $order_id = model('cashier_pendorder')->add([
                'site_id' => $param['site_id'],
                'store_id' => $param['store_id'],
                'member_id' => $param['member_id'] ?? 0,
                'create_time' => time(),
                'remark' => $param['remark'] ?? '',
                'order_money' => $param['order_money'],
                'discount_money' => $param['discount_money'] ?? 0.00,
                'discount_data' => $param['discount'] ?? ''
            ]);

            $order_goods = [];
            foreach ($param['goods'] as $item) {
                $order_goods[] = [
                    'order_id' => $order_id,
                    'site_id' => $param['site_id'],
                    'store_id' => $param['store_id'],
                    'goods_id' => $item['goods_id'],
                    'sku_id' => $item['sku_id'],
                    'num' => $item['num'],
                    'price' => $item['price'],
                    'goods_class' => $item['goods_class']
                ];
            }
            model('cashier_pendorder_goods')->addList($order_goods);
            model('cashier_pendorder')->commit();
            return $this->success($order_id);
        } catch ( Exception $e ) {
            model('cashier_pendorder')->rollback();
            Log::write('挂单出现错误，错误原因：' . $e->getMessage() . $e->getFile() . $e->getLine());
            return $this->error();
        }
    }

    private function handleData($param)
    {
        $store_info = model('store')->getInfo([['site_id', '=', $param['site_id']], ['store_id', '=', $param['store_id']]], 'store_id');
        if (empty($store_info)) return $this->error('', '门店不存在！');

        $param['order_money'] = 0;

        $join = [
            ['goods g', 'sku.goods_id=g.goods_id', 'inner'],
            ['store_goods_sku sgs', 'sku.sku_id=sgs.sku_id and sgs.store_id=' . $param['store_id'], 'inner'],
        ];

        foreach ($param['goods'] as $k => $item) {
            if (isset($item['money']) && $item['money'] > 0) {
                $param['goods'][$k]['price'] = round($item['money'], 2);
                $param['goods'][$k]['goods_class'] = 'money';
            } else {
                $sku_info = model('goods_sku')->getInfo([['sku.sku_id', '=', $item['sku_id']]], 'sku.goods_class,IF(g.is_unify_price = 1,sku.price,sgs.price) as price', 'sku', $join);
                if (empty($sku_info)) return $this->error('', '不存在的商品！');
                $param['goods'][$k]['price'] = !empty($item['price']) ? $item['price'] : $sku_info['price'];
                $param['goods'][$k]['goods_class'] = $sku_info['goods_class'];
            }
            $param['order_money'] += ($param['goods'][$k]['price'] * $item['num']);
        }

        return $this->success($param);
    }

    /**
     * 编辑
     * @param $param
     * @return array
     */
    public function edit($param)
    {
        $res = $this->handleData($param);
        if ($res['code'] != 0) return $res;
        $param = $res['data'];

        model('cashier_pendorder')->startTrans();
        try {
            $condition = [
                ['site_id', '=', $param['site_id']],
                ['store_id', '=', $param['store_id']],
                ['order_id', '=', $param['order_id']]
            ];

            model('cashier_pendorder')->update([
                'member_id' => $param['member_id'] ?? 0,
                'remark' => $param['remark'] ?? '',
                'order_money' => $param['order_money'],
                'discount_money' => $param['discount_money'] ?? 0.00,
                'discount_data' => $param['discount'] ?? ''
            ], $condition);

            model('cashier_pendorder_goods')->delete($condition);

            $order_goods = [];
            foreach ($param['goods'] as $item) {
                $order_goods[] = [
                    'order_id' => $param['order_id'],
                    'store_id' => $param['store_id'],
                    'site_id' => $param['site_id'],
                    'goods_id' => $item['goods_id'],
                    'sku_id' => $item['sku_id'],
                    'num' => $item['num'],
                    'price' => $item['price'],
                    'goods_class' => $item['goods_class']
                ];
            }
            model('cashier_pendorder_goods')->addList($order_goods);

            model('cashier_pendorder')->commit();
            return $this->success($param['order_id']);
        } catch ( Exception $e ) {
            model('cashier_pendorder')->rollback();
            Log::write('挂单出现错误，错误原因：' . $e->getMessage() . $e->getFile() . $e->getLine());
            return $this->error();
        }
    }

    /**
     * 挂单表编辑
     * @param $data
     * @param $where
     * @return array
     */
    public function update($data, $where)
    {
        $res = model('cashier_pendorder')->update($data, $where);
        if ($res) {
            return $this->success($res);
        } else {
            return $this->error();
        }
    }

    /**
     * 删除订单
     * @param $param
     * @return array
     */
    public function delete($param)
    {
        model('cashier_pendorder')->startTrans();
        try {
            $res = model('cashier_pendorder')->delete([
                ['site_id', '=', $param['site_id']],
                ['store_id', '=', $param['store_id']],
                ['order_id', '=', $param['order_id']]
            ]);
            if (!$res) {
                model('cashier_pendorder')->rollback();
                return $this->error();
            }
            model('cashier_pendorder_goods')->delete([['order_id', '=', $param['order_id']]]);
            model('cashier_pendorder')->commit();
            return $this->success();
        } catch ( Exception $e ) {
            model('cashier_pendorder')->rollback();
            Log::write('挂单删除出现错误，错误原因：' . $e->getMessage() . $e->getFile() . $e->getLine());
            return $this->error();
        }
    }

    /**
     * 查询挂单分页列表
     * @param array $condition
     * @param bool $field
     * @param string $order
     * @param int $page
     * @param int $list_rows
     * @param string $alias
     * @param array $join
     * @return array
     */
    public function getOrderPageList($condition = [], $field = true, $order = '', $page = 1, $list_rows = PAGE_LIST_ROWS, $alias = 'a', $join = [])
    {
        $data = model('cashier_pendorder')->pageList($condition, $field, $order, $page, $list_rows, $alias, $join);
        return $this->success($data);
    }

    /**
     * 查询挂单商品项列表
     * @param array $condition
     * @param bool $field
     * @param string $order
     * @param string $alias
     * @param array $join
     * @return array
     */
    public function getOrderGoodsList($condition = [], $field = true, $order = '', $alias = 'a', $join = [])
    {
        $list = model('cashier_pendorder_goods')->getList($condition, $field, $order, $alias, $join);
        foreach ($list as $k => $v) {
            if (isset($v['num'])) {
                $list[$k]['num'] = numberFormat($v['num']);
            }
        }
        return $this->success($list);
    }

}
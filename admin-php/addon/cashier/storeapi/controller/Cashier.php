<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 上海牛之云网络科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\cashier\storeapi\controller;

use addon\printer\model\PrinterOrder;
use app\model\system\Export as ExportModel;
use app\model\web\Config as ConfigModel;
use app\storeapi\controller\BaseStoreApi;
use addon\cashier\model\Cashier as CashierModel;
use app\model\order\OrderCommon;

class Cashier extends BaseStoreApi
{
    /**
     * 交接班
     */
    public function changeShifts()
    {
        $res = (new CashierModel())->changeShifts($this->user_info, $this->site_id, $this->store_id);
        return $this->response($res);
    }

    /**
     * 班次数据
     */
    public function shiftsData()
    {
        $data = [
            'shifts_data' => (new CashierModel())->getShiftsData($this->site_id, $this->store_id),
            'userinfo' => [
                'username' => $this->user_info[ 'username' ]
            ]
        ];
        return $this->response($this->success($data));
    }

    /**
     * 交接班记录
     */
    public function changeShiftsRecord()
    {
        $page = $this->params[ 'page' ] ?? 1;
        $page_size = $this->params[ 'page_size' ] ?? PAGE_LIST_ROWS;
        $uid = $this->params[ 'uid' ] ?? 0;
        $start_time = $this->params[ 'start_time' ] ?? '';
        $end_time = $this->params[ 'end_time' ] ?? '';
        if (!empty($start_time)) $start_time = strtotime($start_time);
        if (!empty($end_time)) $end_time = strtotime($end_time);

        $condition = [
            ['csr.site_id', '=', $this->site_id],
            ['csr.store_id', '=', $this->store_id],
        ];
        if ($uid != 0) $condition[] = ['csr.uid', '=', $uid];
        if ($start_time && $end_time) {
            $condition[] = ['csr.end_time', 'between', [$start_time, $end_time]];
        } elseif (!$start_time && $end_time) {
            $condition[] = ['csr.end_time', '<=', $end_time];
        } elseif ($start_time && !$end_time) {
            $condition[] = ['csr.end_time', '>=', $start_time];
        }

        $join = [['user u', 'u.uid = csr.uid', 'left']];
        $cashier_model = new CashierModel();
        $res = $cashier_model->getchangeShiftsPageList($condition, 'csr.*,u.username', 'csr.id desc', $page, $page_size, 'csr', $join);
        foreach($res['data']['list'] as &$val){
            $val['sale_goods_count'] = $cashier_model->getSaleGoodsCount([
                ['o.store_id', '=', $val['store_id']],
                ['o.pay_time', '>', $val['start_time']],
                ['o.pay_time', '<=', $val['end_time']],
            ])['data'];
        }
        return $this->response($res);
    }

    /**
     * 交接班销售记录
     */
    public function changeShiftsSaleGoodsList()
    {
        $record_id = $this->params['record_id'] ?? 0;
        $page = $this->params[ 'page' ] ?? 1;
        $page_size = $this->params[ 'page_size' ] ?? PAGE_LIST_ROWS;
        $sale_channel = $this->params['sale_channel'] ?? '';
        $sku_name = $this->params['sku_name'] ?? '';

        //交接班信息
        $cashier_model = new CashierModel();
        $record_info = $cashier_model->getChangeShiftsRecordInfo([['id', '=', $record_id]], '*')['data'];

        $alias = 'og';
        $join = [
            ['order o', 'og.order_id = o.order_id', 'inner'],
        ];
        $condition = [
            ['o.store_id', '=', $record_info['store_id']],
            ['o.pay_time', '>', $record_info['start_time']],
            ['o.pay_time', '<=', $record_info['end_time']],
        ];
        if($sale_channel !== '' && $sale_channel !== 'all'){
            if($sale_channel == 'offline'){
                $condition[] = ['o.order_from', '=', 'cashier'];
            }else{
                $condition[] = ['o.order_from', '<>', 'cashier'];
            }
        }
        if($sku_name !== ''){
            $condition[] = ['og.sku_name', 'like', '%'.$sku_name.'%'];
        }
        $order = 'og.sku_id desc';
        $field = "og.sku_id,og.goods_name,og.sku_name,og.sku_image,og.goods_id,sum(og.num) as num,sum(og.goods_money) as goods_money,sum(IF(o.order_from = 'cashier', og.num, 0)) as offline_num, sum(IF(o.order_from <> 'cashier', og.num, 0)) as online_num,og.goods_class,og.goods_class_name";
        $group = 'og.sku_id';

        $order_model = new OrderCommon();
        $res = $order_model->getOrderGoodsPageList($condition, $page, $page_size, $order, $field, $alias, $join, $group);
        foreach($res['data']['list'] as &$val){
            $val['online_num'] = numberFormat($val['online_num']);
            $val['offline_num'] = numberFormat($val['offline_num']);
            $val['price'] = sprintf("%.2f",$val['goods_money'] / $val['num']);
            $val['spec_name'] = mb_substr($val['sku_name'], mb_strlen($val['goods_name']) + 1);
        }

        return $this->response($res);
    }

    /**
     * 交接班销售导出
     */
    public function changeShiftsSaleGoodsExport()
    {
        $record_id = $this->params['record_id'] ?? 0;
        $sale_channel = $this->params['sale_channel'] ?? '';
        $sku_name = $this->params['sku_name'] ?? '';

        //交接班信息
        $cashier_model = new CashierModel();
        $record_info = $cashier_model->getChangeShiftsRecordInfo([['id', '=', $record_id]], '*')['data'];

        $condition = [
            ['o.store_id', '=', $record_info['store_id']],
            ['o.pay_time', '>', $record_info['start_time']],
            ['o.pay_time', '<=', $record_info['end_time']],
        ];
        $condition_desc = [];
        if($sale_channel !== '' && $sale_channel !== 'all'){
            if($sale_channel == 'offline'){
                $condition[] = ['o.order_from', '=', 'cashier'];
                $sale_channel_name = '线下';
            }else{
                $condition[] = ['o.order_from', '<>', 'cashier'];
                $sale_channel_name = '线上';
            }
            $condition_desc[] = ['name' => '销售渠道', 'value' => $sale_channel_name];
        }
        if($sku_name !== ''){
            $condition[] = ['og.sku_name', 'like', '%'.$sku_name.'%'];
            $condition_desc[] = ['name' => '商品名称', 'value' => $sku_name];
        }

        $field = "og.sku_id,og.goods_name,og.sku_name,og.sku_image,og.goods_id,sum(og.num) as num,sum(og.goods_money) as goods_money,sum(IF(o.order_from = 'cashier', og.num, 0)) as offline_num, sum(IF(o.order_from <> 'cashier', og.num, 0)) as online_num";

        $param = [
            'site_id' => $this->site_id,
            'store_id' => $this->store_id,
            'from_type' => 'change_shifts_sale_goods',
            'from_type_name' => '交接班商品销售',
            'condition_desc' => $condition_desc,
            'query' => [
                'table' => 'order_goods',
                'alias' => 'og',
                'join' => [
                    ['order o', 'og.order_id = o.order_id', 'inner'],
                ],
                'group' => 'og.sku_id',
                'condition' => $condition,
                'field' => $field,
                'chunk_field' => 'og.sku_id',
                'chunk_order' => 'desc',
            ],
            'export_field' => [
                ['field' => 'goods_name', 'name' => '商品名称'],
                ['field' => 'spec_name', 'name' => '商品规格'],
                ['field' => 'num', 'name' => '总数量'],
                ['field' => 'price', 'name' => '平均销售价'],
                ['field' => 'goods_money', 'name' => '销售总额'],
                ['field' => 'offline_num', 'name' => '线下销售'],
                ['field' => 'online_num', 'name' => '线上销售'],
            ],
            'handle' => function($item_list){
                foreach($item_list as &$val){
                    $val['num'] = numberFormat($val['num']);
                    $val['online_num'] = numberFormat($val['online_num']);
                    $val['offline_num'] = numberFormat($val['offline_num']);
                    $val['price'] = sprintf("%.2f",$val['goods_money'] / $val['num']);
                    $val['spec_name'] = mb_substr($val['sku_name'], mb_strlen($val['goods_name']) + 1);
                }
                return $item_list;
            },
        ];
        $export_model = new ExportModel();
        $res = $export_model->export($param);
        return $this->response($res);
    }

    /**
     * 交班打印
     * @return false|string
     */
    public function printTicket()
    {
        $record_id = $this->params[ 'record_id' ] ?? 0;
        $printer_ids = $this->params['printer_ids'] ?? 'all';

        $printer_order_model = new PrinterOrder();
        $res = $printer_order_model->printer([
            'type' => 'change_shifts',
            'site_id' => $this->site_id,
            'store_id' => $this->store_id,
            'userinfo' => $this->user_info,
            'record_id' => $record_id,
            'printer_ids' => $printer_ids,
        ]);
        return $this->response($res);
    }

    /**
     * 获取收银台收款设置
     * @return false|string
     */
    public function getCashierCollectMoneyConfig()
    {
        $res = (new CashierModel())->getCashierCollectMoneyConfig($this->site_id, $this->store_id);
        return $this->response($res);
    }

    /**
     * 收银台收款设置
     * @return false|string
     */
    public function setCashierCollectMoneyConfig()
    {
        $data = [
            'reduction' => $this->params[ 'reduction' ] ?? 1,
            'point' => $this->params[ 'point' ] ?? 1,
            'balance' => $this->params[ 'balance' ] ?? 1,
            'balance_safe' => $this->params[ 'balance_safe' ] ?? 0,
            'sms_verify' => $this->params[ 'sms_verify' ] ?? 0,
            'pay_type' => json_decode($this->params[ 'pay_type' ] ?? '[]', true),
        ];
        $res = (new CashierModel())->setCashierCollectMoneyConfig($this->site_id, $this->store_id, $data);
        return $this->response($res);
    }

    /**
     * 详情信息
     */
    public function defaultimg()
    {
        $upload_config_model = new ConfigModel();
        $res = $upload_config_model->getDefaultImg($this->site_id, 'shop');
        if (!empty($res[ 'data' ][ 'value' ])) {
            return $this->response($this->success($res[ 'data' ][ 'value' ]));
        } else {
            return $this->response($this->error());
        }
    }

    /**
     * 设置收银台主题风格配置
     * @return false|string
     */
    public function setThemeConfig()
    {
        $data = [
            'title' => $this->params[ 'title' ] ?? '',
            'name' => $this->params[ 'name' ] ?? '',
            'color' => $this->params[ 'color' ] ?? ''
        ];
        $res = (new CashierModel())->setThemeConfig($data, $this->site_id);
        return $this->response($res);
    }

    /**
     * 获取收银台主题风格列表
     * @return false|string
     */
    public function getThemeList()
    {
        $res = (new CashierModel())->getThemeList();
        return $this->response($res);
    }
}
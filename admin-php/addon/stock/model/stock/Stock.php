<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 上海牛之云网络科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\stock\model\stock;


use addon\stock\dict\StockDict;
use app\model\BaseModel;
use app\model\store\Store;
use app\model\system\Config as ConfigModel;

/**
 * 库存model
 *
 * @author Administrator
 *
 */
class Stock extends BaseModel
{
    public $document_type_list = [
        'PURCHASE' => [
            'name' => '采购入库单',
            'type' => StockDict::input,
            'prefix' => 'CGRK',
            'key' => 'PURCHASE',
        ],
        'REFUND' => [
            'name' => '退货入库单',
            'type' => StockDict::input,
            'prefix' => 'THRK',
            'key' => 'REFUND',
        ],
        'OTHERRK' => [
            'name' => '其他入库单',
            'type' => StockDict::input,
            'prefix' => 'QTRK',
            'key' => 'OTHERRK',
        ],
        'TRANSFORMRK' => [
            'name' => '其他入库单',
            'type' => StockDict::input,
            'prefix' => 'ZHRK',
            'key' => 'TRANSFORMRK',
        ],
        'SEAILCK' => [
            'name' => '销售出库单',
            'type' => StockDict::output,
            'prefix' => 'XSCK',
            'key' => 'SEAILCK',
        ],
        'OTHERCK' => [
            'name' => '其他出库单',
            'type' => StockDict::output,
            'prefix' => 'QTCK',
            'key' => 'OTHERCK',
        ],
        'TRANSFORMCK' => [
            'name' => '转换出库单',
            'type' => StockDict::output,
            'prefix' => 'ZHCK',
            'key' => 'TRANSFORMCK',
        ],
        'PDD' => [
            'name' => '盘点单',
            'type' => 'pandian',
            'prefix' => 'PDD',
            'key' => 'PDD',
        ],
        'PANYING' => [
            'name' => '盘盈入库单',
            'type' => StockDict::input,
            'prefix' => 'PYRK',
            'key' => 'PANYING',
        ],
        'PANKUI' => [
            'name' => '盘亏出库单',
            'type' => StockDict::output,
            'prefix' => 'PKCK',
            'key' => 'PANKUI',
        ],
        'ALLOTIN' => [
            'name' => '调拨入库单',
            'type' => StockDict::input,
            'prefix' => 'DBRK',
            'key' => 'ALLOTIN',
        ],
        'ALLOTPUT' => [
            'name' => '调拨出库单',
            'type' => StockDict::output,
            'prefix' => 'DBCK',
            'key' => 'ALLOTPUT',
        ],
    ];

    /**
     * 设置库存
     * @param $params
     */
    public function changeStock($params)
    {
        $user_info = $params[ 'user_info' ] ?? [];
        $site_id = $params[ 'site_id' ] ?? 1;

        $store_id = $params[ 'store_id' ] ?? 0;
        if ($store_id == 0) {
            $store_model = new Store();
            $store_info = $store_model->getDefaultStore()[ 'data' ] ?? [];
            $store_id = $store_info[ 'store_id' ];
        }
        $key = $params[ 'key' ];
        $remark = $params[ 'remark' ] ?? '';
        $is_out_stock = $params[ 'is_out_stock' ] ?? 0;
        $time = $params[ 'time' ] ?? time();
        $document_model = new Document();
        if (!empty($params[ 'goods_sku_list' ])) {
            foreach ($params[ 'goods_sku_list' ] as $k => $v) {
                $params[ 'goods_sku_list' ][ $k ] = [
                    'goods_sku_id' => $v[ 'sku_id' ],
                    'goods_id' => $v[ 'goods_id' ] ?? 0,
                    'goods_num' => $v[ 'num' ] ?? $v[ 'stock' ],
                    'goods_price' => $v[ 'price' ] ?? 0
                ];
            }
        } else {
            $sku_id = $params[ 'sku_id' ] ?? 0;
            $goods_id = $params[ 'goods_id' ] ?? 0;
            $goods_num = $params[ 'num' ] ?? $params[ 'stock' ];
            $goods_price = $params[ 'price' ] ?? 0;
        }
        $goods_sku_list = $params[ 'goods_sku_list' ] ?? [
                [
                    'goods_sku_id' => $sku_id,
                    'goods_id' => $goods_id,
                    'goods_num' => $goods_num,
                    'goods_price' => $goods_price
                ]
        ];

        $document_params = [
            'store_id' => $store_id,
            'site_id' => $site_id,
            'remark' => $remark,
            'goods_sku_list' => $goods_sku_list,
            'user_info' => $user_info,
            'time' => $time,
            'is_out_stock' => $is_out_stock
        ];
        switch ( $key ) {
            case 'PURCHASE'://采购入库单
                $result = $document_model->addPurchase($document_params);
                break;
            case 'REFUND'://退货入库单
                $result = $document_model->addRefundInput($document_params);
                break;
            case 'OTHERRK'://其他入库单
                $result = $document_model->addOtherInput($document_params);
                break;
            case 'TRANSFORMRK'://转换入库单
                $result = $document_model->addTransformInput($document_params);
                break;
            case 'SEAILCK'://销售出库单
                $result = $document_model->addSell($document_params);
                break;
            case 'OTHERCK'://其他出库单
                $result = $document_model->addOtherOutput($document_params);
                break;
            case 'TRANSFORMCK'://转换出库单
                $result = $document_model->addTransformput($document_params);
                break;
            case 'PANYING'://盘盈入库单
                $result = $document_model->addDocument($document_params);
                break;
            case 'PANKUI'://盘亏出库单
                $result = $document_model->addDocument($document_params);
                break;
        }
        return $result;
    }

    /**
     * 设置商品库存(比对差值,得出)
     * @param $params
     * @return array
     */
    public function setGoodsStock($params)
    {

        $site_id = $params[ 'site_id' ];
        $remark = $params[ 'remark' ] ?? '';
        $store_id = $params[ 'store_id' ];

        $goods_sku_list = $params[ 'goods_sku_list' ] ?? [];

        //todo  这儿可以查询商品信息类型  来定义可能只有实物商品才需要出入库记录
        if (!empty($goods_sku_list)) {
            foreach ($goods_sku_list as $k => $v) {
                $sku_list[] = [
                    'goods_sku_id' => $v[ 'sku_id' ],
                    'goods_num' => $v[ 'stock' ],
                ];
            }
        } else {
            $goods_id = $params[ 'goods_id' ] ?? 0;//只要传递就必然是与sku_id匹配的
            $sku_id = $params[ 'sku_id' ];
            $stock = $params[ 'stock' ];//设置的新库存
            $sku_list = [
                [
                    'goods_sku_id' => $sku_id,
                    'goods_num' => $stock,
                ]
            ];
        }

        //同步整理,  将要设置的销售库存转化为实际库存
        $sku_ids = array_column($sku_list, 'goods_sku_id');
//        $sku_key_list = array_column($sku_list, null, 'goods_sku_id');
        $sku_info_list = model('store_goods_sku')->getList([ [ 'sku_id', 'in', $sku_ids ], [ 'store_id', '=', $store_id ] ]);
        foreach ($sku_info_list as $k => $v) {
            $sku_info_list[ $k ][ 'stock' ] = numberFormat($v[ 'stock' ]);
            $sku_info_list[ $k ][ 'sale_num' ] = numberFormat($sku_info_list[ $k ][ 'sale_num' ]);
            $sku_info_list[ $k ][ 'real_stock' ] = numberFormat($sku_info_list[ $k ][ 'real_stock' ]);
        }
        $sku_info_column_list = array_column($sku_info_list, null, 'sku_id');
        $stock_sku_list = [];

        foreach ($sku_list as $k => $item) {
            $item_sku_id = $item[ 'goods_sku_id' ];
            $temp_item = $sku_info_column_list[ $item_sku_id ] ?? [];
            $item_stock = $temp_item[ 'stock' ] ?? 0;
            $item_real_stock = $temp_item[ 'real_stock' ] ?? 0;
            if($item_stock > $item_real_stock){
                $item_stock = $item_real_stock;
            }
            if ($item_stock != $item_real_stock) {
                $item[ 'goods_num' ] = $item[ 'goods_num' ] + ( $item_real_stock - $item_stock );
            }
            $stock_sku_list[] = $item;
        }

        $inventory_params = [
            'site_id' => $site_id,
            'store_id' => $store_id,
            'sku_list' => $stock_sku_list,
            'user_info' => $params[ 'user_info' ] ?? [],
            'remark' => $remark,
            'is_limit' => false
        ];
        $inventory_model = new Inventory();
        return $inventory_model->addInventory($inventory_params);
    }

    /**
     * 获取商品sku列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param int $store_id
     * @return array
     */
    public function getStoreGoodsSkuList($condition = [], $field = '*', $order = 'gs.create_time desc', $store_id = 0)
    {
        $alias = 'gs';
        $join = [
            [ 'goods g', 'g.goods_id = gs.goods_id', 'left' ],
            [
                'store_goods_sku sgs',
                'sgs.sku_id = gs.sku_id and (sgs.store_id is null or sgs.store_id = ' . $store_id . ')',
                'left'
            ]
        ];
        $list = model('goods_sku')->getList($condition, $field, $order, $alias, $join, '', 15);
        foreach ($list as $k => $v) {
            if (isset($v[ 'goods_stock' ])) {
                $list[ $k ][ 'goods_stock' ] = numberFormat($v[ 'goods_stock' ]);
            }
            if (isset($v[ 'stock' ])) {
                $list[ $k ][ 'stock' ] = numberFormat($list[ $k ][ 'stock' ]);
            }
            if (isset($v[ 'sale_num' ])) {
                $list[ $k ][ 'sale_num' ] = numberFormat($list[ $k ][ 'sale_num' ]);
            }
            if (isset($v[ 'virtual_sale' ])) {
                $list[ $k ][ 'virtual_sale' ] = numberFormat($list[ $k ][ 'virtual_sale' ]);
            }

            if (isset($v[ 'real_stock' ])) {
                $list[ $k ][ 'real_stock' ] = numberFormat($list[ $k ][ 'real_stock' ]);
            }
        }
        return $this->success($list);
    }

    /**
     * 设置库存配置
     * @param $data
     * @param $site_id
     * @return array
     */
    public function setStockConfig($data, $site_id)
    {
        $config = new ConfigModel();
        return $config->setConfig($data, '库存配置', 1, [ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'STOCK_CONFIG' ] ]);
    }

    /**
     * 获取库存配置
     * @param $site_id
     * @return array
     */
    public function getStockConfig($site_id)
    {
        $config = new ConfigModel();
        $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'STOCK_CONFIG' ] ]);

        if (empty($res[ 'data' ][ 'value' ])) {
            $res[ 'data' ][ 'value' ] = [
                'is_audit' => 0
            ];
        }
        return $res;
    }

    /**
     * 查询商品是否存在盘点单据
     * @param $goods_id
     * @param $site_id
     * @return array
     */
    public function getGoodsIsHasStockRecords($goods_id, $site_id){
        $stock_model = new Stock();
        $stock_config = $stock_model->getStockConfig($site_id)[ 'data' ][ 'value' ];
        $is_audit = $stock_config[ 'is_audit' ];
        if ($is_audit) {
            //查询商品是否存在盘点单据
            $inventory_model = new Inventory();
            $info = $inventory_model->getInventoryGoodsInfo([
                ['sig.site_id', '=', $site_id],
                ['sig.goods_id', '=', $goods_id],
                ['si.status', '=', Inventory::AUDIT],
            ])['data'] ?? [];
            if (empty($info)) {
                return $this->success();
            } else {
                return $this->error();
            }
        }else{
            return $this->success();
        }
    }

    /**
     * 获取商品sku列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param int $store_id
     * @return array
     */
    public function getStoreGoodsSkuPage($condition = [], $field = '*', $order = 'gs.create_time desc', $store_id = 0, $page = 1, $page_size = 10)
    {
        $alias = 'gs';
        $join = [
            [ 'goods g', 'g.goods_id = gs.goods_id', 'left' ],
            [
                'store_goods_sku sgs',
                'sgs.sku_id = gs.sku_id and (sgs.store_id is null or sgs.store_id = ' . $store_id . ')',
                'left'
            ]
        ];
        $list = model('goods_sku')->pageList($condition, $field, $order, $page, $page_size, $alias, $join, '');
//        $list = model('goods_sku')->getList($condition, $field, $order, $alias, $join, '', 15);

        foreach ($list['list'] as $k => $v) {
            if (isset($v[ 'goods_stock' ])) {
                $list['list'][ $k ][ 'goods_stock' ] = numberFormat($list['list'][ $k ][ 'goods_stock' ]);
            }
            if (isset($v[ 'stock' ])) {
                $list['list'][ $k ][ 'stock' ] = numberFormat($list['list'][ $k ][ 'stock' ]);
            }
            if (isset($v[ 'sale_num' ])) {
                $list['list'][ $k ][ 'sale_num' ] = numberFormat($list['list'][ $k ][ 'sale_num' ]);
            }
            if (isset($v[ 'virtual_sale' ])) {
                $list['list'][ $k ][ 'virtual_sale' ] = numberFormat($list['list'][ $k ][ 'virtual_sale' ]);
            }

            if (isset($v[ 'real_stock' ])) {
                $list['list'][ $k ][ 'real_stock' ] = numberFormat($list['list'][ $k ][ 'real_stock' ]);
            }
        }
//        $data = [];
//        $data['list'] = $list['list'];
        return $this->success($list);
    }
}

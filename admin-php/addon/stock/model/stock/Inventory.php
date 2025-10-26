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


use app\model\store\Store;
use app\model\BaseModel;
use app\model\storegoods\StoreGoods;
use think\facade\Log;

/**
 * 盘点
 * Class Inventory
 * @package addon\stock\model\stock
 */
class Inventory extends BaseModel
{

    const AUDIT = 1; // 待审核

    const REFUSE = -1;// 审核被拒绝

    const AUDITED = 2; // 审核通过

    public $status = [

        self::AUDIT => [
            'status' => self::AUDIT,
            'name' => '待审核'
        ],
        self::AUDITED => [
            'status' => self::AUDITED,
            'name' => '已审核'
        ],
        self::REFUSE => [
            'status' => self::REFUSE,
            'name' => '已拒绝'
        ]
    ];

    /**
     * 获取审核状态
     * @return array
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function inventoryNo()
    {
        return 'PD' . date('ymdhis', time()) . rand(1111, 9999);
    }

    /**
     * 添加盘点单
     * @param $params
     * @return array|mixed
     */
    public function addInventory($params)
    {
        $site_id = $params[ 'site_id' ];
        $store_id = $params[ 'store_id' ];
        //是否限制只能有一个盘点单据
        $is_limit = $params['is_limit'] ?? true;
        if($is_limit){
            //同商品同时只能存在一个盘点单据
            $count = model('stock_inventory')->getCount([
                [ 'store_id', '=', $store_id ],
                [ 'site_id', '=', $site_id ],
                [ 'status', '=', self::AUDIT ]
            ]);
            if ($count > 0) {
                return $this->error([], '一段时间内只能存在一个待审核的盘点单据');
            }
        }

        model('stock_inventory')->startTrans();
        try {
            $sku_list = $params[ 'sku_list' ];
            $user_info = $params[ 'user_info' ];
            $inventory_no = isset($params['inventory_no']) && $params['inventory_no'] ? $params['inventory_no'] : $this->inventoryNo();

            $count = model('stock_inventory')->getCount([ ['inventory_no', '=', $inventory_no] ]);
            if($count > 0) return $this->error([], '录入失败，单号重复');

            //查询门店名称信息
            $store_model = new Store();
            $store_condition = [
                [ 'store_id', '=', $store_id ]
            ];
            $store_info = $store_model->getStoreInfo($store_condition, 'store_name')[ 'data' ] ?? [];
            if (empty($store_info)) {
                model('stock_inventory')->rollback();
                return $this->success([], '找不到所选的门店');
            }
            $store_name = $store_info[ 'store_name' ];
            $common_data = [
                'site_id' => $site_id,
                'create_time' => time(),
                'store_id' => $store_id
            ];
            $data = [
                'operater' => $user_info[ 'uid' ] ?? 0,
                'operater_name' => $user_info[ 'username' ] ?? '系统',
                'create_time' => time(),
                'inventory_no' => $inventory_no,
                'store_name' => $store_name,
                'remark' => $params[ 'remark' ] ?? '',
                'action_time' => $params[ 'action_time' ] ?? time(),
                'status' => self::AUDIT
            ];
            //盘点单据
            $inventory_data = array_merge($data, $common_data);
            $inventory_id = model('stock_inventory')->add($inventory_data);
            $common_data[ 'inventory_id' ] = $inventory_id;
            $temp_goods_list = model('goods_sku')->getList(
                [
                    ['gs.sku_id', 'in', array_column($sku_list, 'goods_sku_id')]
                ],
                'gs.sku_id,gs.goods_id,gs.sku_name,gs.sku_no,gs.sku_image,gs.spec_name, 
            sgs.real_stock',
                '',
                'gs',
                [
                    [
                        'store_goods_sku sgs',
                        'gs.sku_id = sgs.sku_id and sgs.store_id = '.$store_id,
                        'left'
                    ]
                ]
            );
            $temp_goods_list = array_column($temp_goods_list, null, 'sku_id');
            $insert_data = [];
            foreach ($sku_list as $k => $goods_sku) {
                $goods_sku_id = $goods_sku[ 'goods_sku_id' ];
                $goods_num = numberFormat($goods_sku[ 'goods_num' ]);
                $item_temp_item = $temp_goods_list[$goods_sku_id] ?? [];
                $goods_remark = '';
                //具体业务尚未调试，表结构不一致
//                $goods_sku_info = model('goods_sku')->getInfo([ [ 'sku_id', '=', $goods_sku_id ] ], 'sku_id,goods_id,sku_name,sku_no,sku_image,spec_name');
//                $goods_sku_data_info = model('store_goods_sku')->getInfo([ [ 'sku_id', '=', $goods_sku_id ], [ 'store_id', '=', $store_id ] ], '*');
                $goods_stock = $item_temp_item[ 'real_stock' ] ?? 0;
                $goods_sku_data = [];
                $goods_sku_data[ 'inventory_num' ] = $goods_num;
                $goods_sku_data[ 'inventory_remark' ] = $goods_remark;
                $goods_sku_data[ 'goods_id' ] = $item_temp_item[ 'goods_id' ];
                $goods_sku_data[ 'goods_sku_id' ] = $goods_sku_id;
                $goods_sku_data[ 'goods_sku_name' ] = $item_temp_item[ 'sku_name' ];
                $goods_sku_data[ 'goods_sku_no' ] = $item_temp_item[ 'sku_no' ];
                $goods_sku_data[ 'goods_sku_spec' ] = $item_temp_item[ 'spec_name' ];
                $goods_sku_data[ 'goods_img' ] = $item_temp_item[ 'sku_image' ];
                $goods_sku_data[ 'stock' ] = numberFormat($goods_stock);
                $insert_data[] = array_merge($goods_sku_data, $common_data);
            }
            model('stock_inventory_goods')->addList($insert_data);
            $is_auto_audit = $params[ 'is_auto_audit' ] ?? true;
            if ($is_auto_audit) {
                //主动调用审核
                $inventory_params = [
                    'inventory_id' => $inventory_id,
                    'site_id' => $site_id,
                    'user_info' => $user_info
                ];
                $audit_result = $this->audit($inventory_params);
                if ($audit_result[ 'code' ] < 0) {
                    model('stock_inventory')->rollback();
                    return $audit_result;
                }
            } else {
                $stock_model = new Stock();
                $stock_config = $stock_model->getStockConfig($site_id)[ 'data' ][ 'value' ];
                $is_audit = $stock_config[ 'is_audit' ];
                if (!$is_audit) {
                    //主动调用审核
                    $inventory_params = [
                        'inventory_id' => $inventory_id,
                        'site_id' => $site_id,
                        'user_info' => $user_info
                    ];
                    $audit_result = $this->audit($inventory_params);
                    if ($audit_result[ 'code' ] < 0) {
                        model('stock_inventory')->rollback();
                        return $audit_result;
                    }
                }
            }

            model('stock_inventory')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('stock_inventory')->rollback();
            return $this->error($e->getMessage() . $e->getFile() . $e->getLine());
        }

    }

    /**
     * 通过审核
     * @param $params
     * @return array|mixed
     */
    public function audit($params)
    {
        $inventory_id = $params[ 'inventory_id' ];
        $site_id = $params[ 'site_id' ];
        $user_info = $params[ 'user_info' ];
        $condition = [
            [ 'inventory_id', '=', $inventory_id ],
            [ 'site_id', '=', $site_id ]
        ];
        $info = model('stock_inventory')->getInfo($condition);
        if (empty($info))
            return $this->error([], '找不到可审核的单据');

        if ($info[ 'status' ] == self::AUDITED)
            return $this->error([], '当前单据已审核');
        $data = [
            'status' => self::AUDITED,
            'audit_time' => time(),
            'verifier' => $user_info[ 'uid' ] ?? 0,//审核人
            'verifier_name' => $user_info[ 'username' ] ?? '系统'
        ];
        model('stock_inventory')->update($data, $condition);
        $store_id = $info[ 'store_id' ];
        return $this->complete([ 'inventory_id' => $inventory_id, 'site_id' => $site_id, 'store_id' => $store_id, 'user_info' => $user_info ]);
    }

    /**
     * 单据通过审核
     * @param $params
     * @return array|mixed
     */
    public function complete($params)
    {
        $inventory_id = $params[ 'inventory_id' ];
        $user_info = $params[ 'user_info' ] ?? [];
        $site_id = $params[ 'site_id' ];
        $condition = [
            [ 'inventory_id', '=', $inventory_id ],
            [ 'site_id', '=', $site_id ]
        ];
        $info = model('stock_inventory')->getInfo($condition);
        if (empty($info))
            return $this->error([], '找不到可审核的单据');

        $stock_inventory_goods_list = model('stock_inventory_goods')->getList($condition, '*');
        if (empty($stock_inventory_goods_list))
            return $this->error([], '找不到可审核的单据');

        $store_id = $info[ 'store_id' ];
        $stat_data = $this->stat([ 'inventory_id' => $inventory_id, 'store_id' => $store_id ])[ 'data' ] ?? [];

        $data = [
            'kinds_num' => $stat_data[ 'kinds_num' ],
            'kinds_profit_num' => $stat_data[ 'kinds_profit_num' ],
            'kinds_loss_num' => $stat_data[ 'kinds_loss_num' ],
            'kinds_even_num' => $stat_data[ 'kinds_even_num' ],
            'num' => $stat_data[ 'num' ],
            'profit_num' => $stat_data[ 'profit_num' ],
            'loss_num' => $stat_data[ 'loss_num' ],
            'even_num' => $stat_data[ 'even_num' ],
            'profitloss_num' => $stat_data[ 'profitloss_num' ],
            'inventory_cost_money' => $stat_data[ 'inventory_cost_money' ],
            'profitloss_sale_money' => $stat_data[ 'profitloss_sale_money' ],
        ];
        model('stock_inventory')->update($data, $condition);
        $inventory_goods_list = $stat_data[ 'inventory_goods_list' ] ?? [];

        $store_goods_model = new StoreGoods();
        $store_goods_sku_list_condition = [
            [ 'sgs.sku_id', 'in', array_column($inventory_goods_list, 'goods_sku_id') ],
            [ 'sgs.store_id', '=', $store_id ]
        ];
        $store_sku_list = $store_goods_model->getStoreGoodsSkuList($store_goods_sku_list_condition, 'sgs.*, gs.sku_name, gs.unit', '', 'sgs',
        [
            [
                'goods_sku gs',
                'gs.sku_id = sgs.sku_id',
                'left'
            ]
        ]
        )['data'] ?? [];
        $store_sku_list_column = array_column($store_sku_list, null, 'sku_id');
        $document = new Document();
        $input_array = [];
        $output_array = [];
        foreach ($inventory_goods_list as $v) {
            $item_sku_id = $v[ 'goods_sku_id' ];
            $item_goods_id = $v[ 'goods_id' ];
            $item_condition = [
                [ 'inventory_id', '=', $inventory_id ],
                [ 'goods_sku_id', '=', $item_sku_id ]
            ];
            $item_data = [
                'inventory_id' => $inventory_id,
                'goods_id' => $v[ 'goods_id' ],
                'goods_sku_id' => $v[ 'goods_sku_id' ],
                'stock' => $v[ 'stock' ],
                'inventory_num' => $v[ 'inventory_num' ],
                'inventory_remark' => $v[ 'inventory_remark' ] ?? '',
                'profitloss_num' => $v[ 'profitloss_num' ],
                'inventory_cost_money' => $v[ 'inventory_cost_money' ],
                'profitloss_sale_money' => $v[ 'profitloss_sale_money' ],
            ];
            model('stock_inventory_goods')->update($item_data, $item_condition);

            $store_sku_info = $store_sku_list_column[$item_sku_id] ?? [];
            $store_sku_stock = $store_sku_info[ 'real_stock' ] ?? 0;
            $inventory_num = $v[ 'inventory_num' ];
            $item_price = $store_sku_info[ 'cost_price' ] ?? 0;
            $item_unit = $store_sku_info[ 'unit' ] ?? '件';
            $diff = abs($inventory_num - $store_sku_stock);
            if ($diff != 0) {
                if ($inventory_num > $store_sku_stock) {
                    $input_array[] = [
                        'goods_id' => $item_goods_id,
                        'goods_sku_id' => $item_sku_id,
                        'goods_sku_name' => $store_sku_info[ 'sku_name' ],
                        'goods_unit' => $item_unit,
                        'goods_num' => $diff,
                        'goods_price' => $item_price,
                    ];
                } else if ($inventory_num < $store_sku_stock) {
                    $output_array[] = [
                        'goods_id' => $item_goods_id,
                        'goods_sku_id' => $item_sku_id,
                        'goods_sku_name' => $store_sku_info[ 'sku_name' ],
                        'goods_unit' => $item_unit,
                        'goods_num' => $diff,
                        'goods_price' => $item_price,
                    ];
                }
            }
        }
        $document_params = [
            'inventory_id' => $inventory_id,
            'store_id' => $store_id,
            'user_info' => $user_info,
            'site_id' => $site_id,
            'remark' => $info['remark'],
        ];

        if (!empty($input_array)) {
            $document_params[ 'document_type' ] = 'PANYING';
            $document_params[ 'goods_sku_list' ] = $input_array;
            $result = $document->addDocument($document_params);
        }
        if (!empty($output_array)) {
            $document_params[ 'document_type' ] = 'PANKUI';
            $document_params[ 'goods_sku_list' ] = $output_array;
            $document_params[ 'is_out_stock' ] = 1;
            $result = $document->addDocument($document_params);
        }

        if (isset($result) && $result[ 'code' ] < 0) {
            return $result;
        }
        return $this->success();
    }

    /**
     * 数据统计以及整理
     * @param $params
     * @return array
     */
    public function stat($params)
    {
        $inventory_id = $params[ 'inventory_id' ];
        $condition = [
            [ 'inventory_id', '=', $inventory_id ]
        ];
        $info = model('stock_inventory')->getInfo($condition);
        $store_id = $info[ 'store_id' ];
        $kinds_num = $kinds_profit_num = $kinds_loss_num = $kinds_even_num = $num = $profit_num = $loss_num = $even_num = $profitloss_num = $inventory_cost_money = $profitloss_sale_money = 0;

        $stock_inventory_goods_list = model('stock_inventory_goods')->getList($condition, '*');

        $temp_goods_sku_list = model('goods_sku')->getList(
            [
                ['gs.sku_id', 'in', array_column($stock_inventory_goods_list, 'goods_sku_id')]
            ],
            'gs.sku_id, gs.sku_name, gs.sku_no, gs.unit, sgs.price, sgs.cost_price, sgs.real_stock',
            '',
            'gs',
            [
                [
                    'store_goods_sku sgs',
                    'gs.sku_id = sgs.sku_id and sgs.store_id = '.$store_id,
                    'left'
                ]
            ]
        );
        $goods_list_column = array_column($temp_goods_sku_list, null, 'sku_id');
        $store_goods_model = new StoreGoods();
        $inventory_goods_list = [];
        foreach ($stock_inventory_goods_list as $k => $v) {
            $kinds_num++;
            $sku_id = $v[ 'goods_sku_id' ];
            $goods_id = $v[ 'goods_id' ];
            $store_sku_condition = [
                [ 'store_id', '=', $store_id ],
                [ 'sku_id', '=', $sku_id ]
            ];

            $v[ 'stock' ] = numberFormat($v[ 'stock' ]);
            $v[ 'inventory_num' ] = numberFormat($v[ 'inventory_num' ]);
            $v[ 'profitloss_num' ] = numberFormat($v[ 'profitloss_num' ]);
            $item_info = $goods_list_column[$sku_id] ?? [];
            $item_price = $item_info[ 'price' ] ?? 0;//销售价
            $item_cost_price = $item_info[ 'cost_price' ] ?? 0;// 成本价
            $item_stock = $item_info[ 'real_stock' ] ?? 0;//实物库存
            $item_total_sale_money = $item_price * $item_stock;//总销售价

            $inventory_num = $v[ 'inventory_num' ];//盘点数量
            $item_inventory_cost_money = $inventory_num * $item_cost_price;
            $inventory_cost_money += $item_inventory_cost_money;//盘点总成本价
            $ing_total_sale_money = $inventory_num * $item_price;//盘点总销售价
            $num += $inventory_num;//盘点总数
            $item_profitloss_num = $inventory_num - $item_stock;
            $item_profitloss_sale_money = 0;
            if ($item_profitloss_num == 0) {//持平
                $kinds_even_num++;
                $even_num += abs($item_profitloss_num);
            } else if ($item_profitloss_num > 0) {//盘盈数
                $kinds_profit_num++;
                $profit_num += abs($item_profitloss_num);
                $item_profitloss_sale_money = $ing_total_sale_money - $item_total_sale_money;
            } else {//盘亏数
                $kinds_loss_num++;
                $loss_num += abs($item_profitloss_num);
            }
            $profitloss_num += $item_profitloss_num;
            $profitloss_sale_money += $item_profitloss_sale_money;

            $inventory_goods_list[ $sku_id ] = [
                'goods_id' => $goods_id,
                'goods_sku_id' => $sku_id,
                'goods_sku_name' => $item_info[ 'sku_name' ],
                'goods_sku_no' => $item_info[ 'sku_no' ],
                'goods_unit' => $item_info[ 'unit' ],
                'stock' => numberFormat($item_stock),
                'inventory_num' => numberFormat($inventory_num),
                'profitloss_num' => numberFormat($item_profitloss_num),
                'inventory_cost_money' => $item_inventory_cost_money,
                'profitloss_sale_money' => $item_profitloss_sale_money,
            ];
        }
        $data = [
            'kinds_num' => $kinds_num,
            'kinds_profit_num' => $kinds_profit_num,
            'kinds_loss_num' => $kinds_loss_num,
            'kinds_even_num' => $kinds_even_num,
            'num' => $num,
            'profit_num' => $profit_num,
            'loss_num' => $loss_num,
            'even_num' => $even_num,
            'profitloss_num' => $profitloss_num,
            'inventory_cost_money' => $inventory_cost_money,
            'profitloss_sale_money' => $profitloss_sale_money,
            'inventory_goods_list' => $inventory_goods_list
        ];

        return $this->success($data);
    }

    /**
     * 库存盘点单列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getInventoryPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*')
    {
        $res = model('stock_inventory')->pageList($condition, $field, $order, $page, $page_size);
        foreach ($res[ 'list' ] as $k => $v) {
            if (isset($v[ 'num' ])) {
                $res[ 'list' ][ $k ][ 'num' ] = numberFormat($res[ 'list' ][ $k ][ 'num' ]);
            }
            if (isset($v[ 'profit_num' ])) {
                $res[ 'list' ][ $k ][ 'profit_num' ] = numberFormat($res[ 'list' ][ $k ][ 'profit_num' ]);
            }
            if (isset($v[ 'loss_num' ])) {
                $res[ 'list' ][ $k ][ 'loss_num' ] = numberFormat($res[ 'list' ][ $k ][ 'loss_num' ]);
            }
            if (isset($v[ 'even_num' ])) {
                $res[ 'list' ][ $k ][ 'even_num' ] = numberFormat($res[ 'list' ][ $k ][ 'even_num' ]);
            }
            if (isset($v[ 'profitloss_num' ])) {
                $res[ 'list' ][ $k ][ 'profitloss_num' ] = numberFormat($res[ 'list' ][ $k ][ 'profitloss_num' ]);
            }
            if (isset($v[ 'status' ])) {
                $res[ 'list' ][ $k ][ 'status_name' ] = $this->status[ $v[ 'status' ] ][ 'name' ];
            }
        }
        return $this->success($res);
    }

    /**
     * 单据详情
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getInventoryInfo($condition = [], $field = '*')
    {
        $inventory_info = model('stock_inventory')->getInfo($condition, $field);
        if (!empty($inventory_info)) {

            if (isset($inventory_info[ 'status' ])) {
                $inventory_info[ 'status_name' ] = $this->status[ $inventory_info[ 'status' ] ][ 'name' ];
            }

            if (isset($inventory_info[ 'num' ])) {
                $inventory_info[ 'num' ] = numberFormat($inventory_info[ 'num' ]);
            }
            if (isset($inventory_info[ 'profit_num' ])) {
                $inventory_info[ 'profit_num' ] = numberFormat($inventory_info[ 'profit_num' ]);
            }
            if (isset($inventory_info[ 'loss_num' ])) {
                $inventory_info[ 'loss_num' ] = numberFormat($inventory_info[ 'loss_num' ]);
            }
            if (isset($inventory_info[ 'even_num' ])) {
                $inventory_info[ 'even_num' ] = numberFormat($inventory_info[ 'even_num' ]);
            }
            if (isset($inventory_info[ 'profitloss_num' ])) {
                $inventory_info[ 'profitloss_num' ] = numberFormat($inventory_info[ 'profitloss_num' ]);
            }

            //单据产品项
            $inventory_goods_list = model('stock_inventory_goods')->getList([
                [ 'inventory_id', '=', $inventory_info[ 'inventory_id' ] ]
            ]);
            foreach ($inventory_goods_list as $k => $v) {
                $inventory_goods_list[ $k ][ 'stock' ] = numberFormat($v[ 'stock' ]);
                $inventory_goods_list[ $k ][ 'inventory_num' ] = numberFormat($inventory_goods_list[ $k ][ 'inventory_num' ]);
                $inventory_goods_list[ $k ][ 'profitloss_num' ] = numberFormat($inventory_goods_list[ $k ][ 'profitloss_num' ]);
            }
            $inventory_info[ 'goods_sku_list_array' ] = $inventory_goods_list;
            $inventory_info[ 'goods_count' ] = count($inventory_info[ 'goods_sku_list_array' ]);
            $inventory_info[ 'create_time' ] = date('Y-m-d H:i:s', $inventory_info[ 'create_time' ]);
            if ($inventory_info[ 'audit_time' ]) $inventory_info[ 'audit_time' ] = date('Y-m-d H:i:s', $inventory_info[ 'audit_time' ]);
            if ($inventory_info[ 'action_time' ]) $inventory_info[ 'action_time' ] = date('Y-m-d H:i:s', $inventory_info[ 'action_time' ]);
        }
        return $this->success($inventory_info);
    }

    /**
     * 获取编辑单据数据
     * @param array $condition
     * @return array
     */
    public function getInventoryEditData($condition = [])
    {
        $field = 'inventory_id, store_id,inventory_no,action_time,remark';
        $inventory_info = model('stock_inventory')->getInfo($condition, $field);
        if (!empty($inventory_info)) {
            $goods_field = 'gs.sku_id,gs.sku_image,gs.sku_name,gs.unit,gs.sku_no,
            sgs.stock,sgs.real_stock,sgs.price,sgs.cost_price,
            ig.inventory_num as goods_num,ig.goods_sku_id';

            $join = [
                [ 'goods_sku gs', 'ig.goods_sku_id = gs.sku_id', 'left' ],
                [
                    'store_goods_sku sgs',
                    'sgs.sku_id = gs.sku_id and (sgs.store_id is null or sgs.store_id = ' . $inventory_info[ 'store_id' ] . ')',
                    'left'
                ]
            ];

            $inventory_goods_list = model('stock_inventory_goods')->getList([
                [ 'inventory_id', '=', $inventory_info[ 'inventory_id' ] ]
            ], $goods_field, '', 'ig', $join);
            foreach ($inventory_goods_list as $k => $v) {
                $inventory_goods_list[ $k ][ 'stock' ] = numberFormat($v[ 'stock' ]);
                $inventory_goods_list[ $k ][ 'goods_num' ] = numberFormat($inventory_goods_list[ $k ][ 'goods_num' ]);
            }
            $inventory_info[ 'goods_list' ] = array_column($inventory_goods_list, null, 'goods_sku_id');
        }
        return $this->success($inventory_info);
    }

    /**
     * 拒绝审核
     * @param $params
     * @return array
     */
    public function refuse($params)
    {
        $inventory_id = $params[ 'inventory_id' ];
        $site_id = $params[ 'site_id' ];
        $user_info = $params[ 'user_info' ];
        $condition = [
            [ 'inventory_id', '=', $inventory_id ],
            [ 'site_id', '=', $site_id ]
        ];
        $info = model('stock_inventory')->getInfo($condition);
        if (empty($info))
            return $this->error([], '找不到可审核的单据');

        if ($info[ 'status' ] == self::AUDITED)
            return $this->error([], '当前单据已审核');
        $data = [
            'status' => self::REFUSE,
            'audit_time' => time(),
            'refuse_reason' => $params[ 'refuse_reason' ],
            'verifier' => $user_info[ 'uid' ] ?? 0,//审核人
            'verifier_name' => $user_info[ 'username' ] ?? '系统'
        ];
        model('stock_inventory')->update($data, $condition);
        return $this->success();
    }

    /**
     * 删除单据
     * @param $params
     * @return array
     */
    public function delete($params)
    {
        $site_id = $params[ 'site_id' ];
        $inventory_id = $params[ 'inventory_id' ];
        $user_info = $params[ 'user_info' ];
        $store_id = $params[ 'store_id' ] ?? 0;
        //查询单据
        $condition = [
            [ 'inventory_id', '=', $inventory_id ],
            [ 'site_id', '=', $site_id ]
        ];
        if ($store_id > 0) {
            $condition[] = [ 'store_id', '=', $store_id ];
        }
        $info = model('stock_inventory')->getInfo($condition);
        if (empty($info)) {
            return $this->error([], '找不到盘点单据');
        }
        //被拒绝也可以删除
        if ($info[ 'status' ] == self::AUDITED) {
            return $this->error('已审核的单据不能删除');
        }
        if ($info[ 'operater' ] != $user_info[ 'uid' ]) {
            return $this->error('只有单据创建者可以删除单据');
        }
        model('stock_inventory')->delete($condition);
        model('stock_inventory_goods')->delete($condition);
        return $this->success();
    }

    /**
     * 编辑盘点单
     * @param $params
     * @return array|mixed
     */
    public function editInventory($params)
    {
        $inventory_id = $params[ 'inventory_id' ];
        $site_id = $params[ 'site_id' ];
        $store_id = $params[ 'store_id' ];
        $user_info = $params[ 'user_info' ];
        //同商品同时只能存在一个盘点单据
        $info = model('stock_inventory')->getInfo([
            [ 'site_id', '=', $site_id ],
            [ 'inventory_id', '=', $inventory_id ],
        ]);

        if (empty($info)) {
            return $this->error([], '找不到盘点单据');
        }

        if ($info[ 'status' ] == self::AUDITED) {
            return $this->error('已审核的单据不能编辑');
        }
        if ($info[ 'operater' ] != $user_info[ 'uid' ]) {
            return $this->error('只有单据创建者可以编辑单据');
        }

        $inventory_no = isset($params['inventory_no']) && $params['inventory_no'] ? $params['inventory_no'] : $this->inventoryNo();

        $count = model('stock_inventory')->getCount([ ['inventory_no', '=', $inventory_no], ['inventory_id', '<>', $inventory_id] ]);
        if($count > 0) return $this->error([], '录入失败，单号重复');

        model('stock_inventory')->startTrans();
        try {
            $sku_list = $params[ 'sku_list' ];

            //查询门店名称信息
            $store_model = new Store();
            $store_condition = [
                [ 'store_id', '=', $store_id ]
            ];
            $store_info = $store_model->getStoreInfo($store_condition, 'store_name')[ 'data' ] ?? [];
            if (empty($store_info)) {
                model('stock_inventory')->rollback();
                return $this->success([], '找不到所选的门店');
            }
            $store_name = $store_info[ 'store_name' ];

            $common_data = [
                'site_id' => $info[ 'site_id' ],
                'create_time' => $info[ 'create_time' ],
                'store_id' => $store_id
            ];
            $data = [
                'remark' => $params[ 'remark' ] ?? '',
                'status' => self::AUDIT,
                'action_time' => $params[ 'action_time' ] ?? time(),
                'inventory_no' => $inventory_no,
                'store_name' => $store_name
            ];
            //盘点单据
            $inventory_data = array_merge($data, $common_data);

            $inventory_goods_condition = [
                [ 'site_id', '=', $site_id ],
                [ 'inventory_id', '=', $inventory_id ]
            ];
            //编辑盘点单据
            model('stock_inventory')->update($inventory_data, $inventory_goods_condition);

            $common_data[ 'inventory_id' ] = $inventory_id;

            //删除原来的盘点商品单据
            model('stock_inventory_goods')->delete($inventory_goods_condition);
            foreach ($sku_list as $k => $goods_sku) {
                $goods_sku_id = $goods_sku[ 'goods_sku_id' ];
                $goods_num = numberFormat($goods_sku[ 'goods_num' ]);
                $goods_remark = '';
                //具体业务尚未调试，表结构不一致
                $goods_sku_info = model('goods_sku')->getInfo([ [ 'sku_id', '=', $goods_sku_id ] ], 'sku_id,goods_id,sku_name,sku_no,sku_image,spec_name');
                $goods_sku_data_info = model('store_goods_sku')->getInfo([ [ 'sku_id', '=', $goods_sku_id ], [ 'store_id', '=', $store_id ] ], '*');
                $goods_stock = $goods_sku_data_info[ 'real_stock' ] ?? 0;
                $goods_sku_data = [];
                $goods_sku_data[ 'inventory_num' ] = $goods_num;
                $goods_sku_data[ 'inventory_remark' ] = $goods_remark;
                $goods_sku_data[ 'goods_id' ] = $goods_sku_info[ 'goods_id' ];
                $goods_sku_data[ 'goods_sku_id' ] = $goods_sku_id;
                $goods_sku_data[ 'goods_sku_name' ] = $goods_sku_info[ 'sku_name' ];
                $goods_sku_data[ 'goods_sku_no' ] = $goods_sku_info[ 'sku_no' ];
                $goods_sku_data[ 'goods_sku_spec' ] = $goods_sku_info[ 'spec_name' ];
                $goods_sku_data[ 'goods_img' ] = $goods_sku_info[ 'sku_image' ];
                $goods_sku_data[ 'stock' ] = numberFormat($goods_stock);
                model('stock_inventory_goods')->add(array_merge($goods_sku_data, $common_data));
            }

            $stock_model = new Stock();
            $stock_config = $stock_model->getStockConfig($site_id)[ 'data' ][ 'value' ];
            $is_audit = $stock_config[ 'is_audit' ];
            if (!$is_audit) {
                //主动调用审核
                $inventory_params = [
                    'inventory_id' => $inventory_id,
                    'site_id' => $site_id,
                    'user_info' => $user_info
                ];
                $audit_result = $this->audit($inventory_params);
                if ($audit_result[ 'code' ] < 0) {
                    model('stock_inventory')->rollback();
                    return $audit_result;
                }
            }
            model('stock_inventory')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('stock_inventory')->rollback();
            return $this->error($e->getMessage() . $e->getFile() . $e->getLine());
        }

    }

    /**
     * 查询商品盘点单据
     * @param $condition
     * @param string $field
     * @return array
     */
    public function getInventoryGoodsInfo($condition, $field = '*')
    {
        //关联单据主表
        $join = [
            [ 'stock_inventory si', 'si.inventory_id = sig.inventory_id', 'inner' ],
        ];
        $info = model('stock_inventory_goods')->getInfo($condition, $field, 'sig', $join);

        return $this->success($info);

    }
}

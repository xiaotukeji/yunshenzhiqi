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
use app\dict\goods\GoodsDict;
use app\model\BaseModel;
use app\model\stock\GoodsStock;
use app\model\stock\StockData;
use app\model\store\Store;
use app\model\storegoods\StoreGoods;
use Exception;
use think\facade\Log;

/**
 * 单据管理
 * @author Administrator
 *
 */
class Document extends BaseModel
{
    public const DOCUMENT_DRAFT = 0; // 草稿

    public const DOCUMENT_AUDIT = 1; // 待审核

    public const DOCUMENT_REFUSE = -1;// 审核被拒绝

    public const DOCUMENT_AUDITED = 2; // 审核通过

    public $document_status = [
//        self::DOCUMENT_DRAFT => [
//            'status' => self::DOCUMENT_DRAFT,
//            'name' => '草稿'
//        ],
        self::DOCUMENT_AUDIT => [
            'status' => self::DOCUMENT_AUDIT,
            'name' => '待审核'
        ],
        self::DOCUMENT_AUDITED => [
            'status' => self::DOCUMENT_AUDITED,
            'name' => '已审核'
        ],
        self::DOCUMENT_REFUSE => [
            'status' => self::DOCUMENT_REFUSE,
            'name' => '已拒绝'
        ]
    ];

    /**
     * 获取审核状态
     * @return array
     */
    public function getStatus()
    {
        return $this->document_status;
    }

    /**
     * 添加采购入库
     * @param $params
     * @return array|mixed
     */
    public function addPurchase($params)
    {
        $params['document_type'] = 'PURCHASE';
        //此处要针对商品库存以及单价处理
        return $this->addDocument($params);

    }

    /**
     * 添加单据
     * @param $params
     * @return array|mixed
     */
    public function addDocument($params)
    {
        $site_id = $params['site_id'];
        $document_type = $params['document_type'];
        $user_info = $params['user_info'];
        $goods_sku_list = $params['goods_sku_list'];
        $remark = $params['remark'] ?? '';
        $store_id = $params['store_id'];
        $document_type_info = $this->getDocumentTypeInfo(['key' => $document_type]);

        //结构化
        $goods_sku_list = $this->getSkuListArray($goods_sku_list, $store_id, $document_type_info);
        $code = $goods_sku_list['code'] ?? 0;
        if ($code < 0) {
            return $goods_sku_list;
        } else {
            $goods_sku_list = $goods_sku_list['data'];
        }
        $is_out_stock = $params['is_out_stock'] ?? 0;//当前单据是否变动销售库存

        $promotion_money = $params['promotion_money'] ?? 0;
        $time = $params['time'] ?? time();
        $relate_id = $params['relate_id'] ?? 0;
        $relate_type = $params['relate_type'] ?? 0;

        model('stock_document')->startTrans();
        try {


            $prefix = $document_type_info['prefix'];//单据前缀
            $type = $document_type_info['type'];//出入库类型
            $document_no = isset($params['document_no']) && $params['document_no'] ? $params['document_no'] : $this->createDocumentNo($prefix);

            $count = model('stock_document')->getCount([['document_no', '=', $document_no]]);
            if ($count > 0) return $this->error([], '录入失败，单号重复');

            //查询门店名称信息
            $store_name = (new Store())->getStoreName([['store_id', '=', $store_id]])['data'] ?? '';
            if (empty($store_name)) {
                model('stock_document')->rollback();
                return $this->success([], '找不到所选的门店');
            }
            //计算单据商品总额(累加)
//            if ($type == StockDict::input) {
            $goods_money = getArraySum($goods_sku_list, 'goods_price', 'goods_num');
//            }else{
//                $goods_money = 0;
//            }
            $common_data = [
                'operater' => $user_info['uid'] ?? 0,
                'operater_name' => $user_info['username'] ?? '系统',
                'create_time' => time(),
            ];

            $document_money = $goods_money - $promotion_money;
            $data = [
                'site_id' => $site_id,
                'key' => $document_type,
                'type' => $type,
                'document_no' => $document_no,//单据单号
                'goods_money' => $goods_money,
                'store_id' => $store_id,
                'store_name' => $store_name,
                'is_out_stock' => $is_out_stock,
                'allot_id' => $params['allot_id'] ?? 0,
                'inventory_id' => $params['inventory_id'] ?? 0,
                'document_money' => $document_money,
                'remark' => $remark,
                'time' => $time,
                'relate_id' => $relate_id,
                'relate_type' => $relate_type,
                'status' => self::DOCUMENT_AUDIT,
            ];
            $data = array_merge($data, $common_data);
            $document_id = model('stock_document')->add($data);
            $insert_data = [];
            foreach ($goods_sku_list as $v) {
                $item_data = $v;
                $item_data = array_merge($item_data, $common_data);
                $item_data['document_id'] = $document_id;
                $item_data['site_id'] = $site_id;
                $item_data['store_id'] = $store_id;
                $insert_data[] = $item_data;
            }
            model('stock_document_goods')->addList($insert_data);
            //todo  根据站点的配置和业务传入的参数来判断是否需要审核
            $is_auto_audit = $params['is_auto_audit'] ?? true;
            if ($is_auto_audit) {
                $result = $this->audit(['document_id' => $document_id, 'site_id' => $site_id, 'user_info' => $user_info, 'store_id' => $store_id]);
                if ($result['code'] < 0) {
                    model('stock_document')->rollback();
                    return $result;
                }
            } else {
                $stock_model = new Stock();
                $stock_config = $stock_model->getStockConfig($site_id)['data']['value'];
                $is_audit = $stock_config['is_audit'];
                if (!$is_audit) {
                    $result = $this->audit(['document_id' => $document_id, 'site_id' => $site_id, 'user_info' => $user_info, 'store_id' => $store_id]);
                    if ($result['code'] < 0) {
                        model('stock_document')->rollback();
                        return $result;
                    }
                }
            }
            model('stock_document')->commit();
            return $this->success($document_id);
        } catch ( Exception $e ) {
            model('stock_document')->rollback();
            return $this->error($e->getMessage() . $e->getFile() . $e->getLine());
        }
    }

    /**
     * 获取商品列表
     * @param $goods_sku_list
     * @param $store_id
     * @return array
     */
    private function getSkuListArray($goods_sku_list, $store_id, $document_type_info)
    {
        $goods_sku_list_array = [];
        $temp_goods_sku_list = model('goods_sku')->getList(
            [
                ['gs.sku_id', 'in', array_column($goods_sku_list, 'goods_sku_id')]
            ],
            'gs.*, sgs.store_id as store_sku_store_id, sgs.cost_price as store_sku_cost_price, sgs.real_stock as store_real_stock',
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
        foreach ($goods_sku_list as $goods_sku) {
            $goods_sku_id = $goods_sku['goods_sku_id'] ?? 0;
            if ($goods_sku_id <= 0) {
                return $this->error([], '缺少goods_sku_id参数');
            }
            $goods_sku_info = $goods_list_column[$goods_sku_id] ?? [];
            if (empty($goods_sku_info))
                return $this->error([], '找不到业务相关的商品！');
            $goods_num = $goods_sku['goods_num'] ?? 0;
            $goods_class = $goods_sku_info['goods_class'];
            if($goods_class == GoodsDict::weigh && !preg_match(config('regexp.>0float3'), $goods_num)){
                return $this->error(null, '[' . $goods_sku_info[ 'sku_name' ] . ']数量必须为正数且最多保留三位小数！');
            }else if($goods_class != GoodsDict::weigh && !preg_match(config('regexp.>0num'), $goods_num)){
                return $this->error(null, '[' . $goods_sku_info[ 'sku_name' ] . ']数量必须为正整数！');
            }else if($document_type_info['type'] == StockDict::output && $goods_num > $goods_sku_info['store_real_stock']){
                return $this->error([], '[' . $goods_sku_info['sku_name'] . ']库存不足！');
            }
            if ($goods_num <= 0) {
                return $this->error([], '[' . $goods_sku_info['sku_name'] . ']变动数量必须大于0');
            }
            /*$temp_store_sku_store_id = $goods_sku_info['store_sku_store_id'] ?? 0;
            if ($temp_store_sku_store_id > 0) {
//                $store_sku_info = $store_goods_model->getStoreGoodsSkuInfo([['sku_id', '=', $goods_sku_id], ['store_id', '=', $store_id]])['data'] ?? [];
//                $goods_price = $store_sku_info['cost_price'];
                $goods_price = $goods_sku_info['store_sku_cost_price'] ?? 0;
            } else {
                $goods_price = $goods_sku['goods_price'] ?? 0;
            }*/

            $goods_sku_list_array[] = [
                'goods_id' => $goods_sku_info['goods_id'],
                'goods_sku_id' => $goods_sku_info['sku_id'],
                'goods_sku_name' => $goods_sku_info['sku_name'],
                'goods_sku_no' => $goods_sku_info['sku_no'],
                'goods_sku_img' => $goods_sku_info['sku_image'],
                'goods_sku_spec' => $goods_sku_info['spec_name'],
                'goods_unit' => $goods_sku_info['unit'] ?? '件',
                'goods_num' => $goods_num,
                'goods_price' => $goods_sku['goods_price'],
                'goods_remark' => ''
            ];
        }
        return $this->success($goods_sku_list_array);
    }

    /**
     * 单据类型信息
     * @param $params
     * @return array
     */
    public function getDocumentTypeInfo($params)
    {
        $key = $params['key'];
        $type_list = $this->getDocumentType([]);
        return $type_list[$key] ?? [];
    }

    /**
     * 多场景调用单据类型
     * @param $params
     * @return array
     */
    public function getDocumentType($params)
    {
        return (new Stock())->document_type_list;
    }

    /**
     * 创建出入库单号
     * @param $prefix
     * @return string
     */
    public function createDocumentNo($prefix)
    {
        return $prefix . date('ymdhis', time()) . rand(1111, 9999);
    }

    /**
     * 审核
     * @param $params
     * @return array
     */
    public function audit($params)
    {
        $user_info = $params['user_info'];
        //todo  校验用户是否具备单据审核权限
        $document_id = $params['document_id'] ?? 0;
        $site_id = $params['site_id'];
        $condition = [
            ['document_id', '=', $document_id],
            ['site_id', '=', $site_id],
        ];
        $store_id = $params['store_id'] ?? 0;
        if ($store_id > 0) {
            $condition[] = ['store_id', '=', $store_id];
        }
        $document_info = model('stock_document')->getInfo($condition);

        if (empty($document_info)) {
            return $this->error([], '单据不存在');
        }
        if ($document_info['status'] == self::DOCUMENT_AUDITED) {
            return $this->error([], '当前单据已经被审核');
        }
        $result = $this->complete($params);
        if ($result['code'] < 0)
            return $result;
        $data = [
            'status' => self::DOCUMENT_AUDITED,
            'audit_time' => time(),
            'verifier' => $user_info['uid'] ?? 0,//审核人
            'verifier_name' => $user_info['username'] ?? '系统'
        ];
        model('stock_document')->update($data, $condition);
        return $this->success();
    }

    /**
     * 单据完成(通过审核...)
     * @param $params
     * @return array
     */
    public function complete($params)
    {
        //计算统计改变商品的成本价和库存
        $document_id = $params['document_id'] ?? 0;
        $site_id = $params['site_id'];
        $condition = [
            ['document_id', '=', $document_id],
            ['site_id', '=', $site_id]
        ];
        $document_info = model('stock_document')->getInfo($condition);
        if (empty($document_info)) {
            return $this->error([], '单据不存在');
        }

        //影响库存和成本均价
        $result = $this->changeGoods($document_info);
        if (!empty($result['code']) && $result['code'] < 0)
            return $result;

        return $this->success();

    }

    /**
     * 采购入库改变商品库存与成本价
     * @param $document_info
     * @return array
     */
    public function changeGoods($document_info)
    {
        $document_id = $document_info['document_id'] ?? 0;
        $condition = [
            ['document_id', '=', $document_id]
        ];
        $goods_sku_list_array = model('stock_document_goods')->getList($condition);
        $store_goods_model = new StoreGoods();
        if (!empty($goods_sku_list_array)) {
            $stock_sku_data = [];
            $goods_stock_model = new GoodsStock();
            $type = $document_info['type'];
            $store_id = $document_info['store_id'];
            $site_id = $document_info['site_id'];
            $is_out_stock = $document_info['is_out_stock'] ?? 0;
            //todo 批量校验门店sku,不存在就创建
            (new StockData())->getStoreSkuAndCreateIfNotExists(['goods_sku_list' => array_map(function ($value) {
                $value['sku_id'] = $value['goods_sku_id'];
                return $value;
            }, $goods_sku_list_array), 'store_id' => $store_id]);
            $store_goods_model = new StoreGoods();
            $store_goods_sku_list_condition = [
                [ 'sgs.sku_id', 'in', array_column($goods_sku_list_array, 'goods_sku_id') ],
                [ 'sgs.store_id', '=', $store_id ]
            ];
            $store_sku_list = $store_goods_model->getStoreGoodsSkuList($store_goods_sku_list_condition, 'sgs.sku_id, sgs.cost_price as store_cost_price, sgs.real_stock as store_real_stock, gs.real_stock, gs.cost_price', '', 'sgs',
                [
                    [
                        'goods_sku gs',
                        'gs.sku_id = sgs.sku_id',
                        'left'
                    ]
                ]
            )['data'] ?? [];
            $store_sku_list_column = array_column($store_sku_list, null, 'sku_id');

            $default_store_id = (new Store())->getDefaultStore()['data']['store_id'] ?? 0;
            foreach ($goods_sku_list_array as $goods_sku_item) {
                $goods_sku_item['goods_num'] = numberFormat($goods_sku_item['goods_num']);
                $goods_sku_item['before_stock'] = numberFormat($goods_sku_item['before_stock']);
                $goods_sku_item['after_stock'] = numberFormat($goods_sku_item['after_stock']);
                $goods_sku_item['before_store_stock'] = numberFormat($goods_sku_item['before_store_stock']);
                $goods_sku_item['after_store_stock'] = numberFormat($goods_sku_item['after_store_stock']);

                $item_sku_id = $goods_sku_item['goods_sku_id'];
                $item_goods_id = $goods_sku_item['goods_id'];
                $item_goods_price = $goods_sku_item['goods_price'];
                $change_num = $goods_sku_item['goods_num'];
                if ($type == StockDict::output) {
                    $change_num = -$change_num;
                }
                $sku_condition = [
                    ['sku_id', '=', $item_sku_id]
                ];

                $goods_sku_info = $store_sku_list_column[$item_sku_id] ?? [];
//                $goods_sku_info = model('goods_sku')->getInfo($sku_condition);
                $goods_sku_stock = numberFormat($goods_sku_info['real_stock']);
                $goods_sku_cost_price = $goods_sku_info['cost_price'];
                $after_stock = $goods_sku_stock + $change_num;
                //todo  不同的业务模式,待商榷
//                if ($after_stock < 0) {
//                    return $this->error([], '库存不能小于0');
//                }
                $store_goods_sku_condition = [
                    ['store_id', '=', $store_id],
                    ['goods_id', '=', $item_goods_id],
                    ['sku_id', '=', $item_sku_id]
                ];
//                $store_goods_sku_info = $store_goods_model->getStoreGoodsSkuInfo($store_goods_sku_condition)['data'] ?? [];
//                if(empty($store_goods_sku_info)){
//                    $return_info = $goods_stock_model->isNotExistCreateStoreStock([ 'store_id' => $store_id, 'sku_id' => $item_sku_id, 'goods_id' => $item_goods_id ])[ 'data' ] ?? [];
//                    $store_goods_sku_info = $return_info[ 'sku_info' ];
//                }
                $store_goods_sku_cost_price = $goods_sku_info['store_cost_price'] ?? 0;
                $store_goods_sku_stock = $goods_sku_info['store_real_stock'] ?? 0;
//                $store_goods_sku_sale_stock = $store_goods_sku_info[ 'sale_stock' ] ?? 0;//销售库存
                $after_store_stock = $store_goods_sku_stock + $change_num;

                //门店sku库存
                $total_stock = $store_goods_sku_stock + $change_num;
                $total_goods_money = $item_goods_price * $change_num;
                //计算门店 成本均价
                // 判断采购成本与之前的成本是否不同
                if ($store_goods_sku_cost_price != $item_goods_price) {
                    //只有库存增加才会重新计算成本价(只有入库)
                    if ($change_num > 0) {
                        $total_cost_price = $store_goods_sku_stock * $store_goods_sku_cost_price + $change_num * $item_goods_price;
                        if ($total_cost_price >= 0 && $total_stock > 0) {
                            $curr_cost_price = round(($total_cost_price / $total_stock), 2);
                            model('store_goods_sku')->update(['cost_price' => $curr_cost_price], $store_goods_sku_condition);
                            if($store_id == $default_store_id){
                                $total_goods_cost_price = $goods_sku_stock * $goods_sku_cost_price + $change_num * $item_goods_price;
                                $after_goods_cost_price = round($total_goods_cost_price / $after_stock, 2);
                                model('goods_sku')->update(['cost_price' => $after_goods_cost_price], $sku_condition);
                            }
                        }
                    }
                }
                $stock_sku_data[] = [
                    'sku_id' => $item_sku_id,
                    'store_id' => $store_id,
                    'stock' => abs($change_num),
                    'goods_id' => $item_goods_id,
                ];


                $item_data = [
                    'before_stock' => $goods_sku_stock,
                    'after_stock' => $after_stock,
                    'before_goods_price' => $goods_sku_cost_price,
                    'after_goods_price' => $after_goods_cost_price ?? $goods_sku_cost_price,
                    'before_store_stock' => $store_goods_sku_stock,
                    'before_store_goods_price' => $store_goods_sku_cost_price,
                    'after_store_stock' => $after_store_stock,
                    'after_store_goods_price' => $curr_cost_price ?? $store_goods_sku_cost_price,
                    'total_goods_money' => $total_goods_money
                ];
                $item_condition = [
                    ['document_goods_id', '=', $goods_sku_item['document_goods_id']]
                ];
                model('stock_document_goods')->update($item_data, $item_condition);
            }

            $stock_params = [
                'store_id' => $store_id,
                'goods_sku_list' => $stock_sku_data,
            ];
            //调用公共的库存函数库
            if ($type == StockDict::input) {
                $goods_stock_model->incGoodsStock($stock_params);
            } else if ($change_num < 0) {
                //防重复扣销售库存
                $stock_params['is_out_stock'] = $is_out_stock;
                $result = $goods_stock_model->decGoodsStock($stock_params);
                if ($result['code'] < 0)
                    return $result;
            }
        }
        return $this->success();
    }

    /**
     * 其他入库
     * @param $params
     * @return array|mixed
     */
    public function addOtherInput($params)
    {
        //商品库存改变进行处理
        $params['document_type'] = 'OTHERRK';
        return $this->addDocument($params);
    }

    /**
     * 转换入库
     * @param $params
     * @return array|mixed
     */
    public function addTransformInput($params)
    {
        //文档编号
        $document_type_info = $this->getDocumentTypeInfo([ 'key' => 'TRANSFORMRK' ]);
        $prefix = $document_type_info[ 'prefix' ];
        $params['document_no'] = $this->createDocumentNo($prefix);
        //商品库存改变进行处理
        $params['document_type'] = 'TRANSFORMRK';
        return $this->addDocument($params);
    }

    /**
     * 退货入库
     * @param $params
     * @return array|mixed
     */
    public function addRefundInput($params)
    {
        //商品库存改变进行处理
        $params['document_type'] = 'REFUND';
        return $this->addDocument($params);
    }

    /**
     * 销售出库
     * @param $params
     * @return array|mixed
     */
    public function addSell($params)
    {
        //商品库存改变之后要针对性修改
        $params['document_type'] = 'SEAILCK';
        return $this->addDocument($params);
    }

    /**
     * 其他出库
     * @param $params
     * @return array|mixed
     */
    public function addOtherOutput($params)
    {
        //商品库存改变之后要针对性修改
        $params['document_type'] = 'OTHERCK';
        return $this->addDocument($params);
    }

    /**
     * 转换出库
     * @param $params
     * @return array|mixed
     */
    public function addTransformOutput($params)
    {
        //文档编号
        $document_type_info = $this->getDocumentTypeInfo([ 'key' => 'TRANSFORMCK' ]);
        $prefix = $document_type_info[ 'prefix' ];
        $params['document_no'] = $this->createDocumentNo($prefix);
        //商品库存改变之后要针对性修改
        $params['document_type'] = 'TRANSFORMCK';
        return $this->addDocument($params);
    }

    /**
     * 修改单据
     * @param $params
     * @return array|mixed
     */
    public function editDocument($params)
    {
        $site_id = $params['site_id'];
        $document_id = $params['document_id'];

        $user_info = $params['user_info'];
        $goods_sku_list = $params['goods_sku_list'];
        $remark = $params['remark'] ?? '';
        $store_id = $params['store_id'];

        //查询单据
        $condition = [
            ['document_id', '=', $document_id],
            ['site_id', '=', $site_id],
        ];
        $document_info = model('stock_document')->getInfo($condition);

        $document_type_info = $this->getDocumentTypeInfo(['key' => $document_info['key']]);

        //结构化
        $goods_sku_list = $this->getSkuListArray($goods_sku_list, $store_id, $document_type_info);
        $code = $goods_sku_list['code'] ?? 0;
        if ($code < 0) {
            return $goods_sku_list;
        } else {
            $goods_sku_list = $goods_sku_list['data'];
        }

        $promotion_money = $params['promotion_money'] ?? 0;
        $time = $params['time'] ?? time();

        model('stock_document')->startTrans();
        try {
            if ($document_info['status'] == 2) {
                model('stock_document')->rollback();
                return $this->error([], '已审核的单据不能编辑');
            }
            if ($document_info['operater'] != $user_info['uid']) {
                model('stock_document')->rollback();
                return $this->error([], '只有单据创建者可以编辑单据');
            }

            $document_no = isset($params['document_no']) && $params['document_no'] ? $params['document_no'] : $document_info['document_no'];

            $count = model('stock_document')->getCount([['document_no', '=', $document_no], ['document_id', '<>', $document_id]]);
            if ($count > 0) return $this->error([], '录入失败，单号重复');

            //查询门店名称信息
            $store_name = (new Store())->getStoreName([['store_id', '=', $store_id]])['data'] ?? '';
            if (empty($store_name)) {
                model('stock_document')->rollback();
                return $this->success([], '找不到所选的门店');
            }

            $goods_money = getArraySum($goods_sku_list, 'goods_price', 'goods_num');

            $document_money = $goods_money - $promotion_money;
            $data = [
                'goods_money' => $goods_money,
                'document_money' => $document_money,
                'remark' => $remark,
                'time' => $time,
                'status' => self::DOCUMENT_AUDIT,
                'document_no' => $document_no,
                'store_id' => $store_id,
                'store_name' => $store_name
            ];
            //修改单据
            model('stock_document')->update($data, [['site_id', '=', $site_id], ['document_id', '=', $document_id]]);
            //删除原有的单据商品
            model('stock_document_goods')->delete(['document_id' => $document_id]);
            $document_common_common_data = [
                'operater' => $document_info['operater'] ?? 0,
                'operater_name' => $document_info['operater_name'],
                'create_time' => $document_info['create_time'],
            ];
            foreach ($goods_sku_list as $k => $v) {
                $item_data = $v;
                $item_data = array_merge($item_data, $document_common_common_data);
                $item_data['document_id'] = $document_id;
                $item_data['site_id'] = $site_id;
                $item_data['store_id'] = $store_id;
                model('stock_document_goods')->add($item_data);
            }

            $stock_model = new Stock();
            $stock_config = $stock_model->getStockConfig($site_id)['data']['value'];
            $is_audit = $stock_config['is_audit'];
            if (!$is_audit) {
                $result = $this->audit(['document_id' => $document_id, 'site_id' => $site_id]);
            }

            model('stock_document')->commit();
            return $this->success();
        } catch ( Exception $e ) {
            model('stock_document')->rollback();
            return $this->error($e->getMessage());
        }
    }

    /**
     * 删除单据
     * @param $params
     * @return array
     */
    public function delete($params)
    {
        $site_id = $params['site_id'];
        $document_id = $params['document_id'];
        $user_info = $params['user_info'];
        $store_id = $params['store_id'] ?? 0;
        //查询单据
        $condition = [
            ['document_id', '=', $document_id],
            ['site_id', '=', $site_id],
        ];
        if ($store_id > 0) {
            $condition[] = ['store_id', '=', $store_id];
        }
        $document_info = model('stock_document')->getInfo($condition);
        //被拒绝也可以删除
        if ($document_info['status'] == 2) {
            return $this->error('已审核的单据不能删除');
        }
        if ($document_info['operater'] != $user_info['uid']) {
            return $this->error('只有单据创建者可以删除单据');
        }
        model('stock_document')->delete($condition);
        model('stock_document_goods')->delete($condition);
        return $this->success();
    }

    /**
     * 确认单据单据(转为待审核)
     * @param int $document_id
     * @param array $user_info
     */
    public function confirmDocument($document_id, $user_info)
    {

    }

    /**
     * 获取单据分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getDocumentPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*')
    {
        $document_list = model('stock_document')->pageList($condition, $field, $order, $page, $page_size);
        if (!empty($document_list['list'])) {
            foreach ($document_list['list'] as $k => $v) {
                //单据产品项
//                $document_goods_list = model('stock_document_goods')->getList([
//                    'document_id' => $v['document_id']
//                ]);
//                $document_list['list'][$k]['goods_sku_list_array'] = $document_goods_list;
                $type_info = $this->getDocumentTypeInfo(['key' => $v['key']]);
                $type_name = '';
                if (!empty($type_info)) {
                    $type_name = $type_info['name'];
                }
                $document_list['list'][$k]['type_name'] = $type_name;
                $document_list['list'][$k]['status_name'] = $this->document_status[$v['status']]['name'];
            }
        }
        return $this->success($document_list);
    }
    /********************************************************************* 类型调用 ********************************************************/

    /**
     * 获取编辑单据数据
     * @param array $condition
     * @return array
     */
    public function getDocumentEditData($condition = [])
    {
        $field = 'document_id, key, goods_money,status,store_id,document_no,remark,time';
        $document_info = $this->getDocumentInfo($condition, $field)['data'];
        if (!empty($document_info)) {
            $goods_field = 'dg.document_goods_id,gs.sku_id,gs.sku_image,gs.sku_name,gs.unit,
            gs.sku_no,sgs.stock,sgs.real_stock,sgs.price,sgs.cost_price,
            dg.goods_num,dg.goods_price,dg.goods_sku_id';

            $join = [
                ['goods_sku gs', 'dg.goods_sku_id = gs.sku_id', 'left'],
                [
                    'store_goods_sku sgs',
                    'sgs.sku_id = gs.sku_id and (sgs.store_id is null or sgs.store_id = ' . $document_info['store_id'] . ')',
                    'left'
                ]
            ];

            $goods_list = $this->getDocumentGoodsList([
                ['document_id', '=', $document_info['document_id']]
            ], $goods_field, '', 'dg', $join)['data'];
            $document_info['goods_list'] = array_column($goods_list, null, 'goods_sku_id');
        }
        return $this->success($document_info);
    }

    /**
     * 单据详情
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getDocumentInfo($condition = [], $field = '*')
    {
        $document_info = model('stock_document')->getInfo($condition, $field);
        if (!empty($document_info)) {
            if (isset($document_info['status'])) {
                $document_info['status_name'] = $this->document_status[$document_info['status']]['name'];
            }

            if (isset($document_info['create_time'])) $document_info['create_time'] = date('Y-m-d H:i:s', $document_info['create_time']);

            if (isset($document_info['audit_time']) && !empty($document_info['audit_time'])) $document_info['audit_time'] = date('Y-m-d H:i:s', $document_info['audit_time']);
        }
        return $this->success($document_info);
    }

    /**
     * 获取单据项列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $alias
     * @param array $join
     * @param string $group
     * @return array
     */
    public function getDocumentGoodsList($condition = [], $field = '*', $order = '', $alias = '', $join = [], $group = '')
    {

        $document_list = model('stock_document_goods')->getList($condition, $field, $order, $alias, $join, $group);
        if (!empty($document_list)) {
            foreach ($document_list as $k => &$v) {
                if (isset($v['goods_num'])) {
                    $v['goods_num'] = numberFormat($v['goods_num']);
                }
                if (isset($v['before_stock'])) {
                    $v['before_stock'] = numberFormat($v['before_stock']);
                }
                if (isset($v['after_stock'])) {
                    $v['after_stock'] = numberFormat($v['after_stock']);
                }
                if (isset($v['before_store_stock'])) {
                    $v['before_store_stock'] = numberFormat($v['before_store_stock']);
                }
                if (isset($v['after_store_stock'])) {
                    $v['after_store_stock'] = numberFormat($v['after_store_stock']);
                }
            }
        }
        return $this->success($document_list);
    }

    /********************************************************************* 类型调用 ********************************************************/

    /**
     * 单据详情
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getDocumentDetail($condition = [], $field = '*')
    {
        $document_info = model('stock_document')->getInfo($condition, $field);
        if (!empty($document_info)) {
            //单据产品项
            $document_goods_list = model('stock_document_goods')->getList([
                'document_id' => $document_info['document_id']
            ]);
            foreach ($document_goods_list as $k => $v) {
                $document_info['goods_unit'] = $v['goods_unit'];
                $document_goods_list[$k]['goods_num'] = numberFormat($v['goods_num']);
                $document_goods_list[$k]['before_stock'] = numberFormat($document_goods_list[$k]['before_stock']);
                $document_goods_list[$k]['after_stock'] = numberFormat($document_goods_list[$k]['after_stock']);
                $document_goods_list[$k]['before_store_stock'] = numberFormat($document_goods_list[$k]['before_store_stock']);
                $document_goods_list[$k]['after_store_stock'] = numberFormat($document_goods_list[$k]['after_store_stock']);
            }
            $document_info['goods_sku_list_array'] = $document_goods_list;
            $document_info['goods_price'] = 0;
            $document_info['goods_total_price'] = 0.00;

            foreach ($document_goods_list as $key => $value) {
                $document_info['goods_sku_list_array'][$key]['goods_sum'] = floatval($value['goods_num'] * $value['goods_price']);
                $document_info['goods_price'] += numberFormat($value['goods_num']);
                $document_info['goods_total_price'] += $document_info['goods_sku_list_array'][$key]['goods_sum'];
            }

            $type_info = $this->getDocumentTypeInfo(['key' => $document_info['key']]);
            $type_name = '';
            if (!empty($type_info)) {
                $type_name = $type_info['name'];
            }
            $document_info['type_name'] = $type_name;

            $document_info['status_data'] = $this->document_status[$document_info['status']];

            $document_info['goods_count'] = count($document_info['goods_sku_list_array']);
            $document_info['goods_total_price'] = floatval($document_info['goods_total_price']);

            $document_info['create_time'] = date('Y-m-d H:i:s', $document_info['create_time']);

            if (!empty($document_info['time'])) $document_info['time'] = date('Y-m-d H:i:s', $document_info['time']);

            if (!empty($document_info['audit_time'])) $document_info['audit_time'] = date('Y-m-d H:i:s', $document_info['audit_time']);
        }
        return $this->success($document_info);
    }

    /**
     * 单据类型
     * @return array
     */
    public function getDocumentTypeList()
    {
        return $this->success((new Stock())->document_type_list);
    }

    /**
     * 获取单据项分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getDocumentGoodsPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*')
    {
        $join = [
            ['goods_sku gs', 'gs.sku_id = dg.goods_sku_id', 'left'],
            ['stock_document d', 'd.document_id = dg.document_id', 'inner'],
//            [ 'stock_document_type dt', 'dt.key = d.key', 'inner' ]
        ];
        $document_list = model('stock_document_goods')->pageList($condition, $field, $order, $page, $page_size, 'dg', $join);
        if (!empty($document_list['list'])) {
            foreach ($document_list['list'] as $k => &$v) {
                $v['status_name'] = $this->document_status[$v['status']]['name'];
                $v['name'] = $this->getDocumentTypeInfo(['key' => $v['key']])['name'] ?? '';
                if (isset($v['goods_num'])) {
                    $v['goods_num'] = numberFormat($v['goods_num']);
                }
                if (isset($v['before_stock'])) {
                    $v['before_stock'] = numberFormat($v['before_stock']);
                }
                if (isset($v['after_stock'])) {
                    $v['after_stock'] = numberFormat($v['after_stock']);
                }
                if (isset($v['before_store_stock'])) {
                    $v['before_store_stock'] = numberFormat($v['before_store_stock']);
                }
                if (isset($v['after_store_stock'])) {
                    $v['after_store_stock'] = numberFormat($v['after_store_stock']);
                }
            }
        }
        return $this->success($document_list);
    }

    /**
     * 获取单据项数量
     * @param array $condition
     * @return array
     */
    public function getDocumentGoodsCount($condition = [])
    {
        $res = model('stock_document_goods')->getCount($condition);
        return $this->success($res);
    }

    /**
     * 拒绝
     * @param $params
     * @return array
     */
    public function refuse($params)
    {
        $user_info = $params['user_info'];
        //todo  校验用户是否具备单据审核权限
        $document_id = $params['document_id'] ?? 0;
        $site_id = $params['site_id'];
        $condition = [
            ['document_id', '=', $document_id],
            ['site_id', '=', $site_id],
        ];
        $store_id = $params['store_id'] ?? 0;
        if ($store_id > 0) {
            $condition[] = ['store_id', '=', $store_id];
        }
        $document_info = model('stock_document')->getInfo($condition);

        if (empty($document_info)) {
            return $this->error([], '单据不存在');
        }

        if ($document_info['status'] == self::DOCUMENT_AUDITED) {
            return $this->error([], '当前单据已经被审核');
        }

        $data = [
            'status' => self::DOCUMENT_REFUSE,
            'audit_time' => time(),
            'refuse_reason' => $params['refuse_reason'],
            'verifier' => $user_info['uid'] ?? 0,//审核人
            'verifier_name' => $user_info['username'] ?? '系统'
        ];
        model('stock_document')->update($data, $condition);
        return $this->success();
    }

    /**
     * 检查当前账号是否允许审核单据
     * @param $params
     * @return array
     */
    public function checkAudit($params)
    {
        $is_audit = false;
        // 平台端
        if ($params['app_module'] == 'shop' &&
            ($params['is_admin'] == 1 ||
                (isset($params['is_system']) && $params['is_system'] == 1) ||
                strpos($params['menu_array'], 'STOCK_INVENTORY_DOCUMENT_AUDIT'))
        ) {
            $is_audit = true;
        }

        // 收银台门店端
        if ($params['app_module'] == 'store' && ($params['is_admin'] == 1 || empty($params['menu_array']) || strpos($params['menu_array'], 'stock_audit'))) {
            $is_audit = true;
        }

        if ($is_audit) {
            return $this->success();
        } else {
            return $this->error('', '当前账号没有审核单据权限');
        }
    }

    /**
     * 审核单据
     * @param int $document_id
     * @param array $user_info
     */
    private function verifyDocument($document_id, $user_info)
    {

    }

}

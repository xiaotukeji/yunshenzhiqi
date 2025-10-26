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
use addon\stock\model\stock\Stock as StockModel;
use app\model\store\Store;
use app\model\BaseModel;
use app\model\system\User;
use app\dict\goods\GoodsDict;

/**
 * 调拨
 * Class Allot
 * @package addon\stock\model\stock
 */
class Allot extends BaseModel
{

    public function getAllotNo()
    {
        return 'ALLOT' . date('ymdhis', time()) . rand(1111, 9999);
    }

    /**
     * 新增库存调拨
     * @param $params
     * @return array|mixed
     */
    public function addAllot($params)
    {
        model('stock_allot')->startTrans();
        try {
            $output_store_id = $params[ 'output_store_id' ];//出库门店
            $input_store_id = $params[ 'input_store_id' ];//入库门店
            $site_id = $params[ 'site_id' ];
            $allot_time = $params[ 'allot_time' ] ?? time();
            $remark = $params[ 'remark' ] ?? '';
            $goods_sku_list = $params[ 'goods_sku_list' ];

            //查询门店名称信息
            $store_model = new Store();
            $output_store_condition = [
                [ 'store_id', '=', $output_store_id ]
            ];
            $output_store_info = $store_model->getStoreInfo($output_store_condition)[ 'data' ] ?? [];
            if (empty($output_store_info))
                return $this->success([], '找不到所选的门店');

            $output_store_name = $output_store_info[ 'store_name' ] ?? '';
            $input_store_condition = [
                [ 'store_id', '=', $input_store_id ]
            ];
            $input_store_info = $store_model->getStoreInfo($input_store_condition)[ 'data' ] ?? [];
            if (empty($input_store_info))
                return $this->success([], '找不到所选的门店');

            $operater = $params[ 'operater' ];

            $goods_sku_list_array_result = $this->getSkuArray($goods_sku_list, $output_store_id);

            if ($goods_sku_list_array_result[ 'code' ] < 0) {
                model('stock_allot')->rollback();
                return $goods_sku_list_array_result;
            }
            $goods_sku_list_array = $goods_sku_list_array_result[ 'data' ];
            $input_store_name = $input_store_info[ 'store_name' ] ?? '';
            $allot_no = isset($params['allot_no']) && $params['allot_no'] ? $params['allot_no'] : $this->getAllotNo();

            $count = model('stock_allot')->getCount([ ['allot_no', '=', $allot_no] ]);
            if($count > 0) return $this->error([], '录入失败，单号重复');

            $user_model = new User();
            $user_info = $user_model->getUserInfo([ [ 'uid', '=', $operater ] ])[ 'data' ] ?? [];
            $goods_money = getArraySum($goods_sku_list, 'cost_price', 'goods_num');
            $data = [
                'site_id' => $site_id,
                'output_store_id' => $output_store_id,
                'output_store_name' => $output_store_name,
                'input_store_id' => $input_store_id,
                'input_store_name' => $input_store_name,
                'allot_time' => $allot_time,
                'goods_money' => $goods_money,
                'allot_no' => $allot_no,
                'remark' => $remark,
                'status' => 1,
                'create_time' => time(),
                'operater' => $operater,
                'operater_name' => $user_info[ 'username' ] ?? ''
            ];
            $allot_id = model('stock_allot')->add($data);
            $common_data = [
                'site_id' => $site_id,
                'allot_id' => $allot_id,
                'create_time' => time()
            ];
            foreach ($goods_sku_list_array as $k => $v) {
                $item_data = array_merge($common_data, $v);
                model('stock_allot_goods')->add($item_data);
            }

            $is_auto_audit = $params[ 'is_auto_audit' ] ?? true;
            if ($is_auto_audit) {
                //主动调用审核
                $result = $this->audit([
                    'allot_id' => $allot_id,
                    'site_id' => $site_id
                ]);
                if ($result[ 'code' ] < 0) {
                    model('stock_allot')->rollback();
                    return $result;
                }
            } else {
                $stock_model = new Stock();
                $stock_config = $stock_model->getStockConfig($site_id)[ 'data' ][ 'value' ];
                $is_audit = $stock_config[ 'is_audit' ];
                if (!$is_audit) {
                    $result = $this->audit([
                        'allot_id' => $allot_id,
                        'site_id' => $site_id
                    ]);
                    if ($result[ 'code' ] < 0) {
                        model('stock_allot')->rollback();
                        return $result;
                    }
                }
            }

            model('stock_allot')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('stock_allot')->rollback();
            return $this->error([$e->getFile(),$e->getLine(),$e->getMessage()], $e->getMessage());
        }
    }
   /**
     * 修改库存调拨
     * @param $params
     * @return array|mixed
     */
    public function editAllot($params)
    {
        model('stock_allot')->startTrans();
        try {
            $output_store_id = $params[ 'output_store_id' ];//出库门店
            $input_store_id = $params[ 'input_store_id' ];//入库门店
            $site_id = $params[ 'site_id' ];
            $allot_time = $params[ 'allot_time' ] ?? time();
            $remark = $params[ 'remark' ] ?? '';
            $goods_sku_list = $params[ 'goods_sku_list' ];
            $allot_id = $params['allot_id'];
            $user_info = $params['user_info'];

            $condition = [
                [ 'allot_id', '=', $allot_id ],
                [ 'site_id', '=', $site_id ],
            ];
            $allot_info = model('stock_allot')->getInfo($condition);

            if ($allot_info[ 'status' ] == 2) {
                model('stock_allot')->rollback();
                return $this->error([], '已审核的单据不能编辑');
            }
            if ($allot_info[ 'operater' ] != $user_info[ 'uid' ]) {
                model('stock_allot')->rollback();
                return $this->error([], '只有单据创建者可以编辑单据');
            }

            //查询门店名称信息
            $store_model = new Store();
            $output_store_condition = [
                [ 'store_id', '=', $output_store_id ]
            ];
            $output_store_info = $store_model->getStoreInfo($output_store_condition)[ 'data' ] ?? [];
            if (empty($output_store_info))
                return $this->success([], '找不到所选的门店');

            $output_store_name = $output_store_info[ 'store_name' ] ?? '';
            $input_store_condition = [
                [ 'store_id', '=', $input_store_id ]
            ];
            $input_store_info = $store_model->getStoreInfo($input_store_condition)[ 'data' ] ?? [];
            if (empty($input_store_info))
                return $this->success([], '找不到所选的门店');

            $goods_sku_list_array_result = $this->getSkuArray($goods_sku_list, $output_store_id);

            if ($goods_sku_list_array_result[ 'code' ] < 0) {
                model('stock_allot')->rollback();
                return $goods_sku_list_array_result;
            }
            $goods_sku_list_array = $goods_sku_list_array_result[ 'data' ];
            $input_store_name = $input_store_info[ 'store_name' ] ?? '';
            $allot_no = isset($params['allot_no']) && $params['allot_no'] ? $params['allot_no'] : $this->getAllotNo();

            $count = model('stock_allot')->getCount([ ['allot_no', '=', $allot_no], ['allot_id', '<>', $allot_id] ]);
            if($count > 0) return $this->error([], '录入失败，单号重复');
            $goods_money = getArraySum($goods_sku_list, 'cost_price', 'goods_num');

            $data = [
                'output_store_id' => $output_store_id,
                'output_store_name' => $output_store_name,
                'input_store_id' => $input_store_id,
                'input_store_name' => $input_store_name,
                'allot_time' => $allot_time,
                'allot_no' => $allot_no,
                'remark' => $remark,
                'status' => 1,
                'goods_money' => $goods_money
            ];
            model('stock_allot')->update($data, [ ['site_id', '=', $site_id], ['allot_id', '=', $allot_id] ]);
            //删除原有的单据商品
            model('stock_allot_goods')->delete([ 'allot_id' => $allot_id ]);
            $common_data = [
                'site_id' => $site_id,
                'allot_id' => $allot_id,
                'create_time' => time()
            ];
            foreach ($goods_sku_list_array as $k => $v) {
                $item_data = array_merge($common_data, $v);
                model('stock_allot_goods')->add($item_data);
            }

            $is_auto_audit = $params[ 'is_auto_audit' ] ?? true;

            if ($is_auto_audit) {
                //主动调用审核
                $result = $this->audit([
                    'allot_id' => $allot_id,
                    'site_id' => $site_id
                ]);
                if ($result[ 'code' ] < 0) {
                    model('stock_allot')->rollback();
                    return $result;
                }
            } else {
                $stock_model = new Stock();
                $stock_config = $stock_model->getStockConfig($site_id)[ 'data' ][ 'value' ];
                $is_audit = $stock_config[ 'is_audit' ];
                if (!$is_audit) {
                    $result = $this->audit([
                        'allot_id' => $allot_id,
                        'site_id' => $site_id
                    ]);
                    if ($result[ 'code' ] < 0) {
                        model('stock_allot')->rollback();
                        return $result;
                    }
                }
            }

            model('stock_allot')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('stock_allot')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    public function getSkuArray($goods_sku_list, $store_id)
    {
        $sku_ids = array_column($goods_sku_list, 'goods_sku_id');
        $goods_nums = array_column($goods_sku_list, 'goods_num', 'goods_sku_id');
        $stock_model = new StockModel();
        $condition = [
            ['gs.sku_id', 'in', $sku_ids],
        ];
        $field = 'g.goods_class,gs.sku_id,gs.goods_id,gs.sku_image,gs.sku_name,gs.unit,gs.sku_no,gs.spec_name,sgs.stock,sgs.real_stock,sgs.price,gs.cost_price';
        $goods_sku_list = $stock_model->getStoreGoodsSkuPage($condition, $field, 'gs.create_time desc, gs.sku_id desc', $store_id, 1, 0)['data']['list'];

        $goods_sku_list_array = [];
        foreach ($goods_sku_list as $goods_sku_info) {
            $goods_sku_id = $goods_sku_info[ 'sku_id' ];
            $goods_num = $goods_nums[$goods_sku_id] ?? 0;
            $goods_class = $goods_sku_info['goods_class'];
            if($goods_class == GoodsDict::weigh && !preg_match(config('regexp.>0float3'), $goods_num)){
                return $this->error(null, '[' . $goods_sku_info[ 'sku_name' ] . ']调拨数量必须为正数且最多保留三位小数！');
            }else if($goods_class != GoodsDict::weigh && !preg_match(config('regexp.>0num'), $goods_num)){
                return $this->error(null, '[' . $goods_sku_info[ 'sku_name' ] . ']调拨数量必须为正整数！');
            }else if($goods_num > $goods_sku_info['real_stock']){
                return $this->error(null, '[' . $goods_sku_info[ 'sku_name' ] . ']可调拨数量不足！');
            }

            $goods_sku_list_array[] = [
                'goods_id' => $goods_sku_info[ 'goods_id' ],
                'goods_sku_id' => $goods_sku_info[ 'sku_id' ],
                'goods_sku_name' => $goods_sku_info[ 'sku_name' ],
                'goods_sku_no' => $goods_sku_info[ 'sku_no' ],
                'goods_sku_img' => $goods_sku_info[ 'sku_image' ],
                'goods_sku_spec' => $goods_sku_info[ 'spec_name' ],
                'goods_unit' => $goods_sku_info[ 'unit' ] ? $goods_sku_info[ 'unit' ] : '件',
                'goods_num' => $goods_num,
                'goods_price' => $goods_sku_info[ 'cost_price' ],
                'goods_remark' => '',
            ];
        }

        return $this->success($goods_sku_list_array);
    }

    /**
     * 审核
     * @param $params
     * @return array|mixed
     */
    public function audit($params)
    {
        $site_id = $params[ 'site_id' ];
        $allot_id = $params[ 'allot_id' ];
        $condition = [
            [ 'site_id', '=', $site_id ],
            [ 'allot_id', '=', $allot_id ]
        ];
        $info = model('stock_allot')->getInfo($condition);
        if (empty($info)) {
            return $this->error([], '调拨单据不存在');
        }

        $allot_data = [
            'verify_time' => time(),
            'status' => 2
        ];

        model('stock_allot')->update($allot_data, $condition);
        $result = $this->complete($params);
        if ($result[ 'code' ] < 0)
            return $result;
        return $this->success();
    }

    /**
     * 拒绝审核
     * @param $params
     * @return array
     */
    public function refuse($params)
    {
        $allot_id = $params[ 'allot_id' ];
        $site_id = $params[ 'site_id' ];
        $user_info = $params[ 'user_info' ];
        $condition = [
            [ 'allot_id', '=', $allot_id ],
            [ 'site_id', '=', $site_id ]
        ];
        $info = model('stock_allot')->getInfo($condition);
        if (empty($info))
            return $this->error([], '找不到可审核的单据');

        if ($info[ 'status' ] == 2)
            return $this->error([], '当前单据已审核');

        $data = [
            'status' => -1,
            'verify_time' => time(),
            'refuse_reason' => $params[ 'refuse_reason' ],
            'verifier' => $user_info[ 'uid' ] ?? 0,//审核人
            'verifier_name' => $user_info[ 'username' ] ?? '系统'
        ];
        model('stock_allot')->update($data, $condition);
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
        $allot_id = $params[ 'allot_id' ];
        $user_info = $params[ 'user_info' ];
        $store_id = $params[ 'store_id' ] ?? 0;
        //查询单据
        $condition = [
            [ 'allot_id', '=', $allot_id ],
            [ 'site_id', '=', $site_id ],
        ];
        if ($store_id > 0) {
            $condition[] = [ 'store_id', '=', $store_id ];
        }
        $allot_info = model('stock_allot')->getInfo($condition);
        //被拒绝也可以删除
        if ($allot_info[ 'status' ] == 2) {
            return $this->error('已审核的单据不能删除');
        }
        if ($allot_info[ 'operater' ] != $user_info[ 'uid' ]) {
            return $this->error('只有单据创建者可以删除单据');
        }
        model('stock_allot')->delete($condition);
        model('stock_allot_goods')->delete($condition);
        return $this->success();
    }

    public function complete($params)
    {
        $allot_id = $params[ 'allot_id' ];
        $condition = [
            [ 'allot_id', '=', $allot_id ]
        ];
        $info = model('stock_allot')->getInfo($condition);
        if (empty($info)) {
            return $this->error([], '调拨单据不存在');
        }
        $result = $this->setStock($info);
        if ($result[ 'code' ] < 0)
            return $result;
        return $this->success();
    }

    /**
     * 整理调拨单据
     * @param $params
     * @return array|mixed
     */
    public function setStock($params)
    {
        $site_id = $params[ 'site_id' ];
        $allot_id = $params[ 'allot_id' ];
        $allot_goods_condition = [
            [ 'allot_id', '=', $allot_id ]
        ];
        $allot_goods_list = model('stock_allot_goods')->getList($allot_goods_condition);
        $output_store_id = $params[ 'output_store_id' ];
        $input_store_id = $params[ 'input_store_id' ];
        $remark = $params[ 'remark' ];
        $allot_time = $params[ 'allot_time' ];
        $operater = $params[ 'operater' ];
        $operater_name = $params[ 'operater_name' ];
        foreach ($allot_goods_list as $k => $goods_sku_item) {
            $allot_goods_id = $goods_sku_item[ 'allot_goods_id' ];
            $item_sku_id = $goods_sku_item[ 'goods_sku_id' ];
            $item_goods_id = $goods_sku_item[ 'goods_id' ];

            $goods_num = numberFormat($goods_sku_item[ 'goods_num' ]);

            $sku_condition = [
                [ 'sku_id', '=', $item_sku_id ]
            ];
            $goods_sku_info = model('goods_sku')->getInfo($sku_condition);

            $goods_price = $goods_sku_info[ 'cost_price' ];
            $output_store_sku_condition = [
                [ 'store_id', '=', $output_store_id ],
                [ 'goods_id', '=', $item_goods_id ],
                [ 'sku_id', '=', $item_sku_id ]
            ];
            $output_store_sku_info = model('store_goods_sku')->getInfo($output_store_sku_condition);

            $output_store_stock = $output_store_sku_info[ 'real_stock' ] ?? 0;
            $input_store_sku_condition = [
                [ 'store_id', '=', $input_store_id ],
                [ 'goods_id', '=', $item_goods_id ],
                [ 'sku_id', '=', $item_sku_id ]
            ];
            $input_store_sku_info = model('store_goods_sku')->getInfo($input_store_sku_condition);

            $input_store_stock = $input_store_sku_info[ 'real_stock' ] ?? 0;
            $total_goods_money = $goods_num * $goods_price;
            $update_data = [
                'output_store_stock' => numberFormat($output_store_stock),
                'input_store_stock' => numberFormat($input_store_stock),
                'goods_price' => $goods_price,
                'total_goods_money' => $total_goods_money,
            ];
            $update_condition = [
                [ 'allot_goods_id', '=', $allot_goods_id ]
            ];
            model('stock_allot_goods')->update($update_data, $update_condition);

            $goods_money = model('stock_allot_goods')->getSum([ [ 'site_id', '=', $site_id ], [ 'allot_id', '=', $allot_id ] ], 'total_goods_money');

            model('stock_allot')->update([ 'goods_money' => $goods_money ], [
                [ 'site_id', '=', $site_id ],
                [ 'allot_id', '=', $allot_id ]
            ]);

            //新增门店出库
            $goods_sku_list = [
                [
                    'goods_sku_id' => $item_sku_id,
                    'goods_id' => $item_goods_id,
                    'goods_num' => $goods_num,
                    'goods_price' => $goods_price
                ]
            ];

            $user_info = [
                'uid' => $operater,
                'username' => $operater_name,
            ];
            $document_model = new Document();
            $document_params = [
                'allot_id' => $allot_id,
                'store_id' => $output_store_id,
                'user_info' => $user_info,
                'site_id' => $site_id,
                'is_auto_audit' => false,//调拨的单据如果开始审核开关的话就需要审核
            ];
            $document_params[ 'document_type' ] = 'ALLOTPUT';
            $document_params[ 'goods_sku_list' ] = $goods_sku_list;
            $document_params[ 'is_auto_audit' ] = 1;

            $out_result = $document_model->addDocument(array_merge($document_params, [ 'is_out_stock' => 1 ]));
            if ($out_result[ 'code' ] < 0) {
                return $out_result;
            }

            $document_params[ 'store_id' ] = $input_store_id;
            $document_params[ 'document_type' ] = 'ALLOTIN';
            $out_result = $document_model->addDocument($document_params);
            if ($out_result[ 'code' ] < 0) {
                return $out_result;
            }
        }

        return $this->success();
    }

    /**
     * 获取调拨单分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getStockAllotPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*', $alias = '', $join = '')
    {
        $document_list = model('stock_allot')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        return $this->success($document_list);
    }

    /**
     * 单据详情
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getAllotInfo($condition = [], $field = '*')
    {
        $info = model('stock_allot')->getInfo($condition, $field);
        if (!empty($info)) {

            $document_model = new Document();

            // 查询入库单
            $info[ 'input_info' ] = $document_model->getDocumentInfo([ [ 'site_id', '=', $info[ 'site_id' ] ], [ 'allot_id', '=', $info[ 'allot_id' ] ], [ 'type', '=', StockDict::input ], [ 'store_id', '=', $info[ 'input_store_id' ] ] ], 'operater_name,verifier_name,status,refuse_reason,create_time,audit_time')[ 'data' ];

            // 查询出库单
            $info[ 'out_info' ] = $document_model->getDocumentInfo([ [ 'site_id', '=', $info[ 'site_id' ] ], [ 'allot_id', '=', $info[ 'allot_id' ] ], [ 'type', '=', StockDict::output ], [ 'store_id', '=', $info[ 'output_store_id' ] ] ], 'operater_name,verifier_name,status,refuse_reason,create_time,audit_time')[ 'data' ];

            //单据产品项
            $goods_list = model('stock_allot_goods')->getList([
                'allot_id' => $info[ 'allot_id' ]
            ]);
            foreach ($goods_list as $k => $v) {
                $goods_list[ $k ][ 'goods_num' ] = numberFormat($v[ 'goods_num' ]);
                $goods_list[ $k ][ 'output_store_stock' ] = numberFormat($goods_list[ $k ][ 'output_store_stock' ]);
                $goods_list[ $k ][ 'input_store_stock' ] = numberFormat($goods_list[ $k ][ 'input_store_stock' ]);
                $info[ 'goods_unit' ] = $v[ 'goods_unit' ];
            }
            $info[ 'goods_sku_list_array' ] = $goods_list;
            $info[ 'goods_price' ] = 0;
            $info[ 'goods_total_price' ] = 0.00;
            foreach ($goods_list as $key => $value) {
                $info[ 'goods_sku_list_array' ][ $key ][ 'goods_sum' ] = floatval($value[ 'goods_num' ] * $value[ 'goods_price' ]);
                $info[ 'goods_price' ] += numberFormat($value[ 'goods_num' ]);
                $info[ 'goods_total_price' ] += $info[ 'goods_sku_list_array' ][ $key ][ 'goods_sum' ];
            }
            $info[ 'goods_count' ] = count($info[ 'goods_sku_list_array' ]);
        }
        return $this->success($info);
    }

    /**
     * 获取编辑单据数据
     * @param array $condition
     * @return array
     */
    public function getAllotEditData($condition = [])
    {
        $field = 'site_id,allot_id,output_store_id,input_store_id,allot_no,remark,allot_time';
        $allot_info = $this->getAllotInfo($condition, $field)[ 'data' ];
        if (!empty($allot_info)) {
            $goods_field = 'gs.sku_id,gs.sku_image,gs.sku_name,gs.unit,gs.sku_no,
            sgs.stock,sgs.real_stock,sgs.price,sgs.cost_price,
            ig.goods_num,ig.goods_sku_id';

            $join = [
                [ 'goods_sku gs', 'ig.goods_sku_id = gs.sku_id', 'left' ],
                [
                    'store_goods_sku sgs',
                    'sgs.sku_id = gs.sku_id and (sgs.store_id is null or sgs.store_id = ' . $allot_info[ 'output_store_id' ] . ')',
                    'left'
                ]
            ];

            $allot_goods_list = model('stock_allot_goods')->getList([
                [ 'allot_id', '=', $allot_info[ 'allot_id' ] ]
            ], $goods_field, '', 'ig', $join);
            foreach ($allot_goods_list as $k => $v) {
                $allot_goods_list[ $k ][ 'stock' ] = numberFormat($v[ 'stock' ]);
                $allot_goods_list[ $k ][ 'goods_num' ] = numberFormat($allot_goods_list[ $k ][ 'goods_num' ]);
            }
            $allot_info[ 'goods_list' ] = array_column($allot_goods_list, null, 'goods_sku_id');
        }
        return $this->success($allot_info);
    }

}

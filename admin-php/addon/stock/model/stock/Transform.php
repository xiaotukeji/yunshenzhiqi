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

use app\dict\goods\GoodsDict;
use app\model\BaseModel;

/**
 * 库存转换
 * Class Transform
 * @package addon\stock\model\stock
 */
class Transform extends BaseModel
{
    /**
     * 添加库存转换
     * @param $data
     * @return array
     */
    public function addTransform($data)
    {
        try{
            //数据检测
            if(count($data['goods_list']) < 2){
                return $this->error(null, '转换商品至少要有两种');
            }
            $sku_ids = array_column($data['goods_list'], 'sku_id');
            $sku_count = model('stock_transform_goods')->getCount([['sku_id', 'in', $sku_ids]]);
            if($sku_count > 0){
                return $this->error(null, '转换商品在其他转换组已存在');
            }
            $transform_id = 0;

            //转换组和转换商品数据
            $transform_data = [
                'name' => $data['name'],
                'create_time' => time(),
            ];
            $transform_goods_data = [];
            foreach($data['goods_list'] as $goods_info){
                $transform_goods_data[] = [
                    'transform_id' => &$transform_id,
                    'sku_id' => $goods_info['sku_id'],
                    'num' => $goods_info['num'],
                ];
            }
        }catch(\Exception $e){
            return $this->error(exceptionData($e), '添加库存转换错误');
        }


        model('stock_transform')->startTrans();
        try{
            $transform_id = model('stock_transform')->add($transform_data);
            model('stock_transform_goods')->addList($transform_goods_data);

            model('stock_transform')->commit();
            return $this->success();
        }catch(\Exception $e){
            model('stock_transform')->rollback();
            return $this->error(exceptionData($e), '添加库存转换错误');
        }
    }

    /**
     * 修改库存转换
     * @param $data
     * @return array
     */
    public function editTransform($data)
    {
        try{
            //数据检测
            if(count($data['goods_list']) < 2){
                return $this->error(null, '转换商品至少要有两种');
            }
            $transform_id = $data['transform_id'];
            $sku_ids = array_column($data['goods_list'], 'sku_id');
            $sku_count = model('stock_transform_goods')->getCount([['sku_id', 'in', $sku_ids], ['transform_id', '<>', $transform_id]]);
            if($sku_count > 0){
                return $this->error(null, '转换商品在其他转换组已存在');
            }

            //转换组和转换商品数据
            $transform_data = [
                'name' => $data['name'],
                'update_time' => time(),
            ];
            $transform_goods_data = [];
            foreach($data['goods_list'] as $goods_info){
                $transform_goods_data[] = [
                    'transform_id' => $transform_id,
                    'sku_id' => $goods_info['sku_id'],
                    'num' => $goods_info['num'],
                ];
            }
        }catch(\Exception $e){
            return $this->error(exceptionData($e), '修改库存转换错误');
        }

        model('stock_transform')->startTrans();
        try{
            model('stock_transform')->update($transform_data, [['transform_id', '=', $transform_id]]);
            model('stock_transform_goods')->delete([['transform_id', '=', $transform_id]]);
            model('stock_transform_goods')->addList($transform_goods_data);

            model('stock_transform')->commit();
            return $this->success();
        }catch(\Exception $e){
            model('stock_transform')->rollback();
            return $this->error(exceptionData($e), '修改库存转换错误');
        }
    }

    /**
     * 删除库存转换
     * @param $transform_ids
     * @return array
     */
    public function deleteTransform($transform_ids)
    {
        model('stock_transform')->startTrans();
        try{
            model('stock_transform')->delete([['transform_id', 'in', $transform_ids]]);
            model('stock_transform_goods')->delete([['transform_id', 'in', $transform_ids]]);

            model('stock_transform')->commit();
            return $this->success();
        }catch(\Exception $e){
            model('stock_transform')->rollback();
            return $this->error(exceptionData($e), '删除库存转换错误');
        }
    }

    /**
     * 获取库存转换分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @param string $alias
     * @param array $join
     * @param null $group
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getTransformPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*', $alias = 'a', $join = [], $group = null)
    {
        $res = model('stock_transform')->pageList($condition, $field, $order, $page, $page_size, $alias, $join, $group);
        return $this->success($res);
    }

    /**
     * 获取库存转换列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $alias
     * @param array $join
     * @param null $group
     * @param null $limit
     * @return array
     */
    public function getTransformList($condition = [], $field = '', $order = '', $alias = '', $join = [], $group = null, $limit = null)
    {
        $list = model('stock_transform')->getList($condition, $field, $order, $alias, $join, $group, $limit);
        return $this->success($list);
    }

    public function getTransformInfo($condition, $field = '*')
    {
        $info = model('stock_transform')->getInfo($condition, $field);
        return $this->success($info);
    }

    /**
     * 获取库存转换商品数据
     * @param $list
     * @return array
     */
    public function getTransformGoodsData(Array $list)
    {
        $list = array_column($list, null, 'transform_id');
        $transform_ids = array_column($list, 'transform_id');
        $alias = 'stg';
        $join = [['goods_sku gs', 'gs.sku_id = stg.sku_id', 'inner']];
        $condition = [['stg.transform_id', 'in', $transform_ids]];
        $field = 'stg.*,gs.sku_name,gs.sku_image,gs.sku_no,gs.stock,gs.goods_class';
        $order = 'stg.transform_id asc,stg.num asc';
        $goods_list = model('stock_transform_goods')->getList($condition, $field, $order, $alias, $join);
        foreach($goods_list as $goods_info){
            $transform_id = $goods_info['transform_id'];
            if(!isset($list[$transform_id])){
                $list[$transform_id]['goods_list'] = [];
            }
            $goods_info['stock'] = numberFormat($goods_info['stock']);
            $goods_info['transform_stock'] = $goods_info['stock'] * $goods_info['num'];
            $list[$transform_id]['goods_list'][] = $goods_info;
        }

        foreach($list as &$info){
            $info['transform_stock'] = array_sum(array_column($info['goods_list'], 'transform_stock'));
            foreach($info['goods_list'] as &$goods_info){
                $transform_stock = $info['transform_stock'] / $goods_info['num'];
                if($goods_info['goods_class'] == GoodsDict::weigh){
                    $transform_stock = numberFormat($transform_stock);
                }else{
                    $transform_stock = floor($transform_stock);
                }
                $goods_info['transform_stock'] = $transform_stock;
            }
        }
        return array_values($list);
    }

    /**
     * 获取商品转换后库存数据
     * @param $param
     * @return array
     */
    public function getGoodsStockTransformData($param)
    {
        $sku_ids = $param['sku_ids'];
        $store_id = $param['store_id'];
        $store_business = $param['store_business'];

        //获取库存转换设置和商品类型数据
        $alias = 'stg';
        $join = [
            ['goods_sku gs', 'gs.sku_id = stg.sku_id', 'inner'],
        ];
        $condition = [
            ['stg.sku_id', 'in', $sku_ids],
        ];
        $field = 'stg.*,gs.goods_class';
        $goods_list = model('stock_transform_goods')->getList($condition, $field, '', $alias, $join);
        $transform_ids = array_unique(array_column($goods_list, 'transform_id'));

        $transform_stock_data = [];
        if(!empty($transform_ids)){
            //所有涉及的库存转换商品
            $alias = 'stg';
            $join = [
                ['goods_sku gs', 'gs.sku_id = stg.sku_id', 'inner'],
                ['goods g', 'g.goods_id = gs.goods_id', 'inner'],
            ];
            $condition = [
                ['stg.transform_id', 'in', $transform_ids],
            ];
            $field = 'stg.transform_id,stg.sku_id,stg.num,gs.stock,gs.unit,gs.sku_name,gs.cost_price';
            $order = 'stg.transform_id asc,stg.num asc';
            if($store_business == 'store'){
                $join[] = [ 'store_goods_sku sgs', 'gs.sku_id = sgs.sku_id and sgs.store_id=' . $store_id, 'left' ];
                //TODO 这里不需要加可售门店的条件，但是还需要进一步的思考
                //$condition[] = [ 'g.sale_store', 'like', [ '%all%', '%,' . $store_id . ',%' ], 'or' ];
                $store_info = model('store')->getInfo([['store_id', '=', $store_id]], 'stock_type');
                if ($store_info[ 'stock_type' ] == 'store') {
                    $field = str_replace('gs.stock', 'IFNULL(sgs.stock, 0) as stock', $field);
                    $field = str_replace('gs.cost_price', 'IFNULL(sgs.cost_price, gs.cost_price) as cost_price', $field);
                }
            }
            $all_goods_list = model('stock_transform_goods')->getList($condition, $field, $order, $alias, $join);
            //库存按照转换设置汇总
            $transform_list = [];
            foreach($all_goods_list as $goods_info){
                if(!isset($transform_list[$goods_info['transform_id']])){
                    $transform_list[$goods_info['transform_id']] = [
                        'total_transform_stock' => 0,
                        'goods_list' => [],
                    ];
                }
                $transform_list[$goods_info['transform_id']]['total_transform_stock'] += $goods_info['num']*$goods_info['stock'];
                $transform_list[$goods_info['transform_id']]['goods_list'][] = $goods_info;
            }
            //组装数据
            foreach($goods_list as $goods_info){
                $total_transform_stock = $transform_list[$goods_info['transform_id']]['total_transform_stock'] ?? null;
                if(!is_null($total_transform_stock)){
                    $transform_stock = $total_transform_stock / $goods_info['num'];
                    if($goods_info['goods_class'] == GoodsDict::weigh){
                        $transform_stock = numberFormat($transform_stock);
                    }else{
                        $transform_stock = floor($transform_stock);
                    }
                    $goods_info['transform_stock'] = $transform_stock;
                    $goods_info['transform_info'] = $transform_list[$goods_info['transform_id']];
                    $transform_stock_data[$goods_info['sku_id']] = $goods_info;
                }
            }
        }
        return $transform_stock_data;
    }

    /**
     * 自动库存转换
     * @param $param
     * @return array|int
     */
    public function autoGoodsStockTransform($param)
    {
        $transform_data = $param['transform_data'];
        $buy_num = $param['buy_num'];
        $site_id = $param['site_id'];
        $store_id = $param['store_id'];
        $sku_id = $transform_data['sku_id'];

        $goods_list = $transform_data['transform_info']['goods_list'];
        $goods_list = array_column($goods_list, null, 'sku_id');
        $curr_goods_info = $goods_list[$sku_id] ?? null;
        unset($goods_list[$sku_id]);
        if(is_null($curr_goods_info)){
            return $this->error(null, '库存转换商品查找失败');
        }

        //如果是门店独立库存，是当前门店转换库存；否则就是总部转换库存。
        $default_store_info = model('store')->getInfo([['is_default', '=', 1]], 'store_id');
        $store_info = model('store')->getInfo([['store_id', '=', $store_id]], 'stock_type');
        $stock_store_id = $store_info['stock_type'] == 'store' ? $store_id : $default_store_info['store_id'];

        $need_transform_num = $buy_num - $curr_goods_info['stock'];
        if($need_transform_num > 0){
            $document_model = new Document();
            foreach($goods_list as $goods_info){
                if($goods_info['stock'] > 0){
                    $least_common_multiple = getLeastCommonMultiple($curr_goods_info['num'], $goods_info['num']);
                    $least_input_num = $least_common_multiple / $curr_goods_info['num'];
                    $least_output_num = $least_common_multiple / $goods_info['num'];
                    if($need_transform_num/$least_input_num <= $goods_info['stock']/$least_output_num){
                        $num_multiple = ceil($need_transform_num/$least_input_num);
                    }else{
                        $num_multiple = floor($goods_info['stock']/$least_output_num);
                    }
                    if($num_multiple > 0){
                        $input_num = $num_multiple*$least_input_num;
                        $output_num = $num_multiple*$least_output_num;
                        $input_unit = $curr_goods_info['unit'] ?: '件';
                        $output_unit = $goods_info['unit'] ?: '件';
                        //转换入库
                        $document_params = [
                            'site_id' => $site_id,
                            'store_id' => $stock_store_id,
                            'user_info' => [],
                            'goods_sku_list' => [
                                ['goods_sku_id' => $curr_goods_info['sku_id'], 'goods_num' => $input_num, 'goods_price' => $curr_goods_info['cost_price']],
                            ],
                            'remark' => "用{$output_num}{$output_unit}[{$goods_info['sku_name']}]自动转换",
                            'time' => time(),
                        ];
                        $document_params[ 'is_auto_audit' ] = true;
                        $result = $document_model->addTransformInput($document_params);

                        if($result['code'] < 0) return $result;
                        //转换出库
                        $document_params = [
                            'site_id' => $site_id,
                            'store_id' => $stock_store_id,
                            'user_info' => [],
                            'goods_sku_list' => [
                                ['goods_sku_id' => $goods_info['sku_id'], 'goods_num' => $output_num, 'goods_price' => $goods_info['cost_price']],
                            ],
                            'remark' => "自动转换为{$input_num}{$input_unit}[{$curr_goods_info['sku_name']}]",
                            'time' => time(),
                            'is_out_stock' => 1,
                        ];
                        $document_params[ 'is_auto_audit' ] = true;
                        $result = $document_model->addTransformOutput($document_params);
                        if($result['code'] < 0) return $result;
                        //判断是否继续转换
                        $need_transform_num -= $input_num;
                        if($need_transform_num <= 0) break;
                    }
                }
            }
        }
        if($need_transform_num > 0){
            return $this->error(null, '库存转换失败');
        }
        return $this->success();
    }
}

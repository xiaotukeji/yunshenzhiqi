<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\store\model;

use app\model\BaseModel;
use app\model\goods\Goods;

class StoreGoodsSku extends BaseModel
{
    /**
     * 添加店铺sku
     * @param $data
     * @return array
     */
    public function addStoreGoodsSku($data)
    {
        $res = model('store_goods_sku')->add($data);
        //判断当前有没有store_goods
        $store_goods_info = model('store_goods')->getInfo([ [ 'goods_id', '=', $data[ 'goods_id' ] ], [ 'store_id', '=', $data[ 'store_id' ] ] ], 'id');
        if (empty($store_goods_info)) {
            $store_goods_data = [
                'goods_id' => $data[ 'goods_id' ],
                'store_id' => $data[ 'store_id' ],
                'create_time' => time(),
            ];
            model('store_goods')->add($store_goods_data);
        }

        return $this->success($res);
    }

    /**
     * 获取门店商品sku详情
     * @param $condition
     * @param string $field
     * @return array
     */
    public function getStoreGoodsSkuInfo($condition, $field = '*')
    {
        $res = model('store_goods_sku')->getInfo($condition, $field);
        if (!empty($info)) {
            if (isset($info[ 'stock' ])) {
                $info[ 'stock' ] = numberFormat($info[ 'stock' ]);
            }
            if (isset($info[ 'sale_num' ])) {
                $info[ 'sale_num' ] = numberFormat($info[ 'sale_num' ]);
            }
            if (isset($info[ 'real_stock' ])) {
                $info[ 'real_stock' ] = numberFormat($info[ 'real_stock' ]);
            }
        }
        return $this->success($res);
    }

    /**
     * 增加库存
     * @param param
     */
    public function incStock($param)
    {

        $condition = array (
            [ 'store_id', '=', $param[ 'store_id' ] ],
            [ 'sku_id', '=', $param[ 'sku_id' ] ]
        );
        $num = $param[ 'stock' ];
        $store_sku_info = model('store_goods_sku')->getInfo($condition, 'id, goods_id');
        if (empty($store_sku_info))
            return $this->error(-1, "");

        //编辑sku库存
        $result = model('store_goods_sku')->setInc($condition, 'stock', $num);

        model('store_goods')->setInc([ [ 'goods_id', '=', $store_sku_info[ 'goods_id' ] ], [ 'store_id', '=', $param[ 'store_id' ] ] ], 'stock', $num);

        return $this->success($result);
    }

    /**
     * 减少库存
     * @param $param
     * @return array
     */
    public function decStock($param)
    {
        $condition = array (
            [ 'store_id', '=', $param[ 'store_id' ] ],
            [ 'sku_id', '=', $param[ 'sku_id' ] ]
        );
        $num = $param[ 'stock' ];
        $store_sku_info = model('store_goods_sku')->getInfo($condition, 'id, goods_id, stock');
        if (empty($store_sku_info)) {
            return $this->error(-1, '库存不足！');
        }
        $store_sku_info[ 'stock' ] = numberFormat($store_sku_info[ 'stock' ]);
        if (( $store_sku_info[ 'stock' ] - $num ) < 0) {
            return $this->error(-1, '库存不足！');
        }
        //编辑sku库存
        $result = model('store_goods_sku')->setDec($condition, 'stock', $num);
        model('store_goods')->setDec([ [ 'goods_id', '=', $store_sku_info[ 'goods_id' ] ], [ 'store_id', '=', $param[ 'store_id' ] ] ], 'stock', $num);
        return $this->success($result);
    }

    /**
     * 编辑门店商品库存信息
     * @param $goods_sku_array
     */
    public function editStock($store_goods_sku_array)
    {
        model('store_goods_sku')->startTrans();
        try {
            foreach ($store_goods_sku_array as $item) {
                $sku_info_result = $this->getStoreGoodsSkuInfo([ [ "store_id", "=", $item[ "store_id" ] ], [ "sku_id", "=", $item[ "sku_id" ] ] ], "sku_id");
                $sku_info = $sku_info_result[ "data" ];
                if (empty($sku_info)) {
                    $sku_data = array (
                        "goods_id" => $item[ "goods_id" ],
                        "sku_id" => $item[ "sku_id" ],
                        "store_id" => $item[ "store_id" ],
                    );
                    $this->addStoreGoodsSku($sku_data);
                }

                if ($item[ 'stock' ] > 0) {
                    $item_result = $this->incStock($item);
                } else {
                    $item[ 'stock' ] = abs($item[ 'stock' ]);
                    $item_result = $this->decStock($item);
                }
                if ($item_result[ "code" ] < 0) {
                    model('store_goods_sku')->rollback();
                    return $item_result;
                }
            }
            model('store_goods_sku')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('store_goods_sku')->rollback();
            return $this->error($e->getMessage());
        }
    }

    /**
     * 门店库存批量修改（导入excel）
     * @param $param
     * @param $site_id
     * @return array
     */
    public function importStoreGoodsStock($param, $site_id, $store_id)
    {

        $PHPReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        //载入文件
        $PHPExcel = $PHPReader->load($param[ 'path' ]);
        //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
        $currentSheet = $PHPExcel->getSheet(0);
        //获取总行数
        $allRow = $currentSheet->getHighestRow();
        if ($allRow < 2) {
            return $this->error('', '导入了一个空文件');
        }
        //添加文件上传记录
        $error_num = 0;
        $data = [
            'site_id' => $site_id,
            'store_id' => $store_id,
            'filename' => $param[ 'filename' ],
            'path' => $param[ 'path' ],
            'sku_num' => $allRow - 1,
            'create_time' => time()
        ];
        $res = model('store_stock_import')->add($data);
        if (!$res) {
            return $this->error('', '上传文件失败');
        }

        model('store_stock_import')->startTrans();
        try {

            for ($i = 2; $i <= $allRow; $i++) {
                //商品id
                $goods_id = $PHPExcel->getActiveSheet()->getCell('A' . $i)->getValue();
                $goods_id = trim($goods_id, ' ');
                //商品名称
                $goods_name = $PHPExcel->getActiveSheet()->getCell('B' . $i)->getValue();
                $goods_name = trim($goods_name, ' ');
                //sku_id
                $sku_id = $PHPExcel->getActiveSheet()->getCell('C' . $i)->getValue();
                $sku_id = trim($sku_id, ' ');
                //sku名称
                $sku_name = $PHPExcel->getActiveSheet()->getCell('D' . $i)->getValue();
                $sku_name = trim($sku_name, ' ');
                //库存（增/减）
                $stock = $PHPExcel->getActiveSheet()->getCell('F' . $i)->getValue();
                $stock = trim($stock, ' ');

                if (empty($goods_id) || empty($sku_id) || empty($stock)) {

                    $error_num += 1;
                    model('store_stock_import')->update([ 'error_num' => $error_num ], [ [ 'id', '=', $res ] ]);
                    //添加失败记录
                    model('store_stock_import_log')->add(
                        [
                            'store_id' => $store_id, 'file_id' => $res, 'goods_id' => $goods_id, 'goods_name' => $goods_name, 'sku_id' => $sku_id,
                            'sku_name' => $sku_name, 'stock' => $stock, 'status' => -1, 'reason' => '商品编号、sku编号或者库存为空'
                        ]
                    );
                    continue;
                }

                $goods_model = new Goods();
                $sku_info = $goods_model->getGoodsSkuInfo([ [ 'sku_id', '=', $sku_id ], [ 'goods_id', '=', $goods_id ], [ 'site_id', '=', $site_id ] ]);
                if (empty($sku_info[ 'data' ])) {
                    $error_num += 1;
                    model('store_stock_import')->update([ 'error_num' => $error_num ], [ [ 'id', '=', $res ] ]);
                    //添加失败记录
                    model('store_stock_import_log')->add(
                        [
                            'store_id' => $store_id, 'file_id' => $res, 'goods_id' => $goods_id, 'goods_name' => $goods_name, 'sku_id' => $sku_id,
                            'sku_name' => $sku_name, 'stock' => $stock, 'status' => -1, 'reason' => '商品不存在'
                        ]
                    );
                    continue;
                }

                $sku_info_result = $this->getStoreGoodsSkuInfo([ [ "store_id", "=", $store_id ], [ "sku_id", "=", $sku_id ] ], "sku_id");
                $sku_info = $sku_info_result[ "data" ];
                if (empty($sku_info)) {
                    $sku_data = array (
                        "goods_id" => $goods_id,
                        "sku_id" => $sku_id,
                        "store_id" => $store_id,
                    );
                    $this->addStoreGoodsSku($sku_data);
                }

                $store_data = [
                    'store_id' => $store_id,
                    'sku_id' => $sku_id,
                    'stock' => $stock
                ];
                if ($stock > 0) {
                    $item_result = $this->incStock($store_data);
                } else {
                    $store_data[ 'stock' ] = abs($store_data[ 'stock' ]);
                    $item_result = $this->decStock($store_data);
                }
                if ($item_result[ "code" ] < 0) {

                    $error_num += 1;
                    model('store_stock_import')->update([ 'error_num' => $error_num ], [ [ 'id', '=', $res ] ]);
                    //添加失败记录
                    model('store_stock_import_log')->add(
                        [
                            'store_id' => $store_id, 'file_id' => $res, 'goods_id' => $goods_id, 'goods_name' => $goods_name, 'sku_id' => $sku_id,
                            'sku_name' => $sku_name, 'stock' => $stock, 'status' => -1, 'reason' => $item_result[ 'message' ]
                        ]
                    );
                    continue;
                }
                //添加记录
                model('store_stock_import_log')->add(
                    [
                        'store_id' => $store_id, 'file_id' => $res, 'goods_id' => $goods_id, 'goods_name' => $goods_name, 'sku_id' => $sku_id,
                        'sku_name' => $sku_name, 'stock' => $stock, 'status' => 0, 'reason' => ''
                    ]
                );
            }

            model('store_stock_import')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('store_stock_import')->rollback();
            model('store_stock_import')->update([ 'error_num' => $allRow - 1 ], [ [ 'id', '=', $res ] ]);
            return $this->error('', $e->getMessage());
        }

    }

    /**
     * 核验门店库存是否足够
     * @param $params
     */
    public function checkStoreGoodsSkuStock($params)
    {
        $store_id = $params[ 'store_id' ];
        $goods_id = $params[ 'goods_id' ];
        $sku_id = $params[ 'sku_id' ];
        $site_id = $params[ 'site_id' ];
        $num = $params[ 'num' ];
        $sku_condition = array (
            [ 'goods_id', '=', $goods_id ],
            [ 'sku_id', '=', $sku_id ],
            [ 'store_id', '=', $store_id ],
        );
        $store_sku_info = model('store_goods_sku')->getInfo($sku_condition, 'stock');
        if (empty($store_sku_info)) {
            return $this->error(-1, '库存不足！');
        }
        $store_sku_info[ 'stock' ] = numberFormat($store_sku_info[ 'stock' ]);
        if (( $store_sku_info[ 'stock' ] - $num ) < 0) {
            return $this->error(-1, '库存不足！');
        }
        return $this->success();
    }
}
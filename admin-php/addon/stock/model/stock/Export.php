<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\stock\model\stock;

use app\model\BaseModel;
use think\facade\Db;

/**
 * 导出
 * @author Administrator
 */
class Export extends BaseModel
{

    public $field = [
        'store_name' => '门店名称',
        'sku_no' => '商品编码',
        'sku_name' => '商品名称',
        'stock' => '销售库存',
        'real_stock' => '实物库存',
        'price' => '销售价',
        'cost_price' => '成本价',
        'sale_num' => '销量',
        'goods_state' => '商品状态'
    ];

    public $store_name = '';


    /**
     * 查询商品项数据并导出
     * @param $condition
     * @param $condition_desc
     * @param $site_id
     * @param $store_id
     * @param $store_name
     * @return array
     */
    public function export($condition, $condition_desc, $site_id, $store_id, $store_name)
    {
        try {
            // 预先创建导出的记录
            $data = [
                'condition' => json_encode($condition_desc),
                'create_time' => time(),
                'status' => 0,
                'site_id' => $site_id
            ];
            $records_result = $this->addExport($data);

            $export_id = $records_result[ 'data' ] ?? 0;
            if ($export_id <= 0) {
                return $this->error();
            }

            $alias = 'gs';

            $field = $this->field;

            if ($store_id > 0) {
                $this->store_name = $store_name;
            } else {
                unset($field[ 'store_name' ]);
            }

            // 通过分批次执行数据导出(防止内存超出配置设置的)

            set_time_limit(0);
            $file_name = date('YmdHis');// csv文件名
            $file_path = 'upload/goods_csv/';
            if (dir_mkdir($file_path)) {
                $file_path = $file_path . $file_name . '.csv';
                //创建一个临时csv文件
                $fp = fopen($file_path, 'w'); // 生成临时文件
                fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF)); // 添加 BOM
                $field_value = [];
                $field_key = [];
                $field_key_array = [];
                // 为了防止部分代码被筛选中替换, 给变量前后两边增加字符串
                foreach ($field as $k => $v) {
                    $field_value[] = $v;
                    $field_key[] = "{\$$k}";
                    $field_key_array[] = $k;
                }

                $table_field = 'gs.sku_id, gs.sku_no, gs.sku_name, gs.goods_state';
                $join = [
                    [ 'goods g', 'g.goods_id = gs.goods_id', 'left' ]
                ];

                $group = '';
                if ($store_id > 0) {
                    $table_field .= ',sgs.price, sgs.sale_num,sgs.cost_price,ifnull(sgs.stock,0) as stock, ifnull(sgs.real_stock, 0) as real_stock,s.store_name';
                    $join[] = [ 'store_goods_sku sgs', 'sgs.sku_id = gs.sku_id and (sgs.store_id is null or sgs.store_id = ' . $store_id . ')', 'left' ];
                    $join[] = [ 'store s', 's.store_id = sgs.store_id', 'left' ];
                } else {
                    $join[] = [ 'store_goods_sku sgs', 'sgs.sku_id = gs.sku_id or sgs.sku_id is null', 'left' ];
                    $table_field .= ',gs.price, gs.sale_num,gs.cost_price,gs.stock, ifnull(sum(sgs.real_stock),0) as real_stock';
                    $group = 'gs.sku_id';
                }

                $table = Db::name('goods_sku')->where($condition)->alias($alias)->group($group)->order('g.create_time desc');

                $table = $this->parseJoin($table, $join);

                $first_line = implode(',', $field_value);
                // 写入第一行表头
                fwrite($fp, $first_line . "\n");

                $temp_line = implode(',', $field_key) . "\n";

                $table->field($table_field)->chunk(5000, function($item_list) use ($fp, $temp_line, $field_key_array) {
                    // 写入导出信息
                    $this->itemExport($item_list, $field_key_array, $temp_line, $fp);
                    unset($item_list);
                }, 'gs.sku_id');

                $table->removeOption();
                fclose($fp); // 每生成一个文件关闭
                unset($table);

                // 将同步导出记录状态
                $records_data = [
                    'path' => $file_path,
                    'status' => 1
                ];
                $records_condition = [
                    [ 'export_id', '=', $export_id ]
                ];
                $this->editExport($records_data, $records_condition);
                return $this->success();
            } else {
                return $this->error();
            }
        } catch (\Exception $e) {
            return $this->error([], $e->getMessage() . $e->getFile() . $e->getLine());
        }

    }

    /**
     * @param $db_obj
     * @param $join
     * @return mixed
     */
    public function parseJoin($db_obj, $join)
    {
        foreach ($join as $item) {
            list($table, $on, $type) = $item;
            $type = strtolower($type);
            switch ( $type ) {
                case 'left':
                    $db_obj = $db_obj->leftJoin($table, $on);
                    break;
                case 'inner':
                    $db_obj = $db_obj->join($table, $on);
                    break;
                case 'right':
                    $db_obj = $db_obj->rightjoin($table, $on);
                    break;
                case 'full':
                    $db_obj = $db_obj->fulljoin($table, $on);
                    break;
                default:
                    break;
            }
        }
        return $db_obj;
    }

    /**
     * 给csv写入新的数据
     * @param $item_list
     * @param $field_key
     * @param $temp_line
     * @param $fp
     */
    public function itemExport($item_list, $field_key, $temp_line, $fp)
    {
        $item_list = $item_list->toArray();
        foreach ($item_list as $k => $item_v) {
            $new_line_value = $temp_line;

            if (!empty($this->store_name)) {
                $item_v[ 'store_name' ] = $this->store_name;
            }

            if (isset($item_v[ 'num' ])) {
                $item_v[ 'num' ] = numberFormat($item_v[ 'num' ]);
            }

            if (isset($item_v[ 'stock' ])) {
                $item_v[ 'stock' ] = numberFormat($item_v[ 'stock' ]);
            }

            if (isset($item_v[ 'sale_num' ])) {
                $item_v[ 'sale_num' ] = numberFormat($item_v[ 'sale_num' ]);
            }
            if (isset($item_v[ 'real_stock' ])) {
                $item_v[ 'real_stock' ] = numberFormat($item_v[ 'real_stock' ]);
            }

            $item_v[ 'goods_state' ] = $item_v[ 'goods_state' ] == 1 ? '销售中' : '仓库中';

            foreach ($item_v as $key => $value) {
                $value = trim($value);

                //CSV比较简单，记得转义 逗号就好
                $values = str_replace(',', '\\', $value . "\t");
                $values = str_replace("\n", '', $values);
                $values = str_replace("\r", '', $values);
                $new_line_value = str_replace("{\$$key}", $values, $new_line_value);
            }

            // 写入第一行表头
            fwrite($fp, $new_line_value);

            // 销毁变量, 防止内存溢出
            unset($new_line_value);
        }
    }

    /**
     * 添加导出记录
     * @param $data
     * @return array
     */
    public function addExport($data)
    {
        $res = model('stock_goods_export')->add($data);
        return $this->success($res);
    }

    /**
     * 更新导出记录
     * @param $data
     * @param $condition
     * @return array
     */
    public function editExport($data, $condition)
    {
        $res = model('stock_goods_export')->update($data, $condition);
        return $this->success($res);
    }

    /**
     * 删除导出记录
     * @param $condition
     * @return array
     */
    public function deleteExport($condition)
    {
        // 先查询数据
        $list = model('stock_goods_export')->getList($condition, '*');
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                if (file_exists($v[ 'path' ])) {
                    // 删除物理文件路径
                    if (!unlink($v[ 'path' ])) {
                        // 失败
                    } else {
                        // 成功
                    }
                }
            }
            $res = model('stock_goods_export')->delete($condition);
        }

        return $this->success($res);
    }

    /**
     * 获取导出记录
     * @param $condition
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getExport($condition, $field = '*', $order = '')
    {
        $list = model('stock_goods_export')->getList($condition, $field, $order);
        return $this->success($list);
    }

    /**
     * 导出记录
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getExportPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*')
    {
        $list = model('stock_goods_export')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }
}

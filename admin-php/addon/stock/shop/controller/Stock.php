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

namespace addon\stock\shop\controller;

use addon\stock\dict\StockDict;
use addon\stock\model\stock\Allot;
use addon\stock\model\stock\Document;
use addon\stock\model\stock\Export;
use addon\stock\model\stock\Import;
use addon\stock\model\stock\Inventory;
use addon\stock\model\stock\Stock as StockModel;
use addon\stock\model\Store;
use app\dict\goods\GoodsDict;
use app\model\goods\GoodsCategory;
use app\model\goods\GoodsCategory as GoodsCategoryModel;
use app\model\goods\GoodsLabel;
use app\model\upload\Upload as UploadModel;
use app\model\goods\Goods;

/**
 * 库存管理
 * Class Stock
 * @package addon\stock\shop\controller
 */
class Stock extends Base
{
    /**
     * 库存列表
     * @return mixed
     */
    public function manage()
    {
        if (request()->isJson()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $search = input('search_text', ''); // 名称或编码
            $category_id = input('category_id', '');
            $min_stock = input('min_stock', 0);
            $max_stock = input('max_stock', 0);
            $store_id = input('store_id', 0);
            $goods_state = input('goods_state', '');

            $field = 'gs.stock,gs.goods_id,gs.sku_id,gs.sku_name,gs.sku_no,gs.sku_image,gs.goods_name,gs.spec_name,gs.create_time,g.unit,g.goods_state';
            $join = [
                [ 'goods g', 'g.goods_id = gs.goods_id', 'left' ],
            ];
            $condition = [];
            $condition[] = [ 'gs.site_id', '=', $this->site_id ];
            $condition[] = [ 'g.is_delete', '=', 0 ];
            $condition[] = [ 'g.goods_class', 'in', [ GoodsDict::real, GoodsDict::weigh ] ];
            if (!empty($search)) {
                $condition[] = [ 'gs.sku_name|sku_no', 'like', '%' . $search . '%' ];
            }

            if (!empty($category_id)) {
                $condition[] = [ 'g.category_id', 'like', '%,' . $category_id . ',%' ];
            }
            // 上架状态
            if ($goods_state !== '') {
                $condition[] = [ 'g.goods_state', '=', $goods_state ];
            }

            if ($store_id > 0) {
                $stock_alias = 'sgs.real_stock';
                $join[] = [
                    'store_goods_sku sgs',
                    'sgs.sku_id = gs.sku_id and (sgs.store_id is null or sgs.store_id = ' . $store_id . ')',
                    'left'
                ];
                $field .= ',sgs.stock, sgs.real_stock, sgs.cost_price';
            } else {
                $stock_alias = 'gs.real_stock';
                $join[] = [
                    'store_goods_sku sgs',
                    'sgs.sku_id = gs.sku_id or sgs.sku_id is null',
                    'left'
                ];
                $field .= ',sum(sgs.stock) as stock, sum(sgs.real_stock) as real_stock, sum(sgs.cost_price*sgs.real_stock)/sum(sgs.real_stock) as cost_price';
                $group = 'gs.sku_id';
            }

            if ($min_stock > 0 && $max_stock > 0) {
                $condition[] = [ $stock_alias, 'between', [ $min_stock, $max_stock ] ];
            } else if ($min_stock > 0 && $max_stock == 0) {
                $condition[] = [ $stock_alias, '>=', $min_stock ];
            } else if ($min_stock == 0 && $max_stock > 0) {
                $condition[] = [ $stock_alias, '<=', $max_stock ];
            }
            $goods_model = new \app\model\goods\Goods();
            $res = $goods_model->getGoodsSkuPageList($condition, $page, $page_size, 'g.create_time desc', $field, 'gs', $join, $group ?? null);
            foreach($res['data']['list'] as &$val){
                $val['cost_price'] = round($val['cost_price'], 2);
            }
            return $res;
        } else {
            $goods_state = input('state', '');
            $this->assign('goods_state', $goods_state);

            //获取一级商品分类
            $goods_category_model = new GoodsCategoryModel();
            $condition = [
                [ 'pid', '=', 0 ],
                [ 'site_id', '=', $this->site_id ]
            ];

            $goods_category_list = $goods_category_model->getCategoryList($condition, 'category_id,category_name,level,commission_rate')[ 'data' ];
            $this->assign('goods_category_list', $goods_category_list);

            $this->assign('store_list', ( new Store )->getStoreList($this->site_id)[ 'data' ] ?? []);

            return $this->fetch('stock/manage');
        }
    }

    /**
     * 入库管理(应该豁免盘点)
     * @return mixed
     */
    public function storage()
    {
        $document_model = new Document();
        if (request()->isJson()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $status = input('status', '');
            $search_text = input('search_text', '');
            $store_id = !empty(input('store_id', 0)) ? input('store_id', 0) : $this->store_id;

            $condition = [
                [ 'site_id', '=', $this->site_id ],
                [ 'type', '=', StockDict::input ]
            ];

            if (!empty($search_text)) {
                $condition[] = [ 'document_no', 'like', '%' . $search_text . '%' ];
            }

            if ($store_id > 0) {
                $condition[] = [ 'store_id', 'in', $store_id ];
            }

            if ($status !== '') {
                $condition[] = [ 'status', '=', $status ];
            }

            $field = 'document_id, document_no, key, goods_money, promotion_money, invoice_money, document_money, remark, status, create_time, store_id, store_name, operater, operater_name, verifier, verifier_name, inventory_id,refuse_reason,audit_time,time';
            return $document_model->getDocumentPageList($condition, $page, $page_size, 'create_time desc', $field);
        } else {
            $this->assign('store_list', ( new Store )->getStoreList($this->site_id)[ 'data' ] ?? []);
            $this->assign('user_info', $this->user_info);

            $status_list = $document_model->getStatus();
            $this->assign('status_list', $status_list);

            // 检查当前账号是否允许审核单据
            $is_audit = $document_model->checkAudit([
                'app_module' => $this->app_module,
                'is_admin' => $this->user_info[ 'is_admin' ],
                'is_system' => $this->group_info[ 'is_system' ],
                'menu_array' => $this->group_info[ 'menu_array' ]
            ]);
            $this->assign('is_audit', $is_audit[ 'code' ]);

            return $this->fetch('stock/storage');
        }

    }

    /**
     * 添加/编辑入库单
     */
    public function stockin()
    {
        $document_model = new Document();
        $document_id = input('document_id', 0);
        if (request()->isJson()) {
            $document_model = new Document();

            $stock_json = input('stock_json', '');
            $stock_array = json_decode($stock_json, true);
            $store_id = input('store_id', 0);
            $remark = input('remark', '');
            $document_no = input('document_no', '');
            $time = date_to_time(input('time', date('Y-m-d H:i:s')));

            $document_params = [
                'site_id' => $this->site_id,
                'store_id' => $store_id,
                'user_info' => $this->user_info,
                'goods_sku_list' => $stock_array,
                'remark' => $remark,
                'document_no' => $document_no,
                'time' => $time
            ];

            if (!empty($document_id)) {
                $document_params[ 'document_id' ] = $document_id;
                $result = $document_model->editDocument($document_params);
            } else {
                $document_params[ 'is_auto_audit' ] = false;
                $result = $document_model->addPurchase($document_params);
            }
            return $result;
        } else {
            $this->assign('store_list', ( new Store )->getStoreList($this->site_id)[ 'data' ] ?? []);

            if (!empty($document_id)) {
                $condition = [
                    [ 'site_id', '=', $this->site_id ],
                    [ 'document_id', '=', $document_id ],
                    [ 'type', '=', StockDict::input ]
                ];
                $document_info = $document_model->getDocumentEditData($condition)[ 'data' ];
                $this->assign('document_info', $document_info);
            }

            $stock_model = new StockModel();
            $stock_config = $stock_model->getStockConfig($this->site_id)[ 'data' ][ 'value' ];
            $this->assign('stock_config', $stock_config);

            $document_type_info = $document_model->getDocumentTypeInfo([ 'key' => 'PURCHASE' ]);
            $prefix = $document_type_info[ 'prefix' ];
            $document_no = $document_model->createDocumentNo($prefix);
            $this->assign('document_no', $document_no);

            return $this->fetch('stock/stockin');
        }
    }

    /**
     * 入库单详情
     */
    public function inputDetail()
    {
        $document_id = intval(input('document_id', 0));
        $document_model = new Document();
        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'document_id', '=', $document_id ],
            [ 'type', '=', StockDict::input ]
        ];
        $document_detail = $document_model->getDocumentDetail($condition)[ 'data' ] ?? [];
        $this->assign('document_detail', $document_detail);
        return $this->fetch('stock/input_detail');
    }

    /**
     * 出库管理(应该豁免盘点)
     * @return mixed
     */
    public function wastage()
    {
        $document_model = new Document();
        if (request()->isJson()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $status = input('status', '');
            $search_text = input('search_text', '');
            $store_id = !empty(input('store_id', 0)) ? input('store_id', 0) : $this->store_id;

            $condition = [
                [ 'site_id', '=', $this->site_id ],
                [ 'type', '=', StockDict::output ]
            ];

            if (!empty($search_text)) {
                $condition[] = [ 'document_no', 'like', '%' . $search_text . '%' ];
            }


            if ($store_id > 0) {
                $condition[] = [ 'store_id', 'in', $store_id ];
            }

            if ($status !== '') {
                $condition[] = [ 'status', '=', $status ];
            }

            $field = 'document_id, document_no, time, key, goods_money, promotion_money, invoice_money, document_money, remark, status, create_time, store_id, store_name, operater, operater_name, verifier, verifier_name, inventory_id,refuse_reason,audit_time';
            return $document_model->getDocumentPageList($condition, $page, $page_size, 'create_time desc', $field);
        } else {
            $this->assign('store_list', ( new Store )->getStoreList($this->site_id)[ 'data' ] ?? []);
            $this->assign('user_info', $this->user_info);

            $status_list = $document_model->getStatus();
            $this->assign('status_list', $status_list);

            // 检查当前账号是否允许审核单据
            $is_audit = $document_model->checkAudit([
                'app_module' => $this->app_module,
                'is_admin' => $this->user_info[ 'is_admin' ],
                'is_system' => $this->group_info[ 'is_system' ],
                'menu_array' => $this->group_info[ 'menu_array' ]
            ]);
            $this->assign('is_audit', $is_audit[ 'code' ]);

            return $this->fetch('stock/wastage');
        }
    }

    /**
     * 添加/编辑出库单
     */
    public function stockout()
    {
        $document_model = new Document();
        $document_id = input('document_id', 0);
        if (request()->isJson()) {
            $document_model = new Document();
            $stock_json = input('stock_json', '');
            $store_id = input('store_id', 0);
            $stock_array = json_decode($stock_json, true);
            $remark = input('remark', '');
            $document_no = input('document_no', '');
            $time = date_to_time(input('time', date('Y-m-d H:i:s')));

            if (!empty($document_id)) {
                $result = $document_model->editDocument([
                    'document_id' => $document_id,
                    'site_id' => $this->site_id,
                    'store_id' => $store_id,
                    'user_info' => $this->user_info,
                    'goods_sku_list' => $stock_array,
                    'remark' => $remark,
                    'time' => $time,
                    'document_no' => $document_no,
                ]);
            } else {
                $result = $document_model->addOtherOutput([
                    'site_id' => $this->site_id,
                    'store_id' => $store_id,
                    'user_info' => $this->user_info,
                    'goods_sku_list' => $stock_array,
                    'is_out_stock' => 1,
                    'is_auto_audit' => false,
                    'remark' => $remark,
                    'time' => $time,
                    'document_no' => $document_no,
                ]);
            }
            return $result;
        } else {
            $this->assign('store_list', ( new Store )->getStoreList($this->site_id)[ 'data' ] ?? []);

            if (!empty($document_id)) {
                $condition = [
                    [ 'site_id', '=', $this->site_id ],
                    [ 'document_id', '=', $document_id ],
                    [ 'type', '=', StockDict::output ]
                ];
                $document_info = $document_model->getDocumentEditData($condition)[ 'data' ];
                $this->assign('document_info', $document_info);
            }

            $this->assign('store_list', ( new Store )->getStoreList($this->site_id)[ 'data' ] ?? []);

            $stock_model = new StockModel();
            $stock_config = $stock_model->getStockConfig($this->site_id)[ 'data' ][ 'value' ];
            $this->assign('stock_config', $stock_config);

            $document_type_info = $document_model->getDocumentTypeInfo([ 'key' => 'OTHERCK' ]);
            $prefix = $document_type_info[ 'prefix' ];
            $document_no = $document_model->createDocumentNo($prefix);
            $this->assign('document_no', $document_no);

            return $this->fetch('stock/stockout');
        }
    }

    /**
     * 出库单详情
     */
    public function outputDetail()
    {
        $document_id = input('document_id', 0);
        $document_model = new Document();
        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'document_id', '=', $document_id ],
            [ 'type', '=', StockDict::output ]
        ];
        $document_detail = $document_model->getDocumentDetail($condition)[ 'data' ] ?? [];
        $this->assign('document_detail', $document_detail);
        return $this->fetch('stock/output_detail');
    }

    /**
     * 出入库单据通过审核
     * @return array
     */
    public function agree()
    {
        if (request()->isJson()) {
            $document_model = new Document();
            $audit_result = $document_model->checkAudit([
                'app_module' => $this->app_module,
                'is_admin' => $this->user_info[ 'is_admin' ],
                'is_system' => $this->group_info[ 'is_system' ],
                'menu_array' => $this->group_info[ 'menu_array' ]
            ]);
            if ($audit_result[ 'code' ] < 0)
                return $audit_result;

            return $document_model->audit([
                'site_id' => $this->site_id,
                'user_info' => $this->user_info,
                'document_id' => input('document_id', 0),
            ]);
        }
    }

    /**
     * 出入库单据审核拒绝
     * @return array
     */
    public function refuse()
    {
        if (request()->isJson()) {
            $document_model = new Document();
            $audit_result = $document_model->checkAudit([
                'app_module' => $this->app_module,
                'is_admin' => $this->user_info[ 'is_admin' ],
                'is_system' => $this->group_info[ 'is_system' ],
                'menu_array' => $this->group_info[ 'menu_array' ]
            ]);
            if ($audit_result[ 'code' ] < 0)
                return $audit_result;
            return $document_model->refuse([
                'site_id' => $this->site_id,
                'user_info' => $this->user_info,
                'document_id' => input('document_id', 0),
                'refuse_reason' => input('refuse_reason', '')
            ]);
        }
    }

    /**
     * 删除单据【待审核状态】
     * @return array
     */
    public function delete()
    {
        if (request()->isJson()) {
            $document_model = new Document();
            $audit_result = $document_model->checkAudit([
                'app_module' => $this->app_module,
                'is_admin' => $this->user_info[ 'is_admin' ],
                'is_system' => $this->group_info[ 'is_system' ],
                'menu_array' => $this->group_info[ 'menu_array' ]
            ]);
            if ($audit_result[ 'code' ] < 0)
                return $audit_result;

            return $document_model->delete([
                'site_id' => $this->site_id,
                'user_info' => $this->user_info,
                'document_id' => input('document_id', 0),
            ]);
        }
    }

    /**
     * 库存盘点
     * @return mixed
     */
    public function check()
    {
        $inventory_model = new Inventory();
        if (request()->isJson()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $store_id = !empty(input('store_id', 0)) ? input('store_id', 0) : $this->store_id;
            $inventory_no = input('inventory_no', '');
            $status = input('status', '');
            $search_text = input('search_text', '');

            $condition = [
                [ 'site_id', '=', $this->site_id ],
            ];
            if (!empty($search_text)) {
                $condition[] = [ 'inventory_no', 'like', '%' . $search_text . '%' ];
            }
            if ($store_id > 0) {
                $condition[] = [ 'store_id', 'in', $store_id ];
            }
            if ($inventory_no) {
                $condition[] = [ 'inventory_no', '=', $inventory_no ];
            }

            if ($status !== '') {
                $condition[] = [ 'status', '=', $status ];
            }
            return $inventory_model->getInventoryPageList($condition, $page, $page_size, 'create_time desc');
        } else {
            $this->assign('store_list', ( new Store )->getStoreList($this->site_id)[ 'data' ] ?? []);
            $this->assign('user_info', $this->user_info);

            $status_list = $inventory_model->getStatus();
            $this->assign('status_list', $status_list);

            // 检查当前账号是否允许审核单据
            $document_model = new Document();
            $is_audit = $document_model->checkAudit([
                'app_module' => $this->app_module,
                'is_admin' => $this->user_info[ 'is_admin' ],
                'is_system' => $this->group_info[ 'is_system' ],
                'menu_array' => $this->group_info[ 'menu_array' ]
            ]);
            $this->assign('is_audit', $is_audit[ 'code' ]);
            return $this->fetch('stock/check');
        }
    }

    /**
     * 库存盘点详情
     */
    public function inventoryDetail()
    {
        $inventory_no = input('inventory_no', 0);
        $inventory_model = new Inventory();
        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'inventory_no', '=', $inventory_no ]
        ];
        $inventory_detail = $inventory_model->getInventoryInfo($condition)[ 'data' ] ?? [];
        if (empty($inventory_detail)) {
            $this->error('盘点单不存在');
        }
        $this->assign('inventory_detail', $inventory_detail);
        return $this->fetch('stock/inventory_detail');
    }

    /**
     * 添加/编辑盘点记录
     * @return mixed
     */
    public function editCheck()
    {
        $inventory_model = new Inventory();
        $inventory_id = input('inventory_id', 0);
        if (request()->isJson()) {
            $store_id = input('store_id', 0);
            $stock_list = input('stock_json', '');//商品库存映照json  {{'sku_id':1, 'stock' : 10, 'cost_price':10,'source':'来源'}}
            $remark = input('remark', '');
            $inventory_no = input('inventory_no', '');
            $time = date_to_time(input('time', date('Y-m-d H:i:s')));
            $stock_list = json_decode($stock_list, true);
            $params = [
                'site_id' => $this->site_id,
                'store_id' => $store_id,
                'sku_list' => $stock_list,
                'user_info' => $this->user_info,
                'is_auto_audit' => false,
                'remark' => $remark,
                'inventory_no' => $inventory_no,
                'action_time' => $time,
            ];
            if (!empty($inventory_id)) {
                $params[ 'inventory_id' ] = $inventory_id;
                $result = $inventory_model->editInventory($params);
            } else {
                $result = $inventory_model->addInventory($params);
            }
            return $result;
        } else {

            $this->assign('store_list', ( new Store )->getStoreList($this->site_id)[ 'data' ] ?? []);

            $stock_model = new StockModel();
            $stock_config = $stock_model->getStockConfig($this->site_id)[ 'data' ][ 'value' ];
            $this->assign('stock_config', $stock_config);

            if (!empty($inventory_id)) {
                $condition = [
                    [ 'site_id', '=', $this->site_id ],
                    [ 'inventory_id', '=', $inventory_id ],
                ];
                $inventory_info = $inventory_model->getInventoryEditData($condition)[ 'data' ];
                $this->assign('inventory_info', $inventory_info);
            }

            $inventory_no = $inventory_model->inventoryNo();
            $this->assign('inventory_no', $inventory_no);


            return $this->fetch('stock/edit_check');
        }
    }

    /**
     * 盘点单据通过审核
     * @return array
     */
    public function inventoryAgree()
    {
        if (request()->isJson()) {
            $document_model = new Document();
            $audit_result = $document_model->checkAudit([
                'app_module' => $this->app_module,
                'is_admin' => $this->user_info[ 'is_admin' ],
                'is_system' => $this->group_info[ 'is_system' ],
                'menu_array' => $this->group_info[ 'menu_array' ]
            ]);
            if ($audit_result[ 'code' ] < 0)
                return $audit_result;

            $inventory_model = new Inventory();
            return $inventory_model->audit([
                'site_id' => $this->site_id,
                'user_info' => $this->user_info,
                'inventory_id' => input('inventory_id', 0),
            ]);
        }
    }

    /**
     * 盘点单据审核拒绝
     * @return array
     */
    public function inventoryRefuse()
    {
        if (request()->isJson()) {
            $document_model = new Document();
            $audit_result = $document_model->checkAudit([
                'app_module' => $this->app_module,
                'is_admin' => $this->user_info[ 'is_admin' ],
                'is_system' => $this->group_info[ 'is_system' ],
                'menu_array' => $this->group_info[ 'menu_array' ]
            ]);
            if ($audit_result[ 'code' ] < 0)
                return $audit_result;

            $inventory_model = new Inventory();
            return $inventory_model->refuse([
                'site_id' => $this->site_id,
                'user_info' => $this->user_info,
                'inventory_id' => input('inventory_id', 0),
                'refuse_reason' => input('refuse_reason', '')
            ]);
        }
    }

    /**
     * 删除盘点单据【待审核状态】
     * @return array
     */
    public function inventoryDelete()
    {
        if (request()->isJson()) {
            $document_model = new Document();
            $audit_result = $document_model->checkAudit([
                'app_module' => $this->app_module,
                'is_admin' => $this->user_info[ 'is_admin' ],
                'is_system' => $this->group_info[ 'is_system' ],
                'menu_array' => $this->group_info[ 'menu_array' ]
            ]);
            if ($audit_result[ 'code' ] < 0)
                return $audit_result;

            $inventory_model = new Inventory();
            return $inventory_model->delete([
                'site_id' => $this->site_id,
                'user_info' => $this->user_info,
                'inventory_id' => input('inventory_id', 0),
            ]);
        }
    }

    /**
     * 库存流水
     */
    public function records()
    {
        $sku_id = input('sku_id', 0);
        $document_model = new Document();
        if (request()->isJson()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $type = input('type', '');
            $start_time = input('start_time', 0);
            $end_time = input('end_time', 0);
            $store_id = input('store_id', 0);
            $condition = [
                [ 'dg.site_id', '=', $this->site_id ],
            ];
            if ($sku_id > 0) {
                $condition[] = [ 'dg.goods_sku_id', '=', $sku_id ];
            }
            if (!empty($type)) {
                $condition[] = [ 'd.key', '=', $type ];
            }
            if ($store_id > 0) {
                $condition[] = [ 'd.store_id', '=', $store_id ];
            }
            //注册时间
            if ($start_time != '' && $end_time != '') {
                $condition[] = [ 'dg.create_time', 'between', [ strtotime($start_time), strtotime($end_time) ] ];
            } else if ($start_time != '' && $end_time == '') {
                $condition[] = [ 'dg.create_time', '>=', strtotime($start_time) ];
            } else if ($start_time == '' && $end_time != '') {
                $condition[] = [ 'dg.create_time', '<=', strtotime($end_time) ];
            }

            return $document_model->getDocumentGoodsPageList($condition, $page, $page_size, 'dg.create_time desc');
        } else {
            $type_list = $document_model->getDocumentTypeList()[ 'data' ] ?? [];
            $this->assign('store_list', ( new Store )->getStoreList($this->site_id)[ 'data' ] ?? []);
            $this->assign('type_list', $type_list);
            $this->assign('sku_id', $sku_id);
            return $this->fetch('stock/records');
        }
    }

    /**
     * 商品规格列表(仅作临时用)
     */
    public function getSkuList()
    {
        $search = input('search', '');
        $goods_id = input('goods_id', '');
        $store_id = input('store_id', 0);
        $stock_model = new StockModel();
        $condition = [
            [ 'gs.site_id', '=', $this->site_id ],
            [ 'g.goods_class', 'in', [ GoodsDict::real, GoodsDict::weigh ] ],
            [ 'g.is_delete', '=', 0 ]
        ];
        if (!empty($search)) {
            $condition[] = [ 'gs.sku_name|gs.sku_no', 'like', '%' . $search . '%' ];
        }
        if (!empty($goods_id)) {
            $condition[] = [ 'g.goods_id', '=', $goods_id ];
        }
        if ($store_id > 0) {
            //查询商品支持门店(支持当前门店或全部)
            $condition[] = [ 'g.sale_store', 'like', [ '%,' . $store_id . ',%', '%all%'], 'or' ];
//            $condition[] = ['sgs.store_id', '=', $store_id];
        }
        $field = 'gs.sku_id,gs.sku_image,gs.sku_name,gs.unit,gs.sku_no,sgs.stock,sgs.real_stock,sgs.price,sgs.cost_price';
        return $stock_model->getStoreGoodsSkuList($condition, $field, 'gs.create_time desc', $store_id);
    }

    /**
     * 更新库存
     */
    public function skuInput()
    {
        $params = [
            'site_id' => $this->site_id,
            'sku_id' => input('sku_id', 0),
            'goods_id' => input('goods_id', 0),
            'store_id' => input('store_id', 0),
            'key' => input('key', 0),
            'remark' => input('remark', 0),
            'goods_num' => input('goods_num', 0),
            'goods_price' => input('goods_price', 0),
            'user_info' => $this->user_info,
            'time' => input('time', 0),
        ];
        $stock_model = new StockModel();
        return $stock_model->changeStock($params);
    }

    /**
     * 调拨单
     * @return mixed
     */
    public function allocate()
    {
        if (request()->isJson()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $output_store_id = input('output_store_id', 0);
            $input_store_id = input('input_store_id', 0);
            $allot_no = input('allot_no', '');
            $condition = [
                [ 'site_id', '=', $this->site_id ],
            ];
            if ($output_store_id > 0) {
                $condition[] = [ 'output_store_id', '=', $output_store_id ];
            }
            if ($input_store_id > 0) {
                $condition[] = [ 'input_store_id', '=', $input_store_id ];
            }
            if ($allot_no) {
                $condition[] = [ 'allot_no', '=', $allot_no ];
            }
            $allot_model = new Allot();
            return $allot_model->getStockAllotPageList($condition, $page, $page_size, 'create_time desc');
        } else {
            // 检查当前账号是否允许审核单据
            $document_model = new Document();
            $is_audit = $document_model->checkAudit([
                'app_module' => $this->app_module,
                'is_admin' => $this->user_info[ 'is_admin' ],
                'is_system' => $this->group_info[ 'is_system' ],
                'menu_array' => $this->group_info[ 'menu_array' ]
            ]);
            $this->assign('is_audit', $is_audit[ 'code' ]);
            $this->assign('store_list', ( new Store )->getStoreList($this->site_id)[ 'data' ] ?? []);
            return $this->fetch('stock/allocate');
        }

    }

    /**
     * 调拨记录
     * @return mixed
     */
    public function allotrecords()
    {
        $allot_id = input('allot_id', 0);
        $allot_model = new Allot();
        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'allot_id', '=', $allot_id ],
        ];
        $detail = $allot_model->getAllotInfo($condition)[ 'data' ] ?? [];
        $this->assign('detail', $detail);
        return $this->fetch('stock/allot_records');
    }

    /**
     * 添加/编辑调拨单
     */
    public function editAllocate()
    {
        $allot_id = input('allot_id', 0);
        if (request()->isJson()) {
            $allot_model = new Allot();
            $data = [
                'output_store_id' => input('output_store_id', 0),
                'input_store_id' => input('input_store_id', 0),
                'remark' => input('remark', ''),
                'allot_no' => input('allot_no', ''),
                'goods_sku_list' => input('goods_sku_list', '') ? json_decode(input('goods_sku_list'), true) : [],
                'allot_time' => input('allot_time', '') ? date_to_time(input('allot_time')) : 0,
                'operater' => $this->uid,
                'is_auto_audit' => false,
                'site_id' => $this->site_id,
                'user_info' => $this->user_info,
            ];
            if (empty($allot_id)) {
                $result = $allot_model->addAllot($data);
            } else {
                $data[ 'allot_id' ] = $allot_id;
                $result = $allot_model->editAllot($data);
            }
            return $result;
        } else {
            $allot_model = new Allot();
            $this->assign('store_list', ( new Store )->getStoreList($this->site_id)[ 'data' ] ?? []);
            $this->assign('start_time', date('Y-m-d'));
            $this->assign('default_time', date('Y-m-d H:i:s'));

            $allot_no = $allot_model->getAllotNo();
            $this->assign('allot_no', $allot_no);

            if (!empty($allot_id)) {

                $condition = [
                    [ 'site_id', '=', $this->site_id ],
                    [ 'allot_id', '=', $allot_id ],
                ];
                $allot_info = $allot_model->getAllotEditData($condition)[ 'data' ];
                $this->assign('allot_info', $allot_info);
            }

            return $this->fetch('stock/edit_allocate');
        }
    }

    /**
     * 调拨单据通过审核
     * @return array
     */
    public function allocateAgree()
    {
        if (request()->isJson()) {
            $document_model = new Document();
            $audit_result = $document_model->checkAudit([
                'app_module' => $this->app_module,
                'is_admin' => $this->user_info[ 'is_admin' ],
                'is_system' => $this->group_info[ 'is_system' ],
                'menu_array' => $this->group_info[ 'menu_array' ]
            ]);
            if ($audit_result[ 'code' ] < 0)
                return $audit_result;

            $allot_model = new Allot();
            return $allot_model->audit([
                'site_id' => $this->site_id,
                'user_info' => $this->user_info,
                'allot_id' => input('allot_id', 0),
            ]);
        }
    }

    /**
     * 调拨单据审核拒绝
     * @return array
     */
    public function allocateRefuse()
    {
        if (request()->isJson()) {
            $document_model = new Document();
            $audit_result = $document_model->checkAudit([
                'app_module' => $this->app_module,
                'is_admin' => $this->user_info[ 'is_admin' ],
                'is_system' => $this->group_info[ 'is_system' ],
                'menu_array' => $this->group_info[ 'menu_array' ]
            ]);
            if ($audit_result[ 'code' ] < 0)
                return $audit_result;

            $allot_model = new Allot();
            return $allot_model->refuse([
                'site_id' => $this->site_id,
                'user_info' => $this->user_info,
                'allot_id' => input('allot_id', 0),
                'refuse_reason' => input('refuse_reason', '')
            ]);
        }
    }

    /**
     * 删除调拨单据【待审核状态】
     * @return array
     */
    public function allocateDelete()
    {
        if (request()->isJson()) {
            $document_model = new Document();
            $audit_result = $document_model->checkAudit([
                'app_module' => $this->app_module,
                'is_admin' => $this->user_info[ 'is_admin' ],
                'is_system' => $this->group_info[ 'is_system' ],
                'menu_array' => $this->group_info[ 'menu_array' ]
            ]);
            if ($audit_result[ 'code' ] < 0)
                return $audit_result;

            $allot_model = new Allot();
            return $allot_model->delete([
                'site_id' => $this->site_id,
                'user_info' => $this->user_info,
                'allot_id' => input('allot_id', 0),
            ]);
        }
    }

    /**
     * 库存设置
     * @return array|mixed
     */
    public function config()
    {
        $stock_model = new StockModel();
        if (request()->isJson()) {
            $data = [
                'is_audit' => input('is_audit', 0),
            ];
            return $stock_model->setStockConfig($data, $this->site_id);
        } else {
            $stock_config = $stock_model->getStockConfig($this->site_id)[ 'data' ][ 'value' ];
            $this->assign('stock_config', $stock_config);
            return $this->fetch('stock/config');
        }
    }

    /**
     * 商品导出
     */
    public function exportGoods()
    {
        if (request()->isJson()) {

            $goods_ids = input('goods_ids', '');
            $search_text = input('search_text', ''); // 名称或编码
            $category_id = input('category_id', '');
            $min_stock = input('min_stock', 0);
            $max_stock = input('max_stock', 0);
            $store_id = input('store_id', 0);
            $store_name = input('store_name', '');
            $goods_state = input('goods_state', '');

            $condition = [];
            $condition[] = [ 'gs.site_id', '=', $this->site_id ];
            $condition[] = [ 'g.is_delete', '=', 0 ];
            $condition[] = [ 'g.goods_class', 'in', [ GoodsDict::real, GoodsDict::weigh ] ];
            $condition_desc = [];

            if (!empty($goods_ids)) {
                $condition[] = [ 'g.goods_id', 'in', explode(',', $goods_ids) ];
                $condition_desc[] = [ 'name' => 'goods_id', 'value' => $goods_ids ];
            }

            if (!empty($search_text)) {
                $condition[] = [ 'gs.sku_name|sku_no', 'like', '%' . $search_text . '%' ];
                $condition_desc[] = [ 'name' => '商品名称/编码', 'value' => $search_text ];
            }

            if (!empty($category_id)) {
                $condition[] = [ 'g.category_id', 'like', '%,' . $category_id . ',%' ];

                $goods_category_model = new GoodsCategoryModel();
                $category_info = $goods_category_model->getCategoryInfo([
                    [ 'category_id', '=', $category_id ],
                    [ 'site_id', '=', $this->site_id ]
                ], 'category_name')[ 'data' ];
                if (!empty($category_info)) {
                    $condition_desc[] = [ 'name' => '商品分类', 'value' => $category_info[ 'category_name' ] ];
                }

            }
            // 上架状态
            if ($goods_state !== '') {
                $condition[] = [ 'g.goods_state', '=', $goods_state ];
                $condition_desc[] = [ 'name' => '商品状态', 'value' => $goods_state == 1 ? '销售中' : '仓库中' ];
            } else {
                $condition_desc[] = [ 'name' => '商品状态', 'value' => '全部' ];
            }

            if ($store_id > 0) {
                $stock_alias = 'sgs.real_stock';
                $condition_desc[] = [ 'name' => '门店名称', 'value' => $store_name ];
            } else {
                $stock_alias = 'gs.real_stock';
            }

            if ($min_stock > 0 && $max_stock > 0) {
                $condition[] = [ $stock_alias, 'between', [ $min_stock, $max_stock ] ];
                $condition_desc[] = [ 'name' => '库存区间', 'value' => $min_stock . '~' . $max_stock ];
            } else if ($min_stock > 0 && $max_stock == 0) {
                $condition[] = [ $stock_alias, '>=', $min_stock ];
                $condition_desc[] = [ 'name' => '库存区间', 'value' => '>=' . $min_stock ];
            } else if ($min_stock == 0 && $max_stock > 0) {
                $condition[] = [ $stock_alias, '<=', $max_stock ];
                $condition_desc[] = [ 'name' => '库存区间', 'value' => '<=' . $max_stock ];
            }

            $goods_export_model = new Export();
            return $goods_export_model->export($condition, $condition_desc, $this->site_id, $store_id, $store_name);
        }
    }

    /**
     * 导出记录
     * @return array|mixed
     */
    public function export()
    {
        if (request()->isJson()) {
            $page_index = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $export_model = new Export();
            $condition = [
                [ 'site_id', '=', $this->site_id ]
            ];
            return $export_model->getExportPageList($condition, $page_index, $page_size, 'create_time desc', ' * ');
        } else {
            return $this->fetch('stock/export');
        }
    }

    /**
     * 删除导出记录
     * @return array
     */
    public function deleteExport()
    {
        if (request()->isJson()) {
            $export_ids = input('export_ids', '');
            $export_model = new Export();
            $condition = [
                [ 'site_id', '=', $this->site_id ],
                [ 'export_id', 'in', (string) $export_ids ]
            ];
            return $export_model->deleteExport($condition);
        }
    }

    /**
     * 商品选择组件
     * @return array|mixed
     */
    public function goodsSelect()
    {
        if (request()->isJson()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $search_text = input('search_text', '');
            $goods_ids = input('goods_ids', '');
            $category_id = input('category_id', '');// 商品分类id
            $select_type = input('select_type', 'all');
            $store_id = input('store_id', 0);

            $condition = [
                [ 'g.is_delete', '=', 0 ],
                [ 'g.goods_state', '=', 1 ],
                //[ 'g.goods_stock', '>', 0 ],
                [ 'g.goods_class', 'in', [ GoodsDict::real, GoodsDict::weigh ] ],
                [ 'gs.site_id', '=', $this->site_id ],
                [ 'g.is_virtual', '=', 0 ]
            ];

            $stock_model = new StockModel();

            if (!empty($search_text)) {
                $condition[] = [ 'gs.sku_name|gs.sku_no', 'like', '%' . $search_text . '%' ];
            }
            if ($store_id > 0) {
                //查询商品支持门店(支持当前门店或全部)
                $condition[] = [ 'g.sale_store', 'like', [ '%,' . $store_id . ',%', '%all%'], 'or' ];
            }

            if ($select_type == 'selected') {
                $condition[] = [ 'g.goods_id', 'in', $goods_ids ];
            }
            if (!empty($category_id)) {
                $condition[] = [ 'g.category_id', 'like', '%,' . $category_id . ',%' ];
            }

            $field = 'g.goods_class,gs.sku_id,gs.goods_id,gs.sku_image,gs.sku_name,gs.unit,gs.sku_no,sgs.stock,sgs.real_stock,sgs.price,sgs.cost_price';
            return $stock_model->getStoreGoodsSkuPage($condition, $field, 'gs.create_time desc, gs.sku_id desc', $store_id, $page, $page_size);
        } else {

            $max_num = input('max_num', 0);
            $min_num = input('min_num', 0);
            $search_text = input('search_text', '');
            $store_id = input('store_id', '');
            $disabled = input('disabled', 0);

            $this->assign('max_num', $max_num);
            $this->assign('min_num', $min_num);
            $this->assign('disabled', $disabled);
            $this->assign('search_text', $search_text);
            $this->assign('store_id', $store_id);

            $goods_class = input('goods_class', ''); //查找商品类型
            $this->assign('goods_class', $goods_class);

            // 商品分组
            $goods_label_model = new GoodsLabel();
            $label_list = $goods_label_model->getLabelList([ [ 'site_id', '=', $this->site_id ] ], 'id,label_name', 'sort asc')[ 'data' ];
            $this->assign('label_list', $label_list);

            // 分类过滤
            $goods_model = new Goods();
            $category_id_arr = $goods_model->getGoodsCategoryIds([
                [ 'is_delete', '=', 0 ],
                [ 'goods_state', '=', 1 ],
                [ 'goods_stock', '>', 0 ],
                [ 'site_id', '=', $this->site_id ],
            ])[ 'data' ];

            $goods_category_model = new GoodsCategory();
            $field = 'category_id,category_name as title,pid';
            $list = $goods_category_model->getCategoryList([
                [ 'site_id', '=', $this->site_id ],
                [ 'category_id', 'in', $category_id_arr ]
            ], $field)[ 'data' ];
            $tree = list_to_tree($list, 'category_id', 'pid', 'children', 0);
            $this->assign('category_list', $tree);

            return $this->fetch('stock/goods_select');
        }
    }

    /**
     * 上传文件
     */
    public function file()
    {
        $upload_model = new UploadModel($this->site_id);

        $param = [
            'name' => 'file',
            'extend_type' => [ 'xlsx' ]
        ];
        return $upload_model->setPath("stock_import/" . date("Ymd") . '/')->file($param);
    }

    public function import()
    {
        if (request()->isJson()) {
            $filename = input('filename', '');
            $path = input('path', '');
            $type = input('type', '');

            $document_model = new Import();

            return $document_model->import($path, $type);
        }

    }
}
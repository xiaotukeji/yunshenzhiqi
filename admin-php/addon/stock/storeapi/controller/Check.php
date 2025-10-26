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

namespace addon\stock\storeapi\controller;

use addon\stock\model\stock\Document;
use addon\stock\model\stock\Inventory;
use app\storeapi\controller\BaseStoreApi;

/**
 * 库存盘点
 */
class Check extends BaseStoreApi
{

    /**
     * 库存盘点
     * @return mixed
     */
    public function lists()
    {
        $page = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;
        $inventory_no = $this->params['inventory_no'] ?? '';
        $store_id = $this->store_id;
        $condition = [
            [ 'site_id', '=', $this->site_id ],
        ];
        if ($store_id > 0) {
            $condition[] = [ 'store_id', '=', $store_id ];
        }
        if (!empty($inventory_no)) {
            $condition[] = [ 'inventory_no', 'like', '%' . $inventory_no . '%' ];
        }
        $inventory_model = new Inventory();
        $list = $inventory_model->getInventoryPageList($condition, $page, $page_size, 'create_time desc');
        return $this->response($list);

    }

    /**
     * 库存盘点详情
     */
    public function detail()
    {
        $inventory_id = $this->params['inventory_id'] ?? 0;
        $inventory_model = new Inventory();
        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'inventory_id', '=', $inventory_id ],
            [ 'store_id', '=', $this->store_id ],
        ];
        $inventory_detail = $inventory_model->getInventoryInfo($condition);
        if (empty($inventory_detail)) {
            return $this->response($this->error('盘点单不存在'));
        }

        $document_model = new Document();
        $inventory_detail[ 'data' ][ 'is_audit' ] = $document_model->checkAudit(
            [
                'app_module' => $this->app_module,
                'is_admin' => $this->user_info[ 'is_admin' ],
                'menu_array' => $this->menu_array
            ]
        )[ 'code' ];
        $inventory_detail[ 'data' ][ 'uid' ] = $this->user_info[ 'uid' ];

        return $this->response($inventory_detail);
    }

    /**
     * 新增盘点记录
     * @return mixed
     */
    public function add()
    {
        $inventory_model = new Inventory();
        $store_id = $this->store_id;
        $stock_list = $this->params['stock_json'] ?? '';//商品库存映照json  {{'sku_id':1, 'stock' : 10, 'cost_price':10,'source':'来源'}}
        $remark = $this->params['remark'] ?? '';
        $inventory_no = $this->params['inventory_no'] ?? '';
        $time = isset($this->params[ 'time' ]) ? date_to_time($this->params[ 'time' ]) : date('Y-m-d H:i:s');
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


        $result = $inventory_model->addInventory($params);
        return $this->response($result);
    }

    /**
     * 编辑盘点记录
     * @return mixed
     */
    public function edit()
    {
        $inventory_id = $this->params['inventory_id'] ?? 0;
        $stock_list = $this->params['stock_json'] ?? '';//商品库存映照json  {{'sku_id':1, 'stock' : 10, 'cost_price':10,'source':'来源'}}
        $remark = $this->params['remark'] ?? '';
        $inventory_no = $this->params['inventory_no'] ?? '';
        $time = isset($this->params[ 'time' ]) ? date_to_time($this->params[ 'time' ]) : date('Y-m-d H:i:s');
        $store_id = $this->store_id;
        $stock_list = json_decode($stock_list, true);

        $inventory_model = new Inventory();
        $params = [
            'site_id' => $this->site_id,
            'store_id' => $store_id,
            'sku_list' => $stock_list,
            'user_info' => $this->user_info,
            'is_auto_audit' => false,
            'inventory_id' => $inventory_id,
            'remark' => $remark,
            'inventory_no' => $inventory_no,
            'action_time' => $time,
        ];
        $result = $inventory_model->editInventory($params);
        return $this->response($result);
    }

    /**
     * 获取编辑盘点单数据
     * @return false|string
     */
    public function editData()
    {
        $inventory_id = $this->params['inventory_id'] ?? 0;


        $inventory_model = new Inventory();
        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'inventory_id', '=', $inventory_id ],
            [ 'store_id', '=', $this->store_id ]
        ];
        $inventory_info = $inventory_model->getInventoryEditData($condition);
        return $this->response($inventory_info);
    }

    /**
     * 盘点单据通过审核
     * @return false|string
     */
    public function agree()
    {
        $document_model = new Document();
        $audit_result = $document_model->checkAudit(
            [
                'app_module' => $this->app_module,
                'is_admin' => $this->user_info[ 'is_admin' ],
                'menu_array' => $this->menu_array
            ]
        );
        if ($audit_result[ 'code' ] < 0) {
            return $this->response($audit_result);
        }

        $inventory_id = $this->params['inventory_id'] ?? 0;

        $inventory_model = new Inventory();
        $res = $inventory_model->audit([
            'site_id' => $this->site_id,
            'user_info' => $this->user_info,
            'inventory_id' => $inventory_id
        ]);
        return $this->response($res);
    }

    /**
     * 盘点单据审核拒绝
     * @return false|string
     */
    public function refuse()
    {
        $document_model = new Document();
        $audit_result = $document_model->checkAudit(
            [
                'app_module' => $this->app_module,
                'is_admin' => $this->user_info[ 'is_admin' ],
                'menu_array' => $this->menu_array
            ]
        );
        if ($audit_result[ 'code' ] < 0) {
            return $this->response($audit_result);
        }

        $inventory_id = $this->params['inventory_id'] ?? 0;
        $refuse_reason = $this->params['refuse_reason'] ?? '';

        $inventory_model = new Inventory();
        $res = $inventory_model->refuse([
            'site_id' => $this->site_id,
            'user_info' => $this->user_info,
            'inventory_id' => $inventory_id,
            'refuse_reason' => $refuse_reason
        ]);
        return $this->response($res);

    }

    /**
     * 删除盘点单据【待审核状态】
     * @return false|string
     */
    public function delete()
    {
        $document_model = new Document();
        $audit_result = $document_model->checkAudit(
            [
                'app_module' => $this->app_module,
                'is_admin' => $this->user_info[ 'is_admin' ],
                'menu_array' => $this->menu_array
            ]
        );
        if ($audit_result[ 'code' ] < 0) {
            return $this->response($audit_result);
        }

        $inventory_id = $this->params['inventory_id'] ?? 0;

        $inventory_model = new Inventory();
        $res = $inventory_model->delete([
            'site_id' => $this->site_id,
            'user_info' => $this->user_info,
            'inventory_id' => $inventory_id
        ]);
        return $this->response($res);
    }

    /**
     * 获取盘点单号
     * @return false|string
     */
    public function getInventoryNo()
    {
        $inventory_model = new Inventory();
        $inventory_no = $inventory_model->inventoryNo();
        return $this->response($this->success($inventory_no));
    }

}
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

use addon\stock\dict\StockDict;
use addon\stock\model\stock\Document;
use app\storeapi\controller\BaseStoreApi;

/**
 * 入库管理
 */
class Storage extends BaseStoreApi
{

    /**
     * 入库管理(应该豁免盘点)
     * @return mixed
     */
    public function lists()
    {
        $document_model = new Document();
        $page = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;
        $search_text = $this->params['search_text'] ?? '';
        $status = $this->params['status'] ?? '';
        $store_id = $this->store_id;
        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'type', '=', StockDict::input ]
        ];
        if ($store_id > 0) {
            $condition[] = [ 'store_id', 'in', $store_id ];
        }
        if (!empty($status)) {
            $condition[] = [ 'document_no', 'like', '%' . $status . '%' ];
        }

        if ($status !== '') {
            $condition[] = [ 'status', '=', $status ];
        }

        $field = 'document_id, document_no, key, goods_money, promotion_money, invoice_money, document_money, remark, status, create_time, store_id, store_name, operater, operater_name, verifier, verifier_name, inventory_id,refuse_reason,audit_time';
        $result = $document_model->getDocumentPageList($condition, $page, $page_size, 'create_time desc', $field);
        return $this->response($result);
    }

    /**
     * 添加/编辑入库单
     */
    public function stockin()
    {
        $stock_json = $this->params['stock_json'] ?? '';
        $document_id = isset($this->params[ 'document_id' ]) ? (int) $this->params[ 'document_id' ] : 0;
        $remark = $this->params['remark'] ?? '';
        $document_no = $this->params['document_no'] ?? '';
        $time = isset($this->params[ 'time' ]) ? date_to_time($this->params[ 'time' ]) : date('Y-m-d H:i:s');
        $store_id = $this->store_id;
        $stock_array = json_decode($stock_json, true);

        $document_model = new Document();

        $document_params = [
            'site_id' => $this->site_id,
            'store_id' => $store_id,
            'user_info' => $this->user_info,
            'remark' => $remark,
            'document_no' => $document_no,
            'goods_sku_list' => $stock_array,
            'time' => $time
        ];

        if (!empty($document_id)) {
            $document_params[ 'document_id' ] = $document_id;
            $result = $document_model->editDocument($document_params);
        } else {
            $document_params[ 'is_auto_audit' ] = false;
            $result = $document_model->addPurchase($document_params);
        }
        return $this->response($result);
    }

    /**
     * 获取编辑入库单据数据
     */
    public function editData()
    {
        $document_id = isset($this->params[ 'document_id' ]) ? (int) $this->params[ 'document_id' ] : 0;

        $document_model = new Document();
        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'document_id', '=', $document_id ],
            [ 'type', '=', StockDict::input ],
            [ 'store_id', '=', $this->store_id ],
        ];
        $document_info = $document_model->getDocumentEditData($condition);
        return $this->response($document_info);
    }

    /**
     * 入库单详情
     */
    public function detail()
    {
        $document_id = isset($this->params[ 'document_id' ]) ? (int) $this->params[ 'document_id' ] : 0;
        $document_model = new Document();
        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'document_id', '=', $document_id ],
            [ 'type', '=', StockDict::input ],
            [ 'store_id', '=', $this->store_id ],
        ];
        $document_detail = $document_model->getDocumentDetail($condition);

        $document_model = new Document();
        $document_detail[ 'data' ][ 'is_audit' ] = $document_model->checkAudit(
            [
                'app_module' => $this->app_module,
                'is_admin' => $this->user_info[ 'is_admin' ],
                'menu_array' => $this->menu_array
            ]
        )[ 'code' ];
        $document_detail[ 'data' ][ 'uid' ] = $this->user_info[ 'uid' ];
        return $this->response($document_detail);
    }

    /**
     * 出入库单据通过审核
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

        $document_id = $this->params['document_id'] ?? 0;

        $res = $document_model->audit([
            'site_id' => $this->site_id,
            'user_info' => $this->user_info,
            'document_id' => $document_id,
        ]);
        return $this->response($res);

    }

    /**
     * 出入库单据审核拒绝
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

        $document_id = $this->params['document_id'] ?? 0;
        $refuse_reason = $this->params['refuse_reason'] ?? '';

        $res = $document_model->refuse([
            'site_id' => $this->site_id,
            'user_info' => $this->user_info,
            'document_id' => $document_id,
            'refuse_reason' => $refuse_reason
        ]);
        return $this->response($res);

    }

    /**
     * 删除单据
     * @return false|string
     */
    public function delete()
    {
        $document_id = $this->params['document_id'] ?? 0;

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

        $res = $document_model->delete([
            'site_id' => $this->site_id,
            'user_info' => $this->user_info,
            'document_id' => $document_id,
        ]);
        return $this->response($res);

    }

    /**
     * 获取入库单号
     * @return false|string
     */
    public function getDocumentNo()
    {
        $document_model = new Document();
        $document_type_info = $document_model->getDocumentTypeInfo([ 'key' => 'PURCHASE' ]);
        $prefix = $document_type_info[ 'prefix' ];
        $document_no = $document_model->createDocumentNo($prefix);
        return $this->response($this->success($document_no));
    }


}
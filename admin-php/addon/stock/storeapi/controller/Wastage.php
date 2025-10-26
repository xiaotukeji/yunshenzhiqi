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
 * 出库管理
 */
class Wastage extends BaseStoreApi
{
    /**
     * 出库管理(应该豁免盘点)
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
            [ 'type', '=', StockDict::output ]
        ];
        if ($store_id > 0) {
            $condition[] = [ 'store_id', '=', $store_id ];
        }
        if (!empty($search_text)) {
            $condition[] = [ 'document_no', 'like', '%' . $search_text . '%' ];
        }

        if ($status !== '') {
            $condition[] = [ 'status', '=', $status ];
        }
        $field = 'document_id, document_no, key, goods_money, promotion_money, invoice_money, document_money, remark, status, create_time, store_id, store_name, operater, operater_name, verifier, verifier_name, inventory_id,refuse_reason,audit_time';
        $result = $document_model->getDocumentPageList($condition, $page, $page_size, 'create_time desc', $field);

        return $this->response($result);
    }

    /**
     * 添加/编辑出库单
     */
    public function stockout()
    {
        $stock_json = $this->params['stock_json'] ?? '';
        $document_id = isset($this->params[ 'document_id' ]) ? (int) $this->params[ 'document_id' ] : 0;
        $remark = $this->params['remark'] ?? '';
        $document_no = $this->params['document_no'] ?? '';
        $time = isset($this->params[ 'time' ]) ? date_to_time($this->params[ 'time' ]) : date('Y-m-d H:i:s');
        $store_id = $this->store_id;
        $stock_array = json_decode($stock_json, true);

        $document_model = new Document();

        if (!empty($document_id)) {
            $result = $document_model->editDocument([
                'document_id' => $document_id,
                'site_id' => $this->site_id,
                'store_id' => $store_id,
                'user_info' => $this->user_info,
                'goods_sku_list' => $stock_array,
                'remark' => $remark,
                'time' => $time,
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
        return $this->response($result);
    }

    /**
     * 获取编辑出库单据数据
     */
    public function editData()
    {
        $document_id = isset($this->params[ 'document_id' ]) ? (int) $this->params[ 'document_id' ] : 0;

        $document_model = new Document();
        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'document_id', '=', $document_id ],
            [ 'type', '=', StockDict::output ],
            [ 'store_id', '=', $this->store_id ],
        ];
        $document_info = $document_model->getDocumentEditData($condition);
        return $this->response($document_info);
    }

    /**
     * 出库单详情
     */
    public function detail()
    {
        $document_id = isset($this->params[ 'document_id' ]) ? (int) $this->params[ 'document_id' ] : 0;

        $document_model = new Document();
        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'document_id', '=', $document_id ],
            [ 'type', '=', StockDict::output ],
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
     * 获取出库单号
     * @return false|string
     */
    public function getDocumentNo()
    {
        $document_model = new Document();
        $document_type_info = $document_model->getDocumentTypeInfo([ 'key' => 'OTHERCK' ]);
        $prefix = $document_type_info[ 'prefix' ];
        $document_no = $document_model->createDocumentNo($prefix);
        return $this->response($this->success($document_no));
    }


}
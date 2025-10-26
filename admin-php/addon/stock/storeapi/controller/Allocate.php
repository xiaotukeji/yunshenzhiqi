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

use addon\stock\model\stock\Allot;
use addon\stock\model\stock\Document;
use app\storeapi\controller\BaseStoreApi;

/**
 * 库存调拨
 */
class Allocate extends BaseStoreApi
{

    /**
     * 调拨单
     * @return mixed
     */
    public function lists()
    {
        $allot_model = new Allot();
        $page = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;
        $allot_no = $this->params['allot_no'] ?? '';
        $store_id = $this->store_id;

        $condition = [
            [ 'site_id', '=', $this->site_id ],
        ];
        $condition[] = [ 'output_store_id|input_store_id', '=', $store_id ];

        if ($allot_no) {
            $condition[] = [ 'allot_no', '=', $allot_no ];
        }
        $result = $allot_model->getStockAllotPageList($condition, $page, $page_size, 'create_time desc');
        return $this->response($result);
    }

    /**
     * 详情
     */
    public function detail()
    {
        $allot_id = $this->params['allot_id'] ?? 0;
        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'allot_id', '=', $allot_id ],
            [ 'output_store_id|input_store_id', '=', $this->store_id ]
        ];
        $allot_model = new Allot();
        $detail = $allot_model->getAllotInfo($condition);
        $detail[ 'data' ][ 'uid' ] = $this->user_info[ 'uid' ];
        $document_model = new Document();
         
        $detail[ 'data' ][ 'is_audit' ] = $document_model->checkAudit(
            [
                'app_module' => $this->app_module,
                'is_admin' => $this->user_info[ 'is_admin' ],
                'menu_array' => $this->menu_array
            ]
        )[ 'code' ];
        return $this->response($detail);
    }

    /**
     * 创建调拨单
     */
    public function addAllocate()
    {
        $document_model = new Document();
        $audit_result = $document_model->checkAudit(
            [
                'app_module' =>'store',
                'is_admin'=>$this->user_info['is_admin'],
                'menu_array'=>$this->menu_array
            ]
        );
        if($audit_result['code'] < 0)
            return $this->response($audit_result);

        $type = $this->params[ 'allot_type' ] ?? '';//in   out
        $store_id = $this->params[ 'temp_store_id' ] ?? 0;
        if ($type == 'in') {
            $output_store_id = $store_id;
            $input_store_id = $this->store_id;
        } else {
            $output_store_id = $this->store_id;
            $input_store_id = $store_id;
        }
        $allot_model = new Allot();
        $data = [
            'output_store_id' => $output_store_id,
            'input_store_id' => $input_store_id,
            'remark' => $this->params[ 'remark' ] ?? '',
            'allot_no' => $this->params[ 'allot_no' ] ?? '',
            'goods_sku_list' => !empty($this->params[ 'goods_sku_list' ]) ? json_decode($this->params[ 'goods_sku_list' ], true) : [],
            'allot_time' => !empty($this->params[ 'allot_time' ]) ? date_to_time($this->params[ 'allot_time' ]) : 0,
            'is_auto_audit' => false,
            'operater' => $this->uid,
            'site_id' => $this->site_id
        ];

        $result = $allot_model->addAllot($data);
        return $this->response($result);
    }

    /**
     * 编辑盘点记录
     * @return mixed
     */
    public function editAllocate()
    {
        $document_model = new Document();
        $audit_result = $document_model->checkAudit(
            [
                'app_module' =>$this->app_module,
                'is_admin'=>$this->user_info['is_admin'],
                'menu_array'=>$this->menu_array
            ]
        );
        if($audit_result['code'] < 0)
            return $this->response($audit_result);

        $type = $this->params[ 'allot_type' ] ?? '';//in   out
        $store_id = $this->params[ 'temp_store_id' ] ?? 0;
        if ($type == 'in') {
            $output_store_id = $store_id;
            $input_store_id = $this->store_id;
        } else {
            $output_store_id = $this->store_id;
            $input_store_id = $store_id;
        }
        $allot_model = new Allot();
        $data = [
            'output_store_id' => $output_store_id,
            'input_store_id' => $input_store_id,
            'remark' => $this->params[ 'remark' ] ?? '',
            'allot_no' => $this->params[ 'allot_no' ] ?? '',
            'allot_id' => $this->params[ 'allot_id' ] ?? '',
            'goods_sku_list' => !empty($this->params[ 'goods_sku_list' ]) ? json_decode($this->params[ 'goods_sku_list' ], true) : [],
            'allot_time' => !empty($this->params[ 'allot_time' ]) ? date_to_time($this->params[ 'allot_time' ]) : 0,
            'operater' => $this->uid,
            'user_info' => $this->user_info,
            'is_auto_audit' => false,
            'site_id' => $this->site_id
        ];

        $result = $allot_model->editAllot($data);
        return $this->response($result);
    }

    /**
     * 获取编辑调拨单数据
     * @return false|string
     */
    public function editData()
    {
        $allot_id = $this->params['allot_id'] ?? 0;

        $allot_model = new Allot();
        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'allot_id', '=', $allot_id ],
            [ 'output_store_id|input_store_id', '=', $this->store_id ],
        ];
        $allot_info = $allot_model->getAllotEditData($condition);
        return $this->response($allot_info);
    }

    /**
     * 通过审核
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

        $allot_id = $this->params['allot_id'] ?? 0;
        $allot_model = new Allot();
        $res = $allot_model->audit([
            'site_id' => $this->site_id,
            'user_info' => $this->user_info,
            'allot_id' => $allot_id,
        ]);
        return $this->response($res);

    }

    /**
     * 审核拒绝
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

        $allot_id = $this->params['allot_id'] ?? 0;
        $allot_model = new Allot();
        $refuse_reason = $this->params['refuse_reason'] ?? '';

        $res = $allot_model->refuse([
            'site_id' => $this->site_id,
            'user_info' => $this->user_info,
            'allot_id' => $allot_id,
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
        $allot_id = $this->params['allot_id'] ?? 0;
        $allot_model = new Allot();
        $res = $allot_model->delete([
            'site_id' => $this->site_id,
            'user_info' => $this->user_info,
            'allot_id' => $allot_id,
        ]);
        return $this->response($res);

    }

    /**
     * 获取调拨单号
     * @return false|string
     */
    public function getAllotNo()
    {
        $allot_model = new Allot();
        $allot_no = $allot_model->getAllotNo();
        return $this->response($this->success($allot_no));
    }

}
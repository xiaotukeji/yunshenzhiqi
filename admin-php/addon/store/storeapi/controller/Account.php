<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\store\storeapi\controller;

use addon\store\model\StoreAccount;
use app\storeapi\controller\BaseStoreApi;

/**
 * 门店账户
 */
class Account extends BaseStoreApi
{
    /**
     * 账户列表
     */
    public function pages()
    {
        $page = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;
        $remark = $this->params['remark'] ?? '';
        $start_date = $this->params['start_date'] ?? '';
        $end_date = $this->params['end_date'] ?? '';
        $from_type = $this->params['from_type'] ?? '';
        $store_id = $this->store_id;

        $alias = 'sa';
        $join = [
            ['store s', 'sa.store_id = s.store_id', 'inner'],
        ];

        $condition = [ [ 'sa.site_id', '=', $this->site_id ] ];

        if (!empty($remark)) {
            $condition[] = [ 'sa.remark', 'like', '%' . $remark . '%' ];
        }
        if (!empty($from_type)) {
            $condition[] = [ 'sa.from_type', '=', $from_type ];
        }
        if ($store_id > 0) {
            $condition[] = [ 'sa.store_id', '=', $store_id ];
        }
        if ($start_date != '' && $end_date != '') {
            $condition[] = [ 'sa.create_time', 'between', [ strtotime($start_date), strtotime($end_date) ] ];
        } else if ($start_date != '' && $end_date == '') {
            $condition[] = [ 'sa.create_time', '>=', strtotime($start_date) ];
        } else if ($start_date == '' && $end_date != '') {
            $condition[] = [ 'sa.create_time', '<=', strtotime($end_date) ];
        }

        $field = 'sa.*,s.store_name';
        $order = 'sa.id desc';

        $account_model = new StoreAccount();
        $list = $account_model->getStoreAccountPageList($condition, $page, $page_size, $order, $field, $alias, $join);
        $list['data']['list'] = $account_model->getRelatedInfo($list['data']['list']);
        return $this->response($list);
    }

    /**
     * 筛选内容
     * @return false|string
     */
    public function screen()
    {
        $account_model = new StoreAccount();
        $from_type_list = $account_model->from_type;
        $res = [
            'from_type_list' => $from_type_list,
        ];
        return $this->response($this->success($res));
    }

    /**
     * 账户导出
     */
    public function export()
    {
        $input = $this->params;
        $input['store_id'] = $this->store_id;
        $account_model = new StoreAccount();
        $res = $account_model->addStoreAccountExport($input, $this->store_id);
        return $this->response($res);
    }
}
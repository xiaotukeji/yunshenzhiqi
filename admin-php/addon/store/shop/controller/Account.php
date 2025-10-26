<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\store\shop\controller;

use addon\store\model\StoreAccount;
use app\model\system\Export as ExportModel;
use app\shop\controller\BaseShop;

/**
 * 门店账户
 */
class Account extends BaseShop
{
    /**
     * 门店提现列表
     * @return mixed
     */
    public function lists()
    {
        $account_model = new StoreAccount();
        if (request()->isJson()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $remark = input('remark', '');
            $start_date = input('start_date', '');
            $end_date = input('end_date', '');
            $from_type = input('from_type', '');
            $store_id = input('store_id', 0);//门店
            
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
            $res = $account_model->getStoreAccountPageList($condition, $page, $page_size, $order, $field, $alias, $join);

            //账户金额累加
            $account_info = $account_model->getStoreAccountInfo($condition, 'sum(sa.account_data) as account_data_sum', $alias, $join)['data'];
            $res['data']['account_data_sum'] = $account_info['account_data_sum'] ?? 0;

            return $res;
        } else {
            //门店列表
            $store_model = new \app\model\store\Store();
            $store_list = $store_model->getStoreList([ [ 'site_id', '=', $this->site_id ] ], '*', 'is_default desc,store_id desc')[ 'data' ] ?? [];
            $this->assign('store_list', $store_list);
            //门店相关统计
            $stat_model = new \addon\store\model\Stat();
            $stat_condition = array (
                [ 'site_id', '=', $this->site_id ]
            );
            $total_account = $stat_model->getStoreAccountSum($stat_condition, 'account')[ 'data' ] ?? 0;
            $total_account_apply = $stat_model->getStoreAccountSum($stat_condition, 'account_apply')[ 'data' ] ?? 0;
            $total_account_withdraw = $stat_model->getStoreAccountSum($stat_condition, 'account_withdraw')[ 'data' ] ?? 0;
            $this->assign('stat', [
                'total_account' => $total_account,
                'total_account_apply' => $total_account_apply,
                'total_account_withdraw' => $total_account_withdraw,
            ]);
            //来源方式
            $from_type_list = $account_model->from_type;
            $this->assign('from_type_list', $from_type_list);
            return $this->fetch('account/lists');
        }
    }

    /**
     * 添加导出
     */
    public function addExport()
    {
        $input = input();
        $input['site_id'] = $this->site_id;
        $account_model = new StoreAccount();
        return $account_model->addStoreAccountExport($input);
    }

    /**
     * 导出列表
     */
    public function exportList()
    {
        $param = [
            'from_type_list' => [
                ['id' => 'store_account', 'name' => '门店账户'],
            ],
            'lists_url' => 'store://shop/account/exportList',
            'delete_url' => 'store://shop/account/deleteExport',
        ];
        $export_controller = new \app\shop\controller\Export();
        return $export_controller->lists($param);
    }

    /**
     * 删除导出
     */
    public function deleteExport()
    {
        $export_controller = new \app\shop\controller\Export();
        return $export_controller->delete('store_account');
    }
}
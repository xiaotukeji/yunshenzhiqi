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

use addon\mobileshop\model\Config as ConfigModel;
use addon\store\model\StoreWithdraw;
use addon\wechatpay\model\Config as WechatPayConfig;
use app\shop\controller\BaseShop;

/**
 * 门店提现控制器
 */
class Withdraw extends BaseShop
{

    /**
     * 转账
     */
    public function transferFinish()
    {
        if (request()->isJson()) {
            $withdraw_id = input('withdraw_id', 0);
            $voucher_img = input('voucher_img', '');
            $voucher_desc = input('voucher_desc', '');
            $withdraw_model = new StoreWithdraw();
            $data = array (
                'withdraw_id' => $withdraw_id,
                'site_id' => $this->site_id,
                'voucher_desc' => $voucher_desc,
                'voucher_img' => $voucher_img,
            );
            $result = $withdraw_model->transferFinish($data);
            return $result;
        }
    }

    /**
     * 门店提现列表
     * @return mixed
     */
    public function lists()
    {
        $withdraw_model = new StoreWithdraw();
        if (request()->isJson()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $withdraw_no = input('withdraw_no', '');
            $start_date = input('start_date', '');
            $end_date = input('end_date', '');
            $status = input('status', 'all');//提现状态
            $transfer_type = input('transfer_type', '');//提现转账方式
            $store_id = input('store_id', 0);//门店

            $transfer_start_date = input('transfer_start_date', '');
            $transfer_end_date = input('transfer_end_date', '');
            $settlement_type = input('settlement_type', '');
            $condition = [ [ 'sw.site_id', '=', $this->site_id ] ];

            if (!empty($withdraw_no)) {
                $condition[] = [ 'withdraw_no', 'like', '%' . $withdraw_no . '%' ];
            }
            if (!empty($transfer_type)) {
                $condition[] = [ 'transfer_type', '=', $transfer_type ];
            }
            if ($store_id > 0) {
                $condition[] = [ 'sw.store_id', '=', $store_id ];
            }
            if (!empty($settlement_type)) {
                $condition[] = [ 'settlement_type', '=', $settlement_type ];
            }
            if ($status != 'all') {
                $condition[] = [ 'sw.status', '=', $status ];
            }
            if ($start_date != '' && $end_date != '') {
                $condition[] = [ 'apply_time', 'between', [ strtotime($start_date), strtotime($end_date) ] ];
            } else if ($start_date != '' && $end_date == '') {
                $condition[] = [ 'apply_time', '>=', strtotime($start_date) ];
            } else if ($start_date == '' && $end_date != '') {
                $condition[] = [ 'apply_time', '<=', strtotime($end_date) ];
            }

            if ($transfer_start_date != '' && $transfer_end_date != '') {
                $condition[] = [ 'transfer_time', 'between', [ strtotime($transfer_start_date), strtotime($transfer_end_date) ] ];
            } else if ($transfer_start_date != '' && $transfer_end_date == '') {
                $condition[] = [ 'transfer_time', '>=', strtotime($transfer_start_date) ];
            } else if ($transfer_start_date == '' && $transfer_end_date != '') {
                $condition[] = [ 'transfer_time', '<=', strtotime($transfer_end_date) ];
            }

            $order = 'apply_time desc';
            $join = [
                [ 'store s', 's.store_id = sw.store_id', 'left' ]
            ];
            return $withdraw_model->getStoreWithdrawPageList($condition, $page, $page_size, $order, 'sw.*,s.telphone', 'sw', $join);
        } else {
            $this->assign('settlement_type_list', $withdraw_model->settlement_type);
            $transfer_type_list = $withdraw_model->getTransferType($this->site_id);
            $this->assign('transfer_type_list', $transfer_type_list);
            $store_model = new \app\model\store\Store();
            $store_list = $store_model->getStoreList([ [ 'site_id', '=', $this->site_id ] ])[ 'data' ] ?? [];
            $this->assign('store_list', $store_list);
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
            $this->assign('status_list', $withdraw_model->status);

            $config_model = new WechatPayConfig();
            $config = $config_model->getPayConfig($this->site_id)[ 'data' ][ 'value' ];;
            $transfer_v3_type = $config['transfer_type'] == 'v3' && $config['transfer_v3_type'] == $config_model::TRANSFER_V3_TYPE_USER ;
            $this->assign("transfer_v3_type",$transfer_v3_type);

            return $this->fetch('withdraw/lists');
        }
    }

    /**
     * 提现详情
     * @return mixed
     */
    public function detail()
    {
        $withdraw_id = input('withdraw_id', 0);
        $withdraw_model = new StoreWithdraw();
        $withdraw_info = $withdraw_model->detail([ 'site_id' => $this->site_id, 'withdraw_id' => $withdraw_id ])[ 'data' ] ?? [];
        if (empty($withdraw_info))
            $this->error('找不到此项提现记录！');

        $this->assign('withdraw_info', $withdraw_info);
        return $this->fetch('withdraw/detail');
    }

    /**
     * 同意
     * @return array
     */
    public function agree()
    {
        if (request()->isJson()) {
            $withdraw_id = input('withdraw_id', 0);
            $withdraw_model = new StoreWithdraw();

            $params = array (
                'site_id' => $this->site_id,
                'withdraw_id' => $withdraw_id,
                'status' => 0
            );
            $result = $withdraw_model->agree($params);
            return $result;
        }
    }

    /**
     * 拒绝
     * @return array
     */
    public function refuse()
    {
        if (request()->isJson()) {
            $withdraw_id = input('withdraw_id', 0);
            $refuse_reason = input('refuse_reason', '');
            $withdraw_model = new StoreWithdraw();
            $params = array (
                'site_id' => $this->site_id,
                'withdraw_id' => $withdraw_id,
                'refuse_reason' => $refuse_reason
            );
            $result = $withdraw_model->refuse($params);
            return $result;
        }
    }

    /**
     * 转账
     */
    public function transfer()
    {
        if (request()->isJson()) {
            $withdraw_id = input('withdraw_id', 0);
            $withdraw_model = new StoreWithdraw();
            $result = $withdraw_model->transfer($withdraw_id);
            return $result;
        }
    }
}
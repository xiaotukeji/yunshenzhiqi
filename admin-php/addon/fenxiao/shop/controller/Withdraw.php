<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\fenxiao\shop\controller;

use addon\fenxiao\model\FenxiaoStat;
use addon\fenxiao\model\FenxiaoWithdraw as FenxiaoWithdrawModel;
use addon\wechatpay\model\Config as WechatPayConfig;
use app\shop\controller\BaseShop;

/**
 *  分销等级管理
 */
class Withdraw extends BaseShop
{

    /**
     * 会员提现列表
     * @return mixed
     */
    public function lists()
    {
        $model = new FenxiaoWithdrawModel();
        $transfer_type_list = $model->getTransferType($this->site_id);
        if (request()->isJson()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $withdraw_no = input('withdraw_no', '');
            $start_date = input('start_date', '');
            $end_date = input('end_date', '');
            $status = input('status', 'all');//提现状态
            $transfer_type = input('transfer_type', '');//提现转账方式
            $fenxiao_name = input('fenxiao_name', '');//提现转账方式

            $payment_start_date = input('payment_start_date', '');
            $payment_end_date = input('payment_end_date', '');

            $condition = [ [ 'fw.site_id', '=', $this->site_id ] ];
            if (!empty($withdraw_no)) {
                $condition[] = [ 'fw.withdraw_no', 'like', '%' . $withdraw_no . '%' ];
            }
            if (!empty($transfer_type)) {
                $condition[] = [ 'fw.transfer_type', '=', $transfer_type ];
            }
            if ($status != "all") {
                $condition[] = [ 'fw.status', '=', $status ];
            }
            if (!empty($fenxiao_name)) {
                $condition[] = [ 'm.nickname|fw.fenxiao_name', '=', $fenxiao_name ];
            }
            if ($start_date != '' && $end_date != '') {
                $condition[] = [ 'fw.create_time', 'between', [ strtotime($start_date), strtotime($end_date) ] ];
            } else if ($start_date != '' && $end_date == '') {
                $condition[] = [ 'fw.create_time', '>=', strtotime($start_date) ];
            } else if ($start_date == '' && $end_date != '') {
                $condition[] = [ 'fw.create_time', '<=', strtotime($end_date) ];
            }

            if ($payment_start_date != '' && $payment_end_date != '') {
                $condition[] = [ 'fw.payment_time', 'between', [ strtotime($payment_start_date), strtotime($payment_end_date) ] ];
            } else if ($payment_start_date != '' && $payment_end_date == '') {
                $condition[] = [ 'fw.payment_time', '>=', strtotime($payment_start_date) ];
            } else if ($payment_start_date == '' && $payment_end_date != '') {
                $condition[] = [ 'fw.payment_time', '<=', strtotime($payment_end_date) ];
            }

            $order = 'fw.create_time desc';
            $field = 'fw.id,fw.site_id,fw.withdraw_no,fw.member_id,fw.fenxiao_id,fw.fenxiao_name,fw.withdraw_type,fw.bank_name,fw.account_number,fw.realname,fw.mobile,fw.money,fw.withdraw_rate,
            fw.withdraw_rate_money,fw.real_money,fw.`status`,fw.remark,fw.create_time,fw.payment_time,fw.modify_time,fw.transfer_type,fw.transfer_name,fw.transfer_remark,fw.transfer_no,
            fw.transfer_account_no,fw.document,fw.audit_time,fw.refuse_reason,fw.applet_type,fw.fail_reason, m.headimg,m.nickname,m.mobile as member_mobile,m.headimg';
            $join = [
                [ 'member m', 'fw.member_id = m.member_id', 'left' ]
            ];
            $list = $model->getFenxiaoWithdrawPageList($condition, $page, $page_size, $order, $field, 'fw', $join);

            foreach ($list[ 'data' ][ 'list' ] as $k => $v) {
                $list[ 'data' ][ 'list' ][ $k ][ 'transfer_type_name' ] = $transfer_type_list[ $v[ 'transfer_type' ] ];
            }
            return $list;
        } else {
            $this->assign('transfer_type_list', $transfer_type_list);

            $fenxiao_stat_model = new FenxiaoStat();
            $fenxiao_balance_sum = $fenxiao_stat_model->getFenxiaoAccountSum($this->site_id)[ 'data' ] ?? [];
            $this->assign('fenxiao_balance_sum', $fenxiao_balance_sum);

            //提现状态
            $this->assign('status_list', $model->status);

            $config_model = new WechatPayConfig();
            $config = $config_model->getPayConfig($this->site_id)[ 'data' ][ 'value' ];;
            $transfer_v3_type = $config['transfer_type'] == 'v3' && $config['transfer_v3_type'] == $config_model::TRANSFER_V3_TYPE_USER ;

            $this->assign("transfer_v3_type",$transfer_v3_type);
            return $this->fetch("withdraw/lists");
        }
    }

    /**
     * 提现记录详情
     * @return mixed
     */
    public function detail()
    {
        $fenxiao_withdraw_model = new FenxiaoWithdrawModel();
        $params = array (
            'id' => input('id', 0),
            'site_id' => $this->site_id
        );
        $detail = $fenxiao_withdraw_model->getFenxiaoWithdrawDetail($params)[ 'data' ] ?? [];
        if (empty($detail))
            $this->error('找不到提现账户记录');

        $this->assign('info', $detail);
        return $this->fetch('withdraw/detail');
    }

    /**
     * 同意
     * @return array
     */
    public function agree()
    {
        if (request()->isJson()) {
            $id = input('id', 0);
            $fenxiao_withdraw_model = new FenxiaoWithdrawModel();
            $params = array (
                'site_id' => $this->site_id,
                "id" => $id,
            );
            $result = $fenxiao_withdraw_model->agree($params);
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
            $id = input('id', 0);
            $refuse_reason = input('refuse_reason', '');
            $fenxiao_withdraw_model = new FenxiaoWithdrawModel();
            $data = array (
                "refuse_reason" => $refuse_reason,
                'site_id' => $this->site_id,
                'id' => $id
            );
            $result = $fenxiao_withdraw_model->refuse($data);
            return $result;
        }
    }

    /**
     * 转账
     */
    public function transferFinish()
    {
        if (request()->isJson()) {
            $id = input('id', 0);
            $certificate = input('certificate', '');
            $certificate_remark = input('certificate_remark', '');
            $fenxiao_withdraw_model = new FenxiaoWithdrawModel();
            $data = array (
                "id" => $id,
                "site_id" => $this->site_id,
                "certificate" => $certificate,
                "certificate_remark" => $certificate_remark,
            );
            $result = $fenxiao_withdraw_model->transferFinish($data);
            return $result;
        }
    }

    /**
     * 转账
     */
    public function transfer()
    {
        if (request()->isJson()) {
            $id = input('id', 0);
            $fenxiao_withdraw_model = new FenxiaoWithdrawModel();
            $result = $fenxiao_withdraw_model->transfer([ 'id' => $id, 'site_id' => $this->site_id ]);
            return $result;
        }
    }

    public function export()
    {
        $fenxiao_withdraw_model = new FenxiaoWithdrawModel();

        $withdraw_no = input('withdraw_no', '');
        $start_date = input('start_date', '');
        $end_date = input('end_date', '');
        $status = input('status', 'all');//提现状态
        $transfer_type = input('transfer_type', '');//提现转账方式
        $fenxiao_name = input('fenxiao_name', '');//提现转账方式

        $payment_start_date = input('payment_start_date', '');
        $payment_end_date = input('payment_end_date', '');

        $condition = [ [ 'site_id', '=', $this->site_id ] ];
        if (!empty($withdraw_no)) {
            $condition[] = [ 'withdraw_no', 'like', '%' . $withdraw_no . '%' ];
        }
        if (!empty($transfer_type)) {
            $condition[] = [ 'transfer_type', '=', $transfer_type ];
        }
        if ($status != "all") {
            $condition[] = [ 'status', '=', $status ];
        }
        if (!empty($fenxiao_name)) {
            $condition[] = [ 'fenxiao_name', '=', $fenxiao_name ];
        }
        if ($start_date != '' && $end_date != '') {
            $condition[] = [ 'create_time', 'between', [ strtotime($start_date), strtotime($end_date) ] ];
        } else if ($start_date != '' && $end_date == '') {
            $condition[] = [ 'create_time', '>=', strtotime($start_date) ];
        } else if ($start_date == '' && $end_date != '') {
            $condition[] = [ 'create_time', '<=', strtotime($end_date) ];
        }

        if ($payment_start_date != '' && $payment_end_date != '') {
            $condition[] = [ 'payment_time', 'between', [ strtotime($payment_start_date), strtotime($payment_end_date) ] ];
        } else if ($payment_start_date != '' && $payment_end_date == '') {
            $condition[] = [ 'payment_time', '>=', strtotime($payment_start_date) ];
        } else if ($payment_start_date == '' && $payment_end_date != '') {
            $condition[] = [ 'payment_time', '<=', strtotime($payment_end_date) ];
        }
        $order = 'create_time desc';
        $fenxiao_withdraw_model->exportFenxiaoWithdraw($condition, $order, $this->site_id);
    }
}
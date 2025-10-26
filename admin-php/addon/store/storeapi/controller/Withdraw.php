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

use addon\mobileshop\model\Config as ConfigModel;
use addon\store\model\StoreWithdraw;
use app\storeapi\controller\BaseStoreApi;

/**
 * 门店结算控制器
 */
class Withdraw extends BaseStoreApi
{
    /**
     * 门店申请结算
     */
    public function apply()
    {
        $store_withdraw_model = new StoreWithdraw();
        $money = $this->params[ 'money' ] ?? 0;
        $apply_params = array (
            'site_id' => $this->site_id,
            'store_id' => $this->store_id,
            'money' => $money,
            'settlement_type' => 'apply'
        );
        $withdraw_result = $store_withdraw_model->apply($apply_params);
        return $this->response($withdraw_result);
    }

    /**
     * 结算记录
     * @return false|string
     */
    public function page()
    {
        $page = $this->params[ 'page' ] ?? 1;
        $page_size = $this->params[ 'page_size' ] ?? PAGE_LIST_ROWS;
        $withdraw_no = $this->params[ 'withdraw_no' ] ?? '';
        $start_date = $this->params[ 'start_date' ] ?? '';
        $end_date = $this->params[ 'end_date' ] ?? '';
        $status = $this->params[ 'status' ] ?? 'all';//提现状态
        $transfer_type = $this->params[ 'transfer_type' ] ?? '';//提现转账方式
        $payment_start_date = $this->params[ 'payment_start_date' ] ?? '';
        $payment_end_time = $this->params[ 'payment_end_time' ] ?? '';
        $settlement_type = $this->params[ 'settlement_type' ] ?? '';

        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'store_id', '=', $this->store_id ]
        ];

        if (!empty($withdraw_no)) {
            $condition[] = [ 'withdraw_no', 'like', '%' . $withdraw_no . '%' ];
        }
        if (!empty($transfer_type)) {
            $condition[] = [ 'transfer_type', '=', $transfer_type ];
        }
        if (!empty($settlement_type)) {
            $condition[] = [ 'settlement_type', '=', $settlement_type ];
        }
        if ($status != 'all') {
            $condition[] = [ 'status', '=', $status ];
        }
        if ($start_date != '' && $end_date != '') {
            $condition[] = [ 'apply_time', 'between', [ strtotime($start_date), strtotime($end_date) ] ];
        } else if ($start_date != '' && $end_date == '') {
            $condition[] = [ 'apply_time', '>=', strtotime($start_date) ];
        } else if ($start_date == '' && $end_date != '') {
            $condition[] = [ 'apply_time', '<=', strtotime($end_date) ];
        }

        if ($payment_start_date != '' && $payment_end_time != '') {
            $condition[] = [ 'transfer_time', 'between', [ strtotime($payment_start_date), strtotime($payment_end_time) ] ];
        } else if ($payment_start_date != '' && $payment_end_time == '') {
            $condition[] = [ 'transfer_time', '>=', strtotime($payment_start_date) ];
        } else if ($payment_start_date == '' && $payment_end_time != '') {
            $condition[] = [ 'transfer_time', '<=', strtotime($payment_end_time) ];
        }

        $order = 'apply_time desc';
        $withdraw_model = new StoreWithdraw();
        $data = $withdraw_model->getStoreWithdrawPageList($condition, $page, $page_size, $order);

        return $this->response($data);
    }

    /**
     * 筛选内容
     * @return false|string
     */
    public function screen()
    {
        $withdraw_model = new StoreWithdraw();
        $data = [
            'status' => $withdraw_model->status,
            'settlement_type' => $withdraw_model->settlement_type,
            'transfer_type_list' => $withdraw_model->getTransferType($this->site_id)
        ];
        return $this->response($this->success($data));
    }

    /**
     * 获取结算详情
     * @return false|string
     */
    public function detail()
    {
        $withdraw_id = $this->params[ 'withdraw_id' ] ?? 0;
        $withdraw_model = new StoreWithdraw();
        $withdraw_info = $withdraw_model->detail([ 'site_id' => $this->site_id, 'store_id' => $this->store_id, 'withdraw_id' => $withdraw_id ]);
        return $this->response($withdraw_info);
    }


    /**
     * 转账二维码
     * @return false|string
     */
    public function getTransferCode()
    {
        $id = input('id', 0);
        if(empty($id)){
            return $this->response($this->error([],'id参数有误'));
        }

        //获取扫码收款页面路径
        $config_model = new \app\model\web\Config();
        $config_info = $config_model->getH5DomainName($this->site_id)[ 'data' ][ 'value' ];
        if($config_info['deploy_way'] == 'default'){
            $domain_url = ROOT_URL.'/h5';
        }else{
            $domain_url = $config_info['domain_name_h5'];
        }
        $url = $domain_url.'/pages_tool/store/store_withdraw?id='.$id;

        //生成页面二维码
        $qrcode_model = new \app\model\system\Qrcode();
        $qrcode_res = $qrcode_model->createBase64Qrcode($url);

        return  $this->response($qrcode_res);
    }


}
<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\fenxiao\api\controller;

use addon\fenxiao\model\FenxiaoWithdraw;
use app\api\controller\BaseApi;
use app\model\member\Member;

/**
 * 分销提现
 */
class Withdraw extends BaseApi
{

    /**
     * 提现记录分页
     * @return false|string
     */
    public function page()
    {

        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $page = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;
        $status = $this->params['status'] ?? 0;// 当前状态 1待审核 2待转账 3已转账 -1 已拒绝

        $condition = [
            [ 'member_id', '=', $this->member_id ]
        ];
        if (!empty($status)) {
            $condition[] = [ 'status', '=', $status ];
        }

        $order = 'id desc';
        $withdraw_model = new FenxiaoWithdraw();
        $list = $withdraw_model->getFenxiaoWithdrawPageList($condition, $page, $page_size, $order);
        foreach ($list[ 'data' ][ 'list' ] as $k => $v) {
            $list[ 'data' ][ 'list' ][ $k ] = $withdraw_model->tran($v);
        }
        return $this->response($list);
    }

    /**
     * 获取转账方式
     * @return false|string
     */
    public function transferType()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $member_model = new Member();
        $member_info = $member_model->getMemberInfo([ [ 'member_id', '=', $token[ 'data' ][ 'member_id' ] ] ], 'site_id,wx_openid,weapp_openid');
        $withdraw_config_model = new \addon\fenxiao\model\Config();
        $transfer_type_list = $withdraw_config_model->getTransferType($member_info[ 'data' ][ 'site_id' ]);
        if (empty($member_info[ 'data' ][ 'wx_openid' ]) && empty($member_info[ 'data' ][ 'weapp_openid' ])) {
            unset($transfer_type_list[ 'wechatpay' ]);
        }
        return $this->response($this->success($transfer_type_list));
    }

    /**
     * 申请提现
     * @return mixed
     */
    public function apply()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $apply_money = $this->params[ 'apply_money' ] ?? 0;
        $transfer_type = $this->params[ 'transfer_type' ] ?? '';//提现方式
        $realname = $this->params[ 'realname' ] ?? '';//真实姓名
        $bank_name = $this->params[ 'bank_name' ] ?? '';//银行名称
        $account_number = $this->params[ 'account_number' ] ?? '';//账号名称
        $mobile = $this->params[ 'mobile' ] ?? '';//手机号
        $app_type = $this->params[ 'app_type' ];
        $fenxiao_withdraw_model = new FenxiaoWithdraw();
        $data = array (
            'member_id' => $this->member_id,
            'transfer_type' => $transfer_type,
            'realname' => $realname,
            'bank_name' => $bank_name,
            'account_number' => $account_number,
            'apply_money' => $apply_money,
            'mobile' => $mobile,
            'app_type' => $app_type
        );
        $result = $fenxiao_withdraw_model->apply($data, $this->site_id);
        return $this->response($result);
    }

    /**
     * 提现详情
     * @return mixed
     */
    public function detail()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $id = $this->params[ 'id' ] ?? 0;
        $fenxiao_withdraw_model = new FenxiaoWithdraw();
        $params = array (
            'id' => $id,
            'site_id' => $this->site_id
        );
        $result = $fenxiao_withdraw_model->getFenxiaoWithdrawDetail($params);
        return $this->response($result);
    }

}
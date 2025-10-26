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

namespace addon\cashier\storeapi\controller;

use addon\cardservice\model\MemberCard;
use addon\coupon\model\Coupon as CouponModel;
use addon\coupon\model\CouponType;
use addon\coupon\model\MemberCoupon;
use addon\store\model\StoreMember;
use app\dict\member_account\AccountDict;
use app\model\member\Member as MemberModel;
use app\model\member\MemberAccount as MemberAccountModel;
use app\model\member\MemberLabel as MemberLabelModel;
use app\model\member\MemberLevel as MemberLevelModel;
use app\model\message\Message;
use app\model\order\OrderCommon;
use app\model\system\PayBalance;
use app\storeapi\controller\BaseStoreApi;
use Exception;
use think\facade\Cache;

/**
 * 会员管理 控制器
 */
class Member extends BaseStoreApi
{
    public function lists()
    {
        $page_index = $this->params[ 'page' ] ?? 1;
        $page_size = $this->params[ 'page_size' ] ?? PAGE_LIST_ROWS;
        $search_text = $this->params[ 'search_text' ] ?? '';

        $condition = [
            [ 'site_id', '=', $this->site_id ],
        ];
        if (!empty($search_text)) $condition[] = [ 'username|nickname|mobile|member_code', 'like', '%' . $search_text . '%' ];
        $member = new MemberModel();
        $field = 'member_id,mobile,nickname,headimg,email,status,headimg,member_level,member_level_name,member_label,member_label_name,last_login_time,sex,point,balance,growth,balance_money,is_member,order_money,order_complete_money,order_num,order_complete_num';
        $list = $member->getMemberPageList($condition, $page_index, $page_size, 'member_id desc', $field);

        return $this->response($list);
    }

    /*
     * 会员信息
     */
    public function info($mid = 0)
    {
        $member_id = $this->params[ 'member_id' ] ?? 0;
        if (!empty($mid)) {
            $member_id = $mid;
        }
        if (empty($member_id)) return $this->response($this->error());

        $condition = [
            [ 'member_id', '=', $member_id ]
        ];

        $member = new MemberModel();
        $field = 'member_id, username, nickname, mobile, status, headimg, member_level, member_level_name, member_label, member_label_name, last_login_time, last_visit_time, realname, sex, location, birthday, reg_time, point, balance, growth, balance_money, account5, is_auth, is_member, member_time, order_money, order_complete_money, order_num, order_complete_num, balance_withdraw_apply, balance_withdraw, member_code';
        $data = $member->getMemberInfo($condition, $field);

        $coupon_model = new MemberCoupon();
        $coupon_num = $coupon_model->getMemberCouponNum($member_id, 1, $this->site_id)[ 'data' ];
        $data[ 'data' ][ 'coupon_num' ] = $coupon_num;
        $data[ 'data' ][ 'card_num' ] = ( new MemberCard() )->getCardCount([ [ 'mgc.member_id', '=', $member_id ], [ 'mgc.status', '=', 1 ], [ 'g.is_delete', '=', 0 ] ], 'card_id', 'mgc', [
            [ 'goods g', 'g.goods_id = mgc.goods_id', 'inner' ]
        ])[ 'data' ];
        return $this->response($data);
    }

    public function searchMember()
    {
        $search_text = $this->params[ 'search_text' ] ?? '';
        $search_type = $this->params[ 'search_type' ] ?? '';

        if ($search_type == 'mobile' || $search_type == 'nickname' || $search_type == 'member_code') {
        } else {
            $search_type = 'username|mobile|member_code';
        }

        $condition = [
            [ $search_type, '=', $search_text ],
            [ 'is_delete', '=', 0 ]
        ];
        $member = new MemberModel();
        $data = $member->getMemberInfo($condition);

        return $this->response($data);
    }

    /**
     * 根据手机号查询会员，支持模糊
     * @return false|string
     */
    public function searchMemberByMobile()
    {
        $mobile = $this->params[ 'mobile' ] ?? '';
        if (empty($mobile)) {
            return $this->response($this->error('', '缺少参数 mobile'));
        }

        $condition = [
            [ 'mobile', 'like', '%' . $mobile . '%' ],
            [ 'is_delete', '=', 0 ]
        ];
        $member = new MemberModel();

        $field = 'member_id, username, nickname, mobile, status, headimg, member_level, member_level_name, member_label, member_label_name, last_login_time, last_visit_time, realname, sex, location, birthday, reg_time, point, balance, growth, balance_money, account5, is_auth, is_member, member_time, order_money, order_complete_money, order_num, order_complete_num, balance_withdraw_apply, balance_withdraw, member_code';

        $res = $member->getMemberPageList($condition, 1, PAGE_LIST_ROWS, 'member_id desc', $field)[ 'data' ];
        if ($res[ 'count' ] == 1) {
            return $this->response($this->success($res[ 'list' ][ 0 ]));
        } elseif ($res[ 'count' ] > 1) {
            return $this->response($this->error($res[ 'count' ], '该账户存在多个，请输入完整的手机号进行查询'));
        } elseif ($res[ 'count' ] == 0) {
            return $this->response($this->error($res[ 'count' ], '会员不存在'));
        }
    }

    public function addMember()
    {
        $data = [
            'site_id' => $this->site_id,
            'username' => '',
            'mobile' => $this->params[ 'mobile' ] ?? '',
            'email' => '',
            'status' => 1,
            'headimg' => '',
            'member_level' => $this->params[ 'member_level' ] ?? 0,
            'member_level_name' => $this->params[ 'member_level_name' ] ?? '',
            'nickname' => $this->params[ 'nickname' ],
            'sex' => $this->params[ 'sex' ] ?? 0,
            'birthday' => $this->params[ 'birthday' ] ? strtotime($this->params[ 'birthday' ]) : 0,
            'realname' => $this->params[ 'realname' ] ?? '',
            'reg_time' => time(),
        ];
        if (empty($data[ 'mobile' ])) return $this->response($this->error('', '手机号不能为空'));
        if (empty($data[ 'nickname' ])) $data[ 'nickname' ] = $data[ 'mobile' ];

        $member_model = new MemberModel();
        $add_res = $member_model->addMember($data);
        if ($add_res[ 'code' ] != 0) return $this->response($add_res);

        $res = ( new StoreMember() )->addStoreMember($this->store_id, $add_res[ 'data' ]);
        $this->addLog('添加会员' . $data[ 'username' ] . $data[ 'mobile' ]);

        return $this->response($add_res);
    }

    /**
     * 会员优惠券
     * @return false|string
     */
    public function coupon()
    {
        $member_id = $this->params[ 'member_id' ] ?? 0;
        $page = $this->params[ 'page' ] ?? 1;
        $page_size = $this->params[ 'page_size' ] ?? PAGE_LIST_ROWS;
        $state = $this->params[ 'state' ] ?? 1;

        $condition = [
            [ 'npc.member_id', '=', $member_id ],
            [ 'npc.state', '=', $state ]
        ];

        $coupon_model = new CouponModel();
        $list = $coupon_model->getMemberCouponPageList($condition, $page, $page_size);
        $coupon_type_model = new CouponType();
        foreach($list['data']['list'] as &$val){
            $val = $coupon_type_model->getCouponSubData($val);
        }
        return $this->response($list);
    }

    /**
     * 会员编辑
     */
    public function editMember()
    {
        $data = [];
        if (isset($this->params[ 'headimg' ])) $data[ 'headimg' ] = $this->params[ 'headimg' ];
        if (isset($this->params[ 'nickname' ])) $data[ 'nickname' ] = $this->params[ 'nickname' ];
        if (isset($this->params[ 'mobile' ])) $data[ 'mobile' ] = $this->params[ 'mobile' ];
        if (isset($this->params[ 'level_id' ]) && !empty($this->params[ 'level_id' ])) $data[ 'member_level' ] = $this->params[ 'level_id' ];
        if (isset($this->params[ 'level_id' ]) && !empty($this->params[ 'level_id' ])) {
            $member_level_model = new MemberLevelModel();
            $condition = [
                [ 'site_id', '=', $this->site_id ],
                [ 'level_id', '=', $this->params[ 'level_id' ] ],
                [ 'status', '=', 1 ]
            ];
            $member_level = $member_level_model->getFirstMemberLevel($condition);
            if(empty($member_level[ 'data' ])){
                return $this->response($this->error(null, '会员等级不存在'));
            }
            $data[ 'member_level_name' ] = $member_level[ 'data' ][ 'level_name' ];
        }
        if (isset($this->params[ 'label_id' ]) && !empty($this->params[ 'label_id' ])) $data[ 'member_label' ] = $this->params[ 'label_id' ];
        if (isset($this->params[ 'label_id' ]) && !empty($this->params[ 'label_id' ])) {
            $member_label_model = new MemberLabelModel();
            $member_label = $member_label_model->getMemberLabelInfo([ [ 'label_id', '=', $this->params[ 'label_id' ] ] ]);
            $data[ 'member_label_name' ] = $member_label[ 'data' ][ 'label_name' ];
        }
        if (isset($this->params[ 'sex' ])) $data[ 'sex' ] = $this->params[ 'sex' ];
        if (isset($this->params[ 'birthday' ])) $data[ 'birthday' ] = $this->params[ 'birthday' ] ? strtotime($this->params[ 'birthday' ]) : 0;
        $member_id = $this->params[ 'member_id' ];
        $member_model = new MemberModel();
        $this->addLog('编辑会员:id' . $member_id, $data);
        $info = $member_model->editMember($data, [ [ 'member_id', '=', $member_id ], [ 'site_id', '=', $this->site_id ] ]);
        return $this->response($info);
    }

    /**
     * 账户流水
     * @return false|string
     */
    public function memberAccountList()
    {
        $member_id = $this->params[ 'member_id' ] ?? 0;
        $page_index = isset($this->params[ 'page' ]) && !empty($this->params[ 'page_size' ]) ? $this->params[ 'page' ] : 1;
        $page_size = isset($this->params[ 'page_size' ]) && !empty($this->params[ 'page_size' ]) ? $this->params[ 'page_size' ] : PAGE_LIST_ROWS;
        $account_type = $this->params[ 'account_type' ] ?? '';
        if (!empty($account_type)) {
            if (!in_array($account_type, [ 'point', 'growth', 'balance', 'balance_money' ])) {
                return $this->response($this->error('', 'INVALID_PARAMETER'));
            }
        }
        $memberAcc = new MemberAccountModel();
        $condition = [
            [ 'member_id', '=', $member_id ],
            [ 'site_id', '=', $this->site_id ]
        ];
        if (!empty($account_type)) {
            $condition[] = [ 'account_type', '=', $account_type ];
        }
        $field = 'id,member_id,account_type,account_data,from_type,type_name,type_tag,remark,create_time,username,mobile,email';
        $data = $memberAcc->getMemberAccountPageList($condition, $page_index, $page_size, 'create_time desc', $field);

        if (!empty($data[ 'data' ][ 'list' ])) {
            $account_type = $memberAcc->getAccountType();
            $data[ 'data' ][ 'list' ] = array_map(function($item) use ($account_type) {
                $item[ 'account_type_name' ] = $account_type[ $item[ 'account_type' ] ];
                return $item;
            }, $data[ 'data' ][ 'list' ]);
        }
        return $this->response($data);
    }

    /**
     * 加入移除黑名单
     * @return false|string
     */
    public function joinBlacklist()
    {
        $member_id = input('member_id', 0);
        $status = input('status', 0);
        $condition = [
            [ 'member_id', '=', $member_id ],
            [ 'site_id', '=', $this->site_id ]
        ];
        $member_model = new MemberModel();
        $info = $member_model->modifyMemberStatus($status, $condition);
        return $this->response($info);
    }

    /**
     * 获取会员订单列表
     */
    public function orderList()
    {
        $search_text = $this->params[ 'search_text' ] ?? '';
        $member_id = $this->params[ 'member_id' ] ?? 0;
        $page_index = $this->params[ 'page' ] ?? 1;
        $page_size = $this->params[ 'page_size' ] ?? PAGE_LIST_ROWS;
        if (!empty($search_text)) {
            $condition[] = [ 'order_no', 'like', '%' . $search_text . '%' ];
        }
        $condition[] = [ 'member_id', '=', $member_id ];
        $condition[] = [ 'site_id', '=', $this->site_id ];
        $field = 'order_id,order_no,order_name,order_type,order_money,pay_money,balance_money,order_type_name,order_status_name,delivery_status_name,create_time';
        $order = new OrderCommon();
        $list = $order->getMemberOrderPageList($condition, $page_index, $page_size, 'order_id desc', $field);
        return $this->response($list);
    }

    /**
     * 重置密码
     */
    public function modifyMemberPassword()
    {
        $password = $this->params[ 'password' ] ?? '123456';
        $member_id = $this->params[ 'member_id' ] ?? 0;
        $member_model = new MemberModel();
        $info = $member_model->resetMemberPassword($password, [ [ 'member_id', '=', $member_id ], [ 'site_id', '=', $this->site_id ] ]);
        return $this->response($info);
    }

    /**
     * 调整余额
     */
    public function modifyBalance()
    {
        $member_id = $this->params[ 'member_id' ] ?? 0;
        $adjust_num = $this->params[ 'adjust_num' ] ?? 0;
        $remark = $this->params[ 'remark' ] ?? '商家调整';
        $this->addLog('会员余额调整id:' . $member_id . '金额' . $adjust_num);
        $member_account_model = new MemberAccountModel();
        $info = $member_account_model->addMemberAccount($this->site_id, $member_id, AccountDict::balance, $adjust_num, 'adjust', 0, $remark);
        return $this->response($info);
    }

    /**
     * 余额调整（可提现）
     */
    public function modifyBalanceMoney()
    {
        return $this->response($this->error());

        $member_id = $this->params[ 'member_id' ] ?? 0;
        $adjust_num = $this->params[ 'adjust_num' ] ?? 0;
        $remark = $this->params[ 'remark' ] ?? '商家调整';
        $this->addLog('会员余额调整id:' . $member_id . '金额' . $adjust_num);
        $member_account_model = new MemberAccountModel();
        $info = $member_account_model->addMemberAccount($this->site_id, $member_id, 'balance_money', $adjust_num, 'adjust', 0, $remark);
        return $this->response($info);
    }

    /**
     * 积分调整
     */
    public function modifyPoint()
    {
        $member_id = $this->params[ 'member_id' ] ?? 0;
        $adjust_num = $this->params[ 'adjust_num' ] ?? 0;
        $remark = $this->params[ 'remark' ] ?? '商家调整';
        $this->addLog('会员积分调整id:' . $member_id . '数量' . $adjust_num);
        $member_account_model = new MemberAccountModel();
        $info = $member_account_model->addMemberAccount($this->site_id, $member_id, 'point', $adjust_num, 'adjust', 0, $remark);
        return $this->response($info);
    }

    /**
     * 成长值调整
     */
    public function modifyGrowth()
    {
        $member_id = $this->params[ 'member_id' ] ?? 0;
        $adjust_num = $this->params[ 'adjust_num' ] ?? 0;
        $remark = $this->params[ 'remark' ] ?? '商家调整';
        $this->addLog('会员成长值调整id:' . $member_id . '数量' . $adjust_num);
        $member_account_model = new MemberAccountModel();
        $info = $member_account_model->addMemberAccount($this->site_id, $member_id, 'growth', $adjust_num, 'adjust', 0, $remark);
        return $this->response($info);
    }

    /**
     * 发放优惠券
     * @return false|string
     */
    public function sendCoupon()
    {
        $member_id = $this->params[ 'member_id' ] ?? 0;
        $coupon_data = json_decode(input('coupon_data', '[]'), true); // [{coupon_type_id: **, num: **}]
        if (empty($coupon_data)) {
            return $this->response($this->error('', '要发放的优惠券不能为空'));
        }
        $res = ( new CouponModel() )->giveCoupon($coupon_data, $this->site_id, $member_id, CouponModel::GET_TYPE_MERCHANT_GIVE);
        return $this->response($res);
    }


    /**
     * 办理会员
     * @return false|string
     */
    public function handleMember()
    {
        $member_id = $this->params[ 'member_id' ] ?? 0;
        $level_id = $this->params[ 'level_id' ] ?? 0;
        $member_code = $this->params[ 'member_code' ] ?? '';

        $member_model = new MemberModel();
        $res = $member_model->handleMember([
            'member_id' => $member_id,
            'level_id' => $level_id,
            'member_code' => $member_code,
            'site_id' => $this->site_id
        ]);
        return $this->response($res);
    }

    /**
     * 会员验证 验证码
     * @return false|string
     * @throws Exception
     */
    public function memberVerifyCode()
    {
        $member_id = $this->params[ 'member_id' ] ?? 0;
        $member_model = new MemberModel();
        $member_info = $member_model->getMemberInfo([ [ 'site_id', '=', $this->site_id ], [ 'member_id', '=', $member_id ] ], 'mobile')[ 'data' ];
        if (empty($member_info)) return $this->response($this->error('', '未获取到会员信息'));
        if (empty($member_info[ 'mobile' ])) return $this->response($this->error('', '会员未绑定手机号'));

        $code = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT);// 生成4位随机数，左侧补0
        $message_model = new Message();
        $res = $message_model->sendMessage([ 'type' => 'code', 'mobile' => $member_info[ 'mobile' ], 'site_id' => $this->site_id, 'code' => $code, 'support_type' => [ 'sms' ], 'keywords' => 'CASHIER_MEMBER_VERIFY_CODE' ]);
        if ($res[ 'code' ] >= 0) {
            //将验证码存入缓存
            $key = 'cashier_member_verify' . md5(uniqid(null, true));
            Cache::tag('bind_mobile_code')->set($key, [ 'code' => $code ], 600);
            return $this->response($this->success([ 'key' => $key ]));
        } else {
            return $this->response($res);
        }
    }

    /**
     * 验证短信验证码
     * @return false|string
     */
    public function checkSmsCode()
    {
        $key = $this->params[ 'key' ] ?? '';
        $code = $this->params[ 'code' ] ?? '';

        $verify_data = Cache::get($key);
        if (!empty($verify_data) && $verify_data[ 'code' ] == $this->params[ 'code' ]) {
            return $this->response($this->success());
        } else {
            return $this->response($this->error('', '验证码不正确'));
        }
    }

    /**
     * 验证付款码
     */
    public function checkPaymentCode()
    {
        $code = $this->params[ 'code' ] ?? '';
        $member_id = $this->params[ 'member_id' ] ?? 0;
        $res = ( new PayBalance() )->checkPaymentCode($code, $member_id);
        if ($res[ 'code' ] >= 0 && !empty($res[ 'data' ][ 'member_id' ])) {
            $res[ 'data' ][ 'member_info' ] = $this->info($res[ 'data' ][ 'member_id' ])->getData()[ 'data' ] ?? [];
        }
        return $this->response($res);
    }
}
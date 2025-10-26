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

use addon\fenxiao\model\Config as ConfigModel;
use addon\fenxiao\model\Fenxiao as FenxiaoModel;
use addon\fenxiao\model\FenxiaoAccount;
use addon\fenxiao\model\FenxiaoApply;
use addon\fenxiao\model\FenxiaoData;
use addon\fenxiao\model\FenxiaoLevel as FenxiaoLevelModel;
use addon\fenxiao\model\FenxiaoOrder as FenxiaoOrderModel;
use app\model\goods\Goods as GoodsModel;
use app\model\member\Member as MemberModel;
use app\shop\controller\BaseShop;
use think\App;

/**
 *  分销设置
 */
class Fenxiao extends BaseShop
{
    public function __construct(App $app = null)
    {
        $this->replace = [
            'FENXIAO_JS' => __ROOT__ . '/addon/fenxiao/shop/view/public/js',
            'FENXIAO_CSS' => __ROOT__ . '/addon/fenxiao/shop/view/public/css'
        ];
        parent::__construct($app);
    }

    /**
     * 分销概况
     */
    public function index()
    {
        return $this->fetch('fenxiao/index');
    }

    public function stat()
    {
        $data = [
            'account_data' => [],
            'fenxiao_account' => 0.00,
            'shop_commission' => [],
            'shop_commission_end' => [],
            'commission_money' => 0.00,
            'fenxiao_apply_num' => 0,
            'fenxiao_num' => 0,
            'fenxiao_goods_num' => 0
        ];

        $fenxiao_data_model = new FenxiaoData();
        $account_data = $fenxiao_data_model->getFenxiaoAccountData($this->site_id);
        $data['account_data'] = $account_data;

        //累计佣金
        $fenxiao_account = number_format($account_data[ 'account' ], 2, '.', '');
        $data['fenxiao_account'] = $fenxiao_account;

        //获取分销的总金额
        $order_model = new FenxiaoOrderModel();
        $commission = $order_model->getFenxiaoOrderInfo([ [ 'site_id', '=', $this->site_id ] ], 'sum(real_goods_money) as real_goods_money,sum(commission) as commission')[ 'data' ];
        if ($commission[ 'real_goods_money' ] == null) {
            $commission[ 'real_goods_money' ] = '0.00';
        }
        if ($commission[ 'commission' ] == null) {
            $commission[ 'commission' ] = '0.00';
        }
        $data['shop_commission'] = $commission;

        //获取已结算分销的总金额
        $commission_end = $order_model->getFenxiaoOrderInfo([ [ 'site_id', '=', $this->site_id ], [ 'is_settlement', '=', 1 ], [ 'is_refund', '=', 0 ] ], 'sum(real_goods_money) as real_goods_money,sum(commission) as commission')[ 'data' ];
        if ($commission_end[ 'real_goods_money' ] == null) {
            $commission_end[ 'real_goods_money' ] = '0.00';
        }
        if ($commission_end[ 'commission' ] == null) {
            $commission_end[ 'commission' ] = '0.00';
        }
        $data['shop_commission_end'] = $commission_end;

        //获取已退款的佣金
        $commission_refund = $order_model->getFenxiaoOrderInfo([ [ 'site_id', '=', $this->site_id ], [ 'is_refund', '=', 1 ] ], 'sum(real_goods_money) as real_goods_money,sum(commission) as commission')[ 'data' ];
        if ($commission_refund[ 'real_goods_money' ] == null) {
            $commission_refund[ 'real_goods_money' ] = '0.00';
        }
        if ($commission_refund[ 'commission' ] == null) {
            $commission_refund[ 'commission' ] = '0.00';
        }
        $commission_money = round($commission[ 'commission' ], 2) - round($commission_end[ 'commission' ], 2) - round($commission_refund[ 'commission' ], 2);
        $commission_money = number_format($commission_money, 2);
        $data['commission_money'] = $commission_money;

        $fenxiao_apply_num = $fenxiao_data_model->getFenxiaoApplyCount($this->site_id);
        $data['fenxiao_apply_num'] = $fenxiao_apply_num;

        //分销商人数
        $fenxiao_num = $fenxiao_data_model->getFenxiaoCount($this->site_id);
        $data['fenxiao_num'] = $fenxiao_num;

        $goods_model = new GoodsModel();
        $fenxiao_goods_num = $goods_model->getGoodsInfo([ [ 'site_id', '=', $this->site_id ], [ 'is_fenxiao', '=', 1 ], [ 'is_delete', '=', 0 ] ], 'count(goods_id) as fenxiao_goods_num')[ 'data' ];
        $data['fenxiao_goods_num'] = $fenxiao_goods_num[ 'fenxiao_goods_num' ];

        return $data;
    }


    /**
     * 分销商列表
     */
    public function lists()
    {
        $model = new FenxiaoModel();
        if (request()->isJson()) {

            $condition[] = [ 'f.site_id', '=', $this->site_id ];
            $fenxiao_name = input('fenxiao_name', '');
            $nickname = input('nickname', '');
            $mobile = input('mobile', '');
            $parent_name = input('parent_name', '');
            $level_id = input('level_id', '');
            $start_time = input('start_time', '');
            $end_time = input('end_time', '');
            $status = input('status', '');

            if ($fenxiao_name) {
                $condition[] = [ 'f.fenxiao_name', 'like', '%' . $fenxiao_name . '%' ];
            }
            if ($nickname) {
                $condition[] = [ 'm.nickname', 'like', '%' . $nickname . '%' ];
            }
            if ($mobile) {
                $condition[] = [ 'm.mobile', 'like', '%' . $mobile . '%' ];
            }

            if ($parent_name) {
                $condition[] = [ 'pf.fenxiao_name', 'like', '%' . $parent_name . '%' ];
            }

            if ($level_id) {
                $condition[] = [ 'f.level_id', '=', $level_id ];
            }
            if ($start_time && $end_time) {
                $condition[] = [ 'f.create_time', 'between', [ date_to_time($start_time), date_to_time($end_time) ] ];
            } elseif (!$start_time && $end_time) {
                $condition[] = [ 'f.create_time', '<=', date_to_time($end_time) ];

            } elseif ($start_time && !$end_time) {
                $condition[] = [ 'f.create_time', '>=', date_to_time($start_time) ];
            }

            if (!empty($status)) {
                $condition[] = [ 'f.status', '=', $status ];
            }
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $list = $model->getFenxiaoPageList($condition, $page, $page_size, 'f.create_time desc', $this->site_id);
            return $list;

        } else {
            $level_model = new FenxiaoLevelModel();
            $level_list = $level_model->getLevelList([ [ 'status', '=', 1 ], [ 'site_id', '=', $this->site_id ] ], 'level_id,level_name')[ 'data' ];
            $this->assign('level_list', $level_list);

            $config_model = new ConfigModel();
            $basics = $config_model->getFenxiaoBasicsConfig($this->site_id)[ 'data' ][ 'value' ];
            $this->assign('basics_info', $basics);


            return $this->fetch('fenxiao/lists');
        }
    }

    /**
     * 添加分销商
     */
    public function add()
    {
        if (request()->isJson()) {

            $fenxiao_data = [
                'site_id' => $this->site_id,
                'fenxiao_name' => input('fenxiao_name', ''),//分销商名称
                'level_id' => input('level_id', 0),//分销商等级
                'parent' => input('fenxiao_id', 0),//上级分销商ID
                'member_id' => input('member_id', 0),//关联会员ID
            ];
            $apply_model = new FenxiaoApply();
            return $apply_model->addFenxiao($fenxiao_data);

        } else {

            //获取分销商等级
            $level_model = new FenxiaoLevelModel();
            $level_list = $level_model->getLevelList([ [ 'status', '=', 1 ], [ 'site_id', '=', $this->site_id ] ], 'level_id,level_name')[ 'data' ];
            $this->assign('level_list', $level_list);

            //获取分销商列表
            $fenxiao_model = new FenxiaoModel();
            $condition[] = [ 'status', '=', '1' ];
            $fenxiao_list = $fenxiao_model->getFenxiaoList($condition, 'fenxiao_id,fenxiao_name')[ 'data' ];
            $this->assign('fenxiao_list', $fenxiao_list);

            //获取会员列表
            $member_model = new MemberModel();
            $where[] = [ 'is_fenxiao', '=', '0' ];
            $member_list = $member_model->getMemberList($where, 'member_id,nickname')[ 'data' ];
            $this->assign('member_list', $member_list);

            return $this->fetch('fenxiao/add');
        }

    }

    /**
     * 获取分销商列表
     */
    public function getFenxiaoList()
    {
        $page_index = input('page', 1);
        $page_size = input('page_size', PAGE_LIST_ROWS);
        $fenxiao_search = input('fenxiao_search', '');
        $condition = [];
        $condition[] = [ 'mobile|fenxiao_name', 'like', '%' . $fenxiao_search . '%' ];
        $condition[] = [ 'status', '=', 1 ];
        $fenxiao_model = new FenxiaoModel();
        $list = $fenxiao_model->getFenxiaoPageLists($condition, $page_index, $page_size, '', 'fenxiao_id,fenxiao_name,account');
        return $list;
    }

    /**
     * 获取会员列表
     */
    public function getMemberList()
    {
        $page_index = input('page', 1);
        $page_size = input('page_size', PAGE_LIST_ROWS);
        $member_search = input('member_search', '');
        $condition = [];
        $condition[] = [ 'mobile|email|username|nickname', 'like', '%' . $member_search . '%' ];
        $condition[] = [ 'site_id', '=', $this->site_id ];
        $condition[] = [ 'is_fenxiao', '=', '0' ];
        $member_model = new MemberModel();
        $list = $member_model->getMemberPageList($condition, $page_index, $page_size, '', 'member_id,headimg,nickname,point,balance');
        return $list;
    }

    /**
     * 详情
     */
    public function detail()
    {
        $fenxiao_id = input('fenxiao_id', '');

        $model = new FenxiaoModel();
        $fenxiao_leve_model = new FenxiaoLevelModel();

        $condition[] = [ 'f.fenxiao_id', '=', $fenxiao_id ];
        $info = $model->getFenxiaoDetailInfo($condition);
        if (empty($info[ 'data' ])) $this->error('未获取到分销商数据', href_url('fenxiao://shop/fenxiao/lists'));
        //团队人员数据
        $info['data']['team_num'] = $model->getFenxiaoTeamNum($fenxiao_id, $this->site_id)['data'];

        $fenxiao_level = $fenxiao_leve_model->getLevelInfo([ [ 'level_id', '=', $info[ 'data' ][ 'level_id' ] ] ]);

        $this->assign('status', $model->fenxiao_status_zh);
        $this->assign('level', $fenxiao_level[ 'data' ]);
        $this->assign('info', $info[ 'data' ]);

        $this->assign('fenxiao_id', $fenxiao_id);

        $config_model = new ConfigModel();
        $basics_config = $config_model->getFenxiaoBasicsConfig($this->site_id);
        $this->assign('fenxiao_level_num', $basics_config[ 'data' ][ 'value' ][ 'level' ]);

        return $this->fetch('fenxiao/fenxiao_detail');
    }

    /**
     * 分销账户信息
     */
    public function account()
    {
        $model = new FenxiaoModel();
        $fenxiao_id = input('fenxiao_id', '');

        $condition[] = [ 'f.fenxiao_id', '=', $fenxiao_id ];
        $info = $model->getFenxiaoDetailInfo($condition);
        if (empty($info[ 'data' ])) $this->error('未获取到分销商数据', href_url('fenxiao://shop/fenxiao/lists'));
        $account = $info[ 'data' ][ 'account' ] - $info[ 'data' ][ 'account_withdraw_apply' ];
        $info[ 'data' ][ 'account' ] = number_format($account, 2, '.', '');
        $this->assign('fenxiao_info', $info[ 'data' ]);

        if (request()->isJson()) {

            $account_model = new FenxiaoAccount();
            $page = input('page', 1);
            $status = input('status', '');

            $fenxiao_id = input('fenxiao_id', '');
            $list_condition[] = [ 'fenxiao_id', '=', $fenxiao_id ];
            if ($status) {
                if ($status == 1) {
                    $list_condition[] = [ 'money', '>', 0 ];
                } else {
                    $list_condition[] = [ 'money', '<', 0 ];
                }
            }

            $start_time = input('start_time', '');
            $end_time = input('end_time', '');
            if ($start_time && $end_time) {
                $list_condition[] = [ 'create_time', 'between', [ $start_time, $end_time ] ];
            } elseif (!$start_time && $end_time) {
                $list_condition[] = [ 'create_time', '<=', $end_time ];

            } elseif ($start_time && !$end_time) {
                $list_condition[] = [ 'create_time', '>=', $start_time ];
            }

            $page_size = input('page_size', PAGE_LIST_ROWS);
            $list = $account_model->getFenxiaoAccountPageList($list_condition, $page, $page_size);
            return $list;
        }
    }

    /**
     * 分销商团队
     */
    public function team()
    {
        $fenxiao_id = input('fenxiao_id', 0);
        $fenxiao_model = new FenxiaoModel();
        if (request()->isJson()) {
            $level = input('level', 1);
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $list = $fenxiao_model->getFenxiaoTeam($level, $fenxiao_id, $page, $page_size);
            return $list;
        }
    }

    /**
     * 订单管理
     */
    public function order()
    {
        $model = new FenxiaoOrderModel();
        if (request()->isJson()) {

            $page_index = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $fenxiao_id = input('fenxiao_id', '');
            $status = input('status', 0);

            $condition[] = [ 'one_fenxiao_id|two_fenxiao_id|three_fenxiao_id', '=', $fenxiao_id ];

            $search_text_type = input('search_text_type', 'goods_name');//订单编号/商品名称
            $search_text = input('search_text', '');
            if (!empty($search_text)) {
                $condition[] = [ 'fo.' . $search_text_type, 'like', '%' . $search_text . '%' ];
            }
            if (in_array($status, [ 1, 2 ])) {
                $condition[] = [ 'fo.is_settlement', '=', $status - 1 ];
            }

            //下单时间
            $start_time = input('start_time', '');
            $end_time = input('end_time', '');
            if (!empty($start_time) && empty($end_time)) {
                $condition[] = [ 'fo.create_time', '>=', date_to_time($start_time) ];
            } elseif (empty($start_time) && !empty($end_time)) {
                $condition[] = [ 'fo.create_time', '<=', date_to_time($end_time) ];
            } elseif (!empty($start_time) && !empty(date_to_time($end_time))) {
                $condition[] = [ 'fo.create_time', 'between', [ date_to_time($start_time), date_to_time($end_time) ] ];
            }

            $list = $model->getFenxiaoOrderPage($condition, $page_index, $page_size);
            return $list;

        }
    }

    /**
     * 订单详情
     */
    public function orderDetail()
    {
        $fenxiao_order_model = new FenxiaoOrderModel();
        $fenxiao_order_id = input('fenxiao_order_id', '');
        $order_info = $fenxiao_order_model->getFenxiaoOrderDetail([ [ 'fenxiao_order_id', '=', $fenxiao_order_id ] ]);
        $this->assign('order_info', $order_info[ 'data' ]);
        return $this->fetch('fenxiao/order_detail');
    }

    /**
     * 冻结
     */
    public function frozen()
    {
        $fenxiao_id = input('fenxiao_id', '');
        $model = new FenxiaoModel();
        return $model->frozen($fenxiao_id);
    }

    /**
     * 恢复正常
     */
    public function unfrozen()
    {
        $fenxiao_id = input('fenxiao_id', '');
        $model = new FenxiaoModel();
        return $model->unfrozen($fenxiao_id);
    }

    /**
     * 分销商申请列表
     */
    public function apply()
    {
        $model = new FenxiaoApply();
        if (request()->isJson()) {

            $condition[] = [ 'fa.site_id', '=', $this->site_id ];
            $condition[] = [ 'fa.status', '=', 1 ];
            $condition[] = [ 'm.is_delete', '=', 0 ];

            $fenxiao_name = input('fenxiao_name', '');
            if ($fenxiao_name) {
                $condition[] = [ 'fenxiao_name', 'like', '%' . $fenxiao_name . '%' ];
            }
            $nickname = input('nickname', '');
            if ($nickname) {
                $condition[] = [ 'm.nickname', 'like', '%' . $nickname . '%' ];
            }
            $mobile = input('mobile', '');
            if ($mobile) {
                $condition[] = [ 'm.mobile', 'like', '%' . $mobile . '%' ];
            }
            $level_id = input('level_id', '');
            if ($level_id) {
                $condition[] = [ 'fa.level_id', '=', $level_id ];
            }
            $create_start_time = input('create_start_time', '');
            $create_end_time = input('create_end_time', '');
            if ($create_start_time && $create_end_time) {
                $condition[] = [ 'fa.create_time', 'between', [ strtotime($create_start_time), strtotime($create_end_time) ] ];
            } elseif (!$create_start_time && $create_end_time) {
                $condition[] = [ 'fa.create_time', '<=', strtotime($create_end_time) ];

            } elseif ($create_start_time && !$create_end_time) {
                $condition[] = [ 'fa.create_time', '>=', strtotime($create_start_time) ];
            }

            $rg_start_time = input('rg_start_time', '');
            $rg_end_time = input('rg_end_time', '');
            if ($rg_start_time && $rg_end_time) {
                $condition[] = [ 'fa.reg_time', 'between', [ strtotime($rg_start_time), strtotime($rg_end_time) ] ];
            } elseif (!$rg_start_time && $rg_end_time) {
                $condition[] = [ 'fa.reg_time', '<=', strtotime($rg_end_time) ];

            } elseif ($rg_start_time && !$rg_end_time) {
                $condition[] = [ 'fa.reg_time', '>=', strtotime($rg_start_time) ];
            }

            $join = [
                [ 'member m', 'fa.member_id = m.member_id', 'inner' ]
            ];
            $field = 'fa.apply_id,fa.fenxiao_name,fa.parent,fa.member_id,fa.level_id,fa.level_name,fa.order_complete_money,fa.order_complete_num,fa.reg_time,fa.create_time,fa.status,m.mobile,m.nickname,m.headimg';

            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $list = $model->getFenxiaoApplyPageList($condition, $page, $page_size, 'fa.create_time desc', $field, 'fa', $join);
            return $list;
        } else {

            $level_model = new FenxiaoLevelModel();
            $level_list = $level_model->getLevelList([ [ 'status', '=', 1 ] ], 'level_id,level_name');
            $this->assign('level_list', $level_list[ 'data' ]);

            return $this->fetch('fenxiao/apply');
        }
    }

    /**
     * 分销商申请通过
     */
    public function applyPass()
    {
        $apply_id = input('apply_id');

        $model = new FenxiaoApply();
        $res = $model->pass($apply_id, $this->site_id);
        return $res;
    }

    /**
     * 分销商申请通过
     */
    public function applyRefuse()
    {
        $apply_id = input('apply_id');
        $model = new FenxiaoApply();
        $res = $model->refuse($apply_id);
        return $res;
    }

    /**
     * 变更上下级
     */
    public function change()
    {
        $member_id = input('member_id');
        $model = new FenxiaoModel();
        $member_model = new MemberModel();

        //用户信息
        $member_info = $member_model->getMemberInfo([ [ 'member_id', '=', $member_id ] ], 'fenxiao_id,is_fenxiao')[ 'data' ];
        //获取分销信息
        $fenxiao_info = $model->getFenxiaoInfo([ [ 'fenxiao_id', '=', $member_info[ 'fenxiao_id' ] ] ], 'parent');
        //获取上级分销商信息
        $parent_info = [];
        if (!empty($fenxiao_info[ 'data' ])) {
            $parent_info = $model->getFenxiaoInfo([ [ 'fenxiao_id', '=', $fenxiao_info[ 'data' ][ 'parent' ] ] ], 'fenxiao_id,fenxiao_name')['data'];
        }
        if (request()->isJson()) {
            if ($member_info[ 'is_fenxiao' ] == 1) {
                $condition[] = [ 'f.fenxiao_id', '<>', $member_info[ 'fenxiao_id' ] ];
                $condition[] = [ 'f.parent', '<>', $member_info[ 'fenxiao_id' ] ];
            }

            $condition[] = [ 'f.site_id', '=', $this->site_id ];
            $status = input('status', 1);
            if ($status) {
                $condition[] = [ 'f.status', '=', $status ];
            }

            $fenxiao_name = input('fenxiao_name', '');
            if ($fenxiao_name) {
                $condition[] = [ 'f.fenxiao_name', 'like', '%' . $fenxiao_name . '%' ];
            }

            $parent_name = input('parent_name', '');
            if ($parent_name) {
                $condition[] = [ 'pf.fenxiao_name', 'like', '%' . $parent_name . '%' ];
            }

            $level_id = input('level_id', '');
            if ($level_id) {
                $condition[] = [ 'f.level_id', '=', $level_id ];
            }
            $start_time = input('start_time', '');
            $end_time = input('end_time', '');
            if ($start_time && $end_time) {
                $condition[] = [ 'f.create_time', 'between', [ date_to_time($start_time), date_to_time($end_time) ] ];
            } elseif (!$start_time && $end_time) {
                $condition[] = [ 'f.create_time', '<=', date_to_time($end_time) ];

            } elseif ($start_time && !$end_time) {
                $condition[] = [ 'f.create_time', '>=', date_to_time($start_time) ];
            }
            if(!empty($parent_info)){
                $order = \think\facade\Db::raw("IF(f.fenxiao_id = {$parent_info['fenxiao_id']}, 1, 2) asc,f.create_time desc");
            }else{
                $order = 'f.create_time desc';
            }

            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $list = $model->getFenxiaoPageList($condition, $page, $page_size, $order);
            return $list;

        } else {
            $level_model = new FenxiaoLevelModel();
            $level_list = $level_model->getLevelList([ [ 'status', '=', 1 ], [ 'site_id', '=', $this->site_id ] ], 'level_id,level_name');
            $this->assign('level_list', $level_list[ 'data' ]);

            $config_model = new ConfigModel();
            $basics = $config_model->getFenxiaoBasicsConfig($this->site_id);
            $this->assign('basics_info', $basics[ 'data' ][ 'value' ]);

            $this->assign('member_id', $member_id);
            $this->assign('parent_info', $parent_info);

            $change_end_func = input('change_end_func', 'changeEnd');
            $this->assign('change_end_func', $change_end_func);

            return $this->fetch('fenxiao/change');
        }
    }

    /**
     * 确认变更
     */
    public function confirmChange()
    {
        if (request()->isJson()) {

            $member_id = input('member_id', '');
            $parent = input('parent', '');
            $type = input('type', '');
            $model = new FenxiaoModel();
            if ($type == 1) {
                $res = $model->changeParentFenxiao($member_id, $parent);
            } else {
                $res = $model->cancelParentFenxiao($member_id);
            }

            return $res;
        }
    }

    /**
     * 会员详情
     */
    public function memberInfo()
    {
        if (request()->isJson()) {

            $member_id = input('member_id', '');
            $condition[] = [ 'site_id', '=', $this->site_id ];
            $condition[] = [ 'member_id', '=', $member_id ];
            $member_model = new MemberModel();
            $member_info_result = $member_model->getMemberInfo($condition);
            $member_info = $member_info_result['data'];
            if (empty($member_info)) return $member_model->error([], '账号不存在！');
            return $member_info_result;
        }
    }

    /**
     * 修改分销商等级
     */
    public function changeLevel()
    {
        $member_id = input('member_id', '');
        $fenxiao_id = input('fenxiao_id', '');
        if (request()->isJson()) {
            $level_model = new FenxiaoLevelModel();
            $fenxiao_model = new FenxiaoModel();
            $fenxiao_info = $fenxiao_model->getFenxiaoInfo([ [ 'fenxiao_id', '=', $fenxiao_id ] ], 'member_id,level_id,level_name')[ 'data' ];
            $condition[] = [ 'site_id', '=', $this->site_id ];
            if (!empty($fenxiao_info)) {
                $condition[] = [ 'level_id', '<>', $fenxiao_info[ 'level_id' ] ];
            }

            $lists = $level_model->getLevelPageList($condition, '1', PAGE_LIST_ROWS, 'level_num asc');
            return $lists;
        } else {
            $config_model = new ConfigModel();
            $basics = $config_model->getFenxiaoBasicsConfig($this->site_id);
            $this->assign('basics_info', $basics[ 'data' ][ 'value' ]);

            $this->assign('member_id', $member_id);

            $this->assign('fenxiao_id', $fenxiao_id);

            $change_end_func = input('change_end_func', 'changeEnd');
            $this->assign('change_end_func', $change_end_func);

            return $this->fetch('fenxiao/change_level');
        }

    }

    /**
     * 确认变更
     */
    public function confirmChangeLevel()
    {
        if (request()->isJson()) {

            $member_id = input('member_id', '');
            $level_id = input('level_id', '');

            $level_model = new FenxiaoLevelModel();
            $level_data = $level_model->getLevelInfo([ [ 'level_id', '=', $level_id ] ], 'level_id,level_name')[ 'data' ];
            $fenxiao_model = new FenxiaoModel();
            $data = [
                'level_id' => $level_data[ 'level_id' ],
                'level_name' => $level_data[ 'level_name' ]
            ];
            $res = $fenxiao_model->changeFenxiaoLevel($data, [ [ 'member_id', '=', $member_id ] ]);
            return $res;
        }
    }
}
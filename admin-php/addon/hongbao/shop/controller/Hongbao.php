<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 */

namespace addon\hongbao\shop\controller;

use app\shop\controller\BaseShop;
use addon\hongbao\model\Hongbao as HongbaoModel;
use addon\hongbao\model\HongbaoGroup;

/**
 * 裂变红包
 * Class hongbao
 * @package addon\hongbao\shop\controller
 */
class Hongbao extends BaseShop
{
    /**
     * 活动列表
     * @return array|mixed
     */
    public function lists()
    {
        $hongbao_model = new HongbaoModel();

        if (request()->isJson()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $name = input('name', '');
            $status = input('status', '');

            $condition = [];
            if ($status !== "") {
                $condition[] = [ 'status', '=', $status ];
            }

            $condition[] = [ 'site_id', '=', $this->site_id ];
            $condition[] = [ 'name', 'like', '%' . $name . '%' ];

            $start_time = input('start_time', '');
            $end_time = input('end_time', '');

            if ($start_time && !$end_time) {
                $condition[] = [ 'end_time', '>=', date_to_time($start_time) ];
            } elseif (!$start_time && $end_time) {
                $condition[] = [ 'start_time', '<=', date_to_time($end_time) ];
            } elseif ($start_time && $end_time) {
                $start_timestamp = date_to_time($start_time);
                $end_timestamp = date_to_time($end_time);
                $sql = "start_time between {$start_timestamp} and {$end_timestamp}";
                $sql .= " or end_time between {$start_timestamp} and {$end_timestamp}";
                $sql .= " or (start_time <= {$start_timestamp} and end_time >= {$end_timestamp})";
                $condition[] = [ '', 'exp', \think\facade\Db::raw($sql) ];
            }

            $data = $hongbao_model->getHongbaoPageList($condition, $page, $page_size);
            return $data;
        } else {
            $hongbao_status = $hongbao_model->getHongbaoStatus();
            $this->assign('hongbao_status', $hongbao_status);
            return $this->fetch("hongbao/lists");
        }
    }

    /**
     * 添加活动
     * @return mixed
     */
    public function add()
    {
        if (request()->isJson()) {
            $data = [
                'site_id' => $this->site_id,
                'name' => input('name', ''),//活动名称
                'money' => input('money', 0),//瓜分金额
                'start_time' => strtotime(input('start_time', '')), // 活动开始时间
                'end_time' => strtotime(input('end_time', '')), // 活动结束时间
                'divide_num' => input('divide_num', 0),//瓜分人数
                'image' => input('image', 0),//优惠券图片
                'inventory' => input('inventory', ''),//库存
                'count' => input('inventory', ''),//发放数量
                'divide_time' => input('divide_time', 1),//瓜分有效期
                'is_simulation' => input('is_simulation', 0),//是否模拟好友
                'is_new' => input('is_new', 0),//仅新人限制
                'balance_set' => input('balance_set', 1),//余额设置
                'divide_type' => input('divide_type', ''),//瓜分方式
                'create_time' => time(),//创建时间
                'remark' => input('remark', ''),//活动规则
            ];
            $hongbao_model = new HongbaoModel();
            $res = $hongbao_model->addHongbao($data);
            return $res;
        } else {
            return $this->fetch("hongbao/add");
        }
    }

    /**
     * 编辑活动
     * @return mixed
     */
    public function edit()
    {
        $hongbao_model = new HongbaoModel();
        if (request()->isJson()) {
            $data = [
                'hongbao_id' => input('hongbao_id', ''),
                'site_id' => $this->site_id,
                'name' => input('name', ''),//活动名称
                'money' => input('money', 0),//瓜分金额
                'divide_time' => input('divide_time', 1),//瓜分有效期
                'start_time' => strtotime(input('start_time', '')), // 活动开始时间
                'end_time' => strtotime(input('end_time', '')), // 活动结束时间
                'divide_num' => input('divide_num', 0),//瓜分人数
                'image' => input('image', 0),//优惠券图片
                'inventory' => input('inventory', ''),//发放数量
                'is_simulation' => input('is_simulation', 0),//是否模拟好友
                'is_new' => input('is_new', 0),//仅新人限制
                'balance_set' => input('balance_set', 1),//余额设置
                'divide_type' => input('divide_type', ''),//瓜分方式
                'remark' => input('remark', ''),//活动规则
            ];

            $res = $hongbao_model->editHongbao($data);
            return $res;
        } else {
            $hongbao_id = input('hongbao_id', 0);
            $this->assign('hongbao_id', $hongbao_id);
            $condition = [
                [ 'hongbao_id', '=', $hongbao_id ],
                [ 'site_id', '=', $this->site_id ],
            ];
            $hongbao_info = $hongbao_model->getHongbaoInfo($condition);
            if (empty($hongbao_info[ 'data' ])) $this->error('未获取到裂变红包数据', href_url('hongbao://shop/hongbao/lists'));
            $this->assign('hongbao_info', $hongbao_info[ 'data' ]);
            return $this->fetch("hongbao/edit");
        }
    }

    /**
     * 活动详情
     */
    public function detail()
    {
        $hongbao_id = input('hongbao_id', 0);
        $hongbao_model = new HongbaoModel();
        $this->assign('hongbao_id', $hongbao_id);
        $condition = [
            [ 'hongbao_id', '=', $hongbao_id ],
            [ 'site_id', '=', $this->site_id ],
        ];
        $info = $hongbao_model->getHongbaoInfo($condition)[ 'data' ] ?? [];
        if (empty($info)) $this->error('未获取到裂变红包数据', href_url('hongbao://shop/hongbao/lists'));
        $info[ 'status_name' ] = $hongbao_model->getHongbaoStatus()[ $info[ 'status' ] ] ?? '';
        $this->assign('info', $info);
        return $this->fetch("hongbao/detail");
    }

    /**
     * 活动推广
     */
    public function spreadHongbao()
    {
        $hongbao_id = input('hongbao_id', '');
        $app_type = input('app_type', 'all');
        $hongbao_model = new HongbaoModel();
        $res = $hongbao_model->hongbaoQrcode('/pages_tool/hongbao/index', [ "hid" => $hongbao_id ], 'hongbao', $app_type, $this->site_id);
        return $res;
    }

    /**
     * 关闭活动
     */
    public function close()
    {
        if (request()->isJson()) {
            $hongbao_id = input('hongbao_id', 0);
            $data = [
                'hongbao_id' => $hongbao_id,
                'site_id' => $this->site_id,
            ];
            $hongbao_model = new HongbaoModel();
            return $hongbao_model->closeHongbao($data);
        }
    }

    /**
     * 删除活动
     */
    public function delete()
    {
        if (request()->isJson()) {
            $hongbao_id = input('hongbao_id', 0);
            $data = [
                'hongbao_id' => $hongbao_id,
                'site_id' => $this->site_id,
            ];
            $hongbao_model = new HongbaoModel();
            return $hongbao_model->deleteHongbao($data);
        }
    }

    /**
     * 运营
     */
    public function operate()
    {
        $hongbao_id = input('hongbao_id', '0');
        if (request()->isJson()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);

            $condition = [];
            $condition[] = [ 'a.site_id', '=', $this->site_id ];

            if ($hongbao_id) {
                $condition[] = [ 'a.hongbao_id', '=', $hongbao_id ];
            }

            $alias = 'a';
            $join = [
                [ 'promotion_hongbao p', 'a.hongbao_id = p.hongbao_id', 'left' ],
                [ 'member m', 'a.header_id = m.member_id', 'left' ],
            ];
            $field = 'a.*,p.name,m.username,m.nickname,m.headimg';

            $hongbao_group_model = new HongbaoGroup();
            $data = $hongbao_group_model->getHongbaoGroupPageList($condition, $page, $page_size, 'a.group_id desc', $field, $alias, $join);
            return $data;
        } else {
            $this->assign('hongbao_id', $hongbao_id);
            return $this->fetch("hongbao/operate");
        }
    }

    /**
     * 邀请人
     */
    public function groupMember()
    {
        $group_id = input('group_id', '0');
        if (request()->isJson()) {
            $hongbao_group_model = new HongbaoGroup();
            $condition = [];
            $condition[] = [ 'a.group_id', '=', $group_id ];
            $condition[] = [ 'a.site_id', '=', $this->site_id ];
            $field = 'a.*,p.divide_num,p.money';
            $join = [
                [ 'promotion_hongbao p', 'a.hongbao_id = p.hongbao_id', 'left' ],
            ];
            $data = $hongbao_group_model->getHongbaoGroupInfo($condition, $field, $alias = 'a', $join);
            $member_arr[ 'code' ] = 0;
            $member_arr[ 'data' ][ 'list' ] = $data[ 'data' ][ 'member_list' ] ?? [];
            if ($member_arr[ 'data' ][ 'list' ]) {
                foreach ($member_arr[ 'data' ][ 'list' ] as $k => $v) {
                    $member_arr[ 'data' ][ 'list' ][ $k ][ 'divide_num' ] = $data[ 'data' ][ 'divide_num' ];
                    $member_arr[ 'data' ][ 'list' ][ $k ][ 'money' ] = $data[ 'data' ][ 'money' ];
                    $member_arr[ 'data' ][ 'list' ][ $k ][ 'balance_money' ] = $v[ 'money' ] ?? '';
                }
            }
            return $member_arr;
        } else {
            $this->assign('group_id', $group_id);
            return $this->fetch("hongbao/group_member");
        }
    }
}
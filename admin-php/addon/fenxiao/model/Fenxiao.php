<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\fenxiao\model;

use addon\fenxiao\model\Config as ConfigModel;
use app\model\BaseModel;
use app\model\member\Member;
use app\model\order\OrderCommon;
use app\model\system\Stat;

/**
 * 分销
 */
class Fenxiao extends BaseModel
{

    public $fenxiao_status_zh = [
        1 => '正常',
        -1 => '冻结',
    ];

    /**
     * 添加分销商
     * @param $data
     * @return mixed
     */
    public function addFenxiao($data)
    {
        $fenxiao_info = model('fenxiao')->getInfo(
            [ [ 'member_id', '=', $data[ 'member_id' ] ], [ 'is_delete', '=', 0 ] ],
            'fenxiao_id'
        );
        if (!empty($fenxiao_info)) return $this->error('', '已经是分销商了');

        $data[ 'fenxiao_no' ] = date('YmdHi') . rand(1000, 9999);
        $data[ 'create_time' ] = time();
        $data[ 'audit_time' ] = time();

        model('fenxiao')->startTrans();
        try {

            if (!empty($data[ 'parent' ])) {
                //添加上级分销商一级下线人数
                model('fenxiao')->setInc([ [ 'fenxiao_id', '=', $data[ 'parent' ] ], [ 'is_delete', '=', 0 ] ], 'one_child_fenxiao_num');
                //获取上上级分销商id
                $grand_parent_id = model('fenxiao')->getInfo([ [ 'fenxiao_id', '=', $data[ 'parent' ] ], [ 'is_delete', '=', 0 ] ], 'parent');

                if (!empty($grand_parent_id) && $grand_parent_id[ 'parent' ] != 0) {
                    //添加上上级分销商二级下线人数
                    model('fenxiao')->setInc([ [ 'fenxiao_id', '=', $grand_parent_id[ 'parent' ] ] ], 'two_child_fenxiao_num');

                    $data[ 'grand_parent' ] = $grand_parent_id[ 'parent' ];
                }

                // 分销商检测升级
                event('FenxiaoUpgrade', $data[ 'parent' ]);
            }

            $res = model('fenxiao')->add($data);
            //修改会员信息
            model('member')->update([ 'fenxiao_id' => $res, 'is_fenxiao' => 1 ], [ [ 'member_id', '=', $data[ 'member_id' ] ] ]);

            $stat_model = new Stat();
            $stat_model->switchStat([ 'type' => 'add_fenxiao_member', 'data' => [ 'site_id' => $data[ 'site_id' ] ] ]);

            model('fenxiao')->commit();
            return $this->success($res);
        } catch (\Exception $e) {
            model('fenxiao')->rollback();
            return $this->error('', $e->getMessage());
        }

    }

    /**
     * 冻结
     * @param $fenxiao_id
     * @return array
     */
    public function frozen($fenxiao_id)
    {
        $data = [
            'status' => -1,
            'lock_time' => time()
        ];

        $res = model('fenxiao')->update($data, [ [ 'fenxiao_id', '=', $fenxiao_id ] ]);
        return $this->success($res);
    }

    /**
     * 解冻
     * @param $fenxiao_id
     * @return array
     */
    public function unfrozen($fenxiao_id)
    {
        $data = [
            'status' => 1,
            'lock_time' => time()
        ];

        $res = model('fenxiao')->update($data, [ [ 'fenxiao_id', '=', $fenxiao_id ] ]);
        return $this->success($res);
    }

    /**
     * 获取分销商详细信息
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getFenxiaoInfo($condition = [], $field = '*')
    {
        $condition[] = [ 'is_delete', '=', 0 ];
        $res = model('fenxiao')->getInfo($condition, $field);
        return $this->success($res);
    }

    /**
     * 获取分销商详细信息
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getFenxiaoDetailInfo($condition = [])
    {
        $condition[] = [ 'f.is_delete', '=', 0 ];

        $field = 'f.*,pf.fenxiao_name as parent_name,nm.username,nm.nickname,nm.headimg,nm.order_num,nm.order_money,fl.level_num';
        $alias = 'f';
        $join = [
            [
                'fenxiao pf',
                'pf.fenxiao_id = f.parent',
                'left'
            ],
            [
                'member nm',
                'nm.member_id = f.member_id',
                'left'
            ],
            [
                'fenxiao_level fl',
                'f.level_id = fl.level_id',
                'left'
            ]
        ];
        $res = model('fenxiao')->getInfo($condition, $field, $alias, $join);
        return $this->success($res);
    }


    /**
     * 获取分销列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param null $limit
     * @return array
     */
    public function getFenxiaoList($condition = [], $field = '*', $order = '', $limit = null)
    {
        $condition[] = [ 'is_delete', '=', 0 ];
        $list = model('fenxiao')->getList($condition, $field, $order, '', '', '', $limit);
        return $this->success($list);
    }

    /**
     * 获取分销分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @return array
     */
    public function getFenxiaoPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $site_id = 0)
    {
        $condition[] = [ 'f.is_delete', '=', 0 ];
        $field = 'f.*,pf.fenxiao_name as parent_name,m.username,m.nickname,m.mobile as member_mobile,m.headimg';
        $alias = 'f';
        $join = [
            [
                'fenxiao pf',
                'pf.fenxiao_id = f.parent',
                'left'
            ],
            [
                'member m',
                'm.member_id = f.member_id',
                'left'
            ]
        ];
        $list = model('fenxiao')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        $id_array = [];
        foreach ($list[ 'list' ] as $k => $v) {
            $id_array[] = $v['fenxiao_id'];
        }

        // 查询分销基础配置
        $config_model = new Config();
        $fenxiao_basic_config = $config_model->getFenxiaoBasicsConfig($site_id)[ 'data' ][ 'value' ];
        $level = $fenxiao_basic_config[ 'level' ];

        switch ( $level ) {
            case 1:
                $member_array = [];
                $member_count_array = model('member')->getList([['fenxiao_id', 'in', $id_array]], 'count(*) as count, fenxiao_id', '', '', '', 'fenxiao_id');
                if (!empty($member_count_array)){
                    $member_key = array_column($member_count_array, 'fenxiao_id');
                    $member_array = array_combine($member_key, $member_count_array);
                }

                foreach ($list[ 'list' ] as $k => $v) {
                    $count = 0;
                    if(isset($member_array[ $v['fenxiao_id'] ])) $count = $member_array[ $v['fenxiao_id'] ][ 'count' ];
                    $list[ 'list' ][ $k ][ 'team_num' ] = $count;
                }
                break;

            case 2:

                $team_array = [];
                $team_list_array = model('fenxiao')->getList([[ 'parent', 'in', $id_array]], 'fenxiao_id, parent');

                if(!empty($team_list_array)){
                    $team_id_array = [];
                    foreach ($team_list_array as $team_k => $team_v) {
                        $team_id_array[] = $team_v['fenxiao_id'];
                        $team_array[$team_v['parent']][] = $team_v;
                    }
                    $id_array = array_merge($id_array, $team_id_array);
                }

                $member_array = [];
                $member_count_array = model('member')->getList([['fenxiao_id', 'in', $id_array]], 'count(*) as count, fenxiao_id', '', '', '', 'fenxiao_id');
                if (!empty($member_count_array)){
                    $member_key = array_column($member_count_array, 'fenxiao_id');
                    $member_array = array_combine($member_key, $member_count_array);
                }

                foreach ($list[ 'list' ] as $k => $v) {
                    $count = 0;
                    $num = 0;
                    if(isset($member_array[ $v['fenxiao_id'] ])) $count = $member_array[ $v['fenxiao_id'] ][ 'count' ];

                    if(isset($team_array[$v['fenxiao_id']])){
                        foreach ($team_array[$v['fenxiao_id']] as $key => $val){
                            if(isset($member_array[ $val['fenxiao_id'] ])) $num += $member_array[ $val['fenxiao_id'] ][ 'count' ];
                        }
                    }
                    $list[ 'list' ][ $k ][ 'team_num' ] = $count + $num;
                }

                break;
        }

        return $this->success($list);
    }

    /**
     * 获取分销分页列表2
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getFenxiaoPageLists($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*', $alias = 'a', $join = null)
    {
        $list = model('fenxiao')->pageList($condition, $field, $order, $page, $page_size, $alias, $join, '');
        return $this->success($list);
    }

    /**
     * 获取分销商团队
     * @param $level
     * @param $fenxiao_id
     * @param int $page
     * @param int $page_size
     * @param int $is_pay
     * @return array
     */
    public function getFenxiaoTeam($level, $fenxiao_id, $page = 1, $page_size = PAGE_LIST_ROWS, $is_pay = 0)
    {
        $condition = '';

        // 下级分销商id集合
        $one_level_fenxiao = model('fenxiao')->getColumn([ [ 'parent', '=', $fenxiao_id ] ], 'fenxiao_id');

        switch ( $level ) {
            // 一级分销
            case 1:
                // 直属会员 + 直属下级分销商
                $or = " OR (f.parent = {$fenxiao_id}) ";
                $condition = "( (m.fenxiao_id = {$fenxiao_id} AND m.is_fenxiao = 0) " . $or . ') AND m.is_delete = 0';
                break;
            // 二级分销
            case 2:
                // 直属下级分销商的下级分销商 + 直属下级分销商的会员
                if (!empty($one_level_fenxiao)) {
                    $or = ' OR (f.parent in (' . implode(',',$one_level_fenxiao) . ') ) ';
                    $condition = '( (m.is_fenxiao = 0 AND m.fenxiao_id in (' . implode(',', $one_level_fenxiao) . ') )' . $or . ') AND m.is_delete = 0';
                }
                break;
        }
        if (empty($condition)) return $this->success([
            'page_count' => 1,
            'count' => 0,
            'list' => []
        ]);

        if ($is_pay) $condition .= ' AND m.order_num > 0';
        $condition .= '';

        $field = 'm.member_id,m.nickname,m.headimg,m.is_fenxiao,m.reg_time,m.order_money,m.order_complete_money,m.order_num,m.order_complete_num,m.bind_fenxiao_time,f.fenxiao_id,f.fenxiao_no,f.fenxiao_name,f.audit_time,f.level_name,f.one_child_num,f.one_child_fenxiao_num';
        $alias = 'm';
        $join = [
            [ 'fenxiao f', 'm.member_id = f.member_id', 'left' ]
        ];

        $list = model('member')->pageList($condition, $field, 'm.bind_fenxiao_time desc', $page, $page_size, $alias, $join);
        return $this->success($list);
    }

    /**
     * 查询我的团队的数量
     * @param $fenxiao_id
     * @param $site_id
     * @return array
     */
    public function getFenxiaoTeamNum($fenxiao_id, $site_id)
    {
        // 查询分销基础配置
        $config_model = new Config();
        $fenxiao_basic_config = $config_model->getFenxiaoBasicsConfig($site_id)[ 'data' ][ 'value' ];
        $level = $fenxiao_basic_config[ 'level' ];

        $return = [
            'num' => 0, // 总人数
            'member_num' => 0, // 会员人数
            'fenxiao_num' => 0 // 分销商人数
        ];

        //递归查询
        $calc_level = 1;
        $parent_fenxiao_ids = [$fenxiao_id];
        while($calc_level <= $level){
            if(!empty($parent_fenxiao_ids)){
                $member_num = model('member')->getCount([ [ 'fenxiao_id', 'in', $parent_fenxiao_ids ], [ 'is_fenxiao', '=', 0 ], [ 'is_delete', '=', 0 ] ]);
                $fenxiao_list = model('fenxiao')->getList([ [ 'parent', 'in', $parent_fenxiao_ids ], [ 'is_delete', '=', 0 ] ], 'fenxiao_id');
                $fenxiao_ids = array_column($fenxiao_list, 'fenxiao_id');
                $fenxiao_num = count($fenxiao_ids);

                //会员数量
                $return['member_num'] += $member_num;
                $return['member_num_'.$calc_level] = $member_num;
                //分销商数量
                $return['fenxiao_num'] += $fenxiao_num;
                $return['fenxiao_num_'.$calc_level] = $fenxiao_num;
                //总数量
                $return['num_'.$calc_level] = $member_num + $fenxiao_num;
                $return['num'] += $member_num + $fenxiao_num;

                $parent_fenxiao_ids = $fenxiao_ids;
            }else{
                $return['member_num_'.$calc_level] = 0;
                $return['fenxiao_num_'.$calc_level] = 0;
                $return['num_'.$calc_level] = 0;
            }

            $calc_level ++;
        }
        $return['level'] = $level;

        return $this->success($return);
    }

    /**
     * 会员注册之后
     * @param $member_id
     * @param $site_id
     */
    public function memberRegister($member_id, $site_id)
    {
        //如果有推荐人则要修改分享关系
        $member_model = new Member();
        $member_info = $member_model->getMemberInfo([ [ 'member_id', '=', $member_id ] ], 'source_member')[ 'data' ];
        if (!empty($member_info[ 'source_member' ])) {
            $member_model->alterShareRelation($member_id, $member_info[ 'source_member' ], $site_id);
        }
        $this->autoBecomeFenxiao($member_id, $site_id);
    }

    /**
     * 自动成为分销商
     * @param $member_id
     * @param $site_id
     * @return array|mixed
     */
    public function autoBecomeFenxiao($member_id, $site_id)
    {
        $member_info = model('member')->getInfo([ [ 'member_id', '=', $member_id ], [ 'site_id', '=', $site_id ], [ 'is_delete', '=', 0 ] ], 'order_num,order_complete_num,order_money,order_complete_money,is_fenxiao');
        if (empty($member_info)) return $this->error('', '未查询到会员信息');

        $fenxiao_info = $this->getFenxiaoDetailInfo([ [ 'f.member_id', '=', $member_id ] ])[ 'data' ];
        if (!empty($fenxiao_info) && $member_info[ 'is_fenxiao' ]) return $this->error('', '已经是分销商');

        try {
            $config = new Config();

            // 分销商基础设置
            $basics_config = $config->getFenxiaoBasicsConfig($site_id)[ 'data' ][ 'value' ];
            if (!$basics_config[ 'level' ]) return $this->error('', '未开启分销');
            if ($basics_config[ 'is_apply' ] != 0) return $this->error('', '成为分销商需进行申请');

            // 成为分销商的资格
            $fenxiao_config = $config->getFenxiaoConfig($site_id)[ 'data' ][ 'value' ];

            switch ( $fenxiao_config[ 'fenxiao_condition' ] ) {
                case 2:
                    // 消费次数
                    if ($fenxiao_config[ 'consume_condition' ] == 1 && $member_info[ 'order_num' ] < $fenxiao_config[ 'consume_count' ]) return $this->error('', '未满足成为分销商的条件');
                    if ($fenxiao_config[ 'consume_condition' ] == 2 && $member_info[ 'order_complete_num' ] < $fenxiao_config[ 'consume_count' ]) return $this->error('', '未满足成为分销商的条件');
                    break;
                case 3:
                    // 消费金额
                    if ($fenxiao_config[ 'consume_condition' ] == 1 && bccomp($member_info[ 'order_money' ], $fenxiao_config[ 'consume_money' ], 2) == -1) return $this->error('', '未满足成为分销商的条件');
                    if ($fenxiao_config[ 'consume_condition' ] == 2 && bccomp($member_info[ 'order_complete_money' ], $fenxiao_config[ 'consume_money' ], 2) == -1) return $this->error('', '未满足成为分销商的条件');
                    break;
                case 4:
                    // 购买指定商品
                    $condition = [
                        [ 'og.goods_id', 'in', $fenxiao_config[ 'goods_ids' ] ],
                        [ 'og.member_id', '=', $member_id ]
                    ];
                    if ($fenxiao_config[ 'consume_condition' ] == 1) $condition[] = [ 'pay_status', '=', 1 ];
                    if ($fenxiao_config[ 'consume_condition' ] == 2) $condition[] = [ 'order_status', '=', OrderCommon::ORDER_COMPLETE ];
                    $count = model('order_goods')->getCount($condition, 'order_goods_id', 'og', [ [ 'order o', 'o.order_id = og.order_id', 'inner' ] ]);
                    if (!$count) return $this->error('', '未满足成为分销商的条件');
                    break;
            }
            return $this->directlyBecomeFenxiao($member_id);
        } catch (\Exception $e) {
            return $this->error('', 'File：' . $e->getFile() . '，Line：' . $e->getLine() . '，Message：' . $e->getMessage() . ',Code：' . $e->getCode());
        }
    }

    /**
     * 会员直接成为分销商
     * @param $member_id
     * @return mixed
     */
    private function directlyBecomeFenxiao($member_id)
    {
        //获取用户信息
        $member_field = 'member_id,site_id,source_member,fenxiao_id,nickname,headimg,mobile,reg_time,order_money,order_complete_money,order_num,order_complete_num';
        $member_info = model('member')->getInfo([ [ 'member_id', '=', $member_id ] ], $member_field);

        if (!empty($member_info)) {
            $parent = 0;
            if (!empty($member_info[ 'fenxiao_id' ])) {
                $fenxiao_info = model('fenxiao')->getInfo([ [ 'fenxiao_id', '=', $member_info[ 'fenxiao_id' ] ], [ 'is_delete', '=', 0 ] ], 'fenxiao_id');
                if (!empty($fenxiao_info)) $parent = $fenxiao_info[ 'fenxiao_id' ];
            }

            //获取分销等级信息
            $level_model = new FenxiaoLevel();
            $level_info = $level_model->getLevelInfo([ [ 'site_id', '=', $member_info[ 'site_id' ] ], [ 'is_default', '=', 1 ] ], 'level_id,level_name');

            $data = [
                'site_id' => $member_info[ 'site_id' ],
                'fenxiao_name' => $member_info[ 'nickname' ],
                'mobile' => $member_info[ 'mobile' ],
                'member_id' => $member_info[ 'member_id' ],
                'parent' => $parent,
                'level_id' => $level_info[ 'data' ][ 'level_id' ],
                'level_name' => $level_info[ 'data' ][ 'level_name' ]
            ];
            $res = $this->addFenxiao($data);
            return $res;
        }
    }

    /**
     * 绑定上下线关系
     * TODO 订单创建对返回结果进行检测 不可返回失败
     * @param $param
     * @return array|void
     */
    public function bindRelation($param)
    {
        $site_id = $param[ 'site_id' ] ?? 0;
        $member_id = $param[ 'member_id' ] ?? 0;
        $action = $param[ 'action' ] ?? '';

        $config = [
            'alter_share_relation' => 1,//对应 首次点击链接后绑定
            'order_create' => 2,//对应 首次下单后绑定
            'order_pay' => 3,//对应 首次付款后绑定
        ];
        if (!isset($config[ $action ])) return;

        //检测触发场景和设置是否匹配
        $config_model = new ConfigModel();
        $child_condition = $config_model->getFenxiaoRelationConfig($site_id)[ 'data' ][ 'value' ][ 'child_condition' ];
        if ($child_condition != $config[ $action ]) return;

        //检测用户
        $member_info = model('member')->getInfo([
            [ 'member_id', '=', $member_id ],
        ], 'share_member,fenxiao_id');
        if (empty($member_info)) return;
        //如果已经是分销商 不可以再修改关系
        if (!empty($member_info[ 'fenxiao_id' ])) return;

        // 查询推荐人是否是分销商
        $fenxiao_info = model('fenxiao')->getInfo([
            [ 'member_id', '=', $member_info[ 'share_member' ] ],
            [ 'is_delete', '=', 0 ],
        ], 'fenxiao_id');
        if (empty($fenxiao_info)) return;

        model('member')->startTrans();
        try {
            $member_data = [
                'fenxiao_id' => $fenxiao_info[ 'fenxiao_id' ],
                'bind_fenxiao_time' => time()
            ];
            model('member')->update($member_data, [ [ 'member_id', '=', $member_id ] ]);
            model('fenxiao')->setInc([ [ 'fenxiao_id', '=', $fenxiao_info[ 'fenxiao_id' ] ] ], 'one_child_num');

            // 分销商检测升级
            event('FenxiaoUpgrade', $fenxiao_info[ 'fenxiao_id' ]);

            model('member')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('member')->rollback();
            return;
        }
    }

    /**
     * 分销商检测升级
     * @param $fenxiao_id
     */
    public function fenxiaoUpgrade($fenxiao_id)
    {
        $join = [
            [ 'member m', 'f.member_id = m.member_id', 'inner' ],
            [ 'fenxiao_level fl', 'f.level_id = fl.level_id', 'inner' ]
        ];
        $fenxiao_info = model('fenxiao')->getInfo([ [ 'f.fenxiao_id', '=', $fenxiao_id ], [ 'f.status', '=', 1 ], [ 'f.is_delete', '=', 0 ] ], 'f.level_id,m.order_num,m.order_money,f.one_fenxiao_order_num,f.one_fenxiao_order_money,f.one_fenxiao_total_order,f.one_child_num,f.one_child_fenxiao_num,fl.one_rate,fl.level_num,f.site_id', 'f', $join);
        if (!empty($fenxiao_info)) {
            $level_list = model('fenxiao_level')->getList([ [ 'site_id', '=', $fenxiao_info[ 'site_id' ] ], [ 'level_num', '>', $fenxiao_info[ 'level_num' ] ] ], '*', 'level_num asc,one_rate asc');
            if (!empty($level_list)) {
                $upgrade_level = null;
                foreach ($level_list as $item) {
                    if ($item[ 'upgrade_type' ] == 2) {
                        if ($fenxiao_info[ 'order_num' ] >= $item[ 'order_num' ] && $fenxiao_info[ 'order_money' ] >= $item[ 'order_money' ] && $fenxiao_info[ 'one_fenxiao_order_num' ] >= $item[ 'one_fenxiao_order_num' ] && $fenxiao_info[ 'one_fenxiao_total_order' ] >= $item[ 'one_fenxiao_total_order' ] && $fenxiao_info[ 'one_fenxiao_order_money' ] >= $item[ 'one_fenxiao_order_money' ] && $fenxiao_info[ 'one_child_num' ] >= $item[ 'one_child_num' ] && $fenxiao_info[ 'one_child_fenxiao_num' ] >= $item[ 'one_child_fenxiao_num' ]) {
                            $upgrade_level = $item;
                            break;
                        }
                    } else {
                        if (( $fenxiao_info[ 'order_num' ] >= $item[ 'order_num' ] && $item[ 'order_num' ] > 0 ) || ( $fenxiao_info[ 'order_money' ] >= $item[ 'order_money' ] && $item[ 'order_money' ] > 0 ) || ( $fenxiao_info[ 'one_fenxiao_order_num' ] >= $item[ 'one_fenxiao_order_num' ] && $item[ 'one_fenxiao_order_num' ] > 0 ) || ( $fenxiao_info[ 'one_fenxiao_order_money' ] >= $item[ 'one_fenxiao_order_money' ] && $item[ 'one_fenxiao_order_money' ] > 0 ) || ( $fenxiao_info[ 'one_fenxiao_total_order' ] >= $item[ 'one_fenxiao_total_order' ] && $item[ 'one_fenxiao_total_order' ] > 0 ) || ( $fenxiao_info[ 'one_child_num' ] >= $item[ 'one_child_num' ] && $item[ 'one_child_num' ] > 0 ) || ( $fenxiao_info[ 'one_child_fenxiao_num' ] >= $item[ 'one_child_fenxiao_num' ] && $item[ 'one_child_fenxiao_num' ] > 0 )) {
                            $upgrade_level = $item;
                            break;
                        }
                    }
                }
                if ($upgrade_level) {
                    model('fenxiao')->update([ 'level_id' => $upgrade_level[ 'level_id' ], 'level_name' => $upgrade_level[ 'level_name' ] ], [ [ 'fenxiao_id', '=', $fenxiao_id ] ]);
                }
            }
        }
    }

    /**
     * 获取下一个可升级的分销商等级 及当前分销商已达成的条件
     * @param $member_id
     * @param $site_id
     * @return array
     */
    public function geFenxiaoNextLevel($member_id, $site_id)
    {
        $array = [];
        $join = [
            [ 'member m', 'f.member_id = m.member_id', 'inner' ],
            [ 'fenxiao_level fl', 'f.level_id = fl.level_id', 'inner' ]
        ];
        $fenxiao_info = model('fenxiao')->getInfo(
            [ [ 'f.member_id', '=', $member_id ], [ 'f.site_id', '=', $site_id ], [ 'f.status', '=', 1 ], [ 'f.is_delete', '=', 0 ] ],
            'f.level_id,m.order_num,m.order_money,f.one_fenxiao_order_num,f.one_fenxiao_order_money,f.one_child_num,f.one_child_fenxiao_num,fl.one_rate,fl.level_num', 'f', $join
        );
        $array[ 'fenxiao' ] = $fenxiao_info;
        $last_level = [];
        if (!empty($fenxiao_info)) {
            $last_level = model('fenxiao_level')->getFirstData([ [ 'site_id', '=', $site_id ], [ 'level_num', '>=', $fenxiao_info[ 'level_num' ] ], [ 'level_id', '<>', $fenxiao_info[ 'level_id' ] ] ], '*', 'level_num asc,one_rate asc');
        }
        $array[ 'last_level' ] = $last_level;
        return $this->success($array);
    }

    /**
     * 变更上下级关系
     * @param $member_id
     * @param $parent
     * @return array
     */
    public function changeParentFenxiao($member_id, $parent)
    {
        if ($member_id == '' || $member_id == 0) {
            return $this->error('', '参数member_id不能为空');
        }
        if ($parent == '' || $parent == 0) {
            return $this->error('', '上级分销商不能为空');
        }

        //获取上级分销商id
        $parent_info = model('fenxiao')->getInfo([ [ 'fenxiao_id', '=', $parent ], [ 'is_delete', '=', 0 ] ]);
        if (empty($parent_info)) {
            return $this->error('', '上级分销商不存在');
        }

        //用户信息
        $member_info = model('member')->getInfo([ [ 'member_id', '=', $member_id ] ], 'fenxiao_id,is_fenxiao');
        if (empty($member_info)) {
            return $this->error('', '用户不存在');
        }

        model('fenxiao')->startTrans();
        try {

            if ($member_info[ 'is_fenxiao' ] == 1) {//是分销商

                $fenxiao_info = model('fenxiao')->getInfo([ [ 'fenxiao_id', '=', $member_info[ 'fenxiao_id' ] ], [ 'is_delete', '=', 0 ] ], 'parent');
                //修改原有上级分销商团队人数
                if ($fenxiao_info[ 'parent' ] > 0) {
                    //获取原有上级分销商信息
                    model('fenxiao')->setDec([ [ 'fenxiao_id', '=', $fenxiao_info[ 'parent' ] ] ], 'one_child_fenxiao_num');
                }

                //修改变更后的上级分销商团队人数
                model('fenxiao')->setInc([ [ 'fenxiao_id', '=', $parent ] ], 'one_child_fenxiao_num');
                //修改上级分销商
                model('fenxiao')->update([ 'parent' => $parent, 'grand_parent' => $parent_info[ 'parent' ] ], [ [ 'fenxiao_id', '=', $member_info[ 'fenxiao_id' ] ] ]);
            } else {
                //不是分销商

                //修改上级分销商
                model('member')->update([ 'fenxiao_id' => $parent ], [ [ 'member_id', '=', $member_id ] ]);
                //修改变更后的上级分销商团队人数
                model('fenxiao')->update([ 'one_child_num' => $parent_info[ 'one_child_num' ] + 1 ], [ [ 'fenxiao_id', '=', $parent ] ]);

            }

            model('fenxiao')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('fenxiao')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 取消上级分销商
     * @param $member_id
     * @return array
     */
    public function cancelParentFenxiao($member_id)
    {
        if ($member_id == '' || $member_id == 0) {
            return $this->error('', '参数member_id不能为空');
        }

        //用户信息
        $member_info = model('member')->getInfo([ [ 'member_id', '=', $member_id ] ], 'fenxiao_id,is_fenxiao');
        if (empty($member_info)) {
            return $this->error('', '用户不存在');
        }

        model('fenxiao')->startTrans();
        try {

            if ($member_info[ 'is_fenxiao' ] == 1) {//是分销商

                $fenxiao_info = model('fenxiao')->getInfo(
                    [ [ 'fenxiao_id', '=', $member_info[ 'fenxiao_id' ] ], [ 'is_delete', '=', 0 ] ],
                    'parent'
                );
                //修改原有上级分销商团队人数
                if ($fenxiao_info[ 'parent' ] > 0) {
                    //获取原有上级分销商信息
                    model('fenxiao')->setDec([ [ 'fenxiao_id', '=', $fenxiao_info[ 'parent' ] ] ], 'one_child_fenxiao_num');
                }
                //修改上级分销商
                model('fenxiao')->update([ 'parent' => '0' ], [ [ 'fenxiao_id', '=', $member_info[ 'fenxiao_id' ] ] ]);
            }
            model('fenxiao')->commit();
            return $this->success();
        } catch (\Exception $e) {

            model('fenxiao')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 获取上级分销商名称
     * @param $fenxiao_id
     * @param int $type 1-上级
     * @return mixed|string
     */
    public function getParentFenxiaoName($fenxiao_id, $type = 1)
    {
        if ($fenxiao_id == 0) {
            return '';
        }
        if ($type == 1) {
            $fenxiao_name = model('fenxiao')->getValue([ [ 'fenxiao_id', '=', $fenxiao_id ] ], 'fenxiao_name');
            return $fenxiao_name;
        } else {

            $parent = model('fenxiao')->getValue([ [ 'fenxiao_id', '=', $fenxiao_id ] ], 'parent');
            if ($parent == 0) {
                return '';
            } else {
                $fenxiao_name = model('fenxiao')->getValue([ [ 'fenxiao_id', '=', $parent ] ], 'fenxiao_name');
                return $fenxiao_name;
            }
        }
    }

    /**
     * 查询上级分销商名称列表（用于会员列表查询）
     * @param $fenxiao_id
     */
    public function getParentFenxiaoNameList($fenxiao_ids)
    {
        $fenxiao_name_list = model('fenxiao')->getList([ [ 'fenxiao_id', 'in', $fenxiao_ids ] ], 'fenxiao_id, fenxiao_name');
        return $fenxiao_name_list;
    }

    /**
     * 会员注销删除分销商
     * @param $member_id
     * @param $site_id
     * @return array
     */
    public function CronMemberCancel($member_id, $site_id)
    {
        $info = model('fenxiao')->getInfo([ [ 'member_id', '=', $member_id ], [ 'site_id', '=', $site_id ] ]);
        if (empty($info)) {
            return $this->success();
        }

        //冻结账户并删除
        $data = [
            'status' => -1,
            'lock_time' => time(),
            'is_delete' => 1
        ];

        $res = model('fenxiao')->update($data, [ [ 'fenxiao_id', '=', $info[ 'fenxiao_id' ] ] ]);
        return $this->success($res);
    }

    /**
     * 变更分销商等级
     * @param $data
     * @param $condition
     * @return array
     */
    public function changeFenxiaoLevel($data, $condition)
    {
        $result = model('fenxiao')->update($data, $condition);
        return $this->success($result);
    }

    /**
     * 获取分销等级分销商数量
     * @param $condition
     * @return int|mixed
     */
    public function getFenxiaoMemberCount($condition)
    {
        $condition[] = [ 'is_delete', '=', 0 ];
        $count = model('fenxiao')->getCount($condition);
        return $count;
    }

    /**
     * 获取分销商排名
     * @param $site_id
     * @param $fenxiao_id
     * @param $order
     * @return array
     */
    public function getFenxiaoRanking($site_id, $fenxiao_id, $order)
    {
        $prefix = config('database.connections.mysql.prefix');
        $version = model('fenxiao')->query('SELECT VERSION() as version')[ 0 ][ 'version' ];

        if (substr($version, 0, 1) == 8) {
            $query = "SELECT * FROM (select *,row_number() OVER(order by {$order} DESC) as rownum from {$prefix}fenxiao nf) AS f WHERE f.fenxiao_id = {$fenxiao_id}";
        } else {
            $query = "SELECT b.rownum FROM (SELECT t.*, @rownum := @rownum + 1 AS rownum FROM (SELECT @rownum := 0) r,(SELECT * FROM {$prefix}fenxiao WHERE site_id = {$site_id} ORDER BY {$order} DESC,fenxiao_id ASC) AS t) AS b WHERE b.fenxiao_id = {$fenxiao_id};";
        }
        $data = model('fenxiao')->query($query);
        $data = empty($data) ? 0 : $data[ 0 ][ 'rownum' ];
        return $this->success($data);
    }

    // todo 删除分销商

}

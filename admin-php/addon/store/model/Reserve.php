<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 上海牛之云网络科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\store\model;

use app\model\BaseModel;
use app\model\member\Member;

/**
 * 预约
 */
class Reserve extends BaseModel
{

    /**
     * 预约状态
     * @var array
     */
    public $reserve_state = [
        'wait_confirm' => [
            'state' => 'wait_confirm',
            'name' => '待确认',
            'color' => '#8558FA'
        ],
        'wait_to_store' => [
            'state' => 'wait_to_store',
            'name' => '待到店',
            'color' => '#1475FA'
        ],
        'arrived_store' => [
            'state' => 'arrived_store',
            'name' => '已到店',
            'color' => '#FA5B14'
        ],
        'completed' => [
            'state' => 'completed',
            'name' => '已完成',
            'color' => '#10C610'
        ],
        'cancelled' => [
            'state' => 'cancelled',
            'name' => '已取消',
            'color' => '#CCCCCC'
        ]
    ];

    /**
     * 设置预约配置
     * @param $data
     * @param $site_id
     * @param $app_module
     * @return array
     */
    public function setReserveConfig($data, $site_id, $store_id)
    {
        $save_data[ 'config_desc' ] = '预约配置';
        $save_data[ 'is_use' ] = 1;
        $save_data[ 'value' ] = json_encode($data);

        $config_model = model('reserve_config');
        $condition = [
            [ 'site_id', '=', $site_id ],
            [ 'store_id', '=', $store_id ],
            [ 'config_key', '=', 'RESERVE_CONFIG' ]
        ];
        $info = $config_model->getInfo($condition, 'id');
        if (empty($info)) {
            $save_data[ 'create_time' ] = time();
            $save_data[ 'site_id' ] = $site_id;
            $save_data[ 'store_id' ] = $store_id;
            $save_data[ 'config_key' ] = 'RESERVE_CONFIG';
            $res = $config_model->add($save_data);
        } else {
            $save_data[ 'modify_time' ] = time();
            $res = $config_model->update($save_data, $condition);
        }
        return $this->success($res);
    }

    /**
     * 获取预约配置
     * @param $site_id
     * @param $app_module
     * @return array
     */
    public function getReserveConfig($site_id, $store_id)
    {
        $key = 'RESERVE_CONFIG';
        $info = model('reserve_config')->getInfo([ [ 'site_id', '=', $site_id ], [ 'store_id', '=', $store_id ], [ 'config_key', '=', $key ] ], 'site_id, store_id, config_key, value, config_desc, is_use, create_time, modify_time');
        if (!empty($info)) {
            $info[ 'value' ] = json_decode($info[ 'value' ], true);
        } else {
            $info = [
                'site_id' => $site_id,
                'store_id' => $store_id,
                'config_key' => $key,
                'value' => [
                    'week' => '[1,2,3,4,5]',
                    'start' => 32400,
                    'end' => 79200,
                    'interval' => 30,
                    'advance' => 1,
                    'max' => 10
                ],
                'config_desc' => '',
                'is_use' => 0,
                'create_time' => 0,
                'modify_time' => 0
            ];
        }
        $info[ 'value' ][ 'week' ] = json_decode($info[ 'value' ][ 'week' ], true);

        return $this->success($info);
    }

    /**
     * 添加预约
     * @param array $param
     * @return array
     */
    public function addReserve(array $param)
    {

        $member_info = ( new Member() )->getMemberInfo([ [ 'member_id', '=', $param[ 'member_id' ] ], [ 'site_id', '=', $param[ 'site_id' ] ] ])[ 'data' ];
        if (empty($member_info)) return $this->error('', '未查找到会员信息');

        $store_info = model('store')->getInfo([ [ 'store_id', '=', $param[ 'store_id' ] ], [ 'site_id', '=', $param[ 'site_id' ] ] ], 'status');
        if (empty($store_info)) return $this->error('', '未查找到门店信息');

        $check_res = $this->checkReserve($param);
        if ($check_res[ 'code' ] != 0) return $check_res;
        model('reserve')->startTrans();
        try {
            $data = [
                'site_id' => $param[ 'site_id' ],
                'member_id' => $param[ 'member_id' ],
                'reserve_name' => $member_info[ 'nickname' ],
                'reserve_time' => strtotime("{$param['date']} {$param['time']}"),
                'reserve_state' => $this->reserve_state[ 'wait_confirm' ][ 'state' ],
                'reserve_state_name' => $this->reserve_state[ 'wait_confirm' ][ 'name' ],
                'remark' => $param[ 'remark' ],
                'store_id' => $param[ 'store_id' ],
                'source' => $param[ 'source' ] ?? 'member',
                'create_time' => time()
            ];
            $reserve_id = model('reserve')->add($data);

            $reserve_item = [];
            $reserve_item_data = [];
            foreach ($param[ 'goods' ] as $item) {
                $goods_info = model('goods')->getInfo([ [ 'site_id', '=', $param[ 'site_id' ] ], [ 'sku_id', '=', $item[ 'sku_id' ] ], [ 'goods_state', '=', 1 ], [ 'is_delete', '=', 0 ] ], 'goods_name');

                if (empty($goods_info)) {
                    model('reserve')->rollback();
                    return $this->error('', '未查找到所预约的服务');
                }

                // 同一时间，一个服务人员只能预约一个客户
                if (!empty($item[ 'uid' ])) {
                    $count = model('reserve_item')->getCount([
                        [ 'site_id', '=', $param[ 'site_id' ] ],
                        [ 'member_id', '=', $param[ 'member_id' ] ],
                        [ 'reserve_user_id', '=', $item[ 'uid' ] ],
                        [ 'reserve_goods_sku_id', '=', $item[ 'sku_id' ] ],
                        [ 'store_id', '=', $param[ 'store_id' ] ],
                        [ 'reserve_time', '=', strtotime("{$param['date']} {$param['time']}") ]
                    ]);
                    if ($count) {
                        model('reserve')->rollback();
                        return $this->error('', '请勿重复预约服务');
                    }
                    $count = model('reserve_item')->getCount([
                        [ 'site_id', '=', $param[ 'site_id' ] ],
                        [ 'reserve_user_id', '=', $item[ 'uid' ] ],
                        [ 'store_id', '=', $param[ 'store_id' ] ],
                        [ 'reserve_time', '=', strtotime("{$param['date']} {$param['time']}") ]
                    ]);
                    if ($count) {
                        model('reserve')->rollback();
                        return $this->error('', '同一时间，一个服务人员只能预约一个客户');
                    }
                }

                $reserve_item[] = $goods_info['goods_name'];
                $reserve_item_data[] = [
                    'reserve_id' => $reserve_id,
                    'site_id' => $param['site_id'],
                    'member_id' => $param['member_id'],
                    'reserve_name' => $member_info['nickname'],
                    'reserve_time' => strtotime("{$param['date']} {$param['time']}"),
                    'remark' => $param['remark'],
                    'reserve_user_id' => $item['uid'] ?? 0,
                    'reserve_goods_sku_id' => $item['sku_id'],
                    'reserve_state' => $this->reserve_state['wait_confirm']['state'],
                    'store_id' => $param['store_id'],
                ];
            }
            model('reserve_item')->addList($reserve_item_data);
            model('reserve')->update([ 'reserve_item' => implode($reserve_item) ], [ [ 'reserve_id', '=', $reserve_id ] ]);

            model('reserve')->commit();
            return $this->success($reserve_id);
        } catch (\Exception $e) {
            model('reserve')->rollback();
            return $this->error('添加失败' . $e->getMessage() . $e->getFile() . $e->getLine());
        }
    }

    /**
     * 编辑预约
     * @param $param
     * @return array
     */
    public function editReserve($param)
    {
        $condition = [
            [ 'reserve_id', '=', $param[ 'reserve_id' ] ],
            [ 'site_id', '=', $param[ 'site_id' ] ]
        ];
        if (isset($param[ 'member_id' ])) $condition[] = [ 'member_id', '=', $param[ 'member_id' ] ];
        $info = model('reserve')->getInfo($condition);

        if (empty($info)) return $this->error('', '未获取到预约信息');
        if (!in_array($info[ 'reserve_state' ], [ $this->reserve_state[ 'wait_confirm' ][ 'state' ], $this->reserve_state[ 'wait_to_store' ][ 'state' ] ]))
            return $this->error('', '该预约已不可更改');

        $check_res = $this->checkReserve($param);
        if ($check_res[ 'code' ] != 0) return $check_res;

        model('reserve')->startTrans();
        try {
            $res = model('reserve')->update([
                'reserve_time' => strtotime("{$param['date']} {$param['time']}"),
                'remark' => $param[ 'remark' ],
            ], $condition);

            // 删除原预约项

            $reserve_item = [];
            $reserve_item_data = [];
            foreach ($param[ 'goods' ] as $item) {
                $goods_info = model('goods')->getInfo([ [ 'site_id', '=', $param[ 'site_id' ] ], [ 'sku_id', '=', $item[ 'sku_id' ] ], [ 'goods_state', '=', 1 ], [ 'is_delete', '=', 0 ] ], 'goods_name');
                if (empty($goods_info)) {
                    model('reserve')->rollback();
                    return $this->error('', '未查找到所预约的服务');
                }

                // 同一时间，一个服务人员只能预约一个客户
                if (!empty($item[ 'uid' ])) {
                    $count = model('reserve_item')->getCount([
                        [ 'site_id', '=', $param[ 'site_id' ] ],
                        [ 'member_id', '=', $info[ 'member_id' ] ],
                        [ 'reserve_user_id', '=', $item[ 'uid' ] ],
                        [ 'reserve_goods_sku_id', '=', $item[ 'sku_id' ] ],
                        [ 'store_id', '=', $info[ 'store_id' ] ],
                        [ 'reserve_time', '=', strtotime("{$param['date']} {$param['time']}") ]
                    ]);
                    if ($count) {
                        model('reserve')->rollback();
                        return $this->error('', '请勿重复预约服务');
                    }
                    $count = model('reserve_item')->getCount([
                        [ 'site_id', '=', $param[ 'site_id' ] ],
                        [ 'reserve_user_id', '=', $item[ 'uid' ] ],
                        [ 'store_id', '=', $info[ 'store_id' ] ],
                        [ 'reserve_time', '=', strtotime("{$param['date']} {$param['time']}") ]
                    ]);
                    if ($count) {
                        model('reserve')->rollback();
                        return $this->error('', '同一时间，一个服务人员只能预约一个客户');
                    }
                }
                $reserve_item[] = $goods_info['goods_name'];
                $reserve_item_data[] = [
                    'reserve_id' => $param['reserve_id'],
                    'site_id' => $param['site_id'],
                    'member_id' => $info['member_id'],
                    'reserve_name' => $info['reserve_name'],
                    'reserve_time' => strtotime("{$param['date']} {$param['time']}"),
                    'remark' => $param['remark'],
                    'reserve_user_id' => $item['uid'] ?? 0,
                    'reserve_goods_sku_id' => $item['sku_id'],
                    'reserve_state' => $info['reserve_state'],
                    'store_id' => $info['store_id']
                ];
            }

            model('reserve_item')->delete([ [ 'reserve_id', '=', $param[ 'reserve_id' ] ] ]);
            model('reserve_item')->addList($reserve_item_data);
            model('reserve')->update([ 'reserve_item' => implode($reserve_item) ], [ [ 'reserve_id', '=', $param[ 'reserve_id' ] ] ]);

            model('reserve')->commit();
            return $this->success($res);
        } catch (\Exception $e) {
            model('reserve')->rollback();
            return $this->error();
        }
    }

    /**
     * 校验预约是否可添加
     * @param $param
     * @return array
     */
    public function checkReserve($param)
    {
        $site_id = $param[ 'site_id' ] ?? 0;
        $date = $param[ 'date' ] ?? 0;
        $time = $param[ 'time' ] ?? 0;
        $store_id = $param[ 'store_id' ] ?? 0;
        $reserve_id = $param[ 'reserve_id' ] ?? 0;
        $app_module = $param[ 'app_module' ] ?? '';
        $config = $this->getReserveConfig($site_id, $store_id)[ 'data' ][ 'value' ];
        // 预约时间
        $reserve_time = strtotime($date . ' ' . $time);
        if (( $reserve_time - time() ) < ( $config[ 'advance' ] * 3600 )) return $this->error('', '需提前' . $config[ 'advance' ] . '小时预约');

        $week = date('w', strtotime($date));

        if (!in_array($week, $config[ 'week' ])) return $this->error('', '所选时间不在可预约时间内');

        $time = strtotime(date('Y-m-d') . ' ' . $time) - strtotime(date('Y-m-d'));
        if ($time < $config[ 'start' ] || $time > $config[ 'end' ]) return $this->error('', '所选时间不在可预约时间内');

        $max_condition = [
            [ 'site_id', '=', $site_id ],
            [ 'reserve_time', '=', $reserve_time ],
            [ 'reserve_state', '<>', $this->reserve_state[ 'cancelled' ][ 'state' ] ]
        ];
        if ($reserve_id > 0) $max_condition[] = [ 'reserve_id', '<>', $reserve_id ];
        $max = model('reserve')->getCount($max_condition);
        if ($max > $config[ 'max' ]) return $this->error('', '所选时段内预约人数已达上限');

        return $this->success();
    }

    /**
     * 获取预约列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param null $limit
     * @return array
     */
    public function getReserveList($condition = [], $field = '*', $order = '', $limit = null)
    {
        $list = model('reserve')->getList($condition, $field, $order, '', '', '', $limit);
        return $this->success($list);
    }

    /**
     * 获取预约数量
     * @param array $condition
     * @return array
     */
    public function getReserveCount($condition = [], $field = '*')
    {
        $list = model('reserve')->getCount($condition, $field);
        return $this->success($list);
    }

    /**
     * 获取预约分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getReservePageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*')
    {
        $join = [
            [
                'member nm',
                'noy.member_id = nm.member_id',
                'left'
            ],
            [
                'store os',
                'noy.store_id = os.store_id',
                'left'
            ]
        ];
        $list = model('reserve')->pageList($condition, $field, $order, $page, $page_size, 'noy', $join);
        return $this->success($list);
    }

    /**
     * 获取预约项列表
     * @param $condition
     * @param $field
     * @param $order
     * @param string $alias
     * @param null $join
     * @return array
     */
    public function getReserveItemList($condition, $field, $order, $alias = 'oyi', $join = null)
    {
        $list = model('reserve_item')->getList($condition, $field, $order, $alias, $join);
        return $this->success($list);
    }

    /**
     * 获取预约详情
     * @param $condition
     * @param string $field
     * @param string $alias
     * @param null $join
     * @return array
     */
    public function getReserveInfo($condition, $field = '*', $alias = 'a', $join = null)
    {
        $res = model('reserve')->getInfo($condition, $field, $alias, $join);
        return $this->success($res);
    }

    /**
     * 确认预约
     * @param $reserve_id
     * @param $site_id
     * @return array
     */
    public function confirmReserve($reserve_id, $site_id)
    {
        $condition = [
            [ 'reserve_id', '=', $reserve_id ],
            [ 'site_id', '=', $site_id ],
            [ 'reserve_state', '=', $this->reserve_state[ 'wait_confirm' ][ 'state' ] ]
        ];
        model('reserve')->update([
            'reserve_state' => $this->reserve_state[ 'wait_to_store' ][ 'state' ],
            'reserve_state_name' => $this->reserve_state[ 'wait_to_store' ][ 'name' ]
        ], $condition);
        model('reserve_item')->update([ 'reserve_state' => $this->reserve_state[ 'wait_to_store' ][ 'state' ] ], $condition);

        return $this->success();
    }

    /**
     * 取消预约
     * @param $reserve_id
     * @param $site_id
     * @return array
     */
    public function cancelReserve($reserve_id, $site_id, $member_id = 0)
    {
        model('reserve')->startTrans();
        try {
            $condition = [
                [ 'reserve_id', '=', $reserve_id ],
                [ 'site_id', '=', $site_id ],
                [ 'reserve_state', 'in', [ $this->reserve_state[ 'wait_confirm' ][ 'state' ], $this->reserve_state[ 'wait_to_store' ][ 'state' ] ] ]
            ];
            if ($member_id) $condition[] = [ 'member_id', '=', $member_id ];
            model('reserve')->update([
                'reserve_state' => $this->reserve_state[ 'cancelled' ][ 'state' ],
                'reserve_state_name' => $this->reserve_state[ 'cancelled' ][ 'name' ],
                'cancel_time' => time()
            ], $condition);
            model('reserve_item')->update([ 'reserve_state' => $this->reserve_state[ 'cancelled' ][ 'state' ] ], $condition);

            model('reserve')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('reserve')->rollback();
            return $this->error();
        }
    }

    /**
     * 删除预约
     * @param $reserve_id
     * @param $site_id
     * @return array
     */
    public function deleteReserve($reserve_id, $site_id, $member_id = 0)
    {
        $condition = [
            [ 'reserve_id', '=', $reserve_id ],
            [ 'site_id', '=', $site_id ],
            [ 'reserve_state', '=', $this->reserve_state[ 'cancelled' ][ 'state' ] ]
        ];
        if ($member_id) $condition[] = [ 'member_id', '=', $member_id ];
        model('reserve')->delete($condition);
        model('reserve_item')->delete($condition);
        return $this->success();
    }

    /**
     * 确认到店
     * @param $reserve_id
     * @param $site_id
     * @return array
     */
    public function confirmToStore($reserve_id, $site_id)
    {
        $condition = [
            [ 'reserve_id', '=', $reserve_id ],
            [ 'site_id', '=', $site_id ],
            [ 'reserve_state', '=', $this->reserve_state[ 'wait_to_store' ][ 'state' ] ]
        ];
        model('reserve')->update([
            'reserve_state' => $this->reserve_state[ 'arrived_store' ][ 'state' ],
            'reserve_state_name' => $this->reserve_state[ 'arrived_store' ][ 'name' ],
            'to_store_time' => time()
        ], $condition);
        model('reserve_item')->update([ 'reserve_state' => $this->reserve_state[ 'arrived_store' ][ 'state' ] ], $condition);

        return $this->success();
    }

    /**
     * 确认完成
     * @param $reserve_id
     * @param $site_id
     * @return array
     */
    public function confirmComplete($reserve_id, $site_id)
    {
        $condition = [
            [ 'reserve_id', '=', $reserve_id ],
            [ 'site_id', '=', $site_id ],
            [ 'reserve_state', '=', $this->reserve_state[ 'arrived_store' ][ 'state' ] ]
        ];
        model('reserve')->update([
            'reserve_state' => $this->reserve_state[ 'completed' ][ 'state' ],
            'reserve_state_name' => $this->reserve_state[ 'completed' ][ 'name' ],
            'complete_time' => time()
        ], $condition);
        model('reserve_item')->update([ 'reserve_state' => $this->reserve_state[ 'completed' ][ 'state' ] ], $condition);

        return $this->success();
    }

    /**
     * 获取周看板日期数据
     * @param $week_offset
     * @return array
     */
    public function getWeekDays($week_offset)
    {
        $first_day = mktime(0, 0, 0, date("m"), date("d") - date("w") + 1, date("Y"));
        $first_day = strtotime($week_offset . ' week', $first_day);

        $week_names = [ '周日', '周一', '周二', '周三', '周四', '周五', '周六' ];
        $data = [];
        for ($i = 0; $i < 7; $i++) {
            $time = strtotime("+ {$i} day", $first_day);
            $data[] = [
                'start_time' => $time,
                'end_time' => strtotime(date('Y-m-d 23:59:59', $time)),
                'year' => date('Y', $time),
                'month' => date('m', $time),
                'day' => date('d', $time),
                'week' => date('w', $time),
                'week_name' => $week_names[ date('w', $time) ],
                'is_curr_day' => date('Y-m-d', $time) == date('Y-m-d') ? 1 : 0
            ];
        }
        return $this->success($data);
    }

    /**
     * 获取月看板日期数据
     * @param $year
     * @param $month
     * @return array
     */
    public function getMonthDays($year, $month)
    {
        $month_start_day = mktime(0, 0, 0, $month, 1, $year);
        //获取日历的第一天
        $first_day = mktime(0, 0, 0, $month, 1 - ( date("w", $month_start_day) - 1 ), $year);
        //获取日历的最后一天
        if ($month < 12) {
            $next_month = $month + 1;
            $next_month_year = $year;
        } else {
            $next_month = 1;
            $next_month_year = $year + 1;
        }
        $month_end_day = mktime(0, 0, 0, $next_month, 0, $next_month_year);
        $end_day = $month_end_day + ( 7 - date('w', $month_end_day) ) * 3600 * 24;

        $data = [];
        for ($timestamp = $first_day; $timestamp <= $end_day; $timestamp += 3600 * 24) {
            $data_item = [
                'start_time' => $timestamp,
                'end_time' => $timestamp + 3600 * 24 - 1,
                'year' => date('Y', $timestamp),
                'month' => date('m', $timestamp),
                'day' => date('d', $timestamp),
                'week' => date('w', $timestamp),
            ];
            $data_item[ 'is_curr_month' ] = ( $month == $data_item[ 'month' ] );
            $data[] = $data_item;
        }

        return $this->success($data);
    }

    /**
     * 通过给定时间获取预约数据
     * @param $param
     */
    public function getReserveDataByDays($param)
    {
        $days_data = $param[ 'days_data' ] ?? [];
        $query_num = $param[ 'query_num' ] ?? 10;
        $site_id = $param[ 'site_id' ] ?? 0;

        $reserve_ids = [];
        foreach ($days_data as $key => $val) {
            $field = 'noy.reserve_id,noy.reserve_state,noy.reserve_time,nm.nickname';
            $list_data = $this->getReservePageList([
                [ 'noy.site_id', '=', $site_id ],
                [ 'noy.reserve_time', 'between', [ $val[ 'start_time' ], $val[ 'end_time' ] ] ]
            ], 1, $query_num, 'noy.create_time desc', $field)[ 'data' ];
            $reserve_ids = array_merge($reserve_ids, array_column($list_data[ 'list' ], 'reserve_id'));
            $days_data[ $key ][ 'data' ] = $list_data;
        }

        //查询所有预约项 并按照预约分配数据
        $reserve_item_list = $this->getReserveItemList([
            [ 'oyi.reserve_id', 'in', $reserve_ids ],
        ], 'oyi.reserve_id,g.goods_name,g.goods_id,g.sku_id', 'reserve_item_id desc', 'oyi',
            [ [ 'goods g', 'g.sku_id = oyi.reserve_goods_sku_id', 'right' ] ])[ 'data' ];
        $reserve_data = [];
        foreach ($reserve_item_list as $val) {
            if (!isset($reserve_data[ $val[ 'reserve_id' ] ])) {
                $reserve_data[ $val[ 'reserve_id' ] ] = [];
            }
            $reserve_data[ $val[ 'reserve_id' ] ][] = $val;
        }

        //预约项关联预约
        foreach ($days_data as $key => $val) {
            foreach ($val[ 'data' ][ 'list' ] as $k => $item) {
                $days_data[ $key ][ 'data' ][ 'list' ][ $k ][ 'item' ] = $reserve_data[ $item[ 'reserve_id' ] ] ?? [];
            }
        }

        return $this->success($days_data);
    }
}
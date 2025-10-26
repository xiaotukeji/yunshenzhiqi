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

namespace addon\store\shop\controller;

use addon\store\model\Reserve as ReserveModel;
use app\dict\goods\GoodsDict;
use app\model\store\Store as StoreModel;
use app\model\goods\Goods as GoodsModel;
use app\model\system\User;
use app\model\system\UserGroup;
use app\shop\controller\BaseShop;
use think\App;


class Reserve extends BaseShop
{

    public function __construct(App $app = null)
    {
        $this->replace = [
            'ADDON_STORE_CSS' => __ROOT__ . '/addon/store/shop/view/public/css',
            'ADDON_STORE_JS' => __ROOT__ . '/addon/store/shop/view/public/js',
            'ADDON_STORE_IMG' => __ROOT__ . '/addon/store/shop/view/public/img',
        ];
        parent::__construct($app);
    }

    /**
     * 预约看板
     * @return mixed
     */
    public function index()
    {
        $this->assign('reserve_state', ( new ReserveModel )->reserve_state);

        $data = $this->getReserveByWeek();
        $this->assign('data', $data[ 'data' ]);
        $store_model = new StoreModel();
        $store_list = $store_model->getStoreList([ [ 'site_id', '=', $this->site_id ], [ 'status', '=', 1 ] ])[ 'data' ] ?? [];
        $this->assign('store_list', $store_list);
        return $this->fetch('reserve/index');
    }

    /**
     * 获取一周内的时间段
     * @param $length
     * @return array
     */
    public function getWeekDay($length = 0)
    {
        $first_day = mktime(0, 0, 0, date("m"), date("d") - date("w") + 1, date("Y"));
        $first_day = strtotime($length . ' week', $first_day);

        $week = [ '周日', '周一', '周二', '周三', '周四', '周五', '周六' ];
        $data = [];
        for ($i = 0; $i < 7; $i++) {
            $time = strtotime("+ {$i} day", $first_day);
            $data[] = [
                'start' => $time,
                'end' => strtotime(date('Y-m-d 23:59:59', $time)),
                'date' => date('m/d', $time),
                'w' => date('w', $time),
                'week' => $week[date('w', $time)],
                'currday' => date('Y-m-d', $time) == date('Y-m-d') ? 1 : 0
            ];
        }
        return success(0, '', $data);
    }

    public function getMonthDays()
    {
        if (request()->isJson()) {
            $reserve_model = new ReserveModel();
            $year = input('year', '');
            $month = input('month', '');
            $days_data = $reserve_model->getMonthDays($year, $month)[ 'data' ];
            return success(0, '', $days_data);
        }
    }

    public function getReserveByWeek()
    {
        $reserve_model = new ReserveModel();

        $length = input('length', 0);
        $data = $this->getWeekDay($length)[ 'data' ];
        foreach ($data as $wk => $w_item) {
            $field = 'noy.reserve_id,noy.reserve_state,noy.reserve_time,nm.nickname,noy.reserve_item';
            $list = $reserve_model->getReservePageList([
                [ 'noy.site_id', '=', $this->site_id ],
                [ 'noy.reserve_time', 'between', [ $w_item[ 'start' ], $w_item[ 'end' ] ] ]
            ], 1, PAGE_LIST_ROWS, 'noy.create_time desc', $field);
            $data[ $wk ][ 'data' ] = $list[ 'data' ];
        }

        return success(0, '', $data);
    }

    /**
     * 获取预约周数据
     * @return array
     */
    public function getReserveWeekData()
    {
        if (request()->isJson()) {
            $reserve_model = new ReserveModel();
            $week_offset = input('week_offset', 0);
            $days_data = $reserve_model->getWeekDays($week_offset)[ 'data' ];
            $res = $reserve_model->getReserveDataByDays([
                'days_data' => $days_data,
                'query_num' => 4,
                'site_id' => $this->site_id,
            ]);
            return $res;
        }
    }

    /**
     * 获取预约月数据
     * @return array
     */
    public function getYuYueMonthData()
    {
        if (request()->isJson()) {
            $reserve_model = new ReserveModel();
            $year = input('year', date('Y'));
            $month = input('year', date('m'));
            $days_data = $reserve_model->getMonthDays($year, $month)[ 'data' ];
            $res = $reserve_model->getReserveDataByDays([
                'days_data' => $days_data,
                'query_num' => 3,
                'site_id' => $this->site_id,
            ]);
            return $res;
        }
    }

    /**
     * 添加预约
     * @return mixed
     */
    public function addReserve()
    {
        $reserve_model = new ReserveModel();
        if (request()->isJson()) {
            return $reserve_model->addReserve([
                'site_id' => $this->site_id,
                'app_module' => $this->app_module,
                'member_id' => input('member_id'),
                'goods' => json_decode(input('goods'), true),
                'store_id' => input('store_id'),
                'date' => input('date'),
                'time' => input('time'),
                'remark' => input('remark', ''),
                'source' => 'store'
            ]);
        }

        $service_model = new GoodsModel();
        $condition = [
            [ 'g.site_id', '=', $this->site_id ],
            [ 'g.goods_state', '=', 1 ],
            [ 'g.goods_class', '=', GoodsDict::service ]
        ];
        $service = $service_model->getGoodsList($condition, 'g.goods_id,g.sku_id,g.goods_name,g.price,sku.service_length', 'g.create_time desc'
            , 0, 'g', [
                [ 'goods_sku sku', 'sku.goods_id=g.goods_id', 'inner' ]
            ]);
        $this->assign('service', $service[ 'data' ]);

        $store_model = new StoreModel();
        $store_list = $store_model->getStoreList([ [ 'site_id', '=', $this->site_id ], [ 'status', '=', 1 ] ])[ 'data' ] ?? [];
        $this->assign('store_list', $store_list);

        $config = $reserve_model->getReserveConfig($this->site_id, $store_list[ 0 ][ 'store_id' ]);
        $this->assign('config', $config[ 'data' ][ 'value' ]);

        $user_model = new User();
        $user_list = $user_model->getUserList([ [ 'site_id', '=', $this->site_id ], [ 'app_module', '=', 'servicer' ], [ 'status', '=', 1 ] ])[ 'data' ] ?? [];
        $this->assign('user_list', $user_list);

        return $this->fetch('reserve/add_reserve');
    }

    /**
     * 修改预约
     * @return mixed|void
     */
    public function updateReserve()
    {
        $model = new ReserveModel();

        if (request()->isJson()) {
            $reserve_id = input('reserve_id', 0);
            return $model->editReserve([
                'site_id' => $this->site_id,
                'app_module' => $this->app_module,
                'reserve_id' => $reserve_id,
                'store_id' => input('store_id'),
                'goods' => json_decode(input('goods'), true),
                'date' => input('date'),
                'time' => input('time'),
                'remark' => input('remark', '')
            ]);
        } else {
            $reserve_id = input('id', 0);
            // 查询预约信息
            $info = $model->getReserveInfo([
                [ 'reserve_id', '=', $reserve_id ],
                [ 'oy.site_id', '=', $this->site_id ]
            ], 'oy.*, nm.headimg, nm.nickname, nm.mobile, os.store_name', 'oy', [
                [ 'member nm', 'oy.member_id = nm.member_id', 'left' ],
                [ 'store os', 'oy.store_id = os.store_id', 'left' ]
            ])[ 'data' ];

            if (empty($info)) {
                $this->error('未获取到预约信息');
                return;
            }
            $info[ 'item' ] = $model->getReserveItemList([
                [
                    'oyi.reserve_id', '=', $reserve_id
                ],

            ], 'g.goods_name,sku.service_length,g.goods_id,g.sku_id,g.price,ys.username,oyi.reserve_user_id', 'reserve_item_id desc', 'oyi',
                [
                    [ 'goods g', 'g.sku_id = oyi.reserve_goods_sku_id', 'right' ],
                    [ 'goods_sku sku', 'sku.sku_id = oyi.reserve_goods_sku_id', 'right' ],
                    [ 'user ys', 'oyi.reserve_user_id = ys.uid', 'left' ]
                ])[ 'data' ];

            $this->assign('info', $info);

            // 查询预约配置
            $config = $model->getReserveConfig($this->site_id, $info[ 'store_id' ]);
            $this->assign('config', $config[ 'data' ][ 'value' ]);

            // 查询可预约服务
            $service_model = new GoodsModel();
            $condition = [
                [ 'g.site_id', '=', $this->site_id ],
                [ 'g.goods_state', '=', 1 ],
                [ 'g.goods_class', '=', GoodsDict::service ]
            ];
            $service = $service_model->getGoodsList($condition, 'g.goods_id,g.sku_id,g.goods_name,g.price,sku.service_length', 'g.create_time desc'
                , 0, 'g', [
                    [ 'goods_sku sku', 'sku.goods_id=g.goods_id', 'inner' ]
                ]);
            $this->assign('service', $service[ 'data' ]);

            $store_model = new StoreModel();
            $store_list = $store_model->getStoreList([ [ 'site_id', '=', $this->site_id ], [ 'status', '=', 1 ] ])[ 'data' ] ?? [];
            $this->assign('store_list', $store_list);

            $user_model = new User();
            $user_list = $user_model->getUserList([ [ 'site_id', '=', $this->site_id ], [ 'app_module', '=', 'servicer' ], [ 'status', '=', 1 ] ])[ 'data' ] ?? [];
            $this->assign('user_list', $user_list);

            return $this->fetch('reserve/add_reserve');
        }
    }

    /**
     * 预约列表
     */
    public function lists()
    {
        $reserve_model = new ReserveModel();
        if (request()->isJson()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $search_text = input('search_text', '');
            $reserve_state = input('reserve_state', 'all');
            $start = input('start_time', 0);
            $end = input('end_time', 0);
            $store_id = !empty(input('store_id', 0)) ? input('store_id', 0) : $this->store_id;

            $condition = [
                [ 'noy.site_id', '=', $this->site_id ]
            ];
            if ($reserve_state != 'all') {
                $condition[] = [ 'noy.reserve_state', '=', $reserve_state ];
            }
            if (!empty($search_text)) {
                $condition[] = [ 'nm.mobile|nm.nickname', 'like', '%' . $search_text . '%' ];
            }
            if (!empty($store_id)) {
                $condition[] = [ 'noy.store_id', 'in', $store_id ];
            }
            if ($start && $end) {
                $condition[] = [ 'noy.reserve_time', 'between', [ $start, $end ] ];
            } else {
                if ($start && !$end) {
                    $condition[] = [ 'noy.reserve_time', '>=', $start ];
                } else {
                    if (!$start && $end) {
                        $condition[] = [ 'noy.reserve_time', '<=', $end ];
                    }
                }
            }

            $field = 'noy.store_id, noy.member_id, noy.remark, noy.reserve_id, noy.reserve_name, noy.reserve_state_name, noy.reserve_state, noy.reserve_time, noy.reserve_item, noy.create_time, noy.source, nm.headimg, nm.nickname, nm.mobile, os.store_name';
            $result = $reserve_model->getReservePageList($condition, $page, $page_size, 'noy.create_time desc', $field);
            return $result;
        } else {
            $this->assign('reserve_state', $reserve_model->reserve_state);

            $start_time = input('start_time', '');
            $end_time = input('end_time', '');
            $this->assign('start_time', $start_time);
            $this->assign('end_time', $end_time);

            $store_model = new StoreModel();
            $store_list = $store_model->getStoreList([ [ 'site_id', '=', $this->site_id ], [ 'status', '=', 1 ] ])[ 'data' ] ?? [];

            $this->assign('store_list', $store_list);
            return $this->fetch('reserve/lists');
        }
    }

    public function servicerList()
    {
        if (request()->isJson()) {
            $store_id = !empty(input('store_id', 0)) ? input('store_id', 0) : $this->store_id;
            $condition = [
                [ 'u.site_id', '=', $this->site_id ],
            ];
            $condition[] = [ 'ug.store_id', '=', $store_id ];

            $user_model = new UserGroup();
            $result = $user_model->getUserList($condition, 'u.username,u.status,u.uid,u.group_name', 'u.uid desc', 'ug', [
                [ 'user u', 'ug.uid=u.uid', 'left' ]
            ]);
            return $result;
        }
    }

    /**
     * 预约设置
     * @return mixed
     */
    public function getConfig()
    {
        if (request()->isJson()) {
            $model = new ReserveModel();
            $store_id = input('store_id', 0);
            $config = $model->getReserveConfig($this->site_id, $store_id);
            return $config;
        }
    }

    /**
     * 确认预约
     * @return array
     */
    public function confirm()
    {
        if (request()->isJson()) {
            $reserve_id = input('reserve_id', 0);
            $reserve_model = new ReserveModel();
            return $reserve_model->confirmReserve($reserve_id, $this->site_id);
        }
    }

    /**
     * 取消预约
     * @return array
     */
    public function cancel()
    {
        if (request()->isJson()) {
            $reserve_id = input('reserve_id', 0);
            $reserve_model = new ReserveModel();
            return $reserve_model->cancelReserve($reserve_id, $this->site_id);
        }
    }

    /**
     * 删除预约
     * @return array
     */
    public function deleteReserve()
    {
        if (request()->isJson()) {
            $reserve_id = input('reserve_id', 0);
            $reserve_model = new ReserveModel();
            return $reserve_model->deleteReserve($reserve_id, $this->site_id);
        }
    }

    /**
     * 确认到店
     * @return array
     */
    public function confirmToStore()
    {
        if (request()->isJson()) {
            $reserve_id = input('reserve_id', 0);
            $reserve_model = new ReserveModel();
            return $reserve_model->confirmToStore($reserve_id, $this->site_id);
        }
    }

    /**
     * 确认完成
     * @return array
     */
    public function complete()
    {
        if (request()->isJson()) {
            $reserve_id = input('reserve_id', 0);
            $reserve_model = new ReserveModel();
            return $reserve_model->confirmComplete($reserve_id, $this->site_id);
        }
    }

    /**
     * 预约详情
     * @return mixed|void
     */
    public function detail()
    {
        $reserve_id = input('id', 0);

        $model = new ReserveModel();

        $info = $model->getReserveInfo([
            [ 'reserve_id', '=', $reserve_id ],
            [ 'oy.site_id', '=', $this->site_id ]
        ], 'oy.*, nm.headimg, nm.nickname, nm.mobile,os.store_name', 'oy', [
            [ 'member nm', 'oy.member_id = nm.member_id', 'left' ],
            [ 'store os', 'oy.store_id = os.store_id', 'left' ]
        ])[ 'data' ];

        if (empty($info)) {
            $this->error('未获取到预约信息');
            return;
        }

        $info[ 'item' ] = $model->getReserveItemList([
            [
                'oyi.reserve_id', '=', $reserve_id
            ]
        ], 'g.goods_name,sku.service_length,g.goods_id,g.sku_id,g.price,ys.username,oyi.reserve_user_id', 'reserve_item_id desc', 'oyi',
            [
                [ 'goods g', 'g.sku_id = oyi.reserve_goods_sku_id', 'right' ],
                [ 'goods_sku sku', 'sku.sku_id = oyi.reserve_goods_sku_id', 'right' ],
                [ 'user ys', 'oyi.reserve_user_id = ys.uid', 'left' ]
            ])[ 'data' ];

        $this->assign('info', $info);
        return $this->fetch('reserve/detail');
    }
}
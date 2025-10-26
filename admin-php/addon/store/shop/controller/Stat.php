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

use app\model\store\Stat as StatModel;
use app\model\store\Store as StoreModel;
use app\shop\controller\BaseShop;
use Carbon\Carbon;


class Stat extends BaseShop
{
    /**
     * 门店统计数据
     */
    public function store()
    {
        $store_list = ( new StoreModel() )->getStoreList([ [ 'site_id', '=', $this->site_id ] ], 'store_id,store_name');
        $this->assign('store_list', $store_list[ 'data' ]);
        $this->assign('today', Carbon::today()->toDateString());
        $this->assign('yesterday', Carbon::yesterday()->toDateString());
        return $this->fetch('stat/store');
    }

    /**
     * 统计数据总和
     */
    public function statTotal()
    {
        if (request()->isJson()) {
            $store_id = input('store_id', 0);
            $start_time = $end_time = input('start_time', strtotime(date('Y-m-d', time())));
            $end_time = input('end_time', time());

            if ($start_time > $end_time) {
                $start_time = input('end_time');
                $end_time = input('start_time');
            }

            $stat_model = new StatModel();

            $data = $stat_model->getShopStatSum($this->site_id, $start_time, $end_time, $store_id);
            return $data;
        }
    }

    /**
     * 获取天统计趋势数据
     */
    public function dayStatData()
    {
        if (request()->isJson()) {
            $store_id = input('store_id', 0);
            $start_time = input('start_time', strtotime(date('Y-m-d', strtotime('-6 day'))));
            $end_time = input('end_time', time());

            if ($start_time > $end_time) {
                $start_time = input('end_time');
                $end_time = input('start_time');
            }

            $stat_model = new StatModel();
            $fields = $stat_model->getStatField();
            $fields[] = 'expected_earnings_total_money';

            $stat_list = $stat_model->getShopStatList($this->site_id, $start_time, $end_time, $store_id)[ 'data' ];
            $temp_stat_list = [];
            foreach ($stat_list as $v){
                $temp_day = date('Y-m-d', $v[ 'day_time' ]);
                $v['day_time'] = $temp_day;
                $temp_stat_list[$temp_day][] = $v;
            }
//            $stat_list = array_map(function($item) {
//                $item[ 'day_time' ] = date('Y-m-d', $item[ 'day_time' ]);
//                return $item;
//            }, $stat_list);
//            $stat_list = array_column($stat_list, null, 'day_time');

            $day = ceil(( $end_time - $start_time ) / 86400);

            foreach ($fields as $field) {
                $value = [];
                $time = [];
                for ($i = 0; $i < $day; $i++) {
                    $date = date('Y-m-d', $start_time + $i * 86400);
                    $time[] = $date;
                    $temp_day_stat_list = $temp_stat_list[$date] ?? [];
                    $field_value = 0;
                    if(!empty($temp_day_stat_list)){
                        foreach($temp_day_stat_list as $temp_v){
                            $field_value += $temp_v ? $temp_v[ $field ] : 0;
                        }
                    }
                    $value[] += $field_value;
                }
                $data[ $field ] = $value;
                $data[ 'time' ] = $time;
            }
            return success(0, '', $data);
        }
    }

    /**
     * 获取小时统计趋势数据
     */
    public function hourStatData()
    {
        if (request()->isJson()) {
            $time = input('start_time', time());
            $store_id = input('store_id', 0);
            $carbon = Carbon::createFromTimestamp($time);

            $stat_model = new StatModel();
            $fields = $stat_model->getStatHourField();
            $fields[] = 'expected_earnings_total_money';

            $stat_list = $stat_model->getShopStatHourList($this->site_id, $carbon->year, $carbon->month, $carbon->day, $store_id)[ 'data' ];

            $data = [];
            $empty = array_map(function() { return 0; }, range(0, 23, 1));
            if (!empty($stat_list)) {
//                $stat_list = array_column($stat_list, null, 'hour');
                $temp_stat_list = [];
                foreach($stat_list as $v){
                    $temp_stat_list[$v['hour']][] = $v;
                }
                foreach ($fields as $field) {
                    $value = [];
                    for ($i = 0; $i < 24; $i++) {
                        $value[ $i ] = 0;
                        $temp_item_stat_list = $temp_stat_list[ $i ] ?? [];
                        if(!empty($temp_item_stat_list)){
                            foreach($temp_item_stat_list as $temp_v){
                                $value[ $i ] += $temp_v ? $temp_v[ $field ] : 0;
                            }
                        }
                    }
                    $data[ $field ] = $value;
                }
            } else {
                foreach ($fields as $field) {
                    $data[ $field ] = $empty;
                }
            }
            $data[ 'time' ] = array_map(function($value) { return $value . '时'; }, range(0, 23, 1));
            return success(0, '', $data);
        }
    }
}
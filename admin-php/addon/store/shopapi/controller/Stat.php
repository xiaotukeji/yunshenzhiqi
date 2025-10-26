<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 上海牛之云网络科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\store\shopapi\controller;

use addon\store\model\Settlement as SettlementModel;
use app\model\order\OrderCommon as OrderCommonModel;
use app\model\store\Stat as StatModel;
use app\shopapi\controller\BaseApi;
use Carbon\Carbon;

/**
 * 门店结算控制器
 */
class Stat extends BaseApi
{

    public function __construct()
    {
        //执行父类构造函数
        parent::__construct();

        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) {
            echo $this->response($token);
            exit;
        }
    }

    /**
     * 统计数据总和
     */
    public function getStatTotal()
    {
        $store_id = $this->params[ 'store_id' ] ?? 0;
        $start_time = $this->params[ 'start_time' ] ?? strtotime(date('Y-m-d', time()));
        $end_time = $this->params[ 'end_time' ] ?? time();

        if ($start_time > $end_time) {
            $start_time = $this->params[ 'end_time' ];
            $end_time = $this->params[ 'start_time' ];
        }

        $stat_model = new StatModel();

        $data = $stat_model->getShopStatSum($this->site_id, $start_time, $end_time, $store_id);
        return $this->response($data);
    }

    /**
     * 获取天统计趋势数据
     */
    public function getStatData()
    {
        $store_id = $this->params[ 'store_id' ] ?? 0;
        $start_time = $this->params[ 'start_time' ] ?? strtotime(date('Y-m-d', strtotime('-6 day')));
        $end_time = $this->params[ 'end_time' ] ?? time();

        if ($start_time > $end_time) {
            $start_time = $this->params[ 'end_time' ];
            $end_time = $this->params[ 'start_time' ];
        }

        $stat_model = new StatModel();
        $fields = $stat_model->getStatField();
        $fields[] = 'expected_earnings_total_money';

        $stat_list = $stat_model->getShopStatList($this->site_id, $start_time, $end_time, $store_id)[ 'data' ];
        $stat_list = array_map(function($item) {
            $item[ 'day_time' ] = date('Y-m-d', $item[ 'day_time' ]);
            return $item;
        }, $stat_list);
        $stat_list = array_column($stat_list, null, 'day_time');

        $day = ceil(( $end_time - $start_time ) / 86400);

        foreach ($fields as $field) {
            $value = [];
            $time = [];
            for ($i = 0; $i < $day; $i++) {
                $date = date('Y-m-d', $start_time + $i * 86400);
                $time[] = $date;
                $value[] = isset($stat_list[ $date ]) ? $stat_list[ $date ][ $field ] : 0;
            }
            $data[ $field ] = $value;
            $data[ 'time' ] = $time;
        }
        return $this->response($this->success($data));
    }

    /**
     * 获取小时统计趋势数据
     */
    public function getStatHourData()
    {
        $store_id = $this->params[ 'store_id' ] ?? 0;
        $time = $this->params[ 'start_time' ] ?? time();
        $carbon = Carbon::createFromTimestamp($time);

        $stat_model = new StatModel();
        $fields = $stat_model->getStatHourField();
        $fields[] = 'expected_earnings_total_money';

        $stat_list = $stat_model->getShopStatHourList($this->site_id, $carbon->year, $carbon->month, $carbon->day, $store_id)[ 'data' ];

        $data = [];
        $empty = array_map(function() { return 0; }, range(0, 23, 1));
        if (!empty($stat_list)) {
            $stat_list = array_column($stat_list, null, 'hour');
            foreach ($fields as $field) {
                $value = [];
                for ($i = 0; $i < 24; $i++) {
                    $value[ $i ] = isset($stat_list[ $i ]) ? $stat_list[ $i ][ $field ] : 0;
                }
                $data[ $field ] = $value;
            }
        } else {
            foreach ($fields as $field) {
                $data[ $field ] = $empty;
            }
        }
        $data[ 'time' ] = array_map(function($value) { return $value . '时'; }, range(0, 23, 1));
        return $this->response($this->success($data));
    }
}
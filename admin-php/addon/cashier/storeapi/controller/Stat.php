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

use app\model\store\Stat as StatModel;
use app\storeapi\controller\BaseStoreApi;
use Carbon\Carbon;

/**
 * 数据统计
 * Class Stat
 * @package addon\shop\siteapi\controller
 */
class Stat extends BaseStoreApi
{
    /**
     * 统计数据总和
     */
    public function statTotal()
    {
        $start_time = $this->params['start_time'] ?? strtotime(date('Y-m-d', time()));
        $end_time = $this->params['end_time'] ?? time();

        if ($start_time > $end_time) {
            $start_time = $this->params['end_time'];
            $end_time = $this->params['start_time'];
        }

        $stat_model = new StatModel();
        $data = $stat_model->getShopStatSum($this->site_id, $start_time, $end_time, $this->store_id);
        return $this->response($data);
    }

    /**
     * 获取天统计趋势数据
     */
    public function dayStatData()
    {
        $start_time = $this->params['start_time'] ?? strtotime(date('Y-m-d', strtotime('-6 day')));
        $end_time = $this->params['end_time'] ?? time();

        if ($start_time > $end_time) {
            $start_time = $this->params['end_time'];
            $end_time = $this->params['start_time'];
        }

        $stat_model = new StatModel();
        $fields = $stat_model->getStatField();
        $fields[] = 'expected_earnings_total_money';

        $stat_list = $stat_model->getShopStatList($this->site_id, $start_time, $end_time, $this->store_id)['data'];
        $stat_list = array_map(function ($item) {
            $item['day_time'] = date('Y-m-d', $item['day_time']);
            return $item;
        }, $stat_list);
        $stat_list = array_column($stat_list, null, 'day_time');

        $day = ceil(($end_time - $start_time) / 86400);

        foreach ($fields as $field) {
            $value = [];
            $time = [];
            for ($i = 0; $i < $day; $i++) {
                $date = date('Y-m-d', $start_time + $i * 86400);
                $time[] = $date;
                $value[] = isset($stat_list[$date]) ? $stat_list[$date][$field] : 0;
            }
            $data[$field] = $value;
            $data['time'] = $time;
        }
        return $this->response($this->success($data));
    }

    /**
     * 获取小时统计趋势数据
     */
    public function hourStatData()
    {
        $time = $this->params['start_time'] ?? time();
        $carbon = Carbon::createFromTimestamp($time);

        $stat_model = new StatModel();
        $fields = $stat_model->getStatHourField();
        $fields[] = 'expected_earnings_total_money';

        $stat_list = $stat_model->getShopStatHourList($this->site_id, $carbon->year, $carbon->month, $carbon->day, $this->store_id)['data'];

        $data = [];
        $empty = array_map(function () {
            return 0;
        }, range(0, 23, 1));
        if (!empty($stat_list)) {
            $stat_list = array_column($stat_list, null, 'hour');
            foreach ($fields as $field) {
                $value = [];
                for ($i = 0; $i < 24; $i++) {
                    $value[$i] = isset($stat_list[$i]) ? $stat_list[$i][$field] : 0;
                }
                $data[$field] = $value;
            }
        } else {
            foreach ($fields as $field) {
                $data[$field] = $empty;
            }
        }
        $data['time'] = array_map(function ($value) {
            return $value . '时';
        }, range(0, 23, 1));
        return $this->response($this->success($data));
    }
}
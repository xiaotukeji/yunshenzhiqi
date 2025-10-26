<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */
namespace addon\weapp\shop\controller;

use addon\weapp\model\Stat as StatModel;
use app\shop\controller\BaseShop;
use think\App;

/**
 * 微信小程序访问统计
 */
class Stat extends BaseShop
{
    public function __construct(App $app = null)
    {
        $this->replace = [
            'WEAPP_CSS' => __ROOT__ . '/addon/weapp/shop/view/public/css',
            'WEAPP_JS'  => __ROOT__ . '/addon/weapp/shop/view/public/js',
            'WEAPP_IMG' => __ROOT__ . '/addon/weapp/shop/view/public/img',
            'WEAPP_SVG' => __ROOT__ . '/addon/weapp/shop/view/public/svg',
        ];
        parent::__construct($app);
    }

    public function stat()
    {
        return $this->fetch('stat/stat');
    }

    /**
     * 统计昨日的数据
     */
    public function visitData()
    {
        if (request()->isJson()) {
            $date_type  = input("date_type", "month");
            $stat_model = new StatModel();
            $result     = $stat_model->visitData($date_type);
            return $result;
        }
    }

    /**
     * 获取微信小程序  数据分析统计
     */
    public function visitStatistics()
    {

        $stat_model = new StatModel();
        $daterange  = input("daterange", "");
        $result     = $stat_model->visitStatistics($daterange);
        return $result;
    }

    /**
     * 得到时间间隔
     * @param $date_type
     * @param string $daterange
     * @return array
     */
    public function getDaterange($date_type, $daterange = "")
    {
        $today_date = date('Ymd');//当前日日期
        $begin_date = "";
        $end_date   = "";

        switch ($date_type) {
            case 'today':
                $begin_date = $today_date;
                $end_date   = $today_date;
                break;
            case 'yesterday':
                $begin_date = date('Ymd', strtotime('-1 days'));
                $end_date   = date('Ymd', strtotime('-1 days'));
                break;
            case 'week':
                $begin_date = date('Ymd', strtotime('-6 days'));
                $end_date   = $today_date;
                break;
            case 'month':
                $begin_date = date('Ymd', strtotime('-29 days'));
                $end_date   = $today_date;
                break;
            case 'daterange':
                if (!empty($daterange)) {
                    $daterange_array = explode(" - ", $daterange);
                    $begin_date      = date_format(date_create($daterange_array[0]), "Ymd");
                    $end_date        = date_format(date_create($daterange_array[1]), "Ymd");
                }
                $begin_date = date('Ymd', strtotime($begin_date));//开始日期
                $end_date   = date('Ymd', strtotime($end_date));//结束日期
                break;
        }

        return array("begin_date" => $begin_date, "end_date" => $end_date);
    }
}
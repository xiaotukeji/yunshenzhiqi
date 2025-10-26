<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\store\event;

use addon\store\model\Settlement;
use Carbon\Carbon;

/**
 * 门店结算
 */
class StoreWithdrawPeriodCalc
{
    public function handle($params)
    {
        $model = new Settlement();
        $time = Carbon::today()->timestamp;
        $res = $model->settlement($params[ 'relate_id' ], $time);

        return $res;
    }
}
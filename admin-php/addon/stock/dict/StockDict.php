<?php

/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\stock\dict;


/**
 * 库存公共属性
 */
class StockDict
{
    const input = 'input';
    const output = 'output';

    /**
     * 出入库类型
     * @param $type
     * @return string|string[]
     */
    public static function getType($type = ''){
        $list = [
            self::input => '入库',
            self::output => '出库',
        ];

        if($type) return $list[$type] ?? '';
        return $list;
    }


}

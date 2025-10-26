<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace addon\cashier\event;

/**
 * 收银交班打印
 */
class PrinterTemplateType
{

    public function handle($data)
    {
       return [
           [
               'type' => 'change_shifts',
               'type_name' => '收银交班',
               'edit' => 'addon/cashier/shop/view/printer/change_shifts.html',
               'add' => 'addon/cashier/shop/view/printer/change_shifts.html',
           ]
       ];
    }
}
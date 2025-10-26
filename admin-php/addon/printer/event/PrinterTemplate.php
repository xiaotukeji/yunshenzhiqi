<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\printer\event;

use addon\printer\model\PrinterTemplate as PrinterTemplateModel;
use app\Controller;

/**
 * 模板
 */
class PrinterTemplate extends Controller
{

    public function handle($params)
    {
        $model = new PrinterTemplateModel();
        $action_type = $params[ 'action' ] ?? 'add';
        return $this->fetch($model->getTemplateType()[ $params[ 'type' ] ][ $action_type ]);
    }
}
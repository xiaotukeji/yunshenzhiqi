<?php

namespace addon\store\component\controller;

use app\component\controller\BaseDiyView;

/**
 * 门店标签·组件
 *
 */
class StoreLabel extends BaseDiyView
{

    /**
     * 设计界面
     */
    public function design()
    {
        return $this->fetch("store_label/design.html");
    }
}
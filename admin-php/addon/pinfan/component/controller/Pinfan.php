<?php

namespace addon\pinfan\component\controller;

use app\component\controller\BaseDiyView;

/**
 * 拼团模块·组件
 *
 */
class Pinfan extends BaseDiyView
{

    /**
     * 设计界面
     */
    public function design()
    {
        return $this->fetch("pinfan/design.html");
    }
}
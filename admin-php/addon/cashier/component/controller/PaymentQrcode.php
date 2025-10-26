<?php

namespace addon\cashier\component\controller;

use app\component\controller\BaseDiyView;

/**
 * 付款码·组件
 */
class PaymentQrcode extends BaseDiyView
{
    /**
     * 后台编辑界面
     */
    public function design()
    {

        return $this->fetch("payment_qrcode/design.html");
    }
}
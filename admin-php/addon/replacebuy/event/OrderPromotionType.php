<?php

namespace addon\replacebuy\event;

//营销类型
class OrderPromotionType
{
    public function handle()
    {
        return [ 'name' => '代客下单', 'type' => 'replacebuy' ];
    }

}
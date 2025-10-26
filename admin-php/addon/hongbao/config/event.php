<?php
// 事件定义文件
return [
    'bind' => [

    ],

    'listen' => [
        //展示活动
        'ShowPromotion' => [
            'addon\hongbao\event\ShowPromotion',
        ],
        #关闭活动
        'CloseHongbao' => [
            'addon\hongbao\event\CloseHongbao',
        ],
        #更改瓜分活动状态
        'CronChangeHongbaoStatus' => [
            'addon\hongbao\event\CronChangeHongbaoStatus'
        ],
        #关闭到时的瓜分任务
        'HongbaoLaunchClose'=>[
          'addon\hongbao\event\HongbaoLaunchClose'
        ],
        #模拟瓜分
        'HongbaoSimulation'=>[
            'addon\hongbao\event\HongbaoSimulation'
        ]
    ],

    'subscribe' => [
    ],
];

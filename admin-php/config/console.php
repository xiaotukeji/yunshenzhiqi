<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
return [
    // 指令定义
    'commands' => [
        'queue:work' => 'think\queue\command\Work',
        'queue:restart' => 'think\queue\command\Restart',
        'queue:listen' => "think\queue\command\Listen"
    ],
];

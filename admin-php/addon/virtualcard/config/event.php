<?php
// 事件定义文件
return [
	'bind' => [
	
	],
	
	'listen' => [
        //展示活动
        'ShowPromotion' => [
            'addon\virtualcard\event\ShowPromotion',
        ],
        'GoodsClass' => [
            'addon\virtualcard\event\GoodsClass',
        ]
	],
	
	'subscribe' => [
	],
];

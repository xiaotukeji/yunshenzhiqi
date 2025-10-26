<?php

return [
    40001 => '获取 access_token 时 AppSecret 错误，或者 access_token 无效。请认真比对 AppSecret 的正确性，或查看是否正在为恰当的公众号调用接口',
    40002 => '不合法的凭证类型',
    40013 => '不合法的 AppID ，请检查 AppID 的正确性，避免异常字符，注意大小写',
    40014 => '不合法的 access_token ，请认真比对 access_token 的有效性',
    40029 => '无效的 oauth_code',
    40030 => '不合法的 refresh_token',
    40125 => '无效的appsecret',
    40132 => '微信号不合法',
    40164 => 'IP：' . request()->ip() . '未加入公众号ip白名单',
    41008 => '缺少 oauth code',
    41009 => '缺少 openid',
    42001 => 'access_token 超时，请检查 access_token 的有效期',
    42002 => 'refresh_token 超时',
    42003 => 'oauth_code 超时',
    43001 => '需要 GET 请求',
    43002 => '需要 POST 请求',
    43003 => '需要 HTTPS 请求',
    45011 => '频率限制，每个用户每分钟100次',// API 调用太频繁，请稍候再试
    48004 => 'api 接口被封禁，请登录 mp.weixin.qq.com 查看详情',
    48005 => 'api 禁止删除被自动回复和自定义菜单引用的素材',
    48006 => 'api 禁止清零调用次数，因为清零次数达到上限',
    40226 => '高风险等级用户，小程序登录拦截 。风险等级详见用户安全解方案',
];
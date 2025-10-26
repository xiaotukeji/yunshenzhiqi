import Vue from 'vue'

const Config = {
  // api请求地址 https://abc.com
  baseUrl: '{{$baseUrl}}',
  // 图片域名 https://abc.com
  imgDomain: '{{$imgDomain}}',
  // 前端域名 默认部署 https://abc.com/web 独立部署 https://abc.com
  webDomain: '{{$webDomain}}',
  // 腾讯地图key 后台设置->其他设置->地图配置
  mpKey: '{{$mpKey}}',
  // 客服 wss://abc.com/wss
  webSocket: '{{$webSocket}}',
  //本地端主动给服务器ping的时间, 0 则不开启 , 单位秒
  pingInterval: 1500,
  // 版本号
  version: '5.5.2'
}

Vue.prototype.$config = Config;

export default Config;

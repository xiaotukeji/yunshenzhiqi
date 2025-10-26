import http from "../utils/http"

/**
 * 获取微信二维码
 */
export function loginCode(params) {
  return http({
    url: "/wechat/api/wechat/logincode",
    data: params
  })
}

/**
 * 检测是否扫码
 */
export function checkLogin(params) {
  return http({
    url: "/api/login/checklogin",
    data: params
  })
}

/**
 * 微信登录绑定手机号
 */
export function wechatLogin(params) {
  return http({
    url: "/api/login/wechatLogin",
    data: params
  })
}

/**
 * 检测是否可以微信扫码登录
 */
export function isWechatLogin(params) {
  return http({
    url: "/api/config/init",
    data: params
  })
}

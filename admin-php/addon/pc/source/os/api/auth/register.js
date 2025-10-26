import http from "../../utils/http"

/**
 * 获取注册协议
 */
export function getRegisterAgreement(params) {
  return http({
    url: "/api/register/aggrement",
    data: params
  })
}

/**
 * 获取新人福利
 */
export function getRegisterReward(params) {
  return http({
    url: "/memberregister/api/Config/Config",
    data: params
  })
}

/**
 * 账号密码注册
 */
export function register(params) {
  return http({
    url: "/api/register/username",
    data: params
  })
}

/**
 * 手机号注册
 */
export function registerMobile(params) {
  return http({
    url: "/api/register/mobile",
    data: params
  })
}

/**
 * 获取注册短信动态码
 */
export function registerMobileCode(params) {
  return http({
    url: "/api/register/mobileCode",
    data: params
  })
}

/**
 * 注册配置
 */
export function registerConfig(params) {
  return http({
    url: "/api/register/config",
    data: params
  })
}

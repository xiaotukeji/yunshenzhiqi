import http from "../utils/http"

/**
 * 支付详情
 * @param {object} params
 */
export function getPayInfo(params) {
  return http({
    url: "/api/pay/info",
    data: params,
    forceLogin: true
  })
}

/**
 * 支付方式
 * @param {object} params
 */
export function getPayType(params) {
  return http({
    url: "/api/pay/type",
    data: params,
    forceLogin: true
  })
}

/**
 * 支付状态
 * @param {object} params
 */
export function checkPayStatus(params) {
  return http({
    url: "/api/pay/status",
    data: params,
    forceLogin: true
  })
}

/**
 * 支付状态
 * @param {object} params
 */
export function pay(params) {
  return http({
    url: "/api/pay/pay",
    data: params,
    forceLogin: true
  })
}

/**
 * 获取线下支付配置
 */
export function getOfflinepayConfig() {
  return http({
    url: "/offlinepay/api/pay/config",
  })
}

/**
 * 线下支付信息
 * @param {object} params
 */
export function getOfflinepayPayInfo(params) {
  return http({
    url: "/offlinepay/api/pay/info",
    data: params,
    forceLogin: true
  })
}

/**
 * 线下支付
 * @param {object} params
 */
export function offlinepay(params) {
  return http({
    url: "/offlinepay/api/pay/pay",
    data: params,
    forceLogin: true
  })
}



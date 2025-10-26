import http from "../utils/http"

/**
 * 优惠券类型列表
 * @param {object} params
 */
export function couponTypeList(params) {
  return http({
    url: "/coupon/api/coupon/typepagelists",
    data: params
  })
}

/**
 * 领取优惠券
 * @param {object} params
 */
export function couponReceive(params) {
  return http({
    url: "/coupon/api/coupon/receive",
    data: params,
    forceLogin: true
  })
}

export function goodsCoupon(params) {
  return http({
    url: "/coupon/api/coupon/goodsCoupon",
    data: params
  })
}

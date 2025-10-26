import http from "../utils/http"

/**
 * 商品列表
 * @param {object} params
 */
export function goodsPage(params) {
  return http({
    url: "/seckill/api/seckillgoods/page",
    data: params
  })
}

/**
 * 商品详情
 * @param {object} params
 */
export function goodsSkuDetail(params) {
  return http({
    url: "/seckill/api/seckillgoods/detail",
    data: params
  })
}

/**
 * 秒杀时间段
 * @param {object} params
 */
export function timeList(params) {
  return http({
    url: "/seckill/api/seckill/lists",
    data: params
  })
}

/**
 * 秒杀商品信息
 * @param { Object } params
 */
export function seckillGoodsInfo(params) {
  return http({
    url: "/seckill/api/seckillgoods/info",
    data: params
  })
}

/**
 * 获取订单初始化数据
 * @param {object} params
 */
export function payment(params) {
  return http({
    url: "/seckill/api/ordercreate/payment",
    data: params,
    forceLogin: true
  })
}

/**
 * 获取订单初始化数据
 * @param {object} params
 */
export function calculate(params) {
  return http({
    url: "/seckill/api/ordercreate/calculate",
    data: params,
    forceLogin: true
  })
}

/**
 * 获取秒杀商品详情评价权限
 */
export function evaluateConfig() {
  return http({
    url: "/api/goodsevaluate/config",
    data: {},
    forceLogin: true
  })
}

/**
 * 订单创建
 * @param {object} params
 */
export function orderCreate(params) {
  return http({
    url: "/seckill/api/ordercreate/create",
    data: params,
    forceLogin: true
  })
}

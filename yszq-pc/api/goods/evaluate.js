import http from "../../utils/http"

/**
 * 商品评价列表
 * @param {object} params
 */
export function goodsEvaluateList(params) {
  return http({
    url: "/api/goodsevaluate/page",
    data: params
  })
}

/**
 * 商品评价数量
 * @param {object} params
 */
export function goodsEvaluateCount(params) {
  return http({
    url: "/api/goodsevaluate/getgoodsevaluate",
    data: params
  })
}

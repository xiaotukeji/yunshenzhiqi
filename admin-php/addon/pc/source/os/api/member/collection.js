import http from "../../utils/http"

/**
 * 我的商品收藏
 */
export function goodsCollect(params) {
  return http({
    url: "/api/goodscollect/page",
    data: params,
    forceLogin: true
  })
}

/**
 * 取消商品收藏
 */
export function deleteGoods(params) {
  return http({
    url: "/api/goodscollect/delete",
    data: params,
    forceLogin: true
  })
}

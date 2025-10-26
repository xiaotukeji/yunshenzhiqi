import http from "../../utils/http"

/**
 * 获取商品分类树结构
 */
export function tree(params) {
  return http({
    url: "/api/goodscategory/tree",
    data: params
  })
}

/**
 * 获取商品分类信息
 * @param {Object} params 参数 category_id:1
 */
export function goodsCategoryInfo(params) {
  return http({
    url: "/api/goodscategory/info",
    data: params
  })
}

/**
 * 获取分类配置
 */
export function categoryConfig(params) {
  return http({
    url: "/api/config/categoryconfig",
    data: params
  })
}

/**
 * 获取商品分类列表
 * @param {Object} params 参数 level:1
 */
export function goodsCategoryList(params) {
  return http({
    url: "/api/goodscategory/lists",
    data: params
  })
}

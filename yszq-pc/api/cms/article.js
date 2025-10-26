import http from "../../utils/http"

/**
 * 获取文章列表
 */
export function getArticleList(params) {
  return http({
    url: "/api/article/page",
    data: params
  })
}

/**
 * 获取文章分类列表
 */
export function articleCategoryList(params) {
  return http({
    url: "/api/article/category",
    data: params
  })
}

/**
 * 获取文章详情
 */
export function articleDetail(params) {
  return http({
    url: "/api/article/info",
    data: params
  })
}

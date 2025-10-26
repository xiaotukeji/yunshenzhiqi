import request from '@/common/js/http'

/**
 * 获取商品核销统计
 * @param  goods_id
 */
export function getGoodsVerifyById(goods_id) {
	return request.post('/shopapi/goods/verify', {data: {goods_id}})
}

/**
 * 获取商品核销列表
 * @param {Object} params
 */
export function getGoodsVerifyListById(params) {
	return request.post('/shopapi/goods/virtualgoodslist', {data: params})
}
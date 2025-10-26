import request from '@/common/js/http'

/**
 * 获取
 * @param {Object} params
 */
export function getShopWithdrawList(params) {
	return request.post('/shopapi/shopwithdraw/lists', {data: params})
}

/**
 * 编辑店铺联系方式
 * @param {Object} params
 */
export function getShopContact(params) {
	return request.post('/shopapi/shop/contact', {data: params})
}
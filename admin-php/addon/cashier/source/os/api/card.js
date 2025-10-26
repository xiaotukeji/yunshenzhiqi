import request from '@/common/js/http'

/**
 * 获取卡项列表(分页)
 * @param {Object} params
 */
export function getCardList(params) {
	return request.post('/cashier/storeapi/card/page', {data: params})
}

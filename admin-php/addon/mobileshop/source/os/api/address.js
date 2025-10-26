import request from '@/common/js/http'

/**
 * 获取详细地址
 * @param {Object} params
 */
export function getAddressInfo(params) {
	return request.post('/api/memberaddress/tranAddressInfo', {data: params})
}

/**
 * 编辑详细地址
 * @param {Object} params
 */
export function editAddress(params) {
	return request.post('/shopapi/order/editAddress', {data: params})
}
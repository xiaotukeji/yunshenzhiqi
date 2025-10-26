import request from '@/common/js/http'

/**
 * 获取核销码信息
 * @param {Object} code
 */
export function getVerifyInfo(code) {
	return request.post('/cashier/storeapi/verify/info', {data: {code: code}})
}

/**
 * 核销操作
 * @param {Object} code
 */
export function verifyCode(code) {
	return request.post('/cashier/storeapi/verify/verify', {data: {verify_code: code}})
}

/**
 * 核销记录列表
 * @param {Object} params
 */
export function getVerifyRecordList(params) {
	return request.post('/cashier/storeapi/verify/recordlists', {data: params})
}

/**
 * 核销记录列表
 * @param {Object} id
 */
export function getVerifyRecordDetail(id) {
	return request.post('/cashier/storeapi/verify/recordsdetail', {data: {id: id}})
}

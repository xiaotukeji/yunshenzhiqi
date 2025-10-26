import request from '@/common/js/http'

/**
 * 获取配送员列表
 * @param {Object} params
 */
export function getDeliverList(params) {
	return request.post('/cashier/storeapi/store/deliverlists', {data: params})
}

/**
 * 获取配送员详情
 * @param {Object} deliver_id
 */
export function getDeliverInfo(deliver_id) {
	return request.post('/cashier/storeapi/store/deliverinfo', {data: {deliver_id}})
}

/**
 * 添加配送员
 * @param {Object} params
 */
export function addDeliver(params) {
	return request.post('/cashier/storeapi/store/adddeliver', {data: params})
}

/**
 * 编辑配送员
 * @param {Object} params
 */
export function editDeliver(params) {
	return request.post('/cashier/storeapi/store/editdeliver', {data: params})
}

/**
 * 删除配送员
 * @param {Object} deliver_id
 */
export function deleteDeliver(deliver_id) {
	return request.post('/cashier/storeapi/store/deletedeliver', {data: {deliver_id}})
}

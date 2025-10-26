import request from '@/common/js/http'

/**
 * 挂单
 * @param {Object} params
 */
export function addPendOrder(params) {
	return request.post('/cashier/storeapi/pendorder/add', {data: params})
}

/**
 * 取单
 * @param {Object} params
 */
export function editPendOrder(params) {
	return request.post('/cashier/storeapi/pendorder/edit', {data: params})
}

/**
 * 修改备注
 * @param {Object} params
 */
export function editPendOrderRemark(params) {
	return request.post('/cashier/storeapi/pendorder/updateremark', {data: params})
}

/**
 * 获取挂单
 * @param {Object} params
 */
export function getPendOrderList(params) {
	return request.post('/cashier/storeapi/pendorder/page', {data: params})
}

/**
 * 删除挂单
 * @param {Object} order_id
 */
export function deletePendOrder(order_id) {
	return request.post('/cashier/storeapi/pendorder/delete', {data: {order_id}})
}
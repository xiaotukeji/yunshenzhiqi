import request from '@/common/js/http'

/**
 * 获取预约配置
 */
export function getReserveConfig() {
	return request.post('/store/storeapi/reserve/getConfig')
}

/**
 * 保存预约配置
 * @param {Object} params
 */
export function setReserveConfig(params) {
	return request.post('/store/storeapi/reserve/setConfig', {data: params})
}

/********************** 预约 ***********************/

/**
 * 获取预约状态字典
 */
export function getReserveStatus() {
	return request.post('/store/storeapi/reserve/status')
}

/**
 * 获取预约记录（每周）
 * @param {Object} params
 */
export function getReserveWeekday(params) {
	return request.post('/store/storeapi/reserve/getweekday', {data: params})
}

/**
 * 获取预约分页数据
 * @param {Object} params
 */
export function getReserveLists(params) {
	return request.post('/store/storeapi/reserve/lists', {data: params})
}

/**
 * 获取预约项目列表
 * @param {Object} params
 */
export function getAppointmentProjectList(params) {
	return request.post('/store/storeapi/reserve/servicelist', {data: params})
}

/**
 * 获取员工列表
 */
export function getEmployeeList() {
	return request.post('/store/storeapi/reserve/servicer')
}

/**
 * 获取预约详情
 * @param  reserve_id
 */
export function getReserveDetail(reserve_id) {
	return request.post('/store/storeapi/reserve/detail', {data: {reserve_id}})
}

/**
 * 添加预约
 * @param {Object} params
 */
export function addReserve(params) {
	return request.post('/store/storeapi/reserve/add', {data: params})
}

/**
 * 编辑预约
 * @param {Object} params
 */
export function editReserve(params) {
	return request.post('/store/storeapi/reserve/update', {data: params})
}

/**
 * 取消预约
 * @param reserve_id
 */
export function cancelReserve(reserve_id) {
	return request.post('/store/storeapi/reserve/cancel', {data: reserve_id})
}

/**
 * 预约到店确认
 * @param  reserve_id
 */
export function reserveToStore(reserve_id) {
	return request.post('/store/storeapi/reserve/confirmToStore', {data: {reserve_id}})
}

/**
 * 预约确认
 * @param  reserve_id
 */
export function reserveConfirm(reserve_id) {
	return request.post('/store/storeapi/reserve/confirm', {data: {reserve_id}})
}

/**
 * 预约完成
 * @param  reserve_id
 */
export function reserveComplete(reserve_id) {
	return request.post('/store/storeapi/reserve/complete', {data: {reserve_id}})
}

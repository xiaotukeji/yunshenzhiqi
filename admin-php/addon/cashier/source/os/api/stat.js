import request from '@/common/js/http'

/**
 * 营业数据统计相关
 */

/**
 * 获取整体统计
 * @param {Object} params
 */
export function getStatTotal(params) {
	return request.post('/cashier/storeapi/stat/statTotal', {data: params})
}

/**
 * 获取当日统计
 * @param {Object} params
 */
export function getStatDay(params) {
	return request.post('/cashier/storeapi/stat/dayStatData', {data: params})
}

/**
 * 获取当日统计
 * @param {Object} params
 */
export function getStatHour(params) {
	return request.post('/cashier/storeapi/stat/hourStatData', {data: params})
}

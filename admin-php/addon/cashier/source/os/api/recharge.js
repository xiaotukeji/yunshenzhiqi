import request from '@/common/js/http'

/**
 * 获取订单列表
 * @param {Object} params
 */
export function getOrderRechargeList(params) {
	return request.post('/cashier/storeapi/recharge/orderpage', {
		data: params
	})
}

/**
 * 获取订单详情
 * @param {Object} params
 */
export function getOrderRechargeDetail(params) {
	return request.post('/cashier/storeapi/recharge/orderdetail', {data: params})
}

/**
 * 获取充值基本配置
 * @param {Object} params
 */
export function getRechargeConfig(params) {
	return request.post('/cashier/storeapi/recharge/activity', {data: params})
}

/**
 * 客户充值
 * @param {Object} params
 */
export function addRecharge(params) {
	return request.post('/cashier/storeapi/cashierordercreate/rechargecreate', {data: params})
}

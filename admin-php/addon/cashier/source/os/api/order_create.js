import request from '@/common/js/http'

/**
 * 开单(计算)
 * @param {Object} params
 */
export function calculate(params) {
	return request.post('/cashier/storeapi/cashierordercreate/calculate', {data: params})
}

/**
 * 开单（订单创建）
 * @param {Object} params
 */
export function create(params) {
	return request.post('/cashier/storeapi/cashierordercreate/create', {data: params})
}

/**
 * 售卡（计算）
 * @param {Object} params
 */
export function cardCalculate(params) {
	return request.post('/cashier/storeapi/cashierordercreate/cardcalculate', {data: params})
}

/**
 * 售卡（订单创建）
 * @param {Object} params
 */
export function cardCreate(params) {
	return request.post('/cashier/storeapi/cashierordercreate/cardcreate', {data: params})
}

/**
 * 支付(计算)
 * @param {Object} params
 */
export function payCalculate(params) {
	return request.post('/cashier/storeapi/cashierpay/paycalculate', {data: params})
}
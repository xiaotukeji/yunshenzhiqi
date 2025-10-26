import request from '@/common/js/http'
import util from '@/common/js/util'

/**
 * 订单列表
 * @param {Object} params
 */
export function getOrderList(params) {
	return request.post('/cashier/storeapi/cashierorder/lists', {
		data: params
	})
}

/**
 * 订单详情
 * @param {Object} params
 */
export function getOrderDetail(params) {
	return request.post('/cashier/storeapi/cashierorder/detail', {
		data: params
	})
}

/**
 * 获取支付信息
 * @param {Object} order_id
 */
export function getOrderInfoById(order_id) {
	return request.post('/cashier/storeapi/order/info', {
		data: {order_id}
	})
}

/**
 * 订单调价
 * @param {Object} params
 */
export function orderAdjustPrice(params) {
	return request.post('/cashier/storeapi/cashierorder/adjustPrice', {
		data: params
	})
}

/**
 * 订单备注
 * @param {Object} params
 */
export function orderRemark(params) {
	return request.post('/cashier/storeapi/cashierorder/orderRemark', {
		data: params
	})
}

/**
 * 提货
 * @param {Object} order_id
 */
export function orderStoreDelivery(order_id) {
	return request.post('/cashier/storeapi/order/storedelivery', {
		data: {order_id}
	})
}

/**
 * 本地配送
 * @param {Object} params
 */
export function orderLocalDelivery(params) {
	return request.post('/cashier/storeapi/order/localdelivery', {
		data: params
	})
}

/**
 * 订单关闭
 * @param {Object} params
 */
export function orderClose(params) {
	return request.post('/cashier/storeapi/order/close', {
		data: params
	})
}

/**
 * 打印小票
 * @param order_id
 * @returns {*}
 */
export function orderPrintTicket(order_id) {
	let params = {order_id:order_id};
	//追加打印机数据
	let local_config = util.getLocalConfig();
	params.printer_ids = local_config.printerSelectType == 'all' ? 'all' : local_config.printerSelectIds.toString();
	return request.post('/cashier/storeapi/cashierorder/printticket', {
		data: params
	})
}

/**
 * 获取物流公司
 */
export function getExpressCompanyList() {
	return request.post('/cashier/storeapi/order/expresscompany')
}

/**
 * 获取配送员列表
 * @param {Object} params
 */
export function getOrderDeliverList(params) {
	return request.post('/cashier/storeapi/order/deliverlist', {data: params})
}

/**
 * 订单删除
 * @param {Object} params
 */
export function orderExpressDelivery(params) {
	return request.post('/cashier/storeapi/order/expressdelivery', {
		data: params
	})
}

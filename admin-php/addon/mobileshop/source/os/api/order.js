import request from '@/common/js/http'

/**
 * 获取订单列表
 * @param {Object} params
 */
export function getOrderList(params) {
	return request.post('/shopapi/order/lists', {data: params})
}

/**
 * 获取订单筛选项
 */
export function getOrderCondition() {
	return request.get('/shopapi/order/condition')
}

/**
 * 获取订单详情
 * @param  order_id
 */
export function getOrderDetailInfoById(order_id) {
	return request.post('/shopapi/order/detail', {data: {order_id}})
}
/**
 * 获取订单详情，包括订单项
 * @param  order_id
 */
export function getOrderDetailById(order_id) {
	return request.post('/shopapi/order/getOrderDetail', {data: {order_id}})
}

/**
 * 获取订单详情，不包括订单项
 * @param  order_id
 */
export function getOrderInfoById(order_id) {
	return request.post('/shopapi/order/getOrderInfo', {data: {order_id}})
}

/**
 * 获取订单项
 * @param  order_id
 */
export function getOrderGoodsList(order_id) {
	return request.post('/shopapi/order/getOrderGoodsList', {data: {order_id}})
}

/**
 * 获取订单日志
 * @param  order_id
 */
export function getOrderLog(order_id) {
	return request.post('/shopapi/order/log', {data: {order_id}})
}

/**
 * 获取订单物流包裹
 * @param  order_id
 */
export function getOrderPackageList(order_id) {
	return request.post('/shopapi/order/package', {data: {order_id}})
}

/**
 * 订单调价
 * @param {Object} params
 */
export function adjustOrderPrice(params) {
	return request.post('/shopapi/order/adjustPrice', {data: params})
}

/**
 * 订单线下支付
 * @param order_id
 */
export function orderOfflinePay(order_id) {
	return request.post('/shopapi/order/offlinePay', {data: {order_id}})
}

/**
 * 订单发货
 * @param {Object} params
 */
export function deliveryOrder(params) {
	return request.post('/shopapi/order/delivery', {data: params})
}

/**
 * 编辑订单发货信息
 * @param {Object} params
 */
export function editOrderDelivery(params) {
	return request.post('/shopapi/order/editOrderDelivery', {data: params})
}

/**
 * 外卖订单发货
 * @param params
 */
export function orderLocalorderDelivery(params) {
	return request.post('/shopapi/localorder/delivery', {data: params})
}

/**
 * 虚拟订单发货
 * @param order_id
 */
export function orderVirtualDelivery(order_id) {
	return request.post('/shopapi/virtualorder/delivery', {data: {order_id}})
}

/**
 * 门店订单提货
 * @param order_id
 */
export function storeOrderTakeDelivery(order_id) {
	return request.post('/shopapi/Storeorder/storeOrderTakeDelivery', {data: {order_id}})
}

/**
 * 延长收货
 * @param order_id
 */
export function orderExtendTakeDelivery(order_id) {
	return request.post('/shopapi/order/extendtakedelivery', {data: {order_id}})
}

/**
 * 确认收货
 * @param order_id
 */
export function ordErtakeDelivery(order_id) {
	return request.post('/shopapi/order/takeDelivery', {data: {order_id}})
}

/**
 * 订单关闭
 * @param order_id
 */
export function closeOrder(order_id) {
	return request.post('/shopapi/order/close', {data: {order_id}})
}

/***************** 订单发票 *****************/

/**
 * 获取订单发票
 * @param {Object} params
 */
export function getOrderInvoicelist(params) {
	return request.post('/shopapi/order/invoicelist', {data: params})
}

/**
 * 编辑订单发票信息
 * @param {Object} params
 */
export function editOrderInvoicelist(params) {
	return request.post('/shopapi/order/invoiceEdit', {data: params})
}






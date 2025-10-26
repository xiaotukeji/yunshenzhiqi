import request from '@/common/js/http'

/**
 * 订单退款申请数据
 * @param {Object} params
 */
export function getRefundApplyData(params) {
	return request.post('/cashier/storeapi/cashierorderrefund/getrefundapplydata', {
		data: params
	})
}

/**
 * 订单退款
 * @param {Object} params
 */
export function orderRefund(params) {
	return request.post('/cashier/storeapi/cashierorderrefund/refund', {
		data: params
	})
}

/**
 * 转账
 * @param {Object} params
 */
export function orderRefundComplete(params) {
	return request.post('/cashier/storeapi/cashierorderrefund/complete', {
		data: params
	})
}

/**
 * 获取订单列表
 * @param {Object} params
 */
export function getOrderRefundLists(params) {
	return request.post('/cashier/storeapi/cashierorderrefund/lists', {
		data: params
	})
}

/**
 * 获取订单详情
 * @param {Object} params
 */
export function getOrderRefundDetail(params) {
	return request.post('/cashier/storeapi/cashierorderrefund/detail', {data: params})
}

/**
 * 同意维权
 * @param {Object} params
 */
export function orderRefundAgree(params) {
	return request.post('/cashier/storeapi/cashierorderrefund/agree', {
		data: params
	})
}

/**
 * 拒绝维权
 * @param {Object} params
 */
export function orderRefundRefuse(params) {
	return request.post('/cashier/storeapi/cashierorderrefund/refuse', {
		data: params
	})
}

/**
 * 买家退货接收，维权收货
 * @param {Object} params
 */
export function orderRefundReceive(params) {
	return request.post('/cashier/storeapi/cashierorderrefund/receive', {
		data: params
	})
}

/**
 * 关闭维权
 * @param {Object} params
 */
export function orderRefundClose(params) {
	return request.post('/cashier/storeapi/cashierorderrefund/close', {
		data: params
	})
}

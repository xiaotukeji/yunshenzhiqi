import request from '@/common/js/http'

/**
 * 获取维权订单筛选项
 */
export function getOrderRefundCondition() {
	return request.get('/shopapi/orderrefund/condition')
}

/**
 * 获取维权订单列表
 * @param {Object} params
 */
export function getOrderRefundList(params) {
	return request.post('/shopapi/orderrefund/lists', {data: params})
}

/**
 * 获取维权订单详情
 */
export function getOrderRefundInfoById(order_goods_id) {
	return request.post('/shopapi/orderrefund/detail', {data: {order_goods_id}})
}

/**
 * 关闭维权订单
 */
export function closeOrderRefund(order_goods_id) {
	return request.post('/shopapi/orderrefund/close', {data: {order_goods_id}})
}
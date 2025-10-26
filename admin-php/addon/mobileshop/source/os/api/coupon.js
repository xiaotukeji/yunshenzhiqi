import request from '@/common/js/http'

/**
 * 获取优惠券列表
 * @param {Object} params
 */
export function getCouponLists(params) {
	return request.post('/coupon/shopapi/coupon/lists', {data: params})
}

/**
 * 优惠券发放
 * @param {Object} params
 */
export function sendCoupon(params) {
	return request.post('/coupon/shopapi/coupon/send', {data: params})
}


import request from '@/common/js/http'

/************************************优惠券*******************************/
/**
 * 优惠券详情
 * @param {Object} coupon_type_id
 */
export function getCouponDetail(coupon_type_id) {
	return request.post('/coupon/storeapi/coupon/detail', {data: {coupon_type_id}})
}

/**
 * 优惠券详情领取记录
 * @param {Object} coupon_type_id
 */
export function getReceiveCouponPageList(coupon_type_id) {
	return request.post('/coupon/storeapi/membercoupon/getReceiveCouponPageList', {data: {coupon_type_id}})
}

/**
 * 新增优惠券
 */
export function addCoupon(params) {
	return request.post('/coupon/storeapi/coupon/add', {data: params})
}

/**
 * 编辑优惠券
 */
export function editCoupon(params) {
	return request.post('/coupon/storeapi/coupon/edit', {data: params})
}

/**
 * 关闭优惠券
 * @param {Object} coupon_type_id
 */
export function closeCoupon(coupon_type_id) {
	return request.post('/coupon/storeapi/coupon/close', {data: {coupon_type_id}})
}

/**
 * 删除优惠券
 * @param {Object} coupon_type_id
 */
export function deleteCoupon(coupon_type_id) {
	return request.post('/coupon/storeapi/coupon/delete', {data: {coupon_type_id}})
}

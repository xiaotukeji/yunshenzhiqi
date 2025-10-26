import request from '@/common/js/http'

/**
 * 获取商品设配置
 */
export function getGoodsConfig() {
	return request.get('/shopapi/goods/config')
}

/**
 * 设置商品配置
 * @param {Object} params
 */
export function setGoodsConfig(params) {
	return request.post('/shopapi/goods/setconfig', {data: params})
}

/**
 * 获取验证码配置
 */
export function getCaptchaConfig() {
	return request.get('/shopapi/config/captchaConfig')
}

/**
 * 获取订单配置
 */
export function getOrderConfig() {
	return request.get('/shopapi/order/config')
}

/**
 * 设置订单配置
 * @param {Object} params
 */
export function setOrderConfig(params) {
	return request.post('/shopapi/order/setconfig', {data: params})
}

/**
 * 设置商城配置
 * @param {Object} params
 */
export function setShopConfig(params) {
	return request.post('/shopapi/shop/config', {data: params})
}

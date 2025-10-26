import request from '@/common/js/http'
/**
 * 获取推广二维码设置
 */
export function getAddonIsExist() {
	return request.post('/cashier/storeapi/Config/addonIsExist')
}

/**
 * 获取推广二维码
 * @param {Object} params
 */
export function getPromotionQrcode(params) {
	return request.post('/cashier/storeapi/Promotion/getPromotionQrcode', {data: params})
}
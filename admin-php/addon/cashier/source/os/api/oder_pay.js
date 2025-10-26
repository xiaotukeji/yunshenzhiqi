import request from '@/common/js/http'

/**
 * 支付确认
 * @param {Object} params
 */
export function cashierConfirm(params) {
	return request.post('/cashier/storeapi/cashierpay/confirm', {data: params})
}

/**
 * 创建支付单
 * @param {Object} out_trade_no
 */
export function addPayCashierPay(out_trade_no) {
	return request.post('/cashier/storeapi/cashierpay/createpay', {data: {out_trade_no}})
}

/**
 * 获取支付二维码
 * @param {Object} out_trade_no
 */
export function getPayQrcode(out_trade_no) {
	return request.post('/cashier/storeapi/cashierpay/payqrcode', {data: {out_trade_no}})
}
/**
 * 获取扫码枪配置
 * */
 export function getPayType() {
 	return request.post('/cashier/storeapi/Cashierpay/payType')
 }
 
/**
 * 扫码枪支付
 * @param {Object} params
 */
export function authCodepay(params) {
	return request.post('/pay/pay/authCodepay', {data: params})
}

/**
 * 获取支付信息
 * @param {Object} out_trade_no
 */
export function getCashierPayInfo(out_trade_no) {
	return request.post('/cashier/storeapi/cashierpay/info', {data: {out_trade_no}})
}

/**
 * 账号余额支付确认
 * @param {Object} params
 */
export function checkPaymentCode(params) {
	return request.post('/cashier/storeapi/member/checkpaymentcode', {data: params})
}
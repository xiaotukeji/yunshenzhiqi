import request from '@/common/js/http'

/**
 * 获取提现设置
 */
export function getWithdrawConfig() {
	return request.post('/store/storeapi/store/withdrawconfig')
}

/**
 * 申请提现
 * @param {Object} money
 */
export function applyWithdraw(money) {
	return request.post('/store/storeapi/withdraw/apply', {data: {money}})
}

/**
 * 提现详情
 * @param {Object} withdraw_id
 */
export function withdrawDetail(withdraw_id) {
	return request.post('/store/storeapi/withdraw/detail', {data: {withdraw_id}})
}

/**
 * 获取提现方式
 */
export function getWithdrawScreen() {
	return request.post('/store/storeapi/withdraw/screen')
}

/**
 * 获取提现列表
 * @param {Object} params
 */
export function getWithdrawPage(params) {
	return request.post('/store/storeapi/withdraw/page', {data: params})
}

/**
 * 账户筛选条件
 */
export function getAccountScreen() {
	return request.post('/store/storeapi/account/screen')
}

/**
 * 账户导出
 */
export function accountExport(params) {
	return request.post('/store/storeapi/account/export', {data: params})
}

/**
 * 是否可以新版本提现
 */
export function withdrawConfig() {
	return request.post('/wechatpay/api/transfer/getWithdrawConfig')
}

/**
 * 提现二维码
 */
export function transferCode(params) {
	return request.post('/store/storeapi/withdraw/getTransferCode', {data: params})
}

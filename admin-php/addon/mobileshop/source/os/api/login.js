import request from '@/common/js/http'

/**
 * 获取验证码
 * @param  captcha_id
 */
export function getCaptcha(captcha_id) {
	return request.post('/shopapi/captcha/captcha', {data: {captcha_id}})
}

/**
 * 登录
 * @param {Object} params
 */
export function login(params) {
	return request.post('/shopapi/login/login', {data: params})
}

/**
 * 修改密码
 * @param {Object} params
 */
export function modifyPassword(params) {
	return request.post('/shopapi/login/modifyPassword', {data: params})
}
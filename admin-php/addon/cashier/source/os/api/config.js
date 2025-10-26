import request from '@/common/js/http'

/************************************整体设置+系统权限相关*******************************/
/**
 * 获取收款设置
 */
export function getCollectMoneyConfig() {
	return request.post('/cashier/storeapi/cashier/getcashiercollectmoneyconfig')
}

/**
 * 收款设置
 * @param {Object} params
 */
export function setCollectMoneyConfig(params) {
	return request.post('/cashier/storeapi/cashier/setcashiercollectmoneyconfig', {data: params})
}

/**
 * 检测页面是否有权限
 * @param {Object} page
 */
export function checkPageAuth(page) {
	return request.post('/cashier/storeapi/store/checkpageauth', {data: {page}})
}

/**
 * 检测是否有新版本
 * @param {Object} params
 */
export function checkUpdate(params) {
	return request.post('/cashier/storeapi/appversion/checkupdate', {data: params})
}

/**
 * 获取插件是否存在
 * @param {Object} params
 */
export function getAddonIsExist(params) {
	return request.post('/storeapi/addon/addonisexit')
}

/**
 * 获取默认图
 * @param {Object} params
 */
export function getDefaultImg(params) {
	return request.post('/cashier/storeapi/cashier/defaultimg')
}

/**
 * 设置收银台主题风格配置
 * @param {Object} params
 */
export function setThemeConfig(params) {
	return request.post('/cashier/storeapi/cashier/setThemeConfig', {data: params})
}

/**
 * 获取收银台主题风格配置
 */
export function getThemeConfig() {
	return request.post('/cashier/storeapi/config/getThemeConfig')
}

/**
 * 获取收银台主题风格列表
 */
export function getThemeList() {
	return request.post('/cashier/storeapi/cashier/getThemeList')
}

/**
 * 设置收银台会员搜索方式配置
 * @param {Object} params
 */
export function setMemberSearchWayConfig(params) {
	return request.post('/cashier/storeapi/config/setMemberSearchWayConfig', {data: params})
}

/**
 * 获取收银台会员搜索方式配置
 */
export function getMemberSearchWayConfig() {
	return request.post('/cashier/storeapi/config/getMemberSearchWayConfig')
}

/**
 * 获取收银台订单消息推送-配置
*/
export function getOrderRemind() {
	return request.post('/cashier/storeapi/config/orderRemind')
}

/**
 * 收银台消息推送-绑定门店
*/
export function pushBind(params) {
	return request.post('/cashier/storeapi/push/bind',{data:params})
}

/**
 * 收银台消息推送-更换绑定门店
*/
export function pushChangeBind(params) {
	return request.post('/cashier/storeapi/push/changebind',{data:params})
}

/**
 * 收银台消息推送-下线
*/
export function pushOffline() {
	return request.post('/cashier/storeapi/push/offline',{data:params})
}

/**
 * 获取收银台订单消息推送-服务状态
*/
export function getPushStatus() {
	return request.post('/cashier/storeapi/push/status')
}
import request from '@/common/js/http'

/**
 * 获取门店信息（当前）
 */
export function getStoreInfo() {
	return request.post('/cashier/storeapi/store/info')
}

/**
 * 获取门店列表（当前用户有权限的）
 */
export function getStoreList() {
	return request.post('/cashier/storeapi/store/lists')
}

/**
 * 门店编辑
 * @param {Object} params
 */
export function editStore(params) {
	return request.post('/store/storeapi/store/edit', {data: params})
}

/**
 * 获取所有门店标签
 */
export function getAllStoreLabel() {
	return request.post('/store/storeapi/store/label')
}

/**
 * 获取所有的门店分类
 */
export function getAllStoreCategory() {
	return request.post('/store/storeapi/store/category')
}

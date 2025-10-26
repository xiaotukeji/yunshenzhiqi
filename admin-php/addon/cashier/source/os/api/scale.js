import request from '@/common/js/http'

/**
 * 获取电子秤列表
 */
export function getScaleList(params) {
	return request.post('/scale/storeapi/scale/page', {data: params})
}

/**
 * 获取电子秤详情
 */
export function getScaleDetail(params) {
	return request.post('/scale/storeapi/scale/detail', {data: params})
}

/**
 * 删除电子秤
 */
export function deleteScale(params) {
	return request.post('/scale/storeapi/scale/delete', {data: params})
}

/**
 * 获取电子秤品牌
 */
export function getScaleBrand() {
	return request.post('/scale/storeapi/scale/scaleBrand')
}

/**
 * 编辑电子秤
 */
export function editScale(params) {
	return request.post('/scale/storeapi/scale/edit', {data: params})
}

/**
 * 添加电子秤
 */
export function addScale(params) {
	return request.post('/scale/storeapi/scale/add', {data: params})
}
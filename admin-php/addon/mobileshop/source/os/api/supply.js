import request from '@/common/js/http'

/**
 * 获取供应商列表
 */
export function getSupplyList() {
	return request.get('/supply/shopapi/supply/lists')
}
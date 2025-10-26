import request from '@/common/js/http'

/**
 * 获取商品表单列表
 */
export function getOrderFormList() {
	return request.get('/form/shopapi/form/lists')
}
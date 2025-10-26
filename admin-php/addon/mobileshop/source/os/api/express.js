import request from '@/common/js/http'

/**
 * 获取运费模板列表
 */
export function getExpressTemplateList() {
	return request.get('/shopapi/express/getExpressTemplateList')
}

/**
 * 获取物流公司列表
 */
export function getExpressCompanyList() {
	return request.get('/shopapi/express/expressCompany')
}
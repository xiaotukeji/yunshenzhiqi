import request from '@/common/js/http'

/**
 * 获取首页信息
 */
export function getIndex() {
	return request.get('/shopapi/index/index')
}
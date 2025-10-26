import request from '@/common/js/http'

/**
 * 获取公告列表
 * @param {Object} params
 */
export function getNoticeList(params) {
	return request.post('/shopapi/notice/lists', {data: params})
}

/**
 * 获取公告详情
 * @param  id
 */
export function getNoticeInfoById(id) {
	return request.post('/shopapi/notice/detail', {data: {id}})
}

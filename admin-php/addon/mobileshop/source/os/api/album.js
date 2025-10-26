import request from '@/common/js/http'

/**
 * 获取相册分组列表
 */
export function getAlbumLists() {
	return request.get('/shopapi/album/lists')
}

/**
 * 获取相册列表
 * @param {Object} params
 */
export function getAlbumPicLists(params) {
	return request.post('/shopapi/album/picList', {data: params})
}

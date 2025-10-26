import request from '@/common/js/http'

/**
 * 获取卡项商品列表
 * @param {Object} params
 */
export function getGoodsCardList(params) {
	return request.post('/cardservice/shopapi/goods/cardlist', {data: params})
}

/**
 * 获取卡项商品详情
 * @param  card_id
 */
export function getGoodsCardInfoById(card_id) {
	return request.post('/cardservice/shopapi/goods/carddetail', {data: {card_id}})
}

/**
 * 获取卡项商品使用记录
 * @param  params
 */
export function getGoodsCardUsageRecords(params) {
	return request.post('/cardservice/shopapi/goods/cardUserecord', {data: params})
}


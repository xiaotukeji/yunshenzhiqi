import request from '@/common/js/http'

/**
 * 获取首页订单概览
 * @param day
 */
export function getOrderStatistics(day) {
	return request.post('/shopapi/statistics/orderstatistics', {data: {day}})
}
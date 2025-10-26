import request from '@/common/js/http'

/**
 * 收银交班
 */
export function changeShifts(params = {}) {
	return request.post('/cashier/storeapi/cashier/changeshifts', {data: params})
}

/**
 * 数量统计
 */
export function getShiftsData() {
	return request.post('/cashier/storeapi/cashier/shiftsdata')
}

/**
 * 导出交班记录
 */
export function saleGoodsExport(params) {
	return request.post('/cashier/storeapi/cashier/changeShiftsSaleGoodsExport', {data: params})
}
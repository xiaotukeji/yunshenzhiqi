import request from '@/common/js/http'
import util from '@/common/js/util'

/**
 * 获取打印机列表
 */
export function getPrinterList(params) {
	return request.post('/printer/storeapi/printer/lists', {data: params})
}

/**
 * 获取打印机模板
 */
export function getTemplate() {
	return request.post('/printer/storeapi/printer/template')
}

/**
 * 获取打印机详情
 */
export function getPrinterInfo(printer_id) {
	return request.post('/printer/storeapi/printer/info', {data: {printer_id}})
}

/**
 * 删除打印机
 */
export function deletePrinter(printer_id) {
	return request.post('/printer/storeapi/printer/deleteprinter', {data: {printer_id}})
}

/**
 * 获取订单类型
 */
export function getOrderType() {
	return request.post('/printer/storeapi/printer/getordertype')
}

/**
 * 编辑打印机
 */
export function editPrinter(params) {
	return request.post('/printer/storeapi/printer/edit', {data: params})
}

/**
 * 添加打印机
 */
export function addPrinter(params) {
	return request.post('/printer/storeapi/printer/add', {data: params})
}

/**
 * 打印小票
 */
export function printTicket(params = {}) {
	//追加打印机数据
	let local_config = util.getLocalConfig();
	params.printer_ids = local_config.printerSelectType == 'all' ? 'all' : local_config.printerSelectIds.toString();
	return request.post('/cashier/storeapi/cashier/printticket', {data: params})
}
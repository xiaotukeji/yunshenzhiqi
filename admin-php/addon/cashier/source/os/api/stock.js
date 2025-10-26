import request from '@/common/js/http'

/**
 * 获取库存商品列表
 * @param {Object} params
 */
export function getStockGoodsList(params) {
	return request.post('/stock/storeapi/manage/lists', {data: params})
}

/**
 * 获取商品的库存流水
 * @param {Object} params
 */
export function getStockGoodsRecords(params) {
	return request.post('/stock/storeapi/manage/records', {data: params})
}

/**
 * 获取单据类型
 */
export function getDocumentType() {
	return request.post('/stock/storeapi/manage/getDocumentType')
}

/************************** 库存通用接口 ********************************/

/**
 * 出入库单据审核通过
 * @param  document_id
 */
export function storageAgree(document_id) {
	return request.post('/stock/storeapi/storage/agree', {data: {document_id}})
}

/**
 * 入库单据审核拒绝
 * @param {Object} params 需要包括拒绝理由
 */
export function storageRefuse(params) {
	return request.post('/stock/storeapi/storage/refuse', {data: params})
}

/**
 * 出入库单据删除
 * @param {Object} document_id
 */
export function storageDelete(document_id) {
	return request.post('/stock/storeapi/storage/delete', {data: {document_id}})
}

/**
 * 库存回车查询单条商品
 * @param {Object} params
 */
export function getSkuListForStock(params) {
	return request.post('/stock/storeapi/manage/getskulist', {data: params})
}

/************************** 入库接口 ********************************/
/**
 * 获取入库单列表
 * @param {Object} params
 */
export function getStorageLists(params) {
	return request.post('/stock/storeapi/storage/lists', {data: params})
}

/**
 * 获取入库单详情
 * @param  document_id
 */
export function getStorageDetail(document_id) {
	return request.post('/stock/storeapi/storage/detail', {data: {document_id}})
}

/**
 * 获取入库单号
 */
export function getStorageDocumentNo() {
	return request.post('/stock/storeapi/storage/getDocumentNo')
}

/**
 * 获取入库单编辑时详情
 * @param  document_id
 */
export function getStorageDetailInEdit(document_id) {
	return request.post('/stock/storeapi/storage/editData', {data: {document_id}})
}

/**
 * 入库单编辑新增
 * @param {Object} params
 */
export function editStorage(params) {
	return request.post('/stock/storeapi/storage/stockin', {data: params})
}

/************************** 出库接口 ********************************/

/**
 * 获取出库单列表
 * @param {Object} params
 */
export function getWastageLists(params) {
	return request.post('/stock/storeapi/wastage/lists', {data: params})
}

/**
 * 获取出库单详情
 * @param  document_id
 */
export function getWastageDetail(document_id) {
	return request.post('/stock/storeapi/wastage/detail', {data: {document_id}})
}

/**
 * 获取出库单号
 */
export function getWastageDocumentNo() {
	return request.post('/stock/storeapi/wastage/getDocumentNo')
}

/**
 * 获取出库单编辑时详情
 * @param  document_id
 */
export function getWastageDetailInEdit(document_id) {
	return request.post('/stock/storeapi/wastage/editData', {data: {document_id}})
}

/**
 * 出库单编辑新增
 * @param {Object} params
 */
export function editWastage(params) {
	return request.post('/stock/storeapi/wastage/stockout', {data: params})
}

/************************** 调拨接口 ********************************/

/**
 * 获取调拨单列表
 * @param {Object} params
 */
export function getAllocateList(params) {
	return request.post('/stock/storeapi/allocate/lists', {data: params})
}

/**
 * 获取调拨单详情
 * @param {Object} allot_id
 */
export function getAllocateDetail(allot_id) {
	return request.post('/stock/storeapi/allocate/detail', {data: {allot_id: allot_id}})
}

/**
 * 获取调拨单号
 */
export function getAllotNo() {
	return request.post('/stock/storeapi/allocate/getAllotNo')
}

/**
 * 获取调拨单编辑时详情
 * @param  allot_id
 */
export function getAllocateDetailInEdit(allot_id) {
	return request.post('/stock/storeapi/allocate/editData', {data: {allot_id}})
}

/**
 * 获取调拨门店列表(库存查询门店)
 */
export function getStoreLists() {
	return request.post('/stock/storeapi/store/lists')
}

/**
 * 调拨单新增
 * @param {Object} params
 */
export function addAllocate(params) {
	return request.post('/stock/storeapi/allocate/addallocate', {data: params})
}

/**
 * 调拨单编辑
 * @param {Object} params
 */
export function editAllocate(params) {
	return request.post('/stock/storeapi/allocate/editAllocate', {data: params})
}

/**
 * 调拨单据删除
 * @param {Object} allot_id
 */
export function allocateDelete(allot_id) {
	return request.post('/stock/storeapi/allocate/delete', {data: {allot_id: allot_id}})
}

/**
 * 调拨单据审核通过
 * @param {Object} allot_id
 */
export function allocateAgree(allot_id) {
	return request.post('/stock/storeapi/allocate/agree', {data: {allot_id: allot_id}})
}

/**
 * 调拨单据审核拒绝
 * @param {Object} params 需要包括拒绝理由
 */
export function allocateRefuse(params) {
	return request.post('/stock/storeapi/allocate/refuse', {data: params})
}

/************************** 库存盘点接口 ********************************/

/**
 * 获取盘点单列表
 * @param {Object} params
 */
export function getInventoryList(params) {
	return request.post('/stock/storeapi/check/lists', {data: params})
}

/**
 * 获取盘点单详情
 * @param {Object} inventory_id
 */
export function getInventoryDetail(inventory_id) {
	return request.post('/stock/storeapi/check/detail', {data: {inventory_id}})
}

/**
 * 获取盘点单号
 */
export function getInventoryNo() {
	return request.post('/stock/storeapi/Check/getInventoryNo')
}

/**
 * 获取盘点单编辑时详情
 * @param  inventory_id
 */
export function getInventoryDetailInEdit(inventory_id) {
	return request.post('/stock/storeapi/check/editData', {data: {inventory_id}})
}

/**
 * 盘点单新增
 * @param {Object} params
 */
export function addInventory(params) {
	return request.post('/stock/storeapi/check/add', {data: params})
}

/**
 * 盘点单编辑
 * @param {Object} params
 */
export function editInventory(params) {
	return request.post('/stock/storeapi/check/edit', {data: params})
}

/**
 * 盘点单据删除
 * @param {Object} inventory_id
 */
export function inventoryDelete(inventory_id) {
	return request.post('stock/storeapi/check/delete', {data: {inventory_id}})
}

/**
 * 盘点单据审核通过
 * @param {Object} inventory_id
 */
export function inventoryAgree(inventory_id) {
	return request.post('/stock/storeapi/check/agree', {data: {inventory_id}})
}

/**
 * 盘点单据审核拒绝
 * @param {Object} params 需要包括拒绝理由
 */
export function inventoryRefuse(params) {
	return request.post('/stock/storeapi/check/refuse', {data: params})
}

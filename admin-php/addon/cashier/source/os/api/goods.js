import request from '@/common/js/http'

/**
 * 商品列表
 * @param {Object} params
 */
export function getGoodsList(params) {
	return request.post('/cashier/storeapi/goods/page', {data: params})
}

/**
 * 商品详情
 * @param {Object} goods_id
 */
export function getGoodsDetail(goods_id) {
	return request.post('/cashier/storeapi/goods/detail', {data: {goods_id}})
}

/**
 * 编辑商品
 * @param {Object} params
 */
export function editGoods(params) {
	return request.post('/cashier/storeapi/goods/editgoods', {data: params})
}


/**
 * 编辑商品
 * @param {Object} params
 */
export function setGoodsLocalRestrictions(params) {
	return request.post('/cashier/storeapi/goods/setGoodsLocalRestrictions', {data: params})
}


/**
 * 通过扫码事件查询会员信息
 * @param {Object} sku_no
 */
export function getGoodsInfoByCode(sku_no) {
	return request.post('/cashier/storeapi/goods/skuinfo', {data: {sku_no}})
}

/**
 * 设置商品状态
 * @param {Object} params
 *{	goods_id: arr.toString(), status: status}
 */
export function setGoodsStatus(params) {
	return request.post('/cashier/storeapi/goods/setstatus', {data: params})
}

/**
 * 商品分类
 * @param {Object} params
 */
export function getGoodsCategory(params) {
	return request.post('/cashier/storeapi/goods/category', {data: params})
}

/**
 * 商品分类[库存]
 * @param {Object} params
 */
export function getManageGoodsCategory(params = {}) {
	return request.post('/stock/storeapi/manage/getGoodsCategory', {data: params})
}

/**
 * 商品库存转换
 * @param {Object} params
 */
export function getStocktransform(params = {}) {
	return request.post('/cashier/storeapi/goods/stocktransform', {data: params})
}

/**
 * 项目分类
 * @param {Object} params
 */
export function getServiceCategory(params) {
	return request.post('/cashier/storeapi/service/category', {data: params})
}

/**
 * 项目列表
 * @param {Object} params
 */
export function getServiceList(params) {
	return request.post('/cashier/storeapi/service/page', {data: params})
}

/**
 * 获取商品项目多规格规格数据
 * @param {Object} goods_id
 */
export function getGoodsSkuList(goods_id) {
	return request.post('/cashier/storeapi/goods/skulist', {data: {goods_id}})
}

/**
 * 获取电子秤信息
 */
export function getElectronicScaleInformation() {
	return request.post('/scale/storeapi/scale/cashierscale')
}

/**
 * 商品搜索条件
 * @param {Object} params
 */
export function getGoodsSceen(params = {}) {
	return request.post('/cashier/storeapi/goods/screen', {data: params})
}

/**
 * 规格选择
 * @param {Object} params
 */
export function getSkuListBySelect(params = {}) {
	return request.post('/cashier/storeapi/goods/getSkuListBySelect', {data: params})
}

/**
 * 导出打印价格标签数据
 * @param {Object} params
 */
export function exportPrintPriceTagData(params = {}) {
	return request.post('/cashier/storeapi/goods/exportPrintPriceTagData', {data: params})
}


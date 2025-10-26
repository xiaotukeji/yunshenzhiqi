import request from '@/common/js/http'

/**
	virtual_order
	account
	addon
	store
 */

/**
 * 获取商品分类树状结构
 * @param {Object} params
 */
export function getCategoryTree(params) {
	return request.post('/shopapi/goods/getCategoryTree', {data: params})
}

/**
 * 获取营销活动
 */
export function getCondition() {
	return request.get('/shopapi/goods/condition')
}

/**
 * 获取商品列表
 * @param {Object} params
 */
export function getGoodsLists(params) {
	return request.post('/shopapi/goods/lists', {data: params})
}

/**
 * 获取商品编辑详情
 * @param  goods_id
 */
export function getGoodsInfoById(goods_id) {
	return request.post('/shopapi/goods/editGetGoodsInfo', {data: {goods_id}})
}

/**
 * 新增实物商品
 * @param {Object} params
 */
export function addGoods(params) {
	return request.post('/shopapi/goods/addGoods', {data: params})
}

/**
 * 编辑实物商品
 * @param {Object} params
 */
export function editGoods(params) {
	return request.post('/shopapi/goods/editGoods', {data: params})
}

/**
 * 新增虚拟商品
 * @param {Object} params
 */
export function addVirtualGoods(params) {
	return request.post('/shopapi/virtualgoods/addGoods', {data: params})
}

/**
 * 编辑虚拟商品
 * @param {Object} params
 */
export function editVirtualGoods(params) {
	return request.post('/shopapi/virtualgoods/editGoods', {data: params})
}
/**
 * 新增电子卡密商品
 * @param {Object} params
 */
export function addVirtualCardGoods(params) {
	return request.post('/virtualcard/shopapi/virtualgoods/addGoods', {data: params})
}

/**
 * 编辑电子卡密商品
 * @param {Object} params
 */
export function editVirtualCardGoods(params) {
	return request.post('/virtualcard/shopapi/virtualgoods/editGoods', {data: params})
}

/**
 * 上架商品
 * @param {Object} params
 */
export function onGoods(params) {
	return request.post('/shopapi/goods/onGoods', {data:params})
}

/**
 * 下架商品
 * @param {Object} params
 */
export function offGoods(params) {
	return request.post('/shopapi/goods/offGoods', {data: params})
}

/**
 * 复制商品
 * @param  goods_id
 */
export function copyGoods(goods_id) {
	return request.post('/shopapi/goods/copyGoods', {data: {goods_id}})
}

/**
 * 
 * @param  goods_id
 */
export function getVerifyStateRemark(goods_id) {
	return request.post('/shopapi/goods/getVerifyStateRemark', {data: {goods_id}})
}

/**
 * 删除商品
 * @param  goods_ids
 */
export function deleteGoods(goods_ids) {
	return request.post('/shopapi/goods/deleteGoods', {data: {goods_ids}})
}

/**
 * 获取商品对应规格库存信息
 * @param  goods_id
 */
export function getOutputListById(goods_id) {
	return request.post('/shopapi/goods/getOutputList', {data: {goods_id}})
}

/**
 * 编辑商品对应规格库存信息
 * @param  {Object} params
 */
export function editOutputList(params) {
	return request.post('/shopapi/goods/editGoodsStock', {data: params})
}

/**
 * 获取商品参数模板
 */
export function getAttrClassList() {
	return request.get('/shopapi/goods/getAttrClassList')
}
/**
 * 获取商品参数模板对应参数
 * @param  attr_class_id
 */
export function getAttributeListById(attr_class_id) {
	return request.post('/shopapi/goods/getAttributeList', {data: {attr_class_id}})
}
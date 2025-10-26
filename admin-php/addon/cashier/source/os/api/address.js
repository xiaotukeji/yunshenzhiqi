import request from '@/common/js/http'

/**
 * 获取地址数据
 * @param {Object} params
 */
export function getAreaList(params) {
	return request.post('/cashier/storeapi/address/arealist', {data: params})
}

/**
 * 通过经纬度查询地址信息(地址名称)
 */
export function getTranAddressInfo(position) {
	return request.post('/cashier/storeapi/address/tranaddressinfo', {data: {latlng: position}})
}

/**
 * 通过地址名称查询详细地址信息(经纬度)
 * @param {Object} name
 */
export function getAddressByName(name) {
	return request.post('/cashier/storeapi/address/getaddressbyname', {data: {address: name}})
}

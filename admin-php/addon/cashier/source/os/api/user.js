import request from '@/common/js/http'

/**
 * 获取员工列表
 * @param {Object} params
 */
export function getUserList(params) {
	return request.post('/cashier/storeapi/user/lists', {data: params})
}

/**
 * 获取员工详情
 * @param {Object} uid
 */
export function getUserDetail(uid) {
	var params = {};
	if (uid) params.uid = uid;
	return request.post('/cashier/storeapi/user/userinfo', {data: params})
}

/**
 * 获取所有用户组（角色）
 */
export function getAllGroups() {
	return request.post('/cashier/storeapi/user/group')
}

/**
 * 添加员工
 * @param {Object} params
 */
export function addUser(params) {
	return request.post('/cashier/storeapi/user/adduser', {data: params})
}

/**
 * 编辑员工
 * @param {Object} params
 */
export function editUser(params) {
	return request.post('/cashier/storeapi/user/edituser', {data: params})
}

/**
 * 删除员工
 * @param {Object} uid
 */
export function deleteUser(uid) {
	return request.post('/cashier/storeapi/user/deleteuser', {data: {uid}})
}

/**
 * 获取门店用户权限
 */
export function getUserGroupAuth() {
	return request.post('/cashier/storeapi/user/usergroupauth')
}

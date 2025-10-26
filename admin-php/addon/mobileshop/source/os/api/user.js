import request from '@/common/js/http'

/**
 * 获取用户权限
 */
export function getUserPermission() {
	return request.get('/shopapi/user/permission')
}
/**
 * 获取用户分组
 */
export function getUserGroupList() {
	return request.get('/shopapi/user/groupList')
}

/**
 * 获取用户列表
 * @param {Object} params
 */
export function getUserList(params) {
	return request.post('/shopapi/user/user', {data: params})
}

/**
 * 获取用户详情
 * @param  uid
 */
export function getUserInfoById(uid) {
	return request.post('/shopapi/user/info', {data: {uid}})
}



/**
 * 新增用户
 * @param {Object} params
 */
export function addUser(params) {
	return request.post('/shopapi/user/addUser', {data: params})
}

/**
 * 编辑用户
 * @param {Object} params
 */
export function editUser(params) {
	return request.post('/shopapi/user/editUser', {data: params})
}

/**
 * 编辑用户密码
 * @param {Object} params
 */
export function editUserPassword(params) {
	return request.post('/shopapi/user/modifyPassword', {data: params})
}

/**
 * 删除用户
 * @param  uid
 */
export function deleteUser(uid) {
	return request.post('/shopapi/user/deleteUser', {data: {uid}})
}

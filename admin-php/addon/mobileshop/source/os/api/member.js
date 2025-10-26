import request from '@/common/js/http'

/**
 * 获取会员列表
 * @param {Object} params
 */
export function getMemberList(params) {
	return request.post('/shopapi/member/lists', {data: params})
}

/**
 * 获取会员基础信息
 * @param member_id
 */
export function getMemberInfoById(member_id) {
	return request.post('/shopapi/member/detail', {data: {member_id}})
}

/**
 * 获取会员账户信息
 * @param {Object} params
 */
export function getMemberAccountList(params) {
	return request.post('/shopapi/member/memberAccountList', {data: params})
}

/**
 * 获取会员订单信息
 * @param {Object} params
 */
export function getMemberOrderList(params) {
	return request.post('/shopapi/member/orderList', {data: params})
}

/**
 * 修改会员基础信息
 * @param {Object} params
 */
export function editMember(params) {
	return request.post('/shopapi/member/editMember', {data: params})
}

/**
 * 设置会员黑白名单
 * @param {Object} params
 */
export function editMemberJoinBlacklist(params) {
	return request.post('/shopapi/member/joinBlacklist', {data: params})
}
/**
 * 修改会员密码
 * @param {Object} params
 */
export function modifyMemberPassword(params) {
	return request.post('/shopapi/member/modifyMemberPassword', {data: params})
}

/**
 * 调整会员积分
 * @param {Object} params
 */
export function modifyPoint(params) {
	return request.post('/shopapi/Member/modifyPoint', {data: params})
}

/**
 * 调整会员储值余额
 * @param {Object} params
 */
export function modifyBalance(params) {
	return request.post('/shopapi/Member/modifyBalance', {data: params})
}

/**
 * 调整会员现金余额
 * @param {Object} params
 */
export function modifyBalanceMoney(params) {
	return request.post('/shopapi/Member/modifyBalanceMoney', {data: params})
}

/**
 * 调整会员成长值
 * @param {Object} params
 */
export function modifyGrowth(params) {
	return request.post('/shopapi/Member/modifyGrowth', {data: params})
}

import request from '@/common/js/http'

/**
 * 获取会员信息
 * @param {Object} member_id
 */
export function getMemberInfoById(member_id) {
	return request.post('/cashier/storeapi/member/info', {data: {member_id}})
}

/**
 * 获取会员信息（通过search_text）
 * @param params
 * @returns {*}
 */
export function getMemberInfoBySearchMember(params) {
	return request.post('/cashier/storeapi/member/searchmember', {data: params})
}

/**
 * 获取会员列表
 * @param {Object} params
 */
export function getMemberList(params) {
	return request.post('/cashier/storeapi/member/lists', {data: params})
}

/**
 * 获取会员等级
 */
export function getMemberLevelList() {
	return request.post('/cashier/storeapi/memberlevel/lists')
}

/**
 * 添加会员
 * @param {Object} params
 */
export function addMember(params) {
	return request.post('/cashier/storeapi/member/addmember', {data: params})
}

/**
 * 编辑会员
 * @param {Object} params
 */
export function editMember(params) {
	return request.post('/cashier/storeapi/member/editmember', {data: params})
}

/**
 * 获取会员卡包列表
 * @param {Object} params
 */
export function getMemberCardList(params) {
	return request.post('/cardservice/storeapi/membercard/lists', {data: params})
}

/**
 * 获取会员卡包详情
 * @param {Object} params
 */
export function getMemberCardDetail(params) {
	return request.post('/cardservice/storeapi/membercard/detail', {data: params})
}

/**
 * 获取可发放优惠券列表
 * @param {Object} params
 */
export function getCouponTypeList(params) {
	return request.post('/coupon/storeapi/coupon/getStoreCouponTypeList', {data: params})
}

/**
 * 发放优惠券
 * @param {Object} params
 */
export function sendMemberCoupon(params) {
	return request.post('/cashier/storeapi/member/sendCoupon', {data: params})
}

/**
 * 调整会员积分
 * @param {Object} params
 */
export function modifyMemberPoint(params) {
	return request.post('/cashier/storeapi/member/modifypoint', {data: params})
}

/**
 * 调整会员余额
 * @param {Object} params
 */
export function modifyMemberBalance(params) {
	return request.post('/cashier/storeapi/member/modifybalance', {data: params})
}

/**
 * 调整会员成长值
 * @param {Object} params
 */
export function modifyMemberGrowth(params) {
	return request.post('/cashier/storeapi/member/modifygrowth', {data: params})
}

/**
 * 办理会员卡
 * @param {Object} params
 */
export function applyingMembershipCard(params) {
	return request.post('/cashier/storeapi/member/handleMember', {data: params})
}

/**
 * 发送短信验证码
 * @param {Object} member_id
 */
export function sendMemberVerifyCode(member_id) {
	return request.post('/cashier/storeapi/member/memberverifycode', {data: {member_id}})
}

/**
 * 验证短信验证码
 * @param {Object} params
 */
export function checkMemberVerifyCode(params) {
	return request.post('/cashier/storeapi/member/checksmscode', {data: params})
}

/**
 * 根据手机号查询会员，支持模糊
 * @param params
 * @returns {*}
 */
export function searchMemberByMobile(params) {
	return request.post('/cashier/storeapi/member/searchMemberByMobile', {data: params})
}
// 充值数据持久化存储

const state = {
	active: '', // 记录当前页面的活跃值
};

const mutations = {
	setActive(state, value) {
		state.active = value;
		if (value) {
			uni.setStorageSync('rechagreActive', state.active);
		} else {
			uni.removeStorageSync('rechagreActive');
		}
	}
};

const actions = {};

export default {
	namespaced: true,
	state,
	mutations,
	actions
}
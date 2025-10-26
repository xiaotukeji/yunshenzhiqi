// 售卡数据持久化存储

const state = {
	goodsData: {},
	orderData: {
		pay_money: 0,
		goods_list: [],
		remark: '',
		goods_num: 0,
		card_type: 'oncecard',
		create_time: 0,
		order_key: '',
		order_id: 0
	},
	active: '', // 记录当前页面的活跃值
};

const mutations = {
	setGoodsData(state, value) {
		state.goodsData = value;
		if (value) {
			uni.setStorageSync('buyCardGoodsData', state.goodsData);
		} else {
			uni.removeStorageSync('buyCardGoodsData');
		}
	},
	setOrderData(state, value) {
		if (value) {
			for (let key in value) {
				if (state.orderData[key] != undefined) state.orderData[key] = value[key];
			}
			uni.setStorageSync('buyCardOrderData', state.orderData);
		} else {
			uni.removeStorageSync('buyCardOrderData');
		}
	},
	setActive(state, value) {
		state.active = value;
		if (value) {
			uni.setStorageSync('buyCardActive', state.active);
		} else {
			uni.removeStorageSync('buyCardActive');
		}
	},
};

const actions = {};

export default {
	namespaced: true,
	state,
	mutations,
	actions
}
// 开单数据持久化存储

const state = {
	goodsData: {},
	orderData: {
		goods_num: 0,
		pay_money: 0,
		goods_list: [],
		remark: '',
		create_time: 0,
		order_key: '',
		order_id: 0
	},
	goodsIds: [],
	pendOrderNum: 0,
	active: '', // 记录当前页面的活跃值
	isScanTrigger: false, // 扫码枪是否触发
	pendOrderId: 0,
	isShowCashBox: false
};

const mutations = {
	setGoodsIds(state, value) {
		state.goodsIds = value;
		if (value) {
			uni.setStorageSync('billingGoodsIds', state.goodsIds);
		} else {
			uni.removeStorageSync('billingGoodsIds');
		}
	},
	setGoodsData(state, value) {
		state.goodsData = value;
		if (value) {
			uni.setStorageSync('billingGoodsData', state.goodsData);
		} else {
			uni.removeStorageSync('billingGoodsData');
		}
	},
	setOrderData(state, value) {
		if (value) {
			for (let key in value) {
				if (state.orderData[key] != undefined) state.orderData[key] = value[key];
			}
			uni.setStorageSync('billingOrderData', state.orderData);
		} else {
			uni.removeStorageSync('billingOrderData');
		}
	},
	setPendOrderNum(state, value) {
		state.pendOrderNum = value;
	},
	setActive(state, value) {
		state.active = value;
		if (value) {
			uni.setStorageSync('billingActive', state.active);
		} else {
			uni.removeStorageSync('billingActive');
		}
	},
	setIsScanTrigger(state, value) {
		state.isScanTrigger = value;
		uni.setStorageSync('billingIsScanTrigger', state.isScanTrigger);
	},
	setPendOrderId(state, value) {
		state.pendOrderId = value;
		uni.setStorageSync('pendOrderId', state.pendOrderId);
	},
	setIsShowCashBox(state, value) {
		state.isShowCashBox = value;
		uni.setStorageSync('isShowCashBox', state.isShowCashBox);
	},

};

const actions = {};

export default {
	namespaced: true,
	state,
	mutations,
	actions
}
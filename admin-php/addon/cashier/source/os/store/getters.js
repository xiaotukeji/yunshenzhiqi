const getters = {
	rootSize: state => state.app.rootSize,
	firstMenuIndex: state => state.app.firstMenuIndex,
	secondMenuIndex: state => state.app.secondMenuIndex,
	thirdMenuIndex: state => state.app.thirdMenuIndex,
	currRoute: state => state.app.currRoute,
	globalMemberId: state => state.app.globalMemberId,
	globalMemberInfo: state => {
		let info = state.app.globalMemberInfo;
		if (info) {
			if (state.app.userInfo && state.app.userInfo.is_admin == 0 && info.mobile) {
				// 非管理员，不能查看会员手机号
				if (info.mobile.indexOf('****') == -1) info.originalMobile = info.mobile;
				info.mobile = info.mobile.substring(0, 4 - 1) + '****' + info.mobile.substring(6 + 1);
			}
		}
		return info;
	},
	globalStoreId: state => state.app.globalStoreId,
	globalStoreInfo: state => state.app.globalStoreInfo,
	userInfo: state => state.app.userInfo,
	addon: state => state.app.addon,
	menu: state => state.app.menu,
	defaultImg: state => state.app.defaultImg,
	themeConfig: state => state.app.themeConfig,
	memberSearchWayConfig: state => state.app.memberSearchWayConfig,

	// 开单
	pendOrderNum: state => state.billing.pendOrderNum,
	billingGoodsData: state => state.billing.goodsData,
	billingOrderData: state => state.billing.orderData,
	billingGoodsIds: state => state.billing.goodsIds,
	billingActive: state => state.billing.active,
	billingIsScanTrigger: state => state.billing.isScanTrigger,
	billingPendOrderId: state => state.billing.pendOrderId,
	billingIsShowCashBox: state => state.billing.isShowCashBox,

	// 售卡
	buyCardGoodsData: state => state.buycard.goodsData,
	buyCardOrderData: state => state.buycard.orderData,
	buyCardActive: state => state.buycard.active,

	// 充值
	rechargeActive: state => state.recharge.active,

};

export default getters;
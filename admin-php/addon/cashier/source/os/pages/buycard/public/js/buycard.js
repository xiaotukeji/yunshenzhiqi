import {cardCalculate, cardCreate} from '@/api/order_create.js'
import {mapGetters} from 'vuex';

export default {
	data() {
		return {
			type: 'goods',
			isRepeat: false,
			outTradeNo: ''
		};
	},
	computed: {
		...mapGetters(['buyCardGoodsData', 'buyCardOrderData', 'buyCardActive', 'memberSearchWayConfig'])
	},
	watch: {
		globalMemberInfo: function (nVal) {
			this.calculation();
		},
		buyCardGoodsData: {
			// 每个属性值发生变化就会调用这个函数
			handler(newVal, oldVal) {
				this.calculation();
			},
			// 深度监听 属性的变化
			deep: true
		}
	},
	onLoad(option) {
		uni.hideTabBar();
		this.$store.commit('buycard/setOrderData', {
			card_type: option.type || 'oncecard'
		});
		if (this.globalMemberInfo) this.type = 'goods';
	},
	onShow() {
		this.$store.commit('buycard/setOrderData', {
			create_time: this.$util.timeFormat(parseInt(new Date().getTime() / 1000))
		});

		if (this.$refs.card) this.$refs.card.init();
		
		this.calculation();

		this.addKeyDownEvent();

		// 添加组件的键盘监听事件
		if (this.$refs.payment) this.$refs.payment.addKeyDownEvent();
	},
	onHide() {
		this.removeKeyDownEvent();

		// 移除组件的键盘监听事件
		this.$refs.payment.removeKeyDownEvent();
	},
	methods: {
		switchStoreAfter() {
			if (this.$refs.card) this.$refs.card.init();
			this.calculation();
		},
		openMember() {
			if (this.$refs.selectMember) {
				this.$store.commit('buycard/setActive', 'ShowMember');
				this.$refs.selectMember.open(() => {
					this.$store.commit('buycard/setActive', 'ShowMemberAfter');
				});
			}
		},
		showMember() {
			this.$store.commit('buycard/setActive', 'ShowMember');
			if (!this.globalMemberInfo) {
				if (this.$refs.selectMember) this.$refs.selectMember.open(() => {
					this.$store.commit('buycard/setActive', 'ShowMemberAfter');
				});
			} else {
				// 打开会员信息弹出框
				this.$store.commit('buycard/setActive', 'ShowMemberAfter');
				this.$refs.memberDetailPopup.open();
			}
		},
		/**
		 * 切换散客
		 */
		replaceMember() {
			this.$store.commit('app/setGlobalMemberInfo', null);
			this.type = 'goods';
		},
		calculation() {
			if (!Object.keys(this.buyCardGoodsData).length) return;
			let sku_array = [];
			Object.keys(this.buyCardGoodsData).forEach(key => {
				let item = this.buyCardGoodsData[key];
				sku_array.push({
					sku_id: item.sku_id,
					num: item.num
				});
			});
			let data = {
				sku_array: JSON.stringify(sku_array),
				create_time: this.buyCardOrderData.create_time
			};
			if (this.globalMemberInfo) data.member_id = this.globalMemberInfo.member_id;
			cardCalculate(data).then(res => {
				if (res.code == 0) {
					this.$store.commit('buycard/setOrderData', res.data);
				} else {
					this.$util.showToast({
						title: res.message
					});
				}
			})
		},
		inc(data) {
			let _buyCardGoodsData = this.$util.deepClone(this.buyCardGoodsData);

			if (data.goods_type != '') {
				_buyCardGoodsData['sku_' + data.sku_id].num += 1;
			} else {
				if (data.num < data.stock) _buyCardGoodsData['sku_' + data.sku_id].num += 1;
			}
			this.$store.commit('buycard/setGoodsData', _buyCardGoodsData);
		},
		dec(data) {
			if (data.num > 1) {
				let _buyCardGoodsData = this.$util.deepClone(this.buyCardGoodsData);
				_buyCardGoodsData['sku_' + data.sku_id].num -= 1;
				this.$store.commit('buycard/setGoodsData', _buyCardGoodsData);
			}
		},
		deleteGoods(data) {
			let _buyCardGoodsData = this.$util.deepClone(this.buyCardGoodsData);

			delete _buyCardGoodsData['sku_' + data.sku_id];
			this.$store.commit('buycard/setGoodsData', _buyCardGoodsData);

			if (!Object.keys(_buyCardGoodsData).length) {
				this.$store.commit('buycard/setOrderData', {
					goods_list: [],
					goods_num: 0,
					pay_money: 0
				});
			}
		},
		clearGoods() {
			this.$store.commit('buycard/setGoodsData', {});
			this.$store.commit('buycard/setOrderData', {
				goods_list: [],
				goods_num: 0,
				pay_money: 0
			});
		},
		pay(type = '', callback) {
			if (!this.globalMemberInfo) {
				if (this.$refs.selectMember) {
					this.$store.commit('buycard/setActive', 'ShowMember');
					this.$refs.selectMember.open(() => {
						this.$store.commit('buycard/setActive', 'ShowMemberAfter');
					});
					setTimeout(() => {
						this.$refs.selectMember.inputFocus = true;
					}, 200);
				}
				return false;
			}

			if (!Object.keys(this.buyCardGoodsData).length || this.isRepeat) return;
			this.isRepeat = true;

			if (this.outTradeNo) {
				this.type = 'pay';
				if (type) this.$refs.payment.type = type;
				return;
			}

			this.$store.commit('buycard/setActive', 'OrderCreate'); // 记录页面活跃值：创建订单

			let sku_array = [];
			Object.keys(this.buyCardGoodsData).forEach(key => {
				let item = this.buyCardGoodsData[key];
				sku_array.push({
					sku_id: item.sku_id,
					num: item.num
				});
			});
			let data = {
				sku_array: JSON.stringify(sku_array),
				remark: this.buyCardOrderData.remark,
				create_time: this.buyCardOrderData.create_time,
				order_key: this.buyCardOrderData.order_key
			};
			if (this.globalMemberInfo) data.member_id = this.globalMemberInfo.member_id;
			cardCreate(data).then(res => {
				this.isRepeat = false;
				if (res.code == 0) {
					this.outTradeNo = res.data.out_trade_no;
					this.type = 'pay';
					if (type) this.$refs.payment.type = type;
					setTimeout(() => {
						if (callback) callback();
					}, 100)
				} else {
					this.$util.showToast({
						title: res.message
					});
				}
			});
		},
		cancelPayment() {
			this.outTradeNo = '';
			this.type = 'goods';
		},
		paySuccess() {
			this.type = 'goods';
			this.isRepeat = false;
			this.$store.commit('buycard/setActive', '');
			this.wholeOrderCancel();
			this.$refs.card.onceCardData.page = 0;
			this.$refs.card.onceCardData.total = 1;
			this.$refs.card.timeCardData.page = 0;
			this.$refs.card.timeCardData.total = 1;
			this.$refs.card.commonCardData.page = 0;
			this.$refs.card.commonCardData.total = 1;
			this.$refs.card.getOnceCard();
			this.$refs.card.getTimeCard();
			this.$refs.card.getCommonCard();
		},
		/**
		 * 整单取消
		 */
		wholeOrderCancel() {
			if (Object.keys(this.buyCardGoodsData).length) {
				// 清除商品数据
				this.$store.commit('buycard/setGoodsData', {});
				let orderId = this.billingOrderData && (this.billingOrderData.order_id ? this.billingOrderData.order_id : 0) || 0
				
				// 清除订单数据
				this.$store.commit('buycard/setOrderData', {
					goods_num: 0,
					pay_money: 0,
					goods_list: [],
					remark: '',
					create_time: this.$util.timeFormat(parseInt(new Date().getTime() / 1000)),
					order_id: orderId
				});
				this.outTradeNo = '';
			}
		},
		toGoods() {
			this.type = 'goods';
		},
		/**
		 * 添加键盘监听事件
		 */
		addKeyDownEvent() {
			// #ifdef H5
			// 绑定监听事件
			window.addEventListener("keydown", this.listenerKeyDown, true);

			// 监听F1~F12，BACKSPACE
			window.POS_HOTKEY_CALLBACK = (control, code) => {
				this.posHotKeyCallback(code);
			};
			// #endif
		},
		/**
		 * 移除键盘监听事件
		 */
		removeKeyDownEvent() {
			// #ifdef H5
			window.removeEventListener("keydown", this.listenerKeyDown, true);

			delete window.POS_HOTKEY_CALLBACK;
			// #endif
		},
		listenerKeyDown(e) {
			var code = e.code;

			// console.log('KeyDown', this.type, code, this.buyCardActive, e);

			if (this.type != 'pay' && code == 'KeyM') {
				// 选择会员，键盘快捷键【M】
				this.openMember();

			} else if(this.buyCardActive == 'ShowMember' && this.memberSearchWayConfig.way == 'list'){

				// 按照会员列表进行搜索
				if (code == 'Enter' || code == 'NumpadEnter') {

					if(this.$refs.selectMember.searchFinish && this.$refs.selectMember.memberId){
						this.$refs.selectMember.getMemberInfo(this.$refs.selectMember.memberId);
					}
				}

			} else if (this.buyCardActive == 'ShowMemberAfter') {
				// 活跃窗口：设置会员后

				if (code == 'Enter' || code == 'NumpadEnter') {
					this.pay('');
				}

			} else if (this.buyCardOrderData.goods_num && this.buyCardActive == 'SelectGoodsAfter') {
				// 选择卡项商品后
				if (code == 'Enter' || code == 'NumpadEnter') {
					this.pay('');
				}

			}

		},
		/**
		 * 监听键盘事件回调
		 * @param {Object} code
		 */
		posHotKeyCallback(code) {
			if (code == 'F2') {
				// 选择卡项

				if (this.type != 'pay') {
					this.toGoods();
					this.$store.commit('buycard/setActive', 'SelectGoodsAfter');
				}

			} else if (code == 'F3') {
				// 选择会员

				if (this.type != 'pay') {
					this.showMember();
				}

			} else if (code == 'BACKSPACE') {
				// 退格键

				if (this.buyCardActive == 'OrderCreate') {
					if (this.$refs.payment) {
						if (this.$refs.payment.active == 'openMoneyPopup') {
							this.$refs.payment.deleteCode();
						}
					}
				}

			} else {
				// 触发左侧菜单按键回调
				this.menuTriggerKeyCodeCallBack(code);
			}
		}
	}
}
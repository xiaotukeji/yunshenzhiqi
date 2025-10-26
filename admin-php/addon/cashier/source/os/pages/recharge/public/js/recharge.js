import {getRechargeConfig, addRecharge} from '@/api/recharge'
import {getMemberInfoById} from '@/api/member'
import {mapGetters} from 'vuex';

export default {
	data() {
		return {
			type: 'member',
			//充值卡相关
			memberRecharge: [],
			rechargeMoney: 0,
			rechargeType: 1,
			rechargeIndex: 0,
			rechargeTypeList: [{
				text: '充值套餐',
				value: 1
			}, {
				text: '自定义金额',
				value: 2
			}],
			outTradeNo: '',
			isRepeat: false
		};
	},
	computed: {
		reward() {
			if (this.rechargeType == 1 && this.memberRecharge.length > 0 && this.memberRecharge[this.rechargeIndex]) {
				let data = this.memberRecharge[this.rechargeIndex];
				if (data.point || data.growth || data.coupon) return true;
			}
			return false;
		},
		...mapGetters(['rechargeActive','memberSearchWayConfig'])
	},
	onLoad() {
		uni.hideTabBar();
	},
	onShow() {
		this.create_time = this.$util.timeFormat(parseInt(new Date().getTime() / 1000));
		this.addKeyDownEvent();

		// 添加组件的键盘监听事件
		if (this.$refs.payment) this.$refs.payment.addKeyDownEvent();

		this.getMemberRecharge();
	},
	onHide() {
		this.removeKeyDownEvent();

		// 移除组件的键盘监听事件
		if (this.$refs.payment) this.$refs.payment.removeKeyDownEvent();
	},
	methods: {
		//充值卡相关
		getMemberRecharge() {
			getRechargeConfig().then(res => {
				if (res.code == 0 && res.data) {
					this.memberRecharge = res.data;
					if (this.memberRecharge.length > 0) {
						for (let i in this.memberRecharge) this.memberRecharge[i]['money'] = this.memberRecharge[i]['price'];
					} else {
						this.rechargeType = 2;
						this.rechargeTypeList[0].disable = true;
					}
				}
			});
		},
		getMemberInfo() {
			getMemberInfoById(this.globalMemberInfo.member_id).then(res => {
				if (res.code == 0 && res.data) {
					this.$store.commit('app/setGlobalMemberInfo', res.data);
				} else {
					this.$util.showToast({
						title: '未获取到会员信息'
					});
				}
			});
		},
		openMember() {
			if (this.$refs.selectMember) {
				this.$store.commit('recharge/setActive', 'ShowMember');
				this.$refs.selectMember.open(() => {
					this.$store.commit('recharge/setActive', 'ShowMemberAfter');
				});
				setTimeout(() => {
					this.$refs.selectMember.inputFocus = true;
				}, 200);
			}
		},
		showMember() {
			this.$store.commit('recharge/setActive', 'ShowMember');
			if (!this.globalMemberInfo) {
				if (this.$refs.selectMember) this.$refs.selectMember.open(() => {
					this.$store.commit('recharge/setActive', 'ShowMemberAfter');
				});
			} else {
				// 打开会员信息弹出框
				this.$store.commit('recharge/setActive', 'ShowMemberAfter');
				this.$refs.memberDetailPopup.open();
			}
		},
		pay() {
			if (!this.globalMemberInfo || (this.globalMemberInfo && !this.globalMemberInfo.member_id)) {
				this.type = 'member';
				this.openMember();
				return false;
			}

			if (this.rechargeType == 1 && !this.memberRecharge[this.rechargeIndex]) {
				this.$util.showToast({
					title: '请选择充值套餐'
				});
				return;
			}
			var isValid = /^-?\d+(\.\d{1,2})?$/;
			if (this.rechargeType == 2 && !isValid.test(this.rechargeMoney)) {
				this.$util.showToast({
					title: '请输入正确的充值金额'
				});
				return;
			}

			let data = {
				member_id: this.globalMemberInfo.member_id
			};
			if (this.rechargeType == 1) {
				data.sku_array = [{
					recharge_id: this.memberRecharge[this.rechargeIndex].recharge_id
				}];
			} else {
				data.sku_array = [{
					money: this.rechargeMoney
				}];
			}
			data.sku_array = JSON.stringify(data.sku_array);

			this.$store.commit('recharge/setActive', 'OrderCreate');

			if (this.isRepeat) return;
			this.isRepeat = true;
			addRecharge(data).then(res => {
				this.isRepeat = false;
				if (res.code == 0) {
					this.outTradeNo = res.data.out_trade_no;
					this.type = 'pay';
				} else {
					this.$util.showToast({
						title: res.message
					});
				}
			});
		},
		cancelPayment() {
			this.type = 'member';
		},
		paySuccess() {
			this.type = 'member';
			this.outTradeNo = '';
			this.getMemberInfo();

			this.$store.commit('recharge/setActive', '');
			this.isRepeat = false;
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

			// console.log('KeyDown', this.type, code, this.rechargeActive, e);

			if (this.rechargeActive == '' && code == 'KeyM') {
				// 选择会员，键盘快捷键【M】

				this.openMember();

			} else if(this.rechargeActive == 'ShowMember' && this.memberSearchWayConfig.way == 'list'){

				// 按照会员列表进行搜索
				if (code == 'Enter' || code == 'NumpadEnter') {

					if(this.$refs.selectMember.searchFinish && this.$refs.selectMember.memberId){
						this.$refs.selectMember.getMemberInfo(this.$refs.selectMember.memberId);
					}
				}

			} else if (this.rechargeActive == 'ShowMemberAfter' || (this.type == 'member' && this.rechargeActive == '')) {
				// 活跃窗口：设置会员后，选择充值金额

				if (code == 'Enter' || code == 'NumpadEnter') {
					this.pay();
				}

			}

		},
		/**
		 * 监听键盘事件回调
		 * @param {Object} code
		 */
		posHotKeyCallback(code) {
			if (code == 'BACKSPACE') {
				// 退格键
				if (this.rechargeActive == 'OrderCreate') {
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
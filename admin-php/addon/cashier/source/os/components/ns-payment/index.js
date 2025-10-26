import {payCalculate} from '@/api/order_create'
import {getOrderInfoById, orderRemark} from '@/api/order'
import {
	getPayQrcode,
	addPayCashierPay,
	authCodepay,
	getPayType,
	getCashierPayInfo,
	cashierConfirm,
	checkPaymentCode
} from '@/api/oder_pay'
import {orderPrintTicket} from '@/api/order.js'
import {sendMemberVerifyCode, checkMemberVerifyCode} from '@/api/member'
import {mapGetters} from 'vuex';

export default {
	props: {
		outTradeNo: {
			type: String,
			default: ''
		},
		storeRoute: {
			type: String,
			default: ''
		}
	},
	data() {
		return {
			type: 'third',
			payType: {
				third: {
					type: 'third',
					name: '付款码',
					icon: 'iconsaomaqiang',
					background: '#f7931e',
					hotKey: 'N',
					KeyCode: 'KeyN'
				},
				cash: {
					type: 'cash',
					name: '现金',
					icon: 'iconxianjin1',
					background: '#f5b719',
					hotKey: 'C',
					KeyCode: 'KeyC'
				},
				own_wechatpay: {
					type: 'own_wechatpay',
					name: '微信',
					icon: 'iconwxpay',
					background: '#09bb07',
					hotKey: 'W',
					KeyCode: 'KeyW'
				},
				own_alipay: {
					type: 'own_alipay',
					name: '支付宝',
					icon: 'iconzhifubaozhifu',
					background: '#1890ff',
					hotKey: 'A',
					KeyCode: 'KeyA'
				},
				own_pos: {
					type: 'own_pos',
					name: 'POS刷卡',
					icon: 'iconyinhangqia',
					background: '#ec6a55',
					hotKey: 'P',
					KeyCode: 'KeyP'
				}
			},
			payInfo: null,
			payStatus: 'pay',
			isRepeat: false,
			qrcodeShow: false,
			payQrcode: [],
			timer: null,
			moneyPopup: {
				money: 0,
				type: '',
				title: ''
			},
			cash: 0,
			discount: {},
			thirdPopupOpen:false,
			scanCodeType: 'scancode',
			scanCodeFocus: true,
			authCode: '',
			autoComplete: {
				time: 8,
				timer: null
			},
			remark: '',
			autoPrintTicket: true,
			balanceSafeVerify: false, // 余额使用安全验证
			dynacodeData: {
				key: '',
				seconds: 120,
				timer: null,
				codeText: '获取验证码',
				isSend: false
			},
			smsCode: '',
			paymentCode: '',
			safeVerifyType: 'payment_code',
			_outTradeNo: '',
			active: '' ,// 活动值
			//扫码枪配置
			scancodeList:''
		}
	},
	computed: {
		balance() {
			if (this.globalMemberInfo) {
				return parseFloat(this.globalMemberInfo.balance_money) + parseFloat(this.globalMemberInfo.balance);
			}
			return 0;
		},
		promotionShow() {
			if (this.payInfo && (this.payInfo.offset.coupon_array || this.payInfo.collectmoney_config.reduction == 1)) {
				return true;
			}
			return false;
		},
		...mapGetters(['billingActive', 'billingIsScanTrigger', 'buyCardActive', 'rechargeActive'])
	},
	created() {
		this._outTradeNo = this.outTradeNo;
		if (this._outTradeNo) this.calculation();
		if (typeof uni.getStorageSync('payAutoPrintTicket') == 'boolean') this.autoPrintTicket = uni.getStorageSync('payAutoPrintTicket');

		window.POS_PRINT_CALLBACK = function (text) {
			uni.showToast({
				title: text,
				icon: 'none'
			})
		};
		this.addKeyDownEvent();
	},
	destroyed() {
		clearInterval(this.timer);
	},
	methods: {
		getPayTypeFn(){
			this.scancodeList = []
			getPayType().then((res)=>{
				this.scancodeList = res.data
				this.$refs.thirdPopup.open('', () => {
					this.active = '';
				});
				this.$forceUpdate()
			})
		},
		/**
		 * 取消支付
		 */
		cancelPayment() {
			this.$emit('cancel', {});
			this.clearPay();
		},
		/**
		 * 支付成功
		 */
		paySuccess() {
			this.$emit('success', {});
			this.clearPay();
		},
		clearPay() {
			clearInterval(this.timer);
			this.type = 'third';
			this.payInfo = null;
			this.payStatus = 'pay';
			this.payQrcode = [];
			this.cash = 0;
			this.discount = {};
			this.isRepeat = false;
			if (this.autoComplete.timer) clearInterval(this.autoComplete.timer);
			this.autoComplete.time = 8;
			this.remark = '';
			this.balanceSafeVerify = false;
			this.smsCode = '';
			this.paymentCode = '';
			this.safeVerifyType = 'payment_code';
			this.active = '';
			this.refreshDynacodeData();
		},
		confirm(callback) {
			if (this.type == 'cash') {
				setTimeout(() => {
					// 打开付款码，设置焦点
					this.scanCodeFocus = true;
				}, 200);
				if (!this.cash) {
					this.cash = this.payInfo.pay_money;
				} else if (isNaN(parseFloat(this.cash))) {
					this.$util.showToast({
						title: '现金收款金额错误'
					});
					return;
				} else if (parseFloat(this.cash) < parseFloat(this.payInfo.pay_money)) {
					this.$util.showToast({
						title: '现金收款金额不能小于支付金额'
					});
					return;
				}
			}

			if (this.isRepeat) return;
			this.isRepeat = true;

			uni.showLoading({});

			let data = {
				pay_type: this.type,
				out_trade_no: this._outTradeNo,
				member_id: this.globalMemberInfo ? this.globalMemberInfo.member_id : 0,
				promotion: JSON.stringify(this.$util.deepClone(this.discount)),
				cash: this.type == 'cash' ? this.cash : 0
			};

			cashierConfirm(data).then(res => {
				uni.hideLoading();
				if (res.code == 0) {
					this.payStatus = 'success';
					this.$emit('getMemberInfo');
					if (callback) callback();
				} else {
					this.isRepeat = false;
					this.$util.showToast({
						title: res.message
					})
				}
			}).catch(res => {
				uni.hideLoading();
			})
		},
		calculation(callback) {
			let data = {
				pay_type: this.type,
				out_trade_no: this._outTradeNo,
				member_id: this.globalMemberInfo ? this.globalMemberInfo.member_id : 0,
				promotion: JSON.stringify(this.$util.deepClone(this.discount)),
				cash: this.type == 'cash' ? this.cash : 0
			};

			payCalculate(data).then(res => {
				if (res.code == 0) {
					this.payInfo = res.data;
					if (this.payInfo.pay_status == 1) {
						this.payStatus = 'success';
					} else if (this.payInfo.pay_money == 0) {
						// 订单完成
						// if (this.discount.coupon_id) this.$refs.couponPopup.close();
						// this.confirm();
					}

					for (let key in this.payType) {
						if (this.payInfo.collectmoney_config.pay_type.indexOf(key) == -1) {
							delete this.payType[key];
						}
					}

					// 如果 付款码没有开启,则取第一项支付方式
					if (!this.payType[this.type]) {
						this.type = Object.keys(this.payType)[0];
					}

					if (callback) callback();
				} else {
					this.$util.showToast({
						title: res.message
					})
				}
			})
		},
		/**
		 * 打印小票
		 */
		printTicket() {
			orderPrintTicket(this.payInfo.order_id).then(res => {
				if (res.code == 0) {
					if (Object.values(res.data).length) {
						let data = Object.values(res.data);
						try {
							let print = {
								printer: []
							};
							data.forEach((item) => {
								print.printer.push({
									printer_type: item.printer_info.printer_type,
									host: item.printer_info.host,
									ip: item.printer_info.ip,
									port: item.printer_info.port,
									content: item.content,
									print_width: item.printer_info.print_width
								})
							});
							this.$pos.send('Print', JSON.stringify(print));
						} catch (e) {
							console.log('err', e, res.data)
						}
					} else {
						this.$util.showToast({
							title: '未开启收银小票打印'
						})
					}
				} else {
					this.$util.showToast({
						title: res.message ? res.message : '小票打印失败'
					})
				}
			})
		},
		thirdConfirm() {
			this.authCode = '';
			this.scanCodeType = 'scancode';
			this.scanCodeFocus = true;
			
			if (this.$refs.thirdPopup) {
				this.active = 'thirdConfirm';
				this.getPayTypeFn()
			}
		},
		/**
		 * 获取支付二维码
		 */
		getQrcode() {
			getPayQrcode(this._outTradeNo).then(res => {
				if (res.code == 0 && res.data.length) {
					this.payQrcode = res.data;
					this._outTradeNo = this.payQrcode[0].out_trade_no;
					this.checkPayStatus();
				}
			})
		},
		popupChange() {
			this.thirdPopupOpen = !this.thirdPopupOpen;
			if (this.timer) {
				clearInterval(this.timer);
			}
		},
		/**
		 * 扫码枪
		 */
		scanCode(e) {
			if (!e.detail.value) return;

			if (this.isRepeat) return;
			this.isRepeat = true;

			uni.showLoading({});

			addPayCashierPay(this._outTradeNo).then(res => {
				if (res.code == 0) {
					this._outTradeNo = res.data;
					this.calculation();
					authCodepay({
						out_trade_no: this._outTradeNo,
						auth_code: e.detail.value
					}).then(res => {
						this.authCode = '';
						// 扫码枪解除键盘占用
						this.$store.commit('billing/setIsScanTrigger', false);
						uni.hideLoading();
						if (res.code >= 0) {
							this.checkPayStatus();
							this.$refs.thirdPopup.close();
							this.payStatus = 'success';
						} else {
							//TODO 如果是支付已取消 要生成新的支付单号,但是这里的判断是否只适用于微信？
							if(res.data.err_code && res.data.err_code == 'TRADE_ERROR'){
								this.calculation('');
							}else{
								this.checkPayStatus();
								this.isRepeat = false;
								this.$util.showToast({
									title: res.message
								})
							}
						}
					})
				} else {
					uni.hideLoading();
					this.isRepeat = false;
					this.$util.showToast({
						title: res.message
					});
					// 扫码枪解除键盘占用
					this.$store.commit('billing/setIsScanTrigger', false);
				}
			})
		},
		//失焦事件
		scanCodeInputBlur(){
			this.scanCodeFocus = false;
			//强制聚焦处理
			if( ( this.thirdPopupOpen && this.scanCodeType == 'scancode') || this.active == 'memberCodePopup' ){
				this.$nextTick(() => {
				  this.scanCodeFocus = true;
				});
			}
		},
		// 清除付款码内容
		clearAuthCode() {
			this.authCode = '';
			// 扫码枪解除键盘占用
			this.$store.commit('billing/setIsScanTrigger', false);
		},
		/**
		 * 查询支付状态
		 */
		checkPayStatus() {
			clearTimeout(this.timer);
			this.timer = setInterval(() => {
				getCashierPayInfo(this._outTradeNo).then(res => {
					if (res.code == 0 && res.data) {
						if(res.data.pay_status == 2){
							// 查询订单状态
							getOrderInfoById(this.payInfo.order_id).then(res => {
								if (res.code == 0 && res.data) {
									if (res.data.order_status == -1 && res.data.close_cause) {
										// 订单关闭，显示退款原因
										this.$util.showToast({
											title: res.data.close_cause
										});
										clearInterval(this.timer)
									} else if (res.data.order_status == 10) {
										this.$refs.thirdPopup.close();
										this.payStatus = 'success';
										clearInterval(this.timer)
									}
								}
							})
						}else if(res.data.pay_status == -1){
							this.$util.showToast({
								title: '用户已取消支付',
							});
							clearInterval(this.timer);
							this.calculation('');
						}
					}
				})

			}, 1500)
		},
		/**
		 * 打开金额弹窗
		 * @param {Object} data
		 */
		openMoneyPopup(data) {
			this.moneyPopup = Object.assign(this.moneyPopup, data);
			if (this.$refs.moneyPopup) {
				this.active = 'OpenMoneyPopup';
				this.$refs.moneyPopup.open('', () => {
					this.active = '';
				});
			}
		},
		deleteCode() {
			this.moneyPopup.money = this.moneyPopup.money.substr(0, this.moneyPopup.money.length - 1);
		},
		moneyPopupConfirm(callback) {
			if (!this.moneyPopup.money.length) {
				this.$util.showToast({
					title: '请输入金额'
				});
				return;
			}
			if (this.moneyPopup.type == 'reduction') {
				this.discount.reduction = parseFloat(this.moneyPopup.money);
			} else if (this.moneyPopup.type == 'cash') {
				this.cash = parseFloat(this.moneyPopup.money);
			}
			this.calculation(callback);
			this.$refs.moneyPopup.close();
		},
		keydown(value) {
			let arr = this.moneyPopup.money.split('.');

			if (arr[1]) {
				if (value == '.' || arr[1].length == 2) return;
				if (value == '00' && arr[1].length == 1) value = '0';
			}
			if (this.moneyPopup.type == 'reduction' && parseFloat(this.moneyPopup.money + value) > parseFloat(this
				.payInfo.pay_money)) {
				this.$util.showToast({
					title: `减免金额不能超过订单金额￥${this.payInfo.pay_money}`
				});
				return;
			}
			if (parseFloat(this.moneyPopup.money + value) > 1000000) {
				this.$util.showToast({
					title: '最大不能超过1000000'
				});
				return;
			}
			this.moneyPopup.money += value;
		},
		/**
		 * 切换支付方式
		 * @param {Object} type
		 * @param {Object} callback
		 */
		switchPayType(type, callback) {
			this.type = type;
			if (type == 'cash') {
				if (this.cash) {
					this.openMoneyPopup({
						title: '收款金额',
						money: this.$util.moneyFormat(this.cash),
						type: 'cash'
					})
				} else {
					this.openMoneyPopup({
						title: '收款金额',
						money: this.$util.moneyFormat(this.payInfo.pay_money),
						type: 'cash'
					})
				}
			} else {
				this.calculation(callback);
			}
		},
		/**
		 * 减免金额
		 */
		reduction() {
			if (this.discount.reduction) {
				delete this.discount.reduction;
				this.calculation();
			} else {
				this.openMoneyPopup({
					title: '减免金额',
					money: '',
					type: 'reduction'
				})
			}
		},
		/**
		 * 使用积分
		 */
		usePoint() {
			if (this.payInfo.offset.point_array.point == 0) return;
			if (this.discount.is_use_point) {
				delete this.discount.is_use_point;
			} else {
				this.discount.is_use_point = 1;
			}
			this.calculation();
		},
		useBalance() {
			if (this.balance == 0) return;

			// 如果开启了余额安全验证
			if (this.payInfo.collectmoney_config.balance_safe == 1 && !this.balanceSafeVerify) {
				if (this.$refs.safeVerifyPopup) {
					this.active = 'safeVerifyPopup';
					this.$refs.safeVerifyPopup.open('', () => {
						this.active = '';
					});
					return;
				}
			}
			if (this.discount.is_use_balance) {
				delete this.discount.is_use_balance;
			} else {
				this.discount.is_use_balance = 1;
			}
			this.calculation();
		},
		selectCoupon() {
			if (!this.payInfo.offset.coupon_array.member_coupon_list.length) return;
			if (this.$refs.couponPopup) {
				this.active = 'couponPopup';
				this.$refs.couponPopup.open('', () => {
					this.active = '';
				});
			}
		},
		selectCouponItem(data) {
			if (!this.discount.coupon_id) {
				this.discount.coupon_id = data.coupon_id;
			} else if (this.discount.coupon_id != data.coupon_id) {
				this.discount.coupon_id = data.coupon_id;
			} else {
				delete this.discount.coupon_id;
			}
			this.$forceUpdate();
			this.calculation();
		},
		openRemark() {
			this.remark = this.payInfo.remark;
			if (this.$refs.remarkPopup) {
				this.active = 'RemarkPopup';
				this.$refs.remarkPopup.open('', () => {
					this.active = '';
				});
			}
		},
		/**
		 * 设置备注
		 */
		remarkConfirm() {
			if (!this.remark) return;
			orderRemark({
				order_id: this.payInfo.order_id,
				remark: this.remark
			}).then(res => {
				this.payInfo.remark = this.remark;
				this.$refs.remarkPopup.close();
			});
		},
		// 清除手机验证码内容
		clearSmsCode() {
			this.smsCode = '';
			// 扫码枪解除键盘占用
			this.$store.commit('billing/setIsScanTrigger', false);
		},
		/**
		 * 发送短信验证码
		 */
		sendMobileCode() {
			if (this.dynacodeData.seconds != 120 || this.dynacodeData.isSend) return;
			this.dynacodeData.isSend = true;

			this.dynacodeData.timer = setInterval(() => {
				this.dynacodeData.seconds--;
				this.dynacodeData.codeText = this.dynacodeData.seconds + 's后可重新获取';
			}, 1000);

			sendMemberVerifyCode(this.payInfo.member_id).then(res => {
				if (res.code >= 0) {
					this.dynacodeData.key = res.data.key;
					this.smsCode = '';
					this.dynacodeData.isSend = false;
				} else {
					this.$util.showToast({
						title: res.message
					});
					this.refreshDynacodeData();
				}
			}).catch(res => {
				this.$util.showToast({
					title: 'request:fail'
				});
				this.refreshDynacodeData();
			});
		},
		refreshDynacodeData() {
			clearInterval(this.dynacodeData.timer);
			this.dynacodeData = {
				key: '',
				seconds: 120,
				timer: null,
				codeText: '获取动态码',
				isSend: false
			};
		},
		/**
		 * 验证短信验证码是否正确
		 */
		verifySmsCode() {
			if (this.smsCode.trim() == '') {
				this.$util.showToast({
					title: '请输入验证码'
				});
				return;
			}
			if (this.isRepeat) return;
			this.isRepeat = true;
			checkMemberVerifyCode({
				key: this.dynacodeData.key,
				code: this.smsCode.trim()
			}).then(res => {
				if (res.code == 0) {
					this.balanceSafeVerify = true;
					this.$refs.safeVerifyPopup.close();
					this.useBalance();
				} else {
					this.$util.showToast({
						title: res.message
					});
				}
				this.isRepeat = false;
			})
		},
		// 清除付款码内容
		clearPaymentCode() {
			this.paymentCode = '';
			// 扫码枪解除键盘占用
			this.$store.commit('billing/setIsScanTrigger', false);
		},
		changeSafeVerifyType(type) {
			this.safeVerifyType = type;
		},
		// 使用会员码，验证付款码，查询会员信息，并且使用余额
		switchMemberCode() {
			if (!this.balanceSafeVerify && this.$refs.safeVerifyPopup) {
				this.active = 'memberCodePopup';
				setTimeout(() => {
					this.scanCodeFocus = true;
				}, 200);
				this.$refs.safeVerifyPopup.open('', () => {
					this.active = '';
				});
			} else {
				this.useBalance();
			}
		},
		verifyPaymentCode(e) {
			setTimeout(() => {
				if (this.paymentCode.trim() == '') {
					this.$util.showToast({
						title: '请输入付款码'
					});
					return;
				}

				if (this.isRepeat) return;
				this.isRepeat = true;

				checkPaymentCode({
					member_id: this.payInfo.member_id,
					code: this.paymentCode.trim()
				}).then(res => {
					if (res.code == 0) {
						this.balanceSafeVerify = true;
						this.$store.commit('app/setGlobalMemberInfo', res.data.member_info);
						this.$refs.safeVerifyPopup.close();
						this.useBalance();
					} else {
						this.$util.showToast({
							title: res.message
						});
					}
					// 扫码枪解除键盘占用
					this.$store.commit('billing/setIsScanTrigger', false);
					this.isRepeat = false;
				})
			}, 200)
		},
		/**
		 * 快捷键支付
		 * @param {Object} code 快捷键KeyCode
		 */
		hotKeyPay(code) {
			this.hotKeyPayCallback(code);
		},
		// 触发快捷键回调
		hotKeyPayCallback(code) {
			// 检查支付方式是否开启
			var pay = null;
			for (let key in this.payType) {
				if (this.payType[key].KeyCode == code) {
					pay = this.payType[key];
				}
			}
			if (!pay) return;

			if (pay.type == 'cash') {
				// 现金支付
				this.calculation(() => {
					this.switchPayType(pay.type, () => {
						if (this.type != 'third') {
							// 个人微信、支付宝、POS刷卡
							this.confirm();
						} else {
							// 付款码/扫码枪
							this.thirdConfirm();
						}
					});
				});
			} else {
				this.switchPayType(pay.type, () => {
					if (this.type != 'third') {
						// 个人微信、支付宝、POS刷卡
						this.confirm();
					} else {
						// 付款码/扫码枪
						this.thirdConfirm();
					}
				});
			}
		},
		/**
		 * 添加键盘监听事件
		 */
		addKeyDownEvent() {
			// #ifdef H5
			window.addEventListener("keydown", this.listenerKeyDown, true);
			// #endif
		},
		/**
		 * 移除键盘监听事件
		 */
		removeKeyDownEvent() {
			// #ifdef H5
			window.removeEventListener("keydown", this.listenerKeyDown, true);
			// #endif
		},
		// 监听键盘按下事件
		listenerKeyDown(e) {
			var code = e.code;

			// 正在使用扫码枪，禁用键盘
			if (this.billingIsScanTrigger) return;

			if ((this.storeRoute == 'billing' && this.billingActive == 'OrderCreate')
				|| (this.storeRoute == 'buycard' && this.buyCardActive == 'OrderCreate')
				|| (this.storeRoute == 'recharge' && this.rechargeActive == 'OrderCreate')
			) {
				// 创建订单

				this.orderCreateCallback(code);

			}
		}, /**
		 * 订单创建 事件回调
		 * @param {string} code 按键代码
		 */
		orderCreateCallback(code) {

			// 快捷支付，KeyN：付款码支付，KeyC：现金支付（cash），KeyW：微信支付（wechat），A：支付宝支付（alipay），KeyP：POS刷卡支付（POS）
			let letterCode = ['KeyN', 'KeyC', 'KeyW', 'KeyA', 'KeyP'];

			if (code == 'KeyM') {
				// 会员码

				this.switchMemberCode();

			} else if (letterCode.indexOf(code) != -1) {
				// 快捷下单

				this.quickOrderCallback(code);

			} else if (code == 'Escape') {
				// 取消支付，回到上一步，检测当前没有弹出框

				if (this.active == '') {

					this.cancelPayment();
					if (this.storeRoute == 'recharge') {
						this.$store.commit(this.storeRoute + '/setActive', '');
					} else {
						this.$store.commit(this.storeRoute + '/setActive', 'SelectGoodsAfter');
					}

				}

			} else if (this.active == 'OpenMoneyPopup') {

				if (code == 'Enter' || code == 'NumpadEnter') {

					if (this.moneyPopup.type == 'reduction') {
						// 减免金额

						this.moneyPopupConfirm();

					} else if (this.moneyPopup.type == 'cash') {
						// 现金支付

						this.moneyPopupConfirm(() => {
							this.confirm();
						});

					}

				} else if (code == 'NumpadDecimal') {

					this.keydown('.');

				} else if (code.indexOf('Numpad') != -1) {

					var num = code.replace('Numpad', '');
					this.keydown(num);


				} else if (code.indexOf('Digit') != -1) {

					var num = code.replace('Digit', '');
					this.keydown(num);

				}

			} else if (code == 'Enter' || code == 'NumpadEnter') {

				if (this.payStatus == 'success') {
					// 支付成功

					this.paySuccess();
					this.$store.commit(this.storeRoute + '/setActive', '');

				} else if (this.active == 'RemarkPopup') {
					// 备注

					this.remarkConfirm();

				} else if (this.active == 'safeVerifyPopup') {
					// 使用付款码，出示付款码、验证手机号

					setTimeout(() => {
						// 打开付款码，设置焦点
						this.scanCodeFocus = true;
					}, 200);

					if (this.safeVerifyType == 'payment_code') {

						this.verifyPaymentCode({
							detail: {
								value: this.paymentCode
							}
						});

					} else if (this.safeVerifyType == 'sms_code') {

						this.verifySmsCode({
							detail: {
								value: this.smsCode
							}
						});

					}

				} else if (this.active == 'memberCodePopup') {
					// 使用付款码，出示付款码、使用余额

					this.verifyPaymentCode({
						detail: {
							value: this.paymentCode
						}
					});

				} else if (this.active == 'thirdConfirm') {
					// 打开付款码，设置焦点

					setTimeout(() => {
						// 打开付款码，设置焦点
						this.scanCodeFocus = true;
					}, 200);

				} else if (this.type == 'cash') {

					// 选择【现金支付】方式，打开现金支付弹出框
					if (this.active == '') {
						this.switchPayType(this.type);
					}

				} else if (this.type != 'third') {
					this.confirm();
				} else if (this.active == '') {
					// 付款码/扫码枪

					this.thirdConfirm();
					setTimeout(() => {
						// 初次打开，设置焦点
						this.scanCodeFocus = true;
					}, 200);

				}
			}
		},
		/**
		 * 快捷下单 事件回调
		 * @param {string} code 按键代码
		 */
		quickOrderCallback(code) {

			// 不能存在操作
			if (this.active) return;

			if (this.payStatus == 'success') return;

			// 快捷支付，KeyN：付款码支付，KeyC：现金支付（cash），KeyW：微信支付（wechat），A：支付宝支付（alipay），KeyP：POS刷卡支付（POS）
			this.hotKeyPay(code);

		},
	},
	watch: {
		outTradeNo: function (nval, oval) {
			if (nval) {
				this._outTradeNo = nval;
				this.calculation();
			}
		},
		type: function (nval) {
			if (nval != 'third' && this.timer) {
				clearInterval(this.timer);
			}
		},
		scanCodeType: function (nval) {
			if (nval == 'scancode') {
				this.scanCodeFocus = true;
			} else {
				this.getQrcode();
			}
		},
		payStatus: function (nval) {
			if (nval == 'success') {
				if (this.autoPrintTicket) this.printTicket();
				this.isRepeat = false;
				this.autoComplete.timer = setInterval(() => {
					if (this.autoComplete.time == 0) {
						this.paySuccess();
					} else {
						this.autoComplete.time--;
					}
				}, 1000)
			}
		},
		autoPrintTicket: function (nval) {
			uni.setStorageSync('payAutoPrintTicket', nval)
		},
		'dynacodeData.seconds': {
			handler(newValue, oldValue) {
				if (newValue == 0) {
					this.refreshDynacodeData();
				}
			},
			immediate: true,
			deep: true
		}
	}
}
import {calculate, create} from '@/api/order_create.js'
import {addPendOrder, editPendOrder} from '@/api/pendorder.js'
import {mapGetters} from 'vuex';

export default {
	data() {
		return {
			type: 'goods',
			outTradeNo: '',
			isRepeat: false,
			leftIndexFocus: -1, // 左侧订单项 焦点索引
			rightIndexFocus: -1, // 右侧选择商品 焦点索引
		}
	},
	computed: {
		...mapGetters(['billingGoodsData', 'billingOrderData', 'pendOrderNum', 'billingActive', 'billingIsScanTrigger','billingPendOrderId','memberSearchWayConfig'])
	},
	watch: {
		pendOrderNum: function (nVal) {
			if (nVal == 0) this.type = 'goods';
		},
		// 会员发生变化，重新计算价格
		globalMemberInfo: function (nVal, oVal) {
			this.calculation();
		},
		billingActive(nVal) {
			if (nVal == 'SelectGoodsAfter') {
				this.rightIndexFocus = -1; // 选择完商品后，取消选中焦点
			}
		},
		billingGoodsData: {
			// 每个属性值发生变化就会调用这个函数
			handler(newVal, oldVal) {
				this.calculation();
			},
			// 深度监听 属性的变化
			deep: true
		}
	},
	onLoad() {
		uni.hideTabBar();
	},
	onShow() {
		this.$store.commit('billing/setOrderData', {
			create_time: this.$util.timeFormat(parseInt(new Date().getTime() / 1000))
		});

		// 添加组件的键盘监听事件
		this.addKeyDownEvent();
		if (this.$refs.goods) {
			this.$refs.goods.init();
			this.$refs.goods.addKeyDownEvent();
			this.$refs.goods.addScanCodeEvent();
		}

		if (this.$refs.payment) {
			this.$refs.payment.addKeyDownEvent();
		}
	},
	onHide() {
		this.removeKeyDownEvent();

		// 移除组件的键盘监听事件
		this.$refs.goods.removeKeyDownEvent();
		this.$refs.goods.removeScanCodeEvent();
		this.$refs.payment.removeKeyDownEvent();
	},
	methods: {
		switchStoreAfter() {
			this.wholeOrderCancel(true,true);
		},
		openMember() {
			if (this.$refs.selectMember) {
				this.$store.commit('billing/setActive', 'ShowMember');
				this.$refs.selectMember.open(() => {
					this.$store.commit('billing/setActive', 'ShowMemberAfter');
				});
			}
		},
		showMember() {
			this.$store.commit('billing/setActive', 'ShowMember');
			if (!this.globalMemberInfo) {
				// 选择会员
				if (this.$refs.selectMember) this.$refs.selectMember.open(() => {
					this.$store.commit('billing/setActive', 'ShowMemberAfter');
				});
			} else {
				// 打开会员信息弹出框
				this.$refs.memberDetailPopup.open();
				this.$store.commit('billing/setActive', 'ShowMemberAfter');
			}
		},
		/**
		 * 切换为散客，清除会员、订单信息
		 */
		replaceMember() {
			this.type = 'goods';
			this.$store.commit('app/setGlobalMemberInfo', null);
			this.$store.commit('billing/setOrderData', {
				goods_num: 0,
				pay_money: 0,
				goods_list: [],
				remark: '',
				create_time: this.$util.timeFormat(parseInt(new Date().getTime() / 1000))
			});
		},
		deleteGoods(data) {
			var _billingGoodsData = this.$util.deepClone(this.billingGoodsData);
			// 恢复调价
			if (!data.card_item_id) {
				for (let i = 0; i < this.$refs.goods.goodsData.list.length; i++) {
					let item = this.$refs.goods.goodsData.list[i];
					if (item.sku_id == data.sku_id) {
						item.adjust_price = item.price;
						item.adjust = {};
						break;
					}
				}
			} 
			delete _billingGoodsData[data.editKey];

			//重组数据
			var index = 0;
			let tempGoodsData = {};
			Object.keys(_billingGoodsData).forEach((key,index) =>{
				if(_billingGoodsData[key].sku_id == data.sku_id){
					let key_data = key.split('_');
					key_data[key_data.length-1] = index;
					index ++;
					tempGoodsData[key_data.join('_')] = _billingGoodsData[key];
				}else{
					tempGoodsData[key] =_billingGoodsData[key];
				}
			});

			let goodsIds = [];

			Object.keys(tempGoodsData).forEach(key => {
				let item = tempGoodsData[key];
				if (!item.item_id && goodsIds.indexOf(item.goods_id) == -1) goodsIds.push(item.goods_id);
			});

			this.$store.commit('billing/setGoodsIds', goodsIds);

			this.$store.commit('billing/setGoodsData', tempGoodsData);

			if (!Object.keys(tempGoodsData).length) {
				this.$store.commit('billing/setOrderData', {
					goods_list: [],
					goods_num: 0,
					pay_money: 0,
					create_time: this.$util.timeFormat(parseInt(new Date().getTime() / 1000))
				});
			}
		},
		/**
		 * 商品数量增加
		 * @param {Object} data
		 */
		inc(data) {
			var _billingGoodsData = this.$util.deepClone(this.billingGoodsData);
			if (data.card_item_id) {
				let _data = _billingGoodsData['sku_' + data.sku_id + '_item_' + data.card_item_id];
				if (_data.num >= data.stock) {
					this.$util.showToast({
						title: '商品库存不足'
					});
					return;
				}
				_billingGoodsData['sku_' + data.sku_id + '_item_' + data.card_item_id].num += 1;
			} else {
				var _data = _billingGoodsData[data.editKey];
				if (_data.num >= data.stock) {
					this.$util.showToast({
						title: '商品库存不足'
					});
					return;
				}

				_billingGoodsData[data.editKey].num += 1;
			}
			this.$store.commit('billing/setGoodsData', _billingGoodsData);
		},
		/**
		 * 商品数量减少
		 * @param {Object} data
		 */
		dec(data) {
			var _billingGoodsData = this.$util.deepClone(this.billingGoodsData);
			if (data.card_item_id) {
				let _data = _billingGoodsData['sku_' + data.sku_id + '_item_' + data.card_item_id];
				if (_data.num == 1) return;

				_billingGoodsData['sku_' + data.sku_id + '_item_' + data.card_item_id].num -= 1;
			} else {
				if (_billingGoodsData[data.editKey].num == 1) return;

				_billingGoodsData[data.editKey].num -= 1;
			}
			this.$store.commit('billing/setGoodsData', _billingGoodsData);
		},
		/**
		 * 计算
		 */
		calculation() {
			if (!Object.keys(this.billingGoodsData).length) {
				this.$store.commit('billing/setOrderData', {
					goods_num: 0
				});
				return;
			}

			let sku_array = [];
			let goodsIds = [];
			let editKeyData = {};

			Object.keys(this.billingGoodsData).forEach(key => {
				let item = this.billingGoodsData[key];
				let skuData = {
					sku_id: item.sku_id,
					num: item.num,
					card_item_id: item.item_id ? item.item_id : 0,
					money: item.money ? item.money : 0 // 无码商品价格
				};
				if (item.is_adjust) {
					skuData.price = item.price; // 手动调整价格
				}
				if (item.goods_money) skuData.goods_money = item.goods_money;
				sku_array.push(skuData);

				if (!item.item_id && goodsIds.indexOf(item.goods_id) == -1) goodsIds.push(item.goods_id);
				//保存editKey数据
				let editKeyIndex = skuData.sku_id+':'+skuData.card_item_id;
				editKeyData[editKeyIndex] = key;
			});

			this.$store.commit('billing/setGoodsIds', goodsIds);

			let data = {
				sku_array: JSON.stringify(sku_array),
				create_time: this.billingOrderData.create_time
			};
			if (this.globalMemberInfo) data.member_id = this.globalMemberInfo.member_id;

			calculate(data).then(res => {
				if (res.code == 0) {
					let calculateOrderData = res.data;
					calculateOrderData.goods_list.forEach((item, index) => {
						let editKeyIndex = item.sku_id+':'+(item.card_item_id||0);
						item.editKey = editKeyData[editKeyIndex];
					});
					
					this.$store.commit('billing/setOrderData', calculateOrderData);
				} else {
					this.$util.showToast({
						title: res.message
					})
				}
			})
		},
		/**
		 * 挂单
		 */
		hangingOrder() {
			if (Object.keys(this.billingGoodsData).length) {
				let data = {
					goods: [],
					order_id: this.billingPendOrderId,
					remark: this.billingOrderData.remark
				};

				if (this.globalMemberInfo) data.member_id = this.globalMemberInfo.member_id;

				Object.keys(this.billingGoodsData).forEach(key => {
					let item = this.billingGoodsData[key];
					let skuData = {
						goods_id: item.goods_id,
						sku_id: item.sku_id,
						num: item.num,
						money: item.money ? item.money : 0 // 无码商品价格
					};
					if (item.is_adjust) {
						skuData.price = item.price; // 手动调整价格
					}
					data.goods.push(skuData)
				});

				data.goods = JSON.stringify(data.goods);

				let api = this.billingPendOrderId ? editPendOrder(data) : addPendOrder(data);
				api.then(res => {
					if (res.code == 0) {
						this.wholeOrderCancel();
						this.$refs.pendOrderPopup.getOrder(0);
					} else {
						this.$util.showToast({
							title: res.message
						})
					}
				})
			} else if (this.pendOrderNum) {
				this.$refs.pendOrderPopup.open();
			} else {
				this.$util.showToast({
					title: '当前没有挂单'
				})
			}
		},
		/**
		 * 整单取消
		 */
		wholeOrderCancel(isInit = false,isDelete = false) {
			if (Object.keys(this.billingGoodsData).length) {
				// 恢复调价
				for (let i = 0; i < this.$refs.goods.goodsData.list.length; i++) {
					let item = this.$refs.goods.goodsData.list[i];
					item.adjust_price = item.price;
					item.adjust = {};
				}

				// 清除当前会员
				this.$store.commit('app/setGlobalMemberInfo', null);

				// 清除商品数据
				this.$store.commit('billing/setGoodsData', {});
				this.$store.commit('billing/setGoodsIds', []);

				if (isDelete && this.billingPendOrderId) {
					this.$refs.pendOrderPopup.deleteOrder(this.billingPendOrderId);
				}
				this.$store.commit('billing/setPendOrderId',0);

				// 清除订单数据
				this.$store.commit('billing/setOrderData', {
					goods_num: 0,
					pay_money: 0,
					goods_list: [],
					remark: '',
					create_time: this.$util.timeFormat(parseInt(new Date().getTime() / 1000)),
					order_id: 0
				});
				this.outTradeNo = '';
			} else {
				if (!isInit) this.$util.showToast({
					title: '当前没有订单数据！！'
				})
			}
		},
		/**
		 * 支付
		 */
		pay(type = '', callback) {
			const memberId = this.globalMemberInfo ? this.globalMemberInfo.member_id : 0;

			if (this.$refs.payment) this.$refs.payment.clearPay();

			if (!Object.keys(this.billingGoodsData).length || this.isRepeat) return;

			this.isRepeat = true;

			if (this.outTradeNo) {
				this.type = 'pay';
				if (type) this.$refs.payment.type = type;
				return;
			}

			this.$store.commit('billing/setActive', 'OrderCreate'); // 记录页面活跃值：创建订单

			let data = {
				remark: this.billingOrderData.remark,
				create_time: this.billingOrderData.create_time,
				member_id: memberId,
				order_key: this.billingOrderData.order_key
			};

			create(data).then(res => {
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
						title: res.message,
					})
				}
			})
		},
		cancelPayment() {
			this.outTradeNo = '';
			this.$store.commit('billing/setActive', 'SelectGoodsAfter');
			this.type = 'goods';
		},
		paySuccess() {
			this.type = 'goods';
			this.$store.commit('billing/setActive', '');
			this.isRepeat = false;
			this.wholeOrderCancel(false,true);
			this.$refs.goods.getGoods();
		},
		// 调整商品价格，数量
		callBox(data) {
			//服务商品不支持修改
			if(data.goods_class == this.$util.goodsClassDict.service) return;
			if(data.card_item_id > 0) return;
			data.status = 'edit'; // 调整商品标识
			this.$refs.goods.goodsSelect(data);
		},
		/**
		 * 添加键盘监听事件
		 */
		addKeyDownEvent() {
			// #ifdef H5
			window.addEventListener("keydown", this.listenerKeyDown, true);

			window.addEventListener("focus", this.listenerFocus, true);

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

			window.removeEventListener("focus", this.listenerFocus, true);

			delete window.POS_HOTKEY_CALLBACK;
			// #endif
		},
		// 监听焦点事件
		listenerFocus(e) {
			if (this.billingActive == 'OrderCreate') return;

			let tab = {
				// 聚焦 获取商品选择
				TabGoodsFocus: {
					className: 'goods-select-focus',
					focusField: 'rightIndexFocus'
				},
				// 聚焦 获取订单结算商品
				TabOrderFocus: {
					className: 'settlement-select-focus',
					focusField: 'leftIndexFocus'
				}
			};

			for (let key in tab) {
				if (e.target.className && e.target.className.indexOf(tab[key].className) != -1) {
					this.leftIndexFocus = -1;
					this.rightIndexFocus = -1;
					for (let i = 0; i < e.target.attributes.length; i++) {
						var item = e.target.attributes[i];
						if (item.name == 'data-tab-index') {
							this[tab[key].focusField] = parseInt(item.value);
							break;
						}
					}
					if (this[tab[key].focusField] > -1) {
						this.$store.commit('billing/setActive', key);
					}
				}

			}

		},
		// 监听键盘按下事件
		listenerKeyDown(e) {
			var code = e.code;

			if (code == 'Tab') return;

			// console.log('监听键盘按下事件 KeyDown', this.type, code, this.billingActive, this.billingIsScanTrigger, this.billingOrderData, e);

			// 按键ESC，解除占用
			if (this.billingIsScanTrigger && code == 'Escape') this.$store.commit('billing/setIsScanTrigger', false);

			// 正在输入商品编码 商品/项目名称
			if (this.billingActive == 'inputSearchText') return;

			// 关闭商品弹出框
			if (code == 'Escape' && this.billingActive == 'SelectGoodsSku' && this.type == 'goods') {
				this.$store.commit('billing/setActive', 'SelectGoodsAfter');
				return;
			}

			if (this.type != 'pay' && this.billingActive != 'SelectGoodsSku' && code == 'KeyM') {
				// 选择会员，键盘快捷键【M】
				this.openMember();

			} else if (this.type == 'goods' && code == 'PageUp') {
				// 切换左侧焦点
				this.$store.commit('billing/setActive', 'TabOrderFocus');
				this.leftIndexFocus = 0;
				this.rightIndexFocus = -1;
				code = 'ArrowUp';
			} else if (this.type == 'goods' && code == 'PageDown') {
				// 切换右侧焦点
				this.$store.commit('billing/setActive', 'TabGoodsFocus');
				this.rightIndexFocus = 0;
				this.leftIndexFocus = -1;
				code = 'ArrowLeft';
			} else if (this.billingActive == 'TabGoodsFocus') {
				// 使用tab键，聚焦选择商品

				this.tabGoodsFocusCallback(code);

			} else if (this.billingActive == 'TabOrderFocus') {
				// 使用tab键，聚焦获取订单结算商品

				this.tabOrderFocusCallback(code);

			} else if(this.billingActive == 'ShowMember' && this.memberSearchWayConfig.way == 'list'){

				// 按照会员列表进行搜索
				if (code == 'Enter' || code == 'NumpadEnter') {

					if(this.$refs.selectMember.searchFinish && this.$refs.selectMember.memberId){
						this.$refs.selectMember.getMemberInfo(this.$refs.selectMember.memberId);
					}
				}

			} else if (this.billingActive == 'ShowMemberAfter' || (this.billingOrderData.goods_num && this.billingActive == 'SelectGoodsAfter' && !this.billingIsScanTrigger)) {
				// 活跃窗口：设置会员后，选择完商品后

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
			// console.log('POS ${code} 按键', code, this.type);

			if (code == 'F2') {
				// 选择商品

				if (this.type != 'pay') {
					this.type = 'goods';
					this.$store.commit('billing/setActive', 'SelectGoodsAfter');
				}

			} else if (code == 'F3') {
				// 挂/取单

				if (this.type != 'pay') {
					this.hangingOrder();
				}

			} else if (code == 'F4') {
				// 选择会员

				if (this.type != 'pay') {
					this.showMember();
				}

			} else if (code == 'F12') {
				// 整单取消

				if (this.type != 'pay') {
					this.wholeOrderCancel(false,true);
				}

			} else if (code == 'BACKSPACE') {
				// 退格键

				if (this.billingActive == 'UnnumberedGoods' && this.type == 'goods') {

					if (this.$refs.goods) this.$refs.goods.deleteCode();

				} else if (this.billingActive == 'OrderCreate') {

					if (this.$refs.payment) {
						if (this.$refs.payment.active == 'OpenMoneyPopup') {
							this.$refs.payment.deleteCode();
						}
					}

				}

			} else if (code == 'X') {
				// 打开钱箱，快捷键：Alt+X
				this.openCashBox();
			} else if (this.type == 'goods' && code == 'PAGEUP') {
				// 切换左侧焦点
				this.$store.commit('billing/setActive', 'TabOrderFocus');
				this.leftIndexFocus = 0;
				this.rightIndexFocus = -1;
				this.tabOrderFocusCallback('ArrowUp');
			} else if (this.type == 'goods' && code == 'PAGEDOWN') {
				// 切换右侧焦点
				this.$store.commit('billing/setActive', 'TabGoodsFocus');
				this.rightIndexFocus = 0;
				this.leftIndexFocus = -1;
				this.tabGoodsFocusCallback('ArrowLeft');
			} else {
				// 触发左侧菜单按键回调
				this.menuTriggerKeyCodeCallBack(code);
			}
		},
		// 打开钱箱
		openCashBox() {
			try {
				let data = {
					message: '打开钱箱'
				};
				this.$pos.send('OpenCashBox', JSON.stringify(data));
			} catch (e) {
				console.log('打开钱箱异常', e)
			}
		},
		/**
		 * 使用tab键，聚焦选择商品 事件回调
		 * @param {string} code 按键代码
		 */
		tabGoodsFocusCallback(code) {
			if (!this.$refs.goods) return;

			let list = document.querySelectorAll(`.${this.$refs.goods.type}-focus.table-item`);

			if (code == 'Enter' || code == 'NumpadEnter') {

				if (this.$refs.goods.type == 'goods') {
					// 添加实物商品
					this.$refs.goods.goodsSelect(this.$refs.goods.goodsData.list[this.rightIndexFocus]);
				} else if (this.$refs.goods.type == 'service') {
					// 添加项目商品
					this.$refs.goods.goodsSelect(this.$refs.goods.serviceData.list[this.rightIndexFocus]);
				}

			} else if (code == 'ArrowUp' && this.rightIndexFocus > -1) {

				// 上箭头，商品，goods，项目：service
				const query = uni.createSelectorQuery().in(this);
				query.select(`.goods-container .list-wrap.${this.$refs.goods.type}`).boundingClientRect(data => {
					this.rightIndexFocus -= Math.floor(data.width / 270); // 单个元素宽度270

					// 超出，选择第一个
					if (this.rightIndexFocus <= 0) this.rightIndexFocus = 0;

					list[this.rightIndexFocus].focus();
				}).exec();

			} else if (code == 'ArrowDown' && this.rightIndexFocus > -1) {

				// 下箭头，商品，goods，项目：service
				const query = uni.createSelectorQuery().in(this);
				query.select(`.goods-container .list-wrap.${this.$refs.goods.type}`).boundingClientRect(data => {
					this.rightIndexFocus += Math.floor(data.width / 270); // 单个元素宽度270

					// 超出，选择最后一个
					if (this.rightIndexFocus >= list.length) this.rightIndexFocus = list.length - 1;

					list[this.rightIndexFocus].focus();
				}).exec();

			} else if (code == 'ArrowRight' && this.rightIndexFocus > -1) {

				// 右箭头
				if (this.rightIndexFocus + 1 >= list.length) return;
				this.rightIndexFocus++;

				list[this.rightIndexFocus].focus();

			} else if (code == 'ArrowLeft' && this.rightIndexFocus > -1) {

				// 左箭头
				if (this.rightIndexFocus <= 0) return;
				this.rightIndexFocus--;

				list[this.rightIndexFocus].focus();

			}
		},
		/**
		 * 使用tab键，聚焦 获取订单结算商品 事件回调
		 * @param {string} code 按键代码
		 */
		tabOrderFocusCallback(code) {
			// 按键回车，弹框调整商品数量，注意，此时只能修改商品数量，不能更改规格
			if (code == 'Enter' || code == 'NumpadEnter') {
				if (this.billingOrderData.goods_list[this.leftIndexFocus]) {
					let data = this.$util.deepClone(this.billingOrderData.goods_list[this.leftIndexFocus]);
					data.status = 'edit'; // 修改商品数量标识

					if (this.$refs.goods) this.$refs.goods.goodsSelect(data);
				}
			} else if (code == 'ArrowUp' && this.leftIndexFocus > -1) {

				// 上箭头
				let list = document.querySelectorAll(`.settlement-select-focus`);
				if (this.leftIndexFocus <= 0) return;
				this.leftIndexFocus--;
				list[this.leftIndexFocus].focus();

			} else if (code == 'ArrowDown' && this.leftIndexFocus > -1) {

				// 下箭头
				let list = document.querySelectorAll(`.settlement-select-focus`);
				if (this.leftIndexFocus + 1 >= list.length) return;
				this.leftIndexFocus++;
				list[this.leftIndexFocus].focus();

			} else if (code == 'Delete' && this.leftIndexFocus > -1) {
				this.deleteGoods(this.billingOrderData.goods_list[this.leftIndexFocus], this.leftIndexFocus);
			}
		},
		// 恢复焦点下标
		restoreFocus() {
			this.leftIndexFocus = -1;
			this.rightIndexFocus = -1;
		},
		leftTabOrderSelectBlur(item){
			this.$store.commit('billing/setActive', 'SelectGoodsAfter');
		}
	},
}
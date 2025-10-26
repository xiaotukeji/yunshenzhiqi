import {
	getOrderList,
	getOrderDetail,
	orderRemark,
	orderClose,
	orderStoreDelivery,
	orderLocalDelivery,
	orderPrintTicket,
	getExpressCompanyList,
	orderExpressDelivery,
	getOrderDeliverList,
	orderAdjustPrice,
	getorderCondition
} from '@/api/order.js'
import {getRefundApplyData, orderRefund} from '@/api/order_refund.js'

export default {
	data() {
		return {
			selectGoodsKeys: 0,
			otherInfoValue: {
				order_no: {
					title: '订单编号：',
					value: ''
				},
				out_trade_no: {
					title: '订单交易号：',
					value: ''
				},
				create_time: {
					title: '消费时间：',
					value: ''
				},
				pay_status: {
					title: '支付状态：',
					value: ''
				},
				order_status: {
					title: '订单状态：',
					value: ''
				},
				pay_type: {
					title: '付款方式：',
					value: ''
				},
				operator_name: {
					title: '收银员：',
					value: ''
				},
				pay_time: {
					title: '付款时间：',
					value: ''
				}
			},
			// 订购日志所需列表数据
			list: [],
			//获取订单的页数
			page: 1,
			//每次获取订单的条数
			page_size: 8,
			// 订单搜索是用到的数据
			search_text: '',
			conditions: {
				order_status: '',
				time_type: '',
				start_time: '',
				end_time: '',
				start_time_val: '',
				end_time_val: '',
				order_type: 'all',
				order_from: '',
				pay_type: '',
			},
			//订单列表类型
			currOrderList: 'cashier',
			//订单类型
			trade_type: '',
			//初始时加载详情数据判断
			one_judge: true,
			//无限滚动请求锁
			listLock: false,
			scrollTop: 0,
			// 订单列表数据
			order_list: [],
			//订单详情数据
			order_detail: {},
			type: 'detail',
			refundStep: 0,
			refundGoods: [],
			refundDetail: null,
			refundRepeat: false,
			refundApply: {
				refund_remark: '',
				refund_transfer_type: ''
			},
			localDelivery: {
				deliverer_mobile: '',
				deliverer: ''
			},
			expresDelivery: {
				delivery_type: 1,
				express_company_id: 0,
				delivery_no: '',
				order_goods_ids: []
			},
			expressCompany: [],
			deliverer: [],
			tabObj: {
				list: [{
					value: 1,
					name: '基础信息'
				}, {
					value: 2,
					name: '商品信息'
				}, {
					value: 3,
					name: '订单日志'
				}],
				index: 1
			},
			isLogisticsRepeat: false,
			//调价
			adjustParams:{
				order_id:null,
				adjust_money:0,
				delivery_money:0
			},
			currGlobalStoreId:'',
			showScreen: false,
			orderConditionList: [],
		};
	},
	onLoad(option) {
		this.search_text = option.order_no || '';
		this.currOrderList = option.order_from == 'online' ? 'online' : 'cashier';
		if(uni.getStorageSync('globalStoreId')) this.currGlobalStoreId = uni.getStorageSync('globalStoreId');
		// 获取订单列表数据
		this.getOrderListFn();
		this.getExpressCompany();
		this.getDeliver();
		this.getOrderCondition()
	},
	watch: {
		type: function (nval, oval) {
			if (oval == 'refund') {
				this.refundStep = 0;
				this.refundGoods = [];
				this.refundDetail = null;
				this.refundRepeat = false;
				this.refundApply = {
					refund_remark: '',
					refund_transfer_type: ''
				};
			}
		}
	},
	methods: {
		changeIsRefundStock(refundItem,e) {
			this.refundApply.refund_array[refundItem.order_goods_info.order_goods_id].is_refund_stock = e.detail.value;
			this.$forceUpdate()
		},
		searchOrder() {
			if(this.conditions.start_time_val || this.conditions.end_time_val){
				if(new Date(this.conditions.end_time_val).getTime() <= new Date(this.conditions.start_time_val).getTime()){
					this.$util.showToast({title:'结束时间不能早于开始时间'})
					return;
				}
			}
			this.page = 1;
			this.order_list = [];
			this.one_judge = true;
			this.listLock = false;
			this.showScreen = false;
			this.getOrderListFn();
		},
		initCondition() {
			this.conditions.order_status = '';
			this.conditions.time_type = '';
			this.conditions.start_time = '';
			this.conditions.end_time = '';
			this.conditions.start_time_val = '';
			this.conditions.end_time_val = '';
			this.conditions.order_type = 'all';
			this.conditions.order_from = '';
			this.conditions.pay_type = '';
		},
		resetCondition() {
			this.page = 1;
			this.order_list = [];
			this.one_judge = true;
			this.listLock = false;
			this.initCondition();
			this.showScreen = false;
			this.getOrderListFn();
		},
		changeCondition(mode,type) {
			this.conditions[mode] = type;
			if(mode == 'order_type'){
				if(this.currOrderList == 'online'){
					this.orderConditionList.order_type_list.forEach((item,index) => {
						if(item.type == type) this.orderConditionList.order_status_list = item.status;
					})
					this.conditions.order_status = '';
				}
			}
			if(mode == 'time_type'){
				this.conditions.start_time_val = '';
				this.conditions.end_time_val = '';
				switch (type){
					case '':
						this.conditions.start_time = '';
						this.conditions.end_time = '';
						break;
					case '7':
						this.conditions.start_time = this.getDay(type);
						this.conditions.end_time = this.getNowDate();
						break;
					case '30':
						this.conditions.start_time = this.getDay(type);
						this.conditions.end_time = this.getNowDate();
						break;
					default:
						break;
				}
			}
		},
		getDay(p_count) {
			var dd = new Date();
			dd.setDate(dd.getDate() - p_count); //获取p_count天后的日期
			var y = dd.getFullYear();
			var m = dd.getMonth() + 1; //获取当前月份的日期
			if (m < 10) {
				m = '0' + m;
			}
			var d = dd.getDate();
			if (d < 10) {
				d = '0' + d;
			}
			return y + '-' + m + '-' + d +' 00:00:00';
		},
		getNowDate() {
			var date = new Date();
			var y = date.getFullYear();
			var m = date.getMonth() + 1; //获取当前月份的日期
			if (m < 10) {
				m = '0' + m;
			}
			var d = date.getDate();
			if (d < 10) {
				d = '0' + d;
			}
			return y + '-' + m + '-' + d +' 23:59:59';
		},
		getOrderCondition() {
			getorderCondition()
			.then((res)=>{
				if(res.code>=0){
					var data = res.data;
					for (var index in data) {
						var arr = [];
						if (index != 'order_label_list' && index != 'order_status_list' && index != 'pay_type_list' && index != 'cashier_pay_type_list' && index != 'cashier_order_type_list') {
							for (var index_c in data[index]) {
								var obj = {
									type: index_c
								};
								obj = Object.assign(obj, data[index][index_c]);
								arr.push(obj);
							}
						} else {
							for (var index_c in data[index]) {
								var obj = {
									type: index_c,
									name: data[index][index_c]
								};
								arr.push(obj);
							}
						}
						data[index] = arr;
					}
					this.orderConditionList = data;
					this.orderConditionList.order_type_list.forEach((item,index)=>{
						var arr = [];
						for (var index_c in item.status) {
							var obj = {
								type: index_c,
								name: item.status[index_c]
							};
							arr.push(obj);
						}
						item.status = arr;
						if(item.type == 'all'){
							this.orderConditionList.order_status_list = item.status;
						}
					})
				}
			})
		},
		switchStoreAfter() {
			if(this.currGlobalStoreId == uni.getStorageSync('globalStoreId')) return;
			this.currGlobalStoreId = uni.getStorageSync('globalStoreId');
			this.page = 1;
			this.order_list = [];
			this.one_judge = true;
			this.listLock = false;
			this.getOrderListFn();
			this.getDeliver()
		},
		// 搜索
		search(type) {
			this.page = 1;
			this.order_list = [];
			this.one_judge = true;
			this.listLock = false;
			if (type == 'enter') {
				document.onkeydown = e => {
					if (e.keyCode === 13) {
						//回车后执行搜索方法
						this.getOrderListFn();
					}
				}
			} else {
				this.getOrderListFn();
			}

		},
		selectOrderList(order_type) {
			this.currOrderList = order_type;
			this.page = 1;
			this.order_list = [];
			this.one_judge = true;
			this.listLock = false;
			this.initCondition();
			this.selectGoodsKeys = 0;
			this.getOrderListFn();
		},
		/**
		 * 获取订单列表
		 */
		getOrderListFn() {
			if (this.listLock) return false;
			this.listLock = true;
			getOrderList({
				page: this.page,
				page_size: this.page_size,
				search_text: this.search_text,
				order_scene: this.currOrderList,
				order_status: this.conditions.order_status,
				start_time: this.conditions.start_time_val ? this.conditions.start_time_val : this.conditions.start_time,
				end_time: this.conditions.end_time_val ? this.conditions.end_time_val : this.conditions.end_time,
				order_type: this.conditions.order_type,
				order_from: this.conditions.order_from,
				pay_type: this.conditions.pay_type,
				
			}).then((res) => {
				if (res.data.list.length == 0 && this.one_judge) {
					this.order_detail = {};
					this.one_judge = false;
					if (this.$refs.detailLoading) this.$refs.detailLoading.hide()
				}
				if (res.code >= 0 && res.data.list.length != 0) {
					if (this.order_list.length == 0) {
						this.order_list = res.data.list;
					} else {
						this.order_list = this.order_list.concat(res.data.list);
					}

					//初始时加载一遍详情数据
					if (this.one_judge) {
						this.getOrderDetailFn(this.order_list[0].order_id);
					}
				}
				if (this.page == 1) {
					this.scrollTop = 0
				}
				if (res.data.list.length >= this.page_size) {
					this.page++
					this.listLock = false
				}
			});
		},
		scroll(e) {
			this.scrollTop = e.detail.scrollTop
		},
		/**
		 * 获取订单详情数据
		 */
		getOrderDetailFn(order_id, keys = 0, callback) {
			// 清空数据
			this.localDelivery = {
				deliverer_mobile: '',
				deliverer: ''
			};

			this.selectGoodsKeys = keys;
			this.type = 'detail';
			this.$refs.detailLoading.show();
			getOrderDetail({
				order_id
			}).then((res) => {
				if (res.code >= 0) {
					this.order_detail = res.data;
					this.order_detail.order_status_action = JSON.parse(res.data.order_status_action)
					this.otherInfoValue.order_no.value = res.data.order_no;
					this.otherInfoValue.out_trade_no.value = res.data.out_trade_no;
					this.otherInfoValue.create_time.value = this.$util.timeFormat(res.data.create_time);
					this.otherInfoValue.operator_name.value = res.data.operator_name;
					this.otherInfoValue.pay_type.value = res.data.pay_type_name;
					this.otherInfoValue.order_status.value = res.data.order_status_name;
					if (res.data.pay_status == 1) {
						this.otherInfoValue.pay_status.value = '已支付';
						this.otherInfoValue.pay_time.value = this.$util.timeFormat(res.data.pay_time);
					} else {
						this.otherInfoValue.pay_status.value = '待支付';
						this.otherInfoValue.pay_time.value = '';
					}
					if (typeof callback == 'function') {
						callback();
					}
					Object.keys(this.adjustParams).forEach(key=>{
						this.adjustParams[key] = parseFloat(this.order_detail[key])
					})
					this.$forceUpdate();
					this.one_judge = false;
					if (this.$refs.detailLoading) this.$refs.detailLoading.hide()
				}
			});
		},
		/**
		 * 打开弹出框
		 * @param action
		 */
		open(action) {
			this.$refs[action].open();
		},
		/**
		 * 关闭弹出框
		 * @param name
		 */
		close(name) {
			this.$refs[name].close();
		},
		/**
		 * 调价提交
		 */
		adjustSave(){
			if(parseFloat(this.adjustParams.delivery_money+0)<0){
				this.$util.showToast({
					title: '运费不可小于0'
				});
				return false
			}else if(!parseFloat(this.adjustParams.delivery_money+0)){
				this.adjustParams.delivery_money = 0
			}
			if(parseFloat(parseFloat(this.order_detail.goods_money)-parseFloat(this.order_detail.promotion_money||0)-parseFloat(this.order_detail.coupon_money||0) -parseFloat(this.order_detail.point_money||0)+ parseFloat(this.adjustParams.adjust_money||0) + parseFloat(this.adjustParams.delivery_money||0)).toFixed(2)<0){
				this.$util.showToast({
					title: '真实商品价格不可小于0'
				});
				return false
			}
			orderAdjustPrice(this.adjustParams).then((res)=>{
				this.$util.showToast({
					title: res.message
				});
				if (res.code >= 0) {
					this.getOrderDetailFn(this.order_list[this.selectGoodsKeys].order_id);
					this.$refs.orderAdjustMoney.close()
				}
			})
		},
		/**
		 * 关闭调价弹框
		 */
		clear(){
			Object.keys(this.adjustParams).forEach(key=>{
				this.adjustParams[key] = this.adjustParams[key] = parseFloat(this.order_detail[key])
			})
			this.$refs.orderAdjustMoney.close()
		},
		/**
		 * 留言数据保存
		 */
		saveRemark() {
			orderRemark({
				order_id: this.order_detail.order_id,
				remark: this.order_detail.remark
			}).then((res) => {
				this.$util.showToast({
					title: res.message
				});
				if (res.code >= 0) {
					this.getOrderDetailFn(this.order_list[this.selectGoodsKeys].order_id);
					this.$refs.remark.close();
				}
			});
		},
		/**
		 * 关闭订单
		 */
		orderCloseFn() {
			orderClose({
				order_id: this.order_detail.order_id
			}).then((res) => {
				this.$util.showToast({
					title: res.message
				});
				if (res.code >= 0) {
					this.getOrderDetailFn(this.order_list[this.selectGoodsKeys].order_id, this.selectGoodsKeys, () => {
						this.order_list[this.selectGoodsKeys] = this.order_detail;
						this.$forceUpdate();
					});
					this.$refs.orderClose.close();
				}
			});
		},
		/**
		 * 订单取消
		 */
		orderOperation(type) {
			switch (type) {
				case 'save':
					this.orderCloseFn();
					break;
				case 'close':
					this.$refs.orderClose.close();
					break;
			}
		},
		selectOrderGoods(data) {
			let index = this.refundGoods.indexOf(data.order_goods_id);
			if (index == -1) this.refundGoods.push(data.order_goods_id);
			else this.refundGoods.splice(index, 1);
		},
		/**
		 * 退款下一步
		 */
		refundNext() {
			
			if (this.refundStep == 0) {
				if (!this.refundGoods.length) {
					this.$util.showToast({
						title: '请选择要退款的商品'
					});
					return;
				}

				getRefundApplyData({
					refund_array: JSON.stringify(this.refundGoods)
				}).then((res) => {

					if (res.code == 0) {
						this.refundDetail = res.data;
						this.refundStep = 1;
						let refundData = {};

						this.refundDetail.refund_list.forEach(refundItem => {
							refundData[refundItem.order_goods_info.order_goods_id] = {
								refund_pay_money: this.$util.moneyFormat(refundItem.order_goods_info.refund_apply_money),
								refund_money: this.$util.moneyFormat(refundItem.order_goods_info.refund_apply_money),
								refund_status:'PARTIAL_REFUND',
								is_refund_stock: 1,
								refund_stock_num: refundItem.order_goods_info.num
							};
						});
						Object.assign(this.refundApply, {
							order_id: this.order_detail.order_id,
							refund_array: refundData,
							refund_transfer_type: Object.keys(this.refundDetail.refund_transfer_type)[0]
						});
					} else {
						this.$util.showToast({
							title: res.message
						});
					}
				});
			} else if (this.refundStep == 1) {
				if (this.refundVerify()) this.refundStep = 2;
			} else if (this.refundStep == 2) {
				this.createRefund();
			}
		},
		/**
		 * 退款验证
		 */
		refundVerify() {
			try {
				this.refundDetail.refund_list.forEach(refundItem => {
					let data = this.refundApply.refund_array[refundItem.order_goods_info.order_goods_id];
					if (isNaN(parseFloat(data.refund_money))) {
						this.$util.showToast({
							title: '退款金额输入错误'
						});
						throw new Error('');
					}
					if (parseFloat(data.refund_money) < 0) {
						this.$util.showToast({
							title: '退款金额不能小于0'
						});
						throw new Error('');
					}
					if (parseFloat(data.refund_money) > parseFloat(data.refund_pay_money)) {
						this.$util.showToast({
							title: '退款金额超出可退金额'
						});
						throw new Error('');
					}
					
					if(data.is_refund_stock == 1){
						if(!Number(data.refund_stock_num)){
							this.$util.showToast({
								title: '请输入返还数量'
							});
							throw new Error('');
						}
						
						if(data.refund_stock_num <= 0){
							this.$util.showToast({
								title: '返还数量不能小于0'
							});
							throw new Error('');
						}
						
						
						
						if(refundItem.order_goods_info.goods_class != 6 || (refundItem.order_goods_info.goods_class == 6 && refundItem.order_goods_info.pricing_type != 'weight' )){
							// 不能为小数
							if(String(data.refund_stock_num).indexOf('.') != -1){
								this.$util.showToast({
									title: '商品'+refundItem.order_goods_info.goods_name+'的返还数量只能是正整数'
								});
								throw new Error('');
							}
						}
						
						if(data.refund_stock_num > refundItem.order_goods_info.num){
							this.$util.showToast({
								title: '商品'+refundItem.order_goods_info.goods_name+'最多返还'+refundItem.order_goods_info.num+'件'
							});
							throw new Error('');
						}
						
					}
				});
			} catch (e) {
				return false;
			}
			return true;
		},
		/**
		 * 退款申请
		 */
		createRefund() {
			if (this.refundRepeat) return;
			this.refundRepeat = true;

			uni.showLoading({
				title: ''
			});

			let data = this.$util.deepClone(this.refundApply);
			data.refund_array = JSON.stringify(data.refund_array);

			orderRefund(data).then((res) => {
				uni.hideLoading();
				if (res.code == 0) {
					this.$util.showToast({
						title: '退款成功'
					});
					this.getOrderDetailFn(this.order_detail.order_id);
					this.type = 'detail';
				} else {
					this.refundRepeat = false;
					this.$util.showToast({
						title: res.message
					});
				}
			});

		},
		/**
		 * 提货
		 */
		storeOrderTakeDelivery() {
			if (this.isRepeat) return;
			this.isRepeat = true;

			uni.showLoading({
				title: ''
			});
			orderStoreDelivery(this.order_detail.order_id).then(res => {
				uni.hideLoading();
				this.isRepeat = false;
				if (res.code == 0) {
					this.getOrderDetailFn(this.order_detail.order_id);
					this.$refs.storeOrderTakeDelivery.close();
				} else {
					this.$util.showToast({
						title: res.message
					});
				}
			})
		},
		/**
		 * 本地配送
		 */
		orderLocalDeliveryFn() {
			if (!this.localDelivery.deliverer) {
				this.$util.showToast({
					title: '请选择配送员'
				});
				return;
			}
			if (!this.localDelivery.deliverer_mobile) {
				this.$util.showToast({
					title: '请输入配送员联系方式'
				});
				return;
			}

			if (this.isRepeat) return;
			this.isRepeat = true;

			uni.showLoading({
				title: ''
			});

			orderLocalDelivery({
				order_id: this.order_detail.order_id,
				deliverer: this.localDelivery.deliverer,
				deliverer_mobile: this.localDelivery.deliverer_mobile
			}).then(res => {
				uni.hideLoading();
				this.isRepeat = false;
				if (res.code == 0) {
					this.getOrderDetailFn(this.order_detail.order_id);
					this.localDelivery = {
						deliverer_mobile: '',
						deliverer: ''
					};
					this.$refs.orderLocalDelivery.close();
				} else {
					this.$util.showToast({
						title: res.message
					});
				}
			})
		},
		getDeliver() {
			getOrderDeliverList().then(res => {
				if (res.code == 0 && res.data) {
					this.deliverer = res.data.map(item => {
						return {
							label: item.deliver_name,
							value: item.deliver_name, // 废弃，deliver_id
							mobile: item.deliver_mobile
						};
					});
				}
			})
		},
		selectDeliverer(index, item) {
			if (index >= 0) {
				this.localDelivery.deliverer_mobile = this.deliverer[index].mobile; // 配送员手机号
				this.localDelivery.deliverer = item.value; // 配送员
			} else {
				this.localDelivery.deliverer_mobile = '';
				this.localDelivery.deliverer = '';
			}
		},
		viewMember() {
			this.$util.redirectTo('/pages/member/list', {member_id: this.order_detail.member_id});
		},
		/**
		 * 打印小票
		 */
		printTicket() {
			orderPrintTicket(this.order_detail.order_id).then(res => {
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
							title: '未开启订单小票打印'
						})
					}
				} else {
					this.$util.showToast({
						title: res.message ? res.message : '小票打印失败'
					})
				}
			})
		},
		getExpressCompany() {
			getExpressCompanyList().then(res => {
				if (res.code == 0 && res.data) {
					this.expressCompany = res.data.map(item => {
						return {
							label: item.company_name,
							value: item.company_id
						};
					});
				}
			})
		},
		selectExpressCompany(index, item) {
			if (index >= 0) {
				this.expresDelivery.express_company_id = parseInt(item.value)
			} else {
				this.expresDelivery.express_company_id = 0
			}
		},
		/**
		 * 物流配送订单发货
		 */
		orderDelivery() {
			if (this.expresDelivery.delivery_type == 1) {
				if (!this.expresDelivery.express_company_id) {
					this.$util.showToast({
						title: '请选择物流公司'
					});
					return;
				}
				if (!this.expresDelivery.delivery_no) {
					this.$util.showToast({
						title: '请输入物流单号'
					});
					return;
				}
			}
			if (!this.expresDelivery.order_goods_ids.length) {
				this.$util.showToast({
					title: '请选择要发货的商品'
				});
				return;
			}

			if (this.isLogisticsRepeat) return;
			this.isLogisticsRepeat = true;

			uni.showLoading({
				title: ''
			});
			orderExpressDelivery({
				order_id: this.order_detail.order_id,
				delivery_type: this.expresDelivery.delivery_type,
				express_company_id: this.expresDelivery.express_company_id,
				delivery_no: this.expresDelivery.delivery_no,
				order_goods_ids: this.expresDelivery.order_goods_ids.toString()
			}).then(res => {
				uni.hideLoading();
				if (res.code == 0) {
					this.isLogisticsRepeat = false;
					this.getOrderDetailFn(this.order_detail.order_id);
					this.expresDelivery = {
						delivery_type: 1,
						express_company_id: 0,
						delivery_no: '',
						order_goods_ids: []
					};
					this.$refs.orderDelivery.close();
				} else {
					this.isLogisticsRepeat = false;
					this.$util.showToast({
						title: res.message
					});
				}
			})
		}
	}

}
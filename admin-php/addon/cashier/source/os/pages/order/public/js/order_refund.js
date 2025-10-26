import {
	orderRefundComplete,
	getOrderRefundLists,
	getOrderRefundDetail,
	orderRefundAgree,
	orderRefundClose,
	orderRefundRefuse,
	orderRefundReceive
} from '@/api/order_refund.js'

export default {
	data() {
		return {
			selectGoodsKeys: 0,
			//获取订单的页数
			page: 1,
			//每次获取订单的条数
			page_size: 8,
			// 订单搜索是用到的数据
			search_text: '',
			//初始时加载详情数据判断
			one_judge: true,
			//无限滚动请求锁
			listLock: true,
			scrollTop: 0,
			// 订单列表数据
			order_list: [],
			//订单详情数据
			order_detail: {},
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
			isRepeat: false,
			refundRefuseReason: '', // 拒绝理由
			refundTransfer: {
				refund_real_money: 0,
				refund_money_type: 1,
				shop_refund_remark: ''
			},
			isRefundStock: 0
		};
	},
	onLoad(option) {
		this.getOrderList();
	},
	methods: {
		// 搜索
		search() {
			this.page = 1;
			this.order_list = [];
			this.one_judge = true;
			this.listLock = true;
			this.getOrderList();
		},
		/**
		 * 获取订单列表
		 */
		getOrderList() {
			if (!this.listLock) return false;
			getOrderRefundLists({
				page: this.page,
				page_size: this.page_size,
				search: this.search_text
			}).then(res => {
				if (res.data.list.length == 0) {
					this.order_detail = {};
					this.one_judge = false;
				}
				if (res.code >= 0) {
					if (this.order_list.length == 0) {
						this.order_list = res.data.list;
					} else {
						this.order_list = this.order_list.concat(res.data.list);
					}
					//初始时加载一遍详情数据
					if (this.one_judge) {
						this.getOrderDetail(this.order_list[0].order_goods_id);
					}
				}
				if (this.page == 1) {
					this.scrollTop = 0
				}
				if (res.data.list.length < this.page_size) {
					this.listLock = false
				} else {
					this.page++
				}
			})
		},
		scroll(e) {
			this.scrollTop = e.detail.scrollTop
		},
		/**
		 * 获取订单详情数据
		 */
		getOrderDetail(order_goods_id, keys = 0, callback) {
			this.selectGoodsKeys = keys;
			getOrderRefundDetail({order_goods_id}).then(res => {
				if (res.code >= 0) {
					this.order_detail = res.data;
					if (typeof callback == 'function') {
						callback();
					}
					this.$forceUpdate();
					this.one_judge = false;
				}
			});
		},
		/**
		 * 打开弹出框
		 */
		open(action) {
			if (action == 'orderRefundTransfer') {
				this.refundTransfer.order_goods_id = this.order_detail.order_goods_id;
				this.refundTransfer.refund_real_money = this.order_detail.refund_apply_money;
				this.refundTransfer.refund_money_type = 1;
				this.refundTransfer.shop_refund_remark = '';
			}
			this.$refs[action].open();
		},
		/**
		 * 关闭弹出框
		 */
		close(name) {
			this.$refs[name].close();
		},
		// 同意维权
		orderRefundAgree() {
			if (this.isRepeat) return;
			this.isRepeat = true;
			orderRefundAgree({order_goods_id: this.order_detail.order_goods_id}).then(res => {
				this.$util.showToast({
					title: res.message
				});
				if (res.code >= 0) {
					this.getOrderDetail(this.order_detail.order_goods_id);
					this.$refs.orderRefundAgree.close();
				}
				this.isRepeat = false;
			});
		},
		// 拒绝维权
		orderRefundRefuse() {
			if (!this.refundRefuseReason) {
				this.$util.showToast({
					title: '请输入拒绝理由'
				});
				return;
			}
			if (this.isRepeat) return;
			this.isRepeat = true;
			orderRefundRefuse({
				order_goods_id: this.order_detail.order_goods_id,
				refund_refuse_reason: this.refundRefuseReason
			}).then(res => {
				this.$util.showToast({
					title: res.message
				});
				if (res.code >= 0) {
					this.getOrderDetail(this.order_detail.order_goods_id);
					this.$refs.orderRefundRefuse.close();
				}
				this.isRepeat = false;
			});
		},
		// 关闭维权
		orderRefundClose() {
			if (this.isRepeat) return;
			this.isRepeat = true;

			orderRefundClose({
				order_goods_id: this.order_detail.order_goods_id
			}).then(res => {
				this.$util.showToast({
					title: res.message
				});
				if (res.code >= 0) {
					this.getOrderDetail(this.order_detail.order_goods_id);
					this.$refs.orderRefundClose.close();
				}
				this.isRepeat = false;
			});
		},
		// 转账
		orderRefundTransfer() {
			if (!this.refundTransfer.refund_real_money) {
				this.$util.showToast({
					title: '请输入退款金额'
				});
				return;
			}

			var money = parseFloat(this.refundTransfer.refund_real_money);
			if (isNaN(money)) {
				this.$util.showToast({
					title: '请输入正确的退款金额'
				});
				return;
			}
			if (money < 0) {
				this.$util.showToast({
					title: '退款金额不能为负数'
				});
				return;
			}

			if (this.isRepeat) return;
			this.isRepeat = true;

			orderRefundComplete(this.refundTransfer).then(res => {
				this.$util.showToast({
					title: res.message
				});
				if (res.code >= 0) {
					this.getOrderDetail(this.order_detail.order_goods_id);
					this.$refs.orderRefundTransfer.close();
				}
				this.isRepeat = false;
			})
		},
		// 买家退货接收，维权收货
		orderRefundTakeDelivery() {
			if (this.isRepeat) return;
			this.isRepeat = true;
			orderRefundReceive({
				order_goods_id: this.order_detail.order_goods_id,
				is_refund_stock: this.isRefundStock
			}).then(res => {
				this.$util.showToast({
					title: res.message
				});
				if (res.code >= 0) {
					this.getOrderDetail(this.order_detail.order_goods_id);
					this.$refs.orderRefundTakeDelivery.close();
				}
				this.isRepeat = false;
			});
		}

	}
};
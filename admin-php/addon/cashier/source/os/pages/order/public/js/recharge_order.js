import {getOrderRechargeDetail, getOrderRechargeList} from '@/api/recharge.js';

export default {
	data() {
		return {
			selectGoodsKeys: 0,
			// 订购日志所需列表数据
			list: [],
			//获取订单的页数
			page: 1,
			//每次获取订单的条数
			page_size: 8,
			// 订单搜索是用到的数据
			search_text: '',
			//订单类型
			trade_type: '',
			//初始时加载详情数据判断
			one_judge: true,
			//无限滚动请求锁
			listLock: true,
			scrollTop: 0,
			// 订单列表数据
			order_list: [],
			//订单详情数据
			order_detail: {}
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
			getOrderRechargeList({
				page: this.page,
				page_size: this.page_size,
				search_text: this.search_text
			}).then(res => {
				if (res.data.list.length == 0 && this.one_judge) {
					this.order_detail = {};
					this.one_judge = false;
				}
				if (res.code >= 0 && res.data.list.length != 0) {
					if (this.order_list.length == 0) {
						this.order_list = res.data.list;
					} else {
						this.order_list = this.order_list.concat(res.data.list);
					}

					//初始时加载一遍详情数据
					if (this.one_judge) {
						this.getOrderDetail(this.order_list[0].order_id);
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
		getOrderDetail(order_id, keys = 0, callback) {
			this.selectGoodsKeys = keys;
			getOrderRechargeDetail({order_id}).then(res => {
				if (res.code >= 0) {
					this.order_detail = res.data;
					if (typeof callback == 'function') {
						callback();
					}
					this.$forceUpdate();
					this.one_judge = false;
				}
			})
		},
	}
};
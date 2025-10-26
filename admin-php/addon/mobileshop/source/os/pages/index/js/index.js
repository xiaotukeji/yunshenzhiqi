import {getIndex} from '@/api/index'
import {getOrderStatistics} from '@/api/statistics'
import {getUserPermission} from '@/api/user'

export default {
	data() {
		return {
			procedureState: '',
			canvas: true,
			cWidth: '',
			cHeight: '',
			arr: [],
			transaction_statistics: 'stat_day',
			transaction_time: 7,
			order_transaction_time: 7,
			data: {
				shop_info: {
					group_name: '',
					site_name: '',
					category_name: ''
				},
				stat_day: {
					order_total: '0.00',
					order_count: 0,
					order_pay_count: 0,
					member_count: 0,
					visit_count: 0
				},
				stat_yesterday: {
					order_total: '0.00',
					order_count: 0,
					order_pay_count: 0,
					member_count: 0,
					visit_count: 0
				},
				num_data: {
					waitsend: 0,
					refund: 0,
					audit_refuse_count: 0
				},
				notice_list: []
			},
			order_info: {},
			order_total: {
				categories: [],
				series: []
			},
			order_pay_count: {
				categories: [],
				series: []
			},
			total_money: {
				order_pay_count: 0,
				order_total: 0
			},
			refCurr: '',

			pickerCurr: {
				order_total: 0,
				order_pay_count: 0
			},
			picker: [{
					date_value: 7,
					date_text: "7天"
				},
				{
					date_value: 15,
					date_text: "15天"
				},
				{
					date_value: 30,
					date_text: "30天"
				}
			],
			menuList: [
				{
					page: '/pages/goods/edit/index',
					img: 'public/uniapp/shop_uniapp/index/manage_good_send.png',
					name: 'PHYSICAL_GOODS_ADD',
					title: '商品发布'
				},
				{
					page: '/pages/goods/list',
					img: 'public/uniapp/shop_uniapp/index/manage_good.png',
					name: 'GOODS_MANAGE',
					title: '商品管理'
				},
				{
					page: '/pages/order/list',
					img: 'public/uniapp/shop_uniapp/index/manage_order.png',
					name: 'ORDER_MANAGE',
					title: '订单管理'
				},
				{
					page: '/pages/member/list',
					img: 'public/uniapp/shop_uniapp/index/member_card.png',
					name: 'MEMBER_LIST',
					title: '会员管理'
				},
				{
					page: '/pages/invoices/invoices',
					img: 'public/uniapp/shop_uniapp/index/invoice_setting.png',
					name: 'INVOICE_LIST',
					title: '发票管理'
				},
				{
					page: '/pages/storemanage/storemanage',
					img: 'public/uniapp/shop_uniapp/index/store_setting.png',
					name: 'STORE_LIST',
					title: '门店管理'
				},
				{
					page: '/pages/property/dashboard/index',
					img: 'public/uniapp/shop_uniapp/index/finance_survey.png',
					name: 'ACCOUNT_DASHBOARD_INDEX',
					title: '财务概况'
				},
				{
					page: '/pages/property/withdraw/list',
					img: 'public/uniapp/shop_uniapp/index/tixian.png',
					name: 'MEMBER_WITHDRAW_LIST',
					title: '会员提现'
				},
				{
					page: '/pages/property/settlement/list_store',
					img: 'public/uniapp/shop_uniapp/index/store_jiesuan.png',
					name: 'ADDON_STORE_SHOP_STORE_SETTLEMENT',
					title: '门店结算'
				},
				{
					page: '/pages/statistics/transaction',
					img: 'public/uniapp/shop_uniapp/index/tongji_jiaoyi.png',
					name: 'STAT_ORDER',
					title: '交易统计'
				},
				{
					page: '/pages/statistics/shop',
					img: 'public/uniapp/shop_uniapp/index/tongji_shop.png',
					name: 'STAT_SHOP',
					title: '店铺统计'
				},
				{
					page: '/pages/statistics/goods',
					img: 'public/uniapp/shop_uniapp/index/tongji_good.png',
					name: 'STAT_GOODS',
					title: '商品统计'
				},
				{
					page: '/pages/statistics/visit',
					img: 'public/uniapp/shop_uniapp/index/tongji_member.png',
					name: 'STAT_VISIT',
					title: '访问统计'
				},
				{
					page: '/pages/my/shop/config',
					img: 'public/uniapp/shop_uniapp/index/set_shop.png',
					name: 'SHOP_CONFIG',
					title: '店铺信息'
				},
				{
					page: '/pages/my/user/user',
					img: 'public/uniapp/shop_uniapp/index/set_member.png',
					name: 'USER_LIST',
					title: '用户管理'
				},
				{
					page: '/pages/my/statistics',
					img: 'public/uniapp/shop_uniapp/index/set_jiaoyi.png',
					name: 'ORDER_CONFIG_SETTING',
					title: '交易设置'
				},
				{
					page: '/pages/goods/config',
					img: 'public/uniapp/shop_uniapp/index/goods_setting.png',
					name: 'CONFIG_BASE_GOODS',
					title: '商品设置'
				},
				{
					page: '/pages/my/shop/contact',
					img: 'public/uniapp/shop_uniapp/index/set_address.png',
					name: 'SHOP_CONTACT',
					title: '联系地址'
				},
				{
					page: '/pages/verify/index',
					img: 'public/uniapp/shop_uniapp/index/verify.png',
					name: 'ORDER_VERIFY_CARD',
					title: '核销台'
				},
				{
					page: '/pages/verify/user',
					img: 'public/uniapp/shop_uniapp/index/verify_peo.png',
					name: 'ORDER_VERIFY_USER',
					title: '核销人员'
				}
			],
			handleMenu: []
		};
	},
	async onShow() {
		if (!this.$util.checkToken('/pages/index/index')) return;
		await this.initData();
	},
	async onPullDownRefresh() {
		await this.initData();
	},
	onLoad(){
		this.getPermission();
	},
	methods: {
		async initData() {
			await this.getData();
			this.getOrderInfo();
			this.$store.dispatch('getShopInfo');
		},
		//公告详情
		toNoticeDetail(val) {
			this.$util.redirectTo('/pages/notice/detail', {
				notice_id: val
			})
		},
		//获取首页信息
		async getData() {
			var res = await getIndex();
			if (res.code >= 0 && res.data) {
				this.data = res.data;
				//续签标识
				var renewObj = {};
				renewObj.is_reopen = res.data.is_reopen;
				renewObj.cert_id = res.data.shop_info.cert_id;
				uni.setStorage({
					key: 'renewObj',
					data: JSON.stringify(renewObj)
				});

			} else {
				this.$util.showToast({
					title: res.message
				});
			}
			uni.stopPullDownRefresh();
			if (this.$refs.loadingCover) this.$refs.loadingCover.hide();
		},
		//去认证
		toCert() {
			if (this.data.shop_info.cert_id) this.$util.redirectTo('/pages/cret/index');
		},
		//获取订单信息
		getOrderInfo() {
			var time = this.refCurr == 'order_total' ? this.transaction_time : this.order_transaction_time;
			getOrderStatistics(time).then(res=>{
				if (res.code >= 0 && res.data) {
					this.order_info = res.data;
					var timeTemp = [],
						timeTempYear = [];
					var time = this.refCurr == 'order_total' ? this.transaction_time : this.order_transaction_time;
					for (var index = (time - 1); index >= 0; index--) {
						timeTemp.push(this.getDay(-index));
						timeTempYear.push(this.getDay(-index, 1));
					}
					this.order_total.categories = this.order_pay_count.categories = timeTemp;
					this.order_total.series = [{
						name: '销售额',
						data: res.data.order_total,
						color: '#FF6A00',
						time: timeTempYear
					}];
					this.order_pay_count.series = [{
						name: '订单数',
						data: res.data.order_pay_count,
						color: '#FF6A00',
						time: timeTempYear
					}]
					if (this.arr.length) {
						this.$refs.order_total[0].changeData('order_total', this.order_total);
						this.total_money.order_total = this.sum(res.data.order_total, this.refCurr == 'order_total' ? 1 : '');
						
						this.$refs.order_pay_count[0].changeData('order_pay_count', this.order_pay_count);
						this.total_money.order_pay_count = this.sum(res.data.order_pay_count, this.refCurr == 'order_pay_count' ? 1 : '');
					} else {
						this.total_money.order_total = this.sum(res.data.order_total, 1);
						this.total_money.order_pay_count = this.sum(res.data.order_pay_count);
						this.initChart();
					}
				}
			});
		},
		//计算和
		sum(arr, toFixed = '') {
			var s = 0;
			arr.forEach(function(val, idx, arr) {
				s += parseFloat(val);
			}, 0);

			return s.toFixed(toFixed ? 2 : '');
		},
		//日期处理
		getDay(p_count, type = '') {
			var dd = new Date();
			dd.setDate(dd.getDate() + p_count); //获取p_count天后的日期
			var y = dd.getFullYear();
			var m = dd.getMonth() + 1; //获取当前月份的日期
			if (m < 10) {
				m = '0' + m;
			}
			var d = dd.getDate();
			if (d < 10) {
				d = '0' + d;
			}
			if (type) {
				return y + "-" + m + "-" + d;
			} else {
				return m + "-" + d;
			}
		},
		//时间段切换
		dayChange(val, id) {
			this.refCurr = id;
			if (id == 'order_total') {
				this.transaction_time = val;
			} else {
				this.order_transaction_time = val;
			}
			this.getOrderInfo();
		},
		transactionChange(val) {
			this.transaction_statistics = val;
		},
		//图表
		initChart() {
			this.cWidth = uni.upx2px(660);
			this.cHeight = uni.upx2px(400);
			this.getServerData();
		},
		//图表数据的处理
		getServerData() {
			var Data = {
				yAxisdisabled: true,
				xAxisgridColor: 'transparent',
				yAxisgridType: 'dash',
				yAxisgridColor: '#eeeeee',
				yAxisdashLength: 5,
				animation: true,
				enableScroll: true,
				scrollPosition: 'right',
				scrollColor: 'transparent',
				scrollBackgroundColor: 'transparent',
				extra: {
					area: {
						addLine: true,
						opacity: 0.5,
						width: 2,
						type: 'curve'
					}
				},
				legend: false,
			}
			this.order_total = Object.assign(this.order_total, Data, {
				'unit': "元"
			});
			this.order_pay_count = Object.assign(this.order_pay_count, Data, {
				unit: "笔"
			});
			var serverData = [{
					title: '销售额',
					opts: this.order_total,
					chartType: "area",
					extraType: "curve",
					id: "order_total",
				},
				{
					title: '订单数',
					opts: this.order_pay_count,
					chartType: "area",
					extraType: "curve",
					id: "order_pay_count",
				}
			];
			this.arr = serverData;
		},
		pendingLink(url, key, val) {
			this.$util.redirectTo(url);
			uni.setStorage({
				key: key,
				data: val
			});
		},
		imgError() {
			this.data.shop_info.logo = this.$util.getDefaultImage().default_headimg;
		},
		pickerChange(val, e) {
			this.pickerCurr[val] = e.detail.value;
			this.dayChange(this.picker[this.pickerCurr[val]].date_value, val)
		},
		getPermission() {
			getUserPermission().then(res=>{
				if (res.code == 0) {
					let menuList = [];
					if (res.data.length) {
						this.menuList.forEach((item, index) => {
							if (this.$util.inArray(item.name, res.data) != -1) menuList.push(item);
						})
					} else {
						menuList = this.menuList;
					}
					this.handleMenu = menuList.slice(0, 7);
				}
			})
		}
	},
	onShareAppMessage(res) {
		var title = '单商户手机管理端';
		var path = '/pages/index/index';
		return {
			title: title,
			path: path,
			success: res => {},
			fail: res => {}
		};
	},
};

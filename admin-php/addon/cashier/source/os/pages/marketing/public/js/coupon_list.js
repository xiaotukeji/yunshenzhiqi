import {closeCoupon, deleteCoupon} from '@/api/marketing.js';

export default {
	data() {
		return {
			option: {
				page_size: 10,
				coupon_name: '',
				type: '',
				status: '',
				use_channel: '',
			},
			coupon_type_id: "",
			flag: false,
			typeList: [{
				value: 'reward',
				label: '满减'
			}, {
				value: 'discount',
				label: '折扣'
			},],
			statusList: [{
				value: '1',
				label: '进行中'
			}, {
				value: '2',
				label: '已结束'
			}, {
					value: '-1',
					label: '已关闭'
				}],
			// validityTypeList:[{
			// 	value: '0',
			// 	label: '固定时间'
			// }, {
			// 	value: '1',
			// 	label: '相对时间'
			// },
			// {
			// 	value: '2',
			// 	label: '长期有效'
			// }],
			useChannelList:[
				{
					value: 'all',
					label: '线上线下使用'
				},
				{
					value: 'online',
					label: '线上使用'
				},
				{
					value: 'offline',
					label: '线下使用'
				},
			],
			cols: [{
				field: 'coupon_name',
				width: 15,
				title: '优惠券名称',
				align: 'left',
			}, {
				field: 'reward',
				title: '优惠券类型',
				align: 'left',
				width: 10,
				templet: function (data) {
					if (data.type == 'reward') {
						return '满减';
					} else {
						return '折扣';
					}
				}
			}, {
				title: '优惠金额/折扣',
				width: 10,
				align: 'left',
				templet: function (data) {
					if (data.type == 'reward') {
						return `<span style="padding-right: 15px;">￥${data.money}</span>`;
					} else {
						return `<span style="padding-right: 15px;">${data.discount}折</span>`;
					}
				}
			}, {
				field: 'count',
				title: '发放数量',
				width: 10,
				templet: function (data) {
					return data.is_show == 0 || data.count == -1 ? '无限制' : data.count;
				}
			}, {
				title: '剩余数量',
				width: 10,
				templet: function (data) {
					return data.is_show == 0 || data.count == -1 ? '无限制' : data.count - data.lead_count;
				}
			}, {
				title: '领取上限',
				width: 10,
				templet: function (data) {
					return data.is_show == 0 || data.max_fetch == 0 ? '无领取限制' : data.max_fetch + '张/人';
				}
			}, {
				title: '有效期限',
				unresize: 'false',
				width: 15,
				templet: (data) => {
					if (data.validity_type == 0) {
						return `失效期：${this.$util.timeFormat(data.end_time)}`
					} else if (data.validity_type == 1) {
						return `领取后，${data.fixed_term}天有效`
					} else {
						return '长期有效'
					}
				}
			},{
				field: 'use_channel_name',
				title: '适用场景',
				unresize: 'false',
				width: 10
			}, {
				field: 'status_name',
				title: '状态',
				width: 10
			}, {
				width: 10,
				title: '操作',
				align: 'right',
				action: true
			}],
		};
	},
	onLoad() {
	},
	methods: {
		switchStoreAfter() {
			this.searchFn();
		},
		selectCouponsType(index) {
			this.option.type = index == -1 ? '' : this.typeList[index].value;
		},
		selectStatus(index) {
			this.option.status = index == -1 ? '' : this.statusList[index].value;
		},
		selectUseChannel(index) {
			this.option.use_channel = index == -1 ? '' : this.useChannelList[index].value;
		},
		// selectValidityType(index){
		// 	this.option.validity_type = index == -1 ? '' : this.validityTypeList[index].value;
		// },
		// 搜索商品
		searchFn() {
			this.$refs.couponListTable.load({
				page: 1
			});
		},
		resetFn() {
			this.option = {
				page_size: 10,
				coupon_name: '',
				type: '',
				status: '',
			}
			this.$refs.couponListTable.load({
				page: 1,
				coupon_name: '',
				type: '',
				status: '',
			});
		},
		add() {
			this.$util.redirectTo('/pages/marketing/edit_coupon');
		},
		detail(coupon_type_id) {
			this.$util.redirectTo('/pages/marketing/coupon_detail', {
				coupon_type_id
			});
		},
		edit(coupon_type_id) {
			this.$util.redirectTo('/pages/marketing/edit_coupon', {
				coupon_type_id
			});
		},
		closeOpen(coupon_type_id) {
			this.coupon_type_id = coupon_type_id
			this.$refs.closeCouponsPop.open()
		},
		close() {
			if (this.flag) return false;
			this.flag = true;
			this.$refs.closeCouponsPop.close()
			closeCoupon(this.coupon_type_id).then(res => {
				if (res.code >= 0) {
					this.flag = false;

					this.$refs.couponListTable.load();
				}
			})
		},
		deleteOpen(coupon_type_id) {
			this.coupon_type_id = coupon_type_id
			this.$refs.deleteCouponsPop.open()
		},
		del() {
			if (this.flag) return false;
			this.flag = true;
			this.$refs.deleteCouponsPop.close()
			deleteCoupon(this.coupon_type_id).then(res => {
				if (res.code >= 0) {
					this.flag = false;

					this.$refs.couponListTable.load();
				}
			})
		},
		promotion(coupon_type_id){
			this.$refs.promotionPop.open({coupon_type_id})
		}
	}
}
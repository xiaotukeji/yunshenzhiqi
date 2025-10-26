import {
	getMemberInfoById,
	getMemberLevelList,
	getCouponTypeList,
	sendMemberCoupon,
	applyingMembershipCard
} from '@/api/member'

export default {
	data() {
		return {
			pageSize: 8,
			sex: [{
				text: '未知',
				value: 0
			}, {
				text: '男',
				value: 1
			}, {
				text: '女',
				value: 2
			}],
			sendCoupon: {
				list: [],
				page: 1
			},
			memberLevelList: [],
			applyMember: {
				level_id: '',
				member_level: '',
				member_level_name: '',
				member_code: ''
			}
		};
	},
	created() {
		this.getMemberLevel();
	},
	methods: {
		open() {
			this.getMemberInfo(); // 保证数据实时性
			this.$refs.memberPop.open();
		},
		getMemberInfo() {
			getMemberInfoById(this.globalMemberInfo.member_id).then(res => {
				if (res.code >= 0) {
					res.data.birthday = res.data.birthday > 0 ? this.$util.timeFormat(res.data.birthday, 'Y-m-d') : '--';
					this.$store.commit('app/setGlobalMemberInfo', res.data);
				}
			});
		},
		getMemberLevel() {
			this.memberLevelList = [];
			getMemberLevelList().then(res => {
				if (res.code == 0 && res.data) {
					for (let i in res.data) {
						this.memberLevelList.push({
							label: res.data[i]['level_name'],
							value: res.data[i]['level_id'].toString(),
							disabled: false
						});
					}
				}
			});
		},
		selectMemberLevel(index, item) {
			if (index >= 0) {
				this.applyMember.level_id = item.value;
				this.applyMember.member_level = item.value;
				this.applyMember.member_level_name = item.label;
			} else {
				this.applyMember.level_id = '';
				this.applyMember.member_level = item.value;
				this.applyMember.member_level_name = item.label;
			}
		},
		// 客户操作
		memberAction(type) {
			switch (type) {
				case 'sendCoupon':
					this.getCouponList();
					this.$refs.sendCouponPop.open('center');
					break;
				case 'applyMember':
					this.$refs.applyMemberPop.open();
					break;
			}
		},
		popClose(type) {
			this.$refs[type + 'Pop'].close();
		},
		//获取发放优惠券列表
		getCouponList() {
			let data = {
				page: this.sendCoupon.page,
				page_size: 7
			};
			getCouponTypeList(data).then(res => {
				if (res.code >= 0) {
					if (this.sendCoupon.page == 1) this.sendCoupon.list = [];
					if (res.data.list && res.data.list.length) {
						res.data.list.forEach((item, index) => {
							if (item.validity_type == 0) item.validity_name = '失效日期：' + this.$util.timeFormat(item.end_time);
							else if (item.validity_type == 1) item.validity_name = '领取后，' + item.fixed_term + '天有效';
							else item.validity_name = '长期有效';
							item.num = 0;
						});
					}
					this.sendCoupon.list = this.sendCoupon.list.concat(res.data.list);
					if (res.data.page_count >= this.sendCoupon.page) this.sendCoupon.page++;
				}
			});
		},
		// 发放数量
		dec: function (item) {
			if (item.num > 0) {
				item.num = item.num - 1;
			}
		},
		inc: function (item) {
			item.num = item.num + 1;
		},
		// 发放优惠券
		sendCouponFn() {
			if (!this.sendCoupon.list || !this.sendCoupon.list.length) return false;
			let data = {};
			data.member_id = this.globalMemberInfo.member_id;
			data.coupon_data = '';
			let couponDataArr = [];

			this.sendCoupon.list.forEach((item, index) => {
				if (item.num > 0) {
					let obj = {};
					obj.coupon_type_id = item.coupon_type_id;
					obj.num = item.num;
					couponDataArr.push(obj);
				}
			});
			if (couponDataArr.length <= 0) return false;
			data.coupon_data = JSON.stringify(couponDataArr);
			sendMemberCoupon(data).then(res => {
				this.$util.showToast({
					title: res.message
				});
				if (res.code >= 0) {
					this.sendCoupon.page = 1;
					this.sendCoupon.list = [];
					this.getMemberInfo();
					this.$refs.sendCouponPop.close();
				}
			});
		},
		//打开会员卡项
		showMemberCard() {
			this.$refs.memberCardPopup.open();
		},
		// 办理会员卡
		saveApplyMember() {
			if (!this.applyMember.level_id) {
				this.$util.showToast({
					title: '请选择会员卡等级'
				});
				return false;
			}
			applyingMembershipCard({
				member_id: this.globalMemberInfo.member_id,
				level_id: this.applyMember.level_id,
				member_code: this.applyMember.member_code
			}).then(res => {
				this.$util.showToast({
					title: res.message
				});
				if (res.code >= 0) {
					this.getMemberInfo();
					this.popClose('applyMember');
				}
			});
		},
		headError(item) {
			item.headimg = this.defaultImg.head;
		}
	}
}
import {
	getMemberInfoById,
	getMemberLevelList,
	getCouponTypeList,
	sendMemberCoupon,
	editMember,
	modifyMemberPoint,
	modifyMemberBalance,
	modifyMemberGrowth,
	applyingMembershipCard
} from '@/api/member'

export default {
	props: {
		memberId: {
			type: [String, Number],
			default: 0
		}
	},
	data() {
		return {
			pageSize: 8,
			endTime: '',
			memberInfo: null,
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
			pointData: {
				num: 0,
				desc: ''
			},
			growthData: {
				num: 0,
				desc: ''
			},
			balanceData: {
				num: 0,
				desc: ''
			},
			option: {},
			sendCoupon: {
				list: [],
				page: 1
			},
			memberLevelList: [],
			applyMember: {
				level_id: '',
				member_level_name: '',
				member_code: ''
			},
			couponCols: [{
				width: 15,
				title: '优惠券名称',
				align: 'left',
				field: 'coupon_name'
			}, {
				width: 7,
				title: '类型',
				align: 'left',
				templet: function (data) {
					if (data.type == 'reward') return '满减';
					if (data.type == 'discount') return '折扣';
				}
			}, {
				width: 18,
				title: '优惠金额',
				align: 'left',
				templet: function (data) {
					if (data.type == 'reward') {
						var html = `满${data.at_least}元减${data.money}`;
						return `<view title="${html}">${html}</view>`;
					}
					if (data.type == 'discount') {
						var text = '满' + data.at_least + '元打' + data.discount + '折';
						if (data.discount_limit) text += '（最多抵扣' + data.discount_limit + '元）';
						return '<view title="' + text + '">' + text + '</view>';
					}
				}
			}, {
				width: 17,
				title: '有效期',
				align: 'center',
				templet: data => {
					if (data.end_time) return this.$util.timeFormat(data.end_time);
					else return '长期有效';
				}
			}, {
				width: 10,
				title: '状态',
				align: 'center',
				return: data => {
					if (data.state == 1) return '未使用';
					if (data.state == 2) return '已使用';
					if (data.state == 3) return '已过期';
				}
			},{
				title: '适用场景',
				field: 'use_channel_name',
				width: 15,
				align: 'left',
			},  {
				width: 18,
				title: '领取时间',
				align: 'right',
				templet: data => {
					return this.$util.timeFormat(data.fetch_time);
				}
			}],
			pointCols: [{
				width: 20,
				title: '积分',
				align: 'left',
				field: 'account_data'
			}, {
				width: 25,
				title: '发生方式',
				align: 'left',
				field: 'type_name'
			}, {
				width: 25,
				title: '发生时间',
				align: 'left',
				templet: data => {
					var html = this.$util.timeFormat(data.create_time);
					return html;
				}
			}, {
				width: 30,
				title: '备注',
				align: 'left',
				field: 'remark'
			}],
			balanceCols: [{
				width: 10,
				title: '账户类型',
				align: 'left',
				field: 'account_type_name'
			}, {
				width: 15,
				title: '余额',
				align: 'left',
				field: 'account_data'
			}, {
				width: 20,
				title: '发生方式',
				align: 'left',
				field: 'type_name'
			}, {
				width: 25,
				title: '发生时间',
				align: 'left',
				templet: data => {
					var html = this.$util.timeFormat(data.create_time);
					return html;
				}
			}, {
				width: 30,
				title: '备注',
				align: 'left',
				field: 'remark'
			}],
			growthCols: [{
				width: 20,
				title: '成长值',
				align: 'left',
				field: 'account_data'
			}, {
				width: 25,
				title: '发生方式',
				align: 'left',
				field: 'type_name'
			}, {
				width: 25,
				title: '发生时间',
				align: 'left',
				templet: data => {
					var html = this.$util.timeFormat(data.create_time);
					return html;
				}
			}, {
				width: 30,
				title: '备注',
				align: 'left',
				field: 'remark'
			}],
		};
	},
	created() {
		this.getMemberInfo();
		this.getMemberLevel();
		let date = new Date();
		var y = date.getFullYear();
		var m = date.getMonth() + 1;
		var d = date.getDate();
		this.endTime = y + '-' + m + '-' + d;
	},
	watch: {
		memberId: function () {
			this.getMemberInfo();
		}
	},
	methods: {
		checkAdmin() {
			if (this.userInfo && this.userInfo.is_admin == 0) {
				// 检查当前账号是否有修改手机号的权限
				var isAgree = false;
				this.userInfo.user_group_list.forEach((item) => {
					if (item.store_id == this.globalStoreInfo.store_id) {
						if (item.menu_array.indexOf('member_edit') != -1) {
							isAgree = true;
						}
					}
				});
				if (isAgree) {
					return false;
				}
			}
			return true;
		},
		getMemberInfo() {
			getMemberInfoById(this.memberId).then(res => {
				if (res.code >= 0) {
					res.data.birthday = res.data.birthday > 0 ? this.$util.timeFormat(res.data.birthday, 'Y-m-d') : '';
					this.memberInfo = res.data;
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
				this.applyMember.member_level_name = item.label;
				this.memberInfo.member_level = item.value;
			} else {
				this.applyMember.level_id = '';
				this.applyMember.member_level_name = '';
				this.memberInfo.member_level = '';
			}
		},
		// 客户操作
		memberAction(type) {
			switch (type) {
				case 'memberInfo':
					this.$refs.memberInfoPop.open('center');
					break;
				case 'point':
					this.$refs.pointPop.open('center');
					break;
				case 'balance':
					this.$store.commit('app/setGlobalMemberInfo', this.memberInfo);
					this.$util.redirectTo('/pages/recharge/index');
					break;
				case 'sendCoupon':
					this.getCouponList();
					this.$refs.sendCouponPop.open('center');
					break;
				case 'growth':
					this.$refs.growthPop.open('center');
					break;
				case 'couponList':
					this.option = {
						member_id: this.memberId
					};
					this.$refs.couponListPop.open('center');
					break;
				case 'cardList':
					this.option = {
						member_id: this.memberId,
						status:1,
					};
					this.$refs.memberCardRecord.open('center');
					break;
				case 'pointList':
					// 积分列表
					this.option = {
						member_id: this.memberId,
						account_type: 'point'
					};
					this.$refs.pointListPop.open();
					break;
				case 'balanceList':
					// 余额列表
					this.option = {
						member_id: this.memberId,
						account_type: 'balance'
					};
					this.$refs.balanceListPop.open();
					break;
				case 'growthList':
					// 成长值列表
					this.option = {
						member_id: this.memberId,
						account_type: 'growth'
					};
					this.$refs.growthListPop.open();
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
			data.member_id = this.memberInfo.member_id;
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
		//修改客户信息
		saveMemberInfo() {
			let data = {
				nickname: this.memberInfo.nickname,
				sex: this.memberInfo.sex,
				birthday: this.memberInfo.birthday,
				member_id: this.memberInfo.member_id,
				level_id: this.memberInfo.member_level
			};
			if (this.checkAdmin()) {
				data.mobile = this.memberInfo.mobile;
			}
			editMember(data).then(res => {
				this.$util.showToast({
					title: res.message
				});
				if (res.code >= 0) {
					this.getMemberInfo();
					this.popClose('memberInfo');
				}
			});
		},
		// 调整积分
		savePoint() {
			if (parseInt(this.pointData.num) < 0 && parseInt(this.memberInfo.point) < parseInt(this.pointData.num * -1)) {
				this.$util.showToast({
					title: '调整数额与当前积分之和不能小于0'
				});
				return false;
			}
			modifyMemberPoint({
				member_id: this.memberInfo.member_id,
				adjust_num: this.pointData.num,
				remark: this.pointData.desc
			}).then(res => {
				this.$util.showToast({
					title: res.message
				});
				if (res.code >= 0) {
					this.pointData.num = 0;
					this.pointData.desc = '';
					this.getMemberInfo();
					this.popClose('point');
				}
			});
		},
		// 调整余额
		saveBalance() {
			modifyMemberBalance({
				member_id: this.memberInfo.member_id,
				adjust_num: this.balanceData.num,
				remark: this.balanceData.desc
			}).then(res => {
				this.$util.showToast({
					title: res.message
				});
				if (res.code >= 0) {
					this.balanceData.num = 0;
					this.balanceData.desc = '';
					this.getMemberInfo();
					this.popClose('balance');
				}
			});
		},
		// 调整成长值
		saveGrowth() {
			if (parseInt(this.growthData.num) < 0 && parseInt(this.memberInfo.growth) < parseInt(this.growthData.num * -1)) {
				this.$util.showToast({
					title: '调整数额与当前成长值之和不能小于0'
				});
				return false;
			}
			modifyMemberGrowth({
				member_id: this.memberInfo.member_id,
				adjust_num: this.growthData.num,
				remark: this.growthData.desc
			}).then(res => {
				this.$util.showToast({
					title: res.message
				});
				if (res.code >= 0) {
					this.growthData.num = 0;
					this.growthData.desc = '';
					this.getMemberInfo();
					this.popClose('growth');
				}
			});
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
				member_id: this.memberInfo.member_id,
				level_id: this.applyMember.level_id,
				member_code: this.applyMember.member_code
			}).then(res => {
				this.$util.showToast({
					title: res.message
				});
				if (res.code >= 0) {
					this.$root.page = 1;
					this.$root.search_text = 1;
					this.$root.getMemberListFn();
					this.popClose('applyMember');
				}
			});
		},
		headError(item) {
			item.headimg = this.defaultImg.head;
		}
	}
}
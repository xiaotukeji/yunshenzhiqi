import {getMemberList, getMemberInfoById, addMember} from '@/api/member.js'

let _self;
export default {
	data() {
		return {
			memberList: [],
			page: 1,
			pageSize: 20,
			searchMobile: '',
			memberId: 0,
			currentMemberInfo: null,
			endTime: '',
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
			addMemberData: {
				mobile: '',
				nickname: '',
				sex: 0,
				birthday: ''
			},
			// 第一次请求列表、详情渲染判断
			one_judge: true,
			//无限滚动请求锁
			memberListLock: true,
			scrollTop: 0,
		};
	},
	onLoad(data) {
		_self = this;
		this.getMemberListFn(data.member_id || 0);
		let date = new Date();
		var y = date.getFullYear();
		var m = date.getMonth() + 1;
		var d = date.getDate();
		this.endTime = y + '-' + m + '-' + d;
	},
	methods: {
		// 查询客户列表
		getMemberListFn(member_id = 0) {
			if (!this.memberListLock) return false;
			let data = {
				page: this.page,
				page_size: this.pageSize,
				search_text: this.searchMobile,
			};
			getMemberList(data).then(res => {
				if (res.code >= 0) {
					if (this.page == 1) this.memberList = [];
					this.memberList = this.memberList.concat(res.data.list);
					this.memberList.forEach((item) => {
						if (item.mobile) {
							if (this.userInfo && this.userInfo.is_admin == 0) {
								// 非管理员，不能查看会员手机号
								item.mobile = item.mobile.substring(0, 4 - 1) + '****' + item.mobile.substring(6 + 1);
							}
						} else {
							item.mobile = '';
						}
					});
					if (this.page == 1 && this.memberList.length > 0) {
						// 订单页面跳转过来查看 会员详情
						this.memberId = member_id || this.memberList[0]['member_id'];
						this.one_judge = false;
					}else if(this.page == 1){
						this.one_judge = false;
						this.memberId = 0
					} else {
						this.one_judge = false;
					}
					if (this.page == 1) {
						this.scrollTop = 0
					}
					if (res.data.list.length < data.page_size) {
						this.memberListLock = false
					} else {
						this.page++;
					}

				}
			})
		},
		scroll(e) {
			this.scrollTop = e.detail.scrollTop
		},
		searchMember() {
			this.page = 1;
			this.one_judge = true;
			this.memberListLock = true;
			this.getMemberListFn();
		},
		selectMember(member_id) {
			this.memberId = member_id;
		},
		getMemberInfo() {
			getMemberInfoById(this.memberId).then(res => {
				if (res.code >= 0) {
					this.currentMemberInfo = res.data;
					this.currentMemberInfo.birthday = res.data.birthday > 0 ? this.$util.timeFormat(res.data.birthday, 'Y-m-d') : '';
					this.one_judge = false;
				}
			})
		},
		verify() {
			if (!this.addMemberData.mobile) {
				this.$util.showToast({
					title: '请输入会员手机号'
				});
				return false;
			}
			if (!this.$util.verifyMobile(this.addMemberData.mobile)) {
				this.$util.showToast({
					title: '请输入正确的手机号码'
				});
				return false;
			}

			return true;
		},
		// 添加客户
		addMemberFn() {
			if (this.verify()) {
				if (this.flag) return;
				this.flag = true;
				addMember(this.addMemberData).then(res => {
					if (res.code == 0 && res.data) {
						this.addMemberData = {
							mobile: '',
							nickname: '',
							sex: 0,
							birthday: ''
						};
						this.page = 1;
						this.one_judge = true;
						this.memberListLock = true;
						this.getMemberListFn();
						this.$refs.addMemberPop.close();
					} else {
						this.$util.showToast({
							title: '该手机号已注册为客户'
						});
					}
					this.flag = false;
				})
			}
		},
		headError(item) {
			item.headimg = this.defaultImg.head;
		}
	}
}
import {getMemberList,getMemberInfoById, getMemberLevelList, addMember, searchMemberByMobile} from '@/api/member.js';
import {mapGetters} from 'vuex';

export default {
	data() {
		return {
			searchText: '',
			page: 1,
			memberList: [],
			memberId: '',
			memberData: {
				sex: 0,
				mobile: '',
				nickname: '',
				birthday: '',
				member_level: '',
				member_level_name: ''
			},
			memberLevelList: [], // 会员等级
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
			memberType: 'login',
			flag: false,
			inputFocus: false,
			isPhone: false,
			searchFinish: false // 搜索是否完成
		};
	},
	created() {
		this.getMemberLevel();
	},
	computed: {
		...mapGetters(['memberSearchWayConfig'])
	},
	watch: {
		memberSearchWayConfig: {
			immediate: true,
			handler(newVal, oldVal) {
				if(newVal) {
					if(newVal.way == 'list'){
						this.getMemberListFn();
					}
				}
			}
		}
	},
	methods: {
		searchMemberInputBlur(){
			this.inputFocus = false;
			//强制聚焦处理
			if(this.memberType = 'login'){
				this.$nextTick(() => {
				  this.inputFocus = true;
				});
			}
		},
		open(callback) {
			this.memberId = this.globalMemberInfo ? this.globalMemberInfo.member_id + '' : '';
			this.$refs.memberPopup.open('', callback);
			this.inputFocus = true;
			this.searchFinish = false;
		},
		// 查询会员列表
		searchMemberByMobileFn() {
			setTimeout(() => {
				if (!this.searchText) return false;
				searchMemberByMobile({
					mobile: this.searchText
				}).then((res) => {
					if (res.code >= 0) {
						this.$store.commit('app/setGlobalMemberInfo', res.data);
						this.initData();
						this.$refs.memberPopup.close();
					} else {
						if (res.data > 1) {
							this.$util.showToast({
								title: res.message
							});
							return false;
						}
						var regex = /^1[3-9]\d{9}$/;
						if (res.data == 0 && regex.test(this.searchText)) {
							this.isPhone = true;
							this.$refs.emptyPopup.open();
							return false;
						}
						if (res.data == 0) {
							this.isPhone = false;
							this.$refs.emptyPopup.open();
							return false;
						}
					}
				});
			}, 200)
		},
		getMemberInfo(memberId, callback) {
			this.memberId = memberId;
			getMemberInfoById(memberId).then(res => {
				if (res.code == 0 && res.data) {
					this.$store.commit('app/setGlobalMemberInfo', res.data);
					if (callback) callback();
					this.initData();
					this.$refs.memberPopup.close();
				} else {
					this.$util.showToast({
						title: '未获取到会员信息'
					});
				}
			})
		},
		/******************************** 录入会员 ********************************/
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
		// 选择会员等级
		selectMemberLevel(index, item) {
			if (index >= 0) {
				this.memberData.member_level = item.value;
				this.memberData.member_level_name = item.label;
			} else {
				this.memberData.member_level = '';
				this.memberData.member_level_name = '';
			}
			this.$forceUpdate();
		},
		// 选择时间
		changeTime(e) {
			this.memberData.birthday = e;
		},
		verify() {
			if (!this.memberData.mobile) {
				this.$util.showToast({
					title: '请输入会员手机号'
				});
				return false;
			}
			if (!this.$util.verifyMobile(this.memberData.mobile)) {
				this.$util.showToast({
					title: '请输入正确的手机号码'
				});
				return false;
			}
			return true;
		},
		// 确定录入
		addMemberFn() {
			if (this.verify()) {
				if (this.flag) return;
				this.flag = true;
				addMember(this.memberData).then(res => {
					if (res.code == 0 && res.data) {
						this.memberType = 'login';
						this.getMemberInfo(res.data)
					} else {
						this.$util.showToast({
							title: res.message
						});
					}
					this.flag = false;
				})
			}
		},
		closedFn() {
			this.memberType = "login";
			this.$refs.memberPopup.close();
		},
		memberEmptyRegister() {
			this.memberType = "register";
			this.memberData.mobile = this.searchText;
			this.$refs.emptyPopup.close();
		},
		initData() {
			this.searchText = '';
			this.memberData.sex = 0;
			this.memberData.mobile = '';
			this.memberData.nickname = '';
			this.memberData.birthday = '';
			this.memberData.member_level = '';
			this.memberData.member_level_name = '';
		},
		stayTuned() {
			this.$util.showToast({
				title: '敬请期待'
			});
		},
		getMemberListFn(isSearch){
			getMemberList({
				page: this.page,
				page_size: 12,
				search_text: this.searchText
			}).then((res)=>{
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
							item.mobile = '--';
						}
					});

					if (isSearch) {
						// 默认选中第一个搜索结果
						this.memberId = 0;
						if (this.memberList.length) {
							this.memberId = this.memberList[0].member_id;
						}
					}
					this.searchFinish = true;
					if (res.data.page_count >= this.page) this.page++;
				}
			})
		},
		searchMemberByList(){
			this.page = 1;
			this.getMemberListFn(Boolean(this.searchText));
		}
	}
};
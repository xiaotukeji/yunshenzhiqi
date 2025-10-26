import {
	getReserveStatus,
	getReserveConfig,
	getReserveWeekday,
	getReserveLists,
	getAppointmentProjectList,
	getEmployeeList,
	editReserve,
	addReserve,
	cancelReserve,
	getReserveDetail,
	reserveToStore,
	reserveConfirm,
	reserveComplete
} from '@/api/reserve'
import {
	getMemberInfoBySearchMember
} from '@/api/member'

export default {
	data() {
		return {
			activeStyle: {},
			active: 0,
			weeks: [],
			status: [],
			length: 0, //周
			//预约记录操作
			operation: {
				arrived_store: [{
					title: '确认完成',
					event: 'complet'
				}],
				wait_confirm: [{
					title: '确认预约',
					event: 'confirm'
				}, {
					title: '更改预约',
					event: 'update'
				}, {
					title: '取消预约',
					event: 'cancel'
				}],
				wait_to_store: [{
					title: '确认到店',
					event: 'tostore'
				}, {
					title: '取消预约',
					event: 'cancel'
				}]
			},
			yuYueTime: [], //预约时间段
			yuYueConfig: {},
			yuYueData: {
				time: '', //时间
				date: '', //日期
				member_id: '',
				member: {},
				goods: [],
				desc: '',
				reserve_id: 0
			},
			searchMobile: '',
			flag: false,
			yuYueDetail: null,
			weekDate: {
				start: '-',
				end: '-'
			},
			week: ['周日', '周一', '周二', '周三', '周四', '周五', '周六'],
			current: '',
			yuYueDateType: 'week',
			goodsList: [],
			allServicerList: [], //所有的员工
			servicerList: [], //所有的员工
			yuyueList: [],
			yuyuePage: 1,
			yuyueSearchText: '',
			reserveId: 0,
			yuyueInfo: null,
			toDay: '', // 今天日期
		};
	},
	onLoad() {
		uni.hideTabBar();
		this.init();
	},
	onReady() {
		this.tabActive('tab');
	},
	methods: {
		init() {
			this.weeks = [];
			this.getReserveStatusFn(); // 预约状态
			this.getReserveConfigFn(); // 预约配置
			this.getAppointmentProjectListFn(); // 预约项目列表
			this.getEmployeeListFn(); // 员工列表
			this.getWeekReserve(); // 预约看板/周
			this.getYuyueList(); // 预约记录列表
			let date = new Date();
			var y = date.getFullYear();
			var m = date.getMonth() + 1;
			var d = date.getDate();
			this.toDay = y + '-' + m + '-' + d;
		},
		tabActive(id) {
			const query = uni.createSelectorQuery().in(this);
			var tab;
			query.select('#' + id).boundingClientRect(data => {
				tab = data;
			});
			query.select('#' + id + ' .active-bar .text').boundingClientRect(data => {
				this.activeStyle = {
					width: data.width + 'px',
					transform: 'translateX(' + (data.left - tab.left) + 'px)'
				};
			}).exec();
		},
		switchTab(value) {
			this.active = value;
			this.$nextTick(() => {
				this.tabActive('tab');
			});
		},
		swiperChange(e) {
			this.active = e.detail.current;
			this.$nextTick(() => {
				this.tabActive('tab');
			});
		},
		// 预约状态
		getReserveStatusFn() {
			getReserveStatus().then(res => {
				if (res.code >= 0) {
					this.status = res.data;
				}
			});
		},
		// 预约配置
		getReserveConfigFn() {
			getReserveConfig().then(res => {
				if (res.code >= 0) {
					this.yuYueConfig = res.data;
				}
			});
		},

		// ******************** 预约看板 ********************

		// 查询预约记录（每周）
		getWeekReserve() {
			if (this.flag) return;
			this.flag = true;
			getReserveWeekday({
				length: this.length
			}).then(res => {
				if (res.code >= 0) {
					this.weeks = res.data;
					this.$refs.loading.hide();
					this.weekDate.start = this.$util.timeFormat(this.weeks[0].start, 'Y-m-d');
					this.weekDate.end = this.$util.timeFormat(this.weeks[this.weeks.length - 1].end, 'Y-m-d');
					for (let i in this.weeks) {
						this.getReserve(i);
						if (i == this.weeks.length - 1) {
							setTimeout(() => {
								this.flag = false;
							}, 500);
						}
					}
				}
				this.flag = false;
			});
		},
		//获取预约分页数据
		getReserve(index) {
			let currentWeek = this.weeks[index];
			if (!currentWeek.page) currentWeek.page = 1;
			getReserveLists({
				page: currentWeek.page,
				start: currentWeek.start,
				end: currentWeek.end
			}).then(res => {
				if (res.code >= 0) {
					let data = res.data;
					if (currentWeek.page == 1) {
						currentWeek.data = {
							list: [],
							page_count: data.page_count,
							count: data.count
						};
						currentWeek.data['list'] = data.list;
					} else {
						currentWeek.data['list'] = currentWeek.data['list'].concat(data.list);
					}
					if (data.page_count >= currentWeek.page) currentWeek.page++;
					this.$forceUpdate();
				}
			});
		},
		//上一周
		prevWeek() {
			this.$refs.loading.show();
			--this.length;
			this.getWeekReserve();
		},
		//下一周
		nextWeek() {
			this.$refs.loading.show();
			++this.length;
			this.getWeekReserve();
		},
		// ******************** 添加/编辑预约 ********************
		// 查询预约项目列表
		getAppointmentProjectListFn() {
			getAppointmentProjectList({
				page: 1,
				page_size: 0
			}).then(res => {
				if (res.code >= 0) {
					this.goodsList = res.data.list;
				}
			});
		},
		// 查询员工列表
		getEmployeeListFn() {
			getEmployeeList().then(res => {
				if (res.code >= 0) {
					this.allServicerList = res.data;
				}
			});
		},
		//添加预约
		addYuyue() {
			this.yuYueData = {
				reserve_id: 0,
				member_id: '',
				member: {},
				time: '', //时间
				date: this.toDay, //日期
				goods: [{}],
				desc: '',
			};
			this.reserveId = 0;
			this.handleYuyueDate();
			this.$refs.addYuyuePop.open();
		},
		closeYuyuePop() {
			this.yuYueData = {
				time: '', //时间
				date: '', //日期
				member_id: '',
				member: {},
				goods: [{}],
				desc: '',
				reserve_id: 0
			};
			this.$refs.addYuyuePop.close();
		},
		// 查询会员信息
		searchMember() {
			if (this.searchMobile.length == 0) {
				this.$util.showToast({
					title: '请输入客户手机号'
				});
				return;
			}
			if (!this.$util.verifyMobile(this.searchMobile)) {
				this.$util.showToast({
					title: '手机号格式不正确'
				});
				return;
			}
			getMemberInfoBySearchMember({
				search_text: this.searchMobile
			}).then(res => {
				if (res.data) {
					this.yuYueData.member_id = res.data.member_id;
					this.yuYueData.member = res.data;
				} else {
					this.yuYueData.member_id = '';
					this.yuYueData.member = {};
					this.$util.showToast({
						title: '客户未找到'
					});
				}
			});
		},
		//处理预约时间段
		handleYuyueDate() {
			let time_list = [];
			let start = this.yuYueConfig.start / 60;
			let end = this.yuYueConfig.end / 60;
			let date = new Date();
			var y = date.getFullYear();
			var m = date.getMonth() + 1;
			var d = date.getDate();
			let time = date.getHours() * 60 + date.getMinutes();

			let yuyue_time_stamp = this.$util.timeTurnTimeStamp(this.yuYueData.date);
			let time_stamp = this.$util.timeTurnTimeStamp(y + '-' + m + '-' + d);
			// if(time > start) start = time;

			for (let i = start; i < end; i++) {
				if (i % this.yuYueConfig.interval == 0) {
					let data = {
						label: (Math.floor(i / 60) < 10 ? '0' + Math.floor(i / 60) : Math.floor(i / 60)) + ':' + (i % 60 == '0' ? '00' : i % 60),
						value: (Math.floor(i / 60) < 10 ? '0' + Math.floor(i / 60) : Math.floor(i / 60)) + ':' + (i % 60 == '0' ? '00' : i % 60),
						disabled: false
					};
					if (yuyue_time_stamp < time_stamp) data.disabled = true;
					if (yuyue_time_stamp == time_stamp && time > i) data.disabled = true;

					let week = new Date(this.yuYueData.date).getDay();

					let yuyue_week = this.yuYueConfig.week;
					let config_week = [];

					for (let i in yuyue_week) config_week.push(parseInt(yuyue_week[i]));

					if (config_week.indexOf(week) === -1) data.disabled = true;
					time_list.push(data);
				}
			}
			this.yuYueTime = time_list;
		},
		// 监听预约时间
		changeYuyueTime(time) {
			this.yuYueData.date = time;
			this.handleYuyueDate();
		},
		// 选择预约时间·
		selectYuYueTime(index, item) {
			if (index >= 0) {
				this.yuYueData.time = item.value;
			} else {
				this.yuYueData.time = '';
			}
		},
		// 设置项目
		selectGoods(data, index) {
			this.yuYueData.goods[index] = Object.assign(this.yuYueData.goods[index], JSON.parse(JSON.stringify(data)));
			this.$forceUpdate();
		},
		// 加载员工列表
		loadServicer(index) {
			this.servicerList = this.allServicerList;
		},
		// 设置员工
		selectServicer(data, index) {
			this.yuYueData.goods[index].uid = data.uid;
			this.yuYueData.goods[index].username = data.username;
			this.$forceUpdate();
		},
		// 添加项目
		addService() {
			this.yuYueData.goods.push({});
		},
		// 删除项目
		deleteService(index) {
			if (this.yuYueData.goods.length == 1) {
				this.$util.showToast({
					title: '至少需要有一项项目'
				});
			} else {
				this.yuYueData.goods.splice(index, 1);
			}
		},
		// 预约验证
		verify() {
			if (!this.yuYueData.member_id) {
				this.$util.showToast({
					title: '请选择会员'
				});
				return false;
			}
			if (!this.yuYueData.date || !this.yuYueData.time) {
				this.$util.showToast({
					title: '请设置到店时间'
				});
				return false;
			}

			if (!this.yuYueData.goods.length) {
				this.$util.showToast({
					title: '请选择预约项目'
				});
				return false;
			}

			for (let i in this.yuYueData.goods) {
				if (!this.yuYueData.goods[i]['goods_id']) {
					this.$util.showToast({
						title: '请选择预约项目'
					});
					return false;
				}
			}

			return true;
		},
		// 添加/编辑预约
		yuYueSubmit() {
			if (this.verify()) {
				if (this.flag) return;
				this.flag = true;
				let data = Object.assign({}, this.yuYueData);
				data.goods = JSON.stringify(data.goods);
				data.member = JSON.stringify(data.member);
				let save = data.reserve_id ? editReserve : addReserve;
				save(data).then(res => {
					this.$util.showToast({
						title: res.message
					});
					this.flag = false;
					if (res.code >= 0) {
						this.getWeekReserve();
						if (this.reserveId) this.getYuyueInfo();
						this.closeYuyuePop();

						this.yuyuePage = 1;
						this.getYuyueList();
					}
				});
			}
		},
		//操作
		yuyueEvent(event, data) {
			this.reserveId = data.reserve_id;
			switch (event) {
				case 'info':
					this.getYuYueDetail(data.reserve_id);
					break;
				case 'tostore':
					this.tostore(data.reserve_id);
					break;
				case 'cancel':
					this.cancel(data.reserve_id);
					break;
				case 'confirm':
					this.confirm(data.reserve_id);
					break;
				case 'update': // 修改预约
					this.$refs.loading.show();
					this.yuYueInfo(data.reserve_id);
					break;
				case 'complet':
					this.complet(data.reserve_id);
					break;
			}
		},
		//修改预约
		yuYueInfo(reserve_id) {
			if (this.flag) return;
			this.flag = true;
			getReserveDetail(reserve_id).then(res => {
				if (res.code >= 0) {
					this.yuYueData = {
						reserve_id: res.data.reserve_id,
						member_id: res.data.member_id,
						member: res.data.member,
						time: this.$util.timeFormat(res.data.reserve_time, 'H:i'),
						date: this.$util.timeFormat(res.data.reserve_time, 'Y-m-d'),
						goods: res.data.item,
						desc: res.data.remark
					};

					this.handleYuyueDate();
					this.$refs.addYuyuePop.open();
				} else {
					this.$util.showToast({
						title: res.message
					});
				}
				this.flag = false;
				this.$refs.loading.hide();
			});
		},
		// 预约详情
		getYuYueDetail(reserve_id) {
			if (this.flag) return;
			this.flag = true;
			getReserveDetail(reserve_id).then(res => {
				if (res.code >= 0) {
					this.yuYueDetail = res.data;
					this.$refs.yuyuePop.open();
				}
				this.flag = false;
			});
		},
		//预约确认
		confirm(reserve_id) {
			if (this.flag) return;
			this.flag = true;
			reserveConfirm(reserve_id).then(res => {
				this.flag = false;
				if (res.code >= 0) {
					this.getWeekReserve();
					this.getYuyueInfo();
				}
				this.$util.showToast({
					title: res.message
				});
			});
		},
		//完成
		complet(reserve_id) {
			if (this.flag) return;
			this.flag = true;
			reserveComplete(reserve_id).then(res => {
				this.flag = false;
				if (res.code >= 0) {
					this.getWeekReserve();
					this.getYuyueInfo();
				}
				this.$util.showToast({
					title: res.message
				});
			});
		},
		//取消预约
		cancel(reserve_id) {
			if (this.flag) return;
			this.flag = true;
			cancelReserve(reserve_id).then(res => {
				this.flag = false;
				if (res.code >= 0) {
					this.getWeekReserve();
					this.getYuyueInfo();
				}
				this.$util.showToast({
					title: res.message
				});
			});
		},
		//确认到店
		tostore(reserve_id) {
			reserveToStore(reserve_id).then(res => {
				this.flag = false;
				if (res.code >= 0) {
					this.getWeekReserve();
					this.getYuyueInfo();
				}
				this.$util.showToast({
					title: res.message
				});
			});
		},

		// ******************** 预约列表 ********************

		// 获取预约分页数据
		getYuyueList() {
			getReserveLists({
				page: this.yuyuePage,
				search_text: this.yuyueSearchText
			}).then(res => {
				if (res.code >= 0) {
					if (this.yuyuePage == 1) this.yuyueList = [];

					this.yuyueList = this.yuyueList.concat(res.data.list);

					if (this.yuyuePage == 1 && this.yuyueList.length > 0) {
						this.reserveId = this.yuyueList[0]['reserve_id'];
						this.getYuyueInfo();
					}

					if (res.data.page_count >= this.yuyuePage) this.yuyuePage++;
				}
			});
		},
		// 搜索预约客户
		searchYuyueList() {
			this.yuyuePage = 1;
			this.getYuyueList();
		},
		selectYuyue(id) {
			this.reserveId = id;
			this.getYuyueInfo();
		},
		getYuyueInfo() {
			getReserveDetail(this.reserveId).then(res => {
				if (res.code >= 0) {
					this.yuyueInfo = res.data;
				} else {
					this.yuyueInfo = null;
				}
				this.refreshStatus();
				this.$forceUpdate();
			});
		},
		refreshStatus() {
			if (this.yuyueList && this.yuyueInfo) {
				Object.keys(this.yuyueList).forEach(key => {
					let data = this.yuyueList[key];
					if (data.reserve_id == this.yuyueInfo['reserve_id']) {
						this.yuyueList[key]['reserve_state'] = this.yuyueInfo['reserve_state'];
						this.yuyueList[key]['reserve_state_name'] = this.yuyueInfo['reserve_state_name'];
					}
				})
			}
		}
	}
};
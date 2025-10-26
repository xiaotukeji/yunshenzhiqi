<template>
	<base-page>
		<view class="userlist">
			<view class="userlist-box">
				<view class="userlist-left">
					<view class="user-title">
						员工
						<text class="iconfont icongengduo1"></text>
					</view>
					<view class="user-search">
						<view class="search">
							<text class="iconfont icon31sousuo"></text>
							<input v-model="search_text" type="text" @input="search" placeholder="请输入员工名称/手机号" />
						</view>
					</view>
					<view class="user-list-wrap">
						<block v-if="list.length > 0">
							<scroll-view :scroll-top="scrollTop" @scroll="scroll" scroll-y="true" class="user-list-scroll all-scroll" @scrolltolower="getUserListFn">
								<view class="item" @click="userSelect(item, index)" v-for="(item, index) in list" :key="index" :class="index == selectUserKeys ? 'itemhover' : ''">
									<image :src="$util.img(defaultImg.head)" mode="aspectFit"/>
									<view class="item-right">
										<view>
											<view class="user-name">{{ item.username }}</view>
											<view class="user-money">{{ item.group_name }}</view>
										</view>
										<view>
											<view class="user-status">{{ item.status ? '正常' : '锁定' }}</view>
											<view class="login-time">{{ item.login_time ? $util.timeFormat(item.login_time) : '--' }}</view>
										</view>
									</view>
								</view>
							</scroll-view>
						</block>
						<view class="notYet" v-else-if="!one_judge && list.length == 0">暂无员工</view>
					</view>
					<view class="add-user">
						<button type="default" class="primary-btn" @click="addUser">添加员工</button>
					</view>
				</view>
				<view class="userlist-right">
					<view class="user-title">员工详情</view>
					<view class="user-information">
						<block v-if="JSON.stringify(detail) != '{}'">
							<view class="title">基本信息</view>
							<view class="information-box">
								<view class="box-left">
									<view class="information">
										<view>员工名称：</view>
										<view>{{ detail.username }}</view>
									</view>
									<view class="information">
										<view>员工角色：</view>
										<view>{{ detail.group_name }}</view>
									</view>
									<view class="information">
										<view>员工状态：</view>
										<view>{{ detail.status ? '正常' : '锁定' }}</view>
									</view>
									<view class="information">
										<view>最后登录IP：</view>
										<view>{{ detail.login_ip ? detail.login_ip : '--' }}</view>
									</view>
									<view class="information">
										<view>最后登录时间：</view>
										<view>{{ detail.login_time ? $util.timeFormat(detail.login_time) : '--' }}</view>
									</view>
								</view>
								<image class="user-img" :src="$util.img(defaultImg.head)" mode="widthFix"/>
							</view>
							<view class="title">操作日志</view>
							<view>
								<uni-table url="/cashier/storeapi/user/userlog" :cols="logCols" :option="{ uid: detail.uid }" :pagesize="7"></uni-table>
							</view>
						</block>
						<block v-else>
							<image class="cart-empty" src="@/static/cashier/cart_empty.png" mode="widthFix"/>
						</block>
					</view>
					<view class="button-box flex justify-end" v-if="detail && (detail.is_admin == 0 || detail.is_system == 0)">
						<button class="default-btn" @click="$refs.deletePop.open()">删除</button>
						<button class="default-btn" @click="editUserAction(detail.uid)">修改</button>
					</view>
				</view>
				<!-- 添加员工 -->
				<uni-popup ref="addUserPop">
					<view class="pop-box">
						<view class="pop-header">
							{{ parseInt(formData.uid) > 0 ? '修改' : '添加' }}员工
							<view class="pop-header-close" @click="cancelAddUser()">
								<text class="iconguanbi1 iconfont"></text>
							</view>
						</view>
						<view class="common-scrollbar pop-content">
							<view class="form-content">
								<view class="form-item">
									<view class="form-label">
										<text class="required">*</text>
										用户名：
									</view>
									<view class="form-inline search-wrap">
										<input type="text" :disabled="parseInt(formData.uid) > 0 ? true : false" class="form-input" v-model="formData.username" placeholder="请输入用户名" />
									</view>
								</view>
								<view class="form-item" v-if="!parseInt(formData.uid)">
									<view class="form-label">
										<text class="required"></text>
										密码：
									</view>
									<view class="form-inline search-wrap">
										<input type="text" class="form-input" v-model="formData.password" placeholder="请输入密码" />
									</view>
								</view>

								<view class="form-item" v-else>
									<view class="form-label">
										<text class="required"></text>
										状态：
									</view>
									<view class="form-inline search-wrap">
										<radio-group @change="statusChange" class="form-radio-group">
											<label class="radio form-radio-item">
												<radio value="1" :checked="formData.status == 1" />
												正常
											</label>
											<label class="radio form-radio-item">
												<radio value="0" :checked="formData.status == 0" />
												锁定
											</label>
										</radio-group>
									</view>
								</view>

								<view class="form-item">
									<view class="form-label">
										<text class="required"></text>
										员工角色：
									</view>
									<view class="form-inline">
										<select-lay :zindex="10" :value="formData.group_id.toString()" name="names" placeholder="请选择员工角色" :options="userGroup" @selectitem="selectUserGroup"/>
									</view>
								</view>
							</view>
						</view>
						<view class="pop-bottom">
							<button type="primary" class="primary-btn" @click="save">{{ parseInt(formData.uid) > 0 ? '修改' : '添加' }}员工</button>
						</view>
					</view>
				</uni-popup>
			</view>
		</view>
		<!-- 删除 -->
		<unipopup ref="deletePop" type="center">
			<view class="confirm-pop">
				<view class="title">确定要删除该员工数据吗？</view>
				<view class="btn">
					<button type="primary" class="default-btn btn save" @click="$refs.deletePop.close()">取消</button>
					<button type="primary" class="primary-btn btn" @click="deleteUserFn(detail.uid)">确定</button>
				</view>
			</view>
		</unipopup>
	</base-page>
</template>

<script>
import {
	getUserList,
	getUserDetail,
	getAllGroups,
	addUser,
	editUser,
	deleteUser
} from '@/api/user.js'
import unipopup from '@/components/uni-popup/uni-popup.vue';

export default {
	components: {
		unipopup
	},
	data() {
		return {
			//选中的员工下标
			selectUserKeys: 0,
			//搜索的数据
			search_text: '',
			// 初始是请求第几页
			page: 1,
			// 每次返回数据数
			page_size: 8,
			//员工列表数据
			list: [],
			// 第一次请求列表做详情渲染判断
			one_judge: true,
			//无限滚动请求锁
			listLock: true,
			scrollTop: 0,
			//员工详情数据
			detail: {},
			logCols: [{
				width: 60,
				title: '操作记录',
				align: 'left',
				field: 'action_name'
			}, {
				width: 20,
				title: '操作IP地址',
				align: 'left',
				field: 'ip'
			}, {
				width: 20,
				title: '操作时间',
				align: 'right',
				templet: data => {
					return this.$util.timeFormat(data.create_time);
				}
			}],
			formData: {
				username: '',
				password: '',
				group_id: ''
			},
			userGroup: [],
			isRepeat: false
		};
	},
	onLoad() {
		// 初始化请求员工列表数据
		this.getUserListFn();
		this.getUserGroup();
	},
	methods: {
		// 选中的员工数据
		userSelect(item, keys) {
			this.selectUserKeys = keys;
			this.getUserDetailFn(item.uid);
			this.one_judge = true;
			this.isRepeat = false;
			this.formData = {
				username: '',
				password: '',
				group_id: ''
			};
		},
		statusChange(e) {
			this.formData.status = e.detail.value;
		},
		// 搜索员工
		search() {
			this.page = 1;
			this.list = [];
			this.one_judge = true;
			this.listLock = true;
			this.getUserListFn();
		},
		/**
		 * 请求的列表数据
		 */
		getUserListFn() {
			if (!this.listLock) return false;
			getUserList({
				page: this.page,
				page_size: this.page_size,
				username: this.search_text
			}).then(res => {
				if (res.data.list.length == 0 && this.one_judge) {
					this.detail = {};
					this.one_judge = false;
				}
				if (res.code >= 0 && res.data.list.length != 0) {
					
					if (this.list.length == 0) {
						this.list = res.data.list;
					} else {
						this.list = this.list.concat(res.data.list);
					}

					//初始时加载一遍详情数据
					if (this.one_judge) {
						this.getUserDetailFn(this.list[0].uid);
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
			});
		},
		scroll(e) {
			this.scrollTop = e.detail.scrollTop
		},
		getUserDetailFn(uid) {
			getUserDetail(uid).then(res => {
				if (res.code == 0) {
					this.detail = res.data;
					this.one_judge = false;
				}
			});
		},
		getUserGroup() {
			getAllGroups().then(res => {
				if (res.code == 0 && res.data) {
					this.userGroup = res.data.map(item => {
						return {
							label: item.group_name,
							value: item.group_id,
							create_uid:item.create_uid,
							store_id:item.store_id,
						};
					});
				}
			})
		},
		editUserAction(uid) {
			getUserDetail(uid).then(res => {
				if (res.code == 0) {
					if(res.data.create_user_info){
						this.formData = {
							username: res.data.username,
							group_id: res.data.group_id,
							uid: res.data.uid,
							status: res.data.status
						};
						this.$refs.addUserPop.open();
					}
				}
			});
		},
		deleteUserFn(uid) {
			if(this.isRepeat) return false;
			this.isRepeat = true;
			deleteUser(uid).then(res => {
				if (res.code >= 0) {
					this.page = 1;
					this.list = [];
					this.one_judge = true;
					this.listLock = true;
					this.getUserListFn();
					this.$refs.deletePop.close()
				} else {
					this.$util.showToast({
						title: res.message
					});
				}
				this.isRepeat = false
			});
		},
		addUser() {
			this.$refs.addUserPop.open();
		},
		cancelAddUser() {
			this.formData = {
				username: '',
				password: '',
				group_id: ''
			};
			this.$refs.addUserPop.close();
		},
		selectUserGroup(index, item) {
			if (index >= 0) {
				this.formData.group_id = parseInt(item.value);
			} else {
				this.formData.group_id = 0;
			}
		},
		save() {
			if (!this.verify() || this.isRepeat) return;
			this.isRepeat = true;
			let action = '';
			if (parseInt(this.formData.uid) > 0) {
				action = editUser(this.formData);
			} else {
				action = addUser(this.formData);
			}
			action.then(res => {
				if (res.code >= 0) {
					this.$util.showToast({
						title: '操作成功'
					});
					this.page = 1;
					this.list = [];
					this.one_judge = true;
					this.listLock = true;
					this.cancelAddUser();
					this.getUserListFn();

					this.isRepeat = false;
					this.formData = {
						username: '',
						password: '',
						group_id: ''
					};
				} else {
					this.isRepeat = false;
					this.$util.showToast({
						title: res.message
					});
				}
			});
		},
		verify() {
			if (!this.formData.username) {
				this.$util.showToast({
					title: '请输入用户名'
				});
				return false;
			}
			if (parseInt(this.formData.uid) == 0 && !this.formData.password) {
				this.$util.showToast({
					title: '请输入密码'
				});
				return false;
			}
			if (!this.formData.group_id) {
				this.$util.showToast({
					title: '请选择员工角色'
				});
				return false;
			}
			return true;
		}
	}
};
</script>

<style scoped lang="scss">
@import './public/css/user.scss';
</style>
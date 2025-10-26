<template>
	<view v-if="menu.length">
		<view class="top-container">
			<view class="top-left">{{ menuName }}</view>
			<view class="top-right">
				<view class="help" @click="$refs.helpRef.open()">
					<text>帮助</text>
				</view>
				<text class="curr-store">{{ globalStoreInfo.store_name }}</text>
				<text class="cut-store" @click="switchStore()">切换门店</text>
				<uni-dropdown>
					<view slot="dropdown-link" class="head-box">
						<view class="login-wrap">
							<text>{{ userInfo ? userInfo.username : '' }}</text>
							<text class="iconfont iconsanjiao_xia"></text>
						</view>
					</view>
					<view slot="dropdown">
						<view class="dropdown-menu">
							<view class="menu-item" v-if="userInfo" @click="changePassword">
								修改密码
								<text class="iconfont iconqianhou2"></text>
							</view>
							<view class="menu-item logout" @click="$refs.logout.open()">
								退出登录
								<text class="iconfont icontuichu"></text>
							</view>
							<view class="arrow"></view>
						</view>
					</view>
				</uni-dropdown>
			</view>
		</view>
		<unipopup ref="helpRef" type="center">
			<view class="help-wrap">
				<view class="help-head">
					帮助
					<text class="iconfont iconguanbi1" @click="$refs.helpRef.close()"></text>
				</view>
				<view class="help-body">
					<view class="help-center">
						<view class="item">
							<view class="title">系统快捷键</view>
							<view class="content">
								<view>F6：开单</view>
								<view>F7：售卡</view>
								<view>F8：充值</view>
								<view>F9：订单管理</view>
								<view>F10：会员管理</view>
							</view>
						</view>
						<view class="item">
							<view class="title">开单快捷键</view>
							<view class="content">
								<view>F2：商品</view>
								<view>F3：挂单</view>
								<view>F4：会员</view>
								<view>F12：整单取消</view>
								<view>M：查询会员</view>
								<view>Alt+X：打开钱箱</view>
							</view>
						</view>
						<view class="item">
							<view class="title">售卡快捷键</view>
							<view class="content">
								<view>F2：卡项</view>
								<view>F3：会员</view>
							</view>
						</view>
						<view class="item">
							<view class="title">充值快捷键</view>
							<view class="content">
								<view>F2：充值</view>
								<view>F3：会员</view>
							</view>
						</view>

					</view>
				</view>

			</view>
		</unipopup>
		<uni-popup ref="passwordPopup">
			<view class="password-popup">
				<view class="head">修改密码</view>
				<view class="common-form body">
					<view class="common-form-item">
						<view class="form-label">
							<text class="required">*</text>
							原密码
						</view>
						<view class="form-input-inline">
							<input type="text" :password="true" v-model="codeData.old_pass" class="form-input" placeholder="请输入原密码" />
						</view>
					</view>
					<view class="common-form-item">
						<view class="form-label">
							<text class="required">*</text>
							新密码
						</view>
						<view class="form-input-inline">
							<input type="text" :password="true" v-model="codeData.new_pass" class="form-input" placeholder="请输入新密码" />
						</view>
					</view>
					<view class="common-form-item">
						<view class="form-label">
							<text class="required">*</text>
							确认新密码
						</view>
						<view class="form-input-inline">
							<input type="text" :password="true" v-model="codeData.confirm_new_pass" class="form-input" placeholder="请输入新密码" />
						</view>
					</view>
					<view class="common-btn-wrap">
						<button type="default" class="primary-btn screen-btn" @click="modifyPasswordFn">确定</button>
						<button type="default" class="default-btn" @click="$refs.passwordPopup.close()">取消</button>
					</view>
				</view>
			</view>
		</uni-popup>

		<uni-popup ref="storePop">
			<view class="pop-box store-select">
				<view class="pop-header">
					<view class="pop-header-text">选择门店</view>
					<view class="pop-header-close" @click="$refs.storePop.close()">
						<text class="iconguanbi1 iconfont"></text>
					</view>
				</view>
				<scroll-view scroll-y="true" class="common-scrollbar pop-content">
					<view class="content-lists">
						<view class="content-items" v-for="(item, index) in storeList" @click="selectStore(item)" :key="index" :class="{ active: globalStoreInfo && globalStoreInfo.store_id == item.store_id }">
							<view class="item-img">
								<image v-if="item.store_image" :src="$util.img(item.store_image)" @error="$util.img(defaultImg.store)" mode="aspectFit"/>
								<image v-else :src="$util.img(defaultImg.store)" mode="aspectFit"/>
							</view>
							<view class="item-info">
								<view class="item-name">{{ item.store_name }}</view>
								<view class="item-phone">
									<text class="iconfont iconshijian"></text>
									{{ item.open_date ? item.open_date : '营业时间请联系管理员' }}
								</view>
								<view class="item-addr">
									<text class="iconfont icondizhi"></text>
									{{ item.full_address }}
								</view>
							</view>
						</view>
					</view>
				</scroll-view>
				<view class="pop-bottom">
					<button class="primary-btn" @click="$refs.storePop.close()">确定</button>
				</view>
			</view>
		</uni-popup>

		<uni-popup ref="logout" type="center">
			<view class="logout-popup">
				<view class="title">确定退出系统？系统将自动交班</view>
				<view class="btn">
					<button type="primary" class="default-btn btn save" @click="$refs.logout.close()">取消</button>
					<button type="primary" class="primary-btn btn" @click="logout">确定</button>
				</view>
			</view>
		</uni-popup>
	</view>
</template>

<script>
import { mapGetters } from 'vuex';
import unipopup from '@/components/uni-popup/uni-popup.vue';
import {checkPageAuth} from '@/api/config.js';
import {changeShifts} from '@/api/shifts.js';
import {modifyPassword} from '@/api/login.js';
import {getStoreList} from '@/api/store.js';

export default {
	name: 'LayoutTop',
	components: {
		unipopup
	},
	created() {
	},
	data() {
		return {
			moreIndex: 0,
			logoError: false,
			storeList: [],
			codeData: {
				old_pass: '',
				new_pass: '',
				confirm_new_pass: ''
			}
		};
	},
	computed: {
		...mapGetters(['firstMenuIndex', 'secondMenuIndex', 'thirdMenuIndex', 'currRoute']),
		menuName: function () {
			let pages = getCurrentPages();
			let page = pages[pages.length - 1];
			let title = page && page.$holder ? page.$holder.navigationBarTitleText : '';
			return title;
		}
	},
	methods: {
		logout() {
			changeShifts().then(res=>{
				if (res.code == 0 && res.data) {
					this.$refs.logout.close();
					uni.removeStorage({
						key: 'cashierToken',
						success: () => {
							this.$util.clearStoreData();
							this.$util.redirectTo('/pages/login/login', {}, 'reLaunch');
						}
					});
				} else {
					this.$util.showToast({
						title: res.message
					});
				}
			})
		},
		switchStore() {
			this.getStoreListFn();
			this.$refs.storePop.open('center');
		},
		changePassword() {
			this.$refs.passwordPopup.open('center');
		},
		modifyPasswordFn() {
			if (this.codeData.new_pass != this.codeData.confirm_new_pass) {
				this.$util.showToast({
					title: '两次密码输入不一致，请重新输入'
				});
				return false;
			}
			modifyPassword(this.codeData).then(res=>{
				this.$util.showToast({
					title: res.message
				});
				if (res.code >= 0) {
					this.codeData.old_pass = '';
					this.codeData.new_pass = '';
					this.codeData.confirm_new_pass = '';
					uni.removeStorageSync('cashierToken');
					this.$refs.passwordPopup.close();
					setTimeout(() => {
						this.$util.redirectTo('/pages/login/login');
					}, 500);
				}
			});
		},
		selectStore(data) {
			this.$store.commit('app/setGlobalStoreId',data.store_id);
			this.$store.dispatch('app/getStoreInfoFn');
			this.$refs.storePop.close();
			this.$forceUpdate();
		},
		getStoreListFn() {
			getStoreList().then(res=>{
				if (res.code == 0 && res.data) this.storeList = res.data;
			})
		},
		/**
		 * 检测页面是否有权限
		 */
		checkPageAuthFn() {
			checkPageAuth(this.currRoute).then(res=>{
				if (res.code && res.code == -10012) {
					this.$util.redirectTo('/pages/index/no_permission', {}, 'redirectTo');
				}
			});
		}
	}
};
</script>

<style lang="scss">
.top-container {
	position: relative;
	margin-left: -.06rem;
	margin-bottom: .06rem;
	padding: 0 .2rem;
	display: flex;
	align-items: center;
	justify-content: space-between;
	flex-direction: row-reverse;
	height: .45rem;
	background-color: #fff;

	.top-left {
		position: absolute;
		left: 50%;
		transform: translateX(-50%);
		font-size: 18px;
	}

	.top-right {
		display: flex;
		align-items: center;

		.help {
			margin-right: .08rem;
		}

		.curr-store {
			margin-right: .08rem;
			border: .01rem solid $primary-color;
			border-radius: .02rem;
			font-size: $uni-font-size-sm;
			color: $primary-color;
			line-height: 1;
			padding: .04rem .06rem;
		}

		.cut-store {
			margin-right: 10px;
			font-size: $uni-font-size-sm;
			color: $primary-color;
		}
	}
}

.dropdown-menu {
	padding: 0;
	margin-top: 0.15rem;
	background-color: #fff;
	border: 0.01rem solid #ebeef5;
	border-radius: 0.04rem;
	box-shadow: 0 0.01rem 0.12rem 0 rgba(0, 0, 0, 0.1);
	position: relative;
	width: 3rem;

	.arrow {
		position: absolute;
		top: -0.1rem;
		right: 0.08rem;
		width: 0;
		height: 0;
		border-left: 0.1rem solid transparent;
		border-right: 0.1rem solid transparent;
		border-bottom: 0.1rem solid #fff;
	}

	.menu-item {
		width: calc(100% - 0.2rem);
		height: 0.57rem;
		display: flex;
		align-items: center;
		justify-content: space-between;
		margin: 0 0.1rem;
		padding: 0.05rem 0.05rem;
		text-align: center;
		cursor: pointer;
		color: #303133;
		box-sizing: border-box;
		border-bottom: 0.01rem solid #e6e6e6;

		&:hover {
			color: $primary-color;
		}

		.iconfont {
			color: #999;
		}

		&:nth-child(3) {
			border: 0;
		}

		&.logout {
			margin: 0;
			background-color: #eff0f4;
			padding: 0.05rem 0.15rem;
			width: 100%;
		}
	}
}

.pop-box {
	background: #ffffff;
	width: 6rem;
	height: 4rem;

	.pop-header {
		padding: 0 0.15rem 0 0.2rem;
		height: 0.5rem;
		line-height: 0.5rem;
		border-bottom: 0.01rem solid #f0f0f0;
		font-size: 0.14rem;
		color: #333;
		overflow: hidden;
		border-radius: 0.02rem 0.2rem 0 0;
		box-sizing: border-box;
		display: flex;
		justify-content: space-between;

		.pop-header-text {}

		.pop-header-close {
			cursor: pointer;

			text {
				font-size: 0.18rem;
			}
		}
	}

	.pop-content {
		height: calc(100% - 1.05rem);
		overflow-y: scroll;
		padding: 0.2rem;
		box-sizing: border-box;
	}

	.pop-bottom {
		padding: 0.1rem;
		height: 0.65rem;
		border-top: 0.01rem solid #eee;

		button {
			width: 1rem;
		}
	}
}

// 选择门店
.store-select {
	.content-lists {
		width: calc(100% - 0.2rem);
		padding-bottom: 0.2rem;

		.content-items {
			cursor: pointer;
			border-width: 0.01rem;
			border-color: #cccccc;
			border-style: solid;
			padding: 0.15rem;
			border-radius: 0.03rem;
			margin-bottom: 0.1rem;
			display: flex;

			.item-info {
				margin-left: 0.1rem;

				.item-name {
					font-size: 0.16rem;
					font-weight: 600;
				}

				.item-phone {
					margin-top: 0.08rem;
					color: #999;

					text {
						margin-right: 0.05rem;
						font-size: 0.14rem;
					}
				}

				.item-addr {
					margin-top: 0.03rem;
					color: #999;

					text {
						font-size: 0.14rem;
						margin-right: 0.05rem;
					}
				}
			}

			.item-img {
				width: 0.7rem;
				height: 0.7rem;

				image {
					width: 100%;
					height: 100%;
				}
			}

			&.active {
				border-color: $primary-color;
			}
		}
	}
}

.password-popup {
	background-color: #fff;
	width: 4.5rem;

	.head {
		padding: 0 0.2rem;
		height: 0.5rem;
		line-height: 0.5rem;
		margin-bottom: 0.1rem;
	}

	.body {
		padding: 0 0.1rem 0.2rem 0.3rem;
	}
}

.logout-popup {
	width: 3rem;
	min-height: 1.5rem;
	border-radius: 0.06rem;
	background: #ffffff;
	padding: 0.4rem 0.15rem 0;
	box-sizing: border-box;

	.title {
		font-size: 0.16rem;
		text-align: center;
	}

	.btn {
		width: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
		margin-top: 0.3rem;

		.btn {
			width: 36%;
			padding: 0 0.15rem;
			margin: 0;
			height: 0.35rem;
		}

		.btn:last-child {
			margin-left: 0.25rem;
		}
	}
}

.help-wrap {
	background-color: #fff;
	border-radius: 0.05rem;
	padding-bottom: 0.15rem;

	.help-head {
		padding: 0 0.15rem;
		display: flex;
		align-items: center;
		justify-content: space-between;
		font-size: 0.15rem;
		height: 0.45rem;
		border-bottom: 0.01rem solid #e8eaec;

		.iconguanbi1 {
			font-size: $uni-font-size-lg;
		}
	}

	.help-body {
		width: 9rem;
		height: 1.8rem;
		padding: 0.2rem 0.2rem 0 0.2rem;
		box-sizing: border-box;
		overflow-y: auto;
		position: relative;

		.help-center {
			display: flex;

		}
		.item{
			width: 33.333%;
		}
		.title {
			font-size: 0.16rem;
			margin-bottom: 0.05rem;
		}

		.content {
			padding-left: 0.1rem;
		}
	}
}
</style>

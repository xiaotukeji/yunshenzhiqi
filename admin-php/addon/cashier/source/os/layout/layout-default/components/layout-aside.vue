<template>
	<view :style="themeColor">
		<view class="aside-container">
			<view class="aside-headimg">
				<view class="head-box">
					<image :src="$util.img(defaultImg.store)" v-if="!globalStoreInfo || !globalStoreInfo.store_image || logoError" mode="aspectFit" />
					<image :src="$util.img(globalStoreInfo.store_image)" v-else @error="$util.img(defaultImg.store)" mode="aspectFit" />
				</view>
			</view>
			<view class="menu-wrap">
				<block v-for="(item, index) in menu" :key="index">
					<view class="menu-item" :class="{ active: index == firstMenuIndex }" @click="firstMenu(item, index)" v-if="item.title">
						<view class="iconfont" :class="item.icon"></view>
						<view>
							<text>{{ item.title }}</text>
							<!-- <text v-if="item.keyCode" class='key-code'>[{{ item.keyCode }}]</text> -->
						</view>
					</view>
				</block>
			</view>
			<view class="overallAudio" @click="setAudioIsPlay">
				<text class="iconfont" :class="{'iconshengyinV6xx1':overallAudioIsPlay,'iconshengyin-jingyinV6xx':!overallAudioIsPlay}"></text>
			</view>
			<view class="member">
				<uni-dropdown :direction="'slide-bottom'">
					<view slot="dropdown-link">
						<view class="login-wrap">
							<text>{{ userInfo ? userInfo.username : '' }}</text>
						</view>
					</view>
					<view slot="dropdown">
						<view class="dropdown-menu">
							<view class="flex flex-shrink-0">
								<view class="item-img">
									<image :src="$util.img(defaultImg.store)" v-if="!globalStoreInfo || !globalStoreInfo.store_image || logoError" mode="aspectFit" />
									<image :src="$util.img(globalStoreInfo.store_image)" v-else @error="$util.img(defaultImg.store)" mode="aspectFit" />
								</view>
								<view class="flex flex-1 right flex-col justify-between">
									<view v-if="globalStoreInfo && globalStoreInfo.store_name" class="storm-name using-hidden">{{ globalStoreInfo.store_name }}</view>
									<view v-if="userInfo&&userInfo.username" class="username using-hidden">{{ userInfo.username }}</view>
									<view v-if="userInfo&&userInfo.group_name" class="group-name using-hidden">{{ userInfo.group_name }}</view>
								</view>
							</view>
							<view class="menu-item" v-if="userInfo" @click="switchStore()">
								切换门店
								<text class="iconfont iconqianhou2"></text>
							</view>
							<view class="menu-item" v-if="userInfo" @click="$refs.helpRef.open()">
								查看帮助
								<text class="iconfont iconqianhou2"></text>
							</view>
							<view class="menu-item" v-if="userInfo" @click="changePassword">
								修改密码
								<text class="iconfont iconqianhou2"></text>
							</view>
							<view class="menu-item" @click="$refs.logout.open()">
								退出登录
								<text class="iconfont icontuichu"></text>
							</view>
							<view class="arrow"></view>
							<view class="arrow"></view>
						</view>
					</view>
				</uni-dropdown>
			</view>
		</view>

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
						<button type="primary" class="screen-btn" @click="modifyPasswordFn">确定</button>
						<button type="primary" class="default-btn" @click="$refs.passwordPopup.close()">取消</button>
					</view>
				</view>
			</view>
		</uni-popup>

		<uni-popup ref="moreMenu">
			<scroll-view scroll-y="true" class="more-menu-scroll common-scrollbar">
				<view class="more-menu">
					<block v-for="(item, secondIndex) in moreMenu" :key="secondIndex">
						<block v-if="item.children && item.children.length">
							<view class="title">{{ item.title }}</view>
							<view class="child-menu-wrap">
								<block v-for="(thirditem, thirdIndex) in item.children" :key="thirdIndex">
									<view class="menu-item" :class="{ active: secondIndex == secondMenuIndex && thirdIndex == thirdMenuIndex }" @click="thirdMenu(thirditem, secondIndex, thirdIndex)" v-if="thirditem.title">
										<view class="iconfont" :class="thirditem.icon"></view>
										<view>{{ thirditem.title }}</view>
									</view>
								</block>
							</view>
						</block>
					</block>
				</view>
			</scroll-view>
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
					<view class="content-lists flex flex-wrap justify-between">
						<view class="content-items" v-for="(item, index) in storeList" @click="selectStore(item)" :key="index" :class="{ active: globalStoreInfo && globalStoreInfo.store_id == item.store_id }">
							<view class="item-img flex-shrink-0">
								<image v-if="item.store_image" :src="$util.img(item.store_image)" @error="$util.img(defaultImg.store)" mode="aspectFit" />
								<image v-else :src="$util.img(defaultImg.store)" mode="aspectFit" />
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
				<view class="title">确定退出系统？请选择退出操作</view>
				<view class="btn">
					<button type="primary" class="default-btn btn save" @click="$refs.logout.close()">取消</button>
					<button type="primary" class="primary-btn btn" @click="logout(false)">仅退出</button>
					<button type="primary" class="primary-btn btn" @click="logout(true)">退出并交班</button>
				</view>
			</view>
		</uni-popup>
		<uni-popup ref="helpRef" type="center">
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
		</uni-popup>
	</view>
</template>

<script>
import {
	mapGetters,
	mapActions
} from 'vuex';
import { checkPageAuth,pushChangeBind,getPushStatus} from '@/api/config.js';
import { changeShifts } from '@/api/shifts.js';
import { modifyPassword } from '@/api/login.js';
import { getStoreList } from '@/api/store.js';

export default {
	name: 'LayoutAside',
	created() {
		this.loadThemeColor();
		this.getStoreListFn();
	},
	data() {
		return {
			moreMenu: [],
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
		isSocketConnect(){
			return this.$store.state.app.isSocketConnect
		},
		overallAudioIsPlay(){
			return this.$store.state.app.overallAudioIsPlay
		},
		client_id(){
			return this.$store.state.app.overallAudioBindClientId
		}
	},
	methods: {
		setAudioIsPlay(){
			if(!this.overallAudioIsPlay){
				getPushStatus().then(res=>{
					if(res.code>=0){
						if(!this.isSocketConnect) this.$store.dispatch('app/setIsSocketConnect',true)
						this.$store.dispatch('app/setOverallAudioIsPlay',true);
						this.$util.showToast({
							title: '语音提醒已开启'
						});
					}else{
						this.$util.showToast({
							title: res.message
						});
						this.$store.dispatch('app/setIsSocketConnect',false)
					}
				})
			}else{
				this.$store.dispatch('app/setOverallAudioIsPlay',false);
				this.$util.showToast({
					title: '语音提醒已关闭'
				});
			}
			
			
		},
		firstMenu(data, index) {
			// #ifdef H5
			if (data.path == this.$route.path) return;
			// #endif

			// #ifdef APP-PLUS
			if (data.path == '/' + this.$mp.page.route) return;
			// #endif

			if (data.children && data.childshow) {
				this.moreMenu = data.children;
				this.moreIndex = index;
				this.$refs.moreMenu.open('left');
			} else {
				this.$refs.moreMenu.close('left');
				this.$util.redirectTo(data.path, data.query ?? {});
			}
		},
		thirdMenu(data, second, third) {
			this.$refs.moreMenu.close('left');
			this.$util.redirectTo(data.path, data.query ?? {});
		},
		logout(change_shifts) {
			if(change_shifts){
				changeShifts().then(res => {
					if (res.code == 0 && res.data) {
						this.logoutAction();
					} else {
						this.$util.showToast({
							title: res.message
						});
					}
				})
			}else{
				this.logoutAction();
			}
		},
		logoutAction(){
			this.$refs.logout.close();
			uni.removeStorage({
				key: 'cashierToken',
				success: () => {
					this.$util.clearStoreData();
					uni.closeSocket()
					this.$store.dispatch('app/setOverallAudioBindClientId','')
					this.$util.redirectTo('/pages/login/login', {}, 'reLaunch');
				}
			});
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
			modifyPassword(this.codeData).then(res => {
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
			if(this.client_id){//收银台消息推送-更换绑定门店
				let old_store_id = this.$store.state.app.globalStoreId
				this.$store.commit('app/setGlobalStoreId', data.store_id);
				pushChangeBind({
					client_id:this.client_id,
					old_store_id
				})
			}else{
				this.$store.commit('app/setGlobalStoreId', data.store_id);
			}
			
			this.$store.dispatch('app/getStoreInfoFn');
			this.$refs.storePop.close();
			this.$forceUpdate();
		},
		getStoreListFn() {
			getStoreList().then(res => {
				if (res.code == 0 && res.data) this.storeList = res.data;
			});
		},
		/**
		 * 检测页面是否有权限
		 */
		checkPageAuthFn() {
			checkPageAuth(this.currRoute).then(res => {
				if (res.code && res.code == -10012) {
					this.$util.redirectTo('/pages/index/no_permission', {}, 'redirectTo');
				}
			});
		},
	}
};
</script>

<style lang="scss">
/deep/ .aside-headimg .dropdown-box {
	left: 0 !important;
	right: unset !important;
}

.aside-container {
	position: fixed;
	display: flex;
	flex-direction: column;
	top: $statusbar-height;
	width: $aside-width;
	height: 100vh;
	background-color: #272738;
	font-size: 0.12rem;
	color: #fff;
	z-index: 1000;
}

.aside-headimg {
	width: 100%;
	height: 1.24rem;
	display: flex;
	align-items: center;
	justify-content: center;
	
	.head-box {
		width: 0.44rem;
		height: 0.44rem;
		border: 2px solid #FFFFFF;
		border-radius: 50%;

		image {
			border-radius: 50%;
			width: 0.44rem;
			height: 0.44rem;
		}
	}
}

.menu-wrap {
	flex:1;
	overflow-y: scroll;
	box-sizing: border-box;
	width: 100%;
	padding: 0 14px;

	&::-webkit-scrollbar {
		display: none;
	}

	.menu-item {
		text-align: center;
		color: #cccccc;
		font-size: 0.14rem;
		line-height: 1;
		width: 0.6rem;
		height: 0.6rem;
		cursor: pointer;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		border-radius: 0.08rem;
		margin-bottom: 0.2rem;

		.iconfont {
			font-size: 0.24rem;
			margin-bottom: 0.02rem;
		}

		&.active {
			background: $primary-color;
			color: #fff;
		}

		.key-code {
			margin-left: 0.05rem;
		}
	}
}

.member{
	padding-bottom: 0.3rem;
}
.more-menu-scroll {
	height: calc(100vh - #{$statusbar-height});
	margin-top: $statusbar-height;
	background-color: #fff;
}

.more-menu {
	width: 3rem;
	background: #fff;
	box-sizing: content-box;
	padding-left: $aside-width;
	height: 100%;

	.title {
		font-size: 0.2rem;
		font-weight: 400;
		color: #303133;
		line-height: 0.36rem;
		padding: 0.2rem;
	}
}

.child-menu-wrap {
	padding: 0 0.2rem;
	display: flex;
	flex-wrap: wrap;

	.menu-item {
		width: 0.8rem;
		height: 0.8rem;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-direction: column;
		font-size: 0.14rem;
		color: #303133;
		background: #f5f5f5;
		cursor: pointer;
		margin: 0 0.1rem 0.1rem 0;

		&:nth-child(3n + 3) {
			margin-right: 0;
		}

		view {
			line-height: 1;
		}

		.iconfont {
			font-size: 0.23rem;
			margin-bottom: 0.1rem;
		}

		&.active {
			background-color: var(--primary-color-light-8);
			color: $primary-color;
		}
	}
}

/deep/ .uni-popup {
	z-index: 999;
}

.pop-box {
	background: #ffffff;
	width: 7rem;
	height: 4rem;

	.pop-header {
		padding: 0 0.15rem 0 0.2rem;
		height: 0.5rem;
		line-height: 0.5rem;
		border-bottom: 0.01rem solid #f0f0f0;
		font-size: 0.13rem;
		color: #333;
		overflow: hidden;
		border-radius: 0.02rem 0.2rem 0 0;
		box-sizing: border-box;
		display: flex;
		justify-content: space-between;

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
		padding: 0.1rem 0.2rem;
		border-top: 0.01rem solid #eee;
		box-sizing: border-box;
		button {
			width: 100%;
			margin: 0;
		}
	}
}

// 选择门店
.store-select {
	.content-lists {
		width: 100%;
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
			width: calc(50% - 0.025rem);
			box-sizing: border-box;
			.item-info {
				margin-left: 0.1rem;

				.item-name {
					font-size: 0.16rem;
					font-weight: 600;
				}

				.item-phone {
					margin-top: 0.08rem;
					color: #999;
					font-size: 0.13rem;
					text {
						margin-right: 0.05rem;
						font-size: 0.14rem;
					}
				}

				.item-addr {
					margin-top: 0.03rem;
					color: #999;
					font-size: 0.13rem;
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
			width: unset;
			padding: 0 0.15rem;
			margin: 0;
			height: 0.35rem;
			margin-right: 0.1rem;
		}

		.btn:last-child {
			margin-right: 0;
		}
	}
}
.overallAudio{
	text-align: center;
	color: #fff;
	cursor: pointer;
	margin-bottom: 0.05rem;
	margin-top: 0.2rem;
	.iconfont{
		font-size: 0.24rem !important;
	}
}
.member {
	.login-wrap {
		text-align: center;
		cursor: pointer;
	}

	.dropdown-box {
		left: 0.2rem;
	}

	.dropdown-menu {
		padding: 0.2rem;
		margin-left: 0.2rem;
		margin-bottom: 0.15rem;
		background-color: #fff;
		border: 0.01rem solid #ebeef5;
		border-radius: 0.04rem;
		box-shadow: 0 0.01rem 0.12rem 0 rgba(0, 0, 0, 0.1);
		position: relative;
		width: 3rem;
		color: #000;

		.item-img {
			width: 0.7rem;
			height: 0.7rem;

			image {
				width: 100%;
				height: 100%;
			}
		}

		.right {
			flex-wrap: wrap;
			box-sizing: border-box;
			padding-left: 0.1rem;
		}
		.operate{
			margin-top: 0.1rem;
			view{
				cursor: pointer;
			}
		}
		.right,
		.username,
		.storm-name,
		.group-name {
			width: 2.2rem;
		}
		.iconbangzhu{
			cursor: pointer;
		}
		.storm-name{
			font-weight: 600;
		}
		.username,
		.group-name {
			color: #999;
		}

		.arrow {
			position: absolute;
			left: 0.08rem;
			width: 0;
			height: 0;
			border-left: 0.1rem solid transparent;
			border-right: 0.1rem solid transparent;
			border-bottom: 0.1rem solid transparent;
			border-top: 0.1rem solid #fff;
			bottom: -0.2rem;
		}
		.menu-item {
			height: 0.57rem;
			display: flex;
			align-items: center;
			justify-content: space-between;
			text-align: center;
			cursor: pointer;
			color: #303133;
			box-sizing: border-box;
			border-bottom: 0.01rem solid #e6e6e6;

			&:hover {
				color: $primary-color;
				.iconfont {
					color: $primary-color;
				}
			}

			.iconfont {
				color: #999;
				
			}

			&:nth-child(5) {
				border: 0;
			}
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
<template>
	<view>
		<view class="search-wrap">
			<view class="search-input-inner">
				<text class="search-input-icon iconfont iconsousuo" @click.stop="searchGoods()"></text>
				<input class="uni-input font-size-tag" maxlength="50" v-model="search_text" placeholder="请输入商品名称" @confirm="searchGoods()" />
			</view>
			<!-- <view class="search-btn color-base-bg" @click.stop="linkSkip()">
				<text>+</text>
				<text>添加门店</text>
			</view> -->
		</view>
		<view class="tab-block">
			<view class="tab-wrap">
				<block v-for="(item, index) in statusList" :key="index">
					<view class="tab-item" @click.stop="tabChange(item.id)" :class="item.id == status ? 'active color-base-text color-base-bg-before' : ''">{{ item.name }}</view>
				</block>
			</view>
		</view>
		<mescroll-uni @getData="getList" top="190" ref="mescroll" >
			<block slot="list">
				<block v-if="dashboard_list.length > 0">
					<view class="goods-class" v-for="(item,index) in dashboard_list" :key="index">
						<view class="goods-item">
							<view class="goods-item-title">
								<view class="title-ordernum">{{item.store_name}}</view>
								<view :class="item.is_frozen == 1 ? 'title-ordertext' : item.is_frozen == 0 && item.status == 1 ? 'title-orderactive' : 'title-orderxiuxi'">
								{{item.is_frozen == 1 ? '停业' : item.is_frozen == 0 && item.status == 1 ? '正常' : '休息'}}</view>
							</view>
							<view class="goods-item-content" style="align-items: center;">
								<view class="content-left">管理员</view>
								<view class="content-right">{{item.username}}</view>
								<!-- <view class="content-last" @click="changePass(item)">重置密码</view> -->
							</view>
							<view class="goods-item-content">
								<view class="content-left">联系方式</view>
								<view class="content-right">{{item.telphone || '暂无'}}</view>
							</view>
							<view class="goods-item-content">
								<view class="content-left">地址</view>
								<view class="content-right">{{item.full_address}}{{item.address}}</view>
							</view>
							<view class="goods-btn">
								<!-- <button type="default" size="mini" class="goods-btn-item bacolors">进入门店</button> -->
								<button type="default" size="mini" class="goods-btn-item" v-if="item.is_frozen == 0" @click="onTag(item.store_id,item.is_frozen)">停业</button>
								<button type="default" size="mini" class="goods-btn-item" v-if="item.is_frozen == 1" @click="onTag(item.store_id,item.is_frozen)">开启</button>
								<!-- <button type="default" size="mini" class="goods-btn-item" @click="onEdit(item.store_id)">编辑</button> -->
							</view>
						</view>
					</view>
				</block>
				<ns-empty v-if="!dashboard_list.length" text="暂无商品数据"></ns-empty>
			</block>
		</mescroll-uni>
		<uni-popup ref="editPasswordPopse">
			<view class="pop-wrap" @touchmove.prevent.stop>
				<view class="title font-size-toolbar">
					重置密码
					<view class="close color-tip" @click.stop="closeEditPasswordPop()"><text class="iconfont iconclose"></text></view>
				</view>
				<view class="flex">
					<view class="flex_left">新密码</view>
					<view class="flex_right"><input placeholder="请输入新密码" password="true" class="uni-input" v-model="password.newPwd" /></view>
				</view>
				<view class="flex last_child margin-bottom">
					<view class="flex_left">确认新密码</view>
					<view class="flex_right"><input placeholder="请输入确认新密码" password="true" class="uni-input" v-model="password.againNew" /></view>
				</view>
				<view class="action-btn">
					<view class="line" @click.stop="closeEditPasswordPop()">取消</view>
					<view class="color-line-border color-base-text" @click.stop="modifyPassword()">确定</view>
				</view>
			</view>
		</uni-popup>
	</view>
</template>

<script>
	import validate from '@/common/js/validate.js';
	export default {
		data() {
			return {
				search_text: '',
				statusList: [
					{
						id: 0,
						name: '全部',
						status: '',
						type: ''
					},
					{
						id: 1,
						name: '营业中',
						status: 1,
						type: 1
					},
					{
						id: 2,
						name: '休息中',
						status: 0,
						type: 1
					},
					{
						id: 3,
						name: '已停业',
						status: 1,
						type: 2
					}
				],
				status: 0,
				dashboard_list: [],
				password: {
					newPwd: '',
					againNew: ''
				},
				store_id: ''
			}
		},
		onShow(){
			if (this.mescroll) this.mescroll.resetUpScroll();
			// this.$refs.mescroll.refresh();
		},
		methods: {
			tabChange(e){
				this.status = e
				this.$refs.mescroll.refresh();
			},
			searchGoods() {
				this.$refs.mescroll.refresh();
			},
			// onEdit(store_id){
			// 	this.$util.redirectTo('/pages/storemanage/edit/edit', {store_id: store_id,type: 0})
			// },
			linkSkip(){
				this.$util.redirectTo('/pages/storemanage/edit/edit', {type: 1})
			},
			//重置密码
			changePass(item) {
				this.store_id = item.store_id
				this.$refs.editPasswordPopse.open();
			},
			closeEditPasswordPop() {
				this.password.newPwd = '';
				this.password.againNew = '';
				this.password.store_id = ''
				this.$refs.editPasswordPopse.close()
			},
			//表单验证
			verify() {
				let rule = [];
				rule = [{
					name: 'newPwd',
					checkType: 'required',
					errorMsg: '密码不能为空'
				}, ];
			
				var checkRes = validate.check(this.password, rule);
				if (checkRes) {
					if (this.password.newPwd != this.password.againNew) {
						this.$util.showToast({
							title: '两次密码不一致'
						});
						return false;
					}
					return true;
				} else {
					this.$util.showToast({
						title: validate.error
					});
					return false;
				}
			},
			modifyPassword() {
				if (this.repeatFlag) return;
				this.repeatFlag = true;
				if (this.verify()) {
					this.$api.sendRequest({
						url: '/shopapi/member/modifyMemberPassword',
						data: {
							store_id: this.store_id,
							password: this.password.newPwd
						},
						success: res => {
							this.$util.showToast({
								title: res.message
							})
							if (res.code == 0) {
								this.closeEditPasswordPop();
							}
							this.repeatFlag = false;
						}
					});
				}else{
					this.repeatFlag = false;
				}
			},
			onTag(store_id,is_frozen){
				uni.showModal({
					title: is_frozen == 1 ? '开启门店' : '关闭门店',
					content: is_frozen == 1 ? '确定要开启该门店吗？' : '门店已开始运营，确定要关闭吗？',
					success: res => {
						if (res.confirm) {
							this.$api.sendRequest({
								url: '/shopapi/store/frozenStore',
								data: {
									store_id,
									is_frozen
								},
								success: res => {
									let msg = res.message;
									this.$util.showToast({
										title: msg
									});
									if(res.code == 0){
										this.$refs.mescroll.refresh();
									}
								}
							});
						}
					}
				});
			},
			getList(mescroll) {
				var data = {
					page: mescroll.num,
					page_size: mescroll.size,
					type: this.statusList[this.status].type,
					status: this.statusList[this.status].status,
					keyword: this.search_text
				};
				this.$api.sendRequest({
					url: '/shopapi/store/lists',
					data: data,
					success: res => {
						let newArr = [];
						let msg = res.message;
						if (res.code == 0 && res.data) {
							if (res.data.page_count == 0) {
								this.emptyShow = true;
							}
							newArr = res.data.list;
						} else {
							this.$util.showToast({
								title: msg
							});
						}
						mescroll.endSuccess(newArr.length);
						//设置列表数据
						if (mescroll.num == 1) this.dashboard_list = []; //如果是第一页需手动制空列表
						this.dashboard_list = this.dashboard_list.concat(newArr); //追加新数据
						if (this.$refs.loadingCover) this.$refs.loadingCover.hide();
					}
				});
			}
		}
	}
</script>

<style lang="scss">
.search-wrap {
	display: flex;
	justify-content: space-between;
	padding: 30rpx 30rpx 0;
	background-color: #fff;
	.search-input-inner {
		display: flex;
		align-items: center;
		width: 460rpx;
		height: 70rpx;
		padding: 0 30rpx;
		background-color: $color-bg;
		border-radius: 100rpx;
		box-sizing: border-box;
		.search-input-icon {
			margin-right: 10rpx;
			color: $color-tip;
		}
	}
	.search-btn {
		display: flex;
		justify-content: center;
		align-items: center;
		width: 200rpx;
		height: 70rpx;
		color: #fff;
		margin-left: 30rpx;
		border-radius: 100rpx;
		text {
			margin-right: 10rpx;
		}
	}
}
.tab-block {
	// position: relative;
	display: flex;
	flex-direction: row;
	justify-content: space-between; 
	background: #fff;
	// margin: 0 30rpx;
	.choose {
		// position: absolute;
		// right: 0;
		min-width: 50px;
		background-color: #fff;
		padding: 20rpx 0rpx 0 20rpx;
		height: 66rpx;
	}
	.tab-wrap {
		width: calc(100% - 120rpx);
		// position: relative;
		// overflow-x: scroll;
		padding: 24rpx 0rpx 0 20rpx;
		height: 66rpx;
		background-color: #fff;
		// white-space: nowrap;
		// overflow: hidden;
		// text-overflow: ellipsis;
		display: flex;
		flex-direction: row;
		justify-content: space-around;
	}
	.tab-item {
		// display: inline-block;
	}
	.active {
		position: relative;
		// margin-right: 32rpx;
		&::after {
			content: '';
			position: absolute;
			bottom: 0;
			left: 0;
			height: 4rpx;
			width: 100%;
		}
	}
}
.goods-class {
	margin:0 30rpx;
}
.goods-item {
	background: #FFFFFF;
	border-radius: 10rpx;
	margin-top: 20rpx;
	padding:0 30rpx 30rpx;
}
.goods-item-title {
	display: flex;
	flex-direction: row;
	justify-content: space-between;
	align-items: center;
	height: 70rpx;
	border-bottom: 1px solid #eee;
	
	.title-ordernum {
		font-size: 30rpx;
		font-family: PingFang SC;
		font-weight: bold;
		color: #303133;
	}
	.title-ordertext {
		font-size: 24rpx;
		font-family: PingFang SC;
		font-weight: 500;
		color: #909399;
	}
	.title-orderactive {
		font-size: 24rpx;
		font-family: PingFang SC;
		font-weight: 500;
		color: #08BA06;
	}
	.title-orderxiuxi {
		font-size: 24rpx;
		font-family: PingFang SC;
		font-weight: 500;
		color: #FF6A00;
	}
}
.goods-item-content {
		display: flex;
		flex-direction: row;
		padding-top: 10rpx;
		// margin-top:10rpx;
		.content-left {
			font-size: 28rpx;
			font-family: PingFang SC;
			font-weight: 500;
			color: #303133;
			min-width: 160rpx;
		}
		.content-right {
			font-size: 28rpx;
			font-family: PingFang SC;
			font-weight: 500;
			color: #303133;
			margin-left: 80rpx;
		}
		.content-last {
			font-size: 28rpx;
			font-family: PingFang SC;
			font-weight: 500;
			color: #FF6A00;
			margin-left: 30rpx;
		}
	}
	.goods-btn {
		display: flex;
		flex-direction: row-reverse;
		margin-top: 25rpx;
		.goods-btn-search {
			// padding:0 20rpx;
			color: #303133;
			border-color: #909399;
			
		}
		.goods-btn-item {
			color: #FF6A00;
			border-color: #FF6A00;
			margin-left: 20rpx !important;
		}
		.bacolors {
			background: #FF6A00;
			border:none;
			color: #fff;
		}
	}
	.pop-wrap {
		width: 80vw;
		.title {
			padding: $padding 30rpx;
			text-align: center;
			position: relative;
			.close {
				position: absolute;
				right: 30rpx;
				top: 20rpx;
				height: 60rpx;
				width: 60rpx;
			}
		}
		.flex-center {
			display: flex;
			justify-content: center;
			align-items: center;
			margin: 0 auto 20rpx;
			width: 480rpx;
			letter-spacing: 2rpx;
			height: 70rpx;
			background: #FFFFFF;
			border: 1px solid #CCCCCC;
			border-radius: 32rpx;
		}
		.active-flex {
			background: #FF6A00;
			border:0;
			color:#fff;
		}
		.flex {
			display: flex;
			justify-content: space-between;
			margin: 0 $margin-both;
			padding: 30rpx 0;
			align-items: center;
			border-bottom: 1px solid $color-line;
			&.last_child {
				border-bottom: 0;
			}
			.flex_right {
				flex: 1;
				text-align: right;
			}
		}
		.action-btn {
			display: flex;
			justify-content: space-between;
			border-top: 1px solid $color-line;
	
			> view {
				flex: 1;
				text-align: center;
				padding: $padding;
				&.line {
					border-right: 1px solid $color-line;
				}
			}
		}
	}
</style>

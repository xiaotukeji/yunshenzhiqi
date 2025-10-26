<template>
	<view class="member" @click="shwoOperation">
		<view class="search-inner">
			<view class="search-wrap">
				<text class="search-input-icon iconfont iconsousuo" @click.stop="searchMember()"></text>
				<input class="uni-input font-size-tag" maxlength="50" v-model="formData.search_text" placeholder="请输入会员昵称 / 手机号" @confirm="searchMember()" />
			</view>
			<view class="screen" @click.stop="showScreen = true">
				<text class="color-tip">...</text>
			</view>
		</view>
		<mescroll-uni class="list-wrap" @getData="getListData" top="160" refs="mescroll" :size="10">
			<block slot="list">
				<view class="item-inner" v-for="(item, index) in dataList" :key="index">
					<view class="item-wrap" @click.stop="shwoOperation(item)">
						<view class="wrap-top">
							<image class="item-img" :src="item.headimg == '' ? $util.img($util.getDefaultImage().default_headimg) : $util.img(item.headimg)" @error="imgError(index)"/>
							<view class="item-desc">
								<view class="item-num-wrap">
									<text class="item-name">{{ item.nickname }}</text>
									<view v-if="item.mobile" class="mobile-wrap">
										<text class="iconfont iconshouji1"></text>
										{{ item.mobile }}
									</view>
								</view>
								<!-- <view class="item-price font-size-tag" v-if="item.email">邮箱：{{ item.email }}</view> -->
								<view class="item-operation">
									<view style="display: flex;">
										<view class="vipbox non-member"  v-if="!item.is_member">非会员</view>
										<view class="vipbox" v-if="item.is_member && item.member_level_name">{{item.member_level_name}}</view>
										<view class="vipbox heiblack" v-if="item.status == 0">黑名单</view>
									</view>
									
									<!-- <text class="item-price">
										手机号：
										<text :class="item.is_subscribe ? 'color-base-text' : 'color-tip'">{{$util.timeStampTurnTime(item.reg_time)}}</text>
									</text> -->
									<text class="iconshenglve iconfont"></text>
								</view>
								<text class="item-price">
									最后访问：
									<text :class="item.is_subscribe ? 'color-base-text' : 'color-tip'">{{$util.timeStampTurnTime(item.last_login_time)}}</text>
								</text>
							</view>
						</view>
						<view class="wrap-bottom">
							<view class="wrap-bottom-box">
								<text>积分：{{ parseInt(item.point) }}</text>
								<text>余额：{{ parseFloat(item.balance) + parseFloat(item.balance_money)}}</text>
								<text>消费金额：{{item.order_money}}</text>
							</view>
							<!-- <view class="bottom-box-time">最后登录时间：{{$util.timeStampTurnTime(item.reg_time)}}</view> -->
						</view>
					</view>
					<view class="operation" @click.stop="showHide(item)" v-if="item.is_off">
						<block v-if="item.status == 1">
							<view class="operation-item" @click.stop="linkSkip(item)">
								<image :src="$util.img('public/uniapp/shop_uniapp/member/member_01.png')" mode=""></image>
								<text>查看详情</text>
							</view>
							<view class="operation-item" @click.stop="changeAccount(item)">
								<image :src="$util.img('public/uniapp/shop_uniapp/member/repass.png')" mode=""></image>
								<text>账户调整</text>
							</view>
							<view class="operation-item" @click.stop="linkSkip(item, 'coupon')">
								<image :src="$util.img('public/uniapp/shop_uniapp/member/member_02.png')" mode=""></image>
								<text>发放优惠券</text>
							</view>
							<view class="operation-item" @click.stop="changePass(item)">
								<image :src="$util.img('public/uniapp/shop_uniapp/member/adjust_account.png')" mode=""></image>
								<text>重置密码</text>
							</view>
						</block>
						<view class="operation-item" @click.stop="blacklist(item)">
							<image :src="$util.img('public/uniapp/shop_uniapp/member/blacklist.png')" mode=""></image>
							<text>{{item.status == 0 ? '移除黑名单' : '设为黑名单'}}</text>
						</view>
					</view>
				</view>
				<ns-empty v-if="!dataList.length" text="暂无会员数据"></ns-empty>
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
		<uni-popup ref="editPasswordPopses">
			<view class="pop-wrap" @touchmove.prevent.stop>
				<view class="title font-size-toolbar">
					选择调整账户
					<view class="close color-tip" @click.stop="closeEditPasswordPop()"><text class="iconfont iconclose"></text></view>
				</view>
				<view class="flex-center" @click="selectAccount(1)" :class="accountData == 1 ? 'active-flex' : ''">积分</view>
				<view class="flex-center" @click="selectAccount(2)" :class="accountData == 2 ? 'active-flex' : ''">储值余额</view>
				<!-- <view class="flex-center" @click="selectAccount(3)" :class="accountData == 3 ? 'active-flex' : ''">余额(可提现)</view> -->
				<view class="flex-center" @click="selectAccount(4)" :class="accountData == 4 ? 'active-flex' : ''">成长值</view>
				<view class="action-btn">
					<view class="line" @click.stop="closeEditPasswordPop()">取消</view>
					<view class="color-line-border color-base-text" @click.stop="onAccount()">确定</view>
				</view>
			</view>
		</uni-popup>
		<uni-drawer :visible="showScreen" mode="right" @close="showScreen = false" class="screen-wrap">
			<view class="title color-tip">筛选</view>
			<scroll-view scroll-y="true">
				<view class="item-wrap">
					<view class="label">会员昵称/手机号</view>
					<view class="value-wrap"><input class="uni-input" placeholder="请输入会员昵称/手机号" v-model="formData.search_text" /></view>
				</view>
				<view class="item-wrap">
					<view class="label">积分</view>
					<view class="value-wrap">
						<input class="uni-input" placeholder="最低积分" v-model="formData.start_point" />
						<view class="h-line"></view>
						<input class="uni-input" placeholder="最高积分" v-model="formData.end_point" />
					</view>
				</view>
				<view class="item-wrap">
					<view class="label">余额</view>
					<view class="value-wrap">
						<input class="uni-input" placeholder="最低余额" v-model="formData.start_balance" />
						<view class="h-line"></view>
						<input class="uni-input" placeholder="最高余额" v-model="formData.end_balance" />
					</view>
				</view>
				<view class="item-wrap">
					<view class="label">成长值</view>
					<view class="value-wrap">
						<input class="uni-input" placeholder="最低成长值" v-model="formData.start_growth" />
						<view class="h-line"></view>
						<input class="uni-input" placeholder="最高成长值" v-model="formData.end_growth" />
					</view>
				</view>
				<view class="item-wrap">
					<view class="label">消费金额</view>
					<view class="value-wrap">
						<input class="uni-input" placeholder="最低消费金额" v-model="formData.start_order_complete_money" />
						<view class="h-line"></view>
						<input class="uni-input" placeholder="最高消费金额" v-model="formData.end_order_complete_money" />
					</view>
				</view>
				<view class="item-wrap">
					<view class="label">是否是会员</view>
					<view class="list">
						<uni-tag
							:inverted="true"
							text="全部"
							type="primary"
							:type="formData.is_member === '' ? 'primary' : 'default'"
							@click="screenIsMember('')"
						/>
						<uni-tag
							:inverted="true"
							text="是会员"
							type="primary"
							:type="formData.is_member == 1 ? 'primary' : 'default'"
							@click="screenIsMember(1)"
						/>
						<uni-tag
							:inverted="true"
							text="非会员"
							type="primary"
							:type="formData.is_member === 0 ? 'primary' : 'default'"
							@click="screenIsMember(0)"
						/>
					</view>
				</view>
				<view class="item-wrap">
					<view class="label">状态</view>
					<view class="list">
						<uni-tag
							:inverted="true"
							text="全部"
							type="primary"
							:type="formData.status === '' ? 'primary' : 'default'"
							@click="screenMemberStatus('')"
						/>
						<uni-tag
							:inverted="true"
							text="正常"
							type="primary"
							:type="formData.status == 1 ? 'primary' : 'default'"
							@click="screenMemberStatus(1)"
						/>
						<uni-tag
							:inverted="true"
							text="黑名单"
							type="primary"
							:type="formData.status === 0 ? 'primary' : 'default'"
							@click="screenMemberStatus(0)"
						/>
					</view>
				</view>
				<!-- <view class="item-wrap">
					<view class="label">是否是会员</view>
					<view class="value-wrap">
						<ns-switch class="balance-switch" @change="onLimit"
							:checked="formData.is_member == 1"></ns-switch>
					</view>
				</view> -->
			</scroll-view>
			<view class="footer">
				<button type="default" @click="resetData">重置</button>
				<button type="primary" @click="searchMember">确定</button>
			</view>
		</uni-drawer>
		<loading-cover ref="loadingCover"></loading-cover>
	</view>
</template>

<script>
import validate from '@/common/js/validate.js';
import {modifyMemberPassword,getMemberList,editMemberJoinBlacklist} from '@/api/member'
export default {
	data() {
		return {
			dataList: [],
			lists:{},
			password: {
				newPwd: '',
				againNew: '',
				member_id: 0
			},
			accountData:1,
			showScreen: false,
			formData: {
				search_text: '',
				start_order_complete_money: '',
				end_order_complete_money: '',
				start_point: '',
				end_point: '',
				start_balance: '',
				end_balance: '',
				start_growth: '',
				end_growth: '',
				is_member: '',
				status: ''
			}
		};
	},
	onShow() {
		if (!this.$util.checkToken('/pages/member/list')) return;
		this.$store.dispatch('getShopInfo');
		if (this.mescroll) this.mescroll.resetUpScroll();
	},
	methods: {
		screenIsMember(value){
			this.formData.is_member = value;
		},
		screenMemberStatus(value){
			this.formData.status = value;
		},
		//设为黑名单
		blacklist(item){
			let that = this
			uni.showModal({
				content: item.status == 0 ? '确定移除黑名单吗？' : '确定加入黑名单吗？',
				showCancel: true,
				success(res){
					item.is_off = 0
					if (res.confirm) {
						editMemberJoinBlacklist({ member_id: item.member_id, status: item.status == 1 ? 0 : 1 }).then(res=>{
							if(res.code == 0){
								that.$util.showToast({ title: '设置成功', icon: 'success'})
								// that.getListData(that.mescroll)
								// that.$forceUpdate();
								that.mescroll.resetUpScroll();
							}else{
								that.$util.showToast({ title: '设置失败'})
							}
						})
					}
				}
			})
		},
		selectAccount(index){
			this.accountData = index
		},
		//账户调整
		changeAccount(item) {
			item.is_off = 0;
			this.lists = item
			this.$refs.editPasswordPopses.open();
		},
		onAccount(){
			this.closeEditPasswordPop();
			this.$util.redirectTo('/pages/member/adjustaccount',{type: this.accountData,member_id: this.lists.member_id});
		},
		//重置密码
		changePass(item) {
			item.is_off = 0;
			this.password.member_id = item.member_id;
			this.$refs.editPasswordPopse.open();
		},
		closeEditPasswordPop() {
			this.password.newPwd = '';
			this.password.againNew = '';
			this.password.member_id = 0
			this.$refs.editPasswordPopse.close();
			this.$refs.editPasswordPopses.close()
		},
		modifyPassword() {
			if (this.repeatFlag) return;
			this.repeatFlag = true;
			if (this.verify()) {
				modifyMemberPassword({
					member_id: this.password.member_id,
					password: this.password.newPwd
				}).then(res=>{
					this.$util.showToast({
						title: res.message
					})
					if (res.code == 0) {
						this.closeEditPasswordPop();
					}
					this.repeatFlag = false;
				});
			}else{
				this.repeatFlag = false;
			}
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
		showHide(val) {
			val.is_off = !val.is_off;
		},
		shwoOperation(item='') {
			let stop = false;
			this.dataList.forEach(v => {
				if (v.is_off == 1) {
					stop = true;
				}
				v.is_off = 0;
			});

			if (!stop && item != '') item.is_off = 1;
		},
		getListData(mescroll) {
			let data = {
				page_size: mescroll.size,
				page: mescroll.num
			};
			Object.assign(data, this.formData)
			this.mescroll = mescroll;
			getMemberList(data).then(res=>{
				let newArr = [];
				let msg = res.message;
				if (res.code == 0 && res.data) {
					newArr = res.data.list;
				} else {
					this.$util.showToast({ title: msg });
				}
				mescroll.endSuccess(newArr.length);
				//设置列表数据
				if (mescroll.num == 1) this.dataList = []; //如果是第一页需手动制空列表
				newArr.forEach(v => {
					v.is_off = 0;
				});
				this.dataList = this.dataList.concat(newArr); //追加新数据
				if (this.$refs.loadingCover) this.$refs.loadingCover.hide();
			});
		},
		resetData(){
			this.formData = {
				search_text: '',
				start_order_complete_money: '',
				end_order_complete_money: '',
				start_point: '',
				end_point: '',
				start_balance: '',
				end_balance: '',
				start_growth: '',
				end_growth: '',
				is_member: '',
				status: ''
			};
		},
		searchMember() {
			this.mescroll.resetUpScroll();
			this.showScreen = false;
		},
		linkSkip(item, type) {
			item.is_off = 0;
			if (type) this.$util.redirectTo('/pages/member/coupon', { member_id: item.member_id });
			else this.$util.redirectTo('/pages/member/detail', { member_id: item.member_id });
		},
		imgError(index) {
			this.dataList[index].headimg = this.$util.getDefaultImage().default_headimg;
			this.$forceUpdate();
		}
	}
};
</script>
<style lang="scss">
page {
	overflow: hidden;
}
.search-inner {
	padding: 30rpx;
	background-color: #fff;
	display: flex;
	align-items: center;
	.screen {
		padding-left: 20rpx;
		
		text {
			font-size: 50rpx;
			line-height: 1;
			display: inline-block;
			transform: translateY(-10rpx);
		}
	}
	.search-wrap {
		flex: 1;
		display: flex;
		align-items: center;
		padding: 0 30rpx;
		height: 70rpx;
		background-color: $color-bg;
		border-radius: 100rpx;
		.search-input-icon {
			margin-right: 20rpx;
			color: $color-tip;
		}
		input {
			flex: 1;
		}
	}
}
.item-inner {
	position: relative;
	margin: 0 30rpx 20rpx;
	background-color: #fff;
	border-radius: $border-radius;
	.item-wrap {
		// display: flex;
		// flex-direction: row;
		// align-items: center;
		padding: 30rpx;
		
		.wrap-top {
			display: flex;
			flex-direction: row;
		}
		.wrap-bottom {
			margin-top:15rpx;
			.wrap-bottom-box {
				display: flex;
				flex-direction: row;
				justify-content: space-between;
				align-items: center;
				text {
					// display: inline-block;
					// margin-right: 20rpx;
					font-size: 24rpx;
					font-family: PingFang SC;
					font-weight: 500;
					color: #303133;
				}
			}
			.bottom-box-time {
				font-family: 'PingFang SC';
				font-size: 14rpx;
				color: #303133;
			}
		}
		.item-img {
			margin-right: 20rpx;
			width: 120rpx;
			height: 120rpx;
			border-radius: 50%;
		}
		.item-desc {
			flex: 1;
			.item-num-wrap {
				display: flex;
				align-items: center;
				color: $color-title;
				margin-bottom: 6rpx;
				.item-name {
					max-width: 190rpx;
					overflow: hidden;
					text-overflow: ellipsis;
					white-space: nowrap;
				}
				.mobile-wrap {
					display: flex;
					align-items: center;
					margin-left: 30rpx;
					.iconfont {
						font-size: 34rpx;
						color: $color-title;
					}
				}
			}
			.item-operation {
				display: flex;
				align-items: center;
				justify-content: space-between;
				line-height: 1;
				.vipbox {
					padding:8rpx 20rpx;
					background: #F6B373;
					border-radius: 20rpx;
					text-align: center;
					line-height: 1;
					font-size: 26rpx;
					font-family: PingFang SC;
					font-weight: 500;
					color: #FFFFFF;
				}
				.heiblack {
					width:100rpx;
					background:#000;
					margin-left:20rpx;
				}
				.non-member {
					background:#ddd;
				}
				.item-price {
					font-size: $font-size-tag;
				}
				.iconshenglve {
					font-size: 48rpx;
					color: $color-tip;
				}
			}
		}
	}
	.operation {
		overflow: hidden;
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background-color: rgba(0, 0, 0, 0.6);
		display: flex;
		justify-content: space-around;
		align-items: center;
		border-radius: 10rpx;
		.operation-item {
			display: flex;
			flex-direction: column;
			align-items: center;
			image {
				width: 64rpx;
				height: 64rpx;
			}
			text {
				margin-top: 20rpx;
				font-size: $font-size-tag;
				line-height: 1;
				color: #fff;
			}
		}
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
.screen-wrap {
	.title {
		font-size: $font-size-tag;
		padding: $padding;
		background: $color-bg;
	}
	scroll-view {
		height: 85%;
		.item-wrap {
			border-bottom: 1px solid $color-line;
			&:last-child {
				border-bottom: none;
			}
			.label {
				font-size: $font-size-tag;
				padding: $padding 30rpx 0 $padding;
				display: flex;
				justify-content: space-between;
				align-items: center;
				.more {
					font-size: $font-size-tag;
					picker {
						display: inline-block;
						vertical-align: middle;
						view {
							font-size: $font-size-tag;
						}
					}
					.iconfont {
						display: inline-block;
						vertical-align: middle;
						color: $color-tip;
						font-size: $font-size-base;
					}
				}
				.uni-tag {
					padding: 0 $padding;
					font-size: $font-size-goods-tag;
					background: $color-bg;
					height: 40rpx;
					line-height: 40rpx;
					border: 0;
					margin-left: $margin-updown;
				}
			}

			.list {
				margin: $margin-updown $margin-both;
				overflow: hidden;
				.uni-tag {
					padding: 0 $padding;
					font-size: $font-size-goods-tag;
					background: $color-bg;
					height: 52rpx;
					line-height: 52rpx;
					border: 0;
					margin-right: 20rpx;
					margin-bottom: 20rpx;
					&:nth-child(3n) {
						margin-right: 0;
					}
				}
			}
			.value-wrap {
				display: flex;
				justify-content: center;
				align-items: center;
				padding: $padding;
				.h-line {
					width: 40rpx;
					height: 2rpx;
					background-color: $color-tip;
				}
				input {
					flex: 1;
					background: $color-line;
					height: 60rpx;
					line-height: 60rpx;
					font-size: $font-size-goods-tag;
					border-radius: 50rpx;
					text-align: center;
					&:first-child {
						margin-right: 10rpx;
					}
					&:last-child {
						margin-left: 10rpx;
					}
				}
				picker {
					display: inline-block;
					vertical-align: middle;
					view {
						font-size: $font-size-tag;
					}
				}
			}
		}
	}
	.footer {
		height: 90rpx;
		display: flex;
		justify-content: center;
		align-items: flex-start;
		//position: absolute;
		bottom: 0;
		width: 100%;
		button {
			margin: 0;
			width: 40%;
			&:first-child {
				border-top-right-radius: 0;
				border-bottom-right-radius: 0;
			}
			&:last-child {
				border-top-left-radius: 0;
				border-bottom-left-radius: 0;
			}
		}
	}
}

</style>

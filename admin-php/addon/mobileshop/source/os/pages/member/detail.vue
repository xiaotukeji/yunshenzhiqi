<template>
	<view class="member">
		<view class="tab-block">
			<view class="tab-wrap">
				<block v-for="(item, index) in list" :key="index">
					<view class="tab-item" @click.stop="changeAct(item)" :class="index == act ? 'active color-base-text color-base-bg-before' : ''">{{ item.name }}</view>
				</block>
			</view>
		</view>

		<view class="content contentbox">
			<block v-if="act == 0">
				<view class="order-list">
					<view class="list-left">头像</view>
					<view class="list-right">
						<image v-if="!memberData.member_info.headimg" :src="memberData.member_info.headimg ? $util.img(memberData.member_info.headimg) : $util.img($util.getDefaultImage().default_headimg)"/>
						<image v-else :src="$util.img(memberData.member_info.headimg)" mode="aspectFit" @error="imgError('headimg')" @click.stop="previewMedia('headimg')"/>
						<!-- <view class="del-wrap iconfont iconclose" @click.stop="delImg('headimg')"
							v-if="memberData.member_info.headimg"></view> -->
					</view>
				</view>
				<view class="order-list">
					<view class="list-left">昵称</view>
					<view class="list-right">
						<input type="text" v-model="memberData.member_info.nickname" />
						<!-- <text class="iconfont iconright"></text> -->
					</view>
				</view>
				<view class="order-list">
					<view class="list-left">手机号</view>
					<view class="list-right"><input type="number" maxlength="11" v-model="memberData.member_info.mobile" placeholder="暂无" /></view>
				</view>
				<view class="order-list">
					<view class="list-left">会员等级</view>
					<view class="list-right">
						<block v-if="memberData.member_info.is_member">
							<picker @change="bindLevelChange" :value="index1" :range="levelList">
								<view class="uni-input">{{ levelList[index1] }}</view>
							</picker>
							<text class="iconfont iconright"></text>
						</block>
						<text v-else>非会员</text>
					</view>
				</view>
				<!-- <view class="order-list">
					<view class="list-left">会员标签</view>
					<view class="list-right">
						<picker style="min-width: 50rpx;" @change="bindLabelChange" :value="index2" :range="labelList">
							<view class="uni-input">{{labelList[index2]}}</view>
						</picker>
						<text class="iconfont iconright content-ico"></text>
					</view>
				</view> -->
				<view class="order-list">
					<view class="list-left">会员性别</view>
					<view class="list-right">
						<picker @change="bindGenderChange" :value="index" :range="genderArray">
							<view class="uni-input">{{ genderArray[index] }}</view>
						</picker>
						<text class="iconfont iconright"></text>
					</view>
				</view>
				<view class="order-list" style="border: none;">
					<view class="list-left">会员生日</view>
					<view class="list-right">
						<picker mode="date" :value="date" :end="endDate" @change="bindDateChange" v-if="memberData.member_info.birthday">
							<view class="uni-input">{{ date }}</view>
						</picker>
						<picker mode="date" :end="endDate" @change="bindDateChange" v-else>
							<view class="uni-input">{{ date }}</view>
						</picker>
						<text class="iconfont iconright"></text>
					</view>
				</view>
				<view class="footer-wrap" :class="{ 'safe-area': isIphoneX }">
					<button type="primary" @click="save()">保存</button>
				</view>
			</block>

			<view class="account information" v-if="act == 1" style="background: #FFFFFF;">
				<view class="order-list" @click="onDetail(1)" :class="accountData == 1 ? 'active-flex' : ''">
					<view class="list-left">积分</view>
					<view class="list-right">
						<!-- 	<input type="text" v-model="memberData.point" placeholder="0.00" /> -->
						<text>{{ parseInt(memberData.member_info.point) || '0' }}</text>
						<text class="iconfont iconright"></text>
					</view>
				</view>
				<view class="order-list" @click="onDetail(2)">
					<view class="list-left">储值余额</view>
					<view class="list-right">
						<!-- 	<input type="text" v-model="memberData.balance" placeholder="0.00" /> -->
						<text>{{ memberData.member_info.balance || '0' }}</text>
						<text class="iconfont iconright"></text>
					</view>
				</view>
				<view class="order-list">
					<view class="list-left">现金余额</view>
					<view class="list-right">
						<!-- 	<input type="text" v-model="memberData.balance_money" placeholder="0.00" /> -->
						<text>{{ memberData.member_info.balance_money || '0' }}</text>
						<text></text>
					</view>
				</view>
				<view class="order-list" style="border: none;" @click="onDetail(4)">
					<view class="list-left">成长值</view>
					<view class="list-right">
						<!-- <input type="text" v-model="memberData.growth" placeholder="0.00" /> -->
						<text>{{ parseInt(memberData.member_info.growth) || '0' }}</text>
						<text class="iconfont iconright"></text>
					</view>
				</view>
			</view>

			<view style="background-color: #fff;margin-top: 20rpx;" v-if="act == 1">
				<view class="order-list" style="border: none;">
					<view class="list-left">账户记录</view>
					<view class="list-right">
						<picker :value="index" :range="array" @change="bindPickerChange">
							<view class="select color-tip account-search" style="display: flex;justify-content: space-between;padding: 5rpx 19rpx;">
								{{ array[index] }}
								<text class="iconfont iconiconangledown" style="transform: scale(1.8);"></text>
							</view>
						</picker>
					</view>
				</view>
				<mescroll-uni @getData="getOrderData" refs="mescroll" top="600" :size="10">
					<block slot="list">
						<block v-if="accountData.length > 0">
							<view class="member-list">
								<view class="member-list-item" v-for="(item, index) in accountData" :key="index">
									<view class="integral-top" style="display: flex;justify-content: space-between; align-items: center;">
										<view class="integral" style="display: flex;">{{ item.type_name }}</view>
										<view class="integral-num" v-if="item.account_type == 'balance' || item.account_type == 'balance_money'">{{ item.account_data }}</view>
										<view class="integral-num" v-else>{{ parseInt(item.account_data) }}</view>
									</view>

									<view class="member-list-sec">
										<view class="integral-time">{{ item.type_name }}</view>
										<view class="integral-times">{{ $util.timeStampTurnTime(item.create_time) }}</view>
										<view class="integral-times">{{ item.account_type_name }}</view>
									</view>
									<view class="member-list-bottom" style="margin-top: 19rpx;margin-right: 30rpx;" v-if="item.remark">
										<view class="integral-give">{{ item.remark }}</view>
									</view>
								</view>
							</view>
						</block>
						<ns-empty v-else text="暂无数据"></ns-empty>
					</block>
				</mescroll-uni>
			</view>

			<view class="account information" v-if="act == 2">
				<mescroll-uni @getData="getDetailData" refs="mescrolls" top="100" :size="10">
					<block slot="list">
						<view class="search-inner">
							<view class="search-wrap">
								<text class="search-input-icon iconfont iconsousuo" @click.stop="searchMember()" style="color: #909399;"></text>
								<input class="uni-input font-size-tag" maxlength="50" v-model="searchMemberName" placeholder="请输入订单编号" @confirm="searchMember()" />
							</view>
						</view>
						<view class="order-list order-items">
							<view class="item-inner" v-for="(item, index) in DetailData" :key="index" @click="linkSkip(item)">
								<view class="order-other-info">
									<text class="color-tip">订单号：{{ item.order_no }}</text>
									<view>
										<text class="color-base-text order-type">{{ item.order_type_name }}</text>
										<text>{{ item.order_status_name }}</text>
									</view>
								</view>
								<view class="order-other-info">
									<text class="place-time color-tip">{{ $util.timeStampTurnTime(item.create_time) }}</text>
									<view>
										<text class="place-time color-tip">订单金额：</text>
										<text class="color-base-text">￥{{ item.order_money }}</text>
									</view>
								</view>
							</view>
						</view>
						<ns-empty v-if="!DetailData.length" text="暂无订单数据"></ns-empty>
					</block>
				</mescroll-uni>
			</view>
		</view>
	</view>
</template>

<script>
import {getMemberInfoById,editMember,getMemberAccountList,getMemberOrderList} from '@/api/member'
import {getShopWithdrawList} from '@/api/shop'
export default {
	data() {
		const currentDate = this.getDate({
			format: true
		});
		return {
			isIphoneX: false,
			title: 'picker',
			date: '未知',
			act: 0,
			memberId: 0,
			dataList: [],
			index1: 0,
			index2: 0,
			memberData: {
				member_info: {},
				member_label_list: [],
				member_level_list: []
			},
			levelList: [],
			labelList: [],
			searchMemberName: '',
			genderArray: ['保密', '男', '女'],
			list: [
				{
					id: 0,
					name: '基础信息'
				},
				{
					id: 1,
					name: '账户信息'
				},
				{
					id: 2,
					name: '订单信息'
				}
			],
			content: '',
			accountData: [],
			DetailData: [],
			array: ['全部', '积分', '现金余额', '储值余额', '成长值'],
			index: 0,
			status: '',
			endDate: currentDate
		};
	},
	onLoad(option) {
		option.member_id ? (this.memberId = option.member_id) : this.$util.redirectTo('/pages/member/list', {}, 'redirectTo');
		if (!this.$util.checkToken('/pages/member/detail?member_id=' + this.memberId)) return;
		option.member_id ? (this.memberId = option.member_id) : this.$util.redirectTo('/pages/Member/list', {}, 'redirectTo');
		if (!this.$util.checkToken('/pages/Member/orderList')) return;
	},
	onShow() {
		// 页面默认显示的是list列表中第一条数据
		this.content = this.list[0];
		this.isIphoneX = this.$util.uniappIsIPhoneX();
		// this.getAccountData()
		this.getMeberData();
	},
	methods: {
		onDetail(e) {
			this.$util.redirectTo('/pages/member/adjustaccount', {
				type: e,
				member_id: this.memberData.member_info.member_id
			});
		},
		save() {
			let data = {
				member_id: this.memberData.member_info.member_id,
				heading: this.memberData.member_info.headimg,
				nickname: this.memberData.member_info.nickname,
				mobile: this.memberData.member_info.mobile,
				level_id: this.memberData.member_level_list[this.index1].level_id,
				sex: this.index,
				birthday: this.date
			};
			editMember(data).then(res=>{
				let msg = res.message;
				this.$util.showToast({
					title: msg
				});
				if (res.code == 0) {
					setTimeout(() => {
						this.$util.redirectTo('/pages/member/list');
					}, 500);
				}
			});
		},
		uplodImg(type) {
			this.$util.upload(
				{
					number: 1,
					path: 'image'
				},
				res => {
					if (res) {
						this.$util.showToast({
							title: '上传成功'
						});
						if (type == 'headimg') this.memberData.member_info.headimg = res[0];
					}
				}
			);
		},
		delImg(type) {
			if (type == 'headimg') this.memberData.member_info.headimg = '';
		},
		previewMedia(type) {
			var paths = [this.$util.img(this.memberData[type])];
			uni.previewImage({
				current: 0,
				urls: paths
			});
		},
		bindGenderChange: function(e) {
			this.index = e.target.value;
		},
		bindLevelChange: function(e) {
			this.index1 = e.target.value;
		},
		bindLabelChange: function(e) {
			this.index2 = e.target.value;
		},
		// 生日时间选择
		bindDateChange: function(e) {
			this.date = e.target.value;
		},
		bindTimeChange: function(e) {
			this.time = e.target.value;
		},
		getDate(type) {
			const date = new Date();
			let year = date.getFullYear();
			let month = date.getMonth() + 1;
			let day = date.getDate();

			if (type === 'start') {
				year = year - 60;
			} else if (type === 'end') {
				year = year + 2;
			}
			month = month > 9 ? month : '0' + month;
			day = day > 9 ? day : '0' + day;
			return `${year}-${month}-${day}`;
		},
		bindPickerChange(e) {
			this.index = e.detail.value;
			if (e.detail.value == 0) {
				this.status = '';
			}
			if (e.detail.value == 1) {
				this.status = 'point';
			}
			if (e.detail.value == 2) {
				this.status = 'balance_money';
			}
			if (e.detail.value == 3) {
				this.status = 'balance';
			}
			if (e.detail.value == 4) {
				this.status = 'growth';
			}
			this.mescroll.resetUpScroll();
		},
		changeAct(item) {
			// 激活样式是当前点击的对应下标--list中对应id
			this.act = item.id;

			// 可以根据点击事件改变内容
			this.content = item;
		},
		imgError(index, type) {
			if (!type) {
				this.dataList[index].sku_image = this.$util.getDefaultImage().default_goods_img;
			} else {
				this.memberData.member_info.headimg = this.$util.getDefaultImage().default_headimg;
			}
			this.$forceUpdate();
		},
		getOrderData(mescroll) {
			let data = {
				page_size: mescroll.size,
				page: mescroll.num,
				member_id: this.memberId,
				account_type: this.status
			};
			this.mescroll = mescroll;
			getMemberAccountList(data).then(res=>{
				let newArr = [];
				let msg = res.message;
				if (res.code == 0 && res.data) {
					newArr = res.data.list;
				} else {
					this.$util.showToast({
						title: msg
					});
				}
				mescroll.endSuccess(newArr.length);
				//设置列表数据
				if (mescroll.num == 1) this.accountData = []; //如果是第一页需手动制空列表
				this.accountData = this.accountData.concat(newArr); //追加新数据
				if (this.$refs.loadingCover) this.$refs.loadingCover.hide();
			});
		},
		getMeberData() {
			getMemberInfoById(this.memberId).then(res=>{
				if (res.code == 0 && res.data) {
					this.memberData = res.data;
					if (res.data.member_info.birthday) this.date = this.$util.timeStampTurnTime(res.data.member_info.birthday, 'Y-m-d');
					for (let i = 0; i < res.data.member_level_list.length; i++) {
						if (res.data.member_info.member_level_name == res.data.member_level_list[i].level_name) {
							this.index1 = i;
						}
						this.levelList.push(res.data.member_level_list[i].level_name);
					}
					if (this.memberData.member_info.sex == '0') {
						this.index = 0;
					} else if (this.memberData.member_info.sex == '1') {
						this.index = 1;
					} else {
						this.index = 2;
					}
				} else {
					this.$util.showToast({
						title: res.message
					});
				}
			});
		},
		searchMember() {
			this.mescrolls.resetUpScroll();
		},
		getDetailData(mescroll) {
			let data = {
				page_size: mescroll.size,
				page: mescroll.num,
				member_id: this.memberId,
				search_text: this.searchMemberName,
			};
			this.mescrolls = mescroll;
			getMemberOrderList(data).then(res=>{
				let newArr = [];
				let msg = res.message;
				if (res.code == 0 && res.data) {
					newArr = res.data.list;
				} else {
					this.$util.showToast({
						title: msg
					});
				}
				mescroll.endSuccess(newArr.length);
				//设置列表数据
				if (mescroll.num == 1) this.DetailData = []; //如果是第一页需手动制空列表
				this.DetailData = this.DetailData.concat(newArr); //追加新数据
				if (this.$refs.loadingCover) this.$refs.loadingCover.hide();
			});
		},
		linkSkip(order) {
			var template = '';
			switch (order.order_type) {
				case 2:
					template = 'store';
					break;
				case 3:
					template = 'local';
					break;
				case 4:
					template = 'virtual';
					break;
				default:
					template = 'basis';
			}
			this.$util.redirectTo('/pages/order/detail/' + template, {
				order_id: order.order_id,
				template: template
			});
		}
	},
	getList(mescroll) {
		var data = {
			page: mescroll.num,
			page_size: mescroll.size,
			status: this.status
		};
		getShopWithdrawList(data).then(res=>{
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
		});
	}
};
</script>

<style lang="scss">
page {
	overflow: hidden;
}

.tab-block {
	display: flex;
	flex-direction: row;
	justify-content: space-between;
	background: #fff;

	.tab-wrap {
		width: 100%;
		height: 90rpx;
		background-color: #fff;
		display: flex;
		flex-direction: row;
		justify-content: space-around;
	}

	.tab-item {
		line-height: 90rpx;
	}

	.active {
		position: relative;

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

.account-search {
	width: 250rpx;
	background: #ffffff;
	border: 1px solid #cccccc;
	border-radius: 50rpx;
	display: flex;
	flex-direction: row;
	justify-content: space-between;
	align-items: center;
}

.search-inner {
	background-color: #f8f8f8;
	padding-left: 30rpx;
	padding-right: 30rpx;

	.search-wrap {
		display: flex;
		align-items: center;
		padding: 0 20rpx;
		height: 70rpx;
		margin-top: 20rpx;
		background-color: #ffffff;
		border-radius: 100rpx;

		.search-input-icon {
			margin-right: 20rpx;
			color: #f8f8f8;
		}

		input {
			flex: 1;
		}
	}
}

.tag {
	font-size: $font-size-goods-tag;
	margin-right: $margin-updown;
	border: 1px solid #ff6a00;
	border-radius: 2rpx;
	padding: 0 13rpx;
	line-height: 1.5;
	font-weight: 400;
	// width: 140rpx;
	color: #ff6a00;
}

.member_info {
	background-color: #fff;
	margin: $margin-both;
	border-radius: $border-radius;
	display: flex;
	flex-direction: row;
	justify-content: space-between;
	align-items: center;

	.account_base {
		display: flex;
		justify-content: space-between;
		padding: $padding 0 10rpx 30rpx;

		.head {
			height: 106rpx;
			width: 106rpx;
			margin-right: $margin-both;
		}

		.nickname {
			max-width: 360rpx;
			font-weight: 500;
			font-size: $font-size-toolbar;
		}
	}

	.account-about {
		// padding: 0 30rpx 30rpx;
		display: flex;
		justify-content: space-between;
		align-items: center;

		> view {
			flex: 1;
		}

		.num,
		.tip {
			color: #fff;
			text-align: center;
		}
	}
}

.order-title,
.member-title {
	position: relative;
	padding-left: 20rpx;
	color: $color-title;
	font-weight: bold;
	font-size: $font-size-toolbar;
	margin: 10rpx 30rpx 0;

	&::after {
		content: '';
		position: absolute;
		left: 0;
		top: 50%;
		height: 28rpx;
		width: 6rpx;
		transform: translateY(-50%);
	}
}

.order-list {
	.item-inner {
		position: relative;
		background-color: #fff;
		padding: 30rpx 30rpx;
		margin-bottom: 20rpx;

		.order-other-info {
			display: flex;
			justify-content: space-between;
			font-size: $font-size-tag;
		}

		.order-type {
			margin-right: 20rpx;
		}

		.item-wrap {
			display: flex;
			padding: 30rpx 20rpx 30rpx 0;

			.item-img {
				margin-right: 20rpx;
				width: 120rpx;
				height: 120rpx;
				border-radius: 10rpx;
			}

			.item-desc {
				display: flex;
				flex-direction: column;
				flex: 1;
				color: $color-title;

				.item-name {
					margin-bottom: 12rpx;
					line-height: 1.4;
				}
			}

			.item-price-inner {
				display: flex;
				// justify-content: space-between;

				.goods-class {
					width: 300rpx;
					font-size: $font-size-activity-tag;
					color: $color-tip;
				}

				.item-price-wrap {
					display: flex;
					flex-direction: column;
					align-items: flex-end;
					font-weight: initial;
				}

				.item-price {
					width: 101rpx;
					height: 24rpx;
					font-size: 24rpx;
					font-family: Roboto;
					font-weight: 500;
					color: #303133;
					line-height: 38rpx;
				}

				.item-number {
					font-size: $font-size-tag;
					color: $color-tip;
				}
			}
		}

		.place-time {
			font-size: $font-size-tag;
		}
	}
}

/* 内容 */
// 头像
// 基础信息模块

.content {
	margin-top: 20rpx;
	background: #fff;
	padding: 0 30rpx;
}

.base-coentent {
	// width: 750rpx;
	height: 742rpx;
	background: #ffffff;
}

.name {
	color: #303133;
	font-size: 28rpx;
}

.order-list {
	display: flex;
	flex-direction: row;
	justify-content: space-between;
	align-items: center;
	border-bottom: 1px solid #eee;
	padding: 20rpx 0;

	.list-right {
		display: flex;
		flex-direction: row;
		align-items: center;
		font-size: 28rpx;
		font-family: PingFang SC;
		font-weight: 500;
		color: #303133;

		input {
			font-size: 28rpx;
			font-family: PingFang SC;
			font-weight: 500;
			color: #909399;
			text-align: right;
			margin-right: 20rpx;
			max-width: 280rpx;
		}

		image {
			width: 82rpx;
			height: 82rpx;
			border-radius: 50%;
		}

		.order-content {
			font-size: 28rpx;
			font-family: PingFang SC;
			font-weight: 500;
			color: #909399;
			text-align: right;
			margin-right: 20rpx;
		}

		switch,
		.uni-switch-wrapper,
		.uni-switch-input {
			width: 80rpx;
			height: 42rpx;
		}

		.iconfont {
			font-size: 30rpx;
			color: #909399;
		}

		label {
			font-size: 28rpx;
			color: #909399;
		}
	}

	.list-left {
		font-size: 28rpx;
		font-family: PingFang SC;
		font-weight: 500;
		color: #303133;
	}
}

.order-items {
	display: block;
}

.content-list {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 20rpx 0;
	border-bottom: 1rpx solid #eeeeee;
	position: relative;
}

.content-list:last-child {
	border: none;
}

.content-list input {
	text-align: right;

	margin-top: 25rpx;
	font-size: 26rpx;
}

.content-list image {
	width: 82rpx;
	height: 82rpx;
	border-radius: 50%;
	margin-left: 70rpx;
}

.account-section {
	width: 750rpx;
	justify-content: space-between;
}

.account-top {
	display: flex;
	background-color: #ffffff;
	justify-content: space-between;
	padding: 20rpx 30rpx;
	border-top: 40rpx solid #f8f8f8;
	//margin-top:40rpx;
}

.account-title {
	font-size: 32rpx;
	font-family: PingFang SC;
	font-weight: bold;
	color: #303133;
	line-height: 60rpx;
}

.account-section input {
	width: 270rpx;
	height: 60rpx;
	background: #ffffff;
	border: 1rpx solid #cccccc;
	border-radius: 30rpx;
	margin-top: 20rpx;
	margin-right: 30rpx;
}

.balance,
.member-list {
	background: #ffffff;
	padding: 0 30rpx;
}

.member-list-item {
	border-bottom: 1px solid #eeeeee;
	padding: 20rpx 0;
}

.member-list-item:last-child {
	border: none;
}

.member-list-sec {
	display: flex;
}

.integral {
	font-size: 28rpx;
	font-family: PingFang SC;
	font-weight: 500;
	color: #303133;
}

.integral-give {
	font-size: 26rpx;
	font-family: PingFang SC;
	font-weight: 500;
	color: #909399;
}

.member-list-top {
	display: flex;
	justify-content: space-between;
	margin-top: 52rpx;
}

.integral-num {
	font-size: 28rpx;
	font-family: PingFang SC;
	font-weight: 500;
	color: #303133;
}

.reward {
	font-size: 26rpx;
	font-family: PingFang SC;
	font-weight: 500;
	color: #909399;
}

.integral-time {
	font-size: 26rpx;
	font-family: PingFang SC;
	font-weight: 500;
	color: #909399;
	line-height: 20rpx;
	margin-top: 10rpx;
}

.integral-times {
	font-size: 26rpx;
	font-family: PingFang SC;
	font-weight: 500;
	color: #909399;
	line-height: 36rpx;
	margin-left: 30rpx;
	&:last-child {
		flex: 1;
		text-align: right;
		color: $color-title;
	}
}

.order {
	height: 25rpx;
	font-size: 26rpx;
	font-family: PingFang SC;
	font-weight: 500;
	color: #909399;
	line-height: 36rpx;
	margin-top: 22rpx;
}

.balance-no {
	height: 27rpx;
	font-size: 28rpx;
	font-family: PingFang SC;
	font-weight: 500;
	color: #303133;
	line-height: 36rpx;
	margin-top: 19rpx;
}

.balance-no-num {
	width: 107rpx;
	height: 22rpx;
	font-size: 28rpx;
	font-family: PingFang SC;
	font-weight: 500;
	color: #303133;
	line-height: 36rpx;
}

.adjust {
}

.order-seach {
	width: 690rpx;
	height: 70rpx;
	background: #ffffff;
	border-radius: 35rpx;
	margin: 20rpx auto;
}

.order-top {
	display: flex;
	justify-content: space-between;
	margin-top: 37rpx;
}

.order-number {
	height: 23rpx;
	font-size: 24rpx;
	font-family: PingFang SC;
	font-weight: 500;
	color: #909399;
	line-height: 20rpx;
}

.order-status {
	height: 23rpx;
	font-size: 24rpx;
	font-family: PingFang SC;
	font-weight: 500;
	color: #303133;
	line-height: 20rpx;
}

.order-introduce {
	width: 528rpx;
	height: 62rpx;
	font-size: 28rpx;
	font-family: PingFang SC;
	font-weight: 500;
	color: #303133;
	line-height: 36rpx;
}

.order-price {
	width: 101rpx;
	height: 24rpx;
	font-size: 24rpx;
	font-weight: 500;
	color: #303133;
	line-height: 38rpx;
}

.order-time {
	width: 249rpx;
	height: 18rpx;
	font-size: 24rpx;
	font-family: PingFang SC;
	font-weight: 500;
	color: #909399;
	line-height: 20rpx;
	margin-top: 30rpx;
}

.safe-area {
	padding-bottom: calc(constant(safe-area-inset-bottom) + 100rpx);
	padding-bottom: calc(env(safe-area-inset-bottom) + 100rpx);
}

.footer-wrap {
	position: fixed;
	width: 100%;
	bottom: 0;
	left: 0;
	// padding: 40rpx 0;
	z-index: 10;
	padding-bottom: 40rpx;
}
</style>

<template>
	<view>
		<view class="closeBox">
			<image :src="$util.img('public/uniapp/store/storeclose.png')" mode="widthFix"></image>
			<text class="close-title">{{ textVal }}</text>
		</view>
	</view>
</template>

<script>
export default {
	data() {
		return {
			isIphoneX: false
		};
	},
	onShow() {
		this.isIphoneX = this.$util.uniappIsIPhoneX();
		// #ifdef MP-WEIXIN
		wx.hideHomeButton();
		// #endif
	},
	methods:{
	},
	computed: {
		textVal() {
			return '商家手机管理端插件不存在';
		}
	},
	onBackPress() {
		return true;
	}
};
</script>

<style lang="scss">
.head-nav {
	width: 100%;
	height: var(--status-bar-height);
}

.head-nav.active {
	padding-top: 40rpx;
}

.head-return {
	padding-left: 30rpx;
	padding-right: 30rpx;
	height: 90rpx;
	line-height: 90rpx;
	text-align: center;
	font-weight: 600;
	font-size: $font-size-toolbar;

	text {
		display: inline-block;
		margin-right: 10rpx;
	}
}
.closeBox {
	width: 100%;
	display: flex;
	flex-direction: column;
	justify-content: center;
	align-items: center;
	margin-top: 330rpx;
}
image {
	width: 240rpx;
}
.close-title {
	font-size: $font-size-toolbar;
	color: $color-tip;
	margin-top: 55rpx;
	letter-spacing: 4rpx;
}
</style>

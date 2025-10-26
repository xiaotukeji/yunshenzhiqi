<template>
	<base-page>
		<view class="empty-box">
			<image src="@/static/cashier/store-close.png" mode="widthFix" />
			<view>门店已停业</view>
			<button class="btn" @click="$util.redirectTo('/pages/login/login')">返回</button>
		</view>
	</base-page>
</template>

<script>
	export default {
		data() {
			return {};
		},
		onLoad() {},
		onShow() {},
		methods: {}
	};
</script>

<style lang="scss" scoped>
	.empty-box {
		width: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-direction: column;
		height: 100%;
		position: fixed;
		left: 0;
		top: 0;
		background-color: #ffffff;
		z-index: 10000;

		image {
			width: 3rem;
			height: 3rem;
			max-height: 3rem;
		}

		view {
			text-align: center;
			font-size: 0.16rem;
			color: $primary-color;
			margin: 0 0 0.3rem;
		}

		button {
			background-color: #fff;
			color: $primary-color;
			border: 0.01rem solid $primary-color;
			font-size: 0.165rem;
			width: 1rem;
		}
	}
</style>
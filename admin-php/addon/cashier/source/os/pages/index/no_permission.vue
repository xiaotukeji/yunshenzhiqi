<template>
	<base-page>
		<view class="uni-flex uni-row height-all">
			<view class="container common-wrap" style="-webkit-flex: 1;flex: 1;">
				<view class="msg">对不起，您没有权限访问该页面</view>
			</view>
		</view>
	</base-page>
</template>

<script>
	export default {
		data() {
			return {};
		},
		onLoad() {
			uni.hideTabBar({});
		},
		methods: {}
	};
</script>

<style lang="scss" scoped>
	.container {
		display: flex;
		align-items: center;
		justify-content: center;
		flex-direction: column;
		background: #fff;

		.msg {
			color: #333;
			font-size: 0.16rem;
		}
	}
</style>
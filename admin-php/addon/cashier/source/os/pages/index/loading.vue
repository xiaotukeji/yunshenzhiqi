<template>
	<page-meta :root-font-size="rootSize"></page-meta>
	<view class="container" :style="themeColor">
		<image src="@/static/cashier/start_logo.png" mode="heightFix" />
	</view>
</template>

<script>
	export default {
		data() {
			return {};
		},
		onShow() {
			this.loadThemeColor();
		},
	};
</script>

<style lang="scss" scoped>
	.container {
		width: 100vw;
		height: 100vh;
		background: #fff;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;

		image {
			height: 80%;
		}
	}
</style>
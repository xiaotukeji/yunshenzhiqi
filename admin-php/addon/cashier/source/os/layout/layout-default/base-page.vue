<template>
	<view class="container">
		<!-- #ifdef APP-PLUS -->
		<layout-aside></layout-aside>
		<!-- #endif -->
		<view class="body-container common-scrollbar">
			<!-- <layout-top></layout-top> -->
			<view class="layout-content">
				<slot></slot>
			</view>
		</view>
	</view>
</template>

<script>
import layoutTop from './components/layout-top.vue';
import layoutAside from './components/layout-aside.vue';

export default {
	name: 'layoutDefault',
    components: {
        layoutTop,
		layoutAside
    },
	data() {
		return {};
	},
	created() {}
};
</script>

<style lang="scss">
.container {
	width: 100vw;
	height: calc(100vh - #{$statusbar-height});
	background: $body-bg;
	/* #ifdef H5 */
	min-width: 1200px;
	/* #endif */
}

.body-container {
	padding-top: 0;
	padding-left: 0.88rem;
	width: 100%;
	height: 100%;
	box-sizing: border-box;
	overflow-y: scroll;
	.layout-content{
		height:100%;
		overflow: hidden;
		box-sizing: border-box;
		padding: 0.20rem;
	}
}
</style>

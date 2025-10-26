<template>
	<page-meta :root-font-size="rootSize"></page-meta>
	<view :style="themeColor">
		<view class="status-bar-block"></view>
		<component :is="componentName">
			<slot></slot>
		</component>
	</view>
</template>

<script>
import layoutDefault from './layout-default/base-page.vue';
export default {
    components: {
        layoutDefault
    },
	data() {
		return {
			componentName: ''
		};
	},
	created() {
		this.componentName = uni.getStorageSync('current_layout') || 'layoutDefault';
		this.loadThemeColor();
	}
};
</script>

<style lang="scss" scoped>
.status-bar-block {
	width: 100vw;
	height: $statusbar-height;
}
</style>

<template>
	<unipopup ref="couponCategoryPop" type="center">
		<view class="coupon-category-pop">
			<view class="header flex justify-between">
				<view class="title">选择分类</view>
				<!-- <view class="pop-header-close" @click="$refs.couponCategoryPop.close()">
					<text class="iconguanbi1 iconfont"></text>
				</view> -->
			</view>
			<!-- :checkStrictly="true" -->
			<view :overflow-y="true" class="body">
				<scroll-view class="tree" :overflow-y="true">
					<DaTreeVue2
					  ref="DaTreeRef"
					  :data="treeData"
					  labelField="category_name"
					  valueField="category_id"
					  childrenField="child_list"
					  :themeColors="'var(--primary-color)'"
					  expandChecked
					  showCheckbox
					  :defaultCheckedKeys="defaultCheckedKeysValue"
					  @change="handleTreeChange"></DaTreeVue2>
				  </scroll-view>
			</view>
			<view class="footer flex justify-end">
				<button type="default" class="confirm btn" @click="confirm">确认</button>
				<button type="default" class="btn" @click="$refs.couponCategoryPop.close()">取消</button>
			</view>
		</view>
	</unipopup>
</template>

<script>
	import DaTreeVue2 from '@/components/da-tree-vue2/index.vue'
	import unipopup from '@/components/uni-popup/uni-popup.vue';
	import index from './index.js';
	export default {
		components: {
			DaTreeVue2,
			unipopup,
		},
		mixins: [index]
	};
</script>
<style lang="scss" scoped>
	@import './index.scss';
</style>
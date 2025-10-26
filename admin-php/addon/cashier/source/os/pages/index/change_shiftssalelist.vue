<template>
	<base-page>
		<view class="goods-list">
			<view class="title-back flex items-center cursor-pointer" @click="backFn">
			        <text class="iconfont iconqianhou1"></text>
			        <text class="left">返回</text>
			        <text class="content">|</text>
			        <text>商品销售</text>
			    </view>
			<view class="screen-warp common-form">
				<view class="common-form-item">
					<view class="form-inline">
						<label class="form-label">销售渠道</label>
						<view class="form-input-inline">
							<select-lay :zindex="10" :value="option.sale_channel" name="sale_channel" placeholder="请选择销售渠道" :options="sale_channel_list" @selectitem="selectChannel"/>
						</view>
					</view>
					<view class="form-inline">
						<label class="form-label">规格名称</label>
						<view class="form-input-inline">
							<input type="text" v-model="option.sku_name" placeholder="请输入规格名称" class="form-input" />
						</view>
					</view>
					<view class="form-inline common-btn-wrap">
						<button type="default" class="screen-btn" @click="searchFn()">筛选</button>
						<button type="default" @click="resetFn()">重置</button>
						<button type="default" class="screen-btn" @click="exportSalelist()">导出</button>
					</view>
				</view>
			</view>
			<uniDataTable url="/cashier/storeapi/cashier/changeShiftsSaleGoodsList" :option="option" :cols="cols" ref="saleListTable">
			</uniDataTable>
		</view>
	</base-page>
</template>

<script>
	import uniDataTable from '@/components/uni-data-table/uni-data-table.vue';
	import saleList from './public/js/sale_list.js';
	export default {
		components: {
			uniDataTable
		},
		mixins: [saleList]
	}
</script>

<style scoped lang="scss">
	@import './../goods/public/css/goods_list.scss';
</style>

<style scoped>
	.common-btn-wrap button{
		margin-right: 0.1rem;
	}
</style>
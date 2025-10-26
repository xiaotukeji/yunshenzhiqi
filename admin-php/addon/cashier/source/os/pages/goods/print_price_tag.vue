<template>
	<base-page>
		<view class="stock-body">
			<view class="content-wrap" @click="goodsShow = false">
				<view class="title-back flex items-center cursor-pointer" @click="backFn">
					<text class="iconfont iconqianhou1"></text>
					<text class="left">返回</text>
					<text class="content">|</text>
					<text>打印价格标签</text>
				</view>
				<view class="batch-action" v-if="editPrintNum.show == false">
					<text class="batch-item" @click="openSelectGoodsDialog()">选择商品</text>
					<text class="batch-item" @click="batchDeleteGoods()">批量删除</text>
					<!-- <text class="batch-item" @click="editPrintNumShow()">批量设置打印份数</text> -->
				</view>
				<view class="screen-warp common-form" v-if="editPrintNum.show == true">
					<view class="common-form-item">
						<view class="form-inline">
							<view class="form-input-inline">
								<input type="digit" placeholder="请输入打印份数" class="form-input" v-model="editPrintNum.value"/>
							</view>
						</view>
						<view class="form-inline common-btn-wrap">
							<button type="default" class="screen-btn" @click="editPrintNumConfirm">确定</button>
							<button type="default" @click="editPrintNum.show = false">取消</button>
						</view>
					</view>
				</view>
				<view class="table-wrap">
					<view class="table-head">
						<view class="table-tr">
							<view class="table-th" >
								<text class="iconfont" :class="allSelected === true ? selectedIcon : (allSelected == 'harf' ?  harfselectedIcon : unselectedIcon)" @click="changeGoodsAllSelected()"></text>
							</view>
							<!-- <view class="table-th" style="flex: 0.5;">打印份数</view> -->
							<view class="table-th" style="flex: 3;">商品名称</view>
							<view class="table-th" style="flex: 1;">条码</view>
							<view class="table-th" style="flex: 1;">划线价</view>
							<view class="table-th" style="flex: 1;">售价</view>
							<view class="table-th" style="flex: 1;">单位</view>
							<view class="table-th" style="flex: 1;">重量</view>
						</view>
					</view>
					<view class="table-body">
						<block v-for="(item, index) in goodsList" :key="index">
							<view class="table-tr">
								<view class="table-td" >
									<text class="iconfont" :class="item.selected? selectedIcon : unselectedIcon" @click="changeGoodsSelected(index)"></text>
								</view>
								<!-- <view class="table-td" style="flex: 0.5;">{{ item.print_num }}</view> -->
								<view class="table-td goods-name" style="flex: 3;">{{ item.sku_name }}</view>
								<view class="table-td" style="flex: 1;">{{ item.sku_no }}</view>
								<view class="table-td" style="flex: 1;">{{ item.market_price }}</view>
								<view class="table-td" style="flex: 1;">{{ item.price }}</view>
								<view class="table-td" style="flex: 1;">{{ item.unit }}</view>
								<view class="table-td" style="flex: 1;">{{ item.weight }}</view>
							</view>
						</block>
						<view class="table-tr table-empty" v-if="!goodsList.length">暂无数据，请选择商品数据</view>
					</view>
				</view>
			</view>
			<view class="action-wrap">
				<view class="table-total">合计：共 {{ goodsList.length }} 种商品</view>
				<view class="btn-wrap">
					<button type="default" class="stockout-btn" @click="designFn" :loading="isSubmit">设计模板</button>
					<button type="default" class="stockout-btn" @click="printFn" :loading="isSubmit">打印</button>
					<button type="default" class="stockout-btn" @click="exportFn" :loading="isSubmit">导出</button>
				</view>
			</view>
		</view>
		<goods-sku-select v-model="dialogVisible" :params="dialogParams" apiType="sku" :goodsClass="[1,6]"  @selectGoods="selectGoodsComplete" />
	</base-page>
</template>

<script>
import printPriceTag from "./public/js/print_price_tag.js"
import goodsSkuSelect from '@/components/ns-goods-sku-select/ns-goods-sku-select.vue';
export default {
	components: {
		goodsSkuSelect
	},
	mixins: [printPriceTag]
};
</script>

<style lang="scss" scoped>
@import './public/css/print_price_tag.scss';
</style>
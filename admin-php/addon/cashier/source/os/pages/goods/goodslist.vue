<template>
	<base-page>
		<view class="goods-list">
			<view class="screen-warp common-form">
				<view class="common-form-item">
					<view class="form-inline">
						<label class="form-label">商品名称</label>
						<view class="form-input-inline">
							<input type="text" v-model="option.search_text" placeholder="请输入商品名称" class="form-input" />
						</view>
					</view>
					<view class="form-inline">
						<label class="form-label">商品编码</label>
						<view class="form-input-inline">
							<input type="text" v-model="option.sku_no" placeholder="请输入商品编码" class="form-input" />
						</view>
					</view>
					<view class="form-inline">
						<label class="form-label">商品类型</label>
						<view class="form-input-inline">
							<select-lay :zindex="10" :value="goods_class" name="goods_class" placeholder="请选择商品类型" :options="goodsClass" @selectitem="selectClass"/>
						</view>
					</view>
					<view class="form-inline">
						<label class="form-label">商品状态</label>
						<view class="form-input-inline">
							<select-lay :zindex="10" :value="status" name="status" placeholder="请选择商品状态" :options="statusList" @selectitem="selectStatus"/>
						</view>
					</view>
					<view class="form-inline">
						<label class="form-label">商品价格</label>
						<view class="form-input-inline input-append">
							<input type="text" v-model="option.start_price" placeholder="最低价格" class="form-input" />
							<view class="unit">元</view>
						</view>
						<view class="form-input-inline split-wrap">-</view>
						<view class="form-input-inline input-append">
							<input type="text" v-model="option.end_price" placeholder="最高价格" class="form-input" />
							<view class="unit">元</view>
						</view>
					</view>
					<view class="form-inline common-btn-wrap">
						<button type="default" class="screen-btn" @click="searchFn()">筛选</button>
						<button type="default" @click="resetFn()">重置</button>
						<button type="default" class="screen-btn" @click="printPriceTag()">打印价格标签</button>
						<button type="default" class="screen-btn" @click="synchronous()" v-if="syncWeighGoods">同步称重商品</button>
					</view>
				</view>
			</view>
			<uniDataTable url="/cashier/storeapi/goods/page" :option="option" :cols="cols" ref="goodsListTable">
				<template v-slot:action="dataTable">
					<view class="action-btn-wrap">
						<text class="action-item" @click="getDetail(dataTable.value.goods_id)">详情</text>
						<text class="action-item" v-if="dataTable.value.store_status == 0 || dataTable.value.store_status == null" @click="goodsStatus(dataTable.value.goods_id, 1)">上架</text>
						<text class="action-item" v-else @click="goodsStatus(dataTable.value.goods_id, 0)">下架</text>
						<text class="action-item" @click="goodsSku(dataTable.value.goods_id)" v-if="(globalStoreInfo.stock_type == 'store') || dataTable.value.is_unify_price != 1">价格库存</text>
						<text class="action-item" @click="isDeliveryRestrictions(dataTable.value.goods_id)" v-if="dataTable.value.goods_class == 1">
							限制起送
						</text>
						<text class="action-item" @click="recordopen(dataTable.value.goods_id)" v-if="dataTable.value.is_virtual != 1 && globalStoreInfo.stock_type == 'store'">库存记录</text>
						<text class="action-item" @click="stockTransform(dataTable.value.goods_id)" v-if="dataTable.value.stock_transform == 1 && globalStoreInfo.stock_type == 'store'">库存转换</text>
					</view>
				</template>
				<template v-slot:batchaction="dataTable">
					<text class="batch-item" @click="goodsStatus(dataTable, 1)">批量上架</text>
					<text class="batch-item" @click="goodsStatus(dataTable, 0)">批量下架</text>
				</template>
			</uniDataTable>

			<unipopup ref="goodsDetail" type="center" :pagesize="9">
				<view class="goods-detail-wrap">
					<view class="detail-head">
						商品详情
						<text class="iconfont iconguanbi1" @click="$refs.goodsDetail.close()"></text>
					</view>
					<view class="detail-body">
						<block v-if="goodsDetail">
							<view class="title">基本信息</view>
							<view class="information-box">
								<view class="box-left">
									<view class="information">
										<view>商品名称：</view>
										<view>{{ goodsDetail.goods_name }}</view>
									</view>
									<view class="information" v-if="goodsDetail.introduction">
										<view>促销语：</view>
										<view>{{ goodsDetail.introduction }}</view>
									</view>
									<view class="information">
										<view>商品类型：</view>
										<view>{{ goodsDetail.goods_class_name }}</view>
									</view>
									<view class="information" v-if="goodsDetail.brand_name">
										<view>商品品牌：</view>
										<view>{{ goodsDetail.brand_name }}</view>
									</view>
									<view class="information" v-if="goodsDetail.unit">
										<view>单位：</view>
										<view>{{ goodsDetail.unit }}</view>
									</view>
									<view class="information" v-if="goodsDetail.goods_class == 6">
										<view>计价方式：</view>
										<view>{{ goodsDetail.pricing_type == 'num' ? '计数' : '计重' }}</view>
									</view>
									<view class="information">
										<view>商品状态：</view>
										<view>
											{{ goodsDetail.store_status == 0 || goodsDetail.store_status == null ? '仓库中' : '已上架' }}
										</view>
									</view>
								</view>
							</view>
							<block v-if="goodsDetail.sku_list.length > 1">
								<view class="title title2">价格库存</view>
								<view class="table">
									<view class="table-th table-all">
										<view class="table-td" style="width:30%">商品规格</view>
										<block v-if="goodsDetail.is_unify_price">
											<view class="table-td" style="width:20%">销售价格</view>
										</block>
										<block v-else>
											<view class="table-td" style="width:15%">统一价格</view>
											<view class="table-td" style="width:15%">独立价格</view>
										</block>
										<view class="table-td" style="width:20%;">商品编码</view>
										<view class="table-td table-group" style="width:15%;">
											<text>商品库存</text>
											<text title="商品库存指下单扣减后的剩余库存" class="iconfont iconwenhao"></text>
										</view>
										<view class="table-td table-group" style="width:15%;">
											<text>实际库存</text>
											<text title="实际库存指实际发货后的剩余库存" class="iconfont iconwenhao"></text>
										</view>
									</view>
									<scroll-view class="table-tb" scroll-y="true">
										<view class="table-tr table-all" v-for="(item, index) in goodsDetail.sku_list" :key="index">
											<view class="table-td" style="width:30%">{{ item.spec_name }}</view>
											<block v-if="goodsDetail.is_unify_price">
												<view class="table-td" style="width:20%">￥{{ item.discount_price }}</view>
											</block>
											<block v-else>
												<view class="table-td" style="width:15%">￥{{ item.discount_price }}</view>
												<view class="table-td" style="width:15%">￥{{ item.store_price }}</view>
											</block>
											<view class="table-td" style="width:20%;">{{ item.sku_no }}</view>
											<view class="table-td" style="width:15%;">{{ item.stock }}</view>
											<view class="table-td" style="width:15%;">{{ item.real_stock }}</view>
										</view>
									</scroll-view>
								</view>
							</block>
							<block v-else>
								<view class="title title2">规格详情</view>
								<view class="table">
									<view class="single-specification">
										<view class="item">
											<view class="name">商品售价：</view>
											<view class="message" v-if="goodsDetail.is_unify_price == 1 || goodsDetail.sku_list[0].store_price == null">￥{{ goodsDetail.sku_list[0].discount_price }}</view>
											<view class="message" v-else>{{ goodsDetail.sku_list[0].store_price }}</view>
										</view>
										<view class="item">
											<view class="name">商品编码：</view>
											<view class="message">{{ goodsDetail.sku_list[0].sku_no ? goodsDetail.sku_list[0].sku_no : '无' }}</view>
										</view>
										<view class="item">
											<view class="name">商品库存：</view>
											<view class="message">{{ goodsDetail.sku_list[0].stock || 0 }}</view>
										</view>
										<view class="item">
											<view class="name">实际库存：</view>
											<view class="message">{{ goodsDetail.sku_list[0].real_stock || 0 }}</view>
										</view>
									</view>
								</view>
							</block>
						</block>
						<block v-else>
							<image class="cart-empty" src="@/static/cashier/cart_empty.png" mode="widthFix"/>
						</block>
					</view>
				</view>
			</unipopup>

			<unipopup ref="goodsSku" type="center">
				<view class="record-body">
					<ns-goods-sku :disabled="disabled" v-if="skuList.length&&goodsDetail" :isUnifyPrice="goodsDetail.is_unify_price" :skuList="skuList" @close="close('goodsSku')" />
				</view>
			</unipopup>

			<unipopup ref="record" type="center">
				<view class="record-body">
					<ns-goods-sku-stock-record @close="close('record')" :goodsId="goodsId"/>
				</view>
			</unipopup>
		</view>
		<ns-scale-goods ref="scaleGoods"/>
		<stock-transform ref="stockTransformRef" @saveStockTransform="saveStockTransform" />
	</base-page>
</template>

<script>
	import uniDataTable from '@/components/uni-data-table/uni-data-table.vue';
	import unipopup from '@/components/uni-popup/uni-popup.vue';
	import nsGoodsSkuStockRecord from '@/components/ns-goods-sku-stock-record/ns-goods-sku-stock-record.vue';
	import nsGoodsSku from '@/components/ns-goods-sku/ns-goods-sku.vue';
	import nsScaleGoods from '@/components/ns-scale-goods/ns-scale-goods.vue';
	import stockTransform from '@/components/stock-transform/stock-transform.vue';
	import goodsList from './public/js/goods_list.js';

	export default {
		components: {
			unipopup,
			nsGoodsSkuStockRecord,
			nsGoodsSku,
			uniDataTable,
			nsScaleGoods,
			stockTransform
		},
		mixins: [goodsList]
	};
</script>

<style scoped lang="scss">
	@import './public/css/goods_list.scss';
</style>
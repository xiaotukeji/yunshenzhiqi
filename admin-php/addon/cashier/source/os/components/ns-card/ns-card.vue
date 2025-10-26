<template>
	<view class="container">
		<view class="header-action common-wrap">
			<view class="header-action-left">
				<view :class="{ active: goodsType == 'oncecard' }" @click="switchGoodsType('oncecard')">限次卡</view>
				<view :class="{ active: goodsType == 'timecard' }" @click="switchGoodsType('timecard')">限时卡</view>
				<view :class="{ active: goodsType == 'commoncard' }" @click="switchGoodsType('commoncard')">通用卡</view>
			</view>
		</view>
		
		<view class="content">
			<scroll-view scroll-y="true" class="list-wrap" @scrolltolower="getOncecard()" v-show="goodsType == 'oncecard'">
				<view class="table-list" v-show="onceCardData.list.length > 0">
					<view class="table-item" :class="{'yes-stock': item.stock>0, 'item-mum-2': itemNum == 2, 'item-mum-3': itemNum == 3, 'item-mum-4': itemNum == 4, 'active': selectCardSkuId.indexOf(item.sku_id) > -1 }" v-for="(item, index) in onceCardData.list" :key="index" @click="goodsSelect(item)">	
						<view class="item-info">
							<view class="item-img">
								<image v-if="item.goods_image == '@/static/goods/goods.png'" src="@/static/goods/goods.png" mode="widthFix"/>
								<image v-else :src="$util.img(item.goods_image.split(',')[0], { size: 'small' })" @error="item.goods_image = '@/static/goods/goods.png'" mode="widthFix"/>
							</view>
							<view class="item-other flex-1">
								<view class="item-name">{{ item.goods_name }}</view>
								<view class="w-full self-end">
									<view class="item-money">
										<text class="util">￥</text>
										{{ item.discount_price | moneyFormat }}
									</view>
								</view>
							</view>
						</view>
						<view class="no-stock" v-if="item.stock <= 0">
							<image src="@/static/stock/stock_empty.png" mode="heightFix"/>
						</view>
					</view>
				</view>
				<view class="empty" v-if="isLoad && !onceCardData.list.length">
					<image src="@/static/goods/goods_empty.png" mode="widthFix"/>
					<view class="tips">暂无卡项</view>
				</view>
			</scroll-view>

			<scroll-view scroll-y="true" class="list-wrap" @scrolltolower="getTimecard()" v-show="goodsType == 'timecard'">
				<view class="table-list" v-show="timeCardData.list.length > 0">
					<view class="table-item"  :class="{'yes-stock': item.stock>0, 'item-mum-2': itemNum == 2, 'item-mum-3': itemNum == 3, 'item-mum-4': itemNum == 4,}" v-for="(item, index) in timeCardData.list" :key="index" @click="goodsSelect(item)">
						<view class="item-info">
							<view class="item-img">
								<image v-if="item.goods_image == '@/static/goods/goods.png'" src="@/static/goods/goods.png" mode="widthFix"/>
								<image v-else :src="$util.img(item.goods_image.split(',')[0], { size: 'small' })" @error="item.goods_image = '@/static/goods/goods.png'" mode="widthFix"/>
							</view>
							<view class="item-other flex-1">
								<view class="item-name">{{ item.goods_name }}</view>
								<view class="w-full self-end">
									<view class="item-money">
										<text class="util">￥</text>
										{{ item.discount_price | moneyFormat }}
									</view>
								</view>
							</view>
						</view>
						<view class="no-stock" v-if="item.stock <= 0">
							<image src="@/static/stock/stock_empty.png" mode="heightFix"/>
						</view>
					</view>
				</view>
				<view class="empty" v-if="!timeCardData.list.length">
					<image src="@/static/goods/goods_empty.png" mode="widthFix"/>
					<view class="tips">暂无卡项</view>
				</view>
			</scroll-view>

			<scroll-view scroll-y="true" class="list-wrap" @scrolltolower="getCommoncard()" v-show="goodsType == 'commoncard'">
				<view class="table-list" v-show="commonCardData.list.length > 0">
					<view class="table-item"  :class="{'yes-stock': item.stock>0, 'item-mum-2': itemNum == 2, 'item-mum-3': itemNum == 3, 'item-mum-4': itemNum == 4,}" v-for="(item, index) in commonCardData.list" :key="index" @click="goodsSelect(item)">
						<view class="item-info">
							<view class="item-img">
								<image v-if="item.goods_image == '@/static/goods/goods.png'" src="@/static/goods/goods.png" mode="widthFix"/>
								<image v-else :src="$util.img(item.goods_image.split(',')[0], { size: 'small' })" @error="item.goods_image = '@/static/goods/goods.png'" mode="widthFix"/>
							</view>
							<view class="item-other flex-1">
								<view class="item-name">{{ item.goods_name }}</view>
								<view class="w-full self-end">
									<view class="item-money">
										<text class="util">￥</text>
										{{ item.discount_price | moneyFormat }}
									</view>
								</view>
							</view>
						</view>
						<view class="no-stock" v-if="item.stock <= 0">
							<image src="@/static/stock/stock_empty.png" mode="heightFix"/>
						</view>
					</view>
				</view>
				<view class="empty" v-if="!commonCardData.list.length">
					<image src="@/static/goods/goods_empty.png" mode="widthFix"/>
					<view class="tips">暂无卡项</view>
				</view>
			</scroll-view>
		</view>
	</view>
</template>

<script>
	import index from './index.js';
	export default {
		mixins: [index]
	};
</script>

<style lang="scss" scoped>
	@import './index.scss';
</style>
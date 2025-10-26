<template>
	<view class="withdrawal safe-area">
		<view class="withdrawal_item">
			<view class="withdrawal_title">
				<view class="withdrawal_title_info">
					<text class="line color-base-bg margin-right"></text>
					<text>商品概况</text>
				</view>
				<picker :value="pickerCurr" @change="pickerChange" :range="picker" range-key="date_text">
					<view class="select color-tip">
						{{ picker[pickerCurr].date_text }}
						<text class="iconfont iconiconangledown"></text>
					</view>
				</picker>
			</view>
			<view class="withdrawal_content margin-top">
				<view class="flex_two">
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/transaction_detail', {field: 'goods_on_type_count', title: '在架商品数', curr: pickerCurr})">
						<view class="tip overhidden">在架商品数</view>
						<view class="num overhidden">{{ parseInt(statTotal.goods_on_type_count) }}</view>
					</view>
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/transaction_detail', {field: 'goods_visited_type_count', title: '被访问商品数', curr: pickerCurr})">
						<view class="tip overhidden">被访问商品数</view>
						<view class="num overhidden">{{ parseInt(statTotal.goods_visited_type_count) }}</view>
					</view>
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/transaction_detail', {field: 'goods_order_type_count', title: '动销商品数', curr: pickerCurr})">
						<view class="tip overhidden">动销商品数</view>
						<view class="num overhidden">{{ parseInt(statTotal.goods_order_type_count) }}</view>
					</view>
				</view>
			</view>
		</view>
		
		<view class="withdrawal_item">
			<view class="withdrawal_title">
				<view class="withdrawal_title_info">
					<text class="line color-base-bg margin-right"></text>
					<text>商品流量</text>
				</view>
			</view>
			<view class="withdrawal_content margin-top">
				<view class="flex_two">
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/transaction_detail', {field: 'goods_visit_count', title: '商品浏览量', curr: pickerCurr})">
						<view class="tip overhidden">商品浏览量</view>
						<view class="num overhidden">{{ parseInt(statTotal.goods_visit_count) }}</view>
					</view>
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/transaction_detail', {field: 'goods_visit_member_count', title: '商品访客数', curr: pickerCurr})">
						<view class="tip overhidden">商品访客数</view>
						<view class="num overhidden">{{ parseInt(statTotal.goods_visit_member_count) }}</view>
					</view>
				</view>
			</view>
		</view>
		
		<view class="withdrawal_item">
			<view class="withdrawal_title">
				<view class="withdrawal_title_info">
					<text class="line color-base-bg margin-right"></text>
					<text>商品转化</text>
				</view>
			</view>
			<view class="withdrawal_content margin-top">
				<view class="flex_two">
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/transaction_detail', {field: 'goods_cart_count', title: '加购件数', curr: pickerCurr})">
						<view class="tip overhidden">加购件数</view>
						<view class="num overhidden">{{ parseInt(statTotal.goods_cart_count) }}</view>
					</view>
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/transaction_detail', {field: 'goods_order_count', title: '下单件数', curr: pickerCurr})">
						<view class="tip overhidden">下单件数</view>
						<view class="num overhidden">{{ parseInt(statTotal.goods_order_count) }}</view>
					</view>
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/transaction_detail', {field: 'order_create_count', title: '支付件数', curr: pickerCurr})">
						<view class="tip overhidden">支付件数</view>
						<view class="num overhidden">{{ parseInt(statTotal.order_create_count) }}</view>
					</view>
				</view>
			</view>
		</view>
		
		<view class="withdrawal_item">
			<view class="withdrawal_title">
				<view class="withdrawal_title_info">
					<text class="line color-base-bg margin-right"></text>
					<text>销售额（元）TOP5</text>
				</view>
			</view>
			<view class="withdrawal_content margin-top">
				<view class="ranking-wrap">
					<view class="ranking-item">
						<view class="ranking"></view>
						<view class="goods-name">商品</view>
						<view class="sale">销售额（元）</view>
					</view>
					<view class="ranking-item" v-for="(item, index) in moneyRanking" :key="index">
						<view class="ranking"><view class="icon">{{ index + 1 }}</view></view>
						<view class="goods-name">{{ item.goods_name }}</view>
						<view class="sale color-base-text">{{ item.order_money }}</view>
					</view>
				</view>
			</view>
		</view>
		
		<view class="withdrawal_item">
			<view class="withdrawal_title">
				<view class="withdrawal_title_info">
					<text class="line color-base-bg margin-right"></text>
					<text>销量（件）TOP5</text>
				</view>
			</view>
			<view class="withdrawal_content margin-top">
				<view class="ranking-wrap">
					<view class="ranking-item">
						<view class="ranking"></view>
						<view class="goods-name">商品</view>
						<view class="sale">销售额（件）</view>
					</view>
					<view class="ranking-item" v-for="(item, index) in numRanking" :key="index">
						<view class="ranking"><view class="icon">{{ index + 1 }}</view></view>
						<view class="goods-name">{{ item.goods_name }}</view>
						<view class="sale color-base-text">{{ item.sale_num }}</view>
					</view>
				</view>
			</view>
		</view>
		
		<loading-cover ref="loadingCover"></loading-cover>
	</view>
</template>

<script>
import uCharts from '@/components/u-charts/u-charts.vue';
import goods from './js/goods.js';
import golbalConfig from '@/common/js/golbalConfig.js';
export default {
	components: {
		uCharts
	},
	mixins: [goods, golbalConfig]
};
</script>

<style lang="scss">
@import './css/overview.scss';
</style>

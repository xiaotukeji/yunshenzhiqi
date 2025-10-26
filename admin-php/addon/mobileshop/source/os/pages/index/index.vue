<template>
	<view>
		<view class="shop" :style="{ backgroundImage: 'url(' + $util.img('public/uniapp/shop_uniapp/shop_bg.png') + ')' }">
			<!-- 店铺基本信息 -->
			<view class="shop_base_info" @click="$util.redirectTo('/pages/my/shop/config')">
				<view class="shop_img">
					<image :src="data.shop_info.logo ? $util.img(data.shop_info.logo) : $util.img($util.getDefaultImage().default_headimg)" @error="imgError()" mode="aspectFit"/>
				</view>
				<view class="shop_info">
					<view class="shop_title">
						<text class="title">{{ data.shop_info.site_name }}</text>
					</view>
				</view>
				<text class="weixincode iconfont iconrichscan_icon" @click.stop="$util.redirectTo('/pages/verify/index')"></text>
			</view>
			<!-- 数据概况 -->
			<view class="trading_statistics margin_none">
				<view class="title">
					<view class="title_left">数据概况</view>
					<view class="title_right color-base-border">
						<text @click="transactionChange('stat_day')" :class="{ active: transaction_statistics == 'stat_day' }">今日</text>
						<text @click="transactionChange('stat_yesterday')" :class="{ active: transaction_statistics == 'stat_yesterday' }">昨日</text>
						<text @click="transactionChange('shop_stat_sum')" :class="{ active: transaction_statistics == 'shop_stat_sum' }">总计</text>
					</view>
				</view>
				<view class="content">
					<view>
						<view class="color-tip">订单数</view>
						<view class="num">{{ data[transaction_statistics].order_pay_count }}</view>
					</view>
					<view>
						<view class="color-tip">销售额（元）</view>
						<view class="num">{{ data[transaction_statistics].order_total }}</view>
					</view>
					<view>
						<view class="color-tip">{{ transaction_statistics == 'shop_stat_sum' ? '会员数' : '新增会员数' }}</view>
						<view class="num">{{ data[transaction_statistics].member_count }}</view>
					</view>
					<view>
						<view class="color-tip">浏览量</view>
						<view class="num">{{ data[transaction_statistics].visit_count }}</view>
					</view>
				</view>
			</view>
			<!-- 公告 -->
			<view class="notice" v-if="data.notice_list.length > 0">
				<text class="iconfont icongonggao color-base-text font-size-sub"></text>
				<swiper class="swiper" autoplay="true" vertical="true">
					<swiper-item v-for="(item, index) in data.notice_list" :key="index" class="swiperitem" @click="toNoticeDetail(item.id)">
						<view class="title">{{ item.title }}</view>
						<!-- <view class="time">{{ $util.timeStampTurnTime(item.create_time, 1) }}</view> -->
					</swiper-item>
				</swiper>
				<view class="more color-base-text" @click="$util.redirectTo('/pages/notice/list')">更多</view>
			</view>
			<!-- 待处理事项 -->
			<view class="trading_statistics padding">
				<view class="grid margin-top order">
					<uni-grid :column="5" :showBorder="!1">
						<uni-grid-item>
							<view @click="pendingLink('/pages/order/list', 'order_id', 0)" class="grid_item">
								<image class="image" :src="$util.img('public/uniapp/shop_uniapp/index/wating_pay.png')" mode="aspectFit" />
								<text class="num" v-if="data.num_data.waitpay > 0">{{ data.num_data.waitpay }}</text>
								<view class="text">待支付</view>
							</view>
						</uni-grid-item>
						<uni-grid-item>
							<view @click="pendingLink('/pages/order/list', 'order_id', 1)" class="grid_item">
								<image class="image" :src="$util.img('public/uniapp/shop_uniapp/index/wating_send.png')" mode="aspectFit" />
								<text class="num" v-if="data.num_data.waitsend > 0">{{ data.num_data.waitsend }}</text>
								<view class="text">待发货</view>
							</view>
						</uni-grid-item>
						<uni-grid-item>
							<view @click="pendingLink('/pages/order/list', 'order_id', 'refunding')" class="grid_item">
								<image class="image" :src="$util.img('public/uniapp/shop_uniapp/index/return_money.png')" mode="aspectFit" />
								<text class="num" v-if="data.num_data.refund > 0">{{ data.num_data.refund }}</text>
								<view class="text">退款中</view>
							</view>
						</uni-grid-item>
						<uni-grid-item>
							<view @click="pendingLink('/pages/goods/list', 'status', '-1')" class="grid_item">
								<image class="image" :src="$util.img('public/uniapp/shop_uniapp/index/stock_warn.png')" mode="aspectFit" />
								<view class="text">全部商品</view>
							</view>
						</uni-grid-item>
						<uni-grid-item>
							<view @click="pendingLink('/pages/goods/list', 'status', '2')" class="grid_item">
								<image class="image" :src="$util.img('public/uniapp/shop_uniapp/index/xiajia.png')" mode="aspectFit" />
								<text class="num" v-if="data.num_data.goods_stock_alarm > 0">{{ data.num_data.goods_stock_alarm }}</text>
								<view class="text">预警商品</view>
							</view>
						</uni-grid-item>
					</uni-grid>
				</view>
			</view>
			<!-- 常用功能 -->
			<view class="trading_statistics padding" v-if="handleMenu.length">
				<view class="grid">
					<uni-grid :column="4" :showBorder="!1">
						<uni-grid-item v-for="(item, index) in handleMenu">
							<view @click="$util.redirectTo(item.page)" class="grid_item">
								<image class="image50" :src="$util.img(item.img)" mode="aspectFit" />
								<view class="text">{{ item.title }}</view>
							</view>
						</uni-grid-item>
						<uni-grid-item>
							<view @click="$util.redirectTo('/pages/index/all_menu')" class="grid_item">
								<image class="image50" :src="$util.img('public/uniapp/shop_uniapp/index/more.png')" mode="aspectFit" />
								<view class="text">全部</view>
							</view>
						</uni-grid-item>
					</uni-grid>
				</view>
			</view>
			<!-- 常用功能 -->
			<block v-for="(item, index) in arr" :key="index">
				<view class="trading_statistics padding">
					<view class="title">
						<view class="title_left">
							{{ item.title }}
							<text v-if="item.opts.unit">({{ item.opts.unit }})</text>
							<text class=" total color-base-text">{{ total_money[item.id] }}</text>
						</view>
						<picker :value="pickerCurr[item.id]" @change="pickerChange(item.id, $event)" :range="picker" range-key="date_text">
							<view class="select ">
								{{ picker[pickerCurr[item.id]].date_text }}
								<text class="iconfont iconiconangledown"></text>
							</view>
						</picker>
					</view>
					<view class="ucharts">
						<view @click="refCurr = item.id">
							<uCharts
								:scroll="item.opts.enableScroll"
								:show="canvas"
								:canvasId="item.id"
								:chartType="item.chartType"
								:extraType="item.extraType"
								:cWidth="cWidth"
								:cHeight="cHeight"
								:opts="item.opts"
								:ref="item.id"
							/>
						</view>
					</view>
				</view>
			</block>
		</view>

		<loading-cover ref="loadingCover"></loading-cover>
	</view>
</template>

<script>
import uniGrid from '@/components/uni-grid/uni-grid.vue';
import uniGridItem from '@/components/uni-grid-item/uni-grid-item.vue';
import uCharts from '@/components/u-charts/u-charts.vue';
import index from './js/index.js';
export default {
	mixins: [index],
	components: {
		uniGrid,
		uniGridItem,
		uCharts
	}
};
</script>

<style lang="scss">
@import './css/index.scss';
</style>

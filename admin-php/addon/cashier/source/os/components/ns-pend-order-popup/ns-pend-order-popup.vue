<template>
	<view class="container pend-order">

		<uni-popup ref="pendOrderPop">
			<view class="pop-box">
				<view class="pop-header">
					<view class="pop-header-text">挂/取单</view>
					<view class="pop-header-close" @click="$refs.pendOrderPop.close()">
						<text class="iconguanbi1 iconfont"></text>
					</view>
				</view>
				<scroll-view scroll-y="true" @scrolltolower="getOrder" class="pend-order-scroll-view-wrap" :style="{height : height}">
				<view class="table-list" v-if="orderData.list.length">
					<block v-for="(item, index) in orderData.list" :key="index">
						<view class="table-item" v-show="item.order_id != orderId">
							<view class="table-header">
								<view class="table-header-info">
									<text>订单总价：</text>
									<text class="color">￥{{ item.order_money | moneyFormat }}</text>
								</view>
								<view class="table-header-time">
									<text>挂单时间：{{ item.create_time | timeFormat }}</text>
									<block v-if="item.member_id">
										<text class="line">|</text>
										<text>会员：{{ item.nickname }}</text>
									</block>
								</view>
							</view>

							<view class="table-content">
								<view class="table-content-item" v-for="(goods, gindex) in item.order_goods" :key="gindex">
									<view class="content-item-left">
										<view class="content-item-img">
											<image v-if="goods.goods_class == 'money'" src="@/static/goods/goods.png"/>
											<image v-else-if="goods.goods_image == '@/static/goods/goods.png'" src="@/static/goods/goods.png"/>
											<image v-else :src="$util.img(goods.goods_image, { size: 'small' })" @error="goods.goods_image = '@/static/goods/goods.png'"/>
										</view>
										<view class="content-item-info">
											<view class="content-item-name" v-if="goods.goods_class == 'money'">无码商品</view>
											<view class="content-item-name" v-else>
												<text>{{ goods.goods_name }}</text>
												<text>{{ goods.spec_name }}</text>
											</view>
											<view>￥{{ goods.price | moneyFormat }}</view>
										</view>
									</view>
									<view class="content-item-number">x {{ goods.num }}</view>
									<view class="content-item-price">￥{{ (goods.num * goods.price) | moneyFormat }}</view>
								</view>
							</view>
							<view class="remark-info" v-if="item.remark">备注：{{ item.remark }}</view>
							<view class="table-bottom">
								<button class="default-btn btn-left" @click="deleteOrder(item.order_id)">删除</button>
								<button class="default-btn btn-left" @click="remarkSetting(item, index)">备注</button>
								<button class="primary-btn btn-right" @click="takeOrder(item)">取单</button>
							</view>
						</view>
					</block>
				</view>
				<view class="empty" v-if="!orderData.list.length || (orderData.list.length == 1 && orderId)">
					<image src="@/static/goods/goods_empty.png" mode="widthFix"/>
					<view class="tips">暂无挂单记录</view>
				</view>
			</scroll-view>

			</view>
		</uni-popup>
		<uni-popup ref="remarkPopup" type="center">
			<view class="remark-wrap">
				<view class="header">
					<text class="title">备注</text>
					<text class="iconfont iconguanbi1" @click="$refs.remarkPopup.close()"></text>
				</view>
				<view class="body">
					<textarea v-model="remark" placeholder="填写备注信息" placeholder-class="placeholder-class" />
				</view>
				<view class="footer">
					<button type="default" class="primary-btn" @click="remarkConfirm">确认</button>
				</view>
			</view>
		</uni-popup>
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
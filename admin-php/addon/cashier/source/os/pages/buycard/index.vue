<template>
	<base-page>
		<view class="uni-flex uni-row page-height">
			<view class="common-wrap left-wrap">
				<view class="cashregister-header-box">
					<view class="order-time">
						<view class="title">消费时间</view>
						<uni-datetime-picker v-model="buyCardOrderData.create_time" type="datetime" :clearIcon="false" />
					</view>
					<view class="header" v-if="globalMemberInfo">
						<view class="headimg" @click="showMember">
							<image class="header-image" :src="globalMemberInfo.headimg ? $util.img(globalMemberInfo.headimg) : $util.img(defaultImg.head)" @error="globalMemberInfo.headimg = defaultImg.head"/>
							<view v-if="globalMemberInfo.member_level" class="member-nameplate">
								{{ globalMemberInfo.member_level_name }}
							</view>
						</view>

						<view class="head-info" @click="showMember">
							<view class="head-info-top">
								<view class="name">
									<block v-if="globalMemberInfo.mobile">
										<view class="mobile">{{ globalMemberInfo.mobile }}</view>
										<view class="text">
											<text>（</text>
											<text class="nickname">{{globalMemberInfo.nickname }}</text>
											<text>）</text>
										</view>
									</block>
									<text v-else>{{ globalMemberInfo.nickname }}</text>
								</view>
							</view>
							<view class="head-info-bottom point">积分：{{ globalMemberInfo.point }}</view>
							<view class="head-info-bottom balance">余额：{{ (parseFloat(globalMemberInfo.balance_money) + parseFloat(globalMemberInfo.balance)) | moneyFormat}}</view>
						</view>

						<button class="switch primary-btn member-open" @click="openMember()">更换会员</button>
						<button class="switch primary-btn replace-member" @click="replaceMember()">散客</button>
					</view>
					<view class="header" v-else>
						<view class="headimg">
							<image class="header-image" :src="$util.img(defaultImg.head)" />
						</view>
						<view class="head-info">
							<view class="name">散客</view>
						</view>
						<button class="switch primary-btn" @click="openMember()">查询会员</button>
					</view>
				</view>

				<view class="content">
					<!-- <view class="title">
						<view>结算清单（<text>{{ buyCardOrderData.goods_num }}</text>）</view>
						<view class="clear" @click="clearGoods">
							<text class="iconfont iconqingchushujuku"></text>
							<text>清空</text>
						</view>
					</view> -->

					<view class="content-list common-scrollbar">
						<block v-if="buyCardOrderData.goods_list.length && Object.keys(buyCardGoodsData).length">
							<view class="content-item" v-for="(item, index) in buyCardOrderData.goods_list" :key="index">
								<view class="item-img">
									<image :src="$util.img(item.goods_image.split(',')[0], { size: 'small' })" mode="widthFix"/>
								</view>
								<view class="uni-flex flex-1 info-wrap">
									<view class="info-top">
										<view class="uni-flex justify-between items-center">
											<view class="item-name">{{ item.sku_name }}</view>
											<view class="item-del" @click="deleteGoods(item)">
												<text class="iconfont iconshanchu"></text>
												<text>删除</text>
											</view>
										</view>
										<view class="item-spe" v-if="item.spec_name" arrow-down>{{ item.spec_name }}</view>
									</view>
									<view class="info-bottom flex justify-between items-center">
										<view class="item-price">￥{{ item.price | moneyFormat }}</view>
										<view class="item-num">
											<view class="num-dec" @click="dec(item)">-</view>
											<view class="num">{{ buyCardGoodsData['sku_' + item.sku_id].num }}</view>
											<view class="num-inc" @click="inc(item)">+</view>
										</view>
									</view>
								</view>
							</view>
						</block>
						<block v-else>
							<view class="empty">
								<image src="@/static/cashier/cart_empty.png" mode="widthFix"/>
								<view class="tips">点击右侧商品，选择商品进行结账</view>
							</view>
						</block>
					</view>
				</view>

				<view class="bottom">
					<view class="bottom-info">
						<view class="bottom-left">合计 <text>{{ buyCardOrderData.goods_num }}</text> 件</view>
						<text class="pay-money">￥{{ buyCardOrderData.pay_money | moneyFormat }}</text>
					</view>
					<view class="bottom-btn">
						<button class="primary-btn btn-right" :disabled="buyCardOrderData.goods_num == 0" @click="pay('')">结账</button>
					</view>
				</view>
				<view class="pay-shade" v-show="type == 'pay'"></view>
			</view>
			<view class="uni-flex uni-row" style="flex: 1;">

				<view class="list-wrap flex-1">

					<!-- 卡项商品 -->
					<view class="content" v-show="type == 'goods'">
						<ns-card :type="buyCardOrderData.card_type" ref="card"/>
					</view>

					<view class="content" v-show="type == 'pay'">
						<ns-payment ref="payment" storeRoute="buycard" @cancel="cancelPayment" @success="paySuccess" :outTradeNo="outTradeNo"/>
					</view>
				</view>
			</view>
		</view>

		<ns-select-member ref="selectMember"/>

		<!-- 会员详情弹出框 -->
		<ns-member-detail-popup ref="memberDetailPopup" />
	</base-page>
</template>

<script>
	import buycard from './public/js/buycard.js';
	import nsSelectMember from '@/components/ns-select-member/ns-select-member.vue';

	export default {
		components: {
			nsSelectMember
		},
		mixins: [buycard]
	};
</script>

<style lang="scss" scoped>
	@import './public/css/index.scss';
</style>
<style>
	.cashregister-header-box>>>.uni-select-lay-select {
		padding-right: 0.1rem !important;
	}

	.cashregister-header-box>>>.uni-select-lay-icon {
		display: none !important;
	}

	.cashregister-header-box>>>.uni-select-lay-input-close {
		display: none !important;
	}
</style>
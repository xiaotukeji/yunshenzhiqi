<template>
	<base-page>
		<view class="uni-flex uni-row page-height" @click="restoreFocus">
			<view class="common-wrap left-wrap">
				<view class="cashregister-header-box">
					<view class="order-time">
						<view class="title">消费时间</view>
						<uni-datetime-picker v-model="billingOrderData.create_time" type="datetime" :clearIcon="false" />
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
					<view class="content-list common-scrollbar">
						<block v-if="billingOrderData.goods_list.length && Object.keys(billingGoodsData).length">
							<view class="content-item settlement-select-focus" :class="{ 'focus bg-primary-color-9': leftIndexFocus == index }"
							      v-for="(item, index) in billingOrderData.goods_list" :key="item.editKey"
							      @click="callBox(item)" tabindex="0" :data-tab-index="index"
							      @blur="leftTabOrderSelectBlur(item)">
								<view class="item-img">
									<image v-if="item.goods_image == '@/static/goods/goods.png'" src="@/static/goods/goods.png" mode="widthFix"/>
									<image v-else :src="$util.img(item.goods_image.split(',')[0], { size: 'small' })" mode="widthFix" @error="item.goods_image = '@/static/goods/goods.png'"/>
								</view>
								<view class="uni-flex flex-1 info-wrap">
									<view class="info-top">
										<view class="uni-flex justify-between items-center">
											<view class="item-name">{{ item.goods_name }}</view>
											<view class="item-del" @click.stop="deleteGoods(item)">
												<text class="iconfont iconshanchu"></text>
												<text>删除</text>
											</view>
										</view>
										<view class="item-spe" v-if="item.spec_name">已选：{{ item.spec_name }}</view>
									</view>
									<view class="info-bottom">
										<view class="uni-flex items-flex-end">
											<view class="item-price">￥{{ item.price | moneyFormat }}{{item.card_item_id > 0 ? '×'+item.num : ''}}</view>
											<view class="item-subtotal" v-if="item.goods_class != $util.goodsClassDict.service && item.card_item_id == 0">
												<view>
													<text class="unit">￥</text>
													<text>{{ item.goods_money | moneyFormat }}</text>
												</view>
											</view>
											<view class="item-num" v-if="item.goods_class != $util.goodsClassDict.service && item.card_item_id == 0 && item.pricing_type == 'num'">
												<view class="num-dec" @click.stop="dec(item)">-</view>
												<view class="num" v-if="item.card_item_id && billingGoodsData['sku_' + item.sku_id + '_item_' + item.card_item_id]">{{ billingGoodsData['sku_' + item.sku_id + '_item_' + item.card_item_id].num }}</view>
												<view class="num" v-else-if="billingGoodsData['sku_' + item.sku_id]">{{ billingGoodsData['sku_' + item.sku_id].num }}</view>
												<view class="num" v-else>{{ item.num }}</view>
												<view class="num-inc" @click.stop="inc(item)">+</view>
											</view>
											<view class="item-num weight" v-if="item.goods_class == $util.goodsClassDict.weigh && item.pricing_type == 'weight'">
												<view class="num" v-if="item.card_item_id && billingGoodsData['sku_' + item.sku_id + '_item_' + item.card_item_id]">
													{{ billingGoodsData['sku_' + item.sku_id + '_item_' + item.card_item_id].num }}
												</view>
												<view class="num" v-else-if="billingGoodsData['sku_' + item.sku_id]">{{ billingGoodsData['sku_' + item.sku_id].num }}</view>
												<view class="num" v-else>{{ item.num }}</view>
												<view>{{ item.unit }}</view>
											</view>
										</view>
										<view class="card-deduction" v-if="item.card_info && Object.keys(item.card_info).length">
											<text>使用卡项：{{ item.card_info.goods_name }}</text>
										</view>
									</view>

								</view>

							</view>
						</block>
						<view class="empty" v-else>
							<image src="@/static/cashier/cart_empty.png" mode="widthFix"/>
							<view class="tips">点击右侧商品，选择商品进行结账</view>
						</view>
					</view>
				</view>

				<view class="bottom">
					<view class="bottom-info">
						<view class="bottom-left">合计 {{ billingOrderData.goods_num.toFixed(2) }} 件</view>
						<view class="bottom-right">
							<text class="pay-money">￥{{ billingOrderData.pay_money | moneyFormat }}</text>
						</view>
					</view>
					<view class="bottom-btn justify-between">
						<view class="tag-parent">
							<button class="comp-btn" :class="type == 'order' ? 'primary-btn' : 'default-btn'" @click="hangingOrder">挂/取单</button>
							<text class="num-tag" v-if="pendOrderNum > 0">{{ pendOrderNum < 100 ? pendOrderNum : '99+' }}</text>
						</view>
						<button class="default-btn comp-btn" @click="wholeOrderCancel(true,true)">整单取消</button>
						<button class="primary-btn btn-right" :disabled="billingOrderData.goods_num == 0" @click="pay('')">结账</button>
					</view>
				</view>
				<view class="pay-shade" v-show="type == 'pay'"></view>
			</view>
			<view class="uni-flex uni-row" style="flex: 1;">
				<view class="list-wrap flex-1">
					<!-- 商品 -->
					<view class="content" v-show="type == 'goods'">
						<ns-goods @openCashBox="openCashBox" :indexFocus="rightIndexFocus" ref="goods" />
					</view>

					<view class="content payment-content common-wrap" v-show="type == 'pay'">
						<ns-payment ref="payment" storeRoute="billing" @cancel="cancelPayment" @success="paySuccess" :outTradeNo="outTradeNo"/>
					</view>
				</view>
			</view>
		</view>

		<!-- 选择会员 -->
		<ns-select-member ref="selectMember" />

		<!-- 会员详情弹出框 -->
		<ns-member-detail-popup ref="memberDetailPopup" isShowMemberCard/>

		<!-- 挂单弹出框 -->
		<ns-pend-order-popup ref="pendOrderPopup"/>

	</base-page>
</template>

<script>
	import billing from './public/js/billing.js';
	import nsSelectMember from '@/components/ns-select-member/ns-select-member.vue';
	import nsMemberDetail from '@/components/ns-member-detail/ns-member-detail.vue';
	export default {
		components: {
			nsSelectMember,
			nsMemberDetail
		},
		mixins: [billing]
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
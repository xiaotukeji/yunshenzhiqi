<template>
	<base-page>
		<view class="goodslist">
			<view class="goodslist-box">
				<view class="goodslist-left">
					<view class="goods-title">
						充值订单
						<text class="iconfont icongengduo1"></text>
					</view>

					<view class="goods-search">
						<view class="search">
							<text class="iconfont icon31sousuo"></text>
							<input type="text" v-model="search_text" @input="search" placeholder="搜索订单号/流水号/买家" />
						</view>
					</view>
					<block v-if="!one_judge && order_list.length > 0">
						<scroll-view :scroll-top="scrollTop" @scroll="scroll" scroll-y="true" class="goods-list-scroll" :show-scrollbar="false" @scrolltolower="getOrderList">
							<view class="item" @click="getOrderDetail(item.order_id, index)" v-for="(item, index) in order_list" :key="index" :class="index == selectGoodsKeys ? 'itemhover' : ''">
								<view class="title">
									<view>订单编号：{{ item.order_no }}</view>
									<view>充值订单</view>
								</view>
								<view class="total-money-num">
									<view class="member-info">
										<view>买家：</view>
										<view v-if="item.member_id">{{ item.nickname }}</view>
										<view v-else>散客</view>
									</view>
									<view class="box">
										<view>充值金额</view>
										<view>￥{{ item.face_value }}</view>
									</view>
									<view class="box">
										<view>实付金额</view>
										<view>￥{{ item.price }}</view>
									</view>
								</view>
							</view>
						</scroll-view>
					</block>
					<view class="notYet" v-else-if="!one_judge && order_list.length == 0">暂无数据</view>
				</view>
				<view class="goodslist-right">
					<view class="goods-title">订单详情</view>
					<view class="order-information" v-show="!one_judge">
						<block v-if="JSON.stringify(order_detail) != '{}'">
							<view class="order-status">充值订单</view>
							<view class="order-types">
								<view class="type type1">
									<view>订单编号：</view>
									<view>{{ order_detail.order_no }}</view>
								</view>
								<view class="type type1">
									<view>订单流水号：</view>
									<view>{{ order_detail.out_trade_no }}</view>
								</view>

								<view class="type type1">
									<view>买家：</view>
									<view v-if="order_detail.member_id">
										{{ order_detail.nickname }}
										<text class="look" @click="$util.redirectTo('/pages/member/list', { member_id: order_detail.member_id })">查看</text>
									</view>
									<view v-else>散客</view>
								</view>
								<view class="type type1">
									<view>实付金额：</view>
									<view>{{ order_detail.price }}</view>
								</view>
								<view class="type type1">
									<view>实付方式：</view>
									<view>{{ order_detail.pay_type_name }}</view>
								</view>
								<view class="type type1">
									<view>状态：</view>
									<view>{{ order_detail.status == 2 ? '已支付' : '未支付' }}</view>
								</view>
								<view class="type type1">
									<view>支付时间：</view>
									<view>{{ order_detail.pay_time > 0 ? $util.timeFormat(order_detail.pay_time) : '' }}
									</view>
								</view>
								<view class="type type1">
									<view>订单来源：</view>
									<view>{{ order_detail.order_from_name }}</view>
								</view>
							</view>

							<view class="other-information">
								<view class="title">其他信息</view>
								<view class="item-box">
									<view class="item">
										<view>套餐名称：</view>
										<view>{{ order_detail.recharge_name }}</view>
									</view>
									<view class="item">
										<view>充值面值：</view>
										<view>{{ order_detail.face_value }}</view>
									</view>
									<view class="item">
										<view>售价：</view>
										<view>{{ order_detail.buy_price }}</view>
									</view>
									<view class="item" v-if="order_detail.point > 0">
										<view>赠送积分：</view>
										<view>{{ order_detail.point }}</view>
									</view>
									<view class="item" v-if="order_detail.growth > 0">
										<view>赠送成长值：</view>
										<view>{{ order_detail.growth }}</view>
									</view>
								</view>
								<view class="goods-info" v-if="order_detail.coupon_list && order_detail.coupon_list['data'].length > 0">
									<view class="title">赠送优惠券</view>
									<view class="table">
										<view class="table-th table-all">
											<view class="table-td" style="width:25%">优惠券名称</view>
											<view class="table-td" style="width:15%">类型</view>
											<view class="table-td" style="width:35%">优惠金额</view>
											<view class="table-td" style="width:25%;justify-content: flex-end;">有效期</view>
										</view>
										<block v-for="(item, index) in order_detail.coupon_list['data']" :key="index">
											<view class="table-tr table-all">
												<view class="table-td" style="width:25%">{{ item.coupon_name }}</view>
												<view class="table-td" style="width:15%">{{ item.type == 'reward' ? '满减券' : '折扣券' }}</view>
												<view class="table-td" style="width:40%" v-if="item.type == 'reward'">满{{ item.at_least }}元减{{ item.money }}</view>
												<view class="table-td" style="width:35%" v-if="item.type == 'discount'">
													满{{ item.at_least }}元打{{ item.discount }}折
													<block v-if="item.discount_limit">（最多抵扣{{ item.discount_limit }}元）</block>
												</view>
												<view class="table-td uni-column" style="width:25%;text-align: right;align-items: flex-end;">
													<view v-if="item.end_time">{{ $util.timeFormat(item.end_time) }}</view>
													<view v-else>长期有效</view>
												</view>
											</view>
										</block>
									</view>
								</view>
							</view>
						</block>
						<block v-else>
							<image class="cart-empty" src="@/static/cashier/cart_empty.png" mode="widthFix" />
						</block>
					</view>
				</view>
			</view>
		</view>
	</base-page>
</template>

<script>
import unipopup from '@/components/uni-popup/uni-popup.vue';
import rechargeOrder from './public/js/recharge_order';

export default {
	components: {
		unipopup
	},
	mixins: [rechargeOrder]
};
</script>

<style scoped lang="scss">
@import './public/css/orderlist.scss';

.goodslist .goodslist-box .goodslist-right .order-information .goods-info {
	padding: 0.2rem 0;
}

/deep/ .goods-list-scroll {
	width: 100%;
	height: calc(100% - 1.71rem) !important;
}</style>

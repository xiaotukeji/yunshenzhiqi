<template>
	<base-page>
		<view class="uni-flex uni-row page-height recharge-wrap">
			<view class="common-wrap left-wrap">
				<view class="cashregister-header-box">
					<view class="order-time">
						<view class="title">消费时间</view>
						<uni-datetime-picker v-model="create_time" type="datetime" :clearIcon="false" />
					</view>

					<view class="header" v-if="globalMemberInfo">
						<view class="headimg">
							<image class="header-image" :src="globalMemberInfo.headimg ? $util.img(globalMemberInfo.headimg) : $util.img(defaultImg.head)" @error="globalMemberInfo.headimg = defaultImg.head"/>
							<view v-if="globalMemberInfo.member_level" class="member-nameplate">
								{{ globalMemberInfo.member_level_name }}
							</view>
						</view>
						<view class="head-info">
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
						<button class="switch primary-btn member-open">更换会员</button>
						<button class="switch primary-btn replace-member">散客</button>
					</view>
					<view class="header" v-else>
						<view class="headimg">
							<image class="header-image" :src="$util.img(defaultImg.head)" />
						</view>
						<view class="head-info">
							<view class="name">散客</view>
						</view>
						<button class="switch primary-btn">查询会员</button>
					</view>
				</view>

				<view class="content">
					<view class="content-list common-scrollbar">
						<view class="empty">
							<image src="@/static/cashier/cart_empty.png" mode="widthFix"/>
							<view class="tips">点击右侧商品，选择商品进行结账</view>
						</view>
					</view>
				</view>

				<view class="bottom">
					<view class="bottom-info">
						<view class="bottom-left">共 0 件</view>
						<view class="bottom-right">
							<text class="pay-money">￥0.00</text>
						</view>
					</view>
					<view class="bottom-btn">
						<button class="primary-btn btn-right">结账</button>
					</view>
				</view>
				<view class="pay-shade"></view>
			</view>
			<view class="uni-flex uni-row common-wrap right-wrap">
				<scroll-view scroll-y="true" class="info-wrap" v-show="type == 'member'">
					<view class="header">会员充值</view>
					<view class="headimg-content" v-if="globalMemberInfo">
						<view class="headimg">
							<image v-if="globalMemberInfo.headimg" :src="globalMemberInfo.headimg ? $util.img(globalMemberInfo.headimg) : $util.img(defaultImg.head)"/>
							<image v-else mode="aspectFill" src="@/static/member/head.png" />
						</view>
						<view class="header-info" @click="showMember">
							<view class="name">
								<text>{{ globalMemberInfo.nickname }}</text>
								<text v-if="globalMemberInfo.member_level" class="level-name">{{ globalMemberInfo.member_level_name }}</text>
								<button type="default" @click.stop="openMember()" class="primary-btn">切换会员</button>
							</view>
							<view class="header-info-item">
								<view>手机号：{{ globalMemberInfo.mobile || '--' }}</view>
							</view>
						</view>
					</view>
					<view class="headimg-content" v-else>
						<view class="headimg">
							<image mode="aspectFill" src="@/static/member/head.png" />
						</view>
						<view class="header-info">
							<view class="name">
								<text>散客</text>
								<button type="default" @click="openMember()" class="primary-btn">登录会员</button>
							</view>
							<view class="header-info-item">
								<view>手机号：--</view>
							</view>
						</view>
					</view>
					<view class="form-box">
						<view class="form-content">
							<view class="form-item">
								<view class="form-label">
									<text class="required"></text>
									充值方式：
								</view>
								<view class="form-inline">
									<uni-data-checkbox v-model="rechargeType" :localdata="rechargeTypeList" />
								</view>
							</view>

							<view class="form-item" v-if="rechargeType == 2">
								<view class="form-label">
									<text class="required">*</text>
									充值金额：
								</view>
								<view class="form-inline">
									<input type="number" class="form-input" v-model="rechargeMoney" />
								</view>
							</view>

							<view class="form-item" v-if="rechargeType == 1">
								<view class="form-label">
									<text class="required">*</text>
									充值金额：
								</view>
								<view class="form-inline ">
									<view class="label-list">
										<view class="form-label" :class="{ active: rechargeIndex == index }" @click="rechargeIndex = index" v-for="(item, index) in memberRecharge" :key="index">
											<view class="price">{{ item.buy_price }}元</view>
											<view class="balance">到账{{ item.face_value }}元</view>
										</view>
									</view>
								</view>
							</view>
							<view class="form-item" v-if="reward">
								<view class="form-label">
									<text class="required">*</text>
									充值优惠：
								</view>
								<view class="form-inline">
									<view class="content-box">
										<view class="content-gift" v-if="memberRecharge[rechargeIndex].growth">
											<text class="iconfont iconchengchangzhi"></text>
											<text>赠送{{ memberRecharge[rechargeIndex].growth }}成长值</text>
										</view>
										<view class="content-gift" v-if="memberRecharge[rechargeIndex].point">
											<text class="iconfont iconjifen"></text>
											<text>赠送{{ memberRecharge[rechargeIndex].point }}积分</text>
										</view>
										<view class="content-gift" v-if="memberRecharge[rechargeIndex].coupon_id">
											<text class="iconfont iconyouhuiquan1"></text>
											<text>赠送优惠券 X{{ memberRecharge[rechargeIndex].coupon_id.split(',').length }}</text>
										</view>
									</view>
								</view>
							</view>
							<view class="gift-remark" v-if="rechargeType == 1 && memberRecharge.length > 0 && memberRecharge[rechargeIndex] && memberRecharge[rechargeIndex].goods_data != undefined">
								备注：赠送的产品需线下提货，赠送的项目可在线下进行使用
							</view>

							<view class="action-btn">
								<button type="primary" class="primary-btn" @click="pay()">充值</button>
							</view>
						</view>
					</view>
				</scroll-view>

				<view class="info-wrap" v-show="type == 'pay'">
					<ns-payment ref="payment" storeRoute="recharge" @cancel="cancelPayment" @success="paySuccess" :outTradeNo="outTradeNo"/>
				</view>
			</view>
		</view>

		<ns-select-member ref="selectMember"/>

		<!-- 会员详情弹出框 -->
		<ns-member-detail-popup ref="memberDetailPopup" />

	</base-page>
</template>

<script>
	import recharge from './public/js/recharge.js';
	import nsSelectMember from '@/components/ns-select-member/ns-select-member.vue';

	export default {
		components: {
			nsSelectMember
		},
		mixins: [recharge],
	};
</script>
<style>
	.recharge-wrap .cashregister-header-box>>>.uni-select-lay-select {
		padding-right: 0.1rem !important;
	}

	.recharge-wrap .cashregister-header-box>>>.uni-select-lay-icon {
		display: none !important;
	}

	.recharge-wrap .cashregister-header-box>>>.uni-select-lay-input-close {
		display: none !important;
	}
	.recharge-wrap >>> .member-head .iconfont {
		display: none;
	}
</style>
<style lang="scss" scoped>
	@import './public/css/index.scss';
</style>
<template>
	<view class="member-detail-wrap">

		<uni-popup ref="memberPop">
			<view class="pop-box member-info-wrap" v-if="globalMemberInfo">

				<view class="pop-header">
					<view class="pop-header-text">会员详情</view>
					<view class="pop-header-close" @click="popClose('member')">
						<text class="iconguanbi1 iconfont"></text>
					</view>
				</view>

				<view class="member-content">
					<view class="content-block">
						<view class="item-img">
							<image mode="aspectFill" v-if="globalMemberInfo.headimg" :src="$util.img(globalMemberInfo.headimg)" @error="headError(globalMemberInfo)"/>
							<image mode="aspectFill" v-else :src="$util.img(defaultImg.head)"/>
						</view>
						<view class="item-content">
							<view class="item-title">
								<view class="item-title-text">{{ globalMemberInfo.nickname ? globalMemberInfo.nickname : '' }}</view>
								<view class="item-label" v-if="globalMemberInfo.member_level && globalMemberInfo.member_level_name">{{ globalMemberInfo.member_level_name }}</view>
							</view>
							<view class="info-list">
								<view class="info-item">手机：{{ globalMemberInfo.mobile ? globalMemberInfo.mobile : '' }}</view>
								<view class="info-item" v-if="globalMemberInfo.sex == 0">性别：未知</view>
								<view class="info-item" v-if="globalMemberInfo.sex == 1">性别：男</view>
								<view class="info-item" v-if="globalMemberInfo.sex == 2">性别：女</view>
								<view class="info-item">生日：{{ globalMemberInfo.birthday }}</view>
								<view class="info-item" v-if="globalMemberInfo.member_time">成为会员：{{ $util.timeFormat(globalMemberInfo.member_time,'Y-m-d') }}</view>
							</view>
						</view>
					</view>

					<view class="content-block account">
						<view class="content-data-item">
							<view class="data-item-title">积分</view>
							<view class="data-item-value">{{ globalMemberInfo.point ? parseInt(globalMemberInfo.point) : '0' }}</view>
						</view>
						<view class="content-data-item">
							<view class="data-item-title">储值余额(元)</view>
							<view class="data-item-value">{{ globalMemberInfo.balance ? globalMemberInfo.balance : '0.00' }}</view>
						</view>
						<view class="content-data-item">
							<view class="data-item-title">现金余额(元)</view>
							<view class="data-item-value">{{ globalMemberInfo.balance_money ? globalMemberInfo.balance_money : '0.00' }}</view>
						</view>
						<view class="content-data-item">
							<view class="data-item-title">成长值</view>
							<view class="data-item-value">{{ globalMemberInfo.growth ? globalMemberInfo.growth : '0' }}</view>
						</view>
						<view class="content-data-item">
							<view class="data-item-title">优惠券(张)</view>
							<view class="data-item-value">{{ globalMemberInfo.coupon_num ? globalMemberInfo.coupon_num : '0' }}</view>
						</view>
						<view class="content-data-item">
							<view class="data-item-title">卡包</view>
							<view class="data-item-value">{{ globalMemberInfo.card_num ? globalMemberInfo.card_num : '0' }}
							</view>
						</view>
					</view>
					<view class="content-block action">
						<view class="content-data-item" @click="memberAction('sendCoupon')">
							<view class="data-item-icon">
								<image mode="aspectFit" src="@/static/member/icon-member-coupon.png" />
							</view>
							<view class="data-item-value">送优惠券</view>
						</view>
						<view class="content-data-item" v-if="isShowMemberCard" @click="showMemberCard">
							<view class="data-item-icon">
								<image mode="aspectFit" src="@/static/member/icon-member-balance.png" />
							</view>
							<view class="data-item-value">会员卡项</view>
						</view>
						<view class="content-data-item" @click="memberAction('applyMember')" v-if="!globalMemberInfo.is_member">
							<view class="data-item-icon">
								<image mode="aspectFit" src="@/static/member/icon-member-apply.png" />
							</view>
							<view class="data-item-value">办理会员</view>
						</view>
					</view>
				</view>
			</view>
		</uni-popup>

		<!-- 发放优惠券 -->
		<uni-popup ref="sendCouponPop">
			<view class="pop-box sendCoupon-box">
				<view class="pop-header">
					<view class="pop-header-text">送优惠券</view>
					<view class="pop-header-close" @click="popClose('sendCoupon')">
						<text class="iconguanbi1 iconfont"></text>
					</view>
				</view>

				<view class="common-scrollbar sendCoupon-content">
					<view class="coupon-table-head">
						<view class="coupon-table-th">优惠券名称</view>
						<view class="coupon-table-th">金额</view>
						<view class="coupon-table-th">有效期</view>
						<view class="coupon-table-th">发放数量</view>
					</view>
					<scroll-view class="coupon-table-body" @scrolltolower="getCouponList()" scroll-y="true">
						<view class="coupon-table-tr" v-for="(item, index) in sendCoupon.list" :key="index">
							<view class="coupon-table-td">{{ item.coupon_name }}</view>
							<view class="coupon-table-td">{{ item.money }}</view>
							<view class="coupon-table-td">{{ item.validity_name }}</view>
							<view class="coupon-table-td">
								<view class="item-num">
									<view class="num-dec" v-on:click="dec(item)">-</view>
									<input class="table-input" type="text" v-model="item.num" />
									<view class="num-inc" v-on:click="inc(item)">+</view>
								</view>
							</view>
						</view>
						<view class="empty" v-if="!sendCoupon.list.length">
							<view class="iconfont iconwushuju"></view>
							<view>暂无数据</view>
						</view>
					</scroll-view>
				</view>
				<view class="pop-bottom">
					<button v-if="sendCoupon.list.length" class="primary-btn" @click="sendCouponFn">发放优惠券</button>
				</view>
			</view>
		</uni-popup>

		<!-- 办理会员 -->
		<uni-popup ref="applyMemberPop">
			<view class="pop-box applyMemberPop-box">
				<view class="pop-header">
					<view class="pop-header-text">办理会员</view>
					<view class="pop-header-close" @click="popClose('applyMember')">
						<text class="iconguanbi1 iconfont"></text>
					</view>
				</view>

				<view class="common-scrollbar pop-content">
					<view class="form-content">
						<view class="form-item">
							<view class="form-label">
								<text class="required"></text>
								会员等级：
							</view>
							<view class="form-inline">
								<select-lay :zindex="10" :value="applyMember.level_id" name="names" placeholder="请选择会员等级" :options="memberLevelList" @selectitem="selectMemberLevel"/>
							</view>
						</view>
						<view class="form-item">
							<view class="form-label">
								<text class="required"></text>
								会员卡号：
							</view>
							<view class="form-inline">
								<input class="form-input" type="text" placeholder="请输入会员卡号" v-model="applyMember.member_code" />
								<view class="word-aux">会员卡号为会员唯一编号，若不设置将会自动生成</view>
							</view>
						</view>
					</view>
				</view>
				<view class="pop-bottom">
					<button class="primary-btn" @click="saveApplyMember">确定</button>
				</view>
			</view>
		</uni-popup>

		<!-- 会员卡项弹出框 -->
		<ns-member-card-popup v-if="isShowMemberCard" ref="memberCardPopup"/>

	</view>
</template>

<script>
import dataTable from '@/components/uni-data-table/uni-data-table.vue';
import index from './index.js';
import UniPopup from "../uni-popup/uni-popup";

export default {
	components: {
		UniPopup,
		dataTable
	},
	props:{
		// 是否展示会员卡项
		isShowMemberCard:{
			type:Boolean,
			default:false
		}
	},
	mixins: [index],
};
</script>

<style lang="scss" scoped>
	@import './index.scss';
</style>
<style>
.member-info-pop >>> .pop-content, .member-info-pop  >>> .uni-scroll-view{
	overflow: inherit !important;
}
</style>
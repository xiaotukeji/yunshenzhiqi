<template>
	<view class="withdrawal safe-area">
		<view class="withdrawal_item">
			<view class="withdrawal_title">
				<view class="withdrawal_title_info">
					<text class="line color-base-bg margin-right"></text>
					<text>会员数据</text>
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
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/transaction_detail', {field: 'member_count', title: '新增会员数', curr: pickerCurr})">
						<view class="tip overhidden">新增会员数</view>
						<view class="num overhidden">{{ statTotal.member_count }}</view>
					</view>
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/transaction_detail', {field: 'order_member_count', title: '下单会员数', curr: pickerCurr})">
						<view class="tip overhidden">下单会员数</view>
						<view class="num overhidden">{{ statTotal.order_member_count }}</view>
					</view>
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/transaction_detail', {field: 'member_recharge_member_count', title: '储值会员数', curr: pickerCurr})">
						<view class="tip overhidden">储值会员数</view>
						<view class="num overhidden">{{ statTotal.member_recharge_member_count }}</view>
					</view>
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/transaction_detail', {field: 'member_level_count', title: '开卡会员数', curr: pickerCurr})">
						<view class="tip overhidden">开卡会员数</view>
						<view class="num overhidden">{{ statTotal.member_level_count }}</view>
					</view>
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/transaction_detail', {field: 'coupon_member_count', title: '领券会员数', curr: pickerCurr})">
						<view class="tip overhidden">领券会员数</view>
						<view class="num overhidden">{{ statTotal.coupon_member_count }}</view>
					</view>
				</view>
			</view>
		</view>
		<loading-cover ref="loadingCover"></loading-cover>
	</view>
</template>

<script>
import uCharts from '@/components/u-charts/u-charts.vue';
import member from './js/member.js';
import golbalConfig from '@/common/js/golbalConfig.js';
export default {
	components: {
		uCharts
	},
	mixins: [member, golbalConfig]
};
</script>

<style lang="scss">
@import './css/overview.scss';
</style>

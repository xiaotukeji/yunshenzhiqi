<template>
	<view class="withdrawal safe-area">
		<view class="withdrawal_item">
			<view class="withdrawal_title">
				<view class="withdrawal_title_info">
					<text class="line color-base-bg margin-right"></text>
					<text>交易概况</text>
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
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/transaction_detail', {field: 'expected_earnings_total_money', title: '预计收入', curr: pickerCurr})">
						<view class="tip overhidden">预计收入(元)</view>
						<view class="num overhidden">{{ statTotal.expected_earnings_total_money|moneyFormat }}</view>
					</view>
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/transaction_detail', {field: 'earnings_total_money', title: '营业收入', curr: pickerCurr})">
						<view class="tip overhidden">营业收入(元)</view>
						<view class="num overhidden">{{ statTotal.earnings_total_money|moneyFormat }}</view>
					</view>
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/transaction_detail', {field: 'expenditure_total_money', title: '营业支出', curr: pickerCurr})">
						<view class="tip overhidden">营业支出(元)</view>
						<view class="num overhidden">{{ statTotal.expenditure_total_money|moneyFormat }}</view>
					</view>
				</view>
			</view>
		</view>
		
		<view class="withdrawal_item">
			<view class="withdrawal_title">
				<view class="withdrawal_title_info">
					<text class="line color-base-bg margin-right"></text>
					<text>营业收入</text>
				</view>
				<view class="total color-base-text">￥{{ statTotal.earnings_total_money|moneyFormat }}</view>
			</view>
			<view class="withdrawal_content margin-top">
				<view class="flex_two">
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/transaction_detail', {field: 'order_pay_money', title: '商城订单', curr: pickerCurr})">
						<view class="tip overhidden">商城订单(元)</view>
						<view class="num overhidden">{{ statTotal.order_pay_money|moneyFormat }}</view>
					</view>
					<view class="flex_three-item overhidden" v-if="addonIsExit.cashier" @click="$util.redirectTo('/pages/statistics/transaction_detail', {field: 'cashier_order_pay_money', title: '收银订单', curr: pickerCurr})">
						<view class="tip overhidden">收银订单(元)</view>
						<view class="num overhidden">{{ statTotal.cashier_order_pay_money|moneyFormat }}</view>
					</view>
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/transaction_detail', {field: 'member_recharge_total_money', title: '会员充值', curr: pickerCurr})">
						<view class="tip overhidden">会员充值(元)</view>
						<view class="num overhidden">{{ statTotal.member_recharge_total_money|moneyFormat }}</view>
					</view>
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/transaction_detail', {field: 'member_level_total_money', title: '会员卡订单', curr: pickerCurr})">
						<view class="tip overhidden">会员卡订单(元)</view>
						<view class="num overhidden">{{ statTotal.member_level_total_money|moneyFormat }}</view>
					</view>
					<view class="flex_three-item overhidden" v-if="addonIsExit.giftcard" @click="$util.redirectTo('/pages/statistics/transaction_detail', {field: 'member_giftcard_total_money', title: '礼品卡订单', curr: pickerCurr})">
						<view class="tip overhidden">礼品卡订单(元)</view>
						<view class="num overhidden">{{ statTotal.member_giftcard_total_money|moneyFormat }}</view>
					</view>
				</view>
			</view>
		</view>
		
		<view class="withdrawal_item">
			<view class="withdrawal_title">
				<view class="withdrawal_title_info">
					<text class="line color-base-bg margin-right"></text>
					<text>营业支出</text>
				</view>
				<view class="total color-base-text">￥{{ statTotal.expenditure_total_money|moneyFormat }}</view>
			</view>
			<view class="withdrawal_content margin-top">
				<view class="flex_two">
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/transaction_detail', {field: 'refund_total', title: '订单退款', curr: pickerCurr})">
						<view class="tip overhidden">订单退款(元)</view>
						<view class="num overhidden">{{ statTotal.refund_total|moneyFormat }}</view>
					</view>
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/transaction_detail', {field: 'member_withdraw_total_money', title: '会员提现', curr: pickerCurr})">
						<view class="tip overhidden">会员提现(元)</view>
						<view class="num overhidden">{{ statTotal.member_withdraw_total_money|moneyFormat }}</view>
					</view>
				</view>
			</view>
		</view>
		<loading-cover ref="loadingCover"></loading-cover>
	</view>
</template>

<script>
import uCharts from '@/components/u-charts/u-charts.vue';
import transaction from './js/transaction.js';
import golbalConfig from '@/common/js/golbalConfig.js';
export default {
	components: {
		uCharts
	},
	mixins: [transaction, golbalConfig]
};
</script>

<style lang="scss">
@import './css/overview.scss';
</style>

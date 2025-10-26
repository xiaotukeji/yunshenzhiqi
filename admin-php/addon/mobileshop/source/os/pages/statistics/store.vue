<template>
	<view class="withdrawal safe-area">
		<view class="withdrawal_item">
			<view class="withdrawal_title">
				<view class="withdrawal_title_info">
					<text class="line color-base-bg margin-right"></text>
					<text>门店数据</text>
				</view>
				<picker :value="storeCurr" @change="storeChange" :range="storeList" range-key="store_name">
					<view class="select color-tip">
						{{ storeList[storeCurr].store_name }}
						<text class="pickericoniconangledown"></text>
					</view>
				</picker>
				<picker :value="pickerCurr" @change="pickerChange" :range="picker" range-key="date_text">
					<view class="select color-tip">
						{{ picker[pickerCurr].date_text }}
						<text class="pickericoniconangledown"></text>
					</view>
				</picker>
			</view>
			<view class="withdrawal_content margin-top">
				<view class="flex_two">
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/store_stat_detail', {field: 'expected_earnings_total_money', title: '预计收入', curr: pickerCurr, store_id: storeList[storeCurr].store_id })">
						<view class="tip overhidden">预计收入</view>
						<view class="num overhidden">{{ statTotal.expected_earnings_total_money|moneyFormat }}</view>
					</view>
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/store_stat_detail', {field: 'billing_money', title: '营业收入', curr: pickerCurr, store_id: storeList[storeCurr].store_id })">
						<view class="tip overhidden">开单金额数</view>
						<view class="num overhidden">{{ statTotal.billing_money|moneyFormat }}</view>
					</view>
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/store_stat_detail', {field: 'billing_count', title: '营业支出', curr: pickerCurr, store_id: storeList[storeCurr].store_id })">
						<view class="tip overhidden">开单数量</view>
						<view class="num overhidden">{{ statTotal.billing_count }}</view>
					</view>
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/store_stat_detail', {field: 'buycard_money', title: '营业支出', curr: pickerCurr, store_id: storeList[storeCurr].store_id })">
						<view class="tip overhidden">办卡金额数</view>
						<view class="num overhidden">{{ statTotal.buycard_money|moneyFormat }}</view>
					</view>
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/store_stat_detail', {field: 'buycard_count', title: '预计收入', curr: pickerCurr, store_id: storeList[storeCurr].store_id })">
						<view class="tip overhidden">办卡数</view>
						<view class="num overhidden">{{ statTotal.buycard_count }}</view>
					</view>
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/store_stat_detail', {field: 'recharge_money', title: '营业收入', curr: pickerCurr, store_id: storeList[storeCurr].store_id })">
						<view class="tip overhidden">会员充值金额</view>
						<view class="num overhidden">{{ statTotal.recharge_money|moneyFormat }}</view>
					</view>
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/store_stat_detail', {field: 'recharge_count', title: '营业支出', curr: pickerCurr, store_id: storeList[storeCurr].store_id })">
						<view class="tip overhidden">会员充值数量</view>
						<view class="num overhidden">{{ statTotal.recharge_count }}</view>
					</view>
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/store_stat_detail', {field: 'refund_money', title: '营业支出', curr: pickerCurr, store_id: storeList[storeCurr].store_id })">
						<view class="tip overhidden">会员退款金额</view>
						<view class="num overhidden">{{ statTotal.refund_money|moneyFormat }}</view>
					</view>
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/store_stat_detail', {field: 'refund_count', title: '营业收入', curr: pickerCurr, store_id: storeList[storeCurr].store_id })">
						<view class="tip overhidden">会员退款数量</view>
						<view class="num overhidden">{{ statTotal.refund_count }}</view>
					</view>
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/store_stat_detail', {field: 'order_member_count', title: '营业支出', curr: pickerCurr, store_id: storeList[storeCurr].store_id })">
						<view class="tip overhidden">门店下单会员数</view>
						<view class="num overhidden">{{ statTotal.order_member_count }}</view>
					</view>
					<view class="flex_three-item overhidden" @click="$util.redirectTo('/pages/statistics/store_stat_detail', {field: 'balance_money', title: '营业支出', curr: pickerCurr, store_id: storeList[storeCurr].store_id })">
						<view class="tip overhidden">会员余额消费金额</view>
						<view class="num overhidden">{{ statTotal.balance_money|moneyFormat }}</view>
					</view>
				</view>
			</view>
		</view>
		
		<loading-cover ref="loadingCover"></loading-cover>
	</view>
</template>

<script>
import uCharts from '@/components/u-charts/u-charts.vue';
import store from './js/store.js';
import golbalConfig from '@/common/js/golbalConfig.js';
export default {
	components: {
		uCharts
	},
	mixins: [store, golbalConfig]
};
</script>

<style lang="scss">
@import './css/overview.scss';
</style>

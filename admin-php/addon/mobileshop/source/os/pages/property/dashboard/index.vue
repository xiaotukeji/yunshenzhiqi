<template>
	<view class="withdrawal">
		<view class="withdrawal_item margin-top">
			<view class="withdrawal_title">
				<view class="withdrawal_title_info">
					<text class="line color-base-bg margin-right"></text>
					<text>财务概况</text>
				</view>
				<picker :value="pickerCurr" @change="pickerChange" :range="picker" range-key="date_text">
					<view class="select color-tip">
						{{ picker[pickerCurr].date_text }}
						<text class="iconfont iconiconangledown"></text>
					</view>
				</picker>
			</view>
			<view class="withdrawal_content">
				<view class="flex_two">
					<view class="flex_three-item">
						<view class="tip">预计收入(元)</view>
						<view class="num">{{ (parseFloat(dashboard.total_income) - parseFloat(dashboard.total_disburse)) | moneyFormat }}</view>
					</view>
					<view class="flex_three-item">
						<view class="tip">收入总额(元)</view>
						<view class="num">{{ dashboard.total_income | moneyFormat }}</view>
					</view>
					<view class="flex_three-item">
						<view class="tip">支出总额(元)</view>
						<view class="num">{{ dashboard.total_disburse | moneyFormat }}</view>
					</view>
				</view>
			</view>
		</view>

		<view class="withdrawal_item margin-top">
			<view class="withdrawal_title">
				<text class="line color-base-bg margin-right"></text>
				<text>收入概况</text>
			</view>
			<view class="formula-wrap">
				<text>收入总额</text>
				<text class="unit">=</text>
				<block v-for="(item, index) in dashboard.income_data" :key="index">
					<text class="unit" v-if="index">+</text>
					<text class="title">{{ item.title }}</text>
				</block>
			</view>
			<view class="withdrawal_content">
				<view class="flex_two">
					<view class="flex_three-item" v-for="(item, index) in dashboard.income_data" :key="index">
						<view class="tip">{{ item.title }}(元)</view>
						<view class="num">{{ item.value | moneyFormat }}</view>
					</view>
				</view>
			</view>
		</view>

		<view class="withdrawal_item margin-top">
			<view class="withdrawal_title">
				<text class="line color-base-bg margin-right"></text>
				<text>支出概况</text>
			</view>
			<view class="formula-wrap">
				<text>支出总额</text>
				<text class="unit">=</text>
				<block v-for="(item, index) in dashboard.disburse_data" :key="index">
					<text class="unit" v-if="index">+</text>
					<text class="title">{{ item.title }}</text>
				</block>
			</view>
			<view class="withdrawal_content">
				<view class="flex_two">
					<view class="flex_three-item" v-for="(item, index) in dashboard.disburse_data" :key="index">
						<view class="tip">{{ item.title }}(元)</view>
						<view class="num">{{ item.value | moneyFormat }}</view>
					</view>
				</view>
			</view>
		</view>

		<loading-cover ref="loadingCover"></loading-cover>
	</view>
</template>

<script>
import golbalConfig from '@/common/js/golbalConfig.js';
export default {
	mixins: [golbalConfig],
	data() {
		return {
			dashboard: {},
			picker: [
				{
					date_type: 0,
					date_text: '今日实时'
				},
				{
					date_type: -1,
					date_text: '昨日'
				},
				{
					date_type: 1,
					date_text: '近7天'
				},
				{
					date_type: 2,
					date_text: '近30天'
				}
			],
			pickerCurr: 0
		};
	},
	onShow() {
		if (!this.$util.checkToken('/pages/property/dashboard/index')) return;
		this.pickerChange({ detail: { value: this.pickerCurr } });
	},
	methods: {
		getBaseInfo(data = {}) {
			this.$api.sendRequest({
				url: '/shopapi/account/dashboard',
				data,
				success: res => {
					if (res.code >= 0) {
						this.dashboard = res.data;
					} else {
						this.$util.showToast({
							title: res.message
						});
					}
					if (this.$refs.loadingCover) this.$refs.loadingCover.hide();
				}
			});
		},
		pickerChange(e) {
			this.pickerCurr = e.detail.value;
			let data = {};
			switch (this.picker[this.pickerCurr].date_type) {
				case -1:
					data.start_time = this.getTime(-1).startTime;
					data.end_time = this.getTime(-1).endTime;
					break;
				case 1:
					data.start_time = this.getTime(-7).startTime;
					data.end_time = this.getTime(0).endTime;
					break;
				case 2:
					data.start_time = this.getTime(-30).startTime;
					data.end_time = this.getTime(0).endTime;
					break;
			}
			this.getBaseInfo(data);
		},
		/**
		 *获取几天前开始时间结束时间
		 * @param {Object} day
		 */
		getTime(day) {
			let date = new Date(new Date(new Date().toLocaleDateString()));
			date.setDate(date.getDate() + day);
			let startTime = parseInt(new Date(date).getTime() / 1000);
			let endTime = startTime + (24 * 60 * 60 - 1);
			return { startTime, endTime };
		}
	}
};
</script>

<style lang="scss">
@import '../css/common.scss';
</style>

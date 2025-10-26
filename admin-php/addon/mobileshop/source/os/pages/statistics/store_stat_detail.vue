<template>
	<view class="withdrawal">
		<view class="head-wrap">
			<view class="title">筛选日期</view>
			<picker :value="pickerCurr" @change="pickerChange" :range="picker" range-key="date_text">
				<view class="select color-tip">
					{{ picker[pickerCurr].date_text }}
					<text class="iconfont iconiconangledown"></text>
				</view>
			</picker>
		</view>
		<view class="chart-wrap">
			<uCharts :scroll="true" :show="true" :canvasId="field" chartType="area" extraType="curve" :cWidth="cWidth" :cHeight="cHeight" :opts="opts" ref="ucharts" />
		</view>
		<view class="list-wrap" v-if="opts.categories">
			<view class="list-item">
				<view class="title">{{ picker[pickerCurr].type == 'oneday' ? '时间' : '日期' }}</view>
				<view class="value">{{ title }}</view>
			</view>
			<view class="list-item" v-for="(item, index) in opts.categories" :key="index">
				<view class="title">{{ item }}</view>
				<view class="value">{{ opts.series[0].data[index] }}</view>
			</view>
		</view>
		<loading-cover ref="loadingCover"></loading-cover>
	</view>
</template>

<script>
import uCharts from '@/components/u-charts/u-charts.vue';

const uchartData = {
	yAxisdisabled: true,
	xAxisgridColor: 'transparent',
	yAxisgridType: 'dash',
	yAxisgridColor: '#eeeeee',
	animation: true,
	enableScroll: true,
	scrollColor: 'transparent',
	scrollBackgroundColor: 'transparent',
	scrollPosition: 'right',
	extra: {
		area: {
			addLine: true,
			opacity: 0.5,
			width: 2,
			type: 'curve'
		}
	},
	legend: false
};

export default {
	components: {
		uCharts
	},
	data() {
		return {
			picker: [
				{
					date_type: 0,
					date_text: '今日实时',
					type: 'oneday'
				},
				{
					date_type: -1,
					date_text: '昨日',
					type: 'oneday'
				},
				{
					date_type: 1,
					date_text: '近7天',
					type: 'manydays'
				},
				{
					date_type: 2,
					date_text: '近30天',
					type: 'manydays'
				}
			],
			pickerCurr: 0,
			statTotal: {},
			field: '',
			title: '',
			opts: {},
			cWidth: 0,
			cHeight: 0,
			storeId: 0
		};
	},
	onLoad(data) {
		this.pickerCurr = data.curr || 0;
		this.storeId = data.store_id || 0;
		this.field = data.field;
		this.title = data.title;
		uni.setNavigationBarTitle({ title: data.title });
		this.pickerChange({ detail: { value: this.pickerCurr } });

		this.cWidth = uni.upx2px(680);
		this.cHeight = uni.upx2px(500);
	},
	methods: {
		getStatData(data = {}) {
			this.$api.sendRequest({
				url: '/store/shopapi/stat/getstatdata',
				data: data,
				success: res => {
					if (res.code >= 0) {
						let data = {
							categories: res.data.time,
							series: [
								{
									name: this.title,
									data: res.data[this.field],
									color: '#FF6A00',
									time: res.data.time
								}
							]
						};
						this.opts = Object.assign(this.$util.deepClone(data), uchartData, { unit: '元' });
						this.$refs.ucharts.changeData(this.field, data);
					}
					if (this.$refs.loadingCover) this.$refs.loadingCover.hide();
				}
			});
		},
		getStatHourData(data = {}) {
			this.$api.sendRequest({
				url: '/store/shopapi/stat/getstathourdata',
				data: data,
				success: res => {
					if (res.code >= 0) {
						let data = {
							categories: res.data.time,
							series: [
								{
									name: this.title,
									data: res.data[this.field],
									color: '#FF6A00',
									time: res.data.time
								}
							]
						};
						this.opts = Object.assign(this.$util.deepClone(data), uchartData, { unit: '元' });
						this.$refs.ucharts.changeData(this.field, data);
					}
					if (this.$refs.loadingCover) this.$refs.loadingCover.hide();
				}
			});
		},
		pickerChange(e) {
			this.pickerCurr = e.detail.value;
			let data = {
				store_id: this.storeId
			};
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
			if (this.picker[this.pickerCurr].type == 'oneday') this.getStatHourData(data);
			else this.getStatData(data);
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
.head-wrap {
	display: flex;
	align-items: center;
	height: 90rpx;
	background-color: #fff;
	padding: 0 30rpx;
	margin-bottom: 20rpx;

	.title {
		flex: 1;
		width: 0;
	}
}
.chart-wrap {
	padding: 30rpx;
	background: #fff;
	margin-bottom: 20rpx;
}
.list-wrap {
	background: #fff;
	margin-bottom: 20rpx;
	padding: 30rpx;

	.list-item {
		display: flex;
		align-content: center;

		view {
			height: 70rpx;
			line-height: 70rpx;
			flex: 1;
			text-align: center;
			font-size: 26rpx;
		}

		&:nth-child(even) {
			background-color: rgba(255, 106, 0, 0.1);
		}

		&:first-child {
			background-color: #fff;
			height: 80rpx;
			line-height: 80rpx;
		}
	}
}
</style>

<template>
	<base-page>
		<view class="common-wrap">
			<view class="title">营业数据</view>
			<view class="choice-day">
				<view class="date-btn" :class="dateType == 'today' ? 'select' : ''" @click="switchDateType('today')" value="today">今日</view>
				<view class="date-btn" :class="dateType == 'yesterday' ? 'select' : ''" @click="switchDateType('yesterday')" value="yesterday">昨日</view>
				<view class="date-btn" :class="dateType == 'week' ? 'select' : ''" @click="switchDateType('week')" value="week">7日内</view>
				<view class="date-btn" :class="dateType == 'month' ? 'select' : ''" @click="switchDateType('month')" value="month">30日内</view>
				<view class="date-btn" :class="dateType == 'custom' ? 'select' : ''" @click="switchDateType('custom')" value="custom">自定义</view>
				<view class="report text-color">
					<text class="move iconfont iconicon-test"></text>
					<text></text>
				</view>
			</view>

			<template v-if="businessData">
				<view class="title-port">线下收银</view>
				<view class="money">
					<view class="estimate" :class="statType == 'expected_earnings_total_money' ? 'estimate-active' : ''">
						<view class="income">
							<text class="income-name">预计收入(元)</text>
							<!-- <uni-dropdown>
							<view class="action" slot="dropdown-link"><text class="iconfont iconbangzhu js-prompt-top"></text></view>
							<view slot="dropdown">
								<view class="dropdown-content-box">
									<view class="text">弹框展示内容</view>
									<view class="arrow"></view>
								</view>
							</view>
						</uni-dropdown> -->
						</view>
						<view class="num-money">
							<text class="last_income">{{ businessData.expected_earnings_total_money || 0.0 }}</text>
							<text class="detail" @click="switchStatType('expected_earnings_total_money')">查看详情</text>
						</view>
					</view>
					<view class="estimate" :class="statType == 'billing_money' ? 'estimate-active' : ''">
						<view class="income">
							<text class="income-name">开单金额数(元)</text>
							<!-- <uni-dropdown>
							<view class="action" slot="dropdown-link"><text class="iconfont iconbangzhu js-prompt-top"></text></view>
							<view slot="dropdown">
								<view class="dropdown-content-box">
									<view class="text">弹框展示内容</view>
									<view class="arrow"></view>
								</view>
							</view>
						</uni-dropdown> -->
						</view>
						<view class="num-money">
							<text class="last_income">{{ businessData.billing_money || 0.0 }}</text>
							<text class="detail" @click="switchStatType('billing_money')">查看详情</text>
						</view>
					</view>
					<view class="estimate" :class="statType == 'billing_count' ? 'estimate-active' : ''">
						<view class="income"><text class="income-name">开单数量</text></view>
						<view class="num-money">
							<text class="last_income">{{ businessData.billing_count || 0 }}</text>
							<text class="detail" @click="switchStatType('billing_count')">查看详情</text>
						</view>
					</view>
					<view class="estimate" :class="statType == 'buycard_money' ? 'estimate-active' : ''">
						<view class="income"><text class="income-name">办卡金额数(元)</text></view>
						<view class="num-money">
							<text class="last_income">{{ businessData.buycard_money || 0.0 }}</text>
							<text class="detail" @click="switchStatType('buycard_money')">查看详情</text>
						</view>
					</view>
					<view class="estimate" :class="statType == 'buycard_count' ? 'estimate-active' : ''">
						<view class="income"><text class="income-name">办卡数</text></view>
						<view class="num-money">
							<text class="last_income">{{ businessData.buycard_count || 0 }}</text>
							<text class="detail" @click="switchStatType('buycard_count')">查看详情</text>
						</view>
					</view>
					<view class="estimate" :class="statType == 'recharge_money' ? 'estimate-active' : ''">
						<view class="income"><text class="income-name">会员充值金额(元)</text></view>
						<view class="num-money">
							<text class="last_income">{{ businessData.recharge_money || 0.0 }}</text>
							<text class="detail" @click="switchStatType('recharge_money')">查看详情</text>
						</view>
					</view>
					<view class="estimate" :class="statType == 'recharge_count' ? 'estimate-active' : ''">
						<view class="income"><text class="income-name">会员充值数量</text></view>
						<view class="num-money">
							<text class="last_income">{{ businessData.recharge_count || 0 }}</text>
							<text class="detail" @click="switchStatType('recharge_count')">查看详情</text>
						</view>
					</view>
					<view class="estimate" :class="statType == 'refund_money' ? 'estimate-active' : ''">
						<view class="income"><text class="income-name">会员退款金额(元)</text></view>
						<view class="num-money">
							<text class="last_income">{{ businessData.refund_money || 0.0 }}</text>
							<text class="detail" @click="switchStatType('refund_money')">查看详情</text>
						</view>
					</view>
					<view class="estimate" :class="statType == 'refund_count' ? 'estimate-active' : ''">
						<view class="income"><text class="income-name">会员退款数量</text></view>
						<view class="num-money">
							<text class="last_income">{{ businessData.refund_count || 0 }}</text>
							<text class="detail" @click="switchStatType('refund_count')">查看详情</text>
						</view>
					</view>
					<view class="estimate" :class="statType == 'order_member_count' ? 'estimate-active' : ''">
						<view class="income"><text class="income-name">门店下单会员数</text></view>
						<view class="num-money">
							<text class="last_income">{{ businessData.order_member_count || 0 }}</text>
							<text class="detail" @click="switchStatType('order_member_count')">查看详情</text>
						</view>
					</view>
					<view class="estimate" :class="statType == 'balance_money' ? 'estimate-active' : ''">
						<view class="income"><text class="income-name">会员余额消费金额</text></view>
						<view class="num-money">
							<text class="last_income">{{ businessData.balance_money || 0.0 }}</text>
							<text class="detail" @click="switchStatType('balance_money')">查看详情</text>
						</view>
					</view>
				</view>
			</template>

			<template v-if="businessData">
				<view class="title-port">线上商城</view>
				<view class="money">
					<view class="estimate" :class="statType == 'online_pay_money' ? 'estimate-active' : ''">
						<view class="income"><text class="income-name">商城订单(元)</text></view>
						<view class="num-money">
							<text class="last_income">{{ businessData.online_pay_money || 0.0 }}</text>
							<text class="detail" @click="switchStatType('online_pay_money')">查看详情</text>
						</view>
					</view>
					<view class="estimate" :class="statType == 'online_refund_money' ? 'estimate-active' : ''">
						<view class="income"><text class="income-name">退款维权(元)</text></view>
						<view class="num-money">
							<text class="last_income">{{ businessData.online_refund_money || 0.0 }}</text>
							<text class="detail" @click="switchStatType('online_refund_money')">查看详情</text>
						</view>
					</view>
				</view>
			</template>

		</view>

		<uni-popup ref="customTime">
			<view class="pop-box">
				<view class="pop-header">
					<view class="pop-header-text">自定义时间选择</view>
					<view class="pop-header-close" @click="$refs.customTime.close()">
						<text class="iconguanbi1 iconfont"></text>
					</view>
				</view>
				<view class="pop-content ">
					<uni-datetime-picker v-model="timeObj.custom" @change="changeTime" :end="endDate" :clearIcon="false" type="datetimerange" rangeSeparator="至" />
				</view>
				<view class="pop-bottom"><button class="primary-btn" @click="getStatData()">确定</button></view>
			</view>
		</uni-popup>
		<uni-popup ref="chartsPop">
			<view class="pop-box charts-pop">
				<view class="pop-header">
					<view class="pop-header-text">运营数据图表展示</view>
					<view class="pop-header-close" @click="$refs.chartsPop.close()">
						<text class="iconguanbi1 iconfont"></text>
					</view>
				</view>
				<view class="pop-content">
					<qiun-data-charts type="line" :chartData="chartData" :eopts="{ seriesTemplate: { smooth: true } }" :ontouch="true" :opts="chartsOpts" />
				</view>
				<!-- <view class="pop-bottom"><button class="primary-btn" @click="getStatData()">确定</button></view> -->
			</view>
		</uni-popup>
	</base-page>
</template>

<script>
	import {
		getStatDay,
		getStatHour,
		getStatTotal
	} from '@/api/stat.js';

	export default {
		data() {
			return {
				statType: 'expected_earnings_total_money',
				statTypeArr: {
					expected_earnings_total_money: '预计收入',
					billing_money: '开单金额数',
					billing_count: '开单数量',
					buycard_money: '办卡金额数',
					buycard_count: '办卡数',
					recharge_money: '会员充值金额',
					recharge_count: '会员充值数量',
					refund_money: '会员退款金额',
					refund_count: '会员退款数量',
					order_member_count: '门店下单会员数',
					balance_money: '会员余额消费金额',
					online_pay_money: '商城订单',
					online_refund_money: '退款维权'
				},
				dateType: 'today',
				timeObj: {
					today: [],
					yesterday: [],
					week: [],
					month: [],
					custom: []
				},
				chartData: {
					categories: [],
					series: []
				},
				businessData: null,
				chartsOpts: {
					enableScroll: true,
					xAxis: {
						scrollShow: true,
						itemCount: 24,
						disableGrid: true
					}
				}
			};
		},
		onLoad() {
			this.setDate();
			this.getStatData();
		},
		onShow() {
			let date = new Date();
			var y = date.getFullYear();
			var m = date.getMonth() + 1;
			var d = date.getDate();
			this.endDate = y + '-' + m + '-' + d + ' 23:59:59';
		},
		methods: {
			// 重置图表数据
			resetChartData() {
				this.chartData.categories = [];
				this.chartData.series = [];
			},
			setDate() {
				let time = this.$util.timeTurnTimeStamp(this.$util.timeFormat(Date.now() / 1000, 'Y-m-d'));
				this.timeObj.today = [time, time + 86399];
				this.timeObj.yesterday = [time - 86400, time - 1];
				this.timeObj.week = [time - 604800, time];
				this.timeObj.month = [time - 2592000, time];
			},
			switchDateType(type) {
				this.dateType = type;
				if (type == 'custom') {
					this.$refs.customTime.open();
					return false;
				}

				if (type == 'month') this.chartsOpts.xAxis.itemCount = 10;
				else this.chartsOpts.xAxis.itemCount = 24;
				this.getStatData();
			},
			switchStatType(type) {
				this.statType = type;
				this.$refs.chartsPop.open();
				this.resetChartData();
				this.getStatData();
				setTimeout(() => {
					this.getChartData();
				}, 500);
			},
			changeTime(e) {
				this.timeObj.custom = e;
				this.chartsOpts.xAxis.itemCount = 10;
			},
			getStatData() {
				if (this.dateType == 'custom') {
					this.$refs.customTime.close();

					this.timeObj.custom[0] = this.$util.timeTurnTimeStamp(this.timeObj.custom[0]) *
						1000; // 解决自定义数据保存之后再次点击出现的数据错乱
					this.timeObj.custom[1] = this.$util.timeTurnTimeStamp(this.timeObj.custom[1]) *
						1000; // 解决自定义数据保存之后再次点击出现的数据错乱
				}
				this.getBusinessData();
			},
			getChartData() {
				let data = {};
				data.start_time = this.timeObj[this.dateType][0];
				let action = '';
				if (this.dateType == 'today' || this.dateType == 'yesterday') {
					action = getStatHour(data);
				} else {
					data.end_time = this.timeObj[this.dateType][1];
					action = getStatDay(data);
				}
				action.then(res => {
					if (res.code >= 0) {
						this.chartData.series = [];
						this.chartData.series.push({
							data: res.data[this.statType],
							name: this.statTypeArr[this.statType]
						});
						this.chartData.categories = res.data.time;
					}
				});
			},
			getBusinessData() {
				let data = {};
				data.start_time = this.dateType == 'custom' ? parseInt(this.timeObj[this.dateType][0] / 1000) : parseInt(this.timeObj[this.dateType][0]);
				data.end_time = this.dateType == 'custom' ? parseInt(this.timeObj[this.dateType][1] / 1000) : parseInt(this.timeObj[this.dateType][1]);
				getStatTotal(data).then(res => {
					if (res.code >= 0) {
						this.businessData = res.data;
					}
				});
			}
		}
	};
</script>

<style>
	.pop-content>>>.uni-icons {
		line-height: 0.32rem;
	}
</style>

<style lang="scss" scoped>
	.common-wrap {
		padding: 0.2rem 0.2rem 0.6rem;
		height: 100vh;
		box-sizing: border-box;
		overflow-y: auto;
	}

	.title {
		display: flex;
		margin-bottom: 0.2rem;
		font-size: 0.16rem;
		font-family: Source Han Sans CN;
		font-weight: bold;
		line-height: 0.2rem;
	}

	.title-port {
		margin-bottom: 0.2rem;
		margin-left: 0.1rem;
		font-size: 0.16rem;
		font-family: Source Han Sans CN;
		font-weight: bold;
		line-height: 0.2rem;
	}

	.choice-day {
		display: flex;
		margin-bottom: 0.2rem;
	}

	.choice-time {
		margin-top: 0.09rem;
		font-size: 0.12rem;
		font-family: Source Han Sans CN;
		font-weight: 400;
		line-height: 0.36rem;
	}

	.report {
		display: flex;
		justify-content: flex-end;
		margin-right: 0.2rem;
		font-size: 0.14rem;
		font-family: Source Han Sans CN;
		font-weight: 400;
		line-height: 0.36rem;
		cursor: pointer;
	}

	.move {
		margin-right: 0.06rem;
	}

	.money {
		display: flex;
		flex-wrap: wrap;
		margin-top: 0.2rem;

		.estimate {
			width: calc((100% - 0.85rem) / 5);
			margin: 0 0.08rem 0.2rem;
			padding: 0.2rem;
			background: #fff;
			border: 0.01rem solid #eee;
			border-radius: 0.02rem;
			cursor: pointer;
			position: relative;
			box-sizing: border-box;

			.income {
				display: flex;
				flex-direction: row;
				box-sizing: border-box;
				line-height: 0.2rem;

				.income-name {
					font-size: 0.16rem;
				}
			}

			.num-money {
				.last_income {
					display: block;
					margin: 0.15rem 0;
					font-size: 0.24rem;
					font-weight: 500;
					line-height: 0.2rem;
				}

				.detail {
					display: block;
					text-align: right;
					color: $primary-color;
					font-size: $uni-font-size-sm;
					position: relative;
					bottom: -0.05rem;
				}
			}
		}

		.estimate:last-child {
			margin-right: 0;
		}
	}

	.yesterday {
		display: flex;
		flex-wrap: wrap;
		font-size: 0.12rem;
	}

	.top-num {
		display: flex;
		margin-left: 0.05rem;
		font-size: 0.12rem;
		font-weight: 400;
	}

	.date-btn {
		height: 0.42rem;
		line-height: 0.42rem;
		font-size: 0.14rem;
		padding: 0 0.3rem;
		box-sizing: border-box;
		border: 0.01rem solid #d2d2d2;
		cursor: pointer;
		border-right: none;
		border-left: none;
		position: relative;
	}

	.date-btn:nth-child(6)::after {
		border-radius: 0 0.02rem 0.02rem 0;
	}

	.date-btn:first-child::after {
		border-radius: 0.02rem 0 0 0.02rem;
	}

	.date-btn:first-child {
		border-radius: 0.02rem 0 0 0.02rem;
	}

	.date-btn::after {
		content: '';
		position: absolute;
		top: -0.01rem;
		left: 0;
		bottom: -0.01rem;
		right: -0.01rem;
		border-right: 0.01rem solid #d2d2d2;
		border-left: 0.01rem solid #d2d2d2;
	}

	.select {
		color: #fff;
		background-color: $primary-color;
		border-color: $primary-color;
	}

	.select::after {
		z-index: 2;
		border-color: $primary-color;
	}

	.select:first-child {
		border-radius: 0.02rem 0 0 0.02rem;
	}

	.seleced:nth-child(6) {
		border-radius: 0 0.02rem 0.02rem 0;
	}

	.c-datepicker-picker {
		z-index: 99999999 !important;
	}

	.yesterday {
		display: none;
	}

	.dropdown-content-box {
		padding: 0.05rem 0;
		margin-top: 0.05rem;
		background-color: #fff;
		border: 0.01rem solid #ebeef5;
		border-radius: 0.04rem;
		box-shadow: 0 0.01rem 0.12rem 0 rgba(0, 0, 0, 0.1);
		position: relative;

		.arrow {
			position: absolute;
			top: -0.06rem;
			right: 0.06rem;
			width: 0;
			height: 0;
			border-left: 0.06rem solid transparent;
			border-right: 0.06rem solid transparent;
			border-bottom: 0.06rem solid #fff;
		}

		.text {
			display: flex;
			align-items: center;
			justify-content: center;
			margin: 0;
			padding: 0 0.1rem;
			transition: all 0.3s;
			font-size: 0.12rem;
			width: 1.5rem;
			box-sizing: border-box;
			text-align: left;
			line-height: 1.5;
		}
	}

	.js-prompt-top {
		color: #c8c9cc;
		font-size: 0.14rem;
		z-index: 999;
		margin-left: 0.05rem;
		cursor: pointer;
	}

	// pop弹框
	.pop-box {
		background: #ffffff;
		width: 6rem;
		height: 3.38rem;

		.pop-header {
			padding: 0 0.15rem 0 0.2rem;
			height: 0.5rem;
			line-height: 0.5rem;
			border-bottom: 0.01rem solid #f0f0f0;
			font-size: 0.14rem;
			color: #333;
			overflow: hidden;
			border-radius: 0.02rem 0.2rem 0 0;
			box-sizing: border-box;
			display: flex;
			justify-content: space-between;

			.pop-header-text {}

			.pop-header-close {
				cursor: pointer;

				text {
					font-size: 0.18rem;
				}
			}
		}

		.pop-content {
			height: calc(100% - 1.05rem);
			// overflow-y: scroll;
			padding: 0.2rem;
			box-sizing: border-box;
		}

		.pop-bottom {
			padding: 0.1rem 0.2rem;
			border-top: 0.01rem solid #eee;

			button {
				width: 100%;
				line-height: 0.35rem;
				height: 0.35rem;
			}
		}
	}

	.charts-pop {
		width: 9.5rem;
		height: 5.4rem;
		background-color: #fff;

		.pop-content {
			width: 100%;
			height: calc(100% - 1rem);
		}
	}
</style>
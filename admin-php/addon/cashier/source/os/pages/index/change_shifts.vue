<template>
	<page-meta :root-font-size="rootSize"></page-meta>
	<view class="uni-flex uni-row height-all" :style="themeColor">
		<view class="container common-wrap" style="-webkit-flex: 1;flex: 1;" v-if="shiftsData">
			<view class="title">{{ info.username }}</view>
			<view class="time-title">
				班次:
				<text>{{ shiftsData.start_time > 0 ? $util.timeFormat(shiftsData.start_time) : '初始化' }}</text>
				<text class="separate">-</text>
				<text class="curr-time">{{ shiftsData.end_time | timeFormat }}</text>
			</view>
			<view class="title-box">
				<view class="box">
					<view class="name-box">
						<text class="title-name">销</text>
						<text class="name">总销售</text>
					</view>
					<view class="money-box">
						<text class="money">（{{ shiftsData.total_sale | moneyFormat }}元{{ shiftsData.total_sale_count }}笔）</text>
					</view>
				</view>
				<view class="box">
					<view class="name-box">
						<text class="title-name">会</text>
						<text class="name">会员充值</text>
					</view>
					<view class="money-box">
						<text class="money">（{{ shiftsData.recharge_money | moneyFormat }}元{{ shiftsData.recharge_count }}笔）</text>
					</view>
				</view>
				<view class="box">
					<view class="name-box">
						<text class="title-name">应</text>
						<text class="name">应收金额</text>
					</view>
					<view class="money-box">
						<text class="money">（{{ shiftsData.total_money | moneyFormat }}元{{ shiftsData.total_count }}笔）</text>
					</view>
				</view>
				<view class="box">
					<view class="name-box">
						<text class="title-name">支</text>
						<text class="name">支付统计</text>
					</view>
					<view class="money-box">
						<text class="money">（{{ shiftsData.total_pay_money | moneyFormat }}元{{ shiftsData.total_pay_count }}笔）</text>
					</view>
				</view>
				<view class="box">
					<view class="name-box">
						<text class="title-name">商</text>
						<text class="name">商品销售</text>
					</view>
					<view class="money-box">
						<text class="money">（{{ shiftsData.sale_goods_count.class_num }}种{{ shiftsData.sale_goods_count.num }}件）</text>
					</view>
				</view>
			</view>

			<view class="basic">
				<text @click="detail()">
					查看详情
					<text class="iconqianhou2 iconfont"></text>
				</text>
			</view>

			<view class="common-btn-wrap">
				<button type="default" class="default-btn cancel-btn" @click="cancel">取消</button>
				<button type="default" class="primary-btn shiftss-btn" @click="changeShiftsFn">交班并登出</button>
			</view>

			<uni-popup ref="shiftslistPop">
				<view class="pop-box shiftsslistPop">
					<view class="pop-header">
						<view class="pop-header-text">交班详情</view>
						<view class="pop-header-close" @click="$refs.shiftslistPop.close()">
							<text class="iconguanbi1 iconfont"></text>
						</view>
					</view>
					<view class="pop-content common-scrollbar">
						<view class="pop-content-item">
							<view class="pop-content-text">总销售（{{ shiftsData.total_sale | moneyFormat }}元 {{ shiftsData.total_sale_count }}笔）</view>
							<view class="pop-contents-text">开单销售（{{ shiftsData.billing_money | moneyFormat }}元 {{ shiftsData.billing_count }}笔）</view>
							<view class="pop-contents-text">售卡销售（{{ shiftsData.buycard_money | moneyFormat }}元 {{ shiftsData.buycard_count }}笔）</view>
						</view>
						<view class="pop-content-item">
							<view class="pop-content-text">会员充值（{{ shiftsData.recharge_money | moneyFormat }}元 {{ shiftsData.recharge_count }}笔）</view>
						</view>
						<view class="pop-content-item">
							<view class="pop-content-text">应收金额（{{ shiftsData.total_money | moneyFormat }}元 {{ shiftsData.total_count }}笔）</view>
							<view class="pop-contents-text">开单销售（{{ shiftsData.billing_money | moneyFormat }}元 {{ shiftsData.billing_count }}笔）</view>
							<view class="pop-contents-text">售卡销售（{{ shiftsData.buycard_money | moneyFormat }}元 {{ shiftsData.buycard_count }}笔）</view>
							<view class="pop-contents-text">会员充值（{{ shiftsData.recharge_money | moneyFormat }}元 {{ shiftsData.recharge_count }}笔）</view>
							<view class="pop-contents-text">订单退款（{{ shiftsData.refund_money | moneyFormat }}元 {{ shiftsData.refund_count }}笔）</view>
						</view>
						<view class="pop-content-item">
							<view class="pop-content-text">支付统计（{{ shiftsData.total_pay_money | moneyFormat }}元 {{ shiftsData.total_pay_count }}笔）</view>
							<view class="pop-contents-text" v-if="shiftsData.cash > 0">现金收款（{{ shiftsData.cash | moneyFormat }}元 {{ shiftsData.cash_count }}笔）</view>
							<view class="pop-contents-text" v-if="shiftsData.wechatpay > 0">微信收款（{{ shiftsData.wechatpay | moneyFormat }}元 {{ shiftsData.wechatpay_count }}笔）</view>
							<view class="pop-contents-text" v-if="shiftsData.alipay > 0">支付宝收款（{{ shiftsData.alipay | moneyFormat }}元 {{ shiftsData.alipay_count }}笔）</view>
							<view class="pop-contents-text" v-if="shiftsData.own_wechatpay > 0">个人微信收款（{{ shiftsData.own_wechatpay | moneyFormat }}元 {{ shiftsData.own_wechatpay_count }}笔）</view>
							<view class="pop-contents-text" v-if="shiftsData.own_alipay > 0">个人支付宝收款（{{ shiftsData.own_alipay | moneyFormat }}元 {{ shiftsData.own_alipay_count }}笔）</view>
							<view class="pop-contents-text" v-if="shiftsData.own_pos > 0">个人POS收款（{{ shiftsData.own_pos | moneyFormat }}元 {{ shiftsData.own_pos_count }}笔）</view>
						</view>
						<view class="pop-content-item">
							<view class="pop-content-text">商品销售（{{ shiftsData.sale_goods_count.class_num }}种{{ shiftsData.sale_goods_count.num }}件）</view>
							<view class="pop-contents-text">线上销售（{{ shiftsData.sale_goods_count.online_class_num }}种 {{ shiftsData.sale_goods_count.online_num }}件）</view>
							<view class="pop-contents-text">线下销售（{{ shiftsData.sale_goods_count.offline_class_num }}种 {{ shiftsData.sale_goods_count.offline_num }}件）</view>
						</view>
					</view>
					<view class="pop-content-footer">
						<button class="primary-btn" @click="printTicketFn">打印小票</button>
					</view>
				</view>
			</uni-popup>
		</view>
		<ns-loading ref="loading"></ns-loading>
	</view>
</template>

<script>
import dataTable from '@/components/uni-data-table/uni-data-table.vue';
import uniPopup from '@/components/uni-popup/uni-popup.vue';
import { getShiftsData, changeShifts } from '@/api/shifts.js';
import { printTicket } from '@/api/printer.js';

export default {
	components: {
		dataTable,
		uniPopup
	},
	data() {
		return {
			shiftsData: null,
			info: null,
			isSub: false
		};
	},
	onShow() {
		this.loadThemeColor();
		this.getShiftsInfoFn();
	},
	methods: {
		detail() {
			this.$refs.shiftslistPop.open('center');
		},
		getShiftsInfoFn() {
			getShiftsData().then(res => {
				if (res.code == 0 && res.data) {
					let shiftsData = res.data.shifts_data;
					shiftsData.total_sale = parseFloat(shiftsData.billing_money) + parseFloat(shiftsData.buycard_money);
					shiftsData.total_sale_count = parseInt(shiftsData.billing_count) + parseInt(shiftsData.buycard_count);
					shiftsData.total_count = shiftsData.total_sale_count + parseInt(shiftsData.recharge_count) + parseInt(shiftsData.refund_count);
					shiftsData.total_money = shiftsData.total_sale + parseFloat(shiftsData.recharge_money) - parseFloat(shiftsData.refund_money);
					shiftsData.total_pay_money =
						parseFloat(shiftsData.cash) +
						parseFloat(shiftsData.alipay) +
						parseFloat(shiftsData.wechatpay) +
						parseFloat(shiftsData.own_wechatpay) +
						parseFloat(shiftsData.own_alipay) +
						parseFloat(shiftsData.own_pos);
					shiftsData.total_pay_count =
						parseInt(shiftsData.cash_count) +
						parseInt(shiftsData.alipay_count) +
						parseInt(shiftsData.wechatpay_count) +
						parseInt(shiftsData.own_wechatpay_count) +
						parseInt(shiftsData.own_alipay_count) +
						parseInt(shiftsData.own_pos_count);

					this.shiftsData = shiftsData;
					this.info = res.data.userinfo;

					this.$refs.loading.hide();
				} else {
					this.$util.showToast({
						title: res.message
					});
				}
			})
		},
		cancel() {
			uni.navigateBack();
		},
		// 交班
		changeShiftsFn() {
			if (this.isSub) return;
			this.isSub = true;

			uni.showLoading({
				title: ''
			});
			changeShifts().then(res => {
				uni.hideLoading();
				if (res.code == 0 && res.data) {
					uni.removeStorage({
						key: 'cashierToken',
						success: () => {
							this.$util.clearStoreData();
							this.$util.redirectTo('/pages/login/login', {}, 'reLaunch');
						}
					});
				} else {
					this.isSub = false;
					this.$util.showToast({
						title: res.message
					});
				}
			})
		},
		/**
		 * 打印小票
		 */
		printTicketFn() {
			printTicket().then(res => {
				if (res.code == 0) {
					if (Object.values(res.data).length) {
						let data = Object.values(res.data);
						try {
							let print = {
								printer: []
							};
							data.forEach((item) => {
								print.printer.push({
									printer_type: item.printer_info.printer_type,
									host: item.printer_info.host,
									ip: item.printer_info.ip,
									port: item.printer_info.port,
									content: item.content,
									print_width: item.printer_info.print_width
								})
							});
							this.$pos.send('Print', JSON.stringify(print));
						} catch (e) {
							console.log('err', e, res.data)
						}
					} else {
						this.$util.showToast({
							title: '未开启交接班小票打印'
						})
					}
				} else {
					this.$util.showToast({
						title: res.message ? res.message : '小票打印失败'
					})
				}
			})
		}
	}
}

/**
 * 打印回调
 * @param {Object} text
 */
window.POS_PRINT_CALLBACK = function (text) {
	uni.showToast({
		title: text,
		icon: 'none'
	})
}
</script>

<style lang="scss" scoped>
.height-all {
	height: 100vh;
}

.pop-box {
	background: #ffffff;
	width: 4rem;
	height: 60vh;
	display: flex;
	flex-direction: column;

	.pop-header {
		width: 100%;
		padding: 0 0.15rem 0 0.2rem;
		height: 0.5rem;
		// width: 3.5rem;
		margin: 0 auto;
		line-height: 0.5rem;
		border-bottom: 0.01rem solid #f0f0f0;
		font-size: 0.14rem;
		color: #333;
		overflow: hidden;
		border-radius: 0.02rem 0.2rem 0 0;
		box-sizing: border-box;
		display: flex;
		justify-content: space-between;

		.pop-header-text {
			font-weight: 900;
		}

		.pop-header-close {
			cursor: pointer;

			text {
				font-size: 0.18rem;
			}
		}
	}

	.pop-content {
		flex: 1;
		height: 0;
		padding: 0.1rem 0.2rem;
		box-sizing: border-box;
		font-weight: 900;
		overflow-y: scroll;
	}

	.pop-contents {
		margin-top: 0.3rem;
		width: 3rem;
		height: 0.8rem;
		padding: 0.1rem 0.2rem;
		box-sizing: border-box;
		font-weight: 900;
		display: flex;
		flex-direction: column;
		flex-wrap: wrap;
		justify-content: space-between;
	}

	.pop-content-item {
		margin-left: 0.3rem;
	}

	.pop-content-items {
		margin-left: 0.3rem;
	}

	.pop-content-text {
		padding: 0.1rem;
	}

	.pop-contents-text {
		margin-left: 0.4rem;
		font-weight: normal;
		padding: 0.1rem;
	}

	.pop-content-footer {
		display: flex;
		padding: .15rem;
		justify-content: flex-end;

		button {
			width: 1rem;
			margin: 0;
		}
	}
}

.container {
	display: flex;
	align-items: center;
	flex-direction: column;
	padding: 0.2rem;
}

.title {
	font-size: 0.16rem;
	margin-top: 0.45rem;
	font-weight: 900;
	color: rgb(86, 116, 133);
}

.time-title {
	padding: 0.1rem;
	line-height: 0.2rem;
	border-radius: 5px;
	background-color: var(--primary-color-light-8);
	color: $primary-color;
	font-size: 0.14rem;
	margin-top: 0.2rem;

	text {
		margin: 0 0.05rem;
	}

	.curr-time {
		font-weight: bold;
	}
}

.title-box {
	width: 5rem;
	display: flex;
	flex-direction: column;
	align-content: space-around;
	justify-content: flex-start;
	align-items: center;
	margin-top: 0.3rem;
}

.box {
	width: 5.4rem;
	height: 0.6rem;
	background: #f9fbfb;
	border: 1px solid rgb(225, 225, 225);
	margin-top: 0.1rem;
	display: flex;
	flex-direction: row;
	justify-content: space-between;
	align-content: space-around;
	flex-wrap: wrap;
	padding: 0 0.23rem 0 0.23rem;
	box-sizing: border-box;
}

.title-name {
	display: inline-block;
	width: 0.3rem;
	height: 0.3rem;
	border-radius: 15%;
	text-align: center;
	line-height: 0.3rem;
	background: $primary-color;
	color: #fff;
	font-weight: 900;
	font-size: 0.16rem;
}

.name-box {
	height: 0.3rem;
}

.money-box {
	line-height: 0.3rem;
}

.name {
	font-size: 0.16rem;
	line-height: 0.3rem;
	margin-left: 0.2rem;
	font-weight: 900;
	color: rgb(86, 116, 133);
}

.money {
	margin-left: 0.25rem;
	color: rgb(86, 116, 133);
}

.basic {
	text-align: center;
	margin-top: 0.33rem;

	text {
		height: 0.15rem;
		color: $primary-color;
		font-size: 0.14rem;
		cursor: pointer;
	}
}

.iconqianhou2 {
	margin-left: 0.05rem;
	font-size: 1px;
	color: $primary-color;
}

.cancel-btn {
	width: 1.7rem;
	height: 0.5rem;
	line-height: 0.5rem;
}

.shiftss-btn {
	width: 1.7rem;
	height: 0.5rem;
	line-height: 0.5rem;
	background-color: $primary-color;
	color: #fff !important;
	margin-left: 0.21rem;
}

.common-btn-wrap {
	margin-top: 1.14rem;
	z-index: 2;
	height: 0.6rem;
	padding-bottom: 0.05rem;
	display: flex;
	align-items: center;
}
</style>
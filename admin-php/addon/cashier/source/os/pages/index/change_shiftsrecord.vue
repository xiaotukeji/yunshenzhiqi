<template>
	<base-page>
		<view class="manage">
			<view class="screen-warp  common-form">
				<uni-datetime-picker v-model="screen.start_time" type="datetime" placeholder="请选择开始时间" :clearIcon="false" />
				<uni-datetime-picker v-model="screen.end_time" type="datetime" placeholder="请选择结束时间" :clearIcon="false" />
				<view>
					<select-lay :zindex="10" :value="screen.uid" name="names" placeholder="请选择员工" :options="userList" @selectitem="selectUser"/>
				</view>
				<view class="common-form-item">
					<view class="form-inline common-btn-wrap">
						<button class="screen-btn" @click="search">搜索</button>
						<button type="default" @click="reset()">重置</button>
					</view>
				</view>
			</view>
			<uni-data-table url="/cashier/storeapi/cashier/changeShiftsRecord" :option="screen" :cols="cols" ref="table">
				<template v-slot:action="data">
					<view class="common-table-action"><text @click="detail(data)">查看详情</text></view>
					<view class="common-table-action"><text @click="saleGoods(data)">商品销售</text></view>
				</template>
			</uni-data-table>
		</view>

		<uni-popup ref="shiftslistPop">
			<view class="pop-box shiftsslistPop">
				<view class="pop-header">
					<view class="pop-header-text">交班详情</view>
					<view class="pop-header-close" @click="$refs.shiftslistPop.close()">
						<text class="iconguanbi1 iconfont"></text>
					</view>
				</view>
				<view class="pop-content common-scrollbar" v-if="shiftsData">
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
						<view class="pop-content-text">商品销售（{{ shiftsData.sale_goods_count.class_num }}种 {{ shiftsData.sale_goods_count.num }}件）</view>
						<view class="pop-contents-text" >线上销售（{{ shiftsData.sale_goods_count.online_class_num }}种 {{ shiftsData.sale_goods_count.online_num }}件）</view>
						<view class="pop-contents-text" >线下销售（{{ shiftsData.sale_goods_count.offline_class_num }}种 {{ shiftsData.sale_goods_count.offline_num }}件）</view>
					</view>
				</view>
				<view class="pop-content-footer">
					<button class="primary-btn" @click="printTicketFn">打印小票</button>
				</view>
			</view>
		</uni-popup>
	</base-page>
</template>

<script>
import { printTicket } from '@/api/printer.js'
import { getUserList } from '@/api/user.js'

export default {
	data() {
		return {
			shiftsData: null,
			screen: {
				page: 1,
				start_time: '',
				end_time: '',
				uid: 0
			},
			userList: [],
			cols: [{
				width: 20,
				title: '员工',
				field: 'username',
				align: 'left'
			}, {
				width: 20,
				title: '开始时间',
				align: 'center',
				return: data => {
					return data.start_time ? this.$util.timeFormat(data.start_time) : '';
				}
			}, {
				width: 20,
				title: '结束时间',
				align: 'center',
				return: data => {
					return this.$util.timeFormat(data.end_time);
				}
			}, {
				width: 15,
				title: '总销售',
				align: 'right',
				return: data => {
					return this.$util.moneyFormat(parseFloat(data.billing_money) + parseFloat(data.buycard_money));
				}
			}, {
				width: 15,
				title: '会员充值',
				align: 'right',
				return: data => {
					return this.$util.moneyFormat(data.recharge_money);
				}
			}, {
				width: 15,
				title: '应收金额',
				align: 'right',
				return: data => {
					return this.$util.moneyFormat(
						parseFloat(data.billing_money) + parseFloat(data.buycard_money) + parseFloat(data.recharge_money) - parseFloat(data.refund_money)
					);
				}
			}, {
				width: 15,
				title: '支付统计',
				align: 'right',
				return: data => {
					return this.$util.moneyFormat(
						parseFloat(data.cash) +
						parseFloat(data.alipay) +
						parseFloat(data.wechatpay) +
						parseFloat(data.own_wechatpay) +
						parseFloat(data.own_alipay) +
						parseFloat(data.own_pos)
					);
				}
			}, {
				width: 16,
				title: '商品销售',
				align: 'right',
				return: data => {
					return data.sale_goods_count.class_num + '种 ' + data.sale_goods_count.num + '件';
				}
			}, {
				width: 15,
				title: '操作',
				action: true, // 表格操作列
				align: 'right'
			}]
		};
	},
	onLoad() {
		this.getUserListFn();
	},
	methods: {
		switchStoreAfter() {
			this.screen = {
				page: 1,
				start_time: '',
				end_time: '',
				uid: 0
			};
			this.$refs.table.load();
			this.getUserListFn();
		},
		saleGoods(data){
			this.$util.redirectTo('/pages/index/change_shiftssalelist',{ id : data.value.id });
		},
		detail(data) {
			let shiftsData = this.$util.deepClone(data.value);
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
			this.$refs.shiftslistPop.open('center');
		},
		getUserListFn() {
			let data = {
				page: 1,
				page_size: 0
			};
			getUserList(data).then(res => {
				if (res.code >= 0 && res.data.list.length != 0) {
					this.userList = res.data.list.map(item => {
						return {
							label: item.username,
							value: item.uid
						};
					});
				}
			})
		},
		reset() {
			this.screen = {
				page: 1,
				start_time: '',
				end_time: '',
				uid: 0
			};
			this.$refs.table.load(this.screen);
		},
		selectUser(index, item) {
			if (index >= 0) {
				this.screen.uid = parseInt(item.value);
			} else {
				this.screen.uid = 0;
			}
		},
		search() {
			this.$refs.table.load(this.screen);
		},
		/**
		 * 打印小票
		 */
		printTicketFn() {
			let data = {
				record_id: this.shiftsData.id
			};
			printTicket(data).then(res => {
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
								});
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
};

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
.manage {
	position: relative;
	background-color: #fff;
	padding: 0.15rem;
	height: 100vh;
	box-sizing: border-box;
}

// 筛选面板
.screen-warp {
	padding: 0.15rem;
	background-color: #f2f3f5;
	margin-bottom: 0.15rem;
	display: flex;
	justify-content: start;

	/deep/ .uni-date-x {
		height: 0.35rem;
	}

	/deep/ .uni-select-lay {
		background: #fff;

		.uni-select-lay-select {
			height: 0.37rem;
		}
	}

	.primary-btn {
		margin-left: 0;
	}

	&>* {
		margin-right: 0.15rem;
	}
	.common-btn-wrap{
		margin-left: 0;
	}
}

// pop弹框
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
		padding: 0.1rem 0.2rem;
		border-top: 0.01rem solid #e6e6e6;
		justify-content: center;
		button {
			width: 1rem;
			margin: 0;
		}
	}
}
</style>
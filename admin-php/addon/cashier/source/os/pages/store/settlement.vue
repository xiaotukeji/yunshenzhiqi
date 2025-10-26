<template>
	<base-page>
		<view class="page-height uni-flex uni-column">
			<view class="uni-flex uni-row">
				<view class="account-wrap">
					<view class="head-title">账户概览</view>
					<view class="account-item">
						<view @click="$util.redirectTo('/pages/store/acccount_record')">
							<view class="account-title">待结算金额（元）</view>
							<view class="money">
								{{ globalStoreInfo.account | moneyFormat }}
								<text class="iconfont iconqianhou2"></text>
							</view>
						</view>
						<view class="action">
							<button type="default" class="primary-btn" v-if="
									globalStoreInfo.is_settlement == 1 &&
										withdrawConfig.is_settlement == 1 &&
										withdrawConfig.period_type == 4 &&
										globalStoreInfo.account > 0 &&
										parseFloat(globalStoreInfo.account) >= parseFloat(withdrawConfig.withdraw_least)
								" @click="applyWithdraw">
								申请结算
							</button>
						</view>
					</view>
					<view class="uni-flex uni-row sub-bottom">
						<view class="account-item" @click="$util.redirectTo('/pages/store/settlement_record')">
							<view class="account-title">打款中金额（元）</view>
							<view class="money">
								{{ globalStoreInfo.account_apply | moneyFormat }}
								<text class="iconfont iconqianhou2"></text>
							</view>
						</view>
						<view class="account-item" @click="$util.redirectTo('/pages/store/settlement_record')">
							<view class="account-title">已打款金额（元）</view>
							<view class="money">
								{{ globalStoreInfo.account_withdraw | moneyFormat }}
								<text class="iconfont iconqianhou2"></text>
							</view>
						</view>
					</view>
				</view>
				<view class="info-wrap">
					<view class="head-title">结算账户</view>
					<view class="info-content">
						<block v-if="globalStoreInfo.is_settlement == 1 && withdrawConfig.is_settlement == 1">
							<view class="info-text">
								结算方式：
								<text>总部收款，</text>
								<text v-if="withdrawConfig.period_type == 1">每日自动结算</text>
								<text v-if="withdrawConfig.period_type == 2">每周自动结算</text>
								<text v-if="withdrawConfig.period_type == 3">每月自动结算</text>
								<text v-if="withdrawConfig.period_type == 4">门店申请结算</text>
							</view>
							<view class="info-text">账户类型：{{ bankType[globalStoreInfo.bank_type] }}</view>
							<block v-if="globalStoreInfo.bank_type == 1">
								<view class="info-text">微信名：{{ globalStoreInfo.bank_user_name }}</view>
							</block>
							<block v-if="globalStoreInfo.bank_type == 2">
								<view class="info-text">真实姓名：{{ globalStoreInfo.bank_user_name }}</view>
								<view class="info-text">支付宝账号：{{ globalStoreInfo.bank_type_account }}</view>
							</block>
							<block v-if="globalStoreInfo.bank_type == 3">
								<view class="info-text">开户行：{{ globalStoreInfo.bank_type_name }}</view>
								<view class="info-text">户头：{{ globalStoreInfo.bank_user_name }}</view>
								<view class="info-text">账户：{{ globalStoreInfo.bank_type_account }}</view>
							</block>
						</block>
						<view class="empty" v-else>无需结算</view>
					</view>
				</view>
			</view>

			<view class="settlement-record uni-flex uni-column">
				<view class="head-title">结算记录</view>
				<view class="record-wrap common-scrollbar">
					<uni-data-table url="/store/storeapi/withdraw/page" :cols="cols" ref="table" :pagesize="8">
						<template v-slot:action="data">
							<view class="common-table-action" v-if="data.value.transfer_type == 'wechatpay' && data.value.status == 1 && newWithdrawDetail && newWithdrawDetail.transfer_type" @click="showQRcode(data)"><text >收款</text></view>
							<view class="common-table-action"><text @click="detail(data)">查看详情</text></view>
						</template>
					</uni-data-table>
				</view>
			</view>
		</view>

		<uni-popup ref="applyWithdraw" type="center">
			<view class="apply-withdraw">
				<view class="title">
					本次可结算金额为
					<text class="money" v-if="globalStoreInfo">{{ globalStoreInfo.account | moneyFormat }}</text>
					元，是否申请结算？
				</view>
				<view class="btn">
					<button type="primary" class="primary-btn btn" @click="apply">确定</button>
					<button type="primary" class="default-btn btn save" @click="$refs.applyWithdraw.close()">取消</button>
				</view>
			</view>
		</uni-popup>

		<uni-popup ref="detailPopup">
			<view class="pop-box">
				<view class="pop-header">
					<view class="">结算详情</view>
					<view class="pop-header-close" @click="$refs.detailPopup.close()">
						<text class="iconguanbi1 iconfont"></text>
					</view>
				</view>
				<view class="pop-content common-scrollbar" v-if="withdrawDetail">
					<view class="pop-content-item">
						<view class="pop-content-text">结算信息</view>
						<view class="pop-contents-text">结算编号：{{ withdrawDetail.withdraw_no }}</view>
						<view class="pop-contents-text">结算状态：{{ withdrawDetail.status_name }}</view>
						<view class="pop-contents-text" v-if="withdrawDetail.status == -1 || withdrawDetail.status == -2">拒绝理由：{{ withdrawDetail.refuse_reason }}</view>
						<view class="pop-contents-text">结算金额：{{ withdrawDetail.money | moneyFormat }}</view>
						<view class="pop-contents-text">结算方式：{{ withdrawDetail.transfer_type_name }}</view>
						<view class="pop-contents-text">结算类型：{{ withdrawDetail.settlement_type_name }}</view>
						<view class="pop-contents-text">结算申请时间：{{ withdrawDetail.apply_time | timeFormat }}</view>
						<view class="pop-contents-text" v-if="withdrawDetail.transfer_type == 'bank'">银行名称：{{ withdrawDetail.bank_name }}</view>
						<view class="pop-contents-text">结算收款账号：{{ withdrawDetail.account_number }}</view>
						<view class="pop-contents-text">结算方式：{{ withdrawDetail.transfer_type_name }}</view>
						<view class="pop-contents-text">真实姓名：{{ withdrawDetail.realname }}</view>
						<view class="pop-contents-text flex" v-if="withdrawDetail.voucher_img">转账凭证：<image class="voucher-img" :src="$util.img(withdrawDetail.voucher_img)" /></view>
						<view class="pop-contents-text" v-if="withdrawDetail.voucher_desc">凭证说明：{{ withdrawDetail.voucher_desc }}</view>
					</view>
					<view class="pop-content-item" v-if="withdrawDetail.settlement_type != 'apply'">
						<view class="pop-content-text">周期结算</view>
						<view class="pop-contents-text">周期结算编号：{{ withdrawDetail.settlement_info.settlement_no }}</view>
						<view class="pop-contents-text">周期开始时间：{{ withdrawDetail.settlement_info.start_time }}</view>
						<view class="pop-contents-text">周期结束时间：{{ withdrawDetail.settlement_info.end_time }}</view>
						<view class="pop-contents-text">结算订单总额：{{ withdrawDetail.settlement_info.order_money }}</view>
						<view class="pop-contents-text">结算总分销佣金：{{ withdrawDetail.settlement_info.commission }}</view>
					</view>
				</view>
			</view>
		</uni-popup>
		
		<uni-popup ref="qrcodePopup">
			<view class="pop-box qrcode">
				<view class="pop-header">
					<view class="">扫码收款</view>
					<view class="pop-header-close" @click="closeQrcodePopup()">
						<text class="iconguanbi1 iconfont"></text>
					</view>
				</view>
				<view class="pop-content common-scrollbar qrcode-area" >
					<image class="qr-img" :src="qrcode" mode="widthFix"></image>
				</view>
			</view>
		</uni-popup>
	</base-page>
</template>

<script>
	import {
		applyWithdraw,
		getWithdrawConfig,
		withdrawDetail,
		withdrawConfig,
		transferCode
	} from '@/api/settlement.js'

	export default {
		data() {
			return {
				bankType: {
					1: '微信',
					2: '支付宝',
					3: '银行卡'
				},
				withdrawConfig: {
					is_settlement: 0
				},
				cols: [{
					width: 12,
					title: '结算方式',
					field: 'transfer_type_name',
					align: 'left'
				}, {
					width: 12,
					title: '结算类型',
					field: 'settlement_type_name',
					align: 'left'
				}, {
					width: 12,
					title: '结算金额',
					align: 'left',
					return: data => {
						return this.$util.moneyFormat(data.money);
					}
				}, {
					width: 12,
					title: '结算状态',
					field: 'status_name'
				}, {
					width: 15,
					title: '申请时间',
					align: 'center',
					return: data => {
						return data.apply_time ? this.$util.timeFormat(data.apply_time) : '';
					}
				}, {
					width: 15,
					title: '转账时间',
					align: 'center',
					return: data => {
						return data.transfer_time ? this.$util.timeFormat(data.transfer_time) : '';
					}
				}, {
					width: 20,
					title: '操作',
					action: true, // 表格操作列
					align: 'right'
				}],
				isRepeat: false,
				withdrawDetail: null,
				newWithdrawDetail: null,
				qrcode: '',
				timer: null,
			};
		},
		onLoad() {
		},
		onShow() {
			this.getWithdrawConfigFn();
			this.getNewWithdrawConfigFn();
			this.$store.dispatch('app/getStoreInfoFn');
		},
		methods: {
			closeQrcodePopup() {
				this.$refs.qrcodePopup.close()
				clearInterval(this.timer);
				this.$refs.table.load();
			},
			checkWithdrawStatus(withdraw_id) {
				this.timer = setInterval(() => {
					withdrawDetail(withdraw_id).then(res => {
						if (res.code == 0) {
							if(res.data.status == 2){
								clearInterval(this.timer);
								this.$refs.qrcodePopup.close();
								this.$refs.table.load();
							}
						}else{
							clearInterval(this.timer);
						}
					});
				}, 1000);
			},
			showQRcode(data) {
				this.qrcode = '';
				transferCode({
					id: data.value.withdraw_id,
				}).then(res => {
					this.qrcode = res.data;
					this.$refs.qrcodePopup.open();
					this.checkWithdrawStatus(data.value.withdraw_id);
				})
			},
			getNewWithdrawConfigFn() {
				withdrawConfig().then(res => {
					if (res.code == 0) {
						this.newWithdrawDetail = res.data;
					}
				});
			},
			switchStoreAfter() {
				this.$refs.table.load();
			},
			getWithdrawConfigFn() {
				getWithdrawConfig().then(res => {
					if (res.code == 0) {
						this.withdrawConfig = res.data;
					}
				});
			},
			applyWithdraw() {
				this.$refs.applyWithdraw.open();
			},
			apply() {
				if (this.isRepeat) return;
				this.isRepeat = true;
				applyWithdraw(this.globalStoreInfo.account).then(res => {
					if (res.code == 0) {
						this.$store.dispatch('app/getStoreInfoFn');
						this.$refs.applyWithdraw.close();
						this.$refs.table.load();
						setTimeout(() => {
							this.isRepeat = false;
						}, 500);
					} else {
						this.isRepeat = false;
						this.$util.showToast({
							title: res.message
						});
					}
				});
			},
			detail(data) {
				withdrawDetail(data.value.withdraw_id).then(res => {
					if (res.code == 0) {
						this.withdrawDetail = res.data;
						this.$refs.detailPopup.open('center');
					}
				});
			}
		}
	};
</script>

<style lang="scss" scoped>
	.page-height {
		height: 100%;
	}

	.account-wrap {
		flex: 1;
		width: 0;
		padding: 0.15rem;
		margin-right: 0.15rem;
		background: #fff;

		.head-title {
			font-size: 0.16rem;
		}

		.account-item {
			padding: 0.2rem 0;
			cursor: pointer;

			.account-title {
				font-size: 0.14rem;
				color: #aaa;
			}

			.money {
				font-size: 0.2rem;
				font-weight: bold;
				margin-top: 0.15rem;
				display: flex;
				align-items: center;

				.iconqianhou2 {
					line-height: 1;
					color: #bbb;
					margin-left: 0.05rem;
				}
			}

			.action {
				margin-top: 0.3rem;
				height: 0.4rem;
				button {
					display: inline-block;
					margin-right: 0.1rem;
					width: auto;
					min-width: 0.8rem;
					line-height: 0.4rem;
					height: 0.4rem;
				}
			}
		}

		.sub-bottom {
			border-top: 0.01rem solid #f5f5f5;

			.account-item {
				flex: 1;
			}
		}
	}

	.info-wrap {
		width: 30vw;
		padding: 0.15rem;
		background: #fff;

		.head-title {
			font-size: 0.16rem;
		}

		.info-content {
			margin-top: 0.2rem;

			.info-text {
				margin-bottom: 0.1rem;
			}
		}

		.empty {
			padding: 1rem;
			text-align: center;
		}
	}

	.settlement-record {
		padding: 0.15rem;
		flex: 1;
		height: 0;
		margin: 0.15rem 0;
		background: #fff;

		.head-title {
			font-size: 0.16rem;
		}

		.record-wrap {
			padding-top: 0.15rem;
			flex: 1;
			height: 0;
			overflow-y: scroll;
		}
	}

	.pop-box {
		background: #ffffff;
		width: 5rem;
		height: 60vh;
		display: flex;
		flex-direction: column;
		&.qrcode{
			width: 2.8rem;
			height: auto;
		}
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
		
		.qrcode-area{
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 0.2rem 0.2rem;
			.qr-img{
				width: 100%;
				display: block;
			}
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
	}

	.apply-withdraw {
		width: 3.8rem;
		border-radius: 0.06rem;
		background: #ffffff;
		padding: 0.6rem 0.15rem 0.2rem 0.15rem;
		box-sizing: border-box;

		.title {
			font-size: 0.16rem;
			text-align: center;
		}

		.money {
			font-weight: bold;
			font-size: 0.16rem;
		}

		.btn {
			width: 100%;
			display: flex;
			align-items: center;
			justify-content: center;
			margin-top: 0.3rem;

			.btn {
				width: auto;
				padding: 0 0.3rem;
				margin: 0;
				height: 0.35rem;
			}

			.btn:last-child {
				margin-left: 0.2rem;
			}
		}
	}

	.voucher-img {
		width: 1.5rem;
	}
</style>
<template>
	<base-page>
		<view class="manage">
			<view class="title-back flex items-center cursor-pointer" @click="backFn">
                    <text class="iconfont iconqianhou1"></text>
                    <text class="left">返回</text>
                    <text class="content">|</text>
                    <text>结算记录</text>
                </view>
			<view class="screen-warp common-form">
				<view class="common-form-item">
					<view class="form-inline goods-category">
						<label class="form-label">结算方式</label>
						<view class="form-input-inline">
							<select-lay :zindex="10" :value="screen.transfer_type" name="names" placeholder="请选择结算方式" :options="transferType" @selectitem="selectTransferType"/>
						</view>
					</view>
					<view class="form-inline goods-category">
						<label class="form-label">结算类型</label>
						<view class="form-input-inline">
							<select-lay :zindex="9" :value="screen.settlement_type" name="names" placeholder="请选择结算类型" :options="settlementType" @selectitem="selectSettlementType"/>
						</view>
					</view>
				</view>
				<view class="common-form-item">
					<view class="form-inline goods-category">
						<label class="form-label">结算状态</label>
						<view class="form-input-inline">
							<select-lay :zindex="9" :value="screen.status" name="names" placeholder="请选择结算状态" :options="status" @selectitem="selectStatus"/>
						</view>
					</view>
					<view class="form-inline">
						<label class="form-label">申请时间</label>
						<view class="form-input-inline">
							<uni-datetime-picker v-model="screen.start_time" type="datetime" placeholder="请选择开始时间" :clearIcon="false" />
						</view>
						<view class="form-input-inline">
							<uni-datetime-picker v-model="screen.end_time" type="datetime" placeholder="请选择结束时间" :clearIcon="false" />
						</view>
					</view>
				</view>
				<view class="common-form-item">
					<view class="form-inline common-btn-wrap">
						<button type="default" class="screen-btn" @click="search()">筛选</button>
						<button type="default" @click="reset()">重置</button>
					</view>
				</view>
			</view>

			<uni-data-table url="/store/storeapi/withdraw/page" :cols="cols" ref="table">
				<template v-slot:action="data">
					<view class="common-table-action"><text @click="detail(data)">查看详情</text></view>
				</template>
			</uni-data-table>

			<uni-popup ref="detailPopup">
				<view class="pop-box">
					<view class="pop-header">
						<view class="pop-header-text">结算详情</view>
						<view class="pop-header-close" @click="$refs.detailPopup.close()">
							<text class="iconguanbi1 iconfont"></text>
						</view>
					</view>
					<view class="pop-content common-scrollbar" v-if="withdrawDetail">
						<view class="pop-content-item">
							<view class="pop-content-text">结算信息</view>
							<view class="pop-contents-text">结算编号：{{ withdrawDetail.withdraw_no }}</view>
							<view class="pop-contents-text">结算状态：{{ withdrawDetail.status_name }}</view>
							<view class="pop-contents-text">结算金额：{{ withdrawDetail.money | moneyFormat }}</view>
							<view class="pop-contents-text">结算方式：{{ withdrawDetail.transfer_type_name }}</view>
							<view class="pop-contents-text">结算类型：{{ withdrawDetail.settlement_type_name }}</view>
							<view class="pop-contents-text">结算申请时间：{{ withdrawDetail.apply_time | timeFormat }}</view>
							<view class="pop-contents-text" v-if="withdrawDetail.transfer_type == 'bank'">银行名称：{{ withdrawDetail.bank_name }}</view>
							<view class="pop-contents-text">结算收款账号：{{ withdrawDetail.account_number }}</view>
							<view class="pop-contents-text">结算方式：{{ withdrawDetail.transfer_type_name }}</view>
							<view class="pop-contents-text">真实姓名：{{ withdrawDetail.realname }}</view>
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
		</view>
	</base-page>
</template>

<script>
	import {
		getWithdrawScreen,
		withdrawDetail
	} from '@/api/settlement.js';

	export default {
		data() {
			return {
				screen: {
					page: 1,
					start_time: '',
					end_time: '',
					withdraw_no: '',
					transfer_type: '',
					settlement_type: '',
					status: 'all'
				},
				userList: [],
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
				status: [],
				settlementType: [],
				transferType: [],
				withdrawDetail: null
			};
		},
		onLoad() {
			this.getScreenContent();
		},
		methods: {
			switchStoreAfter() {
				this.screen = {
					page: 1,
					start_time: '',
					end_time: ''
				};
				this.$refs.table.load();
			},
			search() {
				this.$refs.table.load(this.screen);
			},
			reset() {
				this.screen = {
					page: 1,
					start_time: '',
					end_time: '',
					withdraw_no: '',
					transfer_type: '',
					settlement_type: '',
					status: 'all'
				};
			},
			getScreenContent() {
				getWithdrawScreen().then(res => {
					if (res.code == 0) {
						this.status = Object.keys(res.data.status).map(index => {
							return {
								value: index,
								label: res.data.status[index]
							};
						});
						this.settlementType = Object.keys(res.data.settlement_type).map(index => {
							return {
								value: index,
								label: res.data.settlement_type[index]
							};
						});
						this.transferType = Object.keys(res.data.transfer_type_list).map(index => {
							return {
								value: index,
								label: res.data.transfer_type_list[index]
							};
						});
					}
				});
			},
			selectTransferType(index) {
				this.screen.transfer_type = index == -1 ? '' : this.transferType[index].value;
			},
			selectSettlementType(index) {
				this.screen.settlement_type = index == -1 ? '' : this.settlementType[index].value;
			},
			selectStatus(index) {
				this.screen.status = index == -1 ? 'all' : this.status[index].value;
			},
			detail(data) {
				withdrawDetail(data.value.withdraw_id).then(res => {
					if (res.code == 0) {
						this.withdrawDetail = res.data;
						this.$refs.detailPopup.open('center');
					}
				});
			},
			backFn() {
				this.$util.redirectTo('/pages/store/settlement');
			},
		}
	};
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
		flex-direction: column;

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
	}

	// pop弹框
	.pop-box {
		background: #ffffff;
		width: 5rem;
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
	}
</style>
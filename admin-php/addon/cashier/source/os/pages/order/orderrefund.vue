<template>
	<base-page>
		<view class="goodslist">
			<view class="goodslist-box">
				<view class="goodslist-left">
					<view class="goods-title">
						退款维权
						<text class="iconfont icongengduo1"></text>
					</view>
					<view class="goods-search">
						<view class="search">
							<text class="iconfont icon31sousuo"></text>
							<input type="text" v-model="search_text" @input="search" placeholder="搜索订单号/商品名称" />
						</view>
					</view>
					<block v-if="!one_judge && order_list.length > 0">
						<scroll-view :scroll-top="scrollTop" @scroll="scroll" scroll-y="true" class="goods-list-scroll" :show-scrollbar="false" @scrolltolower="getOrderList">
							<view class="item" @click="getOrderDetail(item.order_goods_id, index)" v-for="(item, index) in order_list" :key="index" :class="index == selectGoodsKeys ? 'itemhover' : ''">
								<view class="title">
									<view>退款编号：{{ item.order_no }}</view>
									<view>{{ item.order_type_name }}</view>
								</view>
								<view class="total-money-num">
									<view class="box">
										<view>订单金额</view>
										<view>￥{{ item.real_goods_money }}</view>
									</view>
									<view class="box">
										<view>退款金额</view>
										<view>￥{{ item.refund_apply_money }}</view>
									</view>
								</view>
								<view class="total-money-num">
									<view class="member-info">
										<view>退款状态：</view>
										<view>{{ item.refund_status_name }}({{ item.refund_type == 1 ? '仅退款' : '退款退货' }})</view>
									</view>
								</view>
							</view>
						</scroll-view>
					</block>
					<view class="notYet" v-else-if="!one_judge && order_list.length == 0">暂无数据</view>
				</view>
				<view class="goodslist-right">
					<view class="goods-title">订单详情</view>
					<view class="order-information tab-wrap" v-if="Object.keys(order_detail).length">
						<view class="tab-head">
							<text v-for="(item, index) in tabObj.list" :key="index" :class="{ active: tabObj.index == item.value }" @click="tabObj.index = item.value">{{ item.name }}</text>
						</view>
						<view class="tab-content">
							<view class="other-information" v-if="tabObj.index == 1">
								<view class="item-box">
									<view class="item">
										<view>买家：</view>
										<view v-if="order_detail.nickname">{{ order_detail.nickname }}</view>
										<view v-else>散客</view>
									</view>
									<view class="item">
										<view>退款编号：</view>
										<view>{{ order_detail.refund_no }}</view>
									</view>
									<view class="item">
										<view>申请时间：</view>
										<view>{{ $util.timeFormat(order_detail.refund_action_time) }}</view>
									</view>
									<view class="item">
										<view>维权类型：</view>
										<view>{{ order_detail.refund_mode > 1 ? '售后' : '退款' }}</view>
									</view>
									<view class="item">
										<view>退款方式：</view>
										<view>
											{{ order_detail.shop_active_refund == 1 ? '主动退款' : order_detail.refund_type == 1 ? '仅退款' : '退货退款' }}
											({{ (order_detail.refund_type == 1 && '原路退款') || (order_detail.refund_type == 2 && '线下退款') || '退款到余额' }})
										</view>
									</view>
									<view class="item">
										<view>退款金额：</view>
										<view>￥{{ order_detail.refund_status == 3 ? order_detail.refund_real_money : order_detail.refund_apply_money }}</view>
									</view>
									<view class="item">
										<view>退款原因：</view>
										<view>{{ order_detail.refund_reason || '--' }}</view>
									</view>
									<view class="item">
										<view>退款说明：</view>
										<view>{{ order_detail.refund_remark || '--' }}</view>
									</view>
									<view class="item">
										<view>商家退款说明：</view>
										<view>{{ order_detail.shop_refund_remark || '--' }}</view>
									</view>
									<view class="item">
										<view>退款状态：</view>
										<view>{{ order_detail.refund_status_name }}</view>
									</view>
									<view class="item" v-if="order_detail.refund_refuse_reason">
										<view>拒绝理由：</view>
										<view>{{ order_detail.refund_refuse_reason }}</view>
									</view>
								</view>
							</view>

							<view class="goods-info" v-if="tabObj.index == 2">
								<view class="table">
									<view class="table-th table-all">
										<view class="table-td" style="width:45%">商品（元）</view>
										<view class="table-td" style="width:15%">价格</view>
										<view class="table-td" style="width:10%">数量</view>
										<view class="table-td" style="width:15%;">小计（元）</view>
										<view class="table-td" style="width:15%;justify-content: flex-end;">状态</view>
									</view>
									<view class="table-tr table-all">
										<view class="table-td" style="width:45%">{{ order_detail.sku_name }}</view>
										<view class="table-td" style="width:15%">{{ order_detail.price }}</view>
										<view class="table-td" style="width:10%">{{ order_detail.num }}</view>
										<view class="table-td" style="width:15%">{{ order_detail.goods_money }}</view>
										<view class="table-td uni-column" style="width:15%;align-items: flex-end;">{{ order_detail.refund_status_name }}</view>
									</view>
								</view>
							</view>

							<view class="other-information journal" v-if="tabObj.index == 3">
								<ns-order-log :list="order_detail.refund_log_list"></ns-order-log>
							</view>
							<view class="remarks-box" v-if="order_detail.refund_action.length">
								<block v-for="(item, index) in order_detail.refund_action" :key="index">
									<button type="primary" class="primary-btn btn remarks" @click="open(item['event'])">{{ item.title }}</button>
								</block>
							</view>
						</view>
					</view>
					<block v-else-if="!one_judge && !Object.keys(order_detail).length" >
						<image class="cart-empty" src="@/static/cashier/cart_empty.png" mode="widthFix" />
					</block>
				</view>
			</view>

			<!-- 同意退款 -->
			<unipopup ref="orderRefundAgree" type="center">
				<view class="order-refund-agree">
					<view class="title">售后维权处理</view>
					<view class="content">
						<view class="content-item">
							<view class="title">注意：</view>
							<view class="info">
								<text v-if="order_detail.pay_type == 'OFFLINE_PAY'">该笔订单通过线下支付，商家同意后，退款将通过线下原路退回。</text>
								<text v-else>该笔订单通过在线付款，商家同意后，退款将自动原路退回买家付款账户。</text>
							</view>
						</view>
						<view class="content-item">
							<view class="title">退款方式：</view>
							<view class="info">
								<text>{{ order_detail.refund_type == 1 ? '仅退款' : '退货退款' }}</text>
							</view>
						</view>
						<view class="content-item">
							<view class="title">退款金额：</view>
							<view class="info">
								<text>￥{{ order_detail.refund_apply_money }}</text>
							</view>
						</view>
					</view>

					<view class="btn">
						<button type="primary" class="default-btn btn save" @click="$refs.orderRefundAgree.close()">取消</button>
						<button type="primary" class="primary-btn btn" @click="orderRefundAgree()">确认退款</button>
					</view>
				</view>
			</unipopup>

			<!-- 拒绝退款 -->
			<unipopup ref="orderRefundRefuse" type="center">
				<view class="order-refund-agree">
					<view class="title">售后维权处理</view>
					<view class="content">
						<view class="tips">注意：建议你与买家协商后，再确定是否拒绝退款。如你拒绝退款后，买家可修改退款申请协议重新发起退款。</view>
						<view class="content-item">
							<view class="title">退款方式：</view>
							<view class="info">
								<text>{{ order_detail.refund_type == 1 ? '仅退款' : '退货退款' }}</text>
							</view>
						</view>
						<view class="content-item">
							<view class="title">退款金额：</view>
							<view class="info">
								<text>￥{{ order_detail.refund_apply_money }}</text>
							</view>
						</view>
						<view class="content-item textarea-wrap">
							<view class="title">拒绝理由：</view>
							<view class="info textarea-box">
								<textarea v-model="refundRefuseReason" class="textarea" maxlength="200" placeholder="请输入拒绝理由，最多不超过200字"></textarea>
							</view>
						</view>
					</view>

					<view class="btn">
						<button type="primary" class="default-btn btn save" @click="$refs.orderRefundRefuse.close()">取消</button>
						<button type="primary" class="primary-btn btn" @click="orderRefundRefuse()">确认退款</button>
					</view>
				</view>
			</unipopup>

			<!-- 关闭维权 -->
			<unipopup ref="orderRefundClose" type="center">
				<view class="order-close">
					<view class="title">确定要关闭本次维权吗？</view>
					<view class="btn">
						<button type="primary" class="default-btn btn save" @click="$refs.orderRefundClose.close()">取消</button>
						<button type="primary" class="primary-btn btn" @click="orderRefundClose()">确定</button>
					</view>
				</view>
			</unipopup>

			<!-- 转账 -->
			<unipopup ref="orderRefundTransfer" type="center">
				<view class="order-refund-agree">
					<view class="title">售后维权处理</view>
					<view class="content">
						<view class="content-item">
							<view class="title">申请退款金额：</view>
							<view class="info">
								<text>￥{{ order_detail.refund_apply_money }}</text>
							</view>
						</view>
						<view class="content-item">
							<view class="title">实际退款金额：</view>
							<view class="info">
								<view class="money-box">
									<input type="number" v-model="refundTransfer.refund_real_money" />
									元
								</view>
							</view>
						</view>
						<view class="content-item" v-if="order_detail.use_point>0">
							<view class="title">退还积分：</view>
							<view class="info">
								<text>{{ order_detail.use_point }}</text>
							</view>
						</view>
						<view class="content-item" v-if="order_detail.coupon_info && order_detail.coupon_info.length>0">
							<view class="title">退还优惠券：</view>
							<view class="info">
								<text>{{ order_detail.coupon_info.coupon_name }}</text>
								<text v-if="order_detail.coupon_info.money>0">（{{order_detail.coupon_info.money}}）</text>
								<text v-else>（{{order_detail.coupon_info.discount}}折）</text>
							</view>
						</view>
						<view class="content-item">
							<view class="title">退款方式：</view>
							<view class="info">
								<radio-group @change="refundTransfer.refund_money_type = $event.detail.value" class="form-radio-group">
									<label class="radio form-radio-item">
										<radio value="1" :checked="refundTransfer.refund_money_type == 1" />
										原路退款
									</label>
									<label class="radio form-radio-item">
										<radio value="2" :checked="refundTransfer.refund_money_type == 2" />
										线下退款
									</label>
									<label class="radio form-radio-item">
										<radio value="3" :checked="refundTransfer.refund_money_type == 3" />
										退款到余额
									</label>
								</radio-group>
							</view>
						</view>

						<view class="content-item textarea-wrap">
							<view class="title">退款说明：</view>
							<view class="info textarea-box">
								<textarea v-model="refundTransfer.shop_refund_remark" class="textarea" maxlength="200" placeholder="请输入拒绝理由，最多不超过200字"></textarea>
							</view>
						</view>
					</view>

					<view class="btn">
						<button type="primary" class="default-btn btn save" @click="$refs.orderRefundTransfer.close()">取消</button>
						<button type="primary" class="primary-btn btn" @click="orderRefundTransfer()">确认退款</button>
					</view>
				</view>
			</unipopup>

			<!-- 买家退货接收，维权收货 -->
			<unipopup ref="orderRefundTakeDelivery" type="center">
				<view class="order-refund-agree">
					<view class="title">售后维权处理</view>
					<view class="content">
						<view class="tips">注意：需你同意退款申请，买家才能退货给你；买家退货后你需再次确认收货后，退款将自动原路退回至买家付款账户。</view>
						<view class="content-item">
							<view class="title">退款方式：</view>
							<view class="info">
								<text>{{ order_detail.refund_type == 1 ? '仅退款' : '退货退款' }}</text>
							</view>
						</view>
						<view class="content-item">
							<view class="title">退款金额：</view>
							<view class="info">
								<text>￥{{ order_detail.refund_apply_money }}</text>
							</view>
						</view>
						<view class="content-item">
							<view class="title">退货地址：</view>
							<view class="info">
								<text>{{ order_detail.refund_address }}</text>
							</view>
						</view>
						<view class="content-item textarea-wrap">
							<view class="title">是否入库：</view>
							<view class="info">
								<radio-group @change="isRefundStock = $event.detail.value" class="form-radio-group">
									<label class="radio form-radio-item">
										<radio value="0" :checked="isRefundStock == 0" />
										否
									</label>
									<label class="radio form-radio-item">
										<radio value="1" :checked="isRefundStock == 1" />
										是
									</label>
								</radio-group>
							</view>
						</view>
					</view>

					<view class="btn">
						<button type="primary" class="default-btn btn save" @click="$refs.orderRefundTakeDelivery.close()">取消</button>
						<button type="primary" class="primary-btn btn" @click="orderRefundTakeDelivery()">确认收到退货</button>
					</view>
				</view>
			</unipopup>

		</view>
	</base-page>
</template>

<script>
	import orderRefund from './public/js/order_refund'
	import nsOrderLog from '@/components/ns-order-log/ns-order-log.vue';
	import unipopup from '@/components/uni-popup/uni-popup.vue';

	export default {
		components: {
			nsOrderLog,
			unipopup
		},
		mixins: [orderRefund]
	};
</script>

<style scoped lang="scss">
	@import './public/css/orderlist.scss';

	.total-money-num .box {
		margin-top: 0;
	}

	.goodslist {
		.goodslist-right {
			.other-information {
				.title {
					margin-bottom: 0.1rem !important;
				}

				.item-box {
					margin-bottom: 0.15rem;

					.item {

						&:nth-child(1),
						&:nth-child(2) {
							margin-top: 0 !important;
						}

						view:nth-child(1) {
							width: 1rem !important;
						}
					}
				}
			}
		}
	}

	.tab-wrap {
		padding: 0 !important;
		background-color: #fff !important;

		.tab-head {
			display: flex;
			background-color: #f8f8f8;

			text {
				width: 1.15rem;
				height: 0.55rem;
				line-height: 0.55rem;
				text-align: center;
				font-size: $uni-font-size-lg;

				&.active {
					background-color: #fff;
				}
			}
		}
	}

	.item-box {
		padding: 0.1rem;
	}
	/deep/ .goods-list-scroll {
			width: 100%;
			height: calc(100% - 1.44rem) !important;
	}
</style>
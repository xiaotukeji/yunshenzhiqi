<template>
	<base-page>
		<view class="goodslist">
			<view class="goodslist-box">
				<view class="goodslist-left">
					<view class="goods-title">
						退款记录
						<text class="iconfont icongengduo1"></text>
					</view>

					<view class="goods-search">
						<view class="search">
							<text class="iconfont icon31sousuo"></text>
							<input type="text" v-model="search_text" @input="search" placeholder="搜索退款编号/订单号/客户手机号" />
						</view>
					</view>
					<block v-if="refund_list.length > 0">
						<scroll-view scroll-y="true" class="goods-list-scroll" :show-scrollbar="false" @scrolltolower="getRefundList">
							<view class="item" @click="getRefundDetail(item.refund_id, index)" v-for="(item, index) in refund_list" :key="index" :class="index == refundIndex ? 'itemhover' : ''">
								<view class="title">
									<view>退款编号：{{ item.refund_no }}</view>
									<view>{{ item.refund_status_name }}</view>
								</view>
								<view class="total-money-num">
									<view class="member-info">
										<view>客户：</view>
										<view v-if="item.member_id">{{ item.nickname }}</view>
										<view v-else>散客</view>
									</view>

									<view class="box">
										<view>退款金额</view>
										<view>￥{{ item.refund_money }}</view>
									</view>
								</view>
							</view>
						</scroll-view>
					</block>
					<view class="notYet" v-else-if="refund_list.length == 0">暂无数据</view>
				</view>
				<view class="goodslist-right">
					<view class="goods-title">退款详情</view>
					<block v-if="refund_detail">
						<view class="order-information">
							<view class="order-status">{{ refund_detail.refund_status_name }}</view>
							<view class="goods-info">
								<block v-for="(item, index) in refund_detail.item_list" :key="index">
									<view class="goods-item">
										<view class="image">
											<image :src="$util.img(item.img, { size: 'small' })" mode="widthFix" />
										</view>
										<view class="info">
											<view class="content-text">{{ item.name }}</view>
										</view>
										<view>
											<view class="price">
												<text class="title">退款金额：</text>
												￥{{ item.refund_pay_money }}
											</view>
										</view>
									</view>
								</block>
							</view>
							<view class="goods-info refund-info">
								<view class="info-item">
									<view class="title">退款类型</view>
									<view class="content">{{ refund_detail.refund_trade_type_name }}</view>
								</view>
								<view class="info-item">
									<view class="title">退款编号</view>
									<view class="content">{{ refund_detail.refund_no }}</view>
								</view>
								<view class="info-item">
									<view class="title">退款时间</view>
									<view class="content">{{ refund_detail.create_time | timeFormat }}</view>
								</view>
								<view class="info-item">
									<view class="title">退款方式</view>
									<view class="content">{{ refund_detail.refund_transfer_type_name }}</view>
								</view>
								<view class="info-item">
									<view class="title">退款说明</view>
									<view class="content">{{ refund_detail.refund_goods_remark }}</view>
								</view>
								<view class="info-item">
									<view class="title">退款金额</view>
									<view class="content">￥{{ refund_detail.refund_pay_money }}</view>
								</view>
								<view class="info-item">
									<view class="title">退还积分</view>
									<view class="content">{{ refund_detail.refund_point }}积分</view>
								</view>
								<view class="info-item">
									<view class="title">退还余额</view>
									<view class="content">￥{{ (parseFloat(refund_detail.refund_balance_money) + parseFloat(refund_detail.refund_balance)) | moneyFormat }}</view>
								</view>
							</view>
						</view>
					</block>
					<block v-else>
						<image class="cart-empty" src="@/static/cashier/cart_empty.png" mode="widthFix" />
					</block>
				</view>
			</view>
		</view>
	</base-page>
</template>

<script>
import { getOrderRefundLists, getOrderRefundDetail } from '@/api/order_refund.js'
export default {
	data() {
		return {
			refundIndex: 0,
			// 订购日志所需列表数据
			list: [],
			//获取订单的页数
			page: 1,
			//每次获取订单的条数
			page_size: 8,
			// 订单搜索是用到的数据
			search_text: '',
			// 订单列表数据
			refund_list: [],
			//订单详情数据
			refund_detail: null
		};
	},
	onLoad(option) {
		this.getRefundList();
	},
	methods: {
		// 搜索
		search() {
			this.page = 1;
			this.refund_list = [];
			this.getRefundList();
		},
		/**
		 * 获取订单列表
		 */
		getRefundList() {
			getOrderRefundLists({
				page: this.page,
				page_size: this.page_size,
				search_text: this.search_text
			}).then(res=>{
				if (res.data.list.length == 0) {
					this.refund_detail = null;
				}
				if (res.code >= 0 && res.data.list.length != 0) {
					if (this.refund_list.length == 0) {
						this.refund_list = res.data.list;
					} else {
						this.refund_list = this.refund_list.concat(res.data.list);
					}
					//初始时加载一遍详情数据
					if (this.page == 1) {
						this.getRefundDetail(this.refund_list[0].refund_id);
					}
					this.page += 1;
				}
			})
		},
		/**
		 * 获取订单详情数据
		 */
		getRefundDetail(refund_id, index = 0) {
			this.refundIndex = index;
			getOrderRefundDetail({refund_id}).then(res=>{
				if (res.code >= 0) {
					this.refund_detail = res.data;
				}
			})
		}
	}
};
</script>

<style scoped lang="scss">
@import './public/css/list.scss';
</style>

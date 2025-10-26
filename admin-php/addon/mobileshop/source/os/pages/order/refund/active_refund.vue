<template>
	<view>
		<!-- 售后申请同意 -->
		<view class="item-wrap">
			<view class="form-wrap">
				<text class="label">退款金额</text>
				<text class="value color-base-text money">￥{{ detail.refund_apply_money }}</text>
			</view>
			<view class="form-wrap border-none">
				<text class="label">主动退款</text>
				<input class="uni-input align-right" type="digit" v-model="activeRefundRealMoney" placeholder="请输入退款金额"/>
			</view>
			<picker @change="shopActiveRefundStatusChange" :value="shopActiveRefundStatusIndex" :range="shopActiveRefundStatusArray" range-key="name">
				<view class="form-wrap more-wrap picker">
					<text class="label">退款状态</text>
						<text class="selected color-title">{{ shopActiveRefundStatusArray[shopActiveRefundStatusIndex].name }}</text>
					<text class="iconfont iconright"></text>
				</view>
			</picker>
			<view class="form-tips">
				<view>1、如果是退部分金额，退款后可以是部分退款状态或退款完成状态</view>
				<view>2、如果是退全部金额，则退款后一定是退款完成状态</view>
				<view>3、退款完成才会执行相关业务如核销码失效，卡包失效等操作</view>
			</view>
			<picker @change="refundTypeChange" :value="refundType" :range="refundTypeArray">
				<view class="form-wrap more-wrap picker">
					<text class="label">退款方式</text>
						<text class="selected color-title">{{ refundTypeArray[refundType] }}</text>
					<text class="iconfont iconright"></text>
				</view>
			</picker>
			<view class="form-wrap reason">
				<text class="label">退款说明</text>
				<textarea class="uni-input" v-model="refundRefuseReason" placeholder="请输入退款说明" maxlength="200" />
			</view>
		</view>
		<view class="footer-wrap">
			<button type="default" @click="cancel()">取消</button>
			<button type="primary" @click="save()">确认退款</button>
		</view>
		<loading-cover ref="loadingCover"></loading-cover>
	</view>
</template>

<script>
import refundAction from '../js/refund_action.js';
export default {
	data() {
		return {
			refundRefuseReason: '',
			activeRefundRealMoney: '',
			shopActiveRefundStatusArray:[
				{id:'PARTIAL_REFUND',name:'部分退款状态'},
				{id:'REFUND_COMPLETE',name:'退款完成状态'},
			],
			shopActiveRefundStatusIndex:0,
		};
	},
	mixins: [refundAction],
	methods: {
		save() {
			if (isNaN(parseFloat(this.activeRefundRealMoney))) {
				this.$util.showToast({
					title: '请输入正确的退款金额'
				});
				return;
			}
			if (parseFloat(this.activeRefundRealMoney) < 0) {
				this.$util.showToast({
					title: '退款金额不能为负数'
				});
				return;
			}
			
			if (this.repeatFlag) return;
			this.repeatFlag = true;
			
			uni.showLoading({ title: '' })
			
			let refundType = this.refundType + 1;
			let refundStatus = this.shopActiveRefundStatusArray[this.shopActiveRefundStatusIndex].id;
			
			this.$api.sendRequest({
				url: '/shopapi/orderrefund/activerefund',
				data: {
					order_goods_id: this.orderGoodsId,
					shop_active_refund_money: this.activeRefundRealMoney,
					shop_active_refund_remark: this.refundRefuseReason,
					shop_active_refund_money_type: refundType,
					refund_status:refundStatus,
				},
				success: res => {
					uni.hideLoading();
					if (res.code == 0) {
						setTimeout(() => {
							this.cancel();
						}, 1000);
					}
					this.repeatFlag = false;
					this.$util.showToast({
						title: res.message
					});
				}
			});
		},
		shopActiveRefundStatusChange(e) {
			this.shopActiveRefundStatusIndex = e.detail.value;
		}
	}
};
</script>

<style lang="scss">
@import '../css/refund_action.scss';
</style>

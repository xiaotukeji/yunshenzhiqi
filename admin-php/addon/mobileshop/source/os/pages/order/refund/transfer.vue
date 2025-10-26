<template>
	<view>
		<!-- 维权转账 -->
		<view class="item-wrap">
			<view class="form-wrap">
				<text class="label">申请退款金额</text>
				<text class="value color-base-text money">
					￥{{ detail.refund_apply_money }}{{ detail.refund_delivery_money > 0 ? '(含运费' + detail.refund_delivery_money + ')' : '' }}
				</text>
			</view>
			<view class="form-wrap">
				<text class="label">实际退款金额</text>
				<input class="uni-input align-right" type="digit" v-model="refundRealMoney" placeholder="请输入退款金额"/>
			</view>
			<view class="form-wrap" v-if="detail.use_point > 0">
				<text class="label">退还积分</text>
				<text class="value">{{ detail.use_point }}积分</text>
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
			<button type="primary" @click="save()">确认转账</button>
		</view>
		<loading-cover ref="loadingCover"></loading-cover>
	</view>
</template>

<script>
import refundAction from '../js/refund_action.js';
export default {
	data() {
		return {};
	},
	mixins: [refundAction],
	methods: {
		save() {
			if (isNaN(parseFloat(this.refundRealMoney))) {
				this.$util.showToast({
					title: '请输入正确的退款金额'
				});
				return;
			}
			if (parseFloat(this.refundRealMoney) < 0) {
				this.$util.showToast({
					title: '退款金额不能为负数'
				});
				return;
			}
			
			if (this.repeatFlag) return;
			this.repeatFlag = true;
				
			let refundType = this.refundType + 1;
				
			this.$api.sendRequest({
				url: '/shopapi/orderrefund/complete',
				data: {
					order_goods_id: this.orderGoodsId,
					refund_money_type: refundType,
					shop_refund_remark: this.refundRefuseReason,
					refund_real_money: this.refundRealMoney
				},
				success: res => {
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
		}
	}
};
</script>

<style lang="scss">
@import '../css/refund_action.scss';
</style>

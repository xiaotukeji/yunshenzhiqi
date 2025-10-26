<template>
	<base-page>
		<view class="collect-money-config">
			<view class="common-wrap common-form fixd common-scrollbar">
				<view class="common-title">收款设置</view>
				<view class="common-form-item">
					<label class="form-label">优惠减现</label>
					<view class="form-inline">
						<radio-group @change="config.reduction = $event.detail.value" class="form-radio-group">
							<label class="radio form-radio-item">
								<radio value="1" :checked="config.reduction == 1" />
								启用
							</label>
							<label class="radio form-radio-item">
								<radio value="0" :checked="config.reduction == 0" />
								关闭
							</label>
						</radio-group>
					</view>
				</view>
				<view class="common-form-item">
					<label class="form-label">积分抵扣</label>
					<view class="form-inline">
						<radio-group @change="config.point = $event.detail.value" class="form-radio-group">
							<label class="radio form-radio-item">
								<radio value="1" :checked="config.point == 1" />
								启用
							</label>
							<label class="radio form-radio-item">
								<radio value="0" :checked="config.point == 0" />
								关闭
							</label>
						</radio-group>
					</view>
					<text class="form-word-aux-line">积分抵扣需要平台开启，同时配置积分抵扣金额比率</text>
				</view>
				<view class="common-form-item">
					<label class="form-label">使用余额</label>
					<view class="form-inline">
						<radio-group @change="config.balance = $event.detail.value" class="form-radio-group">
							<label class="radio form-radio-item">
								<radio value="1" :checked="config.balance == 1" />
								启用
							</label>
							<label class="radio form-radio-item">
								<radio value="0" :checked="config.balance == 0" />
								关闭
							</label>
						</radio-group>
					</view>
				</view>
				<view v-show="config.balance == 1">
					<view class="common-form-item">
						<label class="form-label">余额使用安全验证</label>
						<view class="form-inline">
							<radio-group @change="config.balance_safe = $event.detail.value" class="form-radio-group">
								<label class="radio form-radio-item">
									<radio value="1" :checked="config.balance_safe == 1" />
									启用
								</label>
								<label class="radio form-radio-item">
									<radio value="0" :checked="config.balance_safe == 0" />
									关闭
								</label>
							</radio-group>
						</view>
						<text class="form-word-aux-line">关闭之后直接使用余额进行抵扣，无需会员验证</text>
					</view>
					<view class="common-form-item" v-show="config.balance_safe == 1">
						<label class="form-label">手机号验证</label>
						<view class="form-inline">
							<radio-group @change="config.sms_verify = $event.detail.value" class="form-radio-group">
								<label class="radio form-radio-item">
									<radio value="1" :checked="config.sms_verify == 1" />
									启用
								</label>
								<label class="radio form-radio-item">
									<radio value="0" :checked="config.sms_verify == 0" />
									关闭
								</label>
							</radio-group>
						</view>
						<text class="form-word-aux-line">使用余额安全验证时是否可以使用短信验证码验证</text>
					</view>
				</view>
				<view class="common-form-item">
					<label class="form-label">收款方式</label>
					<view class="form-inline">
						<checkbox-group class="form-checkbox-group" @change="config.pay_type = $event.detail.value">
							<label class="form-checkbox-item">
								<checkbox value="third" :checked="config.pay_type.indexOf('third') != -1" />
								付款码支付
							</label>
							<label class="form-checkbox-item">
								<checkbox value="cash" :checked="config.pay_type.indexOf('cash') != -1" />
								现金支付
							</label>
							<label class="form-checkbox-item">
								<checkbox value="own_wechatpay" :checked="config.pay_type.indexOf('own_wechatpay') != -1" />
								个人微信
							</label>
							<label class="form-checkbox-item">
								<checkbox value="own_alipay" :checked="config.pay_type.indexOf('own_alipay') != -1" />
								个人支付宝
							</label>
							<label class="form-checkbox-item">
								<checkbox value="own_pos" :checked="config.pay_type.indexOf('own_pos') != -1" />
								个人POS刷卡
							</label>
						</checkbox-group>
					</view>
					<text class="form-word-aux-line">付款码支付：扫描会员微信或支付宝付款码进行收款</text>
				</view>
				<view class="common-btn-wrap">
					<button type="default" class="screen-btn" @click="saveFn">保存</button>
				</view>
			</view>
		</view>
	</base-page>
</template>

<script>
import { getCollectMoneyConfig, setCollectMoneyConfig } from '@/api/config.js';
export default {
	data() {
		return {
			config: {
				reduction: 1,
				point: 1,
				balance: 1,
				balance_safe: 0,
				sms_verify: 0,
				pay_type: ['third', 'cash', 'own_wechatpay', 'own_alipay', 'own_pos']
			},
			isRepeat: false
		};
	},
	onLoad() {
		this.getData();
	},
	onShow() { },
	methods: {
		getData() {
			getCollectMoneyConfig().then(res => {
				if (res.code >= 0) {
					this.config = res.data;
				}
			});
		},
		saveFn() {
			if (!this.config.pay_type.length) {
				this.$util.showToast({ title: '至少需启用一种收款方式' });
				return;
			}

			if (this.isRepeat) return;
			this.isRepeat = true;

			let data = this.$util.deepClone(this.config);
			data.pay_type = JSON.stringify(data.pay_type);

			setCollectMoneyConfig(data).then(res => {
				this.isRepeat = false;
				if (res.code >= 0) {
					this.$util.showToast({
						title: '设置成功'
					});
				} else {
					this.$util.showToast({
						title: res.message
					});
				}
			})
		}
	}
};
</script>

<style lang="scss" scoped>
.collect-money-config {
	position: relative;

	.common-btn-wrap {
		position: absolute;
		left: 0;
		bottom: 0;
		right: 0;
		padding: 0.24rem 0.2rem;

		.screen-btn {
			margin: 0;
		}
	}

	.common-wrap.fixd {
		padding: 30rpx;
		height: calc(100vh - 0.4rem);
		overflow-y: auto;
		// padding-bottom: 1rem !important;
		box-sizing: border-box;
	}

	.form-input {
		font-size: 0.16rem;
	}

	.form-input-inline.btn {
		height: 0.37rem;
		line-height: 0.35rem;
		box-sizing: border-box;
		border: 0.01rem solid #e6e6e6;
		text-align: center;
		cursor: pointer;
	}

	.common-title {
		font-size: 0.18rem;
		margin-bottom: 0.2rem;
	}

	.common-form .common-form-item .form-label {
		width: 1.5rem;
	}

	.common-form .common-form-item .form-word-aux-line {
		margin-left: 1.5rem;
	}
}
</style>

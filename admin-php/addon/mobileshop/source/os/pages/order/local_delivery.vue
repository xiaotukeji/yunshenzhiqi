<template>
	<view>
		<view class="item-wrap">
			<view class="form-wrap">
				<text class="label">收货地址</text>
				<text class="value">{{ order.full_address }} {{ order.address }}</text>
			</view>
			<view class="form-wrap">
				<text class="label">发货方式</text>
				<text class="value">商家自配送</text>
			</view>
			<!-- <view class="form-wrap">
				<view class="label">
					<text class="required color-base-text">*</text>
					<text>配送员</text>
				</view>
				<input class="uni-input" v-model="data.deliverer" placeholder="请输入配送员" />
			</view> -->
			<view class="form-wrap more-wrap">
				<text class="label">配送员</text>
				<picker @change="deliverChange" :value="deliver_index" :range="deliverArray" class="selected">
					<view class="uni-input">{{ data['deliverer'] }}</view>
				</picker>
				<text class="iconfont iconright"></text>
			</view>

			<view class="form-wrap">
				<view class="label">
					<text class="required color-base-text">* </text>
					<text>手机号</text>
				</view>
				<input class="uni-input" v-model="data.deliverer_mobile" type="number" placeholder="请输入手机号" />
			</view>
		</view>
		<view class="footer-wrap"><button type="primary" @click="save()">确定</button></view>
		<loading-cover ref="loadingCover"></loading-cover>
	</view>
</template>

<script>
	import {getOrderInfoById,orderLocalorderDelivery} from '@/api/order'
	export default {
		data() {
			return {
				order: {},
				repeatFlag: false,
				data: {
					order_id: 0,
					deliver_id: 0,
					deliverer: '请选择',
					deliverer_mobile: ''
				},
				deliverArray: [],
				deliver_index: 0
			};
		},
		onLoad(option) {
			this.data.order_id = option.order_id || 0;
			this.getOrderInfo();
		},
		onShow() {},
		methods: {
			/**
			 * 配送员选择
			 * @param {Object} e
			 */
			deliverChange(e) {
				if (this.deliverArray.length == 0) return;
				this.deliver_index = e.target.value;
				this.data.deliver_id = this.order['deliver_list'][this.deliver_index].deliver_id;
				this.data.deliverer = this.order['deliver_list'][this.deliver_index].deliver_name;
				this.data.deliverer_mobile = this.order['deliver_list'][this.deliver_index].deliver_mobile;
			},
			getOrderInfo() {
				getOrderInfoById(this.data.order_id).then(res=>{
					if (res.code == 0) {
						this.order = res.data;
						if (this.order.deliver_list) {
							this.order.deliver_list.forEach((item, key) => {
								this.deliverArray.push(item.deliver_name);
							});
						}
						if (this.$refs.loadingCover) this.$refs.loadingCover.hide();
					} else {
						this.$util.showToast({
							title: res.message
						});
						setTimeout(() => {
							uni.navigateBack({
								delta: 1
							});
						}, 1000);
					}
				});
			},
			save() {
				if (this.data.deliver_id == 0) {
					this.$util.showToast({
						title: '请选择配送员'
					});
					return;
				}
				if (this.data.deliverer_mobile == 0) {
					this.$util.showToast({
						title: '请输入手机号'
					});
					return;
				}

				if (this.repeatFlag) return;
				this.repeatFlag = true;

				orderLocalorderDelivery(this.data).then(res=>{
					if (res.code == 0) {
						setTimeout(() => {
							uni.navigateBack({
								delta: 1
							});
						}, 1000);
					} else {
						this.repeatFlag = false;
					}
					this.$util.showToast({
						title: res.message
					});
				});
			}
		}
	};
</script>

<style lang="scss">
	.item-wrap {
		background: #fff;
		margin-top: $margin-updown;

		.form-wrap {
			display: flex;
			align-items: center;
			margin: 0 $margin-both;
			border-bottom: 1px solid $color-line;
			height: 100rpx;
			line-height: 100rpx;

			&:last-child {
				border-bottom: none;
			}

			.label {
				vertical-align: middle;
				margin-right: $margin-both;
			}

			.value {
				vertical-align: middle;
				display: inline-block;
				flex: 1;
				text-align: right;
				overflow: hidden;
				text-overflow: ellipsis;
				white-space: pre;
			}

			input {
				vertical-align: middle;
				display: inline-block;
				flex: 1;
				text-align: right;
			}

			.selected {
				vertical-align: middle;
				display: inline-block;
				flex: 1;
				text-align: right;
				color: #909399;
				overflow: hidden;
				white-space: pre;
				text-overflow: ellipsis;
			}
		}
	}

	.footer-wrap {
		width: 100%;
		padding: 40rpx 0;
	}
</style>
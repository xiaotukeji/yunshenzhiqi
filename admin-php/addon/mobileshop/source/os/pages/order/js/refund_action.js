import {getOrderRefundInfoById,closeOrderRefund} from '@/api/order_refund'
export default {
	data() {
		return {
			orderGoodsId: 0,
			isIphoneX: false,
			detail: {},
			orderInfo: {},
			actionCallback: null,
			repeatFlag: false,
			refundRealMoney: '',
			refundTypeArray: ['原路退款','线下退款','退款到余额'],
			refundType: 0
		};
	},
	onLoad(option) {
		this.orderGoodsId = option.order_goods_id || 0;
	},
	onShow() {
		if (!this.$util.checkToken('/pages/order/refund/detail?order_goods_id=' + this.orderGoodsId)) return;
		this.getOrderDetail();
		this.isIphoneX = this.$util.uniappIsIPhoneX();
		this.actionCallback = () => {
			this.getOrderDetail();
		};
	},
	methods: {
		getOrderDetail() {
			getOrderRefundInfoById(this.orderGoodsId).then(res=>{
				if (res.code == 0) {
					var data = res.data;
					this.detail = data.detail;
					this.detail.refund_images = data.detail.refund_images ? data.detail.refund_images.split(',') : '';
					this.refundRealMoney = data.detail.refund_apply_money;
					if (this.detail.refund_address == '') {
						var shopInfo = uni.getStorageSync('shop_info') ? JSON.parse(uni.getStorageSync('shop_info')) : {};
						this.detail.refund_address = '商家未设置联系地址';
						if (shopInfo.full_address || shopInfo.address) {
							this.detail.refund_address = shopInfo.full_address + ' ' + shopInfo.address;
						}
					}
					this.orderInfo = data.order_info;
					this.detail.sku_spec_format = this.detail.sku_spec_format ? JSON.parse(this.detail.sku_spec_format) : [];
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
		cancel() {
			uni.navigateBack({
				delta: 1
			});
		},
		// 拒绝
		orderRefundRefuse(order_goods_id) {
			this.$util.redirectTo("/pages/order/refund/refuse", {
				order_goods_id
			});
		},
		// 同意
		orderRefundAgree(order_goods_id) {
			this.$util.redirectTo("/pages/order/refund/agree", {
				order_goods_id
			});
		},
		// 收货
		orderRefundTakeDelivery(order_goods_id) {
			this.$util.redirectTo("/pages/order/refund/take_delivery", {
				order_goods_id
			});
		},
		// 转账
		orderRefundTransfer(order_goods_id) {
			this.$util.redirectTo("/pages/order/refund/transfer", {
				order_goods_id
			});
		},
		// 关闭维权
		orderRefundClose(order_goods_id) {
			uni.showModal({
				title: '提示',
				content: '确定要关闭本次维权吗？',
				success: res => {
					if (res.confirm) {
						closeOrderRefund(order_goods_id).then(res=>{
							if (res.code >= 0) {
								this.$util.showToast({
									title: '维权已关闭'
								});
								if (this.actionCallback) this.actionCallback();
							} else {
								this.$util.showToast({
									title: res.message
								});
							}
						})
					}
				}
			})
		},
		refundTypeChange(e) {
			this.refundType = e.detail.value;
		},
		previewRefundImage(index){
			uni.previewImage({
				current: index,
				urls: this.detail.refund_images,
			});
		}
	}
}
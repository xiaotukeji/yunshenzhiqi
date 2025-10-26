import {getOrderDetailInfoById,getOrderLog} from '@/api/order'
export default {
	data() {
		return {
			isIphoneX: false,
			orderId: 0,
			template: '',
			orderDetail: {
				order_status_action: {
					icon: ''
				},
				virtual_goods: {}
			},
			tempOrder: {
				remark: ''
			},
			log: []
		};
	},
	onLoad(option) {
		this.orderId = option.order_id || 0;
		this.template = option.template || 'basis';
	},
	onShow() {
		var url = '/pages/order/detail/' + this.template + '?order_id=' + this.orderId + '&template=' + this.template;
		if (!this.$util.checkToken(url)) return;

		this.getOrderDetail();
		this.isIphoneX = this.$util.uniappIsIPhoneX();
		this.actionCallback = () => {
			this.getOrderDetail();
		};
		this.getLogList();
	},
	methods: {
		getOrderDetail() {
			getOrderDetailInfoById(this.orderId).then(res=>{
				if (res.code == 0) {
					this.orderDetail = res.data;
					this.orderDetail.order_status_action = JSON.parse(this.orderDetail.order_status_action);
					this.orderDetail.order_goods.forEach(v => {
						v.sku_spec_format = v.sku_spec_format ? JSON.parse(v.sku_spec_format) : [];
					});
					if (this.$refs.loadingCover) this.$refs.loadingCover.hide();
				} else {
					this.$util.showToast({
						title: res.message
					});
					setTimeout(() => {
						this.$util.redirectTo('/pages/order/list', {}, 'redirectTo');
					}, 1000);
				}
			});
		},
		getLogList() {
			getOrderLog(this.orderId).then(res=>{
				if (res.code >= 0 && res.data) {
					this.log = res.data;
				}
			});
		},
		imgError(index) {
			this.orderDetail.order_goods[index].sku_image = this.$util.getDefaultImage().default_goods_img;
			this.$forceUpdate();
		},
		// 卖家备注
		orderRemark() {
			this.tempOrder = JSON.parse(JSON.stringify(this.orderDetail));
			this.$refs.orderRemark.show(() => {
				this.getOrderDetail();
			});
		},
		orderAction(action, order_id) {
			try {
				this[action](order_id);
			} catch (e) {
				console.log('orderAction error：', e);
			}
		},
		trace() {
			this.$util.redirectTo('/pages/order/logistics', {
				order_id: this.orderId
			});
		}
	},
	filters: {
		abs(value) {
			return Math.abs(parseFloat(value)).toFixed(2);
		}
	}
}

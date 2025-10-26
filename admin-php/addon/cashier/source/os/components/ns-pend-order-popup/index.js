import { editPendOrderRemark, deletePendOrder, getPendOrderList } from '@/api/pendorder.js';
import { getMemberInfoById } from '@/api/member.js'
import {mapGetters} from 'vuex';

export default {
	name: 'nsPendOrder',
	data() {
		return {
			orderData: {
				page: 0,
				total: 1,
				list: []
			},
			remark: '',
			index: -1,
			orderId: 0,
			isRepeat: false,
			height: ''
		};
	},
	computed: {
		...mapGetters(['pendOrderNum'])
	},
	created() {
		this.getOrder();
	},
	mounted() {
		this.setHeight();
	},
	methods: {
		open() {
			this.$refs.pendOrderPop.open();
		},
		getOrder(page = null) {
			if (page === 0) this.orderData.page = page;
			this.orderId = 0;

			if (this.orderData.page + 1 > this.orderData.total) return;

			this.orderData.page++;

			getPendOrderList({ page: this.orderData.page }).then(res => {
				if (res.code == 0) {
					if (this.orderData.page == 1) this.orderData.list = [];
					this.$store.commit('billing/setPendOrderNum', res.data.count);
					if (res.data.list.length) {
						this.orderData.total = res.data.page_count;
						this.orderData.list = this.orderData.list.concat(res.data.list);
					} else {
						this.orderData.total = 1;
					}
					this.setHeight();
				}
			});
		},
		deleteOrder(order_id) {
			if (this.isRepeat) return;
			this.isRepeat = true;
			deletePendOrder(order_id).then(res => {
				if (res.code == 0) {
					this.isRepeat = false;
					this.getOrder(0);
				}
			});
		},
		remarkConfirm() {
			let data = this.orderData.list[this.index];
			editPendOrderRemark({
				order_id: data.order_id,
				remark: this.remark
			}).then(res => {
				if (res.code == 0) {
					this.orderData.list[this.index].remark = this.remark;
					this.$refs.remarkPopup.close();
				} else {
					this.$util.showToast({
						title: '操作失败'
					});
				}
			})
		},
		remarkSetting(data, index) {
			this.index = index;
			this.remark = data.remark;
			this.$refs.remarkPopup.open();
		},
		async takeOrder(data) {
			this.orderId = data.order_id;
			//获取挂单数据的会员信息
			if (data.member_id) {
				let res = await getMemberInfoById(data.member_id);
				if (res.code == 0 && res.data) {
					this.$store.commit('app/setGlobalMemberInfo', res.data);
				} else {
					this.$store.commit('app/setGlobalMemberInfo', null);
				}
			}
			//取出挂单数据设置到展示列表
			let goodsData = {};
			data.order_goods.forEach(item => {
				if (item.goods_class == 'money') item.money = item.price;
				//item.is_adjust = true;
				var key = 'sku_' + item.sku_id;
				if (item.goods_class == 4 || item.goods_class == 6) {
					var index = 0;
					Object.keys(goodsData).forEach(k => {
						if (k.indexOf(key) != -1) {
							index++;
						}
					});
					key += '_' + index;
				}
				goodsData[key] = item;
			});
			this.$store.commit('billing/setPendOrderId', data.order_id);
			this.$store.commit('billing/setPendOrderNum', this.pendOrderNum - 1);
			this.$store.commit('billing/setGoodsData', goodsData);
			this.$store.commit('billing/setOrderData', {
				goods_list: [],
				remark: data.remark
			});

			this.$store.commit('billing/setActive', 'SelectGoodsAfter');
			this.$refs.pendOrderPop.close();
		},
		switchStoreAfter() {
			this.orderData = {
				page: 0,
				total: 1,
				list: []
			};
			this.getOrder();
		},
		setHeight() {
			this.$nextTick(() => {
				const query = uni.createSelectorQuery()
					// #ifndef MP-ALIPAY
					.in(this)
				// #endif
				query.selectViewport().scrollOffset(data => {
					this.height = (data.scrollHeight - 51 - 67 - 15) / 100 + 'rem';
				}).exec();
			});
		}
	}
};
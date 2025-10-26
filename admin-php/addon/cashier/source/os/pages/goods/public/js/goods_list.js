import {
	setGoodsStatus,
	getGoodsDetail,
} from '@/api/goods.js';

export default {
	data() {
		return {
			page: 1, // 初始是请求第几页
			page_size: 20, // 每次返回数据数
			goodsDetail: null, // 商品详情数据
			skuList: [], // 弹窗中所需要获取到的sku列表数据
			status: '',
			disabled:false,
			goods_class: '',
			goodsId: '',
			option: {
				page_size: 9,
				search_text: '',
				sku_no: '',
				goods_class: 'all',
				status: 'all',
				start_price: '',
				end_price: ''
			},
			goodsClass: [{
				value: 1,
				label: '实物商品'
			}, {
				value: 4,
				label: '服务项目'
			}, {
				value: 5,
				label: '卡项套餐'
			}, {
				value: 6,
				label: '称重商品'
			}],
			statusList: [{
				value: 1,
				label: '仓库中'
			}, {
				value: 2,
				label: '销售中'
			}],
			cols: [{
				width: 6,
				align: 'center',
				checkbox: true
			}, {
				field: 'account_data',
				width: 20,
				title: '商品信息',
				align: 'left',
				templet: data => {
					let img = this.$util.img(data.goods_image,{size: 'small'});
					let html = `
							<view class="goods-content">
								<image class="goods-img" src="${img}" mode="aspectFit"/>
								<text class="goods-name multi-hidden">${data.goods_name}</text>
							</view>
						`;
					return html;
				}
			}, {
				width: 14,
				title: '商品类型',
				align: 'center',
				field: 'goods_class_name'
			}, {
				width: 10,
				title: '价格',
				align: 'center',
				templet: function(data) {
					return '￥' + data.discount_price;
				}
			}, {
				field: 'stock',
				width: 15,
				title: '库存',
				align: 'center'
			}, {
				width: 15,
				title: '售卖模式',
				templet: data => {
					return (this.globalStoreInfo.stock_type == 'store' ? '独立库存' : '统一库存') + ' | ' + (data
						.is_unify_price ? '统一设价' : '独立设价');
				}
			}, {
				width: 15,
				title: '状态',
				align: 'center',
				templet: function(data) {
					var str = '';
					if (data.store_status == 1) {
						str = '销售中';
					} else if (data.store_status == 0) {
						str = '仓库中';
					}
					return str;
				}
			}, {
				width: 20,
				title: '操作',
				align: 'right',
				action: true
			}],
		};
	},
	onLoad() {},
	computed: {
		syncWeighGoods() {
			try {
				return this.addon.includes('weighgoods') && this.addon.includes('scale') && (window.POS_ || window
					.ipcRenderer);
			} catch (e) {
				return false
			}
		}
	},
	methods: {
		selectClass(index) {
			this.goods_class = index == -1 ? '' : this.goodsClass[index].value.toString();
		},
		selectStatus(index) {
			this.status = index == -1 ? '' : this.statusList[index].value.toString();
		},
		// 搜索商品
		searchFn() {
			if (this.status == 1) {
				this.option.status = 0
			} else if (this.status == 2) {
				this.option.status = 1
			} else {
				this.option.status = 'all'
			}
			this.option.goods_class = this.goods_class ? this.goods_class : 'all';
			this.$refs.goodsListTable.load({
				page: 1
			});
		},
		resetFn() {
			this.status = '';
			this.goods_class = '';
			this.option.status = 'all';
			this.option.goods_class = 'all';
			this.option.search_text = '';
			this.option.sku_no = '';
			this.option.start_price = '';
			this.option.end_price = '';
			this.$refs.goodsListTable.load({
				page: 1
			});
		},
		// 查询商品详情
		getDetail(id, type = '') {
			uni.showLoading({
				title: '加载中'
			});
			this.goodsDetail = null;

			getGoodsDetail(id).then(res => {
				if (res.code >= 0) {
					this.goodsDetail = res.data;
					this.goodsDetail.sku_list[0].goods_name = this.goodsDetail.goods_name;
					this.skuList = this.goodsDetail.sku_list;
					if (!type) {
						this.$refs.goodsDetail.open();
					} else {
						this.$refs.goodsSku.open();
					}
				}
				uni.hideLoading();
			})
		},
		goodsStatus(id, status) {
			let arr;
			if (typeof id == 'object') {
				arr = [];
				id.value.length &&
					id.value.forEach((item, index) => {
						arr.push(item.goods_id);
					});

				if (!arr.length) {
					this.$util.showToast({
						title: '请选择要操作的数据'
					});
					return false;
				}
			} else arr = id;
			let data = {
				goods_id: arr.toString(),
				status: status
			};
			setGoodsStatus(data).then(res => {
				this.$util.showToast({
					title: res.message
				});
				if (res.code >= 0) {
					this.$refs.goodsListTable.load();
				}
			})
		},
		goodsSku(id) {
			this.disabled = false
			this.goodsId = id;
			this.getDetail(id, 'goodsSku');
		},
		isDeliveryRestrictions(id){
			this.disabled = true
			this.goodsId = id;
			this.getDetail(id, 'goodsSku');
		},
		// 日志记录弹窗
		recordopen(id) {
			this.goodsId = id;
			this.$refs.record.open();
		},
		// 弹窗关闭
		close(type) {
			this.$refs[type].close();
		},
		synchronous() {
			this.$refs.scaleGoods.open();
		},
		printPriceTag(){
			this.$util.redirectTo('/pages/goods/print_price_tag');
		},
	}
}
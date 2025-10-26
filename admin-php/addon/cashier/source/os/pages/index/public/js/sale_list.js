import {
	saleGoodsExport
} from '@/api/shifts.js';
export default {
	data() {
		return {
			page: 1, // 初始是请求第几页
			page_size: 20, // 每次返回数据数
			option: {
				sale_channel:'all',
				sku_name:'',
				record_id:''
			},
			sale_channel_list: [{
				value: 'online',
				label: '线上'
			}, {
				value: 'offline',
				label: '线下'
			}],
			cols: [{
				field: 'account_data',
				width: 25,
				title: '商品信息',
				align: 'left',
				templet: data => {
					let img = data.goods_class != 'recharge' ? this.$util.img(data.sku_image,{size:'small'}) : this.$util.img(data.sku_image);
					let html = `
							<view class="goods-content">
								<image class="goods-img" src="${img}" mode="aspectFit"/>
								<view class="infos">
									<text class="goods-name multi-hidden">${data.goods_name}</text>
									<text class="spec-name multi-hidden">${data.spec_name}</text>
								</view>
							</view>
						`;
					return html;
				}
			}, {
				width: 15,
				title: '总数量',
				align: 'center',
				field: 'num'
			}, {
				width: 15,
				title: '平均销售价',
				align: 'center',
				field: 'price'
			}, {
				width: 15,
				title: '销售总额',
				align: 'center',
				field: 'goods_money'
			}, {
				width: 15,
				title: '线下销售',
				align: 'center',
				field: 'offline_num'
			}, {
				width: 15,
				title: '线上销售',
				align: 'center',
				field: 'online_num'
			}],
		};
	},
	onLoad(option) {
		this.option.record_id = option.id || '';
	},
	computed: {},
	methods: {
		exportSalelist(){
			saleGoodsExport(this.option).then(res => {
				if (res.code == 0) {
					window.open(this.$util.img(res.data.path));
				}else{
					this.$util.showToast({
						title: res.message
					});
				}
			});
		},
		selectChannel(index){
			this.option.sale_channel = index == -1 ? '' : this.sale_channel_list[index].value.toString();
		},
		searchFn(){
			this.$refs.saleListTable.load({
				page: 1
			});
		},
		resetFn(){
			this.option.sale_channel = '';
			this.option.sku_name = '';
		},
		backFn() {
			this.$util.redirectTo('/pages/index/change_shiftsrecord');
		},
		detail(data){
			if(window.location.origin.indexOf('localhost') != -1){
				window.open(window.location.origin+'/cashregister/pages/order/orderlist?order_no='+data.value.order_no,'_blank');
			}else{
				window.open(this.$config.baseUrl+'/cashregister/pages/order/orderlist?order_no='+data.value.order_no);
			}
		},
	}
}
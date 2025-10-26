import {
	getMemberCardList
} from '@/api/member'
import {mapGetters} from 'vuex';

export default {
	data() {
		return {
			memberCardData: {
				page: 0,
				total: 1,
				list: [],
				index: 0,
				currData: {},
				selected: {}
			},
			itemNum: 1
		}
	},
	computed: {
		...mapGetters(['billingGoodsData'])
	},
	methods: {
		open() {
			this.$refs.memberCardPopup.open();
			this.memberCardData.page = 0;
			this.memberCardData.index = 0;
			this.memberCardData.list = [];
			this.memberCardData.currData = {};
			this.memberCardData.selected = {};
			this.getMemberCard()
		},
		// 获取会员项目
		getMemberCard() {
			if (this.memberCardData.page + 1 > this.memberCardData.total) return;
			this.memberCardData.page += 1;
			getMemberCardList({
				status: 1,
				page: this.memberCardData.page,
				member_id: this.globalMemberInfo.member_id
			}).then(res => {
				if (res.code == 0) {
					this.memberCardData.total = res.data.page_count || 1;
					Object.values(this.billingGoodsData).forEach(data => {
						if (data.card_id) {
							res.data.list.forEach((card)=>{
								if(card.card_id == data.card_id){
									// 通用卡：选择商品，总数量发生变化
									if (data.card_type == 'commoncard') {
										card.total_use_num += data.num;
									} else if (data.card_type == 'oncecard') {
										// 限次卡：选择商品后，商品和总数量都要发生变化
										card.total_use_num += data.num;
										card.item_list.forEach((card_item)=>{
											if(card_item.item_id == data.item_id){
												card_item.use_num += data.num;
											}
										})
									}
								}
							})
						}
					});
					if (res.data.list.length) this.memberCardData.list = this.memberCardData.list.concat(res.data.list);
					if (this.memberCardData.page == 1) {
						// 默认展示第一个卡项信息
						if (res.data.count) this.selectMemberCard(this.memberCardData.list[0], 0);
					}
				}
			})
		},
		/**
		 * 选择会员套餐
		 * @param {Object} data
		 * @param {Object} index
		 */
		selectMemberCard(data, index) {
			this.memberCardData.index = index;
			this.memberCardData.currData = this.$util.deepClone(data);
			this.memberCardData.selected = {};
		},
		/**
		 * 选择会员套餐商品项
		 * @param {Object} data
		 * @param {Object} index
		 */
		selectMemberCardItem(data, index) {
			if (this.memberCardData.selected['item_' + data.item_id]) {
				if (data.card_type == 'commoncard') {
					this.memberCardData.currData.total_use_num -= this.memberCardData.selected['item_' + data.item_id].input_num;
				}
				delete this.memberCardData.selected['item_' + data.item_id];
			} else {
				if (!this.checkStatus(data)) return;
				this.memberCardData.selected['item_' + data.item_id] = this.$util.deepClone(data);
				this.memberCardData.selected['item_' + data.item_id].input_num = 1;
				this.memberCardData.selected['item_' + data.item_id].index = index;
				this.memberCardData.selected['item_' + data.item_id].card_name = this.memberCardData.currData.goods_name;
				if (data.card_type == 'commoncard') {
					this.memberCardData.currData.total_use_num += 1;
				}
			}

			this.$forceUpdate();
		},
		/**
		 * 加入购物车
		 */
		selectGoods() {
			if (!Object.keys(this.memberCardData.selected).length) {
				this.$util.showToast({
					title: '请选择服务/商品',
				});
				return;
			}

			let billingGoodsData = this.$util.deepClone(this.billingGoodsData);
			let billingGoodsKeys = Object.keys(billingGoodsData);
			Object.keys(this.memberCardData.selected).forEach((key) => {
				
				let data = this.memberCardData.selected[key];
				data.card_index = this.memberCardData.index;
				this.memberCardData.list[this.memberCardData.index].total_use_num += data.input_num;
				this.memberCardData.list[this.memberCardData.index].item_list[data.index].use_num += data.input_num;
				this.memberCardData.currData.item_list[data.index].use_num += data.input_num;
				//服务商品每个都是一个订单项，需要循环处理
				if(data.goods_class == this.$util.goodsClassDict.service){
					let addNum = 0;
					Object.values(billingGoodsData).forEach((item)=>{
						if(item.sku_id == data.sku_id){
							addNum ++;
						}
					})
					data.num = 1;
					for(let num = 1;num <= data.input_num;num ++){
						let skuKey = 'sku_' + data.sku_id + '_item_' + data.item_id + '_' + addNum;
						billingGoodsData[skuKey] = this.$util.deepClone(data);
						addNum ++;
					}
				}else{
					data.num = data.input_num;
					let skuKey = 'sku_' + data.sku_id + '_item_' + data.item_id;
					if(billingGoodsData.hasOwnProperty(skuKey)){
						data.num += billingGoodsData[skuKey].num;
					}
					billingGoodsData[skuKey] = this.$util.deepClone(data);
				}
			});
			
			this.$store.commit('billing/setGoodsData', billingGoodsData);

			this.memberCardData.selected = {};
		},
		/**
		 * 数量减
		 * @param {Object} data
		 */
		itemDec(data) {
			let currData = this.memberCardData.currData;
			if (this.memberCardData.selected['item_' + data.item_id].input_num > 1) {
				this.memberCardData.selected['item_' + data.item_id].input_num -= 1;
				if (data.card_type == 'commoncard') {
					currData.total_use_num -= 1;
				}
				this.$forceUpdate();
			}
		},
		/**
		 * 数量加
		 * @param {Object} data
		 */
		itemInc(data) {
			let currData = this.memberCardData.currData;
			if (data.card_type == 'commoncard') {
				if ((currData.total_num - currData.total_use_num - 1) < 0) return;
			} else if (data.card_type == 'oncecard') {
				if ((data.num - data.use_num - this.memberCardData.selected['item_' + data.item_id].input_num - 1) < 0) return;
			}
			if (data.card_type == 'commoncard') {
				currData.total_use_num += 1;
			}
			this.memberCardData.selected['item_' + data.item_id].input_num += 1;
			this.$forceUpdate();
		},
		checkStatus(data) {
			let currData = this.memberCardData.currData;
			if (data.card_type == 'commoncard') {
				return currData.total_num > currData.total_use_num;
			} else if (data.card_type == 'oncecard') {
				return data.num > data.use_num;
			}
			return true;
		}
	}
}
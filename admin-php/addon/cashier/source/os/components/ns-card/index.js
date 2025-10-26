import {getCardList} from '@/api/card.js';
import {mapGetters} from 'vuex';

export default {
	name: 'nsCard',
	props: {
		type: {
			type: String,
			default: 'oncecard'
		}
	},
	data() {
		return {
			goodsType: '',
			pageSize: 35,
			onceCardData: {
				page: 0,
				total: 1,
				list: []
			},
			timeCardData: {
				page: 0,
				total: 1,
				list: []
			},
			commonCardData: {
				page: 0,
				total: 1,
				list: []
			},
			itemNum: 3,
			mediaQueryOb: null,
			selectCardSkuId: [],
			isLoad: false
		};
	},
	created() {
		this.goodsType = this.type;
		this.init();
	},
	computed: {
		...mapGetters(['buyCardGoodsData'])
	},
	watch: {
		buyCardGoodsData: {
			// 每个属性值发生变化就会调用这个函数
			handler(newVal, oldVal) {
				this.selectCardSkuId = [];
				if(!Object.values(this.buyCardGoodsData).length) return false;
				Object.values(this.buyCardGoodsData).forEach((item,index)=>{
					this.selectCardSkuId.push(item.sku_id);
				});
			},
			// 深度监听 属性的变化
			deep: true
		}
	},
	mounted() {
		this.mediaQueryOb = uni.createMediaQueryObserver(this);

		this.mediaQueryOb.observe({maxWidth: 1500}, matches => {
			if (matches) this.itemNum = 2;
		});

		this.mediaQueryOb.observe({minWidth: 1501, maxWidth: 1700}, matches => {
			if (matches) this.itemNum = 3;
		});

		this.mediaQueryOb.observe({minWidth: 1701}, matches => {
			if (matches) this.itemNum = 4;
		});
	},
	destroyed() {
		this.mediaQueryOb.disconnect();
	},
	methods: {
		init() {
			this.isLoad = false;
			this.onceCardData.page = 0;
			this.timeCardData.page = 0;
			this.commonCardData.page = 0;
			this.getOnceCard();
			this.getTimeCard();
			this.getCommonCard();
		},
		switchGoodsType(type) {
			this.goodsType = type;
		},
		//卡项相关
		goodsSelect(data) {
			if (data.stock <= 0) return;

			let _buyCardGoodsData = this.$util.deepClone(this.buyCardGoodsData);

			if (_buyCardGoodsData['sku_' + data.sku_id]) {
				_buyCardGoodsData['sku_' + data.sku_id].num += 1;
			} else {
				_buyCardGoodsData['sku_' + data.sku_id] = data;
				_buyCardGoodsData['sku_' + data.sku_id].num = 1;
			}
			this.$store.commit('buycard/setGoodsData', _buyCardGoodsData);
			this.$store.commit('buycard/setActive', 'SelectGoodsAfter');
		},
		getOnceCard() {
			this.isLoad = false;
			if (this.onceCardData.page + 1 > this.onceCardData.total) return;
			this.onceCardData.page += 1;
			getCardList({
				page: this.onceCardData.page,
				page_size: this.pageSize,
				card_type: 'oncecard',
				goods_state: 1,
				status: 1,
			}).then(res => {
				if (res.code == 0) {
					this.isLoad = true;
					this.onceCardData.total = res.data.page_count || 1;
					if (this.onceCardData.page == 1) this.onceCardData.list = [];
					if (res.data.list.length) this.onceCardData.list = this.onceCardData.list.concat(res.data.list);
				}
			});
		},
		getTimeCard() {
			if (this.timeCardData.page + 1 > this.timeCardData.total) return;
			this.timeCardData.page += 1;
			getCardList({
				page: this.timeCardData.page,
				card_type: 'timecard',
				goods_state: 1,
				page_size: this.pageSize,
				status: 1,
			}).then(res => {
				if (res.code == 0) {
					this.timeCardData.total = res.data.page_count || 1;
					if (this.timeCardData.page == 1) this.timeCardData.list = [];
					if (res.data.list.length) this.timeCardData.list = this.timeCardData.list.concat(
						res.data.list);
				}
			});
		},
		getCommonCard() {
			if (this.commonCardData.page + 1 > this.commonCardData.total) return;
			this.commonCardData.page += 1;
			getCardList({
				page: this.commonCardData.page,
				card_type: 'commoncard',
				goods_state: 1,
				page_size: this.pageSize,
				status: 1,
			}).then(res => {
				if (res.code == 0) {
					this.commonCardData.total = res.data.page_count || 1;
					if (this.commonCardData.page == 1) this.commonCardData.list = [];
					if (res.data.list.length) this.commonCardData.list = this.commonCardData.list.concat(res.data.list);
				}
			});
		},
	}
};
import {getCondition,getGoodsLists,deleteGoods,onGoods,offGoods,getVerifyStateRemark,copyGoods} from '@/api/goods'
import { Weixin } from 'common/js/wx-jssdk.js';
export default {
	data() {
		return {
			statusList: [{
					id: 0,
					name: '销售中',
					goods_state: 1,
					verify_state: 1,
				},
				{
					id: 1,
					name: '仓库中',
					goods_state: 0,
					verify_state: 1,
				},
				{
					id: 2,
					name: '预警中',
					goods_state: '',
					stockalarm: 1,
					verify_state: 1
				}
			],
			status: -1,
			dataList: [],
			searchGoodsName: '',
			showScreen: false,
			goodsCondition: [],
			goodsConditionCurr: {
				goods_promotion_type: 0,
				goods_class: 0
			},
			goodsClass: [{
					name: '全部',
					type: ''
				},
				{
					name: '实物商品',
					type: 1
				},
				{
					name: '虚拟商品',
					type: 2
				}
			],
			formData: {
				search_text: '',
				promotion_type: '',
				start_sale: '',
				end_sale: '',
				goods_class: ""
			}
		};
	},
	onShow() {
		let status = uni.getStorageSync('status');
		if (status) {
			this.status = status;
			uni.removeStorageSync("status");
		}
		if (!this.$util.checkToken('/pages/goods/list')) return;
		this.$store.dispatch('getShopInfo');
		if (this.mescroll) this.mescroll.resetUpScroll();
		this.getConditionFn();
	},
	onLoad(){
		if (this.addonIsExit.virtualcard) {
			this.goodsClass.push({name: '卡密商品', type: 3})
		}
		if (this.addonIsExit.cardservice) {
			this.goodsClass.push({name: '服务项目', type: 4}, {name: '卡项套餐', type: 5})
		}
	},
	methods: {
		showHide(val) {
			val.is_off = !val.is_off;
		},
		shwoOperation(item = '') {
			let stop = false;
			this.dataList.forEach(v => {
				if (v.is_off == 1) {
					stop = true;
				}
				v.is_off = 0;
			});
			if (!stop && item != '') item.is_off = 1;
		},
		tabChange(status = -1) {
			this.status = status;
			this.mescroll.resetUpScroll();
		},
		getConditionFn() {
			getCondition().then(res=>{
				let data = res.data;
				if (res.code == 0 && data) {
					for (let index in data) {
						let arr = [{
							name: "全部",
							type: ''
						}];
						for (let index_c in data[index]) {
							arr.push(data[index][index_c]);
						}
						data[index] = arr;
					}
					this.goodsCondition = data;
				}
			});
		},
		getListData(mescroll) {
			let data = {
				page_size: mescroll.size,
				page: mescroll.num,
				search_text: this.searchGoodsName
			};

			if (this.status != -1 && this.statusList[this.status].verify_state !== '') data.verify_state = this.statusList[this.status].verify_state;
			if (this.status != -1 && this.statusList[this.status].goods_state !== '') data.goods_state = this.statusList[this.status].goods_state;
			if (this.status != -1 && this.statusList[this.status].stockalarm != '' && this.statusList[this.status].stockalarm != undefined) data.stockalarm = this.statusList[this.status].stockalarm;
			data = Object.assign(data, this.formData);
			this.mescroll = mescroll;
			getGoodsLists(data).then(res=>{
				let newArr = [];
					let msg = res.message;
					if (res.code == 0 && res.data) {
						newArr = res.data.list;
					} else {
						this.$util.showToast({
							title: msg
						});
					}
					mescroll.endSuccess(newArr.length);
					//设置列表数据
					if (mescroll.num == 1) this.dataList = []; //如果是第一页需手动制空列表
					newArr.forEach(v => {
						v.is_off = 0;
					});
					this.dataList = this.dataList.concat(newArr); //追加新数据
					if (this.$refs.loadingCover) this.$refs.loadingCover.hide();
			});
		},
		deleteGoodsFn(item) {
			uni.showModal({
				title: '删除',
				content: '删除后进入回收站，确定删除吗?',
				success: res => {
					item.is_off = 0;
					if (res.confirm) {
						deleteGoods(item.goods_id).then(res=>{
							this.$util.showToast({
								title: res.message
							});
							if (res.code == 0) {
								this.mescroll.resetUpScroll();
							}
						});
					}
				}
			});
		},
		offGoodsFn(item) {
			item.is_off = 0;
			offGoods({
				goods_state: 0,
				goods_ids: item.goods_id
			}).then(res=>{
				this.$util.showToast({
					title: res.message
				});
				if (res.code == 0) {
					this.mescroll.resetUpScroll();
				}
			});
		},
		onGoodsFn(item) {
			item.is_off = 0;
			onGoods({
				goods_state: 1,
				goods_ids: item.goods_id
			}).then(res=>{
				this.$util.showToast({
					title: res.message
				});
				if (res.code == 0) {
					this.mescroll.resetUpScroll();
				}
			});
		},
		copyGoodsFn(item) {
			uni.showModal({
				title: '复制',
				content: '复制商品会存放在仓库中,确定复制吗',
				success: res => {
					if (res.confirm) {
						copyGoods(item.goods_id).then(res=>{
							if (res.code == 0) {
								this.mescroll.resetUpScroll();
								this.$util.showToast({
									title: '商品已放入仓库中'
								});
							} else {
								this.$util.showToast({
									title: res.message
								});
							}
						});
					}
					item.is_off = 0;
				}
			});
		},
		getVerifyStateRemarkFn(item) {
			getVerifyStateRemark(item.goods_id).then(res=>{
				if (res.code != 0 && !res.data) return false;
				let data = res.data.verify_state_remark ? res.data.verify_state_remark : '暂无违规信息';
				uni.showModal({
					title: '违规原因',
					content: data,
					showCancel: false,
					success: res => {
						item.is_off = 0;
					}
				});
				return false;
			});
		},
		searchGoods() {
			this.mescroll.resetUpScroll();
		},
		linkSkip(item) {
			let data = {};
			if (item) {
				data.goods_id = item.goods_id;
				item.is_off = 0;
			}
			this.$util.redirectTo('/pages/goods/edit/index', data);
		},
		goOutput(item) {
			let data = {};
			if (item) {
				data.goods_id = item.goods_id;
				item.is_off = 0;
			}
			this.$util.redirectTo('/pages/goods/output', data);
		},
		uTag(val, currType, formitem) {
			if (currType == 'goods_class') {
				this.formData[formitem] = this.goodsClass[val].type;
			} else {
				this.formData[formitem] = this.goodsCondition[currType][val].type;
			}
			this.goodsConditionCurr[currType] = val;
		},
		//重置
		resetData() {
			this.formData.search_text = '';
			this.formData.promotion_type = '';
			this.formData.start_sale = '';
			this.formData.end_sale = '';
			this.formData.goods_class = '';
			this.goodsConditionCurr.goods_promotion_type = 0;
			this.goodsConditionCurr.goods_class = 0;
			this.$forceUpdate();
		},
		//数据提交
		screenData() {
			if (this.formData.start_sale && this.formData.end_sale && this.formData.start_sale > this.formData.end_sale) {
				this.$util.showToast({
					title: "最低销量不能大于最高销量"
				})
				return;
			}
			let data = this.formData;
			this.showScreen = false;
			this.$refs.mescroll.refresh();
		},
		imgError(index) {
			this.dataList[index].goods_image = this.$util.getDefaultImage().default_goods_img;
			this.$forceUpdate();
		},
		/*
		*扫码搜索
		*/
		scanCode() {
			// #ifdef MP
			uni.scanCode({
				onlyFromCamera: true,
				success: res => {
					if (res.errMsg == 'scanCode:ok') {
						let code = res.result;
						this.formData.search_text = code
						this.mescroll.resetUpScroll();
					} else {
						this.$util.showToast({
							title: res.errorMsg
						});
					}
				}
			});
			// #endif
		
			// #ifdef H5
			if (this.$util.isWeiXin()) {
				if (uni.getSystemInfoSync().platform == 'ios') {
					var url = uni.getStorageSync('initUrl');
				} else {
					var url = location.href;
				}
				this.$api.sendRequest({
					url: '/wechat/api/wechat/jssdkconfig',
					data: {
						url: url
					},
					success: jssdkRes => {
						if (jssdkRes.code == 0) {
							var wxJS = new Weixin();
							wxJS.init(jssdkRes.data);
							wxJS.scanQRCode(res => {
								if (res.resultStr) {
									this.formData.search_text = res.resultStr.split(',')[1];
									this.mescroll.resetUpScroll();
								}
							},["barCode"]);
						} else {
							this.$util.showToast({
								title: jssdkRes.message
							});
						}
					}
				});
			}else{
				this.$util.showToast({
					title: 'h5在不支持扫码功能'
				});
			}
			// #endif
		},
	}
};

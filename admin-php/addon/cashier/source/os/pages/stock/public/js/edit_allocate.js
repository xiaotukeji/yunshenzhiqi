import {
	getAllotNo,
	getAllocateDetailInEdit,
	getSkuListForStock,
	getStoreLists,
	editAllocate,
	addAllocate
} from '@/api/stock.js'; 

export default {
	data() {
		return {
			params: {
				search_text: '', //产品名称
				temp_store_id: ''
			},
			goodsList: [],
			goodsIdArr: [],
			goodsShow: false,
			totalData: {
				kindsNum: 0,
				price: 0
			},
			isSubmit: false,
			remark: '',
			// 筛选面板的时间
			type: 'in',
			storeName: '出库门店',
			screen: {
				store_id: "",
				remark: '',
				allot_id: "",
				allot_no: "",
				storeList: [],
				startDate: '1998-01-30 00:00:00',
				birthday: '',
				allocateTypeList: [{
					label: '调拨入库',
					value: 'in'
				},
				{
					label: '调拨出库',
					value: 'out'
				}
				]
			},
			dialogVisible: false, //弹框
			inputIndex: -1
		};
	},
	onLoad(option) {
		this.screen.allot_id = option.allot_id || '';
		if (this.screen.allot_id) {
			this.getEditData();
		} else {
			this.getDocumentNo()
		}

	},
	onShow() {
		this.screen.birthday = this.$util.timeFormat(Date.parse(new Date()) / 1000);
		this.getStoreLists()
	},
	watch: {
		goodsIdArr(data) {
			this.calcTotalData();
		},
	},
	methods: {
		getDocumentNo() {//获取单据号
			getAllotNo().then(res => {
				if (res.code >= 0) {
					this.screen.allot_no = res.data
				} else {
					this.$util.showToast({
						title: res.message
					});
				}
			});
		},
		getEditData() {
			// 编辑时获取详情
			getAllocateDetailInEdit(this.screen.allot_id).then(res => {
				if (res.code >= 0 && res.data) {
					this.info = res.data;
					//当前门店id===入库id则是商品入库，门店选择回填为output_store_id出库门店id，否则为商品出库，门店选择回填为input_store_id入库门店id
					this.type = this.globalStoreInfo.store_id == this.info.input_store_id ? 'in' : 'out'
					this.screen.store_id = this.type == 'in' ? this.info.output_store_id : this.info.input_store_id
					this.screen.allot_no = this.info.allot_no
					this.screen.birthday = this.$util.timeFormat(this.info.allot_time)
					this.remark = JSON.parse(JSON.stringify(this.info.remark))
					this.screen.remark = this.info.remark
					for (let sku_id in this.info.goods_list) {
						this.info.goods_list[sku_id].title = this.info.goods_list[sku_id].sku_name
						this.goodsIdArr.push(parseInt(sku_id));
						this.goodsList.push(this.info.goods_list[sku_id]);
					}
				}
			});
		},
		selectAllocateType(id) {
			this.type = id == -1 ? '' : this.screen.allocateTypeList[id].value;
			this.storeName = id == -1 || id == 0 ? '出库门店' : '入库门店';
			this.params.temp_store_id = this.type == 'in' ? this.screen.store_id : '' //当是入库的时候，需要查出库门店的商品
			this.goodsIdArr = []
			this.goodsList = []
		},
		selectStore(id) {
			this.screen.store_id = id == -1 ? '' : this.screen.storeList[id].value;
			if (this.type == 'in') {
				this.goodsIdArr = []
				this.goodsList = []
			}
		},
		changeTime(data) {
			this.screen.birthday = data;
		},
		getGoodsData({detail}, index) { //input回车处理
			this.inputIndex = index
			var data = {
				search: detail ? detail.value : '',
			}
			if (this.type == 'in') data.temp_store_id = this.screen.store_id;
			if (detail && detail.value) {
				getSkuListForStock(data).then(res => {
					if (res.code >= 0 && res.data.length == 1) {
						this.selectGoods(res.data)
					} else if (res.code >= 0) {
						this.params.search_text = detail ? detail.value : ''
						this.dialogVisible = true
					} else {
						this.$util.showToast({
							title: res.message
						});
					}
				});
			} else {
				this.params.search_text = detail ? detail.value : ''
				this.dialogVisible = true
			}
		},
		selectGoods(data) { //选择数据
			data.forEach((el, index) => {
				el.goods_num = 1;
				el.goods_price = 0;
				el.title = el.sku_name + ''
			    //点击或回车行为选择商品后：当为第一行并且展示列表不存在选择的商品push到展示列表，或者不为第一行并且展示列表中不存在时选择的商品除第一条全部push到展示列表
				if (!this.goodsIdArr.includes(el.sku_id)) {
					console.log(111);
					this.goodsIdArr.push(el.sku_id);
					this.goodsList.push(el);
				} else {//只要展示列表存在直接累加
					var elIndex = this.goodsIdArr.indexOf(el.sku_id)
					if(this.params.search_text){
						this.goodsList[elIndex].goods_num = parseFloat(this.goodsList[elIndex].goods_num) + 1
					}
				}
			})
			this.goodsShow = false;
			this.params.search_text = '';
			this.$forceUpdate();
		},
		delGoods(id) {//删除已选择的商品
			this.goodsList.splice(this.goodsIdArr.indexOf(id), 1);
			this.goodsIdArr.splice(this.goodsIdArr.indexOf(id), 1);
		},
		getStoreLists() {
			this.screen.storeList = [];
			getStoreLists().then(res => {
				if (res.code >= 0) {
					let data = res.data;
					for (let i = 0; i < data.length; i++) {
						if (this.globalStoreId != data[i]['store_id']) {
							this.screen.storeList.push({
								'label': data[i]['store_name'],
								'value': data[i]['store_id'].toString()
							});
						}
					}
					if (this.screen.storeList.length > 0) {
						this.screen.store_id = this.screen.storeList[0].value;
						this.params.temp_store_id = this.screen.store_id
					}
				}
			});
		},
		stockOutFn() {
			if (!this.screen.allot_no) {
				this.$util.showToast({
					title: "请输入调拨单号"
				});
				return false;
			}
			if (!this.type) {
				this.$util.showToast({
					title: "请选择调拨方式"
				});
				return false;
			}
			if (!this.screen.store_id) {
				this.$util.showToast({
					title: "请选择出库门店"
				});
				return false;
			}
			if (!this.screen.birthday) {
				this.$util.showToast({
					title: "请选择调拨时间"
				});
				return false;
			}
			if (!this.goodsIdArr.length) {
				this.$util.showToast({
					title: "请选择调拨数据"
				});
				return false;
			}

			// 检测库存是否填写,且提取数据
			let isStock = false;
			let saveData = [];
			try {
				this.goodsList.forEach((item, index) => {
					if (this.goodsIdArr.includes(item.sku_id)) {
						if (!parseFloat(item.goods_num || 0)) {
							isStock = true;
							let toast = "请输入" + item.sku_name + "的调拨数量";
							this.$util.showToast({
								title: toast
							});
							throw new Error('end');
						}
						var obj = {};
						obj.goods_num = item.goods_num;
						obj.goods_price = item.cost_price;
						obj.goods_sku_id = item.sku_id;
						saveData.push(obj);
					}
				})
			} catch (e) {
				if (e.message != "end") throw e;
			}

			if (isStock) return false;
			if (this.isSubmit) return false;
			this.isSubmit = true;
			let save = this.screen.allot_id ? editAllocate : addAllocate
			save({
				allot_type: this.type,
				allot_id: this.screen.allot_id,
				temp_store_id: this.screen.store_id,
				allot_time: this.screen.birthday,
				remark: this.screen.remark,
				allot_no: this.screen.allot_no,
				goods_sku_list: JSON.stringify(saveData)
			}).then(res => {
				this.isSubmit = false;
				this.$util.showToast({
					title: res.message
				});
				if (res.code >= 0) {
					setTimeout(() => {
						this.backFn();
					}, 500);
					this.resetFn();
				}
			});
		},
		backFn() {
			this.$util.redirectTo('/pages/stock/allocate');
		},
		calcTotalData() {//计算商品种类、金额
			this.totalData.kindsNum = 0;
			this.totalData.price = 0;

			this.goodsList.forEach((item, index) => {
				if (this.goodsIdArr.includes(item.sku_id)) {
					this.totalData.price += parseFloat(item.cost_price || 0) * parseFloat(item.goods_num || 1);
				}
			})
			this.totalData.kindsNum = this.goodsIdArr.length;
		},
		resetFn() {
			this.goodsIdArr = [];
			this.goodsShow = false;
			this.totalData.kindsNum = 0;
			this.totalData.price = 0;
		},
		remarkConfirm() {
			this.screen.remark = JSON.parse(JSON.stringify(this.remark))
			this.$refs.remarkPopup.close()
		}
	}
};
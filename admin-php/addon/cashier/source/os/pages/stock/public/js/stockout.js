import {
	getWastageDocumentNo,
	getWastageDetailInEdit,
	getSkuListForStock,
	editWastage
} from '@/api/stock.js';
export default {
	data() {
		return {
			params: {
				search_text: '', //产品名称
			},
			goodsList: [], //已选择数据
			goodsIdArr: [], //已选择数据id
			goodsShow: false,
			totalData: {
				kindsNum: 0,
				price: 0
			},
			screen: {
				document_id: "",
				document_no: "",
				remark: "",
				stock_json: "",
				time: ""
			},
			remark: '',
			isSubmit: false, //提交防抖
			info: null, //详情原始数据
			dialogVisible: false, //弹框
			inputIndex: -1
		};
	},
	onLoad(option) {
		this.screen.document_id = option.document_id || 0;
		this.screen.time = this.$util.timeFormat(Date.parse(new Date()) / 1000);
		if (this.screen.document_id) {
			this.getEditData();
		} else {
			this.getDocumentNo()
		}
	},
	watch: {
		goodsIdArr(data) {
			this.calcTotalData();
		},
	},
	methods: {
		getDocumentNo() {//获取单据号
			getWastageDocumentNo().then(res => {
				if (res.code >= 0) {
					this.screen.document_no = res.data
				} else {
					this.$util.showToast({
						title: res.message
					});
				}
			});
		},
		getEditData() {//编辑时获取详情
			getWastageDetailInEdit(this.screen.document_id).then(res => {
				if (res.code >= 0 && res.data) {
					this.info = res.data;
					this.screen.document_no = this.info.document_no
					this.screen.time = this.$util.timeFormat(this.info.time)
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
		getGoodsData({
			detail
		}, index) { //input回车处理
			this.inputIndex = index
			if (detail && detail.value) {
				getSkuListForStock({
					search: detail ? detail.value : ''
				}).then(res => {
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
		stockOutFn() {
			if (!this.screen.document_no) {
				this.$util.showToast({
					title: "请输入出库单号"
				});
				return false;
			}
			if (!this.goodsIdArr.length) {
				this.$util.showToast({
					title: "请选择出库数据"
				});
				return false;
			}
			if (this.globalStoreInfo.stock_config && this.globalStoreInfo.stock_config.is_audit == 1) {
				this.$refs.tipsPop.open();
			} else {
				this.save();
			}
		},
		save() {
			// 检测库存是否填写,且提取数据
			let isStock = false;
			let saveData = [];
			try {
				this.goodsList.forEach((item, index) => {
					if (this.goodsIdArr.includes(item.sku_id)) {
						if (!parseFloat(item.goods_num || 0)) {
							isStock = true;
							let toast = "请输入" + item.sku_name + "的出库数量";
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
			this.screen.stock_json = JSON.stringify(saveData)
			editWastage(this.screen).then(res => {
				this.isSubmit = false;
				this.$util.showToast({
					title: res.message
				});
				if (res.code >= 0) {
					if (this.$refs.tipsPop) this.$refs.tipsPop.close();
					setTimeout(() => {
						this.backFn();
					}, 500);
					this.resetFn();
				}
			});
		},
		backFn() {
			this.$util.redirectTo('/pages/stock/wastage');
		},
		calcTotalData() {//计算商品种类、金额
			this.totalData.price = 0;
			this.totalData.kindsNum = 0;

			this.goodsList.forEach((item, index) => {
				if (this.goodsIdArr.includes(item.sku_id)) {
					this.totalData.price += parseFloat(item.cost_price ?? 0) * parseFloat(item.goods_num || 1);
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
		changeTime(data) {
			this.screen.time = data;
		},
		remarkConfirm() {
			this.screen.remark = JSON.parse(JSON.stringify(this.remark))
			this.$refs.remarkPopup.close()
		}
	}
}
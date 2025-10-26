import {exportPrintPriceTagData} from '@/api/goods.js';

export default {
	data() {
		return {
			goodsList: [], //已选择数据
			allSelected:false,
			isSubmit: false, //提交防抖
			dialogParams:{},
			dialogVisible: false, //弹框
			editPrintNum:{
				show:false,
				value:1,
			},
			//选择图标
			selectedIcon:'iconfuxuankuang1',
			unselectedIcon:'iconfuxuankuang2',
			harfselectedIcon:'iconcheckbox_weiquanxuan',
		};
	},
	onLoad(option) {
		
	},
	methods: {
		openSelectGoodsDialog() {
			this.dialogVisible = true
		},
		selectGoodsComplete(data) { //选择数据
			data.forEach((item, index) => {
				let is_in = false;
				this.goodsList.forEach((old_item, old_index)=>{
					if(old_item.sku_id == item.sku_id){
						is_in = true;
						return;
					}
				})
				if(!is_in){
					item.selected = false;
					item.print_num = 1;
					this.goodsList.push(item);
				}
			})
		},
		changeGoodsAllSelected(){
			if(this.goodsList.length == 0) return;
			this.allSelected = this.allSelected === true ? false : true;
			this.goodsList.forEach((item) => {
				item.selected = this.allSelected;
			})
			this.$forceUpdate();
		},
		changeGoodsSelected(index){
			this.goodsList[index].selected = !this.goodsList[index].selected;
			let selected_num = 0;
			this.goodsList.forEach((item) => {
				if(item.selected) selected_num++;
			})
			if(selected_num == this.goodsList.length){
				this.allSelected = true;
			}else if(selected_num == 0){
				this.allSelected = false;
			}else{
				this.allSelected = 'harf';
			}
			this.$forceUpdate();
		},
		getSelectedNum(){
			let selected_num = 0;
			this.goodsList.forEach((item) => {
				if(item.selected) selected_num++;
			})
			return selected_num;
		},
		batchDeleteGoods(){
			if(this.getSelectedNum() == 0){
				this.$util.showToast({
					title: '请选择要操作的数据',
				});
				return;
			}
			let goods_list = [];
			this.goodsList.forEach((item) => {
				if(!item.selected) goods_list.push(item);
			})
			this.goodsList = goods_list;
			if(this.goodsList.length == 0) this.allSelected = false;
			this.$forceUpdate();
		},
		editPrintNumShow(){
			if(this.getSelectedNum() == 0){
				this.$util.showToast({
					title: '请选择要操作的数据',
				});
				return;
			}
			this.editPrintNum.show = true;
			this.$forceUpdate();
		},
		editPrintNumConfirm(){
			this.goodsList.forEach((item) => {
				if(item.selected){
					item.print_num = this.editPrintNum.value;
					item.selected = false;
				}
			})
			this.allSelected = false;
			this.editPrintNum.value = 1;
			this.editPrintNum.show = false;
			this.$forceUpdate();
		},
		designFn(){
			if(!this.isPos()){
				this.$util.showToast({
					title: '请在客户端程序中执行此操作',
				});
				return;
			}
			if(this.goodsList.length == 0){
				this.$util.showToast({
					title: '请先选择商品',
				});
				return;
			}
			let printFieldConfig = [
				{field:'goods_name', name:'商品名称'},
				{field:'spec_name', name:'规格'},
				{field:'sku_no', name:'商品条码'},
				{field:'market_price', name:'划线价'},
				{field:'price', name:'零售价'},
				{field:'unit', name:'单位'},
				{field:'weight', name:'重量'},
				{field:'category_names', name:'商品分类'},
				{field:'brand_name', name:'品牌'},
				{field:'supplier_name', name:'供应商'},
				{field:'label_name', name:'标签'},
			];
			try{
				this.$pos.send('DesignPriceTag', JSON.stringify(this.goodsList));
			}catch(e){
				this.$util.showToast({
					title: '设计错误:'+JSON.stringify(e),
				});
			}
		},
		printFn() {
			if(!this.isPos()){
				this.$util.showToast({
					title: '请在客户端程序中执行此操作',
				});
				return;
			}
			if(this.goodsList.length == 0){
				this.$util.showToast({
					title: '请先选择商品',
				});
				return;
			}
			try{
				this.$pos.send('PrintPriceTag', JSON.stringify(this.goodsList));
			}catch(e){
				this.$util.showToast({
					title: '打印错误:'+JSON.stringify(e),
				});
			}
		},
		exportFn(){
			if(this.goodsList.length == 0){
				this.$util.showToast({
					title: '请先选择商品数据'
				});
				return;
			}
			if(this.isPos()){
				try{
					this.$pos.send('ExportPriceTag', JSON.stringify(this.goodsList));
				}catch(e){
					this.$util.showToast({
						title: '导出错误:'+JSON.stringify(e),
					});
				}
			}else{
				uni.showLoading({
					title: '导出中'
				});
				exportPrintPriceTagData({
					data:JSON.stringify(this.goodsList),
				}).then(res => {
					uni.hideLoading();
					if (res.code == 0) {
						window.open(this.$util.img(res.data.path));
					}else{
						this.$util.showToast({
							title: res.message
						});
					}
				});
			}
		},
		backFn() {
			this.$util.redirectTo('/pages/goods/goodslist');
		},
		isPos(){
			return (window.POS_ || window.ipcRenderer);
		},
	}
};
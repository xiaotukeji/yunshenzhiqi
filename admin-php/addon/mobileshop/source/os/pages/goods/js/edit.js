import {getGoodsInfoById,getCategoryTree,addGoods,editGoods,addVirtualGoods,editVirtualGoods,addVirtualCardGoods,editVirtualCardGoods} from '@/api/goods'
import {getOrderFormList} from '@/api/form'
import {getSupplyList} from '@/api/supply'
export default {
	data() {
		return {
			repeatFlag: false,
			isIphoneX: false,
			goodsImgHeight: 165, // 商品图片高度
			isAWait: false,
			albumPage: 'goodsEdit',
			//店内分类
			shopCategoryNumber: 1,
			shopCategoryData: {
				store_0: {}
			},
			shopCategoryIndex: '',

			categoryList: [],
			secondCategory: [],
			thirdCategory: [],
			categoryId: [0, 0, 0],
			categoryName: ['', '', ''],
			currentLevel: 1,
			lastLevel: 1,
			showFisrt: true,
			showSecond: false,
			showThird: false,

			goodsData: {
				goods_id: 0,
				sku_id: 0,
				goods_class: 1, // 商品类型:1 实物商品 ，2 虚拟商品，3.电子卡密
				goods_name: '',
				introduction: '',
				category_id: 0,
				category_name: '',
				goods_image: [],
				keywords: '',
				brand_id: 0,
				brand_name: '',
				virtual_indate: '', // 有效期/天【虚拟商品用】
				goods_spec_format: [], // 商品规格
				goods_sku_data: [], // 规格列表
				spec_type_status: 0,
				goods_shop_category_ids: '',

				// 单规格数据
				price: '',
				market_price: '',
				cost_price: '',
				weight: '',
				volume: '',
				sku_no: '',

				goods_stock: '',
				goods_stock_alarm: '',
				goods_content: '',

				// 快递运费
				is_free_shipping: 1, // 是否免邮
				shipping_template: 0, // 指定运费模板id
				template_name: '', // 运费模板名称

				max_buy: '',
				min_buy: '',
				unit: '',
				goods_state: 1,

				// 商品参数
				goods_attr_class: 0,
				goods_attr_name: '',
				goods_attr_format: [],

				virtual_sale: 0, // 虚拟销量
				is_consume_discount: 0, //是否参与会员折扣
				recommend_way: 0, // 推荐方式

				is_need_verify: 0, //是否需要核销
				verify_validity_type: 0, // 核销有效期类型
				verify_num: 1, // 核销次数
				is_limit: 0,
				limit_type: 1,
				sale_show: 0,
				stock_show: 0,
				market_price_show: 0,
				barrage_show: 0,
				virtual_deliver_type: 'auto_deliver',
				virtual_receive_type: 'auto_receive',
				goods_form_index: 0,
				supply_index: 0,
			},

			currCategory: 0, // 当前所设置的商品分类
			recommendArray: ['无', '新品', '精品', '热卖'],
			virtualDeliverArray: [{
					name: '自动发货',
					value: 'auto_deliver',
				},
				{
					name: '手动发货',
					value: 'artificial_deliver',
				},
				{
					name: '到店核销',
					value: 'verify',
				}
			],
			virtualDeliverValue: {
				'auto_deliver': '自动发货',
				'artificial_deliver': '手动发货',
				'verify': '到店核销'
			},
			virtualReceiveArray: [{
					name: '自动收货',
					value: 'auto_receive',
				},
				{
					name: '买家确认收货',
					value: 'artificial_receive',
				},
			],
			virtualReceiveValue: {
				'auto_receive': '自动收货',
				'artificial_receive': '买家确认收货',
			},
			validityTypeArray: ['永久', '购买后几日有效', '指定过期日期'],
			virtualIndate: 0,
			virtualTime: '',
			minDate: '',
			carmiLength: '添加卡密',
			limitLength: [ //限购
				{
					name: '单次限购',
					value: '1'
				},
				{
					name: '长期限购',
					value: '2'
				}
			],
			current: 0,
			goodsFormArray: [],
			goodsForm: [],

			supplyFormArray: [],
			supplyForm: []
		};
	},
	async onLoad(option) {
		this.goodsData.goods_id = option.goods_id || 0;

		if (!this.$util.checkToken('/pages/goods/edit/index?goods_id=' + this.goodsData.goods_id)) return;

		this.clearStoreage();

		this.getCategoryTreeFn();

		if (this.goodsData.goods_id) {
			this.isAWait = true;
			uni.setNavigationBarTitle({
				title: '编辑商品'
			});
			await this.editGetGoodsInfo();
		} else {
			this.isAWait = false;
			uni.setNavigationBarTitle({
				title: '添加商品'
			});
		}

		let date = new Date();
		this.minDate = date.getFullYear() + '-' + date.getMonth() + '-' + date.getUTCDate();

		if (this.addonIsExit.form) this.getGoodsForm();

		if (this.addonIsExit.supply) this.getSupplyList();

	},
	onShow() {
		this.isIphoneX = this.$util.uniappIsIPhoneX();
		this.refreshData();
	},
	methods: {
		// 获取编辑商品数据
		async editGetGoodsInfo() {
			var res = await getGoodsInfoById(this.goodsData.goods_id);
			if (res.code == 0 && res.data) {
				var data = res.data;

				data.goods_category.forEach((item, index) => {
					this.shopCategoryData['store_' + index] = {
						category_id: item.id,
						category_name: item.category_name
					}
				})
				this.shopCategoryNumber = data.goods_category.length;

				// 商品分类
				data.category_id = data.goods_category[0].id;
				data.category_name = data.goods_category[0].category_name.replace(/\//g, " / ");

				if (typeof data.category_id == 'string') {
					this.categoryId = data.category_id.split(",");
					this.categoryName = data.category_name.split(" / ");
				} else {
					this.categoryId = data.category_id;
					this.categoryName = data.category_name;
				}

				delete data.category_json;
				delete data.goods_category;

				data.goods_image = data.goods_image.split(",");

				data.goods_sku_data.forEach((item) => {
					if (item.sku_spec_format) item.sku_spec_format = JSON.parse(item.sku_spec_format);
				});

				if (data.goods_spec_format) {
					uni.setStorageSync("editGoodsSpecFormat", data.goods_spec_format);
					uni.setStorageSync("editGoodsSkuData", JSON.stringify(data.goods_sku_data));
					data.goods_spec_format = JSON.parse(data.goods_spec_format);
				} else {
					data.sku_id = data.goods_sku_data[0].sku_id;
					data.price = data.goods_sku_data[0].price;
					data.market_price = data.goods_sku_data[0].market_price;
					data.cost_price = data.goods_sku_data[0].cost_price;
					data.weight = data.goods_sku_data[0].weight;
					data.volume = data.goods_sku_data[0].volume;
					data.sku_no = data.goods_sku_data[0].sku_no;
				}

				if (data.goods_class == 1) {
					// 实物商品
					delete data.virtual_indate;
					uni.setStorageSync("editGoodsShippingTemplateId", data.shipping_template);
					uni.setStorageSync("editGoodsShippingTemplateName", data.template_name ? data.template_name : '');
				} else {
					// 虚拟商品
					delete data.shipping_template;
					delete data.is_free_shipping;
				}

				// 商品参数
				if (data.goods_attr_format) {
					uni.setStorageSync("editGoodsAttrClass", data.goods_attr_class);
					uni.setStorageSync("editGoodsAttrName", data.goods_attr_name);
					uni.setStorageSync("editGoodsAttrFormat", data.goods_attr_format);
					data.goods_attr_format = JSON.parse(data.goods_attr_format);
				}

				uni.setStorageSync("editGoodsState", data.goods_state);
				uni.setStorageSync("editGoodsContent", data.goods_content);

				if (data.verify_validity_type == 1) {
					this.virtualIndate = data.virtual_indate;
				} else if (data.verify_validity_type == 2) {
					this.virtualTime = this.$util.timeStampTurnTime(data.virtual_indate, 'Y-m-d');
				}
				data.verify_num = data.goods_sku_data[0].verify_num

				this.goodsData = data;

				this.goodsData.goods_form_index = 0;

				this.goodsData.supply_index = 0;

				this.$forceUpdate();
			} else {
				this.$util.showToast({
					title: '商品不存在',
				});
				setTimeout(() => {
					this.$util.redirectTo('/pages/goods/list', {}, 'redirectTo');
				}, 1000);
			}
		},
		// 选择商品分类
		openGoodsCategoryPop(index) {
			this.currCategory = index;
			if (this.shopCategoryData['store_' + index].category_id) {
				this.categoryId = this.shopCategoryData['store_' + index].category_id.split(',');
				this.categoryName = this.shopCategoryData['store_' + index].category_name.split(' / ');

				this.categoryList.forEach((item, index) => {
					item.selected = this.categoryId.indexOf(item.category_id.toString()) != -1;
					if (item.selected) {
						this.secondCategory = item.child_list;
					}
					if (item.child_list) {
						if (item.selected) this.lastLevel = 2;
						item.child_list.forEach(secondItem => {
							secondItem.selected = this.categoryId.indexOf(secondItem.category_id.toString()) != -1;
							if (secondItem.selected) {
								this.thirdCategory = secondItem.child_list;
							}
						});
					}
				});
				this.changeShow(this.categoryId.length);
			} else {
				this.categoryId = [0];
				this.categoryName = [''];
				this.changeShow(1);
			}
			this.$refs.categoryPopup.open();
		},
		closeGoodsCategoryPop() {
			this.$refs.categoryPopup.close();
		},
		// 编辑规格类型
		openGoodsSpec() {
			this.$util.redirectTo('/pages/goods/edit/spec');
		},
		// 编辑多规格
		openGoodsSpecEdit() {
			this.$util.redirectTo('/pages/goods/edit/spec_edit', {
				goods_class: this.goodsData.goods_class,
				virtual_deliver_type: this.goodsData.virtual_deliver_type
			});
		},
		//编辑卡密
		openCarmichaelEdit() {
			this.$util.redirectTo('/pages/goods/edit/carmichael_edit', {
				goods_class: this.goodsData.goods_class
			});
		},
		// 编辑商品状态
		openGoodsState() {
			this.$util.redirectTo('/pages/goods/edit/state', {
				goods_state: this.goodsData.goods_state
			});
		},
		// 编辑快递运费
		openExpressFreight() {
			this.$util.redirectTo('/pages/goods/edit/express_freight', {
				template_id: this.goodsData.shipping_template
			});
		},
		// 编辑商品详情
		openGoodsContent() {
			this.$util.redirectTo('/pages/goods/edit/content');
		},
		// 编辑商品参数
		openAttr() {
			this.$util.redirectTo('/pages/goods/edit/attr');
		},
		/**
		 * 刷新商品图片高度
		 * @param {Object} data
		 */
		refreshGoodsImgHeight(data) {
			if (data.height == '') return;
			var height = parseFloat(data.height.replace('px', ''));
			this.goodsImgHeight = height + 80;
			this.$forceUpdate();
			if (data.isLoad && this.$refs.loadingCover) {
				// 数据渲染留点时间
				setTimeout(() => {
					this.$refs.loadingCover.hide();
				}, 100);
			}
			uni.removeStorageSync("selectedAlbumImg");
			
		},
		// 获取商品分类树状结构
		getCategoryTreeFn() {
			getCategoryTree().then(res=>{
				if (res.data) {
					this.categoryList = res.data;
					this.categoryList.forEach((item, index) => {
						item.selected = this.categoryId.indexOf(item.category_id.toString()) != -1;
						if (item.selected) {
							this.secondCategory = item.child_list;
							this.currentLevel = 1;
						}
						if (item.child_list) {
							if (item.selected) this.lastLevel = 2;
							item.child_list.forEach(secondItem => {
								secondItem.selected = this.categoryId.indexOf(secondItem.category_id.toString()) != -1;
								if (secondItem.selected) {
									this.thirdCategory = secondItem.child_list;
									this.currentLevel = 2;
								}
								if (secondItem.child_list) {
									if (secondItem.selected) this.lastLevel = 3;
									secondItem.child_list.forEach(thirdItem => {
										thirdItem.selected = this.categoryId.indexOf(thirdItem.category_id.toString()) != -1;
										if (thirdItem.selected) this.currentLevel = 3;
									});
								}
							});
						}
					});
					this.changeShow(this.lastLevel);
					if (this.goodsData.goods_id == 0 && this.$refs.loadingCover) {
						if (this.$refs.loadingCover) this.$refs.loadingCover.hide();
					}
				}
			});
		},
		changeShow(index) {
			if (index == 1) {
				this.showFisrt = true;
				this.showSecond = false;
				this.showThird = false;
			} else if (index == 2) {
				this.showFisrt = false;
				this.showSecond = true;
				this.showThird = false;
			} else if (index == 3) {
				this.showFisrt = false;
				this.showSecond = false;
				this.showThird = true;
			}
			this.currentLevel = index;
			this.$forceUpdate();
		},
		selectCategory(category) {
			this.currentLevel = category.level;

			// 如果当前选中跟上次不一样,则 要清空下级数据
			if (category.level == 1 && this.categoryId[0] > 0 && this.categoryId[0] != category.captcha_id) {
				this.categoryId[1] = 0;
				this.categoryName[1] = '';
				this.categoryId[2] = 0;
				this.categoryName[2] = '';
			} else if (category.level == 2 && this.categoryId[1] > 0 && this.categoryId[1] != category.captcha_id) {
				this.categoryId[2] = 0;
				this.categoryName[2] = '';
			}

			this.categoryId[category.level - 1] = category.category_id;
			this.categoryName[category.level - 1] = category.category_name;
			if (category.level == 1) {
				if (category.child_list) {
					this.secondCategory = category.child_list;
				} else {
					this.categoryId[1] = 0;
					this.categoryName[1] = '';
					this.categoryId[2] = 0;
					this.categoryName[2] = '';
				}
			} else if (category.level == 2) {
				if (category.child_list) {
					this.thirdCategory = category.child_list;
				} else {
					this.categoryId[2] = 0;
					this.categoryName[2] = '';
				}
			}

			this.lastLevel = 1;
			this.categoryList.forEach((item, index) => {
				item.selected = this.categoryId[0] == item.category_id;
				if (item.child_list) {
					if (item.selected) this.lastLevel = 2;
					item.child_list.forEach((secondItem, secondIndex) => {
						secondItem.selected = this.categoryId[1] == secondItem.category_id;
						if (secondItem.child_list) {
							if (secondItem.selected) this.lastLevel = 3;
						}
					});
				}
			});

			this.changeShow(this.lastLevel);

			this.goodsData.category_id = [];
			this.goodsData.category_name = [];

			for (var i = 0; i < this.categoryId.length; i++) {
				if (this.categoryId[i]) this.goodsData.category_id.push(this.categoryId[i]);
			}
			for (var i = 0; i < this.categoryName.length; i++) {
				if (this.categoryName[i]) this.goodsData.category_name.push(this.categoryName[i]);
			}

			this.goodsData.category_id = this.goodsData.category_id.toString();
			this.goodsData.category_name = this.goodsData.category_name.join(" / ");

			if (
				(this.lastLevel == 3 && this.categoryId[2]) ||
				(this.lastLevel == 2 && this.categoryId[1]) ||
				(this.lastLevel == 1 && this.categoryId[0])
			) {
				this.shopCategoryData['store_' + this.currCategory] = {
					category_id: this.goodsData.category_id,
					category_name: this.goodsData.category_name
				};
				this.closeGoodsCategoryPop();
			}

			this.$forceUpdate();
		},
		addShopCategory() {
			if (this.shopCategoryNumber == 10) {
				this.$util.showToast({
					title: '商品可以属于多个分类，最多10个'
				});
				return;
			}
			this.shopCategoryData['store_' + this.shopCategoryNumber] = {};
			++this.shopCategoryNumber;
		},
		deleteShopCategory(index) {
			delete this.shopCategoryData['store_' + index];
			--this.shopCategoryNumber;

			//重置数据	
			let i = 0;
			let obj = {};
			for (let key in this.shopCategoryData) {
				obj['store_' + i] = this.shopCategoryData[key];
				i++;
			}
			this.shopCategoryData = {};
			this.shopCategoryData = Object.assign(this.shopCategoryData, obj);
		},
		// 刷新数据
		refreshData() {
			var selectedAlbumImg = uni.getStorageSync('selectedAlbumImg');
			if (selectedAlbumImg) {
				uni.setStorageSync('selectedAlbumImgTemp', selectedAlbumImg);
				selectedAlbumImg = JSON.parse(selectedAlbumImg);
				this.goodsData.goods_image = selectedAlbumImg.list.split(",");
				this.$refs.goodsShmilyDragImg.refresh();
			}

			// 规格项
			this.goodsData.goods_spec_format = uni.getStorageSync('editGoodsSpecFormat') ? JSON.parse(uni.getStorageSync('editGoodsSpecFormat')) : [];
			if (this.goodsData.goods_spec_format.length <= 0) {

				this.goodsData.carmichael = uni.getStorageSync('editGoodsCarmichael') ? JSON.parse(uni.getStorageSync('editGoodsCarmichael')) : [];
				if (this.goodsData.carmichael.length > 0) {
					this.carmiLength = '添加卡密【' + this.goodsData.carmichael.length + '】'
				}
			}

			// 多规格数据
			this.goodsData.goods_sku_data = uni.getStorageSync('editGoodsSkuData') ? JSON.parse(uni.getStorageSync('editGoodsSkuData')) : [];
			if (this.goodsData.goods_sku_data.length > 0) {
				this.goodsData.goods_stock = 0;
				this.goodsData.goods_stock_alarm = 0;
				this.goodsData.goods_sku_data.forEach((item) => {
					if (item.stock) this.goodsData.goods_stock += parseInt(item.stock);
					if (item.stock_alarm) this.goodsData.goods_stock_alarm += parseInt(item.stock_alarm);
				});
			}

			// 快递运费
			this.goodsData.shipping_template = uni.getStorageSync('editGoodsShippingTemplateId') || 0;
			this.goodsData.is_free_shipping = this.goodsData.shipping_template > 0 ? 0 : 1;
			this.goodsData.template_name = uni.getStorageSync('editGoodsShippingTemplateName') || '';

			if (uni.getStorageSync('editGoodsState') !== undefined && uni.getStorageSync('editGoodsState') !== '') {
				this.goodsData.goods_state = uni.getStorageSync('editGoodsState');
			}

			if (uni.getStorageSync('editGoodsContent') != undefined && uni.getStorageSync('editGoodsContent') != '') {
				this.goodsData.goods_content = uni.getStorageSync('editGoodsContent');
			}
			// 商品参数
			this.goodsData.goods_attr_class = uni.getStorageSync('editGoodsAttrClass') || 0;
			this.goodsData.goods_attr_name = uni.getStorageSync('editGoodsAttrName') || '';
			this.goodsData.goods_attr_format = uni.getStorageSync('editGoodsAttrFormat') ? JSON.parse(uni.getStorageSync('editGoodsAttrFormat')) : [];
			this.$forceUpdate();
		},
		// 验证
		verify() {

			if (this.goodsData.goods_name.length == 0) {
				this.$util.showToast({
					title: '请输入商品名称'
				});
				return false;
			}
			if (this.goodsData.goods_name.length > 100) {
				this.$util.showToast({
					title: '商品名称不能超过100个字符'
				});
				return false;
			}

			if (this.goodsData.introduction.length > 100) {
				this.$util.showToast({
					title: '促销语不能超过100个字符'
				});
				return false;
			}

			if (this.goodsData.goods_image.length == 0) {
				this.$util.showToast({
					title: '请上传商品图片'
				});
				return false;
			}

			if (!this.shopCategoryData.store_0.category_id) {
				this.$util.showToast({
					title: `请选择商品分类`
				});
				return false;
			}

			if (this.goodsData.goods_class == 2 && this.goodsData.virtual_deliver_type == 'verify') {
				if (this.goodsData.verify_validity_type == 1) {
					if (this.virtualIndate.length == 0) {
						this.$util.showToast({
							title: '请输入有效期'
						});
						return false;
					}
					if (isNaN(this.virtualIndate) || !this.$util.data().regExp.number.test(this.virtualIndate)) {
						this.$util.showToast({
							title: '[有效期]格式输入错误'
						});
						return false;
					}
					if (this.virtualIndate < 1) {
						this.$util.showToast({
							title: '有效期不能小于1天'
						});
						return false;
					}
				}

				if (this.goodsData.verify_validity_type == 2) {
					if (this.virtualTime.length == 0) {
						this.$util.showToast({
							title: '请设置有效期'
						});
						return false;
					}
				}

			}

			// 单规格
			if (this.goodsData.goods_spec_format.length == 0) {
				if (this.goodsData.price.length == 0) {
					this.$util.showToast({
						title: '请输入销售价'
					});
					return false;
				}
				if (isNaN(this.goodsData.price) || !this.$util.data().regExp.digit.test(this.goodsData.price)) {
					this.$util.showToast({
						title: '[销售价]格式输入错误'
					});
					return false;
				}

				if (this.goodsData.market_price.length > 0 && (isNaN(this.goodsData.market_price) || !this.$util.data().regExp.digit.test(this.goodsData.market_price))) {
					this.$util.showToast({
						title: '[划线价]格式输入错误'
					});
					return false;
				}

				if (this.goodsData.cost_price.length > 0 && (isNaN(this.goodsData.cost_price) || !this.$util.data().regExp.digit.test(this.goodsData.cost_price))) {
					this.$util.showToast({
						title: '[成本价]格式输入错误'
					});
					return false;
				}

				if (this.goodsData.goods_class == 2 && this.goodsData.virtual_deliver_type == 'verify') {
					let verify_nums = this.goodsData.verify_num

					if (verify_nums.length == 0) {
						this.$util.showToast({
							title: '请输入核销次数'
						});
						return false;
					}
					if (isNaN(verify_nums) || !this.$util.data().regExp.number.test(verify_nums)) {
						this.$util.showToast({
							title: '[核销次数]格式输入错误'
						});
						return false;
					}

					if (parseInt(verify_nums) < 1) {
						this.$util.showToast({
							title: '核销次数不能小于1'
						});
						return false;
					}
				}

				if (this.goodsData.goods_class == 1 && this.goodsData.weight.length > 0 && (isNaN(this.goodsData.weight) || !this.$util.data().regExp.float3.test(this.goodsData.weight))) {
					this.$util.showToast({
						title: '[重量(kg)]格式输入错误，最多三位小数'
					});
					return false;
				}

				if (this.goodsData.goods_class == 1 && this.goodsData.volume.length > 0 && (isNaN(this.goodsData.volume) || !this.$util.data().regExp.float3.test(this.goodsData.volume))) {
					this.$util.showToast({
						title: '[体积(m³)]格式输入错误，最多三位小数'
					});
					return false;
				}

			} else {
				// 多规格
				if (this.goodsData.goods_sku_data.length == 0) {
					this.$util.showToast({
						title: '请编辑规格信息'
					});
					return false;
				}

				var flag = false;
				for (var i = 0; i < this.goodsData.goods_sku_data.length; i++) {
					if (this.goodsData.goods_sku_data[i].price == '') {
						flag = true;
						break;
					}
				}
				if (flag) {
					this.$util.showToast({
						title: '请编辑规格信息'
					});
					return false;
				}
			}

			// 总库存
			if (this.goodsData.goods_stock.length == 0) {
				this.$util.showToast({
					title: '请输入库存'
				});
				return false;
			}

			if (isNaN(this.goodsData.goods_stock) || !this.$util.data().regExp.number.test(this.goodsData.goods_stock)) {
				this.$util.showToast({
					title: '[库存]格式输入错误'
				});
				return false;
			}

			if (this.goodsData.goods_stock_alarm.length > 0) {
				if (isNaN(this.goodsData.goods_stock_alarm) || !this.$util.data().regExp.number.test(this.goodsData.goods_stock_alarm)) {
					this.$util.showToast({
						title: '[库存预警]格式输入错误'
					});
					return false;
				}
				if (parseInt(this.goodsData.goods_stock_alarm) != 0 && parseInt(this.goodsData.goods_stock_alarm) == parseInt(this.goodsData.goods_stock)) {
					this.$util.showToast({
						title: '[库存预警]不能等于库存数量'
					});
					return false;
				}
				if (parseInt(this.goodsData.goods_stock_alarm) > parseInt(this.goodsData.goods_stock)) {
					this.$util.showToast({
						title: '[库存预警]不能超过库存数量'
					});
					return false;
				}
			}

			if (this.goodsData.goods_class == 1 && this.goodsData.is_free_shipping == 0 && this.goodsData.shipping_template == '') {
				this.$util.showToast({
					title: '请选择运费模板'
				});
				return false;
			}

			if (this.goodsData.goods_content.length == 0) {
				this.$util.showToast({
					title: '请填写商品详情'
				});
				return false;
			}

			if (this.goodsData.max_buy.length > 0) {
				if (isNaN(this.goodsData.max_buy) || !this.$util.data().regExp.number.test(this.goodsData.max_buy)) {
					this.$util.showToast({
						title: '[限购]格式输入错误'
					});
					return false;
				}
				if (this.goodsData.max_buy < 0) {
					this.$util.showToast({
						title: '限购数量不能小于'
					});
					return false;
				}
			}

			if (this.goodsData.min_buy.length > 0) {
				if (isNaN(this.goodsData.min_buy) || !this.$util.data().regExp.number.test(this.goodsData.min_buy)) {
					this.$util.showToast({
						title: '[起售]格式输入错误'
					});
					return false;
				}
				if (this.goodsData.min_buy < 0) {
					this.$util.showToast({
						title: '起售数量不能小于'
					});
					return false;
				}

				if (this.goodsData.max_buy > 0 && parseInt(this.goodsData.min_buy) > parseInt(this.goodsData.max_buy)) {
					this.$util.showToast({
						title: '起售数量不能大于限购数量'
					});
					return false;
				}
			}

			return true;

		},
		// 删除本地缓存
		clearStoreage() {

			// 临时选择的商品图片
			uni.removeStorageSync("selectedAlbumImg");
			uni.removeStorageSync("selectedAlbumImgTemp");

			// 商品规格
			uni.removeStorageSync("editGoodsSpecFormat");
			uni.removeStorageSync("editGoodsSkuData");

			//电子卡密
			uni.removeStorageSync("editGoodsCarmichael");
			uni.removeStorageSync("specName");

			// 物流公司
			uni.removeStorageSync("editGoodsShippingTemplateId");
			uni.removeStorageSync("editGoodsShippingTemplateName");

			// 商品状态
			uni.removeStorageSync("editGoodsState");

			// 商品详情
			uni.removeStorageSync("editGoodsContent");

			// 商品参数
			uni.removeStorageSync("editGoodsAttrClass");
			uni.removeStorageSync("editGoodsAttrName");
			uni.removeStorageSync("editGoodsAttrFormat");
		},
		save() {
			if (!this.verify()) return;

			// 清空规格的图片

			for (var i = 0; i < this.goodsData.goods_sku_data.length; i++) {
				if (this.goodsData.goods_sku_data[i].sku_images.length == 0) this.goodsData.goods_sku_data[i].sku_image = '';
			}
			var data = JSON.parse(JSON.stringify(this.goodsData));
			delete data.category_name;

			data.category_json = [];
			data.category_id = '';

			for (var key in this.shopCategoryData) {
				if (this.shopCategoryData[key].category_id) {
					data.category_id += ',' + this.shopCategoryData[key].category_id;
					data.category_json.push(this.shopCategoryData[key].category_id);
				}
			}
			data.category_id += ',';
			data.category_json = JSON.stringify(data.category_json);
			if (data.goods_spec_format.length == 0) {
				// 单规格数据
				var singData = {
					sku_id: (data.goods_id ? data.sku_id : 0),
					sku_name: data.goods_name,
					spec_name: '',
					sku_no: data.sku_no,
					sku_spec_format: '',
					price: data.price,
					market_price: data.market_price,
					cost_price: data.cost_price,
					stock: data.goods_stock,
					stock_alarm: data.goods_stock_alarm,
					weight: data.weight,
					volume: this.goodsData.volume,
					sku_image: data.goods_image[0],
					sku_images: data.goods_image.toString(),
					// verify_num: data.goods_sku_data.length > 0 ? data.goods_sku_data[0].verify_num : this.goodsData.verify_num
					verify_num: data.verify_num
				}
				var singleSkuData = JSON.stringify([singData]);
			}
			data.goods_image = data.goods_image.toString();
			// 商品规格json格式
			data.goods_spec_format = data.goods_spec_format.length > 0 ? JSON.stringify(data.goods_spec_format) : '';

			// SKU商品数据
			data.goods_sku_data = data.goods_spec_format.length > 0 ? JSON.stringify(data.goods_sku_data) :
				singleSkuData;

			// 商品参数json格式
			data.goods_attr_format = data.goods_attr_format.length > 0 ? JSON.stringify(data.goods_attr_format) : '';

			data.spec_type_status = data.goods_spec_format.length > 0 ? 1 : 0;

			if (this.goodsData.verify_validity_type == 1) {
				data.virtual_indate = this.virtualIndate;
			} else if (this.goodsData.verify_validity_type == 2) {
				data.virtual_indate = this.virtualTime;
			}

			// console.log(this.goodsData.goods_spec_format,'format')
			// if(this.goodsData.goods_spec_format == '[]'){
			// 	data.carmichael= data.carmichael
			// }
			var save = null;
			if (data.goods_class == 3) {
				save = addVirtualCardGoods;
				if (data.goods_id) save = editVirtualCardGoods;
			}else if (data.goods_class == 2){
				save = addVirtualGoods;
				if (data.goods_id) save = editVirtualGoods;
			}else {
				save = addGoods;
				if (data.goods_id) save = editGoods;
			}
			data.goods_form = this.goodsData.goods_form_index ? this.goodsForm[this.goodsData.goods_form_index - 1].id : 0;

			data.supplier_id = this.goodsData.supply_index ? this.supplyForm[this.goodsData.supply_index - 1].supplier_id : 0;
			if (this.repeatFlag) return;
			this.repeatFlag = true;
			save(data).then(res=>{
				this.$util.showToast({
					title: res.message
				});
				if (res.code == 0) {
					this.clearStoreage();
					setTimeout(() => {
						this.$util.redirectTo('/pages/goods/list', {}, 'tabbar');
					}, 1000);
				} else {
					this.repeatFlag = false;
				}
			});
		},
		//是否开启限购
		onLimit() {
			this.goodsData.is_limit = this.goodsData.is_limit == 1 ? 0 : 1
		},

		//限购类型
		limitChange(e) {
			this.goodsData.limit_type = e
		},
		/**
		 * 是否参与会员折扣
		 */
		joinMemberDiscount() {
			this.goodsData.is_consume_discount = this.goodsData.is_consume_discount == 1 ? 0 : 1;
		},
		switchBtn(type) {
			this.goodsData[type] = this.goodsData[type] == 1 ? 0 : 1;
		},
		/**
		 * 推荐方式选择
		 * @param {Object} e
		 */
		recommendWayChange(e) {
			this.goodsData.recommend_way = e.detail.value;
		},
		/**
		 * 发货选择
		 * @param {Object} e
		 */
		virtualDeliverTypeChange(e) {
			this.goodsData.virtual_deliver_type = this.virtualDeliverArray[e.detail.value]['value'];
		},
		/**
		 * 收货选择
		 * @param {Object} e
		 */
		virtualReceiveTypeChange(e) {
			this.goodsData.virtual_receive_type = this.virtualReceiveArray[e.detail.value]['value'];
		},
		/**
		 * 核销有效期类型
		 * @param {Object} e
		 */
		validityTypeChange(e) {
			this.goodsData.verify_validity_type = e.detail.value;
		},
		/**
		 * 核销有效期选择
		 * @param {Object} e
		 */
		virtualTimeChange(e) {
			this.virtualTime = e.detail.value;
		},
		/**
		 * 是否需要核销
		 */
		isNeedVerify() {
			this.goodsData.is_need_verify = this.goodsData.is_need_verify == 1 ? 0 : 1;
		},
		/**
		 * 获取商品表单
		 */
		getGoodsForm() {
			getOrderFormList().then(res=>{
				if (res.data) {
					let goodsForm = ['请选择商品表单'];
					res.data.forEach((item, index) => {
						goodsForm.push(item.form_name);
						if (this.goodsData.form_id && this.goodsData.form_id == item.id) this.goodsData.goods_form_index = index + 1;
					})
					this.goodsForm = res.data;
					this.goodsFormArray = goodsForm;
					this.$forceUpdate();
				}
			})
		},
		goodsFormChange(e) {
			this.goodsData.goods_form_index = e.detail.value;
			this.$forceUpdate();
		},
		/**
		 * 获取供应商
		 */
		getSupplyList() {
			getSupplyList().then(res=>{
				if (res.data) {
					let supplyForm = ['请选择供应商'];
					res.data.forEach((item, index) => {
						supplyForm.push(item.title);
						if (this.goodsData.supplier_id && this.goodsData.supplier_id == item.supplier_id) this.goodsData.supply_index = index + 1;
					})
					this.supplyForm = res.data;
					this.supplyFormArray = supplyForm;
					this.$forceUpdate();
				}
			})
		},
		supplyChange(e) {
			this.goodsData.supply_index = e.detail.value;
			this.$forceUpdate();
		}
	}
};
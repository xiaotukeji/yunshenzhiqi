var self;

import {
	getGoodsList,
	getGoodsCategory,
	getServiceCategory,
	getServiceList,
	getGoodsSkuList,
	getElectronicScaleInformation,
	getGoodsInfoByCode
} from '@/api/goods'
import {mapGetters} from 'vuex';

export default {
	name: 'nsGoods',
	props: {
		indexFocus: {
			type: [String, Number],
			default: ''
		}
	},
	data() {
		return {
			type: 'goods',
			serviceCategory: [],
			serviceCategoryId: 'all',
			serviceData: {
				size: 12,
				index: 1,
				total: 0,
				list: []
			},
			goodsCategoryId: 'all',
			goodsCategory: [],
			goodsData: {
				size: 12,
				index: 1,
				total: 0,
				list: []
			},
			skuInfo: null,
			allSku: null,
			searchText: '',
			itemNum: 3,
			mediaQueryOb: null,
			paymentMoney: '',
			cashierScale: null,
			actionIndex: 0,
			inputFocus: false,
			goodsCategoryShow: false,
			goodsCategoryIndex: 0,
			serviceCategoryShow: false,
			serviceCategoryIndex: 0,
			scanCode: {
				code: '',
				lastTime: 0
			},
			isGoodsLoad: false,
			goodsItems:{},//点击商品后如果是可转换库存商品存储商品数据
			scanCodeSearch:false,
		};
	},
	watch: {
		serviceCategoryId: function (nval) {
			this.serviceData.page = 0;
			this.serviceData.index = 1;
			this.$refs.loading.show();
			this.getService();
		},
		goodsCategoryId: function (nval) {
			this.goodsData.page = 0;
			this.goodsData.index = 1;
			this.$refs.goodsLoading.show();
			this.getGoods();
		},
		type: function () {
			this.searchText = '';
		}
	},
	computed: {
		goodsSpec() {
			if (this.allSku && this.skuInfo) {
				let data = [];
				if (this.skuInfo.goods_class != 6 || (this.skuInfo.goods_class == 6 && this.skuInfo.goods_spec_format)) {
					data = this.allSku['sku_id_' + this.skuInfo.sku_id].goods_spec_format;
				}
				return data;
			}
			return [];
		},
		...mapGetters(['billingGoodsIds', 'billingGoodsData', 'billingActive', 'billingIsScanTrigger','billingIsShowCashBox'])
	},
	created() {
		this.init();
		this.addScanCodeEvent();
		this.addKeyDownEvent();
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

		document.addEventListener('click', this.isListShow)
	},
	beforeDestroy() {
		document.removeEventListener("click", this.isListShow);
	},
	methods: {
		init() {
			self = this;
			this.getServiceCategoryFn();
			this.getGoodsCategoryFn();
			this.getService();
			this.getGoods();
		},
		getServiceCategoryFn() {
			getServiceCategory().then(res => {
				if (res.code == 0 && res.data) {
					this.serviceCategory = res.data;
				}
			});
		},
		getGoodsCategoryFn() {
			getGoodsCategory().then(res => {
				if (res.code == 0 && res.data) {
					this.goodsCategory = res.data;
				}
			});
		},
		getService() {
			getServiceList({
				page: this.serviceData.index,
				page_size: this.serviceData.size,
				category: this.serviceCategoryId,
				search_text: this.searchText,
				status: 1
			}).then(res => {
				if (this.$refs.loading) this.$refs.loading.hide();
				if (res.code == 0) {
					this.serviceData.total = res.data.count;
					this.serviceData.list = res.data.list || [];
					this.searchText = '';
				}
			});
		},
		getGoods() {
			this.isGoodsLoad = false;
			getGoodsList({
				page: this.goodsData.index,
				page_size: this.goodsData.size,
				category: this.goodsCategoryId,
				search_text: this.searchText,
				goods_class: '1,6',
				status: 1
			}).then((res) => {
				if (this.$refs.goodsLoading) this.$refs.goodsLoading.hide();
				if (res.code == 0) {
					this.goodsData.total = res.data.count;
					this.goodsData.list = res.data.list || [];
					this.goodsData.list.forEach(item => {
						item.adjust = {}; // 存储规格调价
					});
					if(this.scanCodeSearch) this.searchText = '';
					this.scanCodeSearch = false;
				}
				this.isGoodsLoad = true;
			})
		},
		goodsSelect(data, index) {
			if (this.type == 'goods' && !data.stock) return;
			if (index != undefined) this.actionIndex = index;

			if (data.goods_class != 6 && data.adjust && data.adjust['sku_id_' + data.sku_id]) {
				data.adjust_price = data.adjust['sku_id_' + data.sku_id].adjust_price;
				data.is_adjust = data.adjust['sku_id_' + data.sku_id].is_adjust;
			}

			// 满足条件：多规格，称重商品计重模式，编辑数量
			if (data.goods_spec_format || (data.goods_class == 6 && data.pricing_type == 'weight') || (data.status && data.status == 'edit')) {
				if(data.stock_transform) this.goodsItems = this.$util.deepClone(data)
				this.setActive('SelectGoodsSku');
				getGoodsSkuList(data.goods_id).then(res => {
					if (res.code == 0) {
						let obj = {};
						res.data.forEach(item => {
							try{
								item.goods_spec_format = JSON.parse(item.goods_spec_format);
							}catch(e){
								item.goods_spec_format = [];
							}
							obj['sku_id_' + item.sku_id] = item;
						});
						this.allSku = obj;
						this.skuInfo = obj['sku_id_' + data.sku_id];
						// 调整价格，称重商品不参与永久调价，每次都是初始原价
						if (data.goods_class != 6 && data.adjust_price) {
							this.skuInfo.adjust_price = data.adjust_price; // 使用调价
						} else {
							this.skuInfo.adjust_price = data.price; // 未调价，使用原价
						}
						
						this.skuInfo.adjust_price = parseFloat(this.skuInfo.adjust_price).toFixed(2);

						this.$set(this.skuInfo, 'status', data.status || '');
						this.$set(this.skuInfo, 'editKey', data.editKey || '');

						if (data.goods_class != 6) {
							this.skuInfo.num = data.num || 1; // 默认购买数量为1
						}

						// 称重商品，计重模式
						if (data.goods_class == 6 && data.pricing_type == 'weight') {
							let num = data.num || '';

							if (num) {
								this.$set(this.skuInfo, 'weigh', num);
								Object.values(this.allSku).forEach((item, index) => {
									this.$set(item, 'weigh', num);
								});
							}

							// 打开收银秤
							this.openCashierScale()
						}

						this.$refs.skuPopup.open();

						setTimeout(() => {
							this.inputFocus = true;
						}, 200);
					}
				});
			} else {
				this.handleSelectGoods(data);
				this.scanCode = {
					lastTime: 0,
					code: ''
				};
				this.$store.commit('billing/setActive', 'SelectGoodsAfter'); // 记录页面当前活跃值：选择完商品
			}
		},
		skuSelect(sku_id) {
			if (!this.skuInfo.status) {
				this.skuInfo = this.allSku['sku_id_' + sku_id];
				this.goodsItems.sku_id = sku_id
				let skuData = this.goodsData.list[this.actionIndex];

				// 调整价格
				if (skuData.adjust && skuData.adjust['sku_id_' + sku_id]) {
					this.skuInfo.adjust_price = skuData.adjust['sku_id_' + sku_id].adjust_price;
					this.skuInfo.is_adjust = skuData.adjust['sku_id_' + sku_id].is_adjust;
				} else {
					// 使用原价
					this.skuInfo.adjust_price = this.skuInfo.price;
				}
				this.skuInfo.adjust_price = parseFloat(this.skuInfo.adjust_price).toFixed(2);

				if (this.skuInfo.goods_class != 6) {
					this.skuInfo.num = this.skuInfo.num || 1; // 默认购买数量为1
				}

			}
		},
		skuConfirm() {
			if (!this.skuInfo) return;
			if (this.skuInfo.stock <= 0) {
				this.$util.showToast({
					title: '商品库存不足'
				});
				return;
			}

			if (this.skuInfo.price.length == 0 || this.skuInfo.adjust_price.length == 0) {
				this.$util.showToast({
					title: '请输入单价'
				});
				return;
			}

			if (this.skuInfo.price < 0 || this.skuInfo.adjust_price < 0) {
				this.$util.showToast({
					title: '单价不能小于0'
				});
				return;
			}

			if (this.skuInfo.goods_class != 6) {
				if (Number.parseInt(this.skuInfo.num) <= 0 || !/^\d{0,10}$/.test(Number.parseInt(this.skuInfo.num))) {
					this.$util.showToast({
						title: '请输入合法的数值，数值要大于零'
					});
					return;
				}
				if (this.skuInfo.stock < this.skuInfo.num) {
					this.$util.showToast({
						title: '商品库存不足'
					});
					return;
				}
			}
			if (this.skuInfo.goods_class == 6 && this.skuInfo.pricing_type == 'weight') {
				if (Number.parseFloat(this.skuInfo.weigh) <= 0 || !/^\d{0,10}(.?\d{0,3})$/.test(Number.parseFloat(this.skuInfo.weigh))) {
					this.$util.showToast({
						title: '请输入合法的数值，数值要大于零且小数位不能超过三位'
					});
					return;
				}
				if (this.skuInfo.stock < this.skuInfo.weigh) {
					this.$util.showToast({
						title: '商品库存不足'
					});
					return;
				}

				try {
					this.$pos.send('CloseWeigher');
				} catch (e) {
				}
			}

			this.skuInfo.is_adjust = this.skuInfo.adjust_price != this.skuInfo.price;

			// 设置右侧商品的调价
			if (!this.goodsData.list[this.actionIndex].adjust['sku_id_' + this.skuInfo.sku_id]) {
				this.goodsData.list[this.actionIndex].adjust['sku_id_' + this.skuInfo.sku_id] = {};
			}
			this.goodsData.list[this.actionIndex].adjust['sku_id_' + this.skuInfo.sku_id].adjust_price = this.skuInfo.adjust_price;
			this.goodsData.list[this.actionIndex].adjust['sku_id_' + this.skuInfo.sku_id].is_adjust = this.skuInfo.is_adjust;

			this.handleSelectGoods(this.skuInfo);
			this.scanCode = {
				lastTime: 0,
				code: ''
			};
			this.$store.commit('billing/setActive', 'SelectGoodsAfter'); // 记录页面当前活跃值：选择完商品
			this.$refs.skuPopup.close();
		},
		search() {
			switch (this.type) {
				case 'service':
					this.serviceData.page = 0;
					this.serviceData.index = 1;
					this.$refs.loading.show();
					this.getService();
					break;
				case 'goods':
					this.goodsData.page = 0;
					this.goodsData.index = 1;
					this.$refs.goodsLoading.show();
					this.getGoods();
					break;
			}
		},
		destroyed() {
			this.mediaQueryOb.disconnect();
		},
		switchStoreAfter() {
			this.serviceCategory = [];
			this.serviceCategoryId = 'all';
			this.serviceData = {
				size: 12,
				index: 1,
				page: 0,
				total: 1,
				list: []
			};
			this.goodsCategoryId = 'all';
			this.goodsCategory = [];
			this.goodsData = {
				size: 12,
				index: 1,
				page: 0,
				total: 1,
				list: []
			};
			this.getServiceCategoryFn();
			this.getGoodsCategoryFn();
			this.getService();
			this.getGoods();
		},
		pageChange(e) {
			if (this.type == 'goods') {
				this.goodsData.index = e.current;
				this.getGoods();
			} else if (this.type == 'service') {
				this.serviceData.index = e.current;
				this.getService();
			}
		},
		switchItem(type) {
			this.type = type;
			if (this.type == 'goods') {
				this.goodsData.index = 1;
				this.getGoods();
			} else if (this.type == 'service') {
				this.serviceData.index = 1;
				this.getService();
			} else if (this.type == 'money') {
				// 无码商品
				this.setActive('UnnumberedGoods');
			}
		},
		keydown(value) {
			let arr = this.paymentMoney.split('.');
			if (arr[1]) {
				if (value == '.' || arr[1].length == 2) return;
				if (value == '00' && arr[1].length == 1) value = '0';
			}
			if (parseFloat(this.paymentMoney + value) > 1000000) {
				this.$util.showToast({
					title: '最大不能超过1000000'
				});
				return;
			}
			this.paymentMoney += value;
		},
		deleteCode() {
			this.paymentMoney = this.paymentMoney.substr(0, this.paymentMoney.length - 1);
		},
		paymentMoneyConfirm() {
			if (!this.paymentMoney.length) {
				this.$util.showToast({
					title: '请输入收款金额'
				});
				return;
			}
			if (isNaN(parseFloat(this.paymentMoney)) || !/^(([0-9][0-9]*)|(([0]\.\d{1,2}|[1-9][0-9]*\.\d{1,2})))$/.test(parseFloat(this.paymentMoney))) {
				this.$util.showToast({
					title: '收款金额格式错误'
				});
				return;
			}
			if (this.paymentMoney <= 0) {
				this.$util.showToast({
					title: '收款金额不能小于等于0'
				});
				return;
			}
			if (parseFloat(this.paymentMoney) > 1000000) {
				this.$util.showToast({
					title: '最大不能超过1000000'
				});
				return;
			}
			this.handleSelectGoods({
				goods_id: parseInt(new Date().getTime() / 1000),
				sku_id: parseInt(new Date().getTime() / 1000),
				num: 1,
				money: parseFloat(this.paymentMoney)
			});
			this.scanCode = {
				lastTime: 0,
				code: ''
			};
			this.$store.commit('billing/setActive', 'SelectGoodsAfter'); // 记录页面当前活跃值：选择完商品
			this.paymentMoney = '';
		},
		/**
		 * 打开收银秤
		 */
		openCashierScale() {
			if (this.addon.includes('scale')) {
				if (!this.cashierScale) {
					getElectronicScaleInformation().then(res => {
						if (res.code == 0 && res.data) {
							this.cashierScale = res.data.config;
							try {
								this.$pos.send('OpenWeigher', `OS2X:${this.cashierScale.serialport}:${this.cashierScale.baudrate}`);
							} catch (e) {
								this.cashierScale = null
							}
						}
					})
				} else {
					try {
						this.$pos.send('OpenWeigher', `OS2X:${this.cashierScale.serialport}:${this.cashierScale.baudrate}`);
					} catch (e) {
						this.cashierScale = null
					}
				}
			}
		},
		/**
		 * 去皮
		 */
		tare() {
			this.$pos.send('Tare')
		},
		/**
		 * 清零
		 */
		zero() {
			this.$pos.send('Zero');
		},
		setActive(key) {
			this.$store.commit('billing/setActive', key);
		},
		paymentMoneyChange(event) {
			if (this.paymentMoney.length == 0) {
				// 如果没有输入则结账
				this.setActive('SelectGoodsAfter')
			} else {
				this.setActive('UnnumberedGoods')
			}
		},
		// 商品数量减少
		dec(data) {
			if (this.skuInfo) {
				if (this.skuInfo.num <= 1 && this.skuInfo.stock > 0) {
					this.skuInfo.num = 1;
				} else if(this.skuInfo.stock > 0) {
					this.skuInfo.num--;
				}
				this.$forceUpdate();
			}
		},
		// 商品数量增加
		inc(data) {
			if (this.skuInfo) {
				if (this.skuInfo.num >= this.skuInfo.stock && this.skuInfo.stock > 0) {
					this.skuInfo.num = this.skuInfo.stock;
				} else if(this.skuInfo.stock > 0){
					this.skuInfo.num++;
				}
				this.$forceUpdate();
			}
		},
		// 打开钱箱
		openCashBox() {
			this.$emit('openCashBox')
		},
		setGoodsCategoryShow(id, index) {
			if (!this.goodsCategoryShow) {
				if (id === 'all') {
					if (this.goodsCategory.length > 13) {
						this.goodsCategoryShow = !this.goodsCategoryShow
					} else {
						this.goodsCategoryId = id
					}
				} else {
					this.goodsCategoryId = id
				}
			} else {
				this.goodsCategoryId = id;
				this.goodsCategoryIndex = index;
				this.goodsCategoryShow = false
			}
		},
		setServiceCategoryShow(id, index) {
			if (!this.serviceCategoryShow) {
				if (id === 'all') {
					if (this.serviceCategory.length > 13) {
						this.serviceCategoryShow = !this.serviceCategoryShow
					} else {
						this.serviceCategoryId = id
					}

				} else {
					this.serviceCategoryId = id
				}
			} else {
				this.serviceCategoryId = id;
				this.serviceCategoryIndex = index;
				this.serviceCategoryShow = false
			}
		},
		isListShow() {
			this.goodsCategoryShow = false;
			this.serviceCategoryShow = false;
		},
		// 获取商品信息通过条形码
		getSkuByCode(code) {
			if (this.type != 'goods' || this.billingActive == 'ShowMember' || this.billingActive == 'OrderCreate') return;

			code = code.toString().trim();
			getGoodsInfoByCode(code).then(res => {
				if (res.code == 0) {
					if (res.data) {
						if (res.data.goods_state == 0) {
							this.$util.showToast({
								title: '该商品已下架'
							});
							return;
						}
						if (res.data.stock == 0) {
							this.$util.showToast({
								title: '该商品库存不足！'
							});
							return;
						}
						this.handleSelectGoods(res.data);
						this.scanCode = {
							lastTime: 0,
							code: ''
						};
						this.$store.commit('billing/setActive', 'SelectGoodsAfter'); // 记录页面当前活跃值：选择完商品
					} else {
						this.$util.showToast({
							title: '未找到该商品！'
						})
					}
				} else {
					this.$util.showToast({
						title: res.message,
					})
				}
			})
		},
		// 处理选中商品数据
		handleSelectGoods(data) {
			let key = 'sku_' + data.sku_id;
			let num = data.num || 1;
			num = parseInt(num);

			// 项目服务和称重商品每次都是新增，重新定义key
			if (data.goods_class == 4 || data.goods_class == 6) {
				if (data.status == 'edit') {
					// 编辑
					key = data.editKey;
				} else {
					//新增
					var index = 0;
					Object.keys(this.billingGoodsData).forEach(k => {
						if (k.indexOf(key) != -1) {
							index++;
						}
					});
					key += '_' + index;
				}

			}

			// 称重商品，计价方式：计重
			if (data.goods_class == 6 && data.pricing_type == 'weight') num = Number.parseFloat(data.weigh);

			let _billingGoodsData = this.$util.deepClone(this.billingGoodsData);

			// 已加入清单，追加数量
			if (_billingGoodsData[key]) {
				_billingGoodsData[key].num += num;
			} else {
				// 第一次加入清单，设置数据
				_billingGoodsData[key] = this.$util.deepClone(data);
				_billingGoodsData[key].num = num;
			}

			// 编辑商品数量
			if (data.status && data.status == 'edit') {
				_billingGoodsData[key].num = num;
			}

			// 称重商品每次覆盖数量
			if (data.goods_class == 6 && data.pricing_type == 'weight') {
				_billingGoodsData[key].num = parseFloat(_billingGoodsData[key].num.toFixed(3))
			}

			// 调整单价
			if (data.adjust_price) {
				_billingGoodsData[key].price = parseFloat(data.adjust_price);
				_billingGoodsData[key].adjust_price = parseFloat(data.adjust_price);
			}

			_billingGoodsData[key].is_adjust = data.is_adjust || false;

			this.$store.commit('billing/setGoodsData', _billingGoodsData);

		},
		/**
		 * 添加扫码监听事件
		 */
		addScanCodeEvent() {
			// #ifdef APP-PLUS
			plus.key.addEventListener('keyup', this.listenerScanCode, true);
			// #endif

			// #ifdef H5
			window.addEventListener('keypress', this.listenerScanCode, true);
			// #endif
		},
		/**
		 * 移除扫码监听事件
		 */
		removeScanCodeEvent() {
			// #ifdef APP-PLUS
			plus.key.removeEventListener('keyup', this.listenerScanCode, true);
			// #endif

			// #ifdef H5
			window.removeEventListener('keypress', this.listenerScanCode, true);
			// #endif
		},
		/**
		 * 监听扫码事件
		 * @param {Object} e
		 */
		listenerScanCode(e) {
			const clearBarCode = () => {
				this.scanCode = {
					lastTime: 0,
					code: ''
				};
				this.$store.commit('billing/setIsScanTrigger', false);
			};

			// #ifdef H5
			var currCode = e.keyCode || e.which || e.charCode;
			// #endif

			// #ifdef APP-PLUS
			const keyArr = {
				'keycode_7': 0,
				'keycode_8': 1,
				'keycode_9': 2,
				'keycode_10': 3,
				'keycode_11': 4,
				'keycode_12': 5,
				'keycode_13': 6,
				'keycode_14': 7,
				'keycode_15': 8,
				'keycode_16': 9
			};
			var currCode = keyArr['keycode_' + e.keyCode] || '';
			// #endif

			var currTime = new Date().getTime();
			if (this.scanCode.lastTime > 0) {
				if (currTime - this.scanCode.lastTime <= 100) {
					this.scanCode.code += String.fromCharCode(currCode);
					this.$store.commit('billing/setIsScanTrigger', true);
				} else if (currTime - this.scanCode.lastTime > 500) {
					// 输入间隔500毫秒清空
					clearBarCode();
				}
			} else {
				this.scanCode.code = String.fromCharCode(currCode);
			}
			this.scanCode.lastTime = currTime;

			// #ifdef H5
			var code = 13;
			// #endif

			// #ifdef APP-PLUS
			var code = 66;
			// #endif

			if (currCode == code) {

				// 扫码枪
				if (this.scanCode.code && this.scanCode.code.length >= 8) {
					this.scanCodeSearch = true;
					this.getSkuByCode(this.scanCode.code);
					this.$store.commit('billing/setIsScanTrigger', true);
				}

				// 回车输入后清空
				clearBarCode();
			}

		},
		/**
		 * 添加键盘监听事件
		 */
		addKeyDownEvent() {
			// #ifdef H5
			window.addEventListener("keydown", this.listenerKeyDown, true);
			// #endif
		},
		/**
		 * 移除键盘监听事件
		 */
		removeKeyDownEvent() {
			// #ifdef H5
			window.removeEventListener("keydown", this.listenerKeyDown, true);
			// #endif
		},
		// 监听键盘按下事件
		listenerKeyDown(e) {
			var code = e.code;

			if (this.billingActive == 'SelectGoodsSku') {

				// 打开商品规格项弹出框
				if (code == 'Enter' || code == 'NumpadEnter') {
					this.skuConfirm();
				}

			} else if (this.billingActive == 'UnnumberedGoods') {
				// 无码商品

				if (code == 'Enter' || code == 'NumpadEnter') {

					if (!this.billingIsScanTrigger) this.paymentMoneyConfirm();

				}

			}
		},
		//打开库存转换
		stockTransform() {
			this.$refs.stockTransformRef.open(this.goodsItems.goods_id,this.goodsItems.sku_id);
		},
		//库存转换回调
		saveStockTransform(){
			this.goodsSelect(this.goodsItems)
			this.getGoods();
		}
	},
};
/**
 * 监听重量变化
 * @param {Object} text
 */
window.WEIGHER_DATA_CALLBACK = function (text) {
	let data = text.split(':');
	self.$set(self.skuInfo, 'weigh', data[0] != '-' ? data[0] : '')
};
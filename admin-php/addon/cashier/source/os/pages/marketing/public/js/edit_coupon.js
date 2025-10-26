import {
	getCouponDetail,
	addCoupon,
	editCoupon,
} from '@/api/marketing.js'

export default {
	data() {
		return {
			couponsData: {
				coupon_type_id: '',
				coupon_name: "",
				type: "reward",
				money: "",
				discount: "",
				discount_limit: "",
				at_least: "",
				is_show: 1,
				count: "",
				max_fetch: "",
				image: "",
				validity_type: 0,
				end_time: this.$util.timeFormat((Date.parse(new Date()) / 1000) + (10 * 24 * 60 * 60)),
				fixed_term: 0,
				goods_type: 1,
				goods_ids: '',
				goods_ids_real:'',
				goods_list: [],
				goods_names:'',
				use_channel:'all',
			},
			flag: false,
			goods_ids: [],
			typeList: [{
				value: 'reward',
				text: '满减'
			}, {
				value: 'discount',
				text: '折扣'
			},],
			validityTypeList: [{
				value: 0,
				text: '固定时间'
			}, {
				value: 1,
				text: '领取之日起'
			}, {
				value: 2,
				text: '长期有效'
			}],
			goodsTypeList: [{
				value: 1,
				text: '全部商品参与'
			}, {
				value: 2,
				text: '指定商品参与'
			}, {
				value: 3,
				text: '指定商品不参与'
			}, {
				value: 4,
				text: '指定分类参与'
			}, {
				value: 5,
				text: '指定分类不参与'
			}],
			
			useChannelList: [{
				value: 'all',
				text: '线上线下使用'
			}, {
				value: 'online',
				text: '线上使用'
			}, {
				value: 'offline',
				text: '线下使用'
			}],
			dialogVisible: false
		};
	},
	onLoad(option) {
		if (option.coupon_type_id) {
			this.couponsData.coupon_type_id = option.coupon_type_id
			this.getData(option.coupon_type_id)
		}
	},
	watch: {
		'couponsData.validity_type'(newValue) {
			if (newValue === 0) this.couponsData.end_time = this.$util.timeFormat((Date.parse(new Date()) / 1000) + (10 * 24 * 60 * 60))
		}
	},
	methods: {
		getData(coupon_type_id) {
			getCouponDetail(coupon_type_id).then(res => {
				let data = res.data;
				if (res.code >= 0 && data) {
					Object.keys(this.couponsData).forEach(key => {
						this.couponsData[key] = data.info[key]
						if (key == 'end_time') this.couponsData[key] = this.couponsData.end_time = this.$util.timeFormat(Date.parse(new Date(data.info[key])))
					})
				}
				this.goods_ids = this.couponsData.goods_list.map(v => v.goods_id)
				this.couponsData.goods_ids = this.goods_ids.join()
			})
		},
		addImg() {
			this.$util.upload(1, {
				path: 'image'
			}, res => {
				if (res.length > 0) {
					this.couponsData.image = res[0];
					this.$forceUpdate();
				}
			});
		},
		checkIsShow(e) {
			this.couponsData.is_show = e.detail.value ? 1 : 0
		},
		changeTime(data) {
			this.couponsData.end_time = data;
		},
		selectGoods(data) { //选择数据
			data.forEach(el => {
				if (!this.goods_ids.includes(el.goods_id)) {
					this.goods_ids.push(el.goods_id)
					this.couponsData.goods_list.push(el)
				}
			})
		},
		delGoods(id) {//删除已选择的商品
			this.couponsData.goods_list.splice(this.goods_ids.indexOf(id), 1);
			this.goods_ids.splice(this.goods_ids.indexOf(id), 1);
		},
		checkData() {
			let _this = this
			let verify = {
				days: function (value) {
					if (_this.couponsData.validity_type == 1) {
						if (value % 1 != 0) {
							return '请输入整数';
						}
						if (value <= 0) {
							return '有效天数不能小于等于0';
						}
						return ''
					}
					return ''
				},
				number: function (value) {
					if (value < 0) {
						return '请输入不小于0的数!'
					}
					return ''
				},
				coupon_money: function (value) {
					if (parseFloat(value) > 10000) {
						return '优惠券面额不能大于10000'
					}
					if (parseFloat(value) <= 0) {
						return '优惠券面额不能小于0'
					}
					return ''
				},
				int: function (value) {
					if (value % 1 != 0) {
						return '最多优惠,请输入整数!'
					}
					if (value < 0) {
						return '最多优惠,请输入大于0的数!'
					}
					return ''
				},
				money: function (value) {
					if (value < 0) {
						return '金额不能小于0'
					}
					var arrMen = value.split(".");
					var val = 0;
					if (arrMen.length == 2) {
						val = arrMen[1];
					}
					if (val.length > 2) {
						return '保留小数点后两位'
					}
					return ''
				},
				time: function (value) {
					if (_this.couponsData.validity_type == 0) {
						var now_time = (new Date()).getTime();
						var end_time = (new Date(value)).getTime();
						if (now_time > end_time) {
							return '结束时间不能小于当前时间!'
						}
						return ''
					}
					return ''
				},
				max: function (value) {
					if (!/[\S]+/.test(value)) {
						return '请输入最大领取数量';
					}

					if (_this.couponsData.count != -1 && parseFloat(value) > parseFloat(_this.couponsData.count)) {
						return '最大领取数量不能超过发放数量!';
					}
					return ''
				},
				fl: function (value, str) {
					str = str.substring(0, str.length - 1);

					if (value < 1) {
						return str + "不能小于1折";
					}

					if (value > 9.9) {
						return str + "不能大于9.9折";
					}

					var arrMen = value.split(".");
					var val = 0;
					if (arrMen.length == 2) {
						val = arrMen[1];
					}
					if (val.length > 2) {
						return str + "最多可保留两位小数";
					}
					return ''
				},
				count: function (value) {
					if (!/[\S]+/.test(value)) {
						return '请输入发放数量';
					}
					if (value % 1 != 0) {
						return '请输入整数';
					}
					if (value == 0) {
						return '发放数量不能为0';
					}
					if (value != -1 && parseInt(value) < parseInt('{$coupon_type_info.count}')) {
						return '发放数量不能小于原发放数量!';
					}
					return ''
				}
			};

			if (!this.couponsData.coupon_name) {
				this.$util.showToast({
					title: "请输入优惠券名称"
				});
				return false
			}
			if (!this.couponsData.type) {
				this.$util.showToast({
					title: "请选择优惠券类型"
				});
				return false
			}
			if (this.couponsData.type === 'reward') {
				if (!this.couponsData.money) {
					this.$util.showToast({
						title: "请输入优惠券面额"
					});
					return false
				}
				if (verify.number(this.couponsData.money) || verify.money(this.couponsData.money) || verify.coupon_money(this.couponsData.money)) {
					this.$util.showToast({
						title: verify.number(this.couponsData.money) || verify.money(this.couponsData.money) || verify.coupon_money(this.couponsData.money)
					});
					return false
				}
			} else {
				if (!this.couponsData.discount) {
					this.$util.showToast({
						title: "请输入优惠券折扣"
					});
					return false
				}
				if (verify.fl(this.couponsData.discount, '优惠券折扣')) {
					this.$util.showToast({
						title: verify.fl(this.couponsData.discount, '优惠券折扣')
					});
					return false
				}
			}
			if (this.couponsData.discount_limit) {
				if (verify.number(this.couponsData.discount_limit) || verify.int(this.couponsData.discount_limit)) {
					this.$util.showToast({
						title: verify.number(this.couponsData.discount_limit) || verify.int(this.couponsData.discount_limit)
					});
					return false
				}
			}
			if (!this.couponsData.at_least) {
				this.$util.showToast({
					title: "请输入满多少元可以使用"
				});
				return false
			}
			if (verify.number(this.couponsData.at_least) || verify.money(this.couponsData.at_least)) {
				this.$util.showToast({
					title: verify.number(this.couponsData.at_least) || verify.money(this.couponsData.at_least)
				});
				return false
			}
			if (this.couponsData.is_show === 1) {
				if (verify.count(this.couponsData.count)) {
					this.$util.showToast({
						title: verify.count(this.couponsData.count)
					});
					return false
				}
				if (verify.max(this.couponsData.max_fetch)) {
					this.$util.showToast({
						title: verify.max(this.couponsData.max_fetch)
					});
					return false
				}
			}
			if (verify.time(this.couponsData.end_time)) {
				this.$util.showToast({
					title: verify.time(this.couponsData.end_time)
				});
				return false
			}
			if (verify.days(this.couponsData.fixed_term)) {
				this.$util.showToast({
					title: verify.days(this.couponsData.fixed_term)
				});
				return false
			}
			if (this.couponsData.goods_type == 2||this.couponsData.goods_type == 3) {
				if (!this.goods_ids.length) {
					this.$util.showToast({
						title: '请选择活动商品'
					});
					return false
				}
			}
			if (this.couponsData.goods_type == 4||this.couponsData.goods_type == 5) {
				if (!this.goods_ids.length) {
					this.$util.showToast({
						title: '请选择商品分类'
					});
					return false
				}
			}
			return true
		},
		goodsType(){
				this.couponsData.goods_ids = ''
				this.couponsData.goods_ids_real = ''
				this.goods_ids = []
				this.couponsData.goods_names = ''
		},
		goodsCategoryConfirm(obj){
			this.goods_ids = obj.id_arr;
			this.couponsData.goods_names = obj.name_arr.join('、');
		},
		saveFn() {
			if (this.checkData(this.couponsData)) {
				if (this.flag) return false;
				this.flag = true;
				if (this.couponsData.goods_type != 1) this.couponsData.goods_ids = this.goods_ids.join();
				let save = this.couponsData.coupon_type_id ? editCoupon : addCoupon;
				save(this.couponsData).then(res => {
					this.flag = false;
					this.$util.showToast({
						title: res.message
					});
					if (res.code >= 0) {
						setTimeout(() => {
							this.backFn();
						}, 500);
					}
				});
			}
		},
		backFn() {
			this.$util.redirectTo('/pages/marketing/coupon_list');
		},
	}
};
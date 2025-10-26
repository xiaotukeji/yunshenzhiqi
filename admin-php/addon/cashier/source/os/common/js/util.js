import Config from './config.js'
import Store from '@/store/index.js'

export default {
	/**
	 * 页面跳转
	 * @param {string} to 跳转链接 /pages/idnex/index
	 * @param {Object} param 参数 {key : value, ...}
	 * @param {string} mode 模式
	 */
	redirectTo(to, param, mode) {
		let tabbar = [
			"/pages/billing/index", // 开单
			"/pages/reserve/index", // 预约
			"/pages/buycard/index", // 售卡
			"/pages/recharge/index", // 充值
			"/pages/verify/index" // 核销
		];
		if (tabbar.indexOf(to) != -1) mode = 'tabbar';

		Store.commit('app/setCurrRoute', to);
		let url = to;
		if (param != undefined) {
			Object.keys(param).forEach(function (key) {
				if (url.indexOf('?') != -1) {
					url += "&" + key + "=" + param[key];
				} else {
					url += "?" + key + "=" + param[key];
				}
			});
		}
		switch (mode) {
			case 'tabbar':
				// 跳转到 tabBar 页面，并关闭其他所有非 tabBar 页面。
				uni.switchTab({
					url
				});
				break;
			case 'redirectTo':
				// 关闭当前页面，跳转到应用内的某个页面。
				uni.redirectTo({
					url
				});
				break;
			case 'reLaunch':
				// 关闭所有页面，打开到应用内的某个页面。
				uni.reLaunch({
					url
				});
				break;
			default:
				// 保留当前页面，跳转到应用内的某个页面
				uni.navigateTo({
					url
				});
		}
	},
	/**
	 * 图片路径转换
	 * @param {String} img_path 图片地址
	 * @param {Object} params 参数，针对商品、相册里面的图片区分大中小，size: big、mid、small
	 */
	img(img_path, params) {
		var path = "";
		if (img_path != undefined && img_path != "") {
			if (img_path.split(',').length > 1) {
				img_path = img_path.split(',')[0];
			}
			if (params && img_path) {
				// 过滤默认图
				let arr = img_path.split(".");
				let suffix = arr[arr.length - 1];
				arr.pop();
				arr[arr.length - 1] = arr[arr.length - 1] + "_" + params.size.toUpperCase();
				arr.push(suffix);
				img_path = arr.join(".");
			}
			if (img_path.indexOf("http://") == -1 && img_path.indexOf("https://") == -1) {
				path = Config.imgDomain + "/" + img_path;
			} else {
				path = img_path;
			}
		}
		return path;
	},
	/**
	 * 验证当前数组或对象是否为空
	 * @param param 校验对象
	 */
	checkIsNotNull(param) {
		if (param) {
			if (typeof (param) == 'object') {
				if (Array.isArray(param)) {
					if (param.length > 0) return true
				} else {
					if (JSON.stringify(param) != '{}') return true
				}
			} else {
				return true;
			}
		}
		return false;
	},
	/**
	 * 金额格式化
	 * @param {Object} money
	 */
	moneyFormat(money) {
		if (isNaN(parseFloat(money))) return money;
		return parseFloat(money).toFixed(2);
	},
	/**
	 * 时间格式化
	 * @param {Object} time 时间戳
	 * @param {Object} format 输出格式
	 */
	timeFormat(time, format = 'y-m-d h:i:s') {
		//一律用小写转换
		format = format.toLowerCase();
		if(time == 0) return '--';
		var date = new Date();
		date.setTime(time * 1000);

		var y = date.getFullYear();
		var m = date.getMonth() + 1;
		var d = date.getDate();
		var h = date.getHours();
		var i = date.getMinutes();
		var s = date.getSeconds();

		format = format.replace('y', y);
		format = format.replace('m', (m < 10 ? '0' + m : m));
		format = format.replace('d', (d < 10 ? '0' + d : d));
		format = format.replace('h', (h < 10 ? '0' + h : h));
		format = format.replace('i', (i < 10 ? '0' + i : i));
		format = format.replace('s', (s < 10 ? '0' + s : s));

		return format;
	},
	/**
	 * 日期格式转时间戳
	 * @param {Object} string
	 */
	timeTurnTimeStamp(string) {
		var f = string.split(' ', 2);
		var d = (f[0] ? f[0] : '').split('-', 3);
		var t = (f[1] ? f[1] : '').split(':', 3);
		return (new Date(
			parseInt(d[0], 10) || null,
			(parseInt(d[1], 10) || 1) - 1,
			parseInt(d[2], 10) || null,
			parseInt(t[0], 10) || null,
			parseInt(t[1], 10) || null,
			parseInt(t[2], 10) || null
		)).getTime() / 1000;
	},
	/**
	 * 获取当前页面路由
	 */
	getCurrRoute() {
		let routes = getCurrentPages(); // 获取当前打开过的页面路由数组
		return routes.length ? routes[routes.length - 1].route : '';
	},
	/**
	 * 显示消息提示框
	 *  @param {Object} params 参数
	 */
	showToast(params = {}) {
		params.title = params.title || "";
		params.icon = params.icon || "none";
		// params.position = params.position || 'bottom';
		params.duration = params.duration || 1500;
		uni.showToast(params);
		if (params.success) params.success();
	},
	/*
	 * 深度拷贝对象
	 * @param {Object} obj
	 */
	deepClone(obj) {
		const isObject = function (obj) {
			return typeof obj == 'object';
		}

		if (!isObject(obj)) {
			throw new Error('obj 不是一个对象！')
		}
		//判断传进来的是对象还是数组
		let isArray = Array.isArray(obj)
		let cloneObj = isArray ? [] : {}
		//通过for...in来拷贝
		for (let key in obj) {
			cloneObj[key] = isObject(obj[key]) ? this.deepClone(obj[key]) : obj[key]
		}
		return cloneObj
	},
	/**
	 * 图片选择加上传
	 * @param num
	 * @param params
	 * @param callback
	 * @param url
	 */
	upload: function (num, params, callback, url) {
		const app_type = 'pc';
		const app_type_name = 'PC';

		var data = {
			token: uni.getStorageSync('cashierToken'),
			app_type: app_type,
			app_type_name: app_type_name
		}
		data = Object.assign(data, params);

		var imgs_num = num;
		var _self = this;

		uni.chooseImage({
			count: imgs_num,
			sizeType: ['compressed'], //可以指定是原图还是压缩图，默认二者都有
			sourceType: ['album', 'camera'], //从相册或者拍照
			success: async function (res) {
				const tempFilePaths = res.tempFilePaths;
				var _data = data;
				var imgs = [];
				for (var i = 0; i < tempFilePaths.length; i++) {
					var path = await _self.upload_file_server(tempFilePaths[i], _data, params.path, url);
					imgs.push(path);
				}

				typeof callback == 'function' && callback(imgs);
			},
			fail: err => {
				console.log('图片上传错误', err)
			}
		});
	},
	//上传
	upload_file_server(tempFilePath, data, path, url = "") {
		if (url) {
			var uploadUrl = Config.baseUrl + url
		} else {
			var uploadUrl = Config.baseUrl + '/cashier/storeapi/upload/' + path
		}
		data.site_id = uni.getStorageSync('siteId');
		return new Promise((resolve, reject) => {
			uni.uploadFile({
				url: uploadUrl,
				filePath: tempFilePath,
				name: 'file',
				formData: data,
				success: function (res) {
					var path_str = JSON.parse(res.data);
					if (path_str.code >= 0) {
						resolve(path_str.data.pic_path);
					} else {
						reject("error");
					}
				},
			});
		});
	},
	/**
	 * 验证手机号
	 * @param  {string} mobile 被验证的mobile
	 * @return {object}   验证后的结果
	 **/
	verifyMobile(mobile) {
		var parse =/^(13[0-9]|14[01456879]|15[0-35-9]|16[2567]|17[0-8]|18[0-9]|19[0-35-9])\d{8}$/.test(mobile);
		return parse;
	},
	clearStoreData() {
		Store.commit('app/setGlobalStoreInfo', null);
		Store.commit('app/setGlobalStoreId', 0);
		Store.commit('app/setGlobalMemberInfo', null);
		Store.commit('app/setUserInfo', null);
		Store.commit('app/setMenu', []);

		// 开单
		Store.commit('billing/setGoodsData', {});
		Store.commit('billing/setPendOrderId',0);
		Store.commit('billing/setGoodsIds', []);
		Store.commit('billing/setOrderData', {});
		Store.commit('billing/setActive', '');
		Store.commit('billing/setIsScanTrigger', false);

		// 售卡
		Store.commit('buycard/setGoodsData', {});
		Store.commit('buycard/setOrderData', {});
		Store.commit('buycard/setActive', '');

		// 充值
		Store.commit('recharge/setActive', '');
	},
	/**
	 * 颜色减值
	 * @param {Object} c1
	 * @param {Object} c2
	 * @param {Object} ratio
	 */
	colourBlend(c1, c2, ratio) {
		ratio = Math.max(Math.min(Number(ratio), 1), 0)
		let r1 = parseInt(c1.substring(1, 3), 16)
		let g1 = parseInt(c1.substring(3, 5), 16)
		let b1 = parseInt(c1.substring(5, 7), 16)
		let r2 = parseInt(c2.substring(1, 3), 16)
		let g2 = parseInt(c2.substring(3, 5), 16)
		let b2 = parseInt(c2.substring(5, 7), 16)
		let r = Math.round(r1 * (1 - ratio) + r2 * ratio)
		let g = Math.round(g1 * (1 - ratio) + g2 * ratio)
		let b = Math.round(b1 * (1 - ratio) + b2 * ratio)
		r = ('0' + (r || 0).toString(16)).slice(-2)
		g = ('0' + (g || 0).toString(16)).slice(-2)
		b = ('0' + (b || 0).toString(16)).slice(-2)
		return '#' + r + g + b
	},
	//商品类型字典
	goodsClassDict:{
		real:1,
		virtual:2,
		virtualcard:3,
		service:4,
		card:5,
		weigh:6,
	},
	setLocalConfig(obj){
		var local_config = this.getLocalConfig();
		local_config = Object.assign(local_config, obj);
		uni.setStorageSync('local_config', local_config);
	},
	getLocalConfig(){
		var local_config = uni.getStorageSync('local_config');
		if(!local_config) local_config = {};
		local_config = Object.assign({
			printerSelectType:'all',
			printerSelectIds:[],
		}, local_config);
		return local_config;
	},
}
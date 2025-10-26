import Config from './config.js'
import Util from './util.js'

const app_type = 'pc';
const app_type_name = 'PC';

export default {
	sendRequest(params) {
		var method = params.method ?? 'POST', // 请求方式
			url = Config.baseUrl + params.url, // 请求路径
			data = {
				app_type,
				app_type_name
			};

		if (uni.getStorageSync('cashierToken')) data.token = uni.getStorageSync('cashierToken');

		if (uni.getStorageSync('siteId')) data.site_id = uni.getStorageSync('siteId');

		if (uni.getStorageSync('globalStoreId')) data.store_id = uni.getStorageSync('globalStoreId');

		if (params.data != undefined) Object.assign(data, params.data);

		if (params.async === false) {
			//同步
			return new Promise((resolve, reject) => {
				uni.request({
					url: url,
					method: method,
					data: data,
					header: params.header || {
						'content-type': 'application/x-www-form-urlencoded;application/json'
					},
					dataType: params.dataType || 'json',
					responseType: params.responseType || 'json',
					success: (res) => {
						if (res.data.code == -10009 || res.data.code == -10010) {
							uni.removeStorage({
								key: 'cashierToken'
							});
							if (Util.getCurrRoute() != 'pages/login/login') {
								Util.redirectTo('/pages/login/login', {}, 'reLaunch');
								return;
							}
						}
						resolve(res.data);
					},
					fail: (res) => {
						reject(res);
					},
					complete: (res) => {
						// reject(res);
					}
				});
			})
		} else {
			//异步
			uni.request({
				url: url,
				method: method,
				data: data,
				header: params.header || {
					'content-type': 'application/x-www-form-urlencoded;application/json'
				},
				dataType: params.dataType || 'json',
				responseType: params.responseType || 'text',
				success: (res) => {
					if (res.data.code == -10009 || res.data.code == -10010) {
						uni.removeStorage({
							key: 'cashierToken'
						});
						if (Util.getCurrRoute() != 'pages/login/login') {
							Util.redirectTo('/pages/login/login', {}, 'reLaunch');
							return;
						}
					}
					typeof params.success == 'function' && params.success(res.data);
				},
				fail: (res) => {
					typeof params.fail == 'function' && params.fail(res);
				},
				complete: (res) => {
					typeof params.complete == 'function' && params.complete(res);
				}
			});
		}
	},
	post(url, params) {
		const option = {
			url,
			method: 'post',
			async: false
		};
		return this.sendRequest({
			...params,
			...option
		});
	},
	get(url, params) {
		const option = {
			url,
			method: 'get',
			async: false
		};
		return this.sendRequest({
			...params,
			...option
		});
	}
}
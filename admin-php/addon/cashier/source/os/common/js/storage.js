import Config from './config.js'

const prefix = Config.baseUrl.replace(/(http:\/\/)|(https:\/\/)/g, '');
var oldPrefix = uni.getStorageSync('prefix');

// 域名不一致，清空
if (oldPrefix != prefix) {
	uni.clearStorageSync();
}
uni.setStorageSync('prefix', prefix);

const handleKey = (key) => {
	const storageKey = (prefix ? `${prefix}_` : '') + key;
	return storageKey;
};

export function uniStorage() {
	const setStorageSync = uni.setStorageSync;
	const setStorage = uni.setStorage;
	const getStorage = uni.getStorage;
	const getStorageSync = uni.getStorageSync;
	const removeStorage = uni.removeStorage;
	const removeStorageSync = uni.removeStorageSync;

	uni.setStorage = (options) => {
		options.key = handleKey(options.key);
		setStorage(options)
	};

	uni.setStorageSync = (key, data) => {
		setStorageSync(handleKey(key), data)
	};

	uni.getStorage = (options) => {
		options.key = handleKey(options.key);
		getStorage(options)
	};

	uni.getStorageSync = (key) => {
		return getStorageSync(handleKey(key))
	};

	uni.removeStorage = (options) => {
		options.key = handleKey(options.key);
		removeStorage(options)
	};

	uni.removeStorageSync = (key) => {
		return removeStorageSync(handleKey(key))
	}
}
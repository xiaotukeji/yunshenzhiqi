<script>
import config from 'common/js/config.js';
export default {
	onLaunch(data) {
		if (uni.getStorageSync('baseUrl') != config.baseUrl) {
			uni.clearStorageSync();
		}
		uni.setStorageSync('baseUrl', config.baseUrl);

		// #ifdef MP
		const updateManager = uni.getUpdateManager();
		updateManager.onCheckForUpdate(function(res) {
			// 请求完新版本信息的回调
		});

		updateManager.onUpdateReady(function(res) {
			uni.showModal({
				title: '更新提示',
				content: '新版本已经准备好，是否重启应用？',
				success(res) {
					if (res.confirm) {
						// 新的版本已经下载好，调用 applyUpdate 应用新版本并重启
						updateManager.applyUpdate();
					}
				}
			});
		});

		updateManager.onUpdateFailed(function(res) {
			// 新的版本下载失败
		});
		// #endif

		// #ifdef H5
		if (uni.getSystemInfoSync().platform == 'ios') {
			uni.setStorageSync('initUrl', location.href);
		}
		// #endif

		uni.onNetworkStatusChange(function(res) {
			if (!res.isConnected) {
				uni.showModal({
					title: '网络失去链接',
					content: '请检查网络链接',
					showCancel: false
				});
			}
		});
	},
	onShow: function() {

		//全局插件
		this.$store.dispatch('getAddonIsexit');
		this.$store.dispatch('getDefaultImg');
		this.$store.dispatch('getShopInfo');

	},
	onHide: function() {},
	methods: {}
};
</script>
<style lang="scss">
@import url('/common/css/iconfont.css');
@import './common/css/main.scss';
</style>

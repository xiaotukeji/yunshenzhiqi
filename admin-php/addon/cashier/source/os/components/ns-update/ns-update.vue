<template>
	<view>
		<uni-popup ref="updatePopup" type="center" :maskClick="false" v-if="versionInfo">
			<view class="update-wrap">
				<view class="head"><image src="@/static/cashier/update_header.png" /></view>
				<view class="body">
					<view class="version-no">版本号：{{ versionInfo.version }}</view>
					<view class="title">更新内容</view>
					<view class="desc common-scrollbar">{{ versionInfo.update_desc }}</view>
					<button type="default" class="primary-btn" @click="update">立即更新</button>
					<view class="giveup-update" @click="giveupUpdate" v-if="!versionInfo.is_force_upgrade">以后再说</view>
				</view>
			</view>
		</uni-popup>
	</view>
</template>

<script>
/**
 * app版本更新
 */
import {checkUpdate} from '@/api/config.js'

export default {
	data() {
		return {
			versionInfo: null
		};
	},
	created() {
		// wifi模式下才检测升级
		if (plus.networkinfo.getCurrentType() == plus.networkinfo.CONNECTION_WIFI) {
			this.checkUpdateFn();
		}
	},
	methods: {
		/**
		 * 检测是否有新版本
		 */
		checkUpdateFn() {
			checkUpdate({
				app_key: this.$config.app.app_key,
				version: this.$config.app.version_no,
				platform: uni.getSystemInfoSync().platform
			}).then(res=>{
				if (res.code == 0 && res.data) {
					this.versionInfo = res.data;
					if (!uni.getStorageSync('version_' + this.versionInfo.version_no)) {
						this.$refs.updatePopup.open();
					}
				}
			})
		},
		/**
		 * 确认更新
		 */
		update() {
			let systemInfo = uni.getSystemInfoSync();
			if (systemInfo.platform == 'android') {
				uni.showLoading({});
				uni.downloadFile({
					url: this.$util.img(this.versionInfo.package_path),
					success: data => {
						uni.hideLoading();
						if (data.statusCode === 200) {
							plus.runtime.install(
								data.tempFilePath,
								{
									force: false
								},
								function() {
									plus.runtime.restart();
								}
							);
						}
					},
					fail: res => {
						this.$util.showToast({ title: '安装包下载失败' });
						uni.hideLoading();
					}
				});
			} else if (systemInfo.platform == 'ios') {
				plus.runtime.launchApplication({ action: this.versionInfo.package_path }, e => {
					this.$util.showToast({ title: e.message });
					this.$refs.updatePopup.close();
				});
			}
		},
		/**
		 * 放弃本次更新
		 */
		giveupUpdate() {
			uni.setStorageSync('version_' + this.versionInfo.version_no, 1);
			this.$refs.updatePopup.close();
		}
	}
};
</script>

<style lang="scss" scoped>
.update-wrap {
	width: 3rem;

	.head {
		height: 0.98rem;

		image {
			width: 3rem;
			height: 0.98rem;
		}
	}

	.body {
		padding: 0.2rem 0.3rem;
		background: #fff;

		.version-no {
			margin-bottom: 0.15rem;
		}

		.desc {
			max-height: 1rem;
		}

		.title {
			font-size: 0.16rem;
			font-weight: 700;
			margin-bottom: 0.15rem;
		}

		.primary-btn {
			margin-top: 0.15rem;
		}

		.giveup-update {
			margin-top: 0.15rem;
			text-align: center;
			line-height: 1;
		}
	}
}

/deep/ .uni-popup {
	z-index: 1010;
}
</style>

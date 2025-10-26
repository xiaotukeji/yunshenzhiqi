<template>
	<unipopup ref="promotionPop" type="center">
		<view class="promotion-pop">
			<view class="header flex justify-between">
				<view class="title">推广</view>
				<view class="pop-header-close" @click="$refs.promotionPop.close()">
					<text class="iconguanbi1 iconfont"></text>
				</view>
			</view>
			<view class="body">
				<view class="alter">活动可分享至多个渠道推广，增加曝光率，提升分享打开率。</view>
				<view class="flex content">
					<view class="qrCode flex items-center justify-center">
						<image v-if="qrData[APPType]&&qrData[APPType].path" :src="$util.img(qrData[APPType].path)"/>
						<text v-else>小程序配置错误</text>
					</view>
					<view class="flex-1 right">
						<view class="form-box">
							<view class="form-content">
								<view class="form-item flex">
									<view class="form-label">充值方式：</view>
									<view class="form-inline">
										<uni-data-checkbox v-model="APPType" :localdata="appTypeArray" />
									</view>
								</view>
								<view class="form-item link" v-if="APPType == 'h5'&&qrData[APPType]&&qrData[APPType].url">
									<view class="form-label">
										推广链接：
									</view>
									<view class="form-inline flex items-center">
										<input type="text" disabled v-model="qrData[APPType].url" @keydown.enter="search('enter')" />
										<button type="default" class="btn" @click="copyTextToClipboard(qrData[APPType].url)">复制</button>
									</view>
								</view>
								<view class="form-item" v-if="qrData[APPType]&&qrData[APPType].path">
									<text class="download" @click="download($util.img(qrData[APPType].path))">下载二维码</text>
								</view>
							</view>
						</view>
					</view>
				</view>
			</view>
		</view>
	</unipopup>
</template>

<script>
	import unipopup from '@/components/uni-popup/uni-popup.vue';
	import index from './index.js';
	export default {
		components: {
			unipopup,
		},
		mixins: [index]
	};
</script>

<style lang="scss" scoped>
	@import './index.scss';
</style>
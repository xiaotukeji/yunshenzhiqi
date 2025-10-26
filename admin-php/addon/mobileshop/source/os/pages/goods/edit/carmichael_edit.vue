<template>
	<view>
		<view class="carmichael">
			<input
				type="text"
				v-for="(item, index) in goodsCarmichael"
				@blur="onblur($event, index)"
				:key="index"
				:value="item"
				placeholder="请输入卡密内容"
				placeholder-class="placeholder"
			/>
			<view @click="addSpec()" class="color-base-text add-spec">+添加卡密</view>
			<view class="color-base-text tips">注：添加卡密格式为卡号+空格+密码，一行一组，如AAAA BBBB</view>
		</view>
		<view class="footer-wrap"><button type="primary" @click="save()">保存</button></view>
	</view>
</template>

<script>
export default {
	data() {
		return {
			goodsCarmichael: [],
			type: 0
		};
	},
	onLoad(param) {
		if (param) {
			this.type = param.type;
			this.specName = param.specName;
		}
		if (!uni.getStorageSync('editGoodsCarmichael') || uni.getStorageSync('editGoodsCarmichael').length <= 0) return;
		this.goodsCarmichael = JSON.parse(uni.getStorageSync('editGoodsCarmichael'));
	},
	methods: {
		onblur(e, index) {
			let value = e.detail.value;
			for (var i = 0; i < this.goodsCarmichael.length; i++) {
				if (index == i) {
					this.goodsCarmichael[i] = value;
				}
			}
		},
		save() {
			if (this.type == 1) {
				uni.setStorageSync('specName', this.specName);
			}
			if (this.goodsCarmichael.length > 0 && this.goodsCarmichael[0] != '') {
				uni.setStorageSync('editGoodsCarmichael', JSON.stringify(this.goodsCarmichael));
			}
			uni.navigateBack({
				delta: 1
			});
		},
		addSpec() {
			for (var i = 0; i < this.goodsCarmichael.length; i++) {
				if (this.goodsCarmichael[i] == '') {
					uni.showToast({
						icon: 'none',
						title: '请填写完再添加！'
					});
					return;
				}
			}
			this.goodsCarmichael.push('');
		}
	},
	onHide() {
		if (this.type == 1) {
			uni.setStorageSync('specName', this.specName);
		}
		if (this.goodsCarmichael.length > 0 && this.goodsCarmichael[0] != '') {
			uni.setStorageSync('editGoodsCarmichael', JSON.stringify(this.goodsCarmichael));
		}
	},
	onUnload() {
		if (this.type == 1) {
			uni.setStorageSync('specName', this.specName);
		}
		if (this.goodsCarmichael.length > 0 && this.goodsCarmichael[0] != '') {
			uni.setStorageSync('editGoodsCarmichael', JSON.stringify(this.goodsCarmichael));
		}
	}
};
</script>

<style lang="scss">
@import '../css/edit.scss';
.carmichael {
	margin: $margin-updown $margin-both;

	input {
		background: #fff;
		height: 100rpx;
		border-radius: 10rpx;
		padding: 0 20rpx;
		margin-bottom: 20rpx;
	}
	.placeholder {
		font-size: 28rpx;
		letter-spacing: 2rpx;
	}
	.add-spec {
		margin-top: 20rpx;
		text-align: center;
		background-color: #fff;
		height: 100rpx;
		line-height: 100rpx;
		border-radius: $border-radius;
	}
	.tips {
		font-size: 16rpx;
		margin-top: 6px;
	}
}
</style>

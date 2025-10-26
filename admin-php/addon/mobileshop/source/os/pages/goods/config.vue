<template>
	<view class="goods">
		<view class="order-setuo">
			<view class="order-title">商品设置</view>
			<view class="order-list">
				<view class="list-left">排序方式:</view>
				<view class="list-right">
					<view class="uni-list-cell-db">
						<picker @change="bindPickerChange" :value="index" :range="array" style="display: inline-block;">
							<view class="uni-input">{{ array[index] }}</view>
						</picker>
					</view>
					<text class="iconfont iconright"></text>
				</view>
			</view>
			<view class="order-list">
				<view class="list-left">默认排序值:</view>
				<view class="list-right"><input type="number" v-model="goodsConfig.goods_sort_config.default_value" placeholder="0" /></view>
			</view>
			<view class="order-list">
				<view class="list-left">默认搜索关键词:</view>
				<view class="list-right"><input type="text" v-model="goodsConfig.default_words.words" placeholder="0" /></view>
			</view>

			<view class="order-list"><view class="list-left">热门搜索:</view></view>
			<view class="more-spec" :class="{ 'safe-area': isIphonex }">
				<view class="spec-item" v-for="(item, index) in words_array" :key="index">
					<view class="spec-name">
						<text class="action iconfont iconjian" @click="deleteSpec(index)"></text>
						<text class="label">关键字:</text>
						<input class="uni-input" v-model="words_array[index]" placeholder="请输入关键字" />
					</view>
				</view>
				<view @click="Add()" class="color-base-text add-spec">+添加</view>
			</view>
		</view>

		<view class="footer-wrap" :class="{ 'safe-area': isIphonex }"><button type="primary" @click="save()">保存</button></view>
	</view>
</template>

<script>
import {getGoodsConfig,setGoodsConfig} from '@/api/config'
export default {
	data() {
		return {
			title: 'picker',
			array: ['正序排列', '倒序排列'],
			index: 0,
			words_array: [],
			default_value: '',
			words: '',
			goodsConfig: {
				default_words: {
					words: ''
				},
				goods_sort_config: {
					default_value: '',
					type: ''
				},
				hot_words: {
					words: '',
					words_array: []
				}
			},
			isIphonex: false,
			goodsSpecMax: 4,
			goodsSpecFormat: []
		};
	},
	onLoad(option) {
		this.isIphonex = this.$util.uniappIsIPhoneX();
		this.goodsSpecFormat = uni.getStorageSync('editGoodsSpecFormat') ? JSON.parse(uni.getStorageSync('editGoodsSpecFormat')) : [];
	},
	onShow() {
		this.goodsSet();
	},
	methods: {
		bindPickerChange: function(e) {
			this.index = e.target.value;
		},
		// 添加关键字
		Add() {
			this.words_array.push('');
		},
		//删除关键字
		deleteSpec(index) {
			uni.showModal({
				title: '操作提示',
				content: '确定要删除吗？',
				success: res => {
					if (res.confirm) this.words_array.splice(index, 1);
				}
			});
		},
		goodsSet() {
			getGoodsConfig().then(res=>{
				if (res.code >= 0 && res.data) {
					this.goodsConfig = res.data;
					this.words_array = res.data.hot_words.words_array;
					if (res.data.goods_sort_config.type == 'asc') {
						this.index = 0;
					} else {
						this.index = 1;
					}
					// this.default_value = res.data.goods_sort_config.default_value;
					// this.words = res.data.default_words.words;
				}
			});
		},
		// 保存
		save() {
			if (this.goodsConfig.hot_words.words_array.length <= 0 || this.goodsConfig.default_words.words == '') {
				this.$util.showToast({
					title: '修改失败'
				});
				return false;
			}
			setGoodsConfig({
				hot_words: this.goodsConfig.hot_words.words_array,
				default_words: JSON.parse(JSON.stringify(this.goodsConfig.default_words.words)),
				sort_type: this.index == 0 ? 'asc' : 'desc',
				sort_value: this.goodsConfig.goods_sort_config.default_value
			}).then(res=>{
				let msg = res.message;
				if (res.code == 0) {
					this.$util.showToast({
						title: msg
					});
					setTimeout(() => {
						this.$util.redirectTo('/pages/index/all_menu');
					}, 500);
				}
			});
			// if (!this.verify()) return;
			// this.goodsSpecFormat.forEach(item => {
			// 	item.value.forEach(citem => {
			// 		if (citem.spec_name == '') {
			// 			citem.spec_name = item.spec_name;
			// 		}
			// 	});
			// });
			// uni.setStorageSync('editGoodsSpecFormat', JSON.stringify(this.goodsSpecFormat));
			// uni.navigateBack({
			// 	delta: 1
			// });
		}
	}
};
</script>

<style lang="scss">
.goods {
	.order-setuo {
		margin: 20rpx 30rpx;
		background: #fff;
		padding: 15rpx 30rpx;
		border-radius: 10rpx;

		.order-list {
			display: flex;
			flex-direction: row;
			justify-content: space-between;
			align-items: center;
			border-bottom: 1px solid #eee;
			padding: 20rpx 0;

			.list-right {
				display: flex;
				flex-direction: row;
				align-items: center;
				font-size: 28rpx;
				font-family: PingFang SC;
				font-weight: 500;
				color: #303133;

				input {
					font-size: 28rpx;
					font-family: PingFang SC;
					font-weight: 500;
					color: #909399;
					text-align: right;
					margin-right: 20rpx;
					max-width: 280rpx;
				}
				.order-content {
					font-size: 28rpx;
					font-family: PingFang SC;
					font-weight: 500;
					color: #909399;
					text-align: right;
					margin-right: 20rpx;
				}

				switch,
				.uni-switch-wrapper,
				.uni-switch-input {
					width: 80rpx;
					height: 42rpx;
				}

				.iconfont {
					font-size: 30rpx;
					color: #909399;
				}
			}
			.list-left {
				font-size: 28rpx;
				font-family: PingFang SC;
				font-weight: 500;
				color: #303133;
			}
		}
		.order-list:last-child {
			border: none;
		}
		.order-title {
			font-size: 32rpx;
			font-family: PingFang SC;
			font-weight: bold;
			color: #303133;
			margin-bottom: 10rpx;
		}
	}
	.footer-wrap {
		margin-top: 80rpx;
		padding: 0 0 100rpx;
	}
}
.footer-wrap {
	position: fixed;
	width: 100%;
	bottom: 0;
	padding: 40rpx 0;
	z-index: 10;
	background-color: #fff;
}
.safe-area {
	/* #ifndef MP */
	padding-bottom: calc(constant(safe-area-inset-bottom) + 100rpx);
	padding-bottom: calc(env(safe-area-inset-bottom) + 100rpx);
	/* #endif */
}
.more-spec {
	margin: $margin-updown 0 80rpx 0;
	&.safe-area {
		margin-bottom: 200rpx;
	}
	.spec-item {
		background-color: #fff;
		border-radius: $border-radius;
		margin-bottom: $margin-updown;
		.action {
			background-color: $color-disabled;
			border-radius: 50%;
			color: #fff;
			width: 36rpx;
			height: 36rpx;
			line-height: 36rpx;
			display: inline-block;
			text-align: center;
			font-weight: bold;
			margin-right: 20rpx;
		}
		.label {
			vertical-align: middle;
			margin-right: $margin-both;
		}
		input {
			vertical-align: middle;
			display: inline-block;
			flex: 1;
			text-align: right;
		}
		.spec-name,
		.spec-value {
			display: flex;
			align-items: center;
			height: 100rpx;
			line-height: 100rpx;
			padding: 0 30rpx;
			border-bottom: 1px solid $color-line;
		}
		.spec-value {
			margin-left: 60rpx;
			padding-left: 0;
		}
		.add-spec-value {
			height: 100rpx;
			line-height: 100rpx;
			margin-left: 60rpx;
		}
	}
	.add-spec {
		text-align: center;
		background-color: #fff;
		height: 100rpx;
		line-height: 100rpx;
		border-radius: $border-radius;
	}
	.tip {
		text-align: center;
		color: $color-tip;
		font-size: $font-size-tag;
		padding: $padding;
	}
}
button {
	color: #ffffff;
	background-color: #ff6a00;
	margin-top: 20rpx;
}
</style>

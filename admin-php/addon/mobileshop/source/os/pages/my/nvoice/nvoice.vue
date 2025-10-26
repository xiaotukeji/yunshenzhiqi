<template>
	<view class="nvoice">
		<form @submit="formSubmit">
			<view class="nvoice-input" v-for="(item, index) in list" :key="index">
				<input type="text" name="aa" placeholder="请填写发票内容" @blur="onBlur($event, index)" :value="item" />
				<text class="action iconfont iconjian" @click="deleteSpecValue(item, index)"></text>
			</view>
			<view class="add-nvoice" @click="addContent">+添加内容</view>
			<view class="footer-wrap"><button type="primary" form-type="submit">保存</button></view>
		</form>
	</view>
</template>

<script>
export default {
	data() {
		return {
			list: []
		};
	},
	onLoad(option) {
		let arr = JSON.parse(option.list);
		this.list = JSON.parse(option.list);
		if (this.list[0] == '' || !this.list.length) {
			this.list.push('');
		}
	},
	methods: {
		onBlur(event, index) {
			this.list[index] = event.detail.value;
		},
		addContent() {
			for (let i = 0; i < this.list.length; i++) {
				if (this.list[i] == '') {
					this.$util.showToast({ title: '请填写后再添加' });
					return;
				}
			}
			this.list.push('');
		},
		deleteSpecValue(index) {
			let that = this;
			uni.showModal({
				title: '操作提示',
				content: '确定要删除此发票内容吗？',
				success: res => {
					if (res.confirm) {
						for (var i = 0; i < that.list.length; i++) {
							if (index == that.list[i]) {
								that.list.splice(i, 1);
							}
						}
					}
				}
			});
		},
		formSubmit(e) {
			uni.setStorageSync('invoicecontent', this.list);
			uni.navigateBack({
				delta: 1
			});
		}
	}
};
</script>

<style lang="scss">
.nvoice {
	padding: 0 30rpx;

	.nvoice-input {
		margin: 20rpx 0;
		padding: 30rpx;
		background: #fff;
		border-radius: 10rpx;
		display: flex;
		flex-direction: row;
		align-items: center;
		justify-content: space-between;

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
			// margin-right: 20rpx;
		}
		input {
			width: 100%;
			font-size: 30rpx;
			font-family: PingFang SC;
			font-weight: 500;
			color: #909399;
		}
	}

	.add-nvoice {
		padding: 20rpx;
		background: #fff;
		border-radius: 10rpx;
		font-size: 30rpx;
		font-family: PingFang SC;
		font-weight: 500;
		color: #ff6a00;
		text-align: center;
	}
	.footer-wrap {
		width: 690rpx;
		position: fixed;
		left: 30rpx;
		bottom: 60rpx;
	}
}
</style>

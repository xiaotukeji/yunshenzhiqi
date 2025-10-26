<template>
	<base-page>
		<view class="uni-flex uni-row height-all">
			<view class="container common-wrap" style="-webkit-flex: 1;flex: 1;" v-if="step == 'search'">
				<view class="search-title">查询核销码核销</view>
				<view class="search-wrap">
					<view class="input-wrap">
						<input type="text" value="" placeholder="请输入核销码或扫描核销码" placeholder-class="placeholder" v-model="code" @confirm="search" :focus="inputFocus" @focus="inputFocus = true" @blur="codeInputBlur" />
						<!-- #ifdef APP-PLUS -->
						<view class="iconfont iconsaoyisaosaoma" @click="scancode"></view>
						<!-- #endif -->
					</view>
					<button type="default" class="primary-btn" @click="search">查询</button>
				</view>
				<view class="search-desc">使用扫码枪扫码时需注意光标需要停留在输入框中</view>
				<view class="record" @click="$util.redirectTo('/pages/verify/list')"><text>核销记录</text></view>
			</view>

			<view class="content-box common-wrap" style="-webkit-flex: 1;flex: 1;" v-if="step == 'verify'">
				<view class="input-wrap">
					<input placeholder="请输入核销码" v-model="code" @confirm="search" />
					<button type="default" class="primary-btn search" @click="search()">查询</button>
				</view>
				<view class="content-data">
					<view class="content-top">
						<view v-for="(item, index) in verifyInfo.data.item_array" :key="index" class="verify-item">
							<view class="container-image">
								<image :src="$util.img(item.img.split(',')[0], { size: 'small' })" mode="aspectFit" />
							</view>
							<view class="container-box">
								<view class="content-name">{{ item.name }}</view>
								<view class="content-name">x{{ item.num }}</view>
							</view>
						</view>
					</view>
					<view class="content-bottom">
						<view class="bottom-item">
							<view>核销状态：{{ verifyInfo.is_verify == 0 ? '待核销' : '已核销' }}</view>
						</view>
						<view class="bottom-item">
							<view>核销类型：{{ verifyInfo.verify_type_name }}核销</view>
						</view>
						<view class="bottom-item">
							<view>
								总次数/已使用：{{ verifyInfo.verify_total_count ? verifyInfo.verify_total_count : '不限' }}次/{{ verifyInfo.verify_use_num }}次
							</view>
						</view>
						<view class="bottom-item">
							<view>
								有效期：{{ verifyInfo.expire_time ? $util.timeFormat(verifyInfo.expire_time, 'Y-m-d H:i') : '永久' }}
							</view>
						</view>
					</view>
					<view class="verify-action">
						<button type="primary" class="default-btn" @click="step = 'search'">取消</button>
						<button type="default" class="primary-btn" @click="verify()" v-show="verifyInfo.is_verify == 0">立即核销</button>
					</view>
				</view>
			</view>
		</view>
	</base-page>
</template>

<script>
	import {
		getVerifyInfo,
		verifyCode
	} from '@/api/verify.js';

	export default {
		data() {
			return {
				step: 'search',
				code: '',
				verifyInfo: null,
				isRepeat: false,
				inputFocus: false
			};
		},
		onLoad() {
			uni.hideTabBar();
			this.$nextTick(()=>{
				this.inputFocus = true;
			})
		},
		methods: {
			codeInputBlur() {
				this.inputFocus = false;
				if(!this.verifyInfo){
					this.$nextTick(()=>{
						this.inputFocus = true;
					})
				}
			},
			deleteCode() {
				this.code = this.code.substr(0, this.code.length - 1);
			},
			search() {
				if (!this.code) {
					this.$util.showToast({
						title: '请输入核销码'
					});
					return;
				}
				
				setTimeout(() => {
					this.code = new URLSearchParams(this.code.split('?')[1]).get('code');
					getVerifyInfo(this.code.trim()).then(res => {
						this.code = '';
						if (res.code >= 0) {
							this.verifyInfo = res.data;
							this.step = 'verify';
						} else {
							this.$util.showToast({
								title: res.message
							});
						}
					});
				}, 200);
			},
			verify() {
				if (!this.verifyInfo) {
					this.$util.showToast({
						title: '请先查询核销码信息'
					});
					return;
				}

				if (this.isRepeat) return;
				this.isRepeat = true;

				verifyCode(this.verifyInfo.verify_code).then(res => {
					this.isRepeat = false;
					if (res.code >= 0) {
						this.step = 'search';
						this.verifyInfo = null;
						this.code = '';
					}
					this.$util.showToast({
						title: res.message
					});
				});
			},
			scancode() {
				uni.scanCode({
					scanType: ['qrCode', 'barCode'],
					success: res => {
						this.code = res.result;
						this.search();
					},
					fail: res => {
						this.$util.showToast({
							title: '扫码失败'
						});
					}
				});
			}
		}
	};
</script>

<style lang="scss" scoped>
	.container {
		display: flex;
		align-items: center;
		justify-content: center;
		flex-direction: column;
	}

	.search-title {
		font-size: 0.18rem;
		color: #303133;
	}

	.search-wrap {
		display: flex;
		margin-top: 0.3rem;

		button {
			width: 1rem;
			text-align: center;
			box-sizing: border-box;
			line-height: 0.5rem;
		}
	}

	.search-desc {
		color: #909399;
		font-size: 0.14rem;
		margin-top: 0.3rem;
	}

	.input-wrap {
		width: 4.5rem;
		height: 0.5rem;
		border: 0.01rem solid #cccccc;
		display: flex;
		border-radius: 0.02rem;
		align-items: center;

		input {
			flex: 1;
			padding: 0 0.15rem;
			font-size: 0.16rem;
		}

		.placeholder {
			flex: 1;
			height: 0.58rem;
			line-height: 0.58rem;
			font-size: 0.16rem;
			font-weight: 400;
			color: #909399;
		}

		.iconfont {
			font-size: 0.18rem;
			padding: 0 0.15rem;
			font-weight: bold;
		}
	}

	.record {
		text-align: center;
		margin-top: 0.2rem;

		text {
			color: $primary-color;
			font-size: 0.14rem;
			cursor: pointer;
		}
	}

	// 核销详情
	.content-box {
		padding: 0.15rem 0.15rem 0.15rem 0.15rem;

		.input-wrap {
			width: 6rem;
			height: 0.4rem;
			border: 0.01rem solid #cccccc;
			display: flex;
			border-radius: 0.02rem;

			input {
				flex: 1;
				padding: 0 0.15rem;
				height: 0.38rem;
				line-height: 0.38rem;
				font-size: 0.16rem;
				font-weight: 400;
			}

			.placeholder {
				font-weight: 400;
				color: #909399;
				font-size: 0.18rem;
			}

			.search {
				border-radius: 0;
				width: 1rem;
				line-height: 0.38rem;
				font-size: 0.16rem;
				font-family: Source Han Sans CN;

				&::after {
					border: none;
				}
			}
		}

		.content-data {
			border: 0.01rem solid #eee;
			margin-top: 0.2rem;
			padding: 0.15rem;

			.verify-item {
				display: flex;
				padding: 0.15rem 0;

				.container-image {
					width: 1rem;
					height: 1rem;

					image {
						width: 100%;
						height: 100%;
					}
				}

				.container-box {
					display: flex;
					flex-direction: column;
					justify-content: space-between;
					margin-left: 0.15rem;
					width: 0;
					flex: 1;

					.content-name {
						font-size: 0.15rem;
						margin-top: 0.05rem;
					}

					.content-desc {
						display: flex;
						margin-top: 0.15rem;
						color: #999;
						font-size: 0.13rem;

						.time {
							margin-left: 0.5rem;
						}
					}
				}
			}

			.verify-action {
				display: flex;
				justify-content: flex-end;
				border-top: 0.01rem solid #eee;
				padding-top: 0.15rem;

				button {
					width: 1rem;
					height: 0.36rem;
					margin: 0 0 0 0.15rem;
				}
			}

			.content-bottom {
				padding: 0.15rem 0;
				border-top: 0.01rem solid #eee;

				.bottom-item {
					color: #999;
					display: flex;
					margin-top: 0.15rem;
					width: 5rem;
					justify-content: space-between;

					view {
						margin-right: 0.5rem;
					}
				}
			}
		}
	}
</style>
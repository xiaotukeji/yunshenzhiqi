<template>
	<base-page>
		<view class="common-wrap common-form body-overhide">
			<view class="common-title">电子秤设置</view>
			<view class="common-form-item">
				<label class="form-label">
					<text class="required">*</text>
					电子秤名称
				</label>
				<view class="form-input-inline"><input type="text" v-model="formData.name" class="form-input" /></view>
				<text class="form-word-aux"></text>
			</view>
			<view class="common-form-item">
				<label class="form-label">
					<text class="required">*</text>
					秤类型
				</label>
				<view class="form-input-inline border-none">
					<view class="scale-type">
						<view :class="{ active: formData.type == 'barcode' }" @click="formData.type = 'barcode'">条码秤</view>
						<view :class="{ active: formData.type == 'cashier' }" @click="formData.type = 'cashier'">收银秤</view>
					</view>
				</view>
			</view>
			<view class="common-form-item">
				<label class="form-label">
					<text class="required">*</text>
					电子秤品牌
				</label>
				<view class="form-input-inline border-none">
					<uni-data-select v-model="formData.brand" :localdata="brandList" label=""></uni-data-select>
				</view>
			</view>
			<view class="common-form-item">
				<label class="form-label">
					<text class="required">*</text>
					电子秤型号
				</label>
				<view class="form-input-inline border-none">
					<uni-data-select v-model="formData.model" :localdata="modelList"></uni-data-select>
				</view>
			</view>
			<view class="common-form-item">
				<label class="form-label">通讯方式</label>
				<view class="form-inline">
					<radio-group @change="networkTypeChange" class="form-radio-group">
						<label class="radio form-radio-item">
							<radio value="tcp" :checked="formData.network_type == 'tcp'" />
							TCP
						</label>
						<label class="radio form-radio-item">
							<radio value="serialport" :checked="formData.network_type == 'serialport'" />
							串口
						</label>
					</radio-group>
				</view>
			</view>

			<view class="scale-tips" v-if="formData.brand == 'dahua' && formData.model == 'TM'">
				<view>使用大华电子秤前需先配置一下电子秤“条码格式”，配置方式为：</view>
				<view>
					使用大华电子秤厂官方提供的大华电子秤上位机软件TMA4.0，连接到设备后，打开基础设置 -> 系统参数，设置条码格式为 “FFWWWWWNNNNNC” 或
					“FFWWWWWNNNNNEEEEEC”，设置好之后点击下载，下载成功之后即配置完成
				</view>
			</view>

			<view class="scale-tips" v-if="formData.brand == 'aclas' && formData.model == 'LS'">
				<view>使用顶尖电子秤需将电子秤默认条码类型配置为“7” 或者 “87”</view>
			</view>

			<view v-show="formData.network_type == 'tcp'">
				<view class="common-form-item">
					<label class="form-label">
						<text class="required">*</text>
						设备ip地址
					</label>
					<view class="form-input-inline">
						<input type="text" v-model="formData.config.ip" class="form-input" />
					</view>
					<text class="form-word-aux"></text>
				</view>
				<view class="common-form-item">
					<label class="form-label">
						<text class="required">*</text>
						设备端口号
					</label>
					<view class="form-input-inline">
						<input type="text" v-model="formData.config.port" class="form-input" />
					</view>
					<text class="form-word-aux"></text>
				</view>
			</view>

			<view v-show="formData.network_type == 'serialport'">
				<view class="common-form-item">
					<label class="form-label">
						<text class="required">*</text>
						串口名称
					</label>
					<view class="form-input-inline">
						<input type="text" v-model="formData.config.serialport" class="form-input" />
					</view>
					<text class="form-word-aux"></text>
				</view>
				<view class="common-form-item">
					<label class="form-label">
						<text class="required">*</text>
						串口波特率
					</label>
					<view class="form-input-inline">
						<input type="text" v-model="formData.config.baudrate" class="form-input" />
					</view>
					<text class="form-word-aux"></text>
				</view>
			</view>

			<view class="common-btn-wrap">
				<button type="primary" class="screen-btn" @click="saveFn">保存</button>
				<button type="default" class="screen-btn" @click="back">返回</button>
			</view>
		</view>
	</base-page>
</template>

<script>
	import uniDataSelect from '@/components/uni-data-select/uni-data-select.vue';
	import {
		getScaleDetail,
		getScaleBrand,
		addScale,
		editScale
	} from '@/api/scale.js'

	export default {
		components: {
			uniDataSelect
		},
		data() {
			return {
				brandList: [],
				modelList: [],
				formData: {
					type: 'barcode',
					name: "",
					brand: "",
					model: "",
					config: {
						ip: '',
						port: '',
						serialport: '',
						baudrate: ''
					},
					network_type: 'tcp'
				},
				flag: false,
				scaleId: 0,
				scaleBrand: {}
			};
		},
		async onLoad(option) {
			await this.getScaleBrandFn();
			if (option.scale_id) {
				this.scaleId = option.scale_id;
				this.getDetailFn();
			}
		},
		onShow() {},
		watch: {
			'formData.brand': {
				handler: function(nval) {
					if (nval) {
						let modelList = this.scaleBrand[nval].model_list
						this.modelList = Object.keys(modelList).map(key => {
							return {
								value: key,
								text: modelList[key].model_name
							}
						})
						if (this.formData.model && !Object.keys(modelList).includes(this.formData.model)) this.formData
							.model = '';
					} else {
						this.formData.model = '';
						this.modelList = []
					}
				},
				immediate: true
			}
		},
		methods: {
			async getScaleBrandFn() {
				if (!this.addon.includes('scale')) {
					this.$util.showToast({
						title: '未安装电子秤插件'
					});
					return;
				}
				let res = await getScaleBrand()
				if (res.code == 0) {
					this.scaleBrand = res.data
					let brandList = [];
					Object.keys(this.scaleBrand).forEach(key => {
						brandList.push({
							text: this.scaleBrand[key].brand_name,
							value: key
						})
					})
					this.brandList = brandList
				}
			},
			bradnChange(e) {
				this.formData.model = e.detail.value;
			},
			selectBrand(e) {
				this.formData.brand = e;
			},
			saveFn() {
				if (!this.addon.includes('scale')) {
					this.$util.showToast({
						title: '未安装电子秤插件'
					});
					return;
				}
				if (this.check()) {
					if (this.flag) return false;
					this.flag = true;

					let data = this.$util.deepClone(this.formData)
					data.config = JSON.stringify(this.formData.config)
					data.scale_id = this.scaleId
					let action = '';
					if (this.scaleId) {
						action = editScale(data)
					} else {
						action = addScale(data)
					}
					action.then(res => {
						this.flag = false;
						this.$util.showToast({
							title: res.message
						});
						if (res.code >= 0) {
							setTimeout(() => {
								this.$util.redirectTo('/pages/scale/list');
							}, 1500);
						}
					});
				}
			},
			back() {
				this.$util.redirectTo('/pages/scale/list');
			},
			check() {
				if (!this.formData.name) {
					this.$util.showToast({
						title: '请输入电子秤名称'
					});
					return false;
				}
				if (!this.formData.model) {
					this.$util.showToast({
						title: '请选择电子秤型号'
					});
					return false;
				}
				if (this.formData.network_type == 'tcp') {
					if (!this.formData.config.ip) {
						this.$util.showToast({
							title: '请输入设备IP地址'
						});
						return false;
					}
					if (!this.formData.config.port) {
						this.$util.showToast({
							title: '请输入设备端口号'
						});
						return false;
					}
				}
				if (this.formData.network_type == 'serialport') {
					if (!this.formData.config.serialport) {
						this.$util.showToast({
							title: '请输入串口名称'
						});
						return false;
					}
					if (!this.formData.config.baudrate) {
						this.$util.showToast({
							title: '请输入串口波特率'
						});
						return false;
					}
				}
				return true;
			},
			getDetailFn() {
				if (!this.addon.includes('scale')) {
					this.$util.showToast({
						title: '未安装电子秤插件'
					});
					return;
				}
				getScaleDetail({
					"scale_id": this.scaleId
				}).then(res => {
					if (res.code >= 0) {
						if (res.data) {
							this.formData = res.data
						}
					}
				})
			},
			networkTypeChange(e) {
				this.formData.network_type = e.detail.value
			}
		}
	};
</script>

<style lang="scss" scoped>
	.common-wrap {
		padding: 30rpx;
		background-color: #fff;
		@extend %body-overhide;
	}

	.common-title {
		font-size: 0.18rem;
		margin-bottom: 0.2rem;
	}

	.scale-type {
		display: flex;
		align-items: center;

		view {
			width: 1rem;
			height: 0.35rem;
			line-height: 0.35rem;
			text-align: center;
			font-size: 0.14rem;
			border: 0.01rem solid #e6e6e6;
			border-left-width: 0;
			transition: all 0.3s;
			cursor: pointer;

			&:hover,
			&.active {
				border-color: $primary-color;
				color: $primary-color;
				background-color: var(--primary-color-light-9);
				box-shadow: -0.01rem 0 0 0 $primary-color;
			}

			&:first-child {
				border-left-width: 0.01rem;
				box-shadow: none;
			}
		}
	}

	.border-none {
		border: none !important;
	}

	/deep/ .uni-select {
		border-radius: 0;
	}

	.scale-tips {
		display: inline-block;
		padding: .1rem;
		border-radius: .05rem;
		color: $primary-color !important;
		border: .01rem solid $primary-color !important;
		background-color: var(--primary-color-light-9) !important;
		margin-left: 1.1rem;
		margin-bottom: 0.1rem;
	}
</style>
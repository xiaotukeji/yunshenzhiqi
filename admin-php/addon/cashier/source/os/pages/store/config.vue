<template>
	<base-page>
		<view class="store-config">
			<view class="common-wrap common-form fixd common-scrollbar">
				<view class="common-title">门店设置</view>
				<view class="common-form-item">
					<label class="form-label">门店名称</label>
					<view class="form-input-inline">
						<input type="text" v-model="storeData.store_name" class="form-input" />
					</view>
					<text class="form-word-aux-line">门店的名称（招牌）</text>
				</view>
				<view class="common-form-item store-img">
					<label class="form-label">门店图片</label>
					<view class="form-input-inline upload-box" @click="addImg">
						<view class="upload" v-if="storeData.store_image">
							<image :src="$util.img(storeData.store_image)" @error="$util.img(defaultImg.store)" mode="heightFix" />
						</view>
						<view class="upload" v-else>
							<text class="iconfont iconyunshangchuan"></text>
							<view>点击上传</view>
						</view>
					</view>
					<text class="form-word-aux-line">门店图片在PC及移动端对应页面及列表作为门店标志出现。建议图片尺寸：100 * 100像素，图片格式：jpg、png、jpeg。</text>
				</view>

				<view class="common-form-item">
					<label class="form-label">门店类型</label>
					<view class="form-inline">
						<radio-group @change="storeTypeChange" class="form-radio-group">
							<label class="radio form-radio-item">
								<radio value="directsale" :checked="storeData.store_type == 'directsale'" />
								直营店
							</label>
							<label class="radio form-radio-item">
								<radio value="franchise" :checked="storeData.store_type == 'franchise'" />
								加盟店
							</label>
						</radio-group>
					</view>
				</view>

				<view class="common-form-item" v-if="category.status">
					<label class="form-label">门店分类</label>
					<view class="form-input-inline">
						<uni-data-select v-model="storeData.category_id" :localdata="category.list"></uni-data-select>
					</view>
				</view>

				<view class="common-form-item" v-if="label.length">
					<label class="form-label">门店标签</label>
					<view class="form-block">
						<checkbox-group class="form-checkbox-group" @change="labelChange">
							<label class="form-checkbox-item" v-for="(item, index) in label">
								<checkbox :value="item.label_id.toString()" :checked="labelChecked(item)" />
								{{ item.label_name }}
							</label>
						</checkbox-group>
					</view>
				</view>

				<view class="common-form-item">
					<label class="form-label">门店电话</label>
					<view class="form-input-inline">
						<input type="number" v-model="storeData.telphone" class="form-input" />
					</view>
				</view>

				<view class="common-form-item">
					<label class="form-label">门店地址</label>
					<view class="form-inline">
						<pick-regions ref="selectArea" :default-regions="defaultRegions" @getRegions="handleGetRegions">
							<view class="form-input-inline long">
								<view class="form-input">{{ storeData.full_address }}</view>
							</view>
						</pick-regions>
					</view>
				</view>
				<view class="common-form-item">
					<label class="form-label"></label>
					<view class="form-inline">
						<view class="form-input-inline long">
							<input type="text" v-model="storeData.address" class="form-input" />
						</view>
						<view class="form-input-inline short btn" @click="getLatLng()">查找</view>
					</view>
				</view>

				<view class="common-form-item store-img">
					<label class="form-label">地图定位</label>
					<view class="form-inline">
						<view class="map-box">
							<image src="@/static/location.png" class="map-icon" />
							<map style="width: 100%; height: 100%;" :latitude="storeData.latitude" :longitude="storeData.longitude" :markers="covers" @markertap="markertap" @regionchange="tap"></map>
						</view>
					</view>
				</view>

				<view class="common-btn-wrap">
					<button type="default" class="screen-btn" @click="saveFn">保存</button>
					<button type="default" @click="$util.redirectTo('/pages/store/index')">返回</button>
				</view>
			</view>
		</view>
	</base-page>
</template>

<script>
import {
	editStore,
	getAllStoreCategory,
	getAllStoreLabel
} from '@/api/store.js'
import { getAddressByName, getTranAddressInfo } from '@/api/address.js'

export default {
	data() {
		return {
			storeData: {},
			covers: [{
				latitude: 39.909,
				longitude: 116.39742,
				iconPath: '/static/location.png'
			}],
			defaultRegions: [],
			category: {
				status: 0,
				list: []
			},
			label: [],
			labelData: {}
		};
	},
	onLoad() {
		this.getLabel();
		this.getCategory();
		this.getData();
	},
	methods: {
		getData() {
			this.storeData = this.$util.deepClone(this.globalStoreInfo);
			this.storeData.start_time = this.timeFormat(this.storeData.start_time);
			this.storeData.end_time = this.timeFormat(this.storeData.end_time);
			this.defaultRegions = [this.storeData.province_id, this.storeData.city_id, this.storeData.district_id];
			this.$nextTick(() => {
				this.$refs.selectArea.handleDefaultRegions();
			});
		},
		getLabel() {
			getAllStoreLabel().then(res => {
				if (res.code == 0) {
					this.label = res.data;
					let labelData = {};
					res.data.forEach(item => {
						labelData[item.label_id] = item.label_name;
					});
					this.labelData = labelData;
				}
			});
		},
		getCategory() {
			getAllStoreCategory().then(res => {
				if (res.code == 0) {
					this.category.status = res.data.status;
					this.category.list = res.data.list.map(item => {
						return {
							value: item.category_id,
							text: item.category_name
						};
					});
				}
			});
		},
		addImg() {
			this.$util.upload(1, {
				path: 'image'
			}, res => {
				if (res.length > 0) {
					this.storeData.store_image = res[0];
					this.$forceUpdate();
				}
			});
		},
		tap(e) {
			if (e.detail && e.detail.centerLocation) {
				this.storeData.latitude = e.detail.centerLocation.latitude;
				this.storeData.longitude = e.detail.centerLocation.longitude;
				this.covers = [{
					latitude: this.storeData.latitude,
					longitude: this.storeData.longitude,
					iconPath: '/static/location.png'
				}];
				this.getAddress();
				this.$forceUpdate();
			}
		},
		handleGetRegions(regions) {
			this.storeData.full_address = '';
			this.storeData.full_address += regions[0] != undefined ? regions[0].label : '';
			this.storeData.full_address += regions[1] != undefined ? '-' + regions[1].label : '';
			this.storeData.full_address += regions[2] != undefined ? '-' + regions[2].label : '';

			this.storeData.province_id = regions[0] != undefined ? regions[0].value : '';
			this.storeData.city_id = regions[1] != undefined ? regions[1].value : '';
			this.storeData.district_id = regions[2] != undefined ? regions[2].value : '';
			this.defaultRegions = [this.storeData.province_id, this.storeData.city_id, this.storeData.district_id];
			this.$forceUpdate();
			this.getLatLng();
		},
		//获取详细地址
		getAddress() {
			let value = this.storeData.latitude + ',' + this.storeData.longitude;
			getTranAddressInfo(value).then(res => {
				if (res.code == 0) {
					this.storeData.full_address = '';
					this.storeData.full_address += res.data.province != undefined ? res.data.province : '';
					this.storeData.full_address += res.data.city != undefined ? '-' + res.data.city : '';
					this.storeData.full_address += res.data.district != undefined ? '-' + res.data.district : '';
					this.storeData.address = res.data.address != undefined ? res.data.address : '';

					this.storeData.province_id = res.data.province_id != undefined ? res.data.province_id : '';
					this.storeData.city_id = res.data.city_id != undefined ? res.data.city_id : '';
					this.storeData.district_id = res.data.district_id != undefined ? res.data.district_id : '';
					this.defaultRegions = [this.storeData.province_id, this.storeData.city_id, this.storeData.district_id];
					this.$forceUpdate();
				}
			});
		},
		//获取详细地址
		getLatLng() {
			let value = this.storeData.full_address + this.storeData.address;
			getAddressByName(value).then(res => {
				if (res.code == 0) {
					this.storeData.latitude = res.data.latitude;
					this.storeData.longitude = res.data.longitude;
				}
			});
		},
		storeTypeChange(e) {
			this.storeData.store_type = e.detail.value;
		},
		getSaveData() {
			let data = Object.assign({}, this.storeData);
			data.start_time = this.timeTurnTimeStamp(data.start_time);
			data.end_time = this.timeTurnTimeStamp(data.end_time);
			data.time_week = this.storeData.time_week.toString();
			return data;
		},
		checkData(data) {
			if (data.store_name == '') {
				this.$util.showToast({
					title: '请输入门店名称'
				});
				return false;
			}

			if (!data.district_id || data.address == '') {
				this.$util.showToast({
					title: '请选择门店地址'
				});
				return false;
			}
			return true;
		},
		saveFn() {
			let data = this.getSaveData();
			if (this.checkData(data)) {
				if (this.flag) return false;
				this.flag = true;
				editStore(data).then(res => {
					this.flag = false;
					this.$util.showToast({
						title: res.message
					});
					if (res.code >= 0) {
						this.$store.dispatch('app/getStoreInfoFn', {
							callback: () => {
								this.getData();
							}
						});
					}
				});
			}
		},
		timeTurnTimeStamp(_time) {
			let data = _time.split(':');
			return data[0] * 3600 + data[1] * 60;
		},
		timeFormat(time) {
			let h = time / 3600;
			let i = (time % 3600) / 60;
			h = h < 10 ? '0' + h : h;
			i = i < 10 ? '0' + i : i;
			return h + ':' + i;
		},
		labelChange(e) {
			if (e.detail.value.length) {
				this.storeData.label_id = ',' + e.detail.value.toString() + ',';
				let labelName = [];
				e.detail.value.forEach(item => {
					labelName.push(this.labelData[item]);
				});
				this.storeData.label_name = ',' + labelName.toString() + ',';
			} else {
				this.storeData.label_id = '';
				this.storeData.label_name = '';
			}
		},
		labelChecked(item) {
			let labelIdArr = [];
			if (!this.storeData.label_id) return false;
			if (typeof this.storeData.label_id == 'string') labelIdArr = this.storeData.label_id.split(',');
			return labelIdArr.includes(item.label_id.toString());
		}
	}
};
</script>

<style lang="scss" scoped>
.store-config {
	position: relative;
	.common-wrap.fixd {
		padding: 30rpx;
		height: calc(100vh - 0.4rem);
		overflow-y: auto;
		// padding-bottom: 1rem !important;
		box-sizing: border-box;

		.form-label {
			width: 1.5rem !important;
		}

		.form-input-inline /deep/ .uni-select {
			border: none;
		}

		.common-btn-wrap {
			position: absolute;
			left: 0;
			bottom: 0;
			right: 0;
			padding: 0.24rem 0.2rem;
		}

		.form-word-aux-line {
			margin-left: 1.5rem !important;
		}
	}

	.upload-box {
		border: 0.01rem dashed #e6e6e6 !important;
		width: 2.5rem !important;
		height: 1.2rem !important;
		display: flex;
		align-items: center;
		justify-content: center;

		.upload {
			text-align: center;
			color: #5a5a5a;

			.iconfont {
				font-size: 0.3rem;
			}

			image {
				max-width: 100%;
				height: 1.2rem !important;
			}
		}
	}

	.store-img {
		align-items: flex-start !important;
	}

	.map-box {
		width: 6.5rem;
		height: 5rem;
		position: relative;

		.map-icon {
			position: absolute;
			top: calc(50% - 0.36rem);
			left: calc(50% - 0.18rem);
			width: 0.36rem;
			height: 0.36rem;
			z-index: 100;
		}
	}

	.form-input {
		font-size: 0.16rem;
	}

	.form-input-inline.btn {
		height: 0.37rem;
		line-height: 0.35rem;
		box-sizing: border-box;
		border: 0.01rem solid #e6e6e6;
		text-align: center;
		cursor: pointer;
	}

	.common-title {
		font-size: 0.18rem;
		margin-bottom: 0.2rem;
	}
}</style>
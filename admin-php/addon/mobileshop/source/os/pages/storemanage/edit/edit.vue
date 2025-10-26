<template>
	<view class="iphone-safe-area">
		<view class="item-wrap">
			<view class="form-wrap">
				<text class="label">门店名称</text>
				<input type="text" class="value" v-model="shopInfo.store_name" placeholder="请输入门店名称" placeholder-class="placetext" />
			</view>
			<view class="form-wrap goods-img">
				<text class="label">门店图片</text>
				<view class="img-list">
					<view class="add logo" @click="uplodImg('store_image')">
						<text class="iconfont iconadd1" v-if="!shopInfo.store_image"></text>
						<image v-else :src="$util.img(shopInfo.store_image)" mode="aspectFit" @error="imgError('store_image')" @click.stop="previewMedia('store_image')"></image>
						<view class="del-wrap iconfont iconclose" @click.stop="delImg('store_image')" v-if="shopInfo.store_image"></view>
					</view>
					<view class="tips">建议图片尺寸：100*100像素</view>
				</view>
			</view>
			<view class="form-wrap more-wrap">
				<text class="label">门店电话</text>
				<input type="text" class="value" v-model="shopInfo.telphone" placeholder="请输入门店电话" placeholder-class="placetext" />
			</view>
			<view class="form-wrap between">
				<view class="label">营业状态</view>
				<view>
					<ns-switch class="switch" :checked="isBusiness" @change="isFree()"></ns-switch>
				</view>
			</view>
			<view class="form-wrap between">
				<view class="label">是否启用自提</view>
				<view>
					<ns-switch class="switch" :checked="is_pickup" @change="isFrees()"></ns-switch>
				</view>
			</view>
			<view class="form-wrap">
				<text class="label">门店地址</text>
				<input class="uni-input" placeholder="请选择省市区" v-model="shopInfo.full_address" maxlength="100" placeholder-class="placetext" @click="selectAddress" />
			</view>
			<view class="form-wrap between">
				<text class="label">营业时间</text>
				<view class="time-change">
					<picker mode="time" @change="bindStartDateChange" class="padding-left padding-right" :value="shopInfo.start_time">
						<view class="uni-input" :class="!startTime ? 'placetext' : ''">{{ startTime || '开始时间' }}</view>
					</picker>
					<text :class="!startTime ? 'placetext' : ''">-</text>
					<picker mode="time" @change="bindEndDateChange" class="padding-left padding-right" :value="shopInfo.end_time">
						<view class="uni-input":class="!endTime ? 'placetext' : ''">{{ endTime || '结束时间' }}</view>
					</picker>
				</view>
			</view>
			<view class="form-wrap more-wrap">
				<text class="label">门店账号</text>
				<input type="text" class="value" v-if="type ==1" :value="shopInfo.username" placeholder="请输入门店账号" placeholder-class="placetext" />
				<input type="text" class="value" v-else :value="shopInfo.username" disabled placeholder-class="placetext" />
			</view>
			<view class="form-wrap more-wrap" v-if="type == 1">
				<text class="label">门店密码</text>
				<input type="text" class="value" placeholder="请输入门店密码" placeholder-class="placetext" />
			</view>
		</view>
		<button type="primary" @click="save()">保存</button>
	</view>
</template>

<script>
	import Config from '@/common/js/config.js';
export default {
	data() {
		return {
			shopInfo: {
				start_time: '',
				end_time: ''
			},
			index: 1,
			arr:['aa','bb','cc'],
			showTime: '',
			isBusiness: false,
			is_pickup: false,
			startTime: '',
			endTime: '',
			type: 0
		};
	},
	onShow() {},
	onLoad(option) {
		if (!this.$util.checkToken('/pages/my/shop/config')) return;
		// this.shopInfo = uni.getStorageSync('shop_info') ? JSON.parse(uni.getStorageSync('shop_info')) : {};
		if(option.store_id) this.dataDetail(option.store_id)
		this.type = option.type
	},
	computed: {
		formData() {
			return {
				store_image: this.shopInfo.store_image,
				avatar: this.shopInfo.avatar,
				banner: this.shopInfo.banner,
				seo_description: this.shopInfo.seo_description,
				seo_keywords: this.shopInfo.seo_keywords
			};
		}
	},
	methods: {
		dataDetail(data){
			this.$api.sendRequest({
				url: '/shopapi/store/detail',
				data: {store_id: data},
				success: res => {
					if (res.code >= 0) {
						this.isBusiness = res.data.info.status == 0 ? false : true
						this.is_pickup = res.data.info.is_pickup == 0 ? false : true
						if(res.data.info.open_date){
							let arr= res.data.info.open_date.split('-')
							this.startTime = arr[0]
							this.endTime = arr[1]
						}
						this.shopInfo = res.data.info
					}
				}
			});
		},
		isFree(){
			this.isBusiness = this.isBusiness == true ? false : true
		},
		isFrees(){
			this.is_pickup = this.is_pickup == true ? false : true
		},
		bindStartDateChange(e){
			this.startTime = e.detail.value
		},
		bindEndDateChange(e){
			this.endTime = e.detail.value
		},
		save() {
			this.shopInfo.status = this.isBusiness == true ? 1 : 0
			this.shopInfo.is_pickup = this.is_pickup == true ? 1 : 0
			this.shopInfo.open_date = this.startTime + '-' + this.endTime
			this.$api.sendRequest({
				url: '/shopapi/store/editStore',
				data: this.shopInfo,
				success: res => {
					this.$util.showToast({
						title: res.message
					});
					if (res.code >= 0) {
						// uni.setStorageSync('shop_info', JSON.stringify(this.shopInfo));
						setTimeout(() => {
							this.$util.redirectTo('/pages/storemanage/storemanage', {}, 'redirectTo');
						}, 1000);
					}
				}
			});
		},
		uplodImg(type) {
			this.$util.upload(
				{
					number: 1,
					path: 'image'
				},
				res => {
					if (res) {
						this.$util.showToast({
							title: '上传成功'
						});
						if (type == 'store_image') this.shopInfo.store_image = res[0];
						else if (type == 'avatar') this.shopInfo.avatar = res[0];
						else if (type == 'banner') this.shopInfo.banner = res[0];
					}
				}
			);
		},
		delImg(type) {
			if (type == 'store_image') this.shopInfo.store_image = '';
			else if (type == 'avatar') this.shopInfo.avatar = '';
			else if (type == 'banner') this.shopInfo.banner = '';
		},
		previewMedia(type) {
			var paths = [this.$util.img(this.shopInfo[type])];
			uni.previewImage({
				current: 0,
				urls: paths
			});
		},
		imgError(type) {
			this.shopInfo[type] = this.$util.img(this.$util.getDefaultImage().default_headimg);
			this.$forceUpdate();
		},
		//获取详细地址
		getAddress(value) {
			this.$api.sendRequest({
				url: '/api/memberaddress/tranAddressInfo',
				data: {
					latlng: value
				},
				success: res => {
					if (res.code == 0) {
						this.shopInfo.full_address = '';
						this.shopInfo.full_address += res.data.province != undefined ? res.data.province : '';
						this.shopInfo.full_address += res.data.city != undefined ? '-' + res.data.city : '';
						this.shopInfo.full_address += res.data.district != undefined ? '-' + res.data.district : '';
						this.shopInfo.province = res.data.province_id;
						this.shopInfo.province_name = res.data.province;
						this.shopInfo.city = res.data.city_id;
						this.shopInfo.city_name = res.data.city;
						this.shopInfo.district = res.data.district_id;
						this.shopInfo.district_name = res.data.district;
					} else {
						this.$util.showToast({
							title: '数据有误'
						});
					}
				}
			});
		},
		selectAddress() {
			// var urlencode = this.shopInfo;
			// uni.setStorageSync('addressInfo', JSON.stringify(urlencode));
			// #ifdef MP
			uni.chooseLocation({
				success: res => {
					this.option.name = res.name;
					this.option.latng = res.latitude + ',' + res.longitude;
				},
				fail(res) {
					uni.getSetting({
						success: function(res) {
							var status = res.authSetting;
							if (!status['scope.userLocation']) {
								uni.showModal({
									title: '是否授权当前位置',
									content: '需要获取您的地理位置，请确认授权，否则地图功能将无法使用',
									success(tip) {
										if (tip.confirm) {
											uni.openSetting({
												success: function(data) {
													if (data.authSetting['scope.userLocation'] === true) {
														this.$util.showToast({
															title: '授权成功'
														});
														//授权成功之后，再调用chooseLocation选择地方
														setTimeout(function() {
															uni.chooseLocation({
																success: data => {
																	this.option.name = res.name;
																	this.option.latng = res.latitude + ',' + res.longitude;
																}
															});
														}, 1000);
													}
												}
											});
										} else {
											this.$util.showToast({
												title: '授权失败'
											});
										}
									}
								});
							}
						}
					});
				}
			});
			// #endif
			// #ifdef H5
			
			let backurl = Config.h5Domain + '/pages/my/shop/contact';
			window.location.href = 'https://apis.map.qq.com/tools/locpicker?search=1&type=0&backurl=' + encodeURIComponent(backurl) + '&key=' + Config.mpKey + '&referer=myapp';
			// #endif
		},
	}
};
</script>

<style lang="scss">
// @import '../css/edit.scss';

.value {
	vertical-align: middle;
	display: inline-block;
	flex: 1;
	text-align: right;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: pre;
}
button {
	margin-top: 40rpx;
}
.placetext {
	font-size: 28rpx;
	font-family: PingFang SC;
	font-weight: 500;
	color: #909399;
}
.container-wrap {
	margin-bottom: 60rpx;
}
.item-wrap {
	background: #fff;
	margin-top: $margin-updown;
	.form-wrap {
		display: flex;
		align-items: center;
		margin: 0 $margin-both;
		border-bottom: 1px solid $color-line;
		min-height: 100rpx;
		line-height: 100rpx;
		&:last-child {
			border-bottom: none;
		}
		.required {
			font-weight: bold;
		}
		.label {
			min-width:150rpx;
			vertical-align: middle;
			margin-right: $margin-both;
		}
		.time-change {
			display: flex;
			align-items: center;
			flex: 1;
			justify-content: flex-end;
		}
		textarea,
		.picker,
		input {
			vertical-align: middle;
			display: inline-block;
			flex: 1;
			text-align: right;
		}
		.picker {
			.iconfont {
				vertical-align: middle;
			}
		}
		textarea {
			height: 100rpx;
			padding: $padding;
		}

		.value {
			vertical-align: middle;
			display: inline-block;
			flex: 1;
			text-align: right;
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: pre;
		}
		&.more-wrap {
			.selected {
				vertical-align: middle;
				display: inline-block;
				flex: 1;
				text-align: right;
				color: $color-tip;
				overflow: hidden;
				white-space: pre;
				text-overflow: ellipsis;
				&.have {
					color: $color-title;
				}
			}
			.flex_1{
				flex:1;
				text-align: right;
				padding-right: 20rpx;
				input{
					height:100rpx;
					display: block;
				}
			}
			.iconfont {
				color: $color-tip;
				margin-left: 20rpx;
			}
		}
		&.goods-img {
			display: flex;
			.label{
				align-self: flex-start;
				margin-top: 20rpx;
			}
			.img-list {
				padding-top: 40rpx;
				padding-bottom: 40rpx;
				padding-left: 40rpx;
				.add{
					position: relative;
					width:140rpx;
					text-align: center;
					border: 1px dashed #cccccc;
					font-weight: bold;
					color: $color-tip;
					.iconfont{
						font-size: 40rpx;
					}
					&.logo{
						height:84rpx;
						line-height: 84rpx;
					}
					&.avatar{
						height:140rpx;
						line-height: 140rpx;
					}
					&.banner{
						height:120rpx;
						line-height: 120rpx;
					}
					image{
						width:100%;
						height:100%
					}
					.del-wrap{
						position: absolute;
						top:-16rpx;
						right:-16rpx;
						line-height: 1;
						width: 16px;
						height: 16px;
						background-color: rgba(0, 0, 0, 0.5);
						border-radius: 50%;
						display: -webkit-box;
						display: -webkit-flex;
						display: flex;
						-webkit-box-pack: center;
						-webkit-justify-content: center;
						justify-content: center;
						-webkit-box-align: center;
						-webkit-align-items: center;
						align-items: center;
						font-size: 12px;
						color: #fff;
						font-weight: bold;
					}
				}
			}
			.tips {
				color: $color-tip;
				font-size: $font-size-activity-tag;
				margin-top: 20rpx;
				word-wrap: break-word;
				word-break: break-all;
			}
		}
	}
}
.form-content {
	display: flex;
	flex-direction: row;
	
	view {
		font-size: 28rpx;
		font-family: PingFang SC;
		font-weight: 500;
		color: #909399;
	}
}
.between{
	display:flex;
	justify-content: space-between;
}
.footer-wrap {
	// position: fixed;
	width: 100%;
	// bottom: 0;
	padding: 40rpx 0;
	z-index: 10;
	/* #ifdef MP */
	padding-bottom: 40rpx;
	/* #endif */
	/* #ifndef MP */
	padding-bottom: calc(constant(safe-area-inset-bottom) + 100rpx);
	padding-bottom: calc(env(safe-area-inset-bottom) + 100rpx);
	/* #endif */
}

</style>

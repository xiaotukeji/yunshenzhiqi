<template>
	<base-page>
		<view class="store-operate">
			<view class="common-wrap common-form fixd common-scrollbar">
				<view class="common-title">运营设置</view>
				
				<template v-if="addon.includes('store')">
					<view class="common-form-item">
						<label class="form-label">门店名称</label>
						<view class="form-input-inline">
							<input type="text" v-model="storeData.store_name" disabled class="form-input" />
						</view>
						<text class="form-word-aux-line">门店的名称（招牌）</text>
					</view>

					<view class="common-form-item">
						<label class="form-label">是否营业</label>
						<view class="form-inline">
							<radio-group @change="statusChange" class="form-radio-group">
								<label class="radio form-radio-item">
									<radio value="1" :checked="storeData.status == 1" />
									是
								</label>
								<label class="radio form-radio-item">
									<radio value="0" :checked="storeData.status == 0" />
									否
								</label>
							</radio-group>
						</view>
					</view>

					<view class="common-form-item">
						<label class="form-label">营业时间</label>
						<view class="form-inline">
							<view class="form-input-inline long">
								<input type="text" v-model="storeData.open_date" class="form-input" />
							</view>
						</view>
					</view>

					<view class="common-form-item">
						<label class="form-label">物流配送</label>
						<view class="form-inline">
							<radio-group @change="expressChange" class="form-radio-group">
								<label class="radio form-radio-item">
									<radio value="1" :checked="storeData.is_express == 1" />
									开启
								</label>
								<label class="radio form-radio-item">
									<radio value="0" :checked="storeData.is_express == 0" />
									关闭
								</label>
							</radio-group>
						</view>
						<text class="form-word-aux-line">物流配送只有在连锁门店模式有效，在平台运营模式，按照总店查询</text>
					</view>

					<view class="common-form-item">
						<label class="form-label">同城配送</label>
						<view class="form-inline">
							<radio-group @change="o2oChange" class="form-radio-group">
								<label class="radio form-radio-item">
									<radio value="1" :checked="storeData.is_o2o == 1" />
									开启
								</label>
								<label class="radio form-radio-item">
									<radio value="0" :checked="storeData.is_o2o == 0" />
									关闭
								</label>
							</radio-group>
						</view>
						<text class="form-word-aux-line">开启同城配送需要门店设置配送费用以及配送员</text>
					</view>

					<view class="common-form-item">
						<label class="form-label">门店自提</label>
						<view class="form-inline">
							<radio-group @change="pickupChange" class="form-radio-group">
								<label class="radio form-radio-item">
									<radio value="1" :checked="storeData.is_pickup == 1" />
									开启
								</label>
								<label class="radio form-radio-item">
									<radio value="0" :checked="storeData.is_pickup == 0" />
									关闭
								</label>
							</radio-group>
						</view>
					</view>

					<block v-if="storeData.is_pickup == 1">
						<view class="common-form-item">
							<label class="form-label">自提日期</label>
							<view class="form-inline">
								<radio-group @change="timeTypeChange" class="form-radio-group">
									<label class="radio form-radio-item">
										<radio value="0" :checked="storeData.time_type == 0" />
										每天
									</label>
									<label class="radio form-radio-item">
										<radio value="1" :checked="storeData.time_type == 1" />
										自定义
									</label>
								</radio-group>
							</view>
						</view>

						<view class="common-form-item" v-if="storeData.time_type == 1">
							<label class="form-label">自提时间</label>
							<view class="form-block">
								<checkbox-group class="form-checkbox-group" @change="checkboxChange">
									<label class="form-checkbox-item">
										<checkbox value="1" :checked="storeData.time_week.includes('1') || storeData.time_week.includes(1)" />
										周一
									</label>
									<label class="form-checkbox-item">
										<checkbox value="2" :checked="storeData.time_week.includes('2') || storeData.time_week.includes(2)" />
										周二
									</label>
									<label class="form-checkbox-item">
										<checkbox value="3" :checked="storeData.time_week.includes('3') || storeData.time_week.includes(3)" />
										周三
									</label>
									<label class="form-checkbox-item">
										<checkbox value="4" :checked="storeData.time_week.includes('4') || storeData.time_week.includes(4)" />
										周四
									</label>
									<label class="form-checkbox-item">
										<checkbox value="5" :checked="storeData.time_week.includes('5') || storeData.time_week.includes(5)" />
										周五
									</label>
									<label class="form-checkbox-item">
										<checkbox value="6" :checked="storeData.time_week.includes('6') || storeData.time_week.includes(6)" />
										周六
									</label>
									<label class="form-checkbox-item">
										<checkbox value="0" :checked="storeData.time_week.includes('0') || storeData.time_week.includes(0)" />
										周日
									</label>
								</checkbox-group>
							</view>
						</view>

						<view class="common-form-item" v-for="(item, index) in storeData.delivery_time" :key="index">
							<label class="form-label">{{ index == 0 ? '时段设置' : '' }}</label>
							<view class="form-inline">
								<view class="form-input-inline">
									<picker mode="time" class="form-input" :value="timeFormat(item.start_time)" @change="bindStartTimeChange($event, index)">
										<view class="uni-input">{{ item.start_time ? timeFormat(item.start_time) : '00:00' }}</view>
									</picker>
								</view>
								<text class="form-mid">-</text>
								<view class="form-input-inline">
									<picker mode="time" class="form-input" :value="timeFormat(item.end_time)" @change="bindEndTimeChange($event, index)">
										<view class="uni-input">{{ item.end_time ? timeFormat(item.end_time) : '' }}</view>
									</picker>
								</view>
								<view class="time-action" v-if="index == 0" @click="addDeliveryTime">添加</view>
								<view class="time-action" v-else @click="deleteDeliveryTime(index)">删除</view>
							</view>
						</view>
						<view class="common-form-item">
							<label class="form-label">细分时段</label>
							<view class="form-block">
								<radio-group @change="timeIntervalChange" class="form-radio-group">
									<label class="radio form-radio-item">
										<radio value="30" :checked="storeData.time_interval == 30" />
										30分钟
									</label>
									<label class="radio form-radio-item">
										<radio value="60" :checked="storeData.time_interval == 60" />
										一小时
									</label>
									<label class="radio form-radio-item">
										<radio value="90" :checked="storeData.time_interval == 90" />
										90分钟
									</label>
									<label class="radio form-radio-item">
										<radio value="120" :checked="storeData.time_interval == 120" />
										两小时
									</label>
								</radio-group>
							</view>
						</view>
						<view class="common-form-item">
							<label class="form-label">提现预约</label>
							<view class="form-block">
								<radio-group @change="advanceDayChange" class="form-radio-group">
									<label class="radio form-radio-item">
										<radio value="0" :checked="storeData.advance_day === 0" />
										无需提前
									</label>
									<label class="radio form-radio-item">
										<radio value="1" :checked="storeData.advance_day !== 0" />
										需提前
										<input type="number" v-model="storeData.advance_day" class="radio-input" :class="{ disabled: storeData.advance_day === 0 }" :disabled="storeData.advance_day === 0" />
										天
									</label>
								</radio-group>
							</view>
							<text class="form-word-aux-line">预约提货是否需提前进行预约</text>
						</view>
						<view class="common-form-item">
							<label class="form-label">最长预约</label>
							<view class="form-block">
								<radio-group @change="mostDayChange" class="form-radio-group">
									<label class="radio form-radio-item">
										<radio value="0" :checked="storeData.most_day === 0" />
										无需提前
									</label>
									<label class="radio form-radio-item">
										<radio value="1" :checked="storeData.most_day !== 0" />
										可预约
										<input type="number" v-model="storeData.most_day" class="radio-input" :class="{ disabled: storeData.most_day === 0 }" :disabled="storeData.most_day === 0" />
										天内
									</label>
								</radio-group>
							</view>
							<text class="form-word-aux-line">预约提货最长可预约多少天内进行提货</text>
						</view>
					</block>

					<view class="common-form-item">
						<label class="form-label">库存设置</label>
						<view class="form-inline">
							<radio-group @change="stockTypeChange" class="form-radio-group">
								<label class="radio form-radio-item">
									<radio value="all" :disabled="Boolean(storeData.is_default)" :checked="storeData.stock_type == 'all'" />
									总部统一库存
								</label>
								<label class="radio form-radio-item">
									<radio value="store" :disabled="Boolean(storeData.is_default)" :checked="storeData.stock_type == 'store'" />
									门店独立库存
								</label>
							</radio-group>
						</view>
					</view>
				</template>

				<view class="common-form-item">
					<label class="form-label">会员搜索方式</label>
					<view class="form-inline">
						<radio-group @change="memberSearchWayChange" class="form-radio-group">
							<label class="radio form-radio-item">
								<radio value="exact" :checked="memberSearchWay == 'exact'" />
								精确搜索
							</label>
							<label class="radio form-radio-item">
								<radio value="list" :checked="memberSearchWay == 'list'" />
								列表搜索
							</label>
						</radio-group>
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
	editStore
} from '@/api/store.js'
import {setMemberSearchWayConfig} from '@/api/config.js'
import {mapGetters} from 'vuex';

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
			memberSearchWay: 'exact'
		};
	},
	onLoad() {},
	onShow() {
		this.getData();
	},
	computed: {
		...mapGetters(['memberSearchWayConfig'])
	},
	watch: {
		memberSearchWayConfig: {
			immediate: true,
			handler(newVal, oldVal) {
				if(newVal) {
					this.memberSearchWay = newVal.way;
				}
			}
		}
	},
	methods: {
		getData() {
			this.storeData = this.$util.deepClone(this.globalStoreInfo);
			this.storeData.start_time = this.timeFormat(this.storeData.start_time);
			this.storeData.end_time = this.timeFormat(this.storeData.end_time);
			if(this.memberSearchWayConfig) {
				this.memberSearchWay = this.memberSearchWayConfig.way;
			}
		},
		statusChange(e) {
			this.storeData.status = e.detail.value;
		},
		o2oChange(e) {
			this.storeData.is_o2o = e.detail.value;
		},
		expressChange(e) {
			this.storeData.is_express = e.detail.value;
		},
		pickupChange(e) {
			this.storeData.is_pickup = e.detail.value;
		},
		timeTypeChange(e) {
			this.storeData.time_type = e.detail.value;
		},
		bindStartTimeChange(e, index) {
			this.storeData.delivery_time[index].start_time = this.timeTurnTimeStamp(e.detail.value);
		},
		bindEndTimeChange(e, index) {
			this.storeData.delivery_time[index].end_time = this.timeTurnTimeStamp(e.detail.value);
		},
		stockTypeChange(e) {
			this.storeData.stock_type = e.detail.value;
		},
		memberSearchWayChange(e) {
			this.memberSearchWay = e.detail.value;
		},
		timeIntervalChange(e) {
			this.storeData.time_interval = e.detail.value;
		},
		checkboxChange(e) {
			this.storeData.time_week = e.detail.value;
		},
		advanceDayChange(e) {
			if (e.detail.value == 1) this.storeData.advance_day = '';
			else this.storeData.advance_day = 0;
		},
		mostDayChange(e) {
			if (e.detail.value == 1) this.storeData.most_day = '';
			else this.storeData.most_day = 0;
		},
		getSaveData() {
			let data = Object.assign({}, this.storeData);
			data.start_time = this.timeTurnTimeStamp(data.start_time);
			data.end_time = this.timeTurnTimeStamp(data.end_time);
			data.time_week = this.storeData.time_week.toString();
			data.advance_day = parseInt(this.storeData.advance_day);
			data.most_day = parseInt(this.storeData.most_day);
			data.delivery_time = JSON.stringify(this.storeData.delivery_time);
			return data;
		},
		checkData(data) {
			if (data.is_pickup) {
				let deliveryTimeVerify = true;
				for (let i = 0; i < this.storeData.delivery_time.length; i++) {
					let time = this.storeData.delivery_time[i];
					if (time.end_time == 0) {
						this.$util.showToast({
							title: '请选择时段结束时间'
						});
						deliveryTimeVerify = false;
						break;
					}
					if (parseInt(time.start_time) > parseInt(time.end_time)) {
						this.$util.showToast({
							title: '时段结束时间不能小于开始时间'
						});
						deliveryTimeVerify = false;
						break;
					}
					if ((parseInt(time.end_time) - parseInt(time.start_time)) / 60 < parseInt(data.time_interval)) {
						this.$util.showToast({
							title: '时段时间间隔不能小于' + data.time_interval + '分钟'
						});
						deliveryTimeVerify = false;
						break;
					}
				}
				if (!deliveryTimeVerify) return deliveryTimeVerify;
				if (isNaN(data.advance_day)) {
					this.$util.showToast({
						title: '提前预约时间格式错误'
					});
					return false;
				}
				if (data.advance_day < 0) {
					this.$util.showToast({
						title: '提前预约时间不能为负数'
					});
					return false;
				}
				if (isNaN(data.most_day)) {
					this.$util.showToast({
						title: '最长可预约时间格式错误'
					});
					return false;
				}
				if (data.most_day < 0) {
					this.$util.showToast({
						title: '最长可预约时间不能为负数'
					});
					return false;
				}
				if (data.most_day > 15) {
					this.$util.showToast({
						title: '最长可预约时间不能超过15天'
					});
					return false;
				}
			}
			return true;
		},
		saveFn() {
			let data = this.getSaveData();

			setMemberSearchWayConfig({
				way : this.memberSearchWay
			}).then((res)=>{
				if(res.code >= 0){
					this.$store.dispatch('app/getMemberSearchWayConfigFn');
					
					if (!this.addon.includes('store')) {
						this.$util.showToast({
							title: res.message
						});
					}
				}
			})
			
			if (this.addon.includes('store')) {

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
			}
		},
		timeTurnTimeStamp(_time) {
			let data = _time.split(':');
			return data[0] * 3600 + data[1] * 60;
		},
		timeFormat(time) {
			let h = parseInt(time / 3600);
			let i = parseInt((time % 3600) / 60);
			h = h < 10 ? '0' + h : h;
			i = i < 10 ? '0' + i : i;
			return h + ':' + i;
		},
		addDeliveryTime() {
			if (this.storeData.delivery_time.length >= 3) {
				this.$util.showToast({
					title: '最多添加三个时段'
				});
				return false;
			}
			this.storeData.delivery_time.push({
				start_time: 0,
				end_time: 0
			});
		},
		deleteDeliveryTime(index) {
			this.storeData.delivery_time.splice(index, 1);
		}
	}
};
</script>

<style lang="scss" scoped>
.store-operate {
	position: relative;
	.common-btn-wrap {
		position: absolute;
		left: 0;
		bottom: 0;
		right: 0;
		padding: 0.24rem 0.2rem;
	}
	.common-wrap {
		padding: 30rpx;
		height: calc(100vh - 0.4rem);
		overflow-y: auto;
		// padding-bottom: 1rem !important;
		box-sizing: border-box;

		.form-label {
			padding: .09rem .15rem;
			text-align: right;
			width: 1.2rem;
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

	.radio-input {
		width: 0.6rem;
		height: 0.35rem;
		line-height: 0.35rem;
		padding: 0 0.1rem;
		margin: 0 0.1rem;
		border: 0.01rem solid #eee;

		&.disabled {
			background: #f5f5f5;
		}
	}

	.time-action {
		color: $primary-color;
		cursor: pointer;
	}
}</style>
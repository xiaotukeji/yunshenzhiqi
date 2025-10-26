<template>
	<base-page>
		<view class="common-wrap common-form">
			<view class="common-title">预约设置</view>
			<view class="common-form-item">
				<label class="form-label">预约时间</label>
				<view class="form-block">
					<checkbox-group class="form-checkbox-group" @change="checkboxChange">
						<label class="form-checkbox-item">
							<checkbox value="1" :checked="week.includes('1') || week.includes(1)" />
							周一
						</label>
						<label class="form-checkbox-item">
							<checkbox value="2" :checked="week.includes('2') || week.includes(2)" />
							周二
						</label>
						<label class="form-checkbox-item">
							<checkbox value="3" :checked="week.includes('3') || week.includes(3)" />
							周三
						</label>
						<label class="form-checkbox-item">
							<checkbox value="4" :checked="week.includes('4') || week.includes(4)" />
							周四
						</label>
						<label class="form-checkbox-item">
							<checkbox value="5" :checked="week.includes('5') || week.includes(5)" />
							周五
						</label>
						<label class="form-checkbox-item">
							<checkbox value="6" :checked="week.includes('6') || week.includes(6)" />
							周六
						</label>
						<label class="form-checkbox-item">
							<checkbox value="0" :checked="week.includes('0') || week.includes(0)" />
							周日
						</label>
					</checkbox-group>
				</view>
			</view>
			<view class="common-form-item">
				<label class="form-label"></label>
				<view class="form-inline">
					<view class="form-input-inline">
						<picker mode="time" class="form-input" :value="time.start" @change="bindStartTimeChange">
							<view class="uni-input">{{ time.start }}</view>
						</picker>
					</view>
					<text class="form-mid">-</text>
					<view class="form-input-inline">
						<picker mode="time" class="form-input" :value="time.end" @change="bindEndTimeChange">
							<view class="uni-input">{{ time.end }}</view>
						</picker>
					</view>
				</view>
			</view>
			<view class="common-form-item">
				<label class="form-label">预约时间间隔</label>
				<view class="form-inline">
					<radio-group @change="radioChange" class="form-radio-group">
						<label class="radio form-radio-item">
							<radio value="30" :checked="interval == 30" />
							30分钟
						</label>
						<label class="radio form-radio-item">
							<radio value="60" :checked="interval == 60" />
							1个小时
						</label>
						<label class="radio form-radio-item">
							<radio value="90" :checked="interval == 90" />
							90分钟
						</label>
						<label class="radio form-radio-item">
							<radio value="120" :checked="interval == 120" />
							2小时
						</label>
					</radio-group>
				</view>
			</view>
			<view class="common-form-item">
				<label class="form-label">预约提前</label>
				<view class="form-input-inline"><input type="number" v-model="advance" class="form-input" /></view>
				<text class="form-word-aux">小时</text>
			</view>
			<view class="common-form-item">
				<label class="form-label">每时段可预约</label>
				<view class="form-input-inline"><input type="number" v-model="max" class="form-input" /></view>
				<text class="form-word-aux">人</text>
			</view>
			<view class="common-btn-wrap"><button type="default" class="screen-btn" @click="saveFn">保存</button></view>
			<ns-loading :layer-background="{ background: 'rgba(255,255,255,.8)' }" ref="loading"></ns-loading>
		</view>
	</base-page>
</template>

<script>
	import {
		getReserveConfig,
		setReserveConfig
	} from '@/api/reserve'

	export default {
		data() {
			return {
				time: {
					start: '08:30',
					end: '23:30'
				},
				interval: 30,
				advance: '',
				max: '',
				week: [],
				flag: false
			};
		},
		onLoad() {},
		onShow() {
			this.getData();
			uni.setLocale('zh-Hans');
		},
		methods: {
			getData() {
				getReserveConfig().then(res => {
					if (res.code >= 0) {
						({
							start: this.time.start,
							end: this.time.end,
							interval: this.interval,
							advance: this.advance,
							max: this.max,
							week: this.week
						} = res.data);
						this.time.start = this.timeFormat(this.time.start);
						this.time.end = this.timeFormat(this.time.end);
						this.$refs.loading.hide();
					} else {
						this.$util.showToast({
							title: res.message
						});
					}
				});
			},
			bindStartTimeChange(e) {
				this.time.start = e.detail.value;
			},
			bindEndTimeChange(e) {
				this.time.end = e.detail.value;
			},
			radioChange(e) {
				this.interval = e.detail.value;
			},
			checkboxChange(e) {
				this.week = e.detail.value;
			},
			getSaveData() {
				let data = {};
				data.start = this.timeTurnTimeStamp(this.time.start);
				data.end = this.timeTurnTimeStamp(this.time.end);
				data.interval = this.interval;
				data.advance = this.advance;
				data.max = this.max;
				data.week = this.week.toString();
				return data;
			},
			saveFn() {
				if (this.flag) return false;
				this.flag = true;
				setReserveConfig(this.getSaveData()).then(res => {
					this.flag = false;
					this.$util.showToast({
						title: res.message
					});
					if (res.code >= 0) {
						this.$refs.loading.show();
						this.getData();
					}
				});
			},
			timeTurnTimeStamp(time) {
				let data = time.split(':');
				return data[0] * 3600 + data[1] * 60;
			},
			timeFormat(time) {
				let h = time / 3600;
				let i = (time % 3600) / 60;
				h = h < 10 ? '0' + h : h;
				i = i < 10 ? '0' + i : i;
				return h + ':' + i;
			}
		}
	};
</script>

<style lang="scss" scoped>
	.common-wrap {
		position: relative;
		padding: 30rpx;
		height: calc(100vh - 51px);

		.form-label {
			width: 1.5rem !important;
		}

		.common-btn-wrap {
			position: absolute;
			left: 0;
			right: 0;
			bottom: 0;
			padding: 0.24rem 0.2rem;
			margin-left: 0;
			text-align: center;
			height: 0.4rem;
			button {
				width: 100%;
				height: 0.4rem;
				line-height: 0.4rem;
			}
		}
	}

	.common-title {
		font-size: 0.18rem;
		margin-bottom: 0.2rem;
	}
</style>
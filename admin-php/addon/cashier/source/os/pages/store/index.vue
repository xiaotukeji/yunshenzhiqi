<template>
	<base-page>
		<view class="">
			<view class="store-information" v-if="addon.includes('store')">
				<view class="store-status">门店信息</view>
				<view class="store-types">
					<view class="info-left">
						<view class="type type1">
							<view>门店名称：</view>
							<view>{{ storeData.store_name }}</view>
						</view>
						<view class="type type1">
							<view>门店电话：</view>
							<view class="message">{{ storeData.telphone }}</view>
						</view>
						<view class="type type1">
							<view>门店类型：</view>
							<view class="message">{{ storeData.store_type == 'directsale' ? '直营店' : '加盟店' }}</view>
						</view>
						<view class="type type1">
							<view>门店地址：</view>
							<view class="message">{{ storeData.full_address }}{{ storeData.address }}</view>
						</view>
					</view>
					<view class="info-img">
						<image :src="$util.img(storeData.store_image)" @error="$util.img(defaultImg.store)" mode="aspectFit"/>
					</view>
					<view class="btn" @click="$util.redirectTo('/pages/store/config')">设置</view>
				</view>
			</view>

			<view class="store-information">
				<view class="store-status">运营信息</view>
				<view class="store-types">
					<view class="info-left">
						<template v-if="addon.includes('store')">
							<view class="type type1">
								<view>营业状态：</view>
								<view class="message" v-if="storeData.is_frozen == 1">已停业</view>
								<view class="message" v-else>{{ storeData.status == 1 ? '营业中' : '休息' }}</view>
							</view>
							<view class="type type1">
								<view>营业时间：</view>
								<view class="message">{{ storeData.open_date }}</view>
							</view>
							<view class="type type1">
								<view>物流配送：</view>
								<view class="message">{{ storeData.is_express ? '开启' : '关闭' }}</view>
							</view>
							<view class="type type1">
								<view>同城配送：</view>
								<view>{{ storeData.is_o2o ? '开启' : '关闭' }}</view>
							</view>
							<view class="type type1">
								<view>门店自提：</view>
								<view class="message">{{ storeData.is_pickup ? '开启' : '关闭' }}</view>
							</view>
							<view class="type type1">
								<view>自提日期：</view>
								<view class="message" v-if="storeData.time_type == 1">
									<text class="week" v-if="storeData.time_week.includes('1') || storeData.time_week.includes(1)">周一</text>
									<text class="week" v-if="storeData.time_week.includes('2') || storeData.time_week.includes(2)">周二</text>
									<text class="week" v-if="storeData.time_week.includes('3') || storeData.time_week.includes(3)">周三</text>
									<text class="week" v-if="storeData.time_week.includes('4') || storeData.time_week.includes(4)">周四</text>
									<text class="week" v-if="storeData.time_week.includes('5') || storeData.time_week.includes(5)">周五</text>
									<text class="week" v-if="storeData.time_week.includes('6') || storeData.time_week.includes(6)">周六</text>
									<text class="week" v-if="storeData.time_week.includes('0') || storeData.time_week.includes(0)">周日</text>
								</view>
								<view class="message" v-if="storeData.time_type == 0">每天</view>
							</view>
							<view class="type type1">
								<view>自提时间：</view>
								<view class="message">{{ storeData.start_time }}-{{ storeData.end_time }}</view>
							</view>

							<view class="type type1">
								<view>库存设置：</view>
								<view class="message">{{ storeData.stock_type == 'all' ? '总部统一库存' : '门店独立库存' }}</view>
							</view>
						</template>

						<view class="type type1">
							<view>会员搜索方式：</view>
							<view class="message">{{ memberSearchWayConfig.way == 'exact' ? '精确搜索' : '列表搜索' }}</view>
						</view>

					</view>
					<view class="btn" @click="$util.redirectTo('/pages/store/operate')">设置</view>
				</view>
			</view>
		</view>
	</base-page>
</template>

<script>
	import {mapGetters} from 'vuex';

	export default {
		data() {
			return {
				storeData: {
					store_name: '',
					store_image: '',
					status: 0,
					telphone: '',
					open_date: '',
					is_o2o: 0,
					is_pickup: 0,
					time_type: 0,
					start_time: '00:00',
					end_time: '23:59',
					stock_type: 'all',
					time_week: '',
					latitude: 39.909,
					longitude: 116.39742,
					province_id: 110000,
					city_id: 110100,
					district_id: 110101,
					address: '',
					full_address: '',
					store_type: 'directsale'
				}
			};
		},
		onLoad() {},
		onShow() {
			this.getData();
		},
		computed: {
			...mapGetters(['memberSearchWayConfig'])
		},
		methods: {
			getData() {
				this.storeData = this.$util.deepClone(this.globalStoreInfo);
				this.storeData.start_time = this.timeFormat(this.storeData.start_time);
				this.storeData.end_time = this.timeFormat(this.storeData.end_time);
			},
			timeFormat(time) {
				let h = parseInt(time / 3600);
				let i = parseInt((time % 3600) / 60);
				h = h < 10 ? '0' + h : h;
				i = i < 10 ? '0' + i : i;
				return h + ':' + i;
			}
		}
	};
</script>

<style lang="scss" scoped>
	.store-information {
		width: 100%;
		box-sizing: border-box;
		padding-bottom: 0.1rem;
		margin-bottom: 0.2rem;
		.store-status {
			font-size: 0.24rem;
			font-weight: bold;
			height: 0.6rem;
			line-height: 0.6rem;
			padding-left: 0.2rem;
		}

		.store-types {
			width: 100%;
			background: #ffffff;
			padding: 0.2rem 0.3rem;
			display: flex;
			flex-direction: row;
			justify-content: space-between;
			margin-bottom: 0.2rem;
			box-sizing: border-box;
			position: relative;

			.info-left {
				display: flex;
				flex-direction: column;
				justify-content: space-between;
			}

			.btn {
				position: absolute;
				top: 0.2rem;
				right: 0.2rem;
				color: $primary-color;
				cursor: pointer;
			}

			.info-img {
				margin-top: 0.4rem;

				image {
					max-width: 1.5rem;
					height: 1rem;
				}
			}

			.type {
				padding-left: 0.1rem;

				view {
					font-size: 0.14rem;

					.look {
						color: $primary-color;
						margin-left: 0.24rem;
					}
				}

				view:nth-child(1) {
					width: 1rem;
					text-align: right;
					margin-right: 0.1rem;
				}
			}

			.type1 {
				display: flex;
				align-items: center;
				height: 0.34rem;
			}
		}

	}

	.week {
		margin-right: 0.1rem;
	}
</style>
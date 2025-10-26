<template>
	<base-page>
		<view class="scalelist">
			<view class="scalelist-box">
				<view class="scalelist-left">
					<view class="scale-title">
						电子秤
						<text class="iconfont icongengduo1"></text>
					</view>
					<view class="scale-list-wrap">
						<block v-if="list.length > 0">
							<scroll-view scroll-y="true" class="scale-list-scroll all-scroll" @scrolltolower="getList">
								<view class="item" @click="scaleSelect(item, index)" v-for="(item, index) in list" :key="index" :class="index == selectScaleKeys ? 'itemhover' : ''">
									<view class="item-right w-full">
										<view class="flex justify-between w-full">
											<view class="scale-name">{{ item.name }}<text class="scale-type-tag">{{ item.type == 'cashier' ? '收银秤': '条码秤' }}</text></view>
											<view class="flex items-center" v-if="item.connect_status">
												<text class="status-icon success"></text>已连接
											</view>
											<view class="flex items-center" v-else>
												<text class="status-icon fail"></text>未连接
											</view>
										</view>
										<view class="scale-money">{{ item.brand_name }}-{{ item.model_name }}</view>
									</view>
								</view>
							</scroll-view>
						</block>
						<view class="notYet" v-else-if="!one_judge && list.length == 0">暂无电子秤</view>
					</view>
					<view class="add-printer">
						<button type="default" class="primary-btn" @click="addScale">添加电子秤</button>
					</view>
				</view>
				<view class="scalelist-right" v-show="!one_judge">
					<view class="scale-title">电子秤详情</view>
					<view class="scale-information">
						<block v-if="JSON.stringify(detail) != '{}'">
							<view class="title">基本信息</view>
							<view class="information-box">
								<view class="box-left">
									<view class="information">
										<view>电子秤名称：</view>
										<view>{{ detail.name }}</view>
									</view>
									<view class="information">
										<view>电子秤类型：</view>
										<view>{{ detail.type == 'cashier' ? '收银秤' : '条码秤' }}</view>
									</view>
									<view class="information">
										<view>电子秤品牌：</view>
										<view>{{ detail.brand_name }}</view>
									</view>
									<view class="information">
										<view>电子秤型号：</view>
										<view>{{ detail.model_name }}</view>
									</view>
								</view>
							</view>

						</block>
						<block v-else>
							<image class="cart-empty" src="@/static/cashier/cart_empty.png" mode="widthFix"/>
						</block>

					</view>
					<view class="button-box" v-if="JSON.stringify(detail) != '{}'">
						<button class="default-btn" @click="$refs.deletePop.open()">删除</button>
						<button class="default-btn" @click="editScale(detail.scale_id)">修改</button>
					</view>
				</view>
			</view>
		</view>
		<!-- 删除 -->
		<uni-popup ref="deletePop" type="center">
			<view class="confirm-pop">
				<view class="title">确定要删除吗？</view>
				<view class="btn">
					<button type="primary" class="default-btn btn save" @click="$refs.deletePop.close()">取消</button>
					<button type="primary" class="primary-btn btn" @click="deleteScaleFn(detail.scale_id)">确定</button>
				</view>
			</view>
		</uni-popup>
	</base-page>
</template>

<script>
import {
	getScaleList,
	getScaleDetail,
	deleteScale
} from '@/api/scale.js'

var self;

export default {
	data() {
		return {
			selectScaleKeys: 0,
			search_text: '',
			page: 1,
			// 每次返回数据数
			page_size: 8,
			// 第一次请求列表做详情渲染判断
			one_judge: true,
			//详情数据
			detail: {},
			brandList: {
				yilianyun: '易联云',
				'365': '365'
			},
			flag: false,
			template: {},
			list: [],
			connectSuccess: []
		};
	},
	onLoad() {
		// 初始化请求打印机列表数据
		this.getList();
		self = this;
	},
	methods: {
		switchStoreAfter() {
			this.page = 1;
			this.list = [];
			this.detail = {};
			this.one_judge = false;
			this.getList()
		},
		getList() {
			if (!this.addon.includes('scale')) {
				this.$util.showToast({
					title: '未安装电子秤插件'
				});
				return;
			}
			getScaleList({
				page: this.page,
				page_size: this.page_size,
			}).then(res => {
				if (res.data.list.length == 0 && this.one_judge) {
					this.detail = {};
					this.one_judge = false;
				}
				if (res.code >= 0 && res.data.list.length != 0) {
					this.page += 1;
					if (this.list.length == 0) {
						this.list = res.data.list;
					} else {
						this.list = this.list.concat(res.data.list);
					}

					// 检测设备是否连接
					this.checkConnect();

					//初始时加载一遍详情数据
					if (this.one_judge) {
						this.getDetailFn(this.list[0].scale_id);
					}
				}
			})
		},
		scaleSelect(item, keys) {
			this.selectScaleKeys = keys;
			this.getDetailFn(item.scale_id);
		},
		addScale() {
			this.$util.redirectTo('/pages/scale/add');
		},
		editScale(scale_id) {
			this.$util.redirectTo('/pages/scale/add', {
				scale_id: scale_id
			});
		},
		getDetailFn(scale_id) {
			getScaleDetail({
				scale_id
			}).then(res => {
				if (res.code == 0) {
					this.detail = res.data;
					this.one_judge = false;
				}
			})
		},
		deleteScaleFn(scale_id) {
			if (this.flag) return;
			this.flag = true;
			deleteScale({scale_id}).then(res => {
				this.flag = false;
				if (res.code >= 0) {
					this.page = 1;
					this.list = [];
					this.one_judge = true;
					this.$refs.deletePop.close()
					this.getList();
				} else {
					this.$util.showToast({
						title: res.message
					});
				}
			})
		},
		checkConnect() {
			if (typeof window.POS_DATA_CALLBACK == 'function') delete window.POS_DATA_CALLBACK;
			/**
			 * 商品同步数据回调
			 * @param {Object} text
			 */
			window.POS_DATA_CALLBACK = function (text) {
				let data = text.split(':');
				let index = parseInt(data[0]);

				switch (data[1]) {
					case 'PingWeigher':
						self.$set(self.list[index], 'connect_status', parseInt(data[3]));
						break;
				}
			};

			try {
				let weigher = this.list.map(item => {
					item.config = typeof item.config == 'string' ? JSON.parse(item.config) : scale.config;
					return item
				});

				this.$pos.send('PingWeigher', JSON.stringify({
					weigher
				}));
			} catch (e) {
			}
		}
	}
};
</script>

<style scoped lang="scss">
@import './public/css/scale.scss';
</style>
<template>
	<view>
		<uni-popup ref="memberInquirePopup" type="center" @change="popupChange" :mask-click="false">
			<view class="popup-inquire-wrap">
				<view class="popup-header">
					<text class="title">称重商品同步到电子秤</text>
					<text class="iconfont iconguanbi1" @click="$refs.memberInquirePopup.close()"></text>
				</view>

				<view class="popup-content">
					<view v-show="step == 1">
						<view class="content-title">选择商品</view>
						<uniDataTable url="/weighgoods/storeapi/goods/skuall" :cols="cols" :classType="true" @checkBox="checkBox" ref="goodsListTable" />
					</view>
					<view v-show="step == 2">
						<view class="content-title">选择需同步的电子秤</view>
						<uniDataTable url="/scale/storeapi/scale/page" :pagesize="0" :cols="scaleCols" :classType="true" ref="scaleListTable" @tableData="onloadScale" />
					</view>
					<view v-show="step == 3">
						<view class="content-title">同步商品到电子秤</view>
						<uniDataTable :cols="syncTaskCols" :classType="true" ref="syncTaskTable" :data="syncTask" />
					</view>
				</view>

				<view class="popup-footer" v-show="step == 1">
					<button type="default" class="default-btn" @click="next">下一步</button>
				</view>
				<view class="popup-footer" v-show="step == 2">
					<button type="default" class="default-btn" @click="step = 1">上一步</button>
					<button type="primary" class="primary-btn" @click="syncGoods">同步</button>
				</view>
				<view class="popup-footer" v-show="step == 3">
					<button type="default" class="primary-btn" :loading="synching" v-if="synching">同步中</button>
					<button type="default" class="primary-btn" :loading="synching" @click="$refs.memberInquirePopup.close()" v-else>完成</button>
				</view>
			</view>
		</uni-popup>
	</view>
</template>

<script>
	import uniDataTable from '@/components/uni-data-table/uni-data-table.vue';
	import { getScaleList } from '@/api/scale.js';
	import {
		createCommand
	} from './command.js'

	var self;

	export default {
		components: {
			uniDataTable
		},
		data() {
			return {
				step: 1,
				cols: [{
						width: 6,
						align: 'center',
						checkbox: true
					},
					{
						field: 'account_data',
						width: 50,
						title: '商品信息',
						align: 'left',
						templet: data => {
							let img = this.$util.img(data.sku_image);
							let html = `
							<view class="goods-content">
								<image class="goods-img" src="${img}" mode="aspectFit"/>
								<text class="goods-name multi-hidden">${data.sku_name}</text>
							</view>
						`;
							return html;
						}
					},
					{
						width: 10,
						title: '价格',
						align: 'center',
						templet: function(data) {
							return '￥' + data.price;
						}
					},
					{
						field: 'plu',
						width: 10,
						title: 'PLU码',
						align: 'center'
					},
					{
						width: 10,
						title: '计价方式',
						align: 'center',
						templet: function(data) {
							if (data.pricing_type == 'num') {
								return '计数';
							} else {
								return '计重';
							}
						}
					},
					{
						width: 10,
						title: '状态',
						align: 'center',
						templet: function(data) {
							var str = '';
							if (data.store_status == 1) {
								str = '销售中';
							} else if (data.store_status == 0) {
								str = '仓库中';
							}
							return str;
						}
					}
				],
				scaleCols: [{
						width: 6,
						align: 'center',
						checkbox: true,
						disabled: (data) => {
							return !data.connect_status
						}
					},
					{
						width: 24,
						title: '设备名称',
						align: 'left',
						field: 'name',
					},
					{
						width: 20,
						title: '设备品牌',
						align: 'center',
						field: 'brand_name',
					},
					{
						width: 20,
						title: '设备型号',
						align: 'center',
						field: 'model_name',
					},
					{
						width: 20,
						title: '状态',
						align: 'center',
						templet: (data) => {
							return data.connect_status ? '已连接' : '未连接'
						}
					}
				],
				syncTaskCols: [{
						width: 70,
						title: '设备名称',
						align: 'left',
						templet: (data) => {
							return data.name
						}
					},
					{
						width: 30,
						title: '同步状态',
						align: 'left',
						templet: (data) => {
							var str = '';
							switch (data.syncStatus) {
								case '1':
									str = `<view>同步成功</view>`;
									break;
								case '0':
									str = `<view>同步失败</view><view style="color: red;display:block;white-space: normal;">失败原因：${data.msg}</view>`;
									break;
								default:
									str = `<view>同步中</view>`;
									break;
							}
							return str;
						}
					}
				],
				syncTask: {},
				synching: false
			};
		},
		created() {
			this.getScaleListFn();
			self = this;
		},
		methods: {
			popupChange(e) {
				if (!e.show) {
					this.syncTask = {};
					this.synching = false;
					this.step = 1
				}
			},
			open() {
				this.$refs.memberInquirePopup.open();
			},
			selectScale(e) {
				this.scale = this.scaleList[e].name;
				this.scaleId = this.scaleList[e].scale_id
			},
			checkBox(e) {
				this.goodsList = e;
			},
			getScaleListFn() {
				if (!this.addon.includes('scale')) {
					return;
				}
				getScaleList({
					page: 1,
					page_size: 100
				}).then(res=>{
					if (res.data.list.length > 0) this.scaleList = res.data.list;
				});
			},
			next() {
				const selected = this.$refs.goodsListTable.selected;
				if (!selected.length) {
					this.$util.showToast({
						'title': '请选择要同步的商品'
					});
					return
				}
				this.step += 1;
			},
			onloadScale(list) {
				if (typeof window.POS_DATA_CALLBACK == 'function') delete window.POS_DATA_CALLBACK;

				/**
				 * 商品同步数据回调
				 * @param {Object} text
				 */
				window.POS_DATA_CALLBACK = function(text) {
					let data = text.split(':');
					let index = parseInt(data[0]);

					switch (data[1]) {
						case 'SyncGoodsPlu':
							self.$set(self.syncTask[index], 'syncStatus', data[2]);
							self.$set(self.syncTask[index], 'msg', data[4]);

							if (index == self.syncTask.length - 1) {
								self.synching = false
							}
							break;
						case 'PingWeigher':
							self.$set(self.$refs.scaleListTable.list[index], 'connect_status', parseInt(data[3]));
							break;
					}
				};

				let weigher = list.map(item => {
					item.config = JSON.parse(item.config);
					return item;
				});

				try {
					this.$pos.send('PingWeigher', JSON.stringify({
						weigher
					}));
				} catch (e) {}
			},
			async syncGoods() {
				const selected = this.$refs.scaleListTable.selected;
				if (!selected.length) {
					this.$util.showToast({
						'title': '请选择要同步的设备'
					});
					return;
				}
				if (this.synching) return;
				this.synching = true;

				this.syncTask = this.$refs.scaleListTable.selected
				this.step += 1;

				setTimeout(() => {
					this.createSyncData()
				}, 100)
			},
			createSyncData() {
				let task = {};
				task.weigher = this.$refs.scaleListTable.selected.map(scale => {
					scale.config = typeof scale.config == 'string' ? JSON.parse(scale.config) : scale.config;
					scale.goodsList = this.$refs.goodsListTable.selected.map(sku => {
						return {
							sku_no: sku.sku_no,
							plu: sku.plu,
							price: sku.price,
							sku_name: sku.sku_name,
							pricing_type: sku.pricing_type,
							command: ''
						}
					});
					return scale;
				});

				this.syncTask = task.weigher;

				try {
					console.log(JSON.stringify(task));
					this.$pos.send('SyncGoodsPlu', JSON.stringify(task));
				} catch (e) {
					this.synching = false
				}
			}
		}
	};
</script>

<style lang="scss" scoped>
	.popup-inquire-wrap {
		overflow: hidden;
		width: 9.55rem;
		height: 5.37rem;
		background-color: #fff;
		border-radius: 0.05rem;
		display: flex;
		flex-direction: column;

		.popup-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 0 0.15rem;
			height: 0.45rem;
			line-height: 0.45rem;
			border-bottom: 0.01rem solid #e8eaec;

			.iconfont {
				font-size: $uni-font-size-lg;
			}
		}

		.popup-content {
			flex: 1;
			height: 0;
			overflow-y: auto;
			padding: 0.1rem;

			/deep/ .content {}

			.content-title {
				margin-bottom: .1rem;
			}
		}

		.popup-footer {
			padding: 0.1rem;
			display: flex;
			justify-content: end;

			button {
				width: 1rem;
				margin: 0 0 0 .15rem;
			}
		}

	}

	/deep/.goods-content {
		display: flex;

		.goods-img {
			margin-right: 0.1rem;
			width: 0.5rem;
			height: 0.5rem;
		}

		.goods-name {
			white-space: pre-wrap;
			align-self: baseline;
		}
	}

	/deep/.uni-select-lay-select {
		width: 2rem !important;
		height: 0.42rem !important;
	}
</style>
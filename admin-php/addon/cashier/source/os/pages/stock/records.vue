<template>
	<base-page>
		<view class="manage">
			<view class="screen-warp common-form">
				<view class="common-form-item">
					<view class="form-inline">
						<label class="form-label">业务类型</label>
						<view class="form-input-inline">
							<picker mode="selector" :range="classifyData.data" @change="pickerChange">
								<view class="form-input">
									{{ classifyData.currIndex === '' ? '请选择业务类型' : classifyData.data[classifyData.currIndex] }}
								</view>
							</picker>
						</view>
					</view>
				</view>
				<view class="common-form-item">
					<view class="form-inline">
						<label class="form-label">时间</label>
						<view class="form-input-inline">
							<picker mode="date" @change="startChange">
								<view class="form-input">
									{{ !searchData.start_time ? '请输入开始时间' : searchData.start_time }}
								</view>
							</picker>
						</view>
						<text class="form-mid">-</text>
						<view class="form-input-inline">
							<picker mode="date" @change="endChange">
								<view class="form-input">{{ !searchData.end_time ? '请输入结束时间' : searchData.end_time }}</view>
							</picker>
						</view>
					</view>
				</view>
				<view class="common-btn-wrap">
					<button type="default" class="screen-btn" @click="searchFn()">筛选</button>
					<button type="default" @click="resetFn()">重置</button>
				</view>
			</view>

			<view class="manage-table">
				<uni-table ref="table" :loading="table.loading" border stripe emptyText="暂无更多数据">
					<uni-tr>
						<uni-th width="100" align="left">时间</uni-th>
						<uni-th width="100" align="center">商品信息</uni-th>
						<uni-th width="100" align="center">操作人</uni-th>
						<uni-th width="100" align="center">业务类型</uni-th>
						<uni-th width="100" align="center">原库存</uni-th>
						<uni-th width="100" align="center">库存变化</uni-th>
						<uni-th width="100" align="center">现库存</uni-th>
						<uni-th width="100" align="center">备注</uni-th>
						<uni-th width="50" align="right">操作</uni-th>
					</uni-tr>
					<uni-tr v-for="(item, index) in table.data">
						<uni-td align="left">{{ $util.timeFormat(item.create_time) }}</uni-td>
						<uni-td align="center">{{ item.goods_sku_name }}</uni-td>
						<uni-td align="center">{{ item.operater_name }}</uni-td>
						<uni-td align="center">{{ item.name }}</uni-td>
						<uni-td align="center">{{ item.before_store_stock }}</uni-td>
						<uni-td align="center">{{ (item.type == 'input' ? '+' : '-') + item.goods_num }}</uni-td>
						<uni-td align="center">{{ item.after_store_stock }}</uni-td>
						<uni-td align="center">{{ item.remark }}</uni-td>
						<uni-td align="right">
							<view class="action-btn">
								<text @click="toDetail(item)">查看</text>
							</view>
						</uni-td>
					</uni-tr>
				</uni-table>
				<view class="paging-wrap">
					<uni-pagination show-icon :page-size="paging.pageSize" :current="paging.pageCurrent" :total="paging.total" @change="paginChange" />
				</view>
			</view>
		</view>
	</base-page>
</template>
<script>
	import {
		getDocumentType,
		getStockGoodsRecords
	} from '@/api/stock.js';

	export default {
		data() {
			return {
				classifyData: {
					data: [],
					idArr: [],
					currIndex: ''
				},
				table: {
					loading: false, //表格加载动画
					data: []
				},
				paging: {
					pageSize: 9, // 每页数据量
					pageCurrent: 1, // 当前页
					total: 0 // 数据总量
				},
				searchData: {
					type: '',
					start_time: '',
					end_time: ''
				}
			};
		},
		onLoad(option) {
			this.DocumentType();
		},
		onShow() {
			this.getTableData();
		},
		methods: {
			searchFn() {
				this.table.loading = true;
				this.paging.pageCurrent = 1;
				this.getTableData(this.searchData);
			},
			resetFn() {
				this.table.loading = true;
				this.paging.pageCurrent = 1;
				this.classifyData.currIndex = '';
				this.searchData.type = '';
				this.searchData.start_time = '';
				this.searchData.end_time = '';
				this.getTableData(this.searchData);
			},
			getTableData(obj = {}) {
				let data = {
					page_size: this.paging.pageSize,
					page: this.paging.pageCurrent
				};
				Object.assign(data, obj);
				getStockGoodsRecords(data).then(res => {
					this.table.loading = false;
					if (res.code == 0) {
						this.table.data = res.data.list;
						this.$forceUpdate();
					} else {
						this.$util.showToast({
							title: res.message
						});
					}
					this.paging.total = res.data.count;
				})
			},
			DocumentType() {
				getDocumentType().then(res => {
					let data = res.data;
					if (res.code == 0 && data.length) {
						data.forEach((item, index) => {
							this.classifyData.data.push(item.name);
							this.classifyData.idArr.push(item.key);
						});
						this.$forceUpdate();
					}
				})
			},
			pickerChange(e) {
				let index = e.detail.value;
				this.classifyData.currIndex = index;
				this.searchData.type = this.classifyData.idArr[index];
			},
			// 切换分页
			paginChange(e) {
				this.table.loading = true;
				this.paging.pageCurrent = e.current;
				this.getTableData();
			},
			startChange(e) {
				this.searchData.start_time = e.detail.value;
			},
			endChange(e) {
				let start_time = this.$util.timeTurnTimeStamp(this.searchData.start_time),
					end_time = this.$util.timeTurnTimeStamp(e.detail.value);

				if (end_time <= start_time) {
					this.$util.showToast({
						title: '结束时间不能小于开始时间'
					});
					return false;
				}
				this.searchData.end_time = e.detail.value;
			},
			toDetail(data) {
				let url = data.type == 'input' ? '/pages/stock/storage' : '/pages/stock/wastage';
				this.$util.redirectTo(url, {
					id: data.document_no
				});
			}
		}
	};
</script>

<style lang="scss" scoped>
	.manage {
		background-color: #fff;
		padding: 0.15rem;
		@extend %body-overhide;
	}

	// 筛选面板
	.screen-warp {
		padding: 0.15rem;
		background-color: #f2f3f5;
		margin-bottom: 0.15rem;
	}

	.paging-wrap {
		margin-top: 0.1rem;
	}

	// 表格详情
	.manage-table {

		.action-btn {
			text {
				color: $primary-color;
			}
		}
	}
</style>
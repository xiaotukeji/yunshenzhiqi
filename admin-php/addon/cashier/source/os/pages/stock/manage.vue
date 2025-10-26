<template>
	<base-page>
		<view class="manage">
			<view class="screen-warp common-form">
				<view class="common-form-item">
					<view class="form-inline">
						<label class="form-label">商品名称/编码</label>
						<view class="form-input-inline">
							<input type="text" v-model="option.search" placeholder="请输入商品名称/编码" class="form-input" />
						</view>
					</view>
					<view class="form-inline goods-category">
						<label class="form-label">商品分类</label>
						<view class="form-input-inline">
							<uni-data-picker v-model="option.category_id" :localdata="classifyData.data" popup-title="请选择商品分类"></uni-data-picker>
						</view>
					</view>
					<view class="form-inline common-btn-wrap">
						<button type="default" class="screen-btn" @click="searchFn()">筛选</button>
						<button type="default" @click="resetFn()">重置</button>
					</view>
				</view>
			</view>

			<view class="manage-table">
				<uniDataTable url="/stock/storeapi/manage/lists" :option="option" :cols="cols" ref="goodsListTable">
					<template v-slot:action="dataTable">
						<view class="action-btn">
							<text @click="toDetail()">查看流水</text>
						</view>
					</template>
				</uniDataTable>
			</view>
		</view>
	</base-page>
</template>
<script>
	import {
		getGoodsCategory
	} from '@/api/goods.js';
	import uniDataPicker from '@/components/uni-data-picker/uni-data-picker.vue';
	import uniDataTable from '@/components/uni-data-table/uni-data-table.vue';

	export default {
		components: {
			uniDataPicker,
			uniDataTable
		},
		data() {
			return {
				classifyData: {
					data: []
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
				option: {
					search: '',
					category_id: '',
					page_size: 10
				},
				cols: [
				{
					field: 'account_data',
					width: 20,
					title: '产品名称',
					align: 'left',
					templet: data => {
						let img = this.$util.img(data.sku_image, { size: 'small' });
						let html = `
								<view class="goods-content">
									<image class="goods-img" src="${img}" mode="aspectFill" />
									<text class="goods-name multi-hidden">${data.goods_name}</text>
								</view>
							`;
						return html;
					}
				}, {
					width: 19,
					title: '规格',
					align: 'center',
					field: 'spec_name'
				}, {
					width: 13,
					title: '编码',
					align: 'center',
					field: 'sku_no'
				}, {
					field: 'real_stock',
					width: 10,
					title: '库存',
					align: 'center'
				}, {
					field: 'cost_price',
					width: 12,
					title: '成本',
					align: 'center'
				}, {
					width: 20,
					title: '添加时间',
					templet: data => {
						return this.$util.timeFormat(data.create_time);
					}
				}, {
					width: 15,
					title: '操作',
					align: 'right',
					action: true
				}],
			};
		},
		onLoad(option) {
			this.getCategory();
		},
		methods: {
			// 搜索商品
			searchFn() {
				this.$refs.goodsListTable.load({
					page: 1
				});
			},
			resetFn() {
				this.option.search = '';
				this.option.category_id = '';
				this.$refs.goodsListTable.load({
					page: 1
				});
			},
			getCategory() {
				getGoodsCategory({
					level: 3
				}).then(res => {

					let data = res.data;
					if (res.code == 0 && data.length) {
						this.classifyData.data = this.analyzeCategory(data);
						this.$forceUpdate();
					} else {
						this.$util.showToast({
							title: res.message
						});
					}
				})
			},
			analyzeCategory(data) {
				var arr = data.map((item, index) => {
					var obj = {};
					obj.text = item.category_name;
					obj.value = item.category_id;
					if (item.child_list && item.child_list.length) {
						obj.children = this.analyzeCategory(item.child_list);
					}
					return obj;
				});
				return arr;
			},
			toDetail() {
				this.$util.redirectTo('/pages/stock/records');
			}
		}
	};
</script>

<style lang="scss" scoped>
	.manage {
		position: relative;
		background-color: #fff;
		padding: 0.15rem;
		min-height: 100vh;
		box-sizing: border-box;
	}

	// 筛选面板
	.screen-warp {
		padding: 0.15rem;
		background-color: #f2f3f5;
		margin-bottom: 0.15rem;

		.common-form-item .form-label {
			width: 1.2rem;
		}

		.common-btn-wrap {
			margin-left: 1.2rem;
		}

		.goods-category .form-input-inline {
			width: 2.8rem;
			/deep/ .input-value-border{
				border-width: 0;
			}
		}

		.common-form-item {
			margin-bottom: 0;
		}
	}

	/deep/ .goods-content {
	  display: flex;
	  .goods-img {
	    margin-right: 0.1rem;
	    width: 0.5rem;
	    height: 0.5rem;
	  }
	
	  .goods-name {
		flex: 1;
	    white-space: pre-wrap;
	    align-self: baseline;
	  }
	}
	.action-btn {
		text {
			color: $primary-color;
		}
	}
</style>
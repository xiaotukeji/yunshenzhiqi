<template>
	<base-page>
		<view class="manage">
			<view class="title-back flex items-center cursor-pointer" @click="backFn">
                    <text class="iconfont iconqianhou1"></text>
                    <text class="left">返回</text>
                    <text class="content">|</text>
                    <text>账户记录</text>
                </view>
			<view class="screen-warp common-form">
				<view class="common-form-item">
					<view class="form-inline goods-category">
						<label class="form-label">来源方式</label>
						<view class="form-input-inline">
							<select-lay :zindex="10" :value="screen.from_type" name="names" placeholder="请选择来源方式" :options="fromType" @selectitem="selectFromType"/>
						</view>
					</view>
					<view class="form-inline">
						<label class="form-label">发生时间</label>
						<view class="form-input-inline">
							<uni-datetime-picker v-model="screen.start_date" type="datetime" placeholder="请选择开始时间" :clearIcon="false" />
						</view>
						<view class="form-input-inline">
							<uni-datetime-picker v-model="screen.end_date" type="datetime" placeholder="请选择结束时间" :clearIcon="false" />
						</view>
					</view>
					<view class="form-inline">
						<label class="form-label">备注</label>
						<view class="form-input-inline">
							<input type="text" v-model="screen.remark" placeholder="请输入备注" class="form-input" />
						</view>
					</view>
				</view>
				<view class="common-form-item">
					<view class="form-inline common-btn-wrap">
						<button type="default" class="screen-btn" @click="search()">筛选</button>
						<button type="default" @click="reset()">重置</button>
						<button type="default" class="screen-btn" @click="exportRecord()">导出</button>
					</view>
				</view>
			</view>

			<uni-data-table url="/store/storeapi/account/pages" :cols="cols" ref="table">
				<template v-slot:action="data">
					<view class="common-table-action" v-if="data.value.from_type == 'order' || data.value.from_type == 'refund'"><text @click="detail(data)">查看详情</text></view>
				</template>
			</uni-data-table>
		</view>
	</base-page>
</template>

<script>
	import {
		getAccountScreen,
		accountExport,
	} from '@/api/settlement.js';

	export default {
		data() {
			return {
				screen: {
					page: 1,
					start_date: '',
					end_date: '',
					from_type: '',
					remark: '',
				},
				userList: [],
				cols: [{
					width: 12,
					title: '来源方式',
					field: 'type_name',
					align: 'left'
				}, {
					width: 12,
					title: '记录金额',
					field: 'account_data',
					align: 'right'
				}, {
					width: 18,
					title: '发生时间',
					align: 'center',
					return: data => {
						return data.create_time ? this.$util.timeFormat(data.create_time) : '';
					}
				}, {
					width: 48,
					title: '备注',
					field: 'remark',
					align: 'left'
				},{
					width: 10,
					title: '操作',
					action: true, // 表格操作列
					align: 'right'
				}],
				fromType: [],
				withdrawDetail: null
			};
		},
		onLoad() {
			this.getScreenContent();
		},
		methods: {
			detail(data){
				if(window.location.origin.indexOf('localhost') != -1){
					window.open(window.location.origin+'/cashregister/pages/order/orderlist?order_no='+data.value.related_info.order_no+'&order_from='+data.value.related_info.order_from,'_blank');
				}else{
					window.open(this.$config.baseUrl+'/cashregister/pages/order/orderlist?order_no='+data.value.related_info.order_no+'&order_from='+data.value.related_info.order_from,'_blank');
				}
			},
			switchStoreAfter() {
				this.screen = {
					page: 1,
					start_date: '',
					end_date: ''
				};
				this.$refs.table.load();
			},
			search() {
				this.$refs.table.load(this.screen);
			},
			reset() {
				this.screen = {
					page: 1,
					start_date: '',
					end_date: '',
					from_type: '',
					remark: '',
				};
			},
			getScreenContent() {
				getAccountScreen().then(res => {
					if (res.code == 0) {
						this.fromType = Object.keys(res.data.from_type_list).map(index => {
							return {
								value: index,
								label: res.data.from_type_list[index].type_name
							};
						});
					}
				});
			},
			selectFromType(index) {
				this.screen.from_type = index == -1 ? '' : this.fromType[index].value;
				this.$forceUpdate();
			},
			backFn() {
				this.$util.redirectTo('/pages/store/settlement');
			},
			exportRecord() {
				accountExport(this.screen).then(res => {
					if (res.code == 0) {
						window.open(this.$util.img(res.data.path));
					}else{
						this.$util.showToast({
							title: res.message
						});
					}
				});
			},
		}
	};
</script>

<style lang="scss" scoped>
	.manage {
		position: relative;
		background-color: #fff;
		padding: 0.15rem;
		height: 100vh;
		box-sizing: border-box;
	}

	// 筛选面板
	.screen-warp {
		padding: 0.15rem;
		background-color: #f2f3f5;
		margin-bottom: 0.15rem;
		display: flex;
		justify-content: start;
		flex-direction: column;

		/deep/ .uni-date-x {
			height: 0.35rem;
		}

		/deep/ .uni-select-lay {
			background: #fff;

			.uni-select-lay-select {
				height: 0.37rem;
			}
		}

		.primary-btn {
			margin-left: 0;
		}

		&>* {
			margin-right: 0.15rem;
		}
		
		.common-btn-wrap button{
			margin-right: .1rem;
		}
	}
</style>
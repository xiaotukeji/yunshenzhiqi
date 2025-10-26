<template>
	<view class="ns-record">
		<view class="title">
			库存记录
			<text class="iconfont iconguanbi1" @click="close"></text>
		</view>
		<view class="table">
			<view class="table-th">
				<view class="table-td" style="width: 15%;">规格名称</view>
				<view class="table-td" style="width: 12%;">业务类型</view>
				<view class="table-td" style="width: 14%;">原始数量</view>
				<view class="table-td" style="width: 14%;">变动数量</view>
				<view class="table-td" style="width: 14%;">剩余数量</view>
				<view class="table-td" style="width: 14%;">入库单价</view>
				<view class="table-td" style="width: 17%;">创建时间</view>
			</view>
			<scroll-view scroll-y="true" class="table-tb">
				<view class="table-tr" v-for="(item, index) in list" :key="index">
					<view class="table-td" style="width: 15%;">{{ item.spec_name ? item.spec_name : item.goods_name }}</view>
					<view class="table-td" style="width: 12%;">{{ item.name }}</view>
					<view class="table-td" style="width: 14%;">{{ item.before_store_stock }}</view>
					<view class="table-td" style="width: 14%;">{{ item.goods_num }}</view>
					<view class="table-td" style="width: 14%;">{{ item.after_store_stock }}</view>
					<view class="table-td" style="width: 14%;">{{ item.goods_price }}</view>
					<view class="table-td" style="width: 17%;">{{ $util.timeFormat(item.create_time) }}</view>
				</view>
			</scroll-view>
		</view>
		<!-- 分页 -->
		<view class="pagination">
			<uni-pagination @change="changePage" :pageSize="page_size" show-icon="true" :total="total" :current="page"/>
		</view>
	</view>
</template>

<script>
import {getStockGoodsRecords} from '@/api/stock.js'

export default {
	data() {
		return {
			page: 1,
			page_size: 8,
			list: [],
			total: 0
		};
	},
	props: {
		goodsId: {
			type: Number,
			default: () => {
				return 0;
			}
		}
	},
	mounted() {
		this.getData();
	},
	methods: {
		// 分页发生变化
		changePage(e) {
			this.page = e.current;
			this.getData();
		},
		// 获取数据
		getData() {
			getStockGoodsRecords({
				page: this.page,
				page_size: this.page_size,
				goods_id: this.goodsId,
			}).then(res => {
				if (res.code >= 0) {
					this.total = res.data.count;
					this.list = res.data.list.map((item, index) => {
						let unit = '';
						if (item.type == 'input') {
							unit = '+';
						} else {
							unit = '-';
						}
						item.goods_num = unit + item.goods_num;
						return item;
					});
				}
			})
		},
		// 弹窗关闭
		close() {
			this.$emit('close');
		}
	}
};
</script>

<style lang="scss" scoped>
.ns-record {
	width: 100%;
	height: 100%;
	background: #ffffff;
	border-radius: 0.04rem;
	min-height: 2rem;
	padding-bottom: 0.4rem;
	.title {
		width: 100%;
		height: 0.5rem;
		border-bottom: 0.01rem solid #e6e6e6;
		font-size: 0.16rem;
		line-height: 0.5rem;
		text-align: center;
		position: relative;
		font-weight: bold;
		.iconguanbi1 {
			font-size: 0.2rem;
			position: absolute;
			top: 50%;
			right: 0.15rem;
			transform: translateY(-50%);
			font-weight: 500;
		}
	}
	.table {
		width: 100%;
		height: 4rem;
		padding: 0 0.15rem;
		box-sizing: border-box;
		.table-th {
			width: 100%;
			height: 0.5rem;
			display: flex;
			align-items: center;
			justify-content: space-between;
			background: #f7f8fa;
			padding: 0 0.15rem;
			box-sizing: border-box;
		}
		.table-tb {
			width: 100%;
			height: calc(100% - 0.5rem);
			.table-tr {
				display: flex;
				align-items: center;
				justify-content: space-between;
				width: 100%;
				height: 0.5rem;
				padding: 0 0.15rem;
				box-sizing: border-box;
				border-bottom: 0.01rem solid #e6e6e6;
			}
		}
	}
}

/deep/ .uni-date-single {
	height: 0.3rem;
}
.table-td {
	height: 100%;
	line-height: 0.5rem;
	text-overflow: ellipsis;
	overflow: hidden;
	white-space: nowrap;
	font-size: 0.14rem;
}
.pagination {
	width: 100%;
	margin-top: 0.2rem;
}
</style>

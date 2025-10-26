<template>
	<view>
		<view class="search-wrap">
			<view class="search-input-inner">
				<text class="search-input-icon iconfont iconsousuo" @click.stop="searchGoods()"></text>
				<input class="uni-input font-size-tag" maxlength="50" v-model="search_text" placeholder="请输入订单号" @confirm="searchGoods()" />
			</view>
			<picker @change="bindPickerChange" :value="indexse" :range="array">
				<view class="select color-tip">{{array[indexse]}}<text class="iconfont iconiconangledown" style="transform: scale(1.8);"></text></view>
			</picker>
		</view>
		<view class="tab-block">
			<view class="tab-wrap">
				<block v-for="(item, index) in statusList" :key="index">
					<view class="tab-item" @click.stop="tabChange(item.id)" :class="item.id == status ? 'active color-base-text color-base-bg-before' : ''">{{ item.name }}</view>
				</block>
			</view>
		</view>
		<mescroll-uni @getData="getList" top="200" ref="mescroll" >
			<block slot="list">
				<block v-if="dashboard_list.length > 0">
					<view class="goods-class" v-for="(item, index) in dashboard_list" :key="index">
						<view class="goods-item">
							<view class="goods-item-title">
								<view class="title-ordernum">订单编号：{{item.order_no}}</view>
								<view :class="item.invoice_status == 0 ? 'title-orderactive' : 'title-ordertext'">{{item.invoice_status == 0 ? '未开票' : '已开票'}}</view>
							</view>
							<view class="goods-item-content">
								<view class="content-left">订单总额</view>
								<view class="content-right">{{item.order_money}}元</view>
							</view>
							<view class="goods-item-content" style="align-items: center;">
								<view class="content-left">发票金额</view>
								<view class="content-right">{{item.invoice_money}}元</view>
								<view class="content-last" v-if="item.invoice_delivery_money > 0">发票邮寄费用：{{item.invoice_delivery_money}}</view>
							</view>
							<view class="goods-item-content">
								<view class="content-left">发票类型</view>
								<view class="content-right">{{item.order_type_name}}</view>
							</view>
							<view class="goods-item-content">
								<view class="content-left">发票抬头</view>
								<view class="content-right">发票抬头：{{item.invoice_title}}<br/>
									抬头类型：{{item.invoice_title_type == 1 ? '个人' : '企业'}}<br/>
									发票内容：{{item.invoice_content}}</view>
							</view>
							<view class="goods-item-content">
								<view class="content-left">发票税率(%)</view>
								<view class="content-right">{{item.invoice_rate}}</view>
							</view>
							<view class="goods-item-content">
								<view class="content-left">订单状态</view>
								<view class="content-right">{{item.order_status_name}}</view>
							</view>
							<view class="goods-item-content">
								<view class="content-left">下单时间</view>
								<view class="content-right">{{$util.timeStampTurnTime(item.create_time)}}</view>
							</view>
							<view class="goods-item-content" v-if="item.invoice_time">
								<view class="content-left">开票时间</view>
								<view class="content-right">{{$util.timeStampTurnTime(item.invoice_time)}}</view>
							</view>
							<view class="goods-btn">
								<button type="default" size="mini" class="goods-btn-search" @click="onDetail(item.order_id)">查看订单</button>
								<button v-if="item.invoice_status == 0" type="default" size="mini" class="goods-btn-item" @click="onEdit(item.order_id)">开票</button>
							</view>
						</view>
					</view>
				</block>
				<ns-empty v-if="!dashboard_list.length" text="暂无商品数据"></ns-empty>
			</block>
		</mescroll-uni>
	</view>
</template>

<script>
	import {getOrderInvoicelist} from '@/api/order'
	export default {
		data() {
			return {
				search_text: '',
				array: ['全部','普通订单', '自提订单', '外卖订单', '虚拟订单'],
				statusList: [
					{
						id: 0,
						name: '全部',
						invoice_status: '',
					},
					{
						id: 1,
						name: '未开票',
						invoice_status: 0,
					},
					{
						id: 2,
						name: '已开票',
						invoice_status: 1,
					}
				],
				status: 0,
				indexse: 0,
				orderType: '',
				dashboard_list: []
			}
		},
		onShow(){
			if (this.mescroll) this.$refs.mescroll.refresh();
		},
		methods: {
			searchGoods(){
				this.$refs.mescroll.refresh();
			},
			tabChange(e){
				this.status = e
				this.$refs.mescroll.refresh();
			},
			onDetail(e){
				this.$util.redirectTo('/pages/order/detail/basis', {order_id: e})
			},
			onEdit(e){
				this.$util.redirectTo('/pages/invoices/edit/edit', {order_id: e})
			},
			bindPickerChange(e){
				this.indexse = e.detail.value
				if(e.detail.value == 0){
					this.orderType = ''
				}else{
					this.orderType = e.detail.value
				}
				this.$refs.mescroll.refresh();
			},
			getList(mescroll) {
				var data = {
					page: mescroll.num,
					page_size: mescroll.size,
					invoice_status: this.statusList[this.status].invoice_status,
					search_text: this.search_text,
					order_type: this.orderType
				};
				getOrderInvoicelist(data).then(res=>{
					let newArr = [];
					let msg = res.message;
					if (res.code == 0 && res.data) {
						if (res.data.page_count == 0) {
							this.emptyShow = true;
						}
						newArr = res.data.list;
					} else {
						this.$util.showToast({
							title: msg
						});
					}
					mescroll.endSuccess(newArr.length);
					//设置列表数据
					if (mescroll.num == 1) this.dashboard_list = []; //如果是第一页需手动制空列表
					this.dashboard_list = this.dashboard_list.concat(newArr); //追加新数据
					if (this.$refs.loadingCover) this.$refs.loadingCover.hide();
				});
			}
		}
	}
</script>

<style lang="scss">
.search-wrap {
	display: flex;
	justify-content: space-between;
	padding: $margin-both;
	background-color: #fff;
	.search-input-inner {
		display: flex;
		align-items: center;
		width: 460rpx;
		height: 70rpx;
		padding: 0 30rpx;
		background-color: #F8F8F8;
		border-radius: 100rpx;
		box-sizing: border-box;
		.search-input-icon {
			margin-right: 10rpx;
			color: $color-tip;
		}
	}
	.search-btn {
		display: flex;
		justify-content: center;
		align-items: center;
		width: 200rpx;
		height: 70rpx;
		color: #fff;
		border-radius: 100rpx;
		text {
			margin-right: 10rpx;
		}
	}
}
.search {
	display: flex;
	padding: 0 30rpx 30rpx;
	justify-content: center;
	text-align: center;
	.search_input {
		padding: 0 20rpx;
		background-color: #fff;
		flex: 1;
		height: 70rpx;
		line-height: 70rpx;
		border-radius: 70rpx;
		display: flex;
		align-items: center;
		input {
			height: 70rpx;
			line-height: 70rpx;
			border-radius: 70rpx;
			padding: 0 $padding;
		}
		.date {
			display: flex;
			align-items: center;
			flex: 1;
			color: $color-tip;
			picker {
				flex: 1;
				&.start{
					margin-right: 20rpx !important;
				}
				&.end{
					margin-left: 20rpx !important;
				}
			}
			.clear{
				min-width: 60rpx;
			}
		}
		
		.search_btn {
			min-width: 60rpx;
		}
		.placeholder {
			font-size: $font-size-base;
			color: $color-tip;
		}
	}
	.search_select {
		background-color: #fff;
		height: 70rpx;
		line-height: 70rpx;
		border-radius: 70rpx;
		width: 160rpx;
		color: $color-tip;
		font-size: $font-size-tag;
		display: flex;
		align-items: center;
		justify-content: center;
		> text {
			margin-left: 10rpx;
		}
	}
}
.select {
	height: 68rpx;
	line-height: 68rpx;
	border-radius: 35rpx;
	min-width: 200rpx;
	// max-width: 200rpx;
	margin-left: 30rpx;
	padding:0 20rpx;
	text-align: center;
	background: #fff;
	border: 1px solid #ccc;
	display: flex;
	justify-content: space-between;
	align-items: center;
	font-size: 28rpx;

	text {
		vertical-align: middle;
		font-size: 28rpx;
	}
}
.tab-block {
	display: flex;
	flex-direction: row;
	justify-content: space-between; 
	background: #fff;
	.choose {
		min-width: 50px;
		background-color: #fff;
		padding: 20rpx 0rpx 0 20rpx;
		height: 66rpx;
	}
	.tab-wrap {
		width: 100%;
		height: 66rpx;
		background-color: #fff;
		display: flex;
		flex-direction: row;
		justify-content: space-around;
	}
	.active {
		position: relative;
		&::after {
			content: '';
			position: absolute;
			bottom: 0;
			left: 0;
			height: 4rpx;
			width: 100%;
		}
	}
}
.goods-class {
	margin:0 30rpx;
}
.goods-item {
	background: #FFFFFF;
	border-radius: 10rpx;
	margin-top: 20rpx;
	padding:30rpx 30rpx 40rpx;
	
	.goods-item-title {
		display: flex;
		flex-direction: row;
		justify-content: space-between;
		align-items: center;
		height: 70rpx;
		border-bottom: 1px solid #eee;
		
		.title-ordernum {
			font-size: 24rpx;
			font-family: PingFang SC;
			font-weight: 500;
			color: #909399;
		}
		.title-ordertext {
			font-size: 24rpx;
			font-family: PingFang SC;
			font-weight: 500;
			color: #4456FF;
		}
		.title-orderactive {
			font-size: 24rpx;
			font-family: PingFang SC;
			font-weight: 500;
			color: #FF4544;
		}
	}
	.goods-item-content {
		display: flex;
		flex-direction: row;
		padding-top: 10rpx;
		// margin-top:10rpx;
		.content-left {
			font-size: 26rpx;
			font-family: PingFang SC;
			font-weight: 500;
			color: #303133;
			min-width: 160rpx;
		}
		.content-right {
			font-size: 26rpx;
			font-family: PingFang SC;
			font-weight: 500;
			color: #303133;
			margin-left: 80rpx;
		}
		.content-last {
			font-size: 24rpx;
			font-family: PingFang SC;
			font-weight: 500;
			color: #FF6A00;
			margin-left: 30rpx;
		}
	}
	.goods-btn {
		display: flex;
		flex-direction: row-reverse;
		margin-top: 25rpx;
		.goods-btn-search {
			// padding:0 20rpx;
			color: #303133;
			border-color: #909399;
			margin-left: 20rpx !important;
		}
		.goods-btn-item {
			color: #FF6A00;
			border-color: #FF6A00;
		}
	}
}
</style>

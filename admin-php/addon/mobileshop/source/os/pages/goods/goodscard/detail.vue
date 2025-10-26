<template>
	<view>
		<block v-if="detail">
			<view class="tab-block">
				<view class="tab-wrap">
					<block v-for="(item, index) in list" :key="index">
						<view class="tab-item" @click.stop="changeAct(item)" :class="index == act ? 'active color-base-text color-base-bg-before' : ''">{{ item.name }}</view>
					</block>
				</view>
			</view>
			
			<view class="content contentbox" v-if="act == 0">
				<view class="order-list">
					<view class="list-left">卡项名称</view>
					<view class="list-right">
						<view class="content-text">{{ detail.goods_name }}</view>
					</view>
				</view>
				<view class="order-list">
					<view class="list-left">卡类型</view>
					<view class="list-right ">
						<view class="content-text">{{ detail.card_type_name }}</view>
					</view>
				</view>
				<view class="order-list">
					<view class="list-left">价格</view>
					<view class="list-right ">
						<view class="content-text">￥{{ detail.price }}</view>
					</view>
				</view>
				<view class="order-list">
					<view class="list-left">所属会员</view>
					<view class="list-right ">
						<view class="content-text">{{ detail.nickname }}</view>
					</view>
				</view>
				<view class="order-list">
					<view class="list-left">总次数/已使用</view>
					<view class="list-right ">
						<view class="content-text">{{ detail.total_num ? detail.total_num + '次' : '不限次数' }}/{{ detail.total_use_num }}次</view>
					</view>
				</view>
				<view class="order-list">
					<view class="list-left">获取时间</view>
					<view class="list-right ">
						<view class="content-text">{{ $util.timeStampTurnTime(detail.create_time) }}</view>
					</view>
				</view>
				<view class="order-list">
					<view class="list-left">到期时间</view>
					<view class="list-right ">
						<view class="content-text">{{ detail.end_time ? $util.timeStampTurnTime(detail.end_time) : '长期有效' }}</view>
					</view>
				</view>
			</view>
			<block v-if="act == 1">
				<view class="item-wrap" v-for="(item, index) in detail.item_list" :key="index">
					<view class="info">
						<text class="title">商品名称：</text>
						<view class="info-content">{{ item.sku_name }}</view>
					</view>
					<view class="info">
						<text class="title">总次数：</text>
						<view class="info-content">{{ item.use_num ? item.use_num : '不限次数' }}</view>
					</view>
					<view class="info">
						<text class="title">已使用：</text>
						<view class="info-content">{{ item.use_num }}</view>
					</view>
				</view>
			</block>
			<block v-if="act == 2">
				<mescroll-uni @getData="getListData" refs="mescroll" top="90rpx" :size="10">
					<block slot="list">
						<block v-if="dataList.length">
							<view class="item-wrap" v-for="(item, index) in dataList" :key="index">
								<view class="info">
									<text class="title">卡项名称：</text>
									<view class="info-content">{{ item.sku_name }}</view>
								</view>
								<view class="info">
									<text class="title">使用门店：</text>
									<view class="info-content">{{ item.store_name }}</view>
								</view>
								<view class="info">
									<text class="title">使用次数：</text>
									<view class="info-content">{{ item.num }}</view>
								</view>
								<view class="info">
									<text class="title">使用时间：</text>
									<view class="info-content">{{ $util.timeStampTurnTime(item.create_time) }}</view>
								</view>
								<view class="operation">
									<view class="color-base-text"  @click="order(item)">查看订单</view>
								</view>
							</view>
						</block>
						<ns-empty v-else text="暂无使用记录"></ns-empty>
					</block>
				</mescroll-uni>
			</block>
		</block>
		<ns-empty v-else></ns-empty>
		<loading-cover ref="loadingCover"></loading-cover>
	</view>
</template>

<script>
	import {getGoodsCardInfoById,getGoodsCardUsageRecords} from '@/api/goods_card'
	export default {
		data() {
			return {
				act: 0,
				list: [
					{
						id: 0,
						name: '基本信息'
					},
					{
						id: 1,
						name: '商品信息'
					},
					{
						id: 2,
						name: '使用记录'
					}
				],
				cardId: 0,
				detail: null,
				dataList: []
			}
		},
		onLoad(data) {
			this.cardId = data.card_id || 0;
			this.getDetail()
		},
		methods: {
			changeAct(item) {
				// 激活样式是当前点击的对应下标--list中对应id
				this.act = item.id;
			},
			getDetail() {
				getGoodsCardInfoById(this.cardId).then(res => {
					if (res.code == 0 && res.data) {
						this.detail = res.data;
					}
					if (this.$refs.loadingCover) this.$refs.loadingCover.hide();
				});
			},
			getListData(mescroll) {
				let data = {
					page_size: mescroll.size,
					page: mescroll.num,
					card_id: this.cardId
				};
				this.mescroll = mescroll;
				getGoodsCardUsageRecords(data).then(res=>{
					let newArr = [];
					let msg = res.message;
					if (res.code == 0 && res.data) {
						newArr = res.data.list;
					} else {
						this.$util.showToast({ title: msg });
					}
					mescroll.endSuccess(newArr.length);
					//设置列表数据
					if (mescroll.num == 1) this.dataList = []; //如果是第一页需手动制空列表
					this.dataList = this.dataList.concat(newArr); //追加新数据
					if (this.$refs.loadingCover) this.$refs.loadingCover.hide();
				});
			},
			order(data){
				this.$util.redirectTo("/pages/order/detail/basis", {
					order_id: data.order_id,
					template: 'basis'
				});
			}
		}
	}
</script>

<style lang="scss">
.tab-block {
	display: flex;
	flex-direction: row;
	justify-content: space-between;
	background: #fff;

	.tab-wrap {
		width: 100%;
		height: 90rpx;
		background-color: #fff;
		display: flex;
		flex-direction: row;
		justify-content: space-around;
	}

	.tab-item {
		line-height: 90rpx;
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

.content {
	margin-top: 20rpx;
	background: #fff;
	padding: 0 30rpx;
}

.order-list {
	display: flex;
	flex-direction: row;
	justify-content: space-between;
	align-items: center;
	border-bottom: 1px solid #eee;
	padding: 20rpx 0;
	
	&:last-child{
		border-bottom: 0;
	}

	.list-right {
		display: flex;
		flex-direction: row;
		align-items: center;
		font-size: 28rpx;
		font-family: PingFang SC;
		font-weight: 500;
		color: #303133;
		flex: 1;
		width: 0;
		padding-left: 20rpx;

		.content-text {
			width: 100%;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
			text-align: right;
		}
	}

	.list-left {
		font-size: 28rpx;
		font-family: PingFang SC;
		font-weight: 500;
		color: #303133;
	}
}

.item-wrap {
	margin: 30rpx 20rpx;
	background-color: #fff;
	border-radius: $border-radius;
	padding: 30rpx;
		
	.info {
		display: flex;
		align-items: center;
		color: #999;
		line-height: 1.6;
		font-size: 26rpx;
		
		text {
			font-size: 26rpx;
		}
		
		.info-content {
			padding-left: 20rpx;
			flex: 1;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
			font-size: 26rpx;
			line-height: 1.6;
		}
	}
}
</style>

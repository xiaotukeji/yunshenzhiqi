<template>
	<view class="container">
		<view class="search-inner">
			<view class="search-wrap">
				<text class="search-input-icon iconfont iconsousuo" @click.stop="search()"></text>
				<input class="uni-input font-size-tag" maxlength="50" v-model="searchText" placeholder="请输入购买人昵称" @confirm="search()" />
			</view>
		</view>
		<mescroll-uni class="list-wrap" @getData="getListData" top="160" refs="mescroll" :size="10">
			<block slot="list">
				<view class="item-wrap" v-for="(item, index) in dataList" :key="index" @click="showHide(index)">
					<view class="headimg">
						<image mode="widthFix" :src="item.headimg == '' ? $util.img($util.getDefaultImage().default_headimg) : $util.img(item.headimg)" @error="imgError(index)"/>
					</view>
					<view class="info-wrap">
						<view class="info">购买人：<text>{{ item.nickname }}</text></view>
						<view class="info">总次数/已使用：<text>{{ item.total_num ? item.total_num + '次' : '不限次数' }}</text>/<text>{{ item.total_use_num }}次</text></view>
						<view class="info">获取时间：<text>{{ $util.timeStampTurnTime(item.create_time) }}</text></view>
						<view class="info">到期时间：<text>{{ item.end_time ? $util.timeStampTurnTime(item.end_time) : '长期有效' }}</text></view>
					</view>
					
					<view class="operation" :class="{show: operation == index}" @click.stop="showHide(index)">
						<view class="operation-item" @click.stop="detail(item)">
							<image :src="$util.img('public/uniapp/shop_uniapp/member/member_01.png')" mode=""></image>
							<text>查看详情</text>
						</view>
						<view class="operation-item" @click.stop="order(item)">
							<image :src="$util.img('public/uniapp/shop_uniapp/member/member_01.png')" mode=""></image>
							<text>查看订单</text>
						</view>
					</view>
				</view>
				<ns-empty v-if="!dataList.length" text="暂无会员数据"></ns-empty>
			</block>
		</mescroll-uni>
		<loading-cover ref="loadingCover"></loading-cover>
	</view> 
</template>

<script>
	import {getGoodsCardList} from '@/api/goods_card'
	export default {
		data() {
			return {
				searchText: '',
				dataList: [],
				goodsId: 0,
				operation: -1
			}
		},
		onLoad(data) {
			this.goodsId = data.goods_id || 0;
		},
		methods: {
			getListData(mescroll) {
				let data = {
					page_size: mescroll.size,
					page: mescroll.num,
					goods_id: this.goodsId,
					search_text: this.searchText
				};
				Object.assign(data, this.formData)
				this.mescroll = mescroll;
				getGoodsCardList(data).then(res=>{
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
			search() {
				this.mescroll.resetUpScroll();
			},
			showHide(index){
				this.operation = this.operation == index ? -1 : index;
			},
			detail(data){
				this.$util.redirectTo('/pages/goods/goodscard/detail', {card_id: data.card_id})
				this.operation = -1;
			},
			order(data){
				this.$util.redirectTo("/pages/order/detail/virtual", {
					order_id: data.order_id,
					template: 'virtual'
				});
				this.operation = -1;
			}
		}
	}
</script>

<style lang="scss">
.search-inner {
	padding: 30rpx;
	background-color: #fff;
	display: flex;
	align-items: center;
	.screen {
		padding-left: 20rpx;
		
		text {
			font-size: 50rpx;
			line-height: 1;
			display: inline-block;
			transform: translateY(-10rpx);
		}
	}
	.search-wrap {
		flex: 1;
		display: flex;
		align-items: center;
		padding: 0 30rpx;
		height: 70rpx;
		background-color: $color-bg;
		border-radius: 100rpx;
		.search-input-icon {
			margin-right: 20rpx;
			color: $color-tip;
		}
		input {
			flex: 1;
		}
	}
}

.item-wrap {
	margin: 0 30rpx 20rpx;
	background-color: #fff;
	border-radius: $border-radius;
	padding: 30rpx;
	display: flex;
	position: relative;
	
	.headimg {
		margin-right: 20rpx;
		width: 120rpx;
		height: 120rpx;
		border-radius: 50%;
		overflow: hidden;
		display: flex;
		align-items: center;
		justify-items: center;
		
		image {
			width: 100%;
		}
	}
	
	.info-wrap {
		flex: 1;
		width: 0;
		
		.info {
			color: #999;
			line-height: 1.6;
			font-size: 26rpx;
			
			text {
				font-size: 26rpx;
			}
		}
	}
	
	.operation {
		overflow: hidden;
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background-color: rgba(0, 0, 0, 0.6);
		display: none;
		justify-content: space-around;
		align-items: center;
		border-radius: 10rpx;
		
		&.show {
			display: flex;
		}
		.operation-item {
			display: flex;
			flex-direction: column;
			align-items: center;
			image {
				width: 64rpx;
				height: 64rpx;
			}
			text {
				margin-top: 20rpx;
				font-size: $font-size-tag;
				line-height: 1;
				color: #fff;
			}
		}
	}
}
</style>

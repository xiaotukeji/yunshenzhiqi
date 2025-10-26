<template>
	<view>
		<block v-if="detail">
			<view class="search-inner">
				<view class="search-wrap">
					<text class="search-input-icon iconfont iconsousuo" @click.stop="search()"></text>
					<input class="uni-input font-size-tag" maxlength="50" v-model="searchText" placeholder="请输入购买人昵称/核销码" @confirm="search()" />
				</view>
			</view>
			<view class="tab-block">
				<view class="tab-wrap">
					<block v-for="(item, index) in list" :key="index">
						<view class="tab-item" @click.stop="changeAct(item)" :class="index == act ? 'active color-base-text color-base-bg-before' : ''">{{ item.name }}</view>
					</block>
				</view>
			</view>
			<block v-if="act == 0">
				<mescroll-uni @getData="getListData" refs="mescroll" top="210rpx" :size="10">
					<block slot="list">
						<block v-if="dataList.length">
							<view class="item-wrap" v-for="(item, index) in dataList" :key="index">
								<view class="info" @click="$util.redirectTo('/pages/member/detail', { member_id: item.member_id })">
									<text class="title">买家信息：</text>
									<view class="info-content member-info color-base-text">
										<view class="headimg">
											<image mode="widthFix" :src="item.headimg == '' ? $util.img($util.getDefaultImage().default_headimg) : $util.img(item.headimg)" @error="imgError(index)"/>
										</view>
										{{ item.nickname }}
									</view>
								</view>
								<view class="info">
									<text class="title">核销码：</text>
									<view class="info-content">{{ item.code }}</view>
								</view>
								<view class="info">
									<text class="title">已核销/可核销：</text>
									<view class="info-content">{{ item.verify_total_count ? item.verify_total_count + '次' : '不限次数' }}/{{ item.verify_use_num }}次</view>
								</view>
								<view class="info">
									<text class="title">售出时间：</text>
									<view class="info-content">{{ $util.timeStampTurnTime(item.sold_time) }}</view>
								</view>
								<view class="info">
									<text class="title">过期时间：</text>
									<view class="info-content">{{ item.expire_time ? $util.timeStampTurnTime(item.expire_time) : '长期有效' }}</view>
								</view>
								<view class="info">
									<text class="title">是否已核销：</text>
									<view class="info-content">{{ item.is_veirfy ? '已核销' : '未核销' }}</view>
								</view>
								<!-- 	<view class="operation">
									<view class="color-base-text"  @click="order(item)">去核销</view>
								</view> -->
							</view>
						</block>
						<ns-empty v-else text="暂无核销码"></ns-empty>
					</block>
				</mescroll-uni>
			</block>
			<view v-if="act == 1" class="count-wrap">
				<view class="count-item">
					<view class="wrap">
						<view class="title">核销码总数（个）</view>
						<view class="value">{{ detail.total_count || 0 }}</view>
					</view>
				</view>
				<view class="count-item">
					<view class="wrap">
						<view class="title">已核销（次）</view>
						<view class="value">{{ detail.verify_use_num || 0 }}</view>
					</view>
				</view>
			</view>
		</block>
		<ns-empty v-else></ns-empty>
		<loading-cover ref="loadingCover"></loading-cover>
	</view>
</template>

<script>
import {getGoodsVerifyById,getGoodsVerifyListById} from '@/api/verify'
export default {
	data() {
		return {
			act: 0,
			list: [
				{
					id: 0,
					name: '核销码'
				},
				{
					id: 1,
					name: '核销码统计'
				}
			],
			dataList: [],
			detail: null,
			searchText: ''
		};
	},
	onLoad(data) {
		this.goodsId = data.goods_id || 0;
		this.getDetail();
	},
	methods: {
		changeAct(item) {
			// 激活样式是当前点击的对应下标--list中对应id
			this.act = item.id;
		},
		getDetail() {
			getGoodsVerifyById(this.goodsId).then(res=>{
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
				goods_id: this.goodsId,
				search_text: this.searchText
			};
			this.mescroll = mescroll;
			getGoodsVerifyListById(data).then(res=>{
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
		}
	}
};
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

.count-wrap {
	.count-item {
		display: inline-block;
		width: calc((100% - 60rpx) / 2);
		border-radius: 10rpx;
		background: #fff;
		margin: 20rpx 0 20rpx 20rpx;
		padding-bottom: 40%;
		position: relative;

		.wrap {
			position: absolute;
			width: 100%;
			height: 100%;
			left: 0;
			top: 0;
			z-index: 5;
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
		}

		.title {
			color: #999;
		}

		.value {
			font-size: 50rpx;
			font-weight: bolder;
		}
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

		.member-info {
			display: flex;
			align-items: center;

			.headimg {
				margin-right: 20rpx;
				width: 60rpx;
				height: 60rpx;
				border-radius: 50%;
				overflow: hidden;
				display: flex;
				align-items: center;
				justify-items: center;

				image {
					width: 100%;
				}
			}
		}
	}
}
</style>

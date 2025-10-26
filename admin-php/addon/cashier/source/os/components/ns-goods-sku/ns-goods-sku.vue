<template>
	<view class="ns-record">
		<view class="title">
			价格库存
			<text class="iconfont iconguanbi1" @click="close"></text>
		</view>
		<view class="table">
			<view class="table-th">
				<view class="table-td" style="min-width: 35%;max-width: 35%;">规格名称</view>
				<view class="table-td">统一售价</view>
				<view class="table-td" v-if="isUnifyPrice == 0">独立售价</view>
				<view class="table-td">库存</view>
				<view class="table-td" v-if="disabled">限制起送</view>
			</view>
			<scroll-view scroll-y="true" class="table-tb">
				<view class="table-tr" v-for="(item, index) in skuList" :key="index">
					<view class="table-td" style="min-width: 35%;max-width: 35%;">
						{{ item.spec_name ? item.spec_name : item.goods_name }}</view>
					<view class="table-td">{{ item.discount_price }}</view>
					<view class="table-td input-wrap" v-if="isUnifyPrice == 0">
						<input class="input" v-model="item.store_price" type="digit" />
						<view class="unit">元</view>
					</view>
					<view class="table-td input-wrap" v-if="!disabled">
						<block v-if="globalStoreInfo.stock_type == 'store'">
							<input class="input" v-model="item.stock" type="digit" :disabled="disabled" />
							<view class="unit">{{ item.unit ? item.unit : '件' }}</view>
						</block>
						<view v-else>{{ item.stock }}</view>
					</view>
					<view class="table-td input-wrap" v-else>
						<view>{{ item.stock }}</view>
					</view>
					<view class="table-td input-wrap" v-if="disabled">
						<switch @change="changeswitch($event,index)" color="#FA6400" style="transform:scale(0.7)"
							:checked="item.is_delivery_restrictions == 1" />
					</view>
				</view>
			</scroll-view>

		</view>
		<view class="pop-bottom"><button class="primary-btn" @click="save()">确定</button></view>
	</view>
</template>

<script>
	import {
		editGoods,
		setGoodsLocalRestrictions
	} from '@/api/goods.js';

	export default {
		data() {
			return {
				goodsSkuList: [],
				flag: true
			};
		},
		props: {
			skuList: {
				type: Array,
				default: () => {
					return [];
				}
			},
			isUnifyPrice: {
				type: Number,
				default: () => {
					return 0;
				}
			},
			disabled: {
				type: Boolean,
				default: () => {
					return false;
				}
			},
		},
		created() {},
		methods: {
			changeswitch(e, index) {
				this.skuList[index].is_delivery_restrictions = e.detail.value ? 1 : 0
			},
			getGoodsSku() {
				this.goodsSkuList = [];
				Object.keys(this.skuList).forEach(key => {
					let data = this.skuList[key];
					let obj = {}
					if (this.disabled) {
						obj.sku_id = data.sku_id
						obj.is_delivery_restrictions = data.is_delivery_restrictions
					} else {
						obj.sku_id = data.sku_id
						obj.price = data.store_price ? data.store_price : data.discount_price
					}
					if (this.globalStoreInfo.stock_type == 'store') {
						obj.stock = data.stock;
					}
					this.goodsSkuList.push(obj);
				});
			},
			save() {
				this.getGoodsSku();
				if (!this.flag) return false;
				this.flag = false;
				uni.showLoading({
					title: '请求处理中'
				});
				if (this.disabled) {
					setGoodsLocalRestrictions({
						goods_sku_list: JSON.stringify(this.goodsSkuList)
					}).then(res => {
						uni.hideLoading();
						this.$util.showToast({
							title: res.message
						});
						if (res.code >= 0) {
							this.$root.$refs.goodsListTable.load();
							this.close();
						} else {
							this.flag = true;
						}
					})
				} else {
					editGoods({
						goods_sku_list: JSON.stringify(this.goodsSkuList)
					}).then(res => {
						uni.hideLoading();
						this.$util.showToast({
							title: res.message
						});
						if (res.code >= 0) {
							this.$root.$refs.goodsListTable.load();
							this.close();
						} else {
							this.flag = true;
						}
					})
				}
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
			padding: 0.15rem;
			box-sizing: border-box;

			.table-th {
				width: 100%;
				height: 0.5rem;
				display: flex;
				align-items: center;
				justify-content: space-between;
				background: #f7f8fa;

				box-sizing: border-box;
			}

			.table-td {
				display: flex;
				flex: 1;
				padding: 0 0.15rem;
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
					box-sizing: border-box;
					border-bottom: 0.01rem solid #e6e6e6;
				}
			}
		}
	}

	/deep/ .uni-date-single {
		height: 0.3rem;
	}

	.input-wrap {
		height: 100%;
		line-height: 0.5rem;
		text-overflow: ellipsis;
		overflow: hidden;
		white-space: nowrap;
		font-size: 0.14rem;
		display: flex;
		align-items: center;

		.input {
			width: 0.7rem;
			height: 0.35rem;
			border: 0.01rem solid #e6e6e6;
			padding: 0 0.1rem;
			border-top-left-radius: 0.03rem;
			border-bottom-left-radius: 0.03rem;
		}

		.unit {
			background-color: #eee;
			height: 0.35rem;
			width: 0.35rem;
			text-align: center;
			line-height: 0.35rem;
			border: 0.01rem solid #e6e6e6;
			border-left: 0;
			border-top-right-radius: 0.03rem;
			border-bottom-right-radius: 0.03rem;
		}
	}

	.save {
		height: 0.3rem;
		line-height: 0.3rem;
	}

	.pop-bottom {
		padding: 0.1rem 0.2rem;
		border-top: 0.01rem solid #eee;

		button {
			width: 100%;
			line-height: 0.35rem;
			height: 0.35rem;
		}
	}
</style>
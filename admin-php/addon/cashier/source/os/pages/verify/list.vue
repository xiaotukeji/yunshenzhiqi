<template>
	<base-page>
		<view class="uni-flex uni-row check-wrap">
			<view class="check-head">
				<text>核销列表</text>
				<text>核销详情</text>
			</view>
			<view class="check-content">
				<view class="left-wrap-content">
					<view class="wrap-search-box">
						<view class="wrap-search">
							<input placeholder="输入核销码" v-model="searchText" @input="search()" placeholder-style="font-size:0.14rem" />
							<text class="iconfont icon31sousuo" @click="search()"></text>
						</view>
					</view>
					<block v-if="list.length > 0">
						<scroll-view scroll-y="true" class="check-list all-scroll" @scrolltolower="getRecordList">
							<view class="item" v-for="(item, index) in list" :key="index" @click="tableDataFn(item)" :class="current_id == item.id ? 'item-hover' : ''">
								<view class="item-box">
									<view class="head">
										<view class="nick-name">核销码：{{ item.verify_code }}</view>
										<view class="time">核销时间：{{ $util.timeFormat(item.verify_time, 'Y-m-d H:i') }}</view>
									</view>
									<view class="body">
										<text>{{ item.verifier_name }}</text>
									</view>
								</view>
							</view>
						</scroll-view>
					</block>
					<view class="not-record" v-else>暂无数据</view>
				</view>
				<view class="check-detail text">
					<view class="form-content" v-if="detailInfo && Object.keys(detailInfo).length">
						<view class="verify-item" v-for="(item, index) in detailInfo.verify_content_json.item_array" :key="index">
							<view class="item-img">
								<image :src="$util.img(item.img.split(',')[0], { size: 'small' })" mode="aspectFit"/>
							</view>
							<view class="item-info">
								<view>{{ item.name }}</view>
							</view>
						</view>

						<view class="form-item">
							<view class="form-label">核销码：</view>
							<view class="form-inline">
								<text>{{ detailInfo.verify_code }}</text>
							</view>
						</view>

						<view class="form-item">
							<view class="form-label">核销类型：</view>
							<view class="form-inline">{{ detailInfo.verify_type_name }}核销</view>
						</view>

						<view class="form-item">
							<view class="form-label">核销员：</view>
							<view class="form-inline">{{ detailInfo.verifier_name }}</view>
						</view>

						<view class="form-item">
							<view class="form-label">核销次数：</view>
							<view class="form-inline">{{ detailInfo.verify_num }}</view>
						</view>

						<view class="form-item">
							<view class="form-label">核销时间：</view>
							<view class="form-inline">{{ $util.timeFormat(detailInfo.verify_time, 'Y-m-d H:i') }}</view>
						</view>

						<view class="form-item" v-if="detailInfo.member_id > 0">
							<view class="form-label">所属会员：</view>
							<view class="form-inline">{{ detailInfo.nickname }}</view>
						</view>
						<view class="form-item" v-if="detailInfo.member_id > 0">
							<view class="form-label">手机号：</view>
							<view class="form-inline">{{ detailInfo.mobile ? detailInfo.mobile : '--' }}</view>
						</view>
					</view>
					<block v-else>
						<image class="detail-empty" :src="$util.img('@/static/goods/goods_empty.png')" mode="widthFix"/>
					</block>
				</view>
			</view>
		</view>
	</base-page>
</template>

<script>
	import {
		getVerifyRecordList,
		getVerifyRecordDetail
	} from '@/api/verify.js';

	export default {
		data() {
			return {
				detailInfo: null,
				// 初始是请求第几页
				page: 1,
				// 每次返回数据数
				page_size: 20,
				list: [],
				current_id: 0,
				searchText: ''
			};
		},
		onLoad() {
			this.getRecordList();
		},
		methods: {
			search() {
				this.page = 1;
				this.list = [];
				this.getRecordList();
			},
			// 查询核销记录
			getRecordList() {
				getVerifyRecordList({
					page: this.page,
					page_size: this.page_size,
					search_text: this.searchText
				}).then(res => {
					if (res.data.list.length == 0) {
						this.detailInfo = {};
						this.$forceUpdate();
					}
					if (res.code >= 0 && res.data.list.length != 0) {
						this.page += 1;
						this.list = this.list.concat(res.data.list);
					}

					if (this.list.length) {
						this.tableDataFn(this.list[0]);
					}
				})
			},
			tableDataFn(e) {
				this.current_id = e.id;
				this.getInfo(e.id);
			},
			getInfo(id) {
				getVerifyRecordDetail(id).then(res => {
					if (res.code >= 0) {
						this.detailInfo = null;
						this.detailInfo = res.data;
					} else {
						this.$util.showToast({
							title: res.message
						});
					}
				});
			}
		}
	};
</script>

<style lang="scss" scoped>
	.check-wrap {
		flex-direction: column;
		background-color: #fff;
		min-height: 100%;

		.check-head {
			display: flex;
			justify-content: space-around;
			line-height: 0.6rem;
			font-weight: 500;
			height: 0.6rem;
			border-top: 0.01rem solid #e6e6e6;
			border-bottom: 0.01rem solid #e6e6e6;

			text {
				width: 5rem;
				text-align: center;
				font-size: 0.18rem;
				border-left: 0.01rem solid #e6e6e6;
				box-sizing: border-box;

				&:nth-child(2) {
					flex: 1;
					width: 0;
				}
			}
		}

		.check-content {
			display: flex;
			min-height: calc(100vh - 1rem);

			>view {
				padding: 0.2rem;
				box-sizing: border-box;
			}

			.left-wrap-content {
				display: flex;
				flex-direction: column;
				height: calc(100vh - 1rem);
				padding: 0;

				.wrap-search-box {
					height: 0.35rem;
					border-bottom: 0.01rem solid #e6e6e6;
					padding: 0.1rem 0.15rem;

					.wrap-search {
						background: #f5f5f5;
						display: flex;
						position: relative;
						padding: 0.05rem 0.15rem 0.05rem 0.4rem;

						input {
							width: 100%;
						}

						.iconfont {
							position: absolute;
							left: 0.15rem;
							top: 0.08rem;
							cursor: pointer;
						}
					}
				}
			}

			.not-record {
				color: #e6e6e6;
				font-size: 0.4rem;
				margin-top: 3rem;
				text-align: center;
				width: 5rem;
			}

			.check-list {
				width: 5rem;
				height: calc(100% - 0.5rem);
				padding: 0;

				.item-hover {
					background: var(--primary-color-light-9);
				}

				.item {
					position: relative;
					padding: 0.2rem;
					border-bottom: 0.01rem solid #e6e6e6;
					cursor: pointer;

					.name {
						font-size: $uni-font-size-lg;
						padding-bottom: 0.07rem;
					}

					.item-box {
						.head {
							display: flex;
							justify-content: space-between;
						}

						.body {
							margin-top: 0.15rem;
							display: flex;
							justify-content: space-between;
						}

						.time {
							text-align: right;
						}
					}

					.time,
					.nick-name {
						line-height: 1;
						width: 50%;
						font-size: $uni-font-size-lg;
					}

					.type {
						position: absolute;
						right: 0.25rem;
						top: 50%;
						transform: translateY(-50%);
					}
				}
			}

			.check-detail {
				flex: 1;
				width: 0;
				border-left: 0.01rem solid #e6e6e6;
				position: relative;

				.detail-empty {
					position: absolute;
					top: 50%;
					left: 50%;
					transform: translate(-50%, -50%);
					width: 2.1rem;
				}

				.form-content {
					margin-top: 0.2rem;

					.form-item {
						margin-bottom: 0.1rem;
						display: flex;

						.form-label {
							width: 1.6rem;
							text-align: right;
							padding-right: 0.1rem;
							box-sizing: border-box;
							height: 0.32rem;
							line-height: 0.32rem;
						}

						.form-inline {
							line-height: 0.32rem;
							margin-right: 0.1rem;
							box-sizing: border-box;
						}
					}

					.verify-item {
						display: flex;
						padding: 0.15rem;
						background: #f5f5f5;
						margin-bottom: 0.1rem;

						.item-img {
							width: 0.8rem;
							height: 0.8rem;
							display: flex;
							align-items: center;
							justify-content: center;

							image {
								width: 100%;
							}
						}

						.item-info {
							flex: 1;
							width: 0;
							margin-left: 0.15rem;
						}
					}
				}
			}
		}
	}
</style>
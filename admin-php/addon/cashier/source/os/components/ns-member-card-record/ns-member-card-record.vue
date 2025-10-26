<template>
	<view class="member-detail-wrap">
		<!-- 卡包 -->
		<uni-popup ref="cardListPop">
			<view class="pop-box card-list-pop-box">
				<view class="pop-header">
					<view class="pop-header-text">卡包</view>
					<view class="pop-header-close" @click="close('cardlist')">
						<text class="iconguanbi1 iconfont"></text>
					</view>
				</view>
				<scroll-view scroll-y="true" class="common-scrollbar pop-content">
					<dataTable url="/cardservice/storeapi/membercard/lists" :cols="card" ref="table" :option="option" :pagesize="pageSize">
						<template v-slot:action="dataTable">
							<text class="view-detail" @click="viewDetails(dataTable.value.card_id)">查看详情</text>
						</template>
					</dataTable>
				</scroll-view>
			</view>
		</uni-popup>

		<uni-popup ref="cardDetailPop">
			<view class="pop-box cardDetailPop-box">
				<view class="pop-header">
					<view class="pop-header-text">详情</view>
					<view class="pop-header-close" @click="$refs.cardDetailPop.close()">
						<text class="iconguanbi1 iconfont"></text>
					</view>
				</view>
				<view class="pop-content">
					<view class="tab-head">
						<text v-for="(item, index) in tabObj.list" :key="index" :class="{ active: tabObj.index == item.value }" v-if="(item.value == 3 && card_detail.card_log && card_detail.card_log.length > 0) || item.value != 3" @click="tabObj.index = item.value">
							{{ item.name }}
						</text>
					</view>
					<view class="tab-content">
						<view class="basic-info" v-if="tabObj.index == 0">
							<view class="basic-item using-hidden">卡项名称：{{ basicInfo.goods_name }}</view>
							<view class="basic-item">价格：{{ basicInfo.price }}</view>
							<view class="basic-item">
								卡类型：{{ (basicInfo.card_type == 'oncecard' && '限次卡') || (basicInfo.card_type == 'timecard' && '限时卡') || (basicInfo.card_type == 'commoncard' && '通用卡') }}
							</view>
							<view class="basic-item">总次数/已使用：{{ basicInfo.card_type == 'timecard' ? '不限' : basicInfo.total_num }}/{{ basicInfo.total_use_num }}</view>
							<view class="basic-item">获取时间：{{ $util.timeFormat(basicInfo.create_time) }}</view>
							<view class="basic-item">到期时间：{{ basicInfo.end_time > 0 ? $util.timeFormat(basicInfo.end_time) : '永久有效' }}</view>
						</view>
						<view class="other-information" v-if="tabObj.index == 1 && basicInfo && basicInfo.card_item">
							<view class="information-head">
								<text>商品名称</text>
								<text>总次数/已使用</text>
								<text>有效期</text>
							</view>
							<view class="information-body">
								<view class="information-tr" v-for="(item, index) in basicInfo.card_item" :key="index">
									<text class="using-hidden">{{ item.sku_name }}</text>
									<text>{{ item.card_type == 'timecard' ? '不限' : item.num }} /{{ item.use_num }}</text>
									<text>{{ item.end_time > 0 ? $util.timeFormat(item.end_time) : '永久有效' }}</text>
								</view>
								<view class="information-tr empty" v-if="!basicInfo.card_item.length">
									<view class="iconfont iconwushuju"></view>
									<view>暂无数据</view>
								</view>
							</view>
						</view>

						<view class="card-info" v-if="tabObj.index == 2">
							<dataTable url="/cardservice/storeapi/membercard/records" :cols="cardInfo.card" ref="table" :option="cardInfo.option" :pagesize="cardInfo.pageSize"></dataTable>
						</view>
					</view>
				</view>
			</view>
		</uni-popup>
	</view>
</template>

<script>
import { getMemberCardDetail } from '@/api/member'
import dataTable from '@/components/uni-data-table/uni-data-table.vue';

export default {
	components: {
		dataTable
	},
	props: {
		option: {}
	},
	data() {
		return {
			pageSize: 8,
			card: [{
				width: 20,
				title: '名称',
				align: 'left',
				field: 'goods_name'
			}, {
				width: 18,
				title: '卡号',
				align: 'center',
				field: 'card_code'
			}, {
				width: 8,
				title: '卡类型',
				align: 'left',
				templet: function (data) {
					if (data.card_type == 'oncecard') return '限次卡';
					if (data.card_type == 'timecard') return '限时卡';
					if (data.card_type == 'commoncard') return '通用卡';
				}
			}, {
				width: 12,
				title: '总次数/已使用',
				align: 'center',
				templet: data => {
					var totalNum = data.card_type == 'timecard' ? '不限' : data.total_num;
					return totalNum + '/' + data.total_use_num;
				}
			}, {
				width: 17,
				title: '创建时间',
				align: 'center',
				templet: data => {
					return this.$util.timeFormat(data.create_time);
				}
			}, {
				width: 17,
				title: '到期时间',
				align: 'center',
				templet: data => {
					if (data.end_time) return this.$util.timeFormat(data.end_time);
					else return '长期有效';
				}
			}, {
				width: 8,
				title: '操作',
				align: 'right',
				action: true
			}],
			tabObj: {
				list: [{
					value: 0,
					name: '基础信息'
				}, {
					value: 1,
					name: '商品/项目'
				}, {
					value: 2,
					name: '使用记录'
				}],
				index: 1
			},
			currCardId: 0,
			basicInfo: {},
			cardInfo: {
				card: [{
					width: 40,
					title: '卡项名称',
					align: 'left',
					field: 'sku_name'
				}, {
					width: 20,
					title: '使用次数',
					align: 'center',
					field: 'num'
				}, {
					width: 25,
					title: '使用时间',
					align: 'right',
					templet: data => {
						return this.$util.timeFormat(data.create_time);
					}
				}, {
					width: 15,
					title: '操作',
					align: 'right',
					action: true
				}],
				option: {},
				pageSize: 6
			}
		};
	},
	created() { },
	methods: {
		open() {
			this.$refs.cardListPop.open();
		},
		close() {
			this.$refs.cardListPop.close();
		},
		viewDetails(card_id) {
			this.currCardId = card_id;
			this.$refs.cardDetailPop.open();
			this.getCardDetail();

			this.cardInfo.option.member_id = this.globalMemberInfo.member_id;
			this.cardInfo.option.card_id = this.currCardId;
		},
		getCardDetail() {
			let data = {};
			data.member_id = this.globalMemberInfo.member_id;
			data.card_id = this.currCardId;
			getMemberCardDetail(data).then(res => {
				this.basicInfo = {};
				if (res.code >= 0) {
					this.basicInfo = res.data;
				}
			});
		}
	}
};
</script>

<style lang="scss" scoped>
.pop-box {
	background: #ffffff;
	width: 8rem;
	height: 7rem;

	.pop-header {
		padding: 0 0.15rem 0 0.2rem;
		height: 0.5rem;
		line-height: 0.5rem;
		border-bottom: 0.01rem solid #f0f0f0;
		font-size: 0.14rem;
		color: #333;
		overflow: hidden;
		border-radius: 0.02rem 0.2rem 0 0;
		box-sizing: border-box;
		display: flex;
		justify-content: space-between;

		.pop-header-close {
			cursor: pointer;

			text {
				font-size: 0.18rem;
			}
		}
	}

	.pop-content {
		height: calc(100% - 1rem);
		overflow-y: scroll;
		padding: 0.1rem 0.2rem;
		box-sizing: border-box;
	}

	.pop-bottom {
		button {
			width: 95%;
		}
	}
}

.card-list-pop-box {
	width: 10rem;
	height: 5.7rem;

	.pop-content {
		height: calc(100% - 0.5rem);
	}

	/deep/ .tpage {
		position: absolute;
		right: 0;
		bottom: 0;
	}

	.basic-box {
		display: flex;
		justify-content: space-between;
		margin-bottom: 0.2rem;
		padding: 0.2rem;
		box-sizing: border-box;
	}

	.basic {
		padding: 0.1rem;
		margin-bottom: 0.5rem;
	}
}

.cardDetailPop-box {
	width: 10rem;
	height: 5.7rem;

	.tab-head {
		display: flex;
		background-color: #f7f8fa;

		text {
			height: 0.5rem;
			line-height: 0.5rem;
			text-align: center;
			padding: 0 0.35rem;
			box-sizing: border-box;

			&.active {
				background-color: #fff;
			}
		}
	}

	.pop-content {
		overflow-y: inherit;

		.basic-info {
			display: flex;
			flex-wrap: wrap;
			justify-content: space-between;
			padding: 0.2rem;

			.basic-item {
				flex-basis: 33%;
				height: 0.4rem;
				line-height: 0.4rem;
			}
		}
	}

	.other-information {
		display: flex;
		justify-content: space-between;
		flex-direction: column;
		padding-top: 0.2rem;

		.information-head {
			display: flex;
			justify-content: space-between;
			background-color: #f7f8fa;

			text {
				padding: 0 0.2rem;
				height: 0.5rem;
				line-height: 0.5rem;

				&:nth-child(1) {
					flex-basis: 35%;
				}

				&:nth-child(2) {
					flex-basis: 35%;
				}

				&:nth-child(2) {
					flex-basis: 30%;
				}
			}
		}

		.information-tr {
			display: flex;
			justify-content: space-between;
			border-bottom: 0.01rem solid #e6e6e6;

			text {
				padding: 0 0.2rem;
				height: 0.5rem;
				line-height: 0.5rem;

				&:nth-child(1) {
					flex-basis: 35%;
				}

				&:nth-child(2) {
					flex-basis: 35%;
				}

				&:nth-child(2) {
					flex-basis: 30%;
				}
			}

			&.empty {
				display: flex;
				justify-content: center;
				align-items: center;
				height: 0.5rem;
				color: #909399;

				.iconfont {
					font-size: 0.25rem;
					margin: 0.05rem;
				}
			}
		}
	}

	.card-info {
		display: flex;
		justify-content: space-between;
		padding-top: 0.2rem;
	}
}

.view-detail {
	color: $primary-color;
}</style>

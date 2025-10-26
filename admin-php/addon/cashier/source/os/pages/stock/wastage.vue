<template>
	<base-page>
		<view class="goodslist">
			<view class="goodslist-box">
				<view class="goodslist-left">
					<view class="goods-title">
						出库单查询
						<text class="iconfont icongengduo1"></text>
					</view>
					<view class="goods-search">
						<view class="search">
							<text class="iconfont icon31sousuo"></text>
							<input type="text" v-model="search_text" @input="search" placeholder="搜索出库单号" />
						</view>
					</view>
					<scroll-view scroll-y="true" class="goods-list-scroll" :show-scrollbar="false" @scrolltolower="getListData">
						<view class="item" @click="getDetailData(item.document_id, index)" v-for="(item, index) in list" :key="index" :class="{ itemhover: selectGoodsKeys == index }">
							<view class="title">
								<view>{{ item.document_no }}</view>
								<view>{{ item.type_name }}</view>
							</view>
							<view class="other-info">
								<view>￥{{ item.document_money }}</view>
								<view>{{ item.operater_name }}</view>
								<view>{{ item.status_name }}</view>
								<view>{{ $util.timeFormat(item.create_time) }}</view>
							</view>
						</view>
						<view class="notYet" v-if="!one_judge && !list.length">暂无数据</view>
					</scroll-view>
					<view class="add-wastage">
						<button type="default" class="primary-btn" v-if="globalStoreInfo.stock_type == 'store'" @click="add()">添加出库单</button>
					</view>
				</view>
				<view class="goodslist-right">
					<view class="goods-title">出库单详情</view>
					<view class="order-information" v-if="Object.keys(detail).length">
						<view class="order-status">基本信息</view>
						<view class="order-types">
							<view class="type type1">
								<view>出库单号：</view>
								<view>{{ detail.document_no }}</view>
							</view>
							<view class="type type1">
								<view>制单人：</view>
								<view>{{ detail.operater_name || '--' }}</view>
							</view>
							<view class="type type1">
								<view>制单时间：</view>
								<view class="message">{{ detail.create_time }}</view>
							</view>
							<view class="type type1">
								<view>出库时间：</view>
								<view class="message">{{ detail.time }}</view>
							</view>
							<view class="type type1">
								<view>单据类型：</view>
								<view class="message">{{ detail.type_name }}</view>
							</view>
							<view class="type type1">
								<view>状态：</view>
								<view class="message">{{ detail.status_data.name }}</view>
							</view>

							<view class="type type1" v-if="detail.verifier_name">
								<view>审核人：</view>
								<view class="message">{{ detail.verifier_name }}</view>
							</view>
							<view class="type type1" v-if="detail.audit_time">
								<view>审核时间：</view>
								<view class="message">{{ detail.audit_time }}</view>
							</view>
							<view class="type type1" v-if="detail.status == -1">
								<view>拒绝理由：</view>
								<view class="message">{{ detail.refuse_reason }}</view>
							</view>
							<view class="type type1">
								<view>备注：</view>
								<view class="message">{{ detail.remark }}</view>
							</view>
						</view>

						<view class="goods-info">
							<view class="title">商品明细</view>
							<view class="table">
								<view class="table-th table-all">
									<view class="table-td" style="width:45%;justify-content: flex-start;">商品名称/规格/条形码</view>
									<view class="table-td" style="width:15%">单位</view>
									<view class="table-td" style="width:10%">数量</view>
									<view class="table-td" style="width:15%">成本价(元)</view>
									<view class="table-td" style="width:15%;justify-content: flex-end;">金额(元)</view>
								</view>
								<view class="table-tr table-all" v-for="(item, index) in detail.goods_sku_list_array" :key="index">
									<view class="table-td table-goods-name" style="width:45%;justify-content: flex-start;">
										<image :src="$util.img(item.goods_sku_img)" mode="aspectFill" />
										<text class="multi-hidden">{{ item.goods_sku_name }}</text>
									</view>
									<view class="table-td" style="width:15%">{{ item.goods_unit || '件' }}</view>
									<view class="table-td" style="width:10%">{{ item.goods_num }}</view>
									<view class="table-td" style="width:15%">{{ item.goods_price }}</view>
									<view class="table-td" style="width:15%;justify-content: flex-end;">
										{{ parseFloat(item.goods_sum).toFixed(2) }}
									</view>
								</view>
							</view>

							<view class="total-money-num">
								<view class="box">
									<view>商品种类</view>
									<view class="money">{{ detail.goods_count }}种</view>
								</view>
								<view class="box">
									<view>商品数量</view>
									<view class="money">{{ detail.goods_price }}{{ detail.goods_unit }}</view>
								</view>
								<view class="box total">
									<view>合计金额</view>
									<view class="money">￥{{ parseFloat(detail.goods_total_price).toFixed(2) }}</view>
								</view>
							</view>
						</view>

						<view class="action-box">
							<!-- 只有经办人才能操作入库单 -->
							<template v-if="(detail.status == 1 || detail.status == -1) && detail.operater == detail.uid">
								<button type="primary" class="default-btn" @click="open('deleteWastagePop')">删除</button>
								<button type="primary" class="default-btn" @click="edit">编辑</button>
							</template>

							<!-- 只有管理员和拥有单据审核权限的才能审核 -->
							<template v-if="detail.status == 1 && detail.is_audit == 0">
								<button type="primary" class="default-btn" @click="open('refuseWastagePop')">审核拒绝</button>
								<button type="primary" class="primary-btn" @click="open('agreeWastagePop')">审核通过</button>
							</template>

						</view>
					</view>
					<block v-else-if="!one_judge && !Object.keys(detail).length">
						<image class="cart-empty" src="@/static/goods/goods_empty.png" mode="widthFix"/>
					</block>
				</view>
			</view>
		</view>

		<!-- 同意 -->
		<unipopup ref="agreeWastagePop" type="center">
			<view class="confirm-pop">
				<view class="title">确定要通过该单据吗？</view>
				<view class="btn">
					<button type="primary" class="default-btn btn save" @click="close('agreeWastagePop')">取消</button>
					<button type="primary" class="primary-btn btn" @click="agree">确定</button>
				</view>
			</view>
		</unipopup>

		<!-- 拒绝 -->
		<unipopup ref="refuseWastagePop" type="center">
			<view class="confirm-pop message">
				<view class="title">
					拒绝理由
					<text class="iconfont iconguanbi1" @click="close('refuseWastagePop')"></text>
				</view>
				<view class="textarea-box">
					<textarea v-model="refuseReason" class="textarea" maxlength="200" placeholder="输入请不多于200字"></textarea>
				</view>
				<button @click="refuse" type="primary" class="primary-btn btn save">保存</button>
			</view>
		</unipopup>

		<!-- 删除 -->
		<unipopup ref="deleteWastagePop" type="center">
			<view class="confirm-pop">
				<view class="title">确定要删除该单据吗？</view>
				<view class="btn">
					<button type="primary" class="default-btn btn save" @click="close('deleteWastagePop')">取消</button>
					<button type="primary" class="primary-btn btn" @click="deleteDocument">确定</button>
				</view>
			</view>
		</unipopup>

	</base-page>
</template>

<script>
	import {
		getWastageLists,
		getWastageDetail,
		storageAgree,
		storageRefuse,
		storageDelete
	} from '@/api/stock.js';
	import unipopup from '@/components/uni-popup/uni-popup.vue';

	export default {
		components: {
			unipopup
		},
		data() {
			return {
				selectGoodsKeys: 0,
				//获取订单的页数
				page: 1,
				//每次获取订单的条数
				page_size: 9,
				// 订单搜索是用到的数据
				search_text: '',
				//初始时加载详情数据判断
				one_judge: true,
				// 订单列表数据
				list: [],
				//订单详情数据
				detail: {},
				repeatFlag: false,
				refuseReason: ''
			};
		},
		onLoad(option) {
			if (option.id) {
				this.search_text = option.id;
			}
			this.getListData();
		},
		methods: {
			// 搜索
			search() {
				this.page = 1;
				this.list = [];
				this.one_judge = true;
				this.getListData();
			},
			/**
			 * 获取订单列表
			 */
			getListData() {
				getWastageLists({
					page: this.page,
					page_size: this.page_size,
					search_text: this.search_text
				}).then(res => {
					if (res.data.list.length == 0 && this.one_judge) {
						this.detail = {};
						this.one_judge = false;
					}
					if (res.code >= 0 && res.data.list.length != 0) {
						this.page += 1;

						if (this.list.length == 0) {
							this.list = res.data.list;
						} else {
							this.list = this.list.concat(res.data.list);
						}

						//初始时加载一遍详情数据
						if (this.one_judge) {
							this.getDetailData(this.list[0].document_id);
						}
					}
				});
			},
			/**
			 * 获取订单详情数据
			 */
			getDetailData(document_id, keys = 0) {
				this.selectGoodsKeys = keys;
				this.type = 'detail';
				getWastageDetail(document_id).then(res => {
					if (res.code >= 0) {
						this.detail = res.data;
						this.$forceUpdate();
						this.one_judge = false;
					}
				});
			},
			add(data) {
				this.$util.redirectTo('/pages/stock/stockout');
			},
			edit() {
				this.$util.redirectTo('/pages/stock/stockout', {
					document_id: this.detail.document_id
				});
			},
			open(action) {
				this.$refs[action].open();
			},
			close(name) {
				this.$refs[name].close();
			},
			agree() {
				if (this.repeatFlag) return;
				this.repeatFlag = true;
				storageAgree(this.detail.document_id).then(res => {
					this.$util.showToast({
						title: res.message
					});
					if (res.code >= 0) {
						this.search();
						this.getDetailData(this.detail.document_id);
						this.close('agreeWastagePop');
					}
					this.repeatFlag = false;
				});
			},
			refuse() {
				if (!this.refuseReason) {
					this.$util.showToast({
						title: '请输入拒绝理由'
					});
					return;
				}
				if (this.repeatFlag) return;
				this.repeatFlag = true;
				storageRefuse({
					document_id: this.detail.document_id,
					refuse_reason: this.refuseReason
				}).then(res => {
					this.$util.showToast({
						title: res.message
					});
					if (res.code >= 0) {
						this.search();
						this.getDetailData(this.detail.document_id);
						this.close('refuseWastagePop');
					}
					this.repeatFlag = false;
				});
			},
			deleteDocument() {
				if (this.repeatFlag) return;
				this.repeatFlag = true;
				storageDelete(this.detail.document_id).then(res => {
					this.$util.showToast({
						title: res.message
					});
					if (res.code >= 0) {
						this.list.splice(this.selectGoodsKeys, 1);

						if (this.selectGoodsKeys == 0) {
							this.selectGoodsKeys = 0;
						} else {
							this.selectGoodsKeys -= 1;
						}

						this.getDetailData(this.list[this.selectGoodsKeys].document_id, this
							.selectGoodsKeys);
						this.close('deleteWastagePop');
					}
					this.repeatFlag = false;
				});
			}
		}
	};
</script>

<style scoped lang="scss">
	@import './public/css/orderlist.scss';
</style>
<template>
	<base-page>
		<view class="deliverlist">
			<view class="deliverlist-box">
				<view class="deliverlist-left">
					<view class="deliver-title">
						配送员
						<text class="iconfont icongengduo1"></text>
					</view>
					<view class="deliver-list-wrap">
						<block v-if="list.length > 0">
							<scroll-view scroll-y="true" class="deliver-list-scroll all-scroll" @scrolltolower="getDeliverList">
								<view class="item" @click="deliverSelect(item, index)" v-for="(item, index) in list" :key="index" :class="index == selectDeliverKeys ? 'itemhover' : ''">
									<view class="item-right">
										<view class="deliver-name">{{ item.deliver_name }}</view>
										<view class="deliver-money">{{ item.deliver_mobile }}</view>
									</view>
								</view>
							</scroll-view>
						</block>
						<view class="notYet" v-else-if="!one_judge && list.length == 0">暂无配送员</view>
					</view>
					<view class="add-deliver">
						<button type="default" class="primary-btn" @click="addDeliver">添加配送员</button>
					</view>
				</view>
				<view class="deliverlist-right" v-show="!one_judge">
					<view class="deliver-title">配送员详情</view>
					<view class="deliver-information">
						<block v-if="detail && Object.keys(detail).length">
							<view class="title">基本信息</view>
							<view class="information-box">
								<view class="box-left">
									<view class="information">
										<view>姓名：</view>
										<view>{{ detail.deliver_name }}</view>
									</view>
									<view class="information">
										<view>电话：</view>
										<view>{{ detail.deliver_mobile }}</view>
									</view>
									<view class="information">
										<view>添加时间：</view>
										<view>{{ detail.create_time ? $util.timeFormat(detail.create_time) : '--' }}</view>
									</view>
									<view class="information">
										<view>最后修改时间：</view>
										<view>{{ detail.modify_time ? $util.timeFormat(detail.modify_time) : '--' }}</view>
									</view>
								</view>
							</view>

						</block>
						<block v-else>
							<image class="cart-empty" src="@/static/goods/goods_empty.png" mode="widthFix" />
						</block>
						<view class="button-box" v-if="detail && Object.keys(detail).length">
							<button class="default-btn" @click="$refs.deletePop.open()">删除</button>
							<button class="default-btn" @click="openEditDeliverPop(detail.deliver_id)">修改</button>
						</view>
					</view>
					
				</view>
				<uni-popup ref="deliverpop" type="center">
					<view class="common-wrap common-form">
						<view class="common-title">{{ deliverData.deliver_id > 0 ? '修改' : '添加' }}配送员</view>
						<view class="common-form-item">
							<label class="form-label">
								<text class="required">*</text>
								姓名
							</label>
							<view class="form-input-inline">
								<input type="text" v-model="deliverData.deliver_name" class="form-input" />
							</view>
							<text class="form-word-aux"></text>
						</view>
						<view class="common-form-item">
							<label class="form-label">
								<text class="required">*</text>
								手机号
							</label>
							<view class="form-input-inline">
								<input type="number" v-model="deliverData.deliver_mobile" class="form-input" />
							</view>
							<text class="form-word-aux"></text>
						</view>
						<view class="common-btn-wrap">
							<button type="default" class="screen-btn" @click="addDeliverSave">{{ deliverData.deliver_id > 0 ? '修改' : '添加' }}</button>
							<button type="primary" class="default-btn btn save" @click="addDeliverClose()">取消</button>
						</view>
					</view>
				</uni-popup>
				<!-- 删除 -->
				<uni-popup ref="deletePop" type="center">
					<view class="confirm-pop">
						<view class="title">确定要删除吗？</view>
						<view class="btn">
							<button type="primary" class="default-btn btn save" @click="$refs.deletePop.close()">取消</button>
							<button type="primary" class="primary-btn btn" @click="deleteDeliverFn(detail.deliver_id)">确定</button>
						</view>
					</view>
				</uni-popup>
				<ns-loading :layer-background="{ background: 'rgba(255,255,255,.8)' }" ref="loading"></ns-loading>
			</view>
		</view>
	</base-page>
</template>

<script>
	import {
		addDeliver,
		deleteDeliver,
		editDeliver,
		getDeliverInfo,
		getDeliverList
	} from '@/api/deliver.js'

	export default {
		data() {
			return {
				search_text: '',
				page: 1,
				// 每次返回数据数
				page_size: 8,
				// 第一次请求列表做详情渲染判断
				one_judge: true,
				//详情数据
				detail: {},
				list: [],
				selectDeliverKeys: 0,
				flag: false,
				deliverData: {
					deliver_id: 0,
					deliver_name: '',
					deliver_mobile: ''
				}
			};
		},
		onLoad() {
			this.getDeliverListFn();
		},
		methods: {
			deliverSelect(item, keys) {
				this.selectDeliverKeys = keys;
				this.getDeliverDetail(item.deliver_id);
			},
			// 搜索员工
			search() {
				this.page = 1;
				this.list = [];
				this.one_judge = true;
				this.getDeliverListFn();
			},
			addDeliver() {
				this.$refs.deliverpop.open();
			},
			addDeliverClose() {
				this.$refs.deliverpop.close();
			},
			addDeliverSave() {
				if (this.deliverData.deliver_name == '') {
					this.$util.showToast({
						title: '请输入配送员名称'
					});
					return false;
				}
				if (this.deliverData.deliver_mobile == '') {
					this.$util.showToast({
						title: '请输入配送员电话'
					});
					return false;
				}
				if (this.flag) return false;
				this.flag = true;
				let action = '';
				if (this.deliverData.deliver_id > 0) {
					action = editDeliver(this.deliverData);
				} else {
					action = addDeliver(this.deliverData);
				}
				action.then(res => {
					this.$util.showToast({
						title: res.message
					});
					if (res.code == 0) {
						this.page = 1;
						this.list = [];
						this.one_judge = true;
						this.getDeliverListFn();
						this.addDeliverClose();
						this.deliverData = {
							deliver_id: 0,
							deliver_name: '',
							deliver_mobile: ''
						};
					}
					this.flag = false;
				});
			},
			openEditDeliverPop(deliver_id) {
				getDeliverInfo(deliver_id).then(res => {
					if (res.code == 0) {
						this.deliverData = res.data;
						this.$refs.deliverpop.open();
					}
				});
			},
			/**
			 * 请求的列表数据
			 */
			getDeliverListFn() {
				getDeliverList({
					page: this.page,
					page_size: this.page_size,
				}).then(res => {
					if (res.data.list.length == 0 && this.one_judge) {
						this.detail = {};
						this.one_judge = false;
					}
					this.$refs.loading.hide();
					if (res.code >= 0 && res.data.list.length != 0) {
						this.page += 1;
						if (this.list.length == 0) {
							this.list = res.data.list;
						} else {
							this.list = this.list.concat(res.data.list);
						}

						//初始时加载一遍详情数据
						if (this.one_judge) {
							this.getDeliverDetail(this.list[0].deliver_id);
						}
					}
				});
			},
			getDeliverDetail(deliver_id) {
				getDeliverInfo(deliver_id).then(res => {
					if (res.code == 0) {
						this.detail = res.data;
						this.one_judge = false;
					}
				});
			},
			deleteDeliverFn(deliver_id) {
				if (this.flag) return;
				this.flag = true;
				deleteDeliver(deliver_id).then(res => {
					this.flag = false;
					if (res.code >= 0) {
						this.page = 1;
						this.list = [];
						this.one_judge = true;
						this.$refs.deletePop.close()
						this.getDeliverListFn();
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

<style scoped lang="scss">
	@import './public/css/deliver.scss';
</style>
<template>
	<base-page>
		<view class="coupons-list">
			<view class="add-coupons">
				<button type="default" class="screen-btn" @click="add">添加优惠券</button>
			</view>
			<view class="screen-warp common-form">
				<view class="common-form-item">
					<view class="form-inline">
						<label class="form-label">优惠券名称</label>
						<view class="form-input-inline">
							<input type="text" v-model="option.coupon_name" placeholder="请输入优惠券名称" class="form-input" />
						</view>
					</view>
					<view class="form-inline">
						<label class="form-label">优惠券类型</label>
						<view class="form-input-inline border-0">
							<select-lay :zindex="10" :value="option.type" name="type" placeholder="请选择优惠券类型" :options="typeList" @selectitem="selectCouponsType"/>
						</view>
					</view>
					<view class="form-inline">
						<label class="form-label">优惠券状态</label>
						<view class="form-input-inline border-0">
							<select-lay :zindex="9" :value="option.status" name="status" placeholder="请选择优惠券状态" :options="statusList" @selectitem="selectStatus"/>
						</view>
					</view>
					<view class="form-inline">
						<label class="form-label">适用场景</label>
						<view class="form-input-inline border-0">
							<select-lay :zindex="9" :value="option.use_channel" name="status" placeholder="请选择优惠券状态" :options="useChannelList" @selectitem="selectUseChannel"/>
						</view>
					</view>
					<!-- <view class="form-inline">
						<label class="form-label">有效期限</label>
						<view class="form-input-inline border-0">
							<select-lay :zindex="9" :value="option.validity_type" name="validity_type" placeholder="请选择有效期限" :options="validityTypeList" @selectitem="selectValidityType"/>
						</view>
					</view> -->
					<view class="form-inline common-btn-wrap">
						<button type="default" class="screen-btn" @click="searchFn()">筛选</button>
						<button type="default" @click="resetFn()">重置</button>
					</view>
				</view>
			</view>
			<uniDataTable url="/coupon/storeapi/coupon/lists" :option="option" :cols="cols" ref="couponListTable">
				<template v-slot:action="dataTable">
					<view class="action-btn-wrap">
						<text v-if="dataTable.value.status=='1'" class="action-item" @click="promotion(dataTable.value.coupon_type_id)">推广</text>
						<text v-if="dataTable.value.status=='1' && globalStoreInfo.store_id===dataTable.value.store_id" class="action-item" @click="edit(dataTable.value.coupon_type_id)">编辑</text>
						<text class="action-item" @click="detail(dataTable.value.coupon_type_id)">详情</text>
						<text v-if="dataTable.value.status=='1' && globalStoreInfo.store_id===dataTable.value.store_id" class="action-item" @click="closeOpen(dataTable.value.coupon_type_id)">关闭</text>
						<text v-if="dataTable.value.status!='1' && globalStoreInfo.store_id===dataTable.value.store_id" class="action-item" @click="deleteOpen(dataTable.value.coupon_type_id)">删除</text>
					</view>
				</template>
			</uniDataTable>
		</view>
		<!-- 推广 -->
		<ns-promotion-popup ref="promotionPop" /> 
		<!-- 关闭 -->
		<unipopup ref="closeCouponsPop" type="center">
			<view class="confirm-pop">
				<view class="title">确定要关闭该优惠券吗？</view>
				<view class="btn">
					<button type="primary" class="default-btn btn save" @click="$refs.closeCouponsPop.close()">取消</button>
					<button type="primary" class="primary-btn btn" @click="close">确定</button>
				</view>
			</view>
		</unipopup>

		<!-- 删除 -->
		<unipopup ref="deleteCouponsPop" type="center">
			<view class="confirm-pop">
				<view class="title">确定要删除该优惠券吗？</view>
				<view class="btn">
					<button type="primary" class="default-btn btn save" @click="$refs.deleteCouponsPop.close()">取消</button>
					<button type="primary" class="primary-btn btn" @click="del">确定</button>
				</view>
			</view>
		</unipopup>

	</base-page>
</template>

<script>
	import uniDataTable from '@/components/uni-data-table/uni-data-table.vue';
	import nsPromotionPopup from '@/components/ns-promotion-popup/ns-promotion-popup.vue';
	import unipopup from '@/components/uni-popup/uni-popup.vue';
	import couponList from './public/js/coupon_list.js';

	export default {
		components: {
			unipopup,
			uniDataTable,
			nsPromotionPopup
		},
		mixins: [couponList]
	};
</script>

<style scoped lang="scss">
	@import './public/css/coupon_list.scss';
</style>
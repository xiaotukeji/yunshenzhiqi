<template>
	<base-page>
		<view class="stock-body">
			<view class="content-wrap" @click="goodsShow = false">
				<view class="title">{{ screen.allot_id ? '编辑调拨单' : '添加调拨单' }}</view>
				<view class="screen-warp form-content">
					<view class="form-item">
						<label class="form-label">
							<text class="required">*</text>
							调拨单号
						</label>
						<view class="form-inline input">
							<input type="text" v-model="screen.allot_no" :disabled="screen.allot_id != ''" placeholder="请输入调拨单号" />
						</view>
					</view>
					<view class="form-item">
						<label class="form-label">
							<text class="required">*</text>
							调拨方式
						</label>
						<view class="form-inline">
							<select-lay :zindex="10" :value="type" name="names" placeholder="请选择调拨方式" :options="screen.allocateTypeList" @selectitem="selectAllocateType"/>
						</view>
					</view>
					<view class="form-item store-info">
						<view class="form-label">当前门店：</view>
						<view class="form-inline">{{ globalStoreInfo.store_name }}</view>
					</view>
					<view class="form-item store-info">
						<view class="form-label">当前操作人：</view>
						<view class="form-inline">{{ userInfo ? userInfo.username : '' }}</view>
					</view>
					<view class="form-item">
						<label class="form-label">
							<text class="required">*</text>
							{{ storeName }}
						</label>
						<view class="form-inline">
							<select-lay :zindex="10" :value="screen.store_id" name="names" :placeholder="'请选择' + storeName" :options="screen.storeList" @selectitem="selectStore"/>
						</view>
					</view>
					<view class="form-item">
						<label class="form-label">
							<text class="required">*</text>
							调拨时间
						</label>
						<view class="form-inline">
							<uni-datetime-picker :start="screen.startDate" v-model="screen.birthday" type="timestamp" :clearIcon="false" @change="changeTime" />
						</view>
					</view>
				</view>
				<view class="table-wrap">
					<view class="table-head">
						<view class="table-tr">
							<view class="table-th" style="flex: 3;">产品名称/规格/编码</view>
							<view class="table-th" style="flex: 1;">当前库存</view>
							<view class="table-th" style="flex: 1;">单位</view>
							<view class="table-th" style="flex: 2;">成本价</view>
							<view class="table-th" style="flex: 2;">数量</view>
							<view class="table-th" style="flex: 1;">总金额</view>
							<view class="table-th" style="flex: 1;">操作</view>
						</view>
					</view>
					<view class="table-body">
						<view class="table-tr">
							<view class="table-td select-goods-input" style="flex: 3;" @click.stop="goodsShow = true">
								<input type="text" @confirm="getGoodsData($event, -1)" placeholder="请输入产品名称/规格/编码" v-model="params.search_text" />
								<text class="iconfont icontuodong" @click="getGoodsData({ detail: null }, -1)"></text>
							</view>
							<view class="table-td" style="flex: 1;"></view>
							<view class="table-td" style="flex: 1;"></view>
							<view class="table-td" style="flex: 2;"></view>
							<view class="table-td" style="flex: 2;"></view>
							<view class="table-td" style="flex: 1;"></view>
							<view class="table-td" style="flex: 1;"></view>
						</view>
						<block v-for="(item, index) in goodsList" :key="index">
							<view class="table-tr" v-if="goodsIdArr.includes(item.sku_id)">
								<view class="table-td goods-name" style="flex: 3;">{{ item.title }}</view>
								<view class="table-td" style="flex: 1;">{{ item.real_stock || 0 }}</view>
								<view class="table-td" style="flex: 1;">{{ item.unit || '件' }}</view>
								<view class="table-td" style="flex: 2;">{{ item.cost_price || 0 }}</view>
								<view class="table-td" style="flex: 2;">
									<input type="number" v-model="item.goods_num" placeholder="请输入数量" @input="calcTotalData" />
								</view>
								<view class="table-td" style="flex: 1;">
									{{ (item.goods_num * item.cost_price || 0).toFixed(2) }}
								</view>
								<view class="table-td" style="flex: 1;">
									<button type="default" class="delete" @click="delGoods(item.sku_id)">删除</button>
								</view>
							</view>
						</block>
						<view class="table-tr table-empty" v-if="!goodsIdArr.length">暂无数据，请选择商品数据</view>
					</view>
				</view>
				<stock-goods-dialog v-model="dialogVisible" :params="params" @selectGoods="selectGoods" />
				
			</view>
			<view class="action-wrap">
				<view class="table-total">
					合计：共{{ totalData.kindsNum }}种产品，合计金额{{ totalData.price.toFixed(2) }}
				</view>
				<view class="btn-wrap">
					<button type="default" class="remark default" @click="$refs.remarkPopup.open()">备注</button>
					<button type="default" class="stockout-btn" @click="stockOutFn" :loading="isSubmit">确认调拨</button>
					<button type="default" class="default" @click="backFn">返回</button>
				</view>
			</view>
		</view>
		<uni-popup ref="remarkPopup" type="center">
			<view class="remark-wrap">
				<view class="header">
					<text class="title">备注</text>
					<text class="iconfont iconguanbi1" @click="$refs.remarkPopup.close()"></text>
				</view>
				<view class="body">
					<textarea v-model="remark" placeholder="填写备注信息" placeholder-class="placeholder-class" />
				</view>
				<view class="footer">
					<button type="default" class="primary-btn" @click="remarkConfirm">确认</button>
				</view>
			</view>
		</uni-popup>
	</base-page>
</template>

<script>
import editAllocate from './public/js/edit_allocate';
import stockGoodsDialog from '@/components/stock-goods-dialog/stock-goods-dialog.vue';

export default {
	components: {
		stockGoodsDialog
	},
	mixins: [editAllocate]
};
</script>

<style lang="scss" scoped>
@import './public/css/editStock.scss';
</style>
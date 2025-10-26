<template>
    <base-page>
        <view class="coupons-form">
            <view class="common-wrap common-form fixd common-scrollbar">
                <view class="common-title">{{ couponsData.coupon_type_id ? '编辑优惠券' : '添加优惠券' }}</view>
                <view class="common-form-item">
                    <label class="form-label"><text class="required">*</text>优惠券名称</label>
                    <view class="form-input-inline">
                        <input type="text" v-model="couponsData.coupon_name" class="form-input" maxlength="15" />
                    </view>
                </view>
                <view class="common-form-item">
                    <label class="form-label"><text class="required">*</text>优惠券类型</label>
                    <view class="form-input-inline border-0">
                        <uni-data-checkbox v-model="couponsData.type" :localdata="typeList"/>
                    </view>
                </view>
                <view class="common-form-item" v-if="couponsData.type == 'reward'">
                    <label class="form-label"><text class="required">*</text>优惠券面额</label>
                    <view class="form-input-inline">
                        <input type="number" v-model="couponsData.money" class="form-input" />
                    </view>
                    <text class="form-word-aux">元</text>
                    <text class="form-word-aux-line">价格不能小于等于0，可保留两位小数</text>
                </view>
                <view class="common-form-item" v-else>
                    <label class="form-label"><text class="required">*</text>优惠券折扣</label>
                    <view class="form-input-inline">
                        <input type="number" v-model="couponsData.discount" class="form-input" />
                    </view>
                    <text class="form-word-aux">折</text>
                    <text class="form-word-aux-line">优惠券折扣不能小于1折，且不可大于9.9折，可保留两位小数</text>
                </view>
                <view class="common-form-item" v-if="couponsData.type == 'discount'">
                    <label class="form-label">最多优惠</label>
                    <view class="form-input-inline"><input type="number" v-model="couponsData.discount_limit" class="form-input" /></view>
                    <text class="form-word-aux">元</text>
                </view>
                <view class="common-form-item">
                    <label class="form-label"><text class="required">*</text>满多少元可以使用</label>
                    <view class="form-input-inline">
                        <input type="number" v-model="couponsData.at_least" class="form-input" />
                    </view>
                    <text class="form-word-aux">元</text>
                    <text class="form-word-aux-line">价格不能小于0，无门槛请设为0</text>
                </view>
                <view class="common-form-item">
                    <label class="form-label">是否允许直接领取</label>
                    <view class="form-input-inline border-0">
                        <switch :checked="couponsData.is_show === 1" style="transform:scale(0.7)" @change="checkIsShow" />
                    </view>
                </view>
                <block v-if="couponsData.is_show === 1">
                    <view class="common-form-item">
                        <label class="form-label"><text class="required">*</text>发放数量</label>
                        <view class="form-input-inline">
                            <input type="number" v-model="couponsData.count" class="form-input" />
                        </view>
                        <text class="form-word-aux">张</text>
                        <text class="form-word-aux-line">优惠券发放数量，没有之后不能领取或发放，-1为不限制发放数量,发放数量只能增加不能减少。</text>
                    </view>
                    <view class="common-form-item">
                        <label class="form-label"><text class="required">*</text>最大领取数量</label>
                        <view class="form-input-inline">
                            <input type="number" v-model="couponsData.max_fetch" class="form-input" />
                        </view>
                        <text class="form-word-aux">张</text>
                        <text class="form-word-aux-line">数量不能小于0，且必须为整数；设置为0时，可无限领取</text>
                    </view>
                </block>
                <view class="common-form-item coupons-img">
                    <label class="form-label">优惠券图片</label>
                    <view class="form-input-inline upload-box" @click="addImg">
                        <view class="upload" v-if="couponsData.image">
                            <image :src="$util.img(couponsData.image)" mode="heightFix" />
                        </view>
                        <view class="upload" v-else>
                            <text class="iconfont iconyunshangchuan"></text>
                            <view>点击上传</view>
                        </view>
                    </view>
                    <text class="form-word-aux-line">建议尺寸：325*95像素，图片上传默认不限制大小</text>
                </view>
                <view class="common-form-item">
                    <label class="form-label">有效期类型</label>
                    <view class="form-input-inline border-0 radio-list">
                        <uni-data-checkbox v-model="couponsData.validity_type" :localdata="validityTypeList"/>
                    </view>
                    <view class="form-word-aux-line top" v-if="couponsData.validity_type === 0">
                        <view class="w-250">
                            <uni-datetime-picker v-model="couponsData.end_time" type="timestamp" :clearIcon="false" @change="changeTime" />
                        </view>
                    </view>
                </view>
                <view class="common-form-item" v-if="couponsData.validity_type === 1">
                    <label class="form-label"><text class="required">*</text>领取后几天有效</label>
                    <view class="form-input-inline">
                        <input type="number" v-model="couponsData.fixed_term" class="form-input" />
                    </view>
                    <text class="form-word-aux">天</text>
                    <text class="form-word-aux-line">不能小于等于0，且必须为整数</text>
                </view>
                <view class="common-form-item">
                    <label class="form-label">活动商品</label>
                    <view class="form-input-inline border-0 radio-list">
                        <uni-data-checkbox v-model="couponsData.goods_type" :localdata="goodsTypeList" @change="goodsType"/>
                    </view>
                    <view class="form-word-aux-line top" v-if="couponsData.goods_type==2||couponsData.goods_type===3">
                        <view class="table-wrap">
                            <view class="table-head">
                                <view class="table-tr">
                                    <view class="table-th" style="flex: 5;">商品名称</view>
                                    <view class="table-th" style="flex: 1;">价格</view>
                                    <view class="table-th" style="flex: 1;">库存</view>
                                    <view class="table-th" style="flex: 1;">操作</view>
                                </view>
                            </view>
                            <view class="table-body">
                                <block v-for="(item, index) in couponsData.goods_list" :key="index">
                                    <view class="table-tr">
                                        <view class="table-td goods-name" style="flex: 5;">{{ item.goods_name }}</view>
                                        <view class="table-td" style="flex: 1;">{{ item.price || '0.00' }}</view>
                                        <view class="table-td" style="flex: 1;">{{ item.goods_stock || 0 }}</view>
                                        <view class="table-td" style="flex: 1;">
                                            <button type="default" class="delete" @click="delGoods(item.sku_id)">删除</button>
                                        </view>
                                    </view>
                                </block>
                                <view class="table-tr table-empty" v-if="!couponsData.goods_list.length">暂无数据，请选择商品数据</view>
                            </view>
                        </view>
                        <button type="default" class="gooods_select" @click="dialogVisible = true">选择商品</button>
                    </view>
					<view class="form-word-aux-line top" v-if="couponsData.goods_type==4||couponsData.goods_type===5">
						<view class="flex items-center">
							<button type="default" class="gooods_select" @click="$refs.couponCategoryPop.open(couponsData.goods_ids_real?couponsData.goods_ids_real.split(','):[])">选择商品分类</button>
							<text class="goods_names">{{couponsData.goods_names}}</text>
						</view>
					</view>
				</view>
				<view class="common-form-item">
				    <label class="form-label">适用场景</label>
				    <view class="form-input-inline border-0 radio-list">
				        <uni-data-checkbox v-model="couponsData.use_channel" :localdata="useChannelList"/>
				    </view>
					<text class="form-word-aux-line">在小程序和pc端商城下单为线上使用，在收银台下单为线下使用。</text>
				</view>
                <view class="common-btn-wrap">
                    <button type="default" class="screen-btn" @click="saveFn">保存</button>
                    <button type="default" @click="backFn">返回</button>
                </view>
            </view>
        </view>
        <stock-goods-dialog v-model="dialogVisible" apiType="spu" @selectGoods="selectGoods" />
		<coupon-category-popup ref="couponCategoryPop" @confirm="goodsCategoryConfirm"/>
    </base-page>
</template>
<script>
import editCoupon from './public/js/edit_coupon.js';
import couponCategoryPopup from '@/components/coupon-category-popup/coupon-category-popup.vue'
export default {
    mixins: [editCoupon]
};
</script>
<style lang="scss" scoped>
    @import './public/css/edit_coupon.scss';
</style>
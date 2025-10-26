<template>
    <base-page>
        <view class="coupons-detail">
            <view class="common-wrap common-form fixd common-scrollbar" v-if="!loading">
                <view class="title-back flex items-center cursor-pointer" @click="backFn">
                    <text class="iconfont iconqianhou1"></text>
                    <text class="left">返回</text>
                    <text class="content">|</text>
                    <text>优惠券详情</text>
                </view>
                <view class="common-title">基本信息</view>
                <view class="flex flex-wrap">
                    <view class="common-form-item">
                        <label class="form-label">优惠券名称：</label>
                        <view class="form-input-inline">{{ couponsData.coupon_name }}</view>
                    </view>
                    <view class="common-form-item">
                        <label class="form-label">优惠券类型：</label>
                        <view class="form-input-inline">{{ couponsData.type == 'reward' ? '满减' : '折扣' }}</view>
                    </view>
                    <view class="common-form-item" v-if="couponsData.type == 'reward'">
                        <label class="form-label">优惠面额：</label>
                        <view class="form-input-inline">￥{{ couponsData.money }}元</view>
                    </view>
                    <view class="common-form-item" v-else>
                        <label class="form-label">优惠券折扣：</label>
                        <view class="form-input-inline">{{ couponsData.discount }}折</view>
                    </view>
                    <view class="common-form-item" v-if="couponsData.type == 'discount' && couponsData.discount_limit != 0">
                        <label class="form-label">最多优惠：</label>
                        <view class="form-input-inline">￥{{ couponsData.discount_limit }}元</view>
                    </view>
                    <view class="common-form-item">
                        <label class="form-label">使用门槛： </label>
                        <view class="form-input-inline">￥{{ couponsData.at_least }}元</view>
                    </view>
                    <view class="common-form-item">
                        <label class="form-label">是否允许直接领取：</label>
                        <view class="form-input-inline">{{ couponsData.is_show === 1 ? '是' : '否' }}</view>
                    </view>
                    <view class="common-form-item">
                        <label class="form-label">发放数量：</label>
                        <view class="form-input-inline">{{ (couponsData.is_show == 0 || couponsData.count == -1) ? '无限制' : couponsData.count + '张' }}</view>
                    </view>
                    <view class="common-form-item">
                        <label class="form-label">最大领取数量：</label>
                        <view class="form-input-inline" v-if="couponsData.is_show == 0 || couponsData.max_fetch == 0">无领取限制</view>
                        <view class="form-input-inline" v-else>{{ couponsData.max_fetch }}张/人</view>
                    </view>
                    <view class="common-form-item">
                        <label class="form-label">有效期：</label>
                        <view class="form-input-inline radio-list" v-if="couponsData.validity_type == 0">{{ couponsData.end_time }}</view>
                        <view class="form-input-inline radio-list" v-else-if="couponsData.validity_type == 1">领取后 {{ couponsData.fixed_term }}天 有效</view>
                        <view class="form-input-inline radio-list" v-else>长期有效</view>
                    </view>
                    <view class="common-form-item">
                        <label class="form-label">使用渠道：</label>
                        <view class="form-input-inline">
                            {{ couponsData.use_channel === 'all' ? '线上线下使用' :couponsData.use_channel === 'online' ?'线上使用':'线下使用' }}
                        </view>
                    </view>
                    <view v-if="couponsData.use_channel != 'online'" class="common-form-item">
                        <label class="form-label">适用门店：</label>
                        <view class="form-input-inline truncate">
                            <text v-if="couponsData.use_store === 'all'">全部门店</text>
                            <text v-else :title="couponsData.use_store_list.map(v=>{return v.store_name}).join('、')">{{ couponsData.use_store_list.map(v=>{return v.store_name}).join('、') }}</text>
                        </view>
                    </view>
                    <view class="common-form-item">
                        <label class="form-label">活动商品：</label>
                        <view class="form-input-inline radio-list">
                            {{couponsData.goods_type == 1 ? '全部商品参与' : couponsData.goods_type == 2 ? '指定商品参与' : '指定不参与商品' }}
                        </view>
                    </view>
                    <view class="common-form-item coupons-img">
                        <label class="form-label">优惠券图片：</label>
                        <view class="form-input-inline upload-box">
                            <view class="upload">
                                <image :src="$util.img(couponsData.image)" mode="heightFix" />
                            </view>
                        </view>
                    </view>
                </view>
                <view class="common-title">数据统计</view>
                <view class="data flex flex-wrap">
                    <view class="data-item">
                        <view class="title">发放数</view>
                        <view class="content">{{ couponsData.count||0 }}</view>
                    </view>
                    <view class="data-item">
                        <view class="title">领取数</view>
                        <view class="content">{{ couponsData.lead_count||0 }}</view>
                    </view>
                    <view class="data-item">
                        <view class="title">使用数</view>
                        <view class="content">{{ couponsData.used_count||0 }}</view>
                    </view>
                </view>
                <view class="common-title mt-20">领取记录</view>
                <view class="record flex">
                    <block v-for="item in statusList">
                        <view :class="{'active':item.value==option.state}" type="default" @click="queryRecord(item.value)">{{ item.label }}</view>
                    </block>
                </view>
                <uniDataTable url="/coupon/storeapi/membercoupon/getReceiveCouponPageList" :option="option" :cols="cols" ref="couponListTable" />
                <block v-if="couponsData.goods_type!=1">
                    <view class="common-title mt-20">{{couponsData.goods_type==2?'指定商品参与':'指定不参与商品'}}</view>
                    <uniDataTable :cols="goodsListCols" :data="couponsData.goods_list" classType />
                </block>
            </view>
        </view>
    </base-page>
</template>
<script>
import couponDetail from './public/js/coupon_detail.js';
import uniDataTable from '@/components/uni-data-table/uni-data-table.vue';
export default {
    components:{
        uniDataTable
    },
    mixins: [couponDetail]
};
</script>
<style lang="scss" scoped>
    @import './public/css/coupon_detail.scss';
</style>
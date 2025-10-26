<template>
	<view class="member-detail-wrap">
		<view class="member-head flex items-center justify-between">
			<text>会员详情</text>
			<text class="iconfont iconguanbi1 cursor-pointer" @click="$emit('close')"></text>
		</view>
		<view class="member-content">
			<view class="content-block">
				<view class="item-img">
					<image mode="aspectFill" v-if="memberInfo && memberInfo.headimg" :src="$util.img(memberInfo.headimg)" @error="headError(memberInfo)"/>
					<image mode="aspectFill" v-else :src="$util.img(defaultImg.head)"/>
				</view>
				<view class="item-content">
					<view class="item-title">
						<view class="item-title-text">{{ memberInfo && memberInfo.nickname ? memberInfo.nickname : '' }}</view>
						<view class="item-label" v-if="memberInfo && memberInfo.member_level && memberInfo.member_level_name">{{ memberInfo.member_level_name }}</view>
					</view>
					<view class="info-list">
						<view class="info-item">手机：{{ memberInfo && memberInfo.mobile ? memberInfo.mobile : '' }}</view>
						<view class="info-item" v-if="memberInfo && memberInfo.sex == 0">性别：未知</view>
						<view class="info-item" v-if="memberInfo && memberInfo.sex == 1">性别：男</view>
						<view class="info-item" v-if="memberInfo && memberInfo.sex == 2">性别：女</view>
						<view class="info-item">生日：{{ memberInfo && memberInfo.birthday ? memberInfo.birthday : '' }}</view>
						<view class="info-item" v-if="memberInfo && memberInfo.member_time">成为会员：{{ $util.timeFormat(memberInfo.member_time) }}</view>
					</view>
				</view>
			</view>

			<view class="content-block account">
				<view class="content-data-item">
					<view class="data-item-title">积分</view>
					<view class="data-item-value">{{ memberInfo && memberInfo.point ? parseInt(memberInfo.point) : '0' }}</view>
					<view class="data-item-action" @click="memberAction('pointList')">查看</view>
				</view>
				<view class="content-data-item">
					<view class="data-item-title">储值余额(元)</view>
					<view class="data-item-value">{{ memberInfo && memberInfo.balance ? memberInfo.balance : '0.00' }}</view>
					<view class="data-item-action" @click="memberAction('balanceList')">查看</view>
				</view>
				<view class="content-data-item">
					<view class="data-item-title">现金余额(元)</view>
					<view class="data-item-value">{{ memberInfo && memberInfo.balance_money ? memberInfo.balance_money : '0.00' }}</view>
				</view>
				<view class="content-data-item">
					<view class="data-item-title">成长值</view>
					<view class="data-item-value">{{ memberInfo && memberInfo.growth ? memberInfo.growth : '0' }}</view>
					<view class="data-item-action" @click="memberAction('growthList')">查看</view>
				</view>
				<view class="content-data-item">
					<view class="data-item-title">优惠券(张)</view>
					<view class="data-item-value">{{ memberInfo && memberInfo.coupon_num ? memberInfo.coupon_num : '0' }}</view>
					<view class="data-item-action" @click="memberAction('couponList')">查看</view>
				</view>
				<view class="content-data-item">
					<view class="data-item-title">卡包</view>
					<view class="data-item-value">{{ memberInfo && memberInfo.card_num ? memberInfo.card_num : '0' }}
					</view>
					<view class="data-item-action" @click="memberAction('cardList')">查看</view>
				</view>
			</view>
			<view class="content-block action">
				<view class="content-data-item" @click="memberAction('memberInfo')">
					<view class="data-item-icon">
						<image mode="aspectFit" src="@/static/member/icon-member-info.png" />
					</view>
					<view class="data-item-value">会员信息</view>
				</view>
				<view class="content-data-item" @click="memberAction('point')">
					<view class="data-item-icon">
						<image mode="aspectFit" src="@/static/member/icon-member-point.png" />
					</view>
					<view class="data-item-value">积分调整</view>
				</view>
				<view class="content-data-item" @click="memberAction('balance')">
					<view class="data-item-icon">
						<image mode="aspectFit" src="@/static/member/icon-member-balance.png" />
					</view>
					<view class="data-item-value">余额充值</view>
				</view>
				<view class="content-data-item" @click="memberAction('sendCoupon')">
					<view class="data-item-icon">
						<image mode="aspectFit" src="@/static/member/icon-member-coupon.png" />
					</view>
					<view class="data-item-value">送优惠券</view>
				</view>
				<view class="content-data-item" @click="memberAction('growth')">
					<view class="data-item-icon">
						<image mode="aspectFit" src="@/static/member/icon-member-growth.png" />
					</view>
					<view class="data-item-value">成长值调整</view>
				</view>
				<view class="content-data-item" @click="memberAction('applyMember')" v-if="memberInfo && !memberInfo.is_member">
					<view class="data-item-icon">
						<image mode="aspectFit" src="@/static/member/icon-member-apply.png" />
					</view>
					<view class="data-item-value">办理会员</view>
				</view>
			</view>
		</view>

		<!-- 会员详情 -->
		<uni-popup ref="memberInfoPop">
			<view class="pop-box memberInfo-box">
				<view class="pop-header">
					<view class="pop-header-text">会员详情</view>
					<view class="pop-header-close" @click="popClose('memberInfo')">
						<text class="iconguanbi1 iconfont"></text>
					</view>
				</view>

				<scroll-view scroll-y="true" class="common-scrollbar pop-content">
					<view class="form-content" v-if="memberInfo">
						<view class="form-item">
							<view class="form-label">
								<text class="required"></text>
								昵称：
							</view>
							<view class="form-inline">
								<input class="form-input" placeholder="请输入会员昵称" v-model="memberInfo.nickname" />
							</view>
						</view>

						<view class="form-item">
							<view class="form-label">
								<text class="required"></text>
								手机号：
							</view>
							<view class="form-inline">
								<input class="form-input" placeholder="请输入手机号" v-model="memberInfo.mobile" maxlength="11" />
							</view>
						</view>

						<view class="form-item">
							<view class="form-label">
								<text class="required"></text>
								会员等级：
							</view>
							<view class="form-inline">
								<select-lay :zindex="10" :value="memberInfo.member_level" name="names" placeholder="请选择会员等级" :options="memberLevelList" @selectitem="selectMemberLevel"/>
							</view>
						</view>

						<view class="form-item">
							<view class="form-label">
								<text class="required"></text>
								性别：
							</view>
							<view class="form-inline">
								<uni-data-checkbox v-model="memberInfo.sex" :localdata="sex"></uni-data-checkbox>
							</view>
						</view>

						<view class="form-item">
							<view class="form-label">
								<text class="required"></text>
								生日：
							</view>
							<view class="form-inline">
								<uni-datetime-picker :end="endTime" v-model="memberInfo.birthday" type="date" :clearIcon="false" />
							</view>
						</view>

						<view class="form-item">
							<view class="form-label">
								<text class="required"></text>
								注册时间：
							</view>
							<view class="form-inline">
								{{ memberInfo && memberInfo.reg_time ? $util.timeFormat(memberInfo.reg_time) : '--' }}
							</view>
						</view>

						<view class="form-item">
							<view class="form-label">
								<text class="required"></text>
								最后访问时间：
							</view>
							<view class="form-inline">
								{{ memberInfo && memberInfo.last_login_time ? $util.timeFormat(memberInfo.last_login_time) : '--' }}
							</view>
						</view>
					</view>
				</scroll-view>
				<view class="pop-bottom">
					<button class="primary-btn" @click="saveMemberInfo">确定</button>
				</view>
			</view>
		</uni-popup>

		<!-- 积分调整 -->
		<uni-popup ref="pointPop">
			<view class="pop-box pointPop-box">
				<view class="pop-header">
					<view class="pop-header-text">调整积分</view>
					<view class="pop-header-close" @click="popClose('point')">
						<text class="iconguanbi1 iconfont"></text>
					</view>
				</view>

				<scroll-view scroll-y="true" class="common-scrollbar pop-content">
					<view class="form-content">
						<view class="form-item">
							<view class="form-label">
								<text class="required"></text>
								当前积分：
							</view>
							<view class="form-inline">{{ memberInfo && memberInfo.point ? memberInfo.point : '0' }}</view>
						</view>

						<view class="form-item">
							<view class="form-label">
								<text class="required"></text>
								调整数额：
							</view>
							<view class="form-inline">
								<input class="form-input" type="number" placeholder="请输入调整数额" v-model="pointData.num" />
								<view class="word-aux">调整数额与当前积分数相加不能小于0</view>
							</view>
						</view>

						<view class="form-item">
							<view class="form-label">
								<text class="required"></text>
								备注：
							</view>
							<view class="form-inline">
								<textarea class="form-textarea" v-model="pointData.desc"/>
							</view>
						</view>
					</view>
				</scroll-view>
				<view class="pop-bottom">
					<button class="primary-btn" @click="savePoint">确定</button>
				</view>
			</view>
		</uni-popup>

		<!-- 发放优惠券 -->
		<uni-popup ref="sendCouponPop">
			<view class="pop-box sendCoupon-box">
				<view class="pop-header">
					<view class="pop-header-text">送优惠券</view>
					<view class="pop-header-close" @click="popClose('sendCoupon')">
						<text class="iconguanbi1 iconfont"></text>
					</view>
				</view>

				<view class="common-scrollbar sendCoupon-content">
					<view class="coupon-table-head">
						<view class="coupon-table-th">优惠券名称</view>
						<view class="coupon-table-th">金额</view>
						<view class="coupon-table-th">有效期</view>
						<view class="coupon-table-th">发放数量</view>
					</view>
					<scroll-view class="coupon-table-body" @scrolltolower="getCouponList()" scroll-y="true">
						<view class="coupon-table-tr" v-for="(item, index) in sendCoupon.list" :key="index">
							<view class="coupon-table-td">{{ item.coupon_name }}</view>
							<view class="coupon-table-td">{{ item.money }}</view>
							<view class="coupon-table-td">{{ item.validity_name }}</view>
							<view class="coupon-table-td">
								<view class="item-num">
									<view class="num-dec" v-on:click="dec(item)">-</view>
									<input class="table-input" type="text" v-model="item.num" />
									<view class="num-inc" v-on:click="inc(item)">+</view>
								</view>
							</view>
						</view>
						<view class="empty" v-if="!sendCoupon.list.length">
							<view class="iconfont iconwushuju"></view>
							<view>暂无数据</view>
						</view>
					</scroll-view>
				</view>
				<view class="pop-bottom">
					<button v-if="sendCoupon.list.length" class="primary-btn" @click="sendCouponFn">发放优惠券</button>
				</view>
			</view>
		</uni-popup>

		<!-- 余额调整 -->
		<uni-popup ref="balancePop">
			<view class="pop-box pointPop-box">
				<view class="pop-header">
					<view class="pop-header-text">调整余额</view>
					<view class="pop-header-close" @click="popClose('balance')">
						<text class="iconguanbi1 iconfont"></text>
					</view>
				</view>

				<scroll-view scroll-y="true" class="common-scrollbar pop-content">
					<view class="form-content">
						<view class="form-item">
							<view class="form-label">
								<text class="required"></text>
								当前余额：
							</view>
							<view class="form-inline">
								{{ memberInfo && memberInfo.balance ? memberInfo.balance : '0.00' }}
							</view>
						</view>

						<view class="form-item">
							<view class="form-label">
								<text class="required"></text>
								调整数额：
							</view>
							<view class="form-inline">
								<input class="form-input" type="number" placeholder="请输入调整数额" v-model="balanceData.num" />
								<view class="word-aux">调整数额与当前储值余额相加不能小于0</view>
							</view>
						</view>

						<view class="form-item">
							<view class="form-label">
								<text class="required"></text>
								备注：
							</view>
							<view class="form-inline">
								<textarea class="form-textarea" v-model="balanceData.desc"></textarea>
							</view>
						</view>
					</view>
				</scroll-view>
				<view class="pop-bottom">
					<button class="primary-btn" @click="saveBalance">确定</button>
				</view>
			</view>
		</uni-popup>

		<!-- 成长值调整 -->
		<uni-popup ref="growthPop">
			<view class="pop-box pointPop-box">
				<view class="pop-header">
					<view class="pop-header-text">调整成长值</view>
					<view class="pop-header-close" @click="popClose('growth')">
						<text class="iconguanbi1 iconfont"></text>
					</view>
				</view>

				<scroll-view scroll-y="true" class="common-scrollbar pop-content">
					<view class="form-content">
						<view class="form-item">
							<view class="form-label">
								<text class="required"></text>
								当前成长值：
							</view>
							<view class="form-inline">{{ memberInfo && memberInfo.growth ? memberInfo.growth : '0' }}</view>
						</view>

						<view class="form-item">
							<view class="form-label">
								<text class="required"></text>
								调整数额：
							</view>
							<view class="form-inline">
								<input class="form-input" type="number" placeholder="请输入调整数额" v-model="growthData.num" />
								<view class="word-aux">调整数额与当前成长值相加不能小于0</view>
							</view>
						</view>

						<view class="form-item">
							<view class="form-label">
								<text class="required"></text>
								备注：
							</view>
							<view class="form-inline">
								<textarea class="form-textarea" v-model="growthData.desc"></textarea>
							</view>
						</view>
					</view>
				</scroll-view>
				<view class="pop-bottom">
					<button class="primary-btn" @click="saveGrowth">确定</button>
				</view>
			</view>
		</uni-popup>

		<!-- 办理会员 -->
		<uni-popup ref="applyMemberPop">
			<view class="pop-box applyMemberPop-box">
				<view class="pop-header">
					<view class="pop-header-text">办理会员</view>
					<view class="pop-header-close" @click="popClose('applyMember')">
						<text class="iconguanbi1 iconfont"></text>
					</view>
				</view>

				<view class="common-scrollbar pop-content">
					<view class="form-content">
						<view class="form-item">
							<view class="form-label">
								<text class="required"></text>
								会员等级：
							</view>
							<view class="form-inline">
								<select-lay :zindex="10" :value="applyMember.level_id" name="names" placeholder="请选择会员等级" :options="memberLevelList" @selectitem="selectMemberLevel"/>
							</view>
						</view>
						<view class="form-item">
							<view class="form-label">
								<text class="required"></text>
								会员卡号：
							</view>
							<view class="form-inline">
								<input class="form-input" type="text" placeholder="请输入会员卡号" v-model="applyMember.member_code" />
								<view class="word-aux">会员卡号为会员唯一编号，若不设置将会自动生成</view>
							</view>
						</view>
					</view>
				</view>
				<view class="pop-bottom">
					<button class="primary-btn" @click="saveApplyMember">确定</button>
				</view>
			</view>
		</uni-popup>

		<!-- 优惠券列表 -->
		<uni-popup ref="couponListPop">
			<view class="pop-box coupon-list-pop-box">
				<view class="pop-header">
					<view class="pop-header-text">优惠券</view>
					<view class="pop-header-close" @click="popClose('couponList')">
						<text class="iconguanbi1 iconfont"></text>
					</view>
				</view>
				<scroll-view scroll-y="true" class="common-scrollbar pop-content">
					<dataTable url="/cashier/storeapi/member/coupon" :cols="couponCols" ref="table" :option="option" :pagesize="pageSize"></dataTable>
				</scroll-view>
			</view>
		</uni-popup>

		<!-- 积分列表 -->
		<uni-popup ref="pointListPop">
			<view class="pop-box coupon-list-pop-box">
				<view class="pop-header">
					<view class="pop-header-text">积分</view>
					<view class="pop-header-close" @click="popClose('pointList')">
						<text class="iconguanbi1 iconfont"></text>
					</view>
				</view>

				<scroll-view scroll-y="true" class="common-scrollbar pop-content">
					<dataTable url="/cashier/storeapi/member/memberaccountlist" :cols="pointCols" ref="table" :option="option" :pagesize="pageSize"></dataTable>
				</scroll-view>
			</view>
		</uni-popup>

		<!-- 余额列表 -->
		<uni-popup ref="balanceListPop">
			<view class="pop-box coupon-list-pop-box">
				<view class="pop-header">
					<view class="pop-header-text">余额</view>
					<view class="pop-header-close" @click="popClose('balanceList')">
						<text class="iconguanbi1 iconfont"></text>
					</view>
				</view>

				<scroll-view scroll-y="true" class="common-scrollbar pop-content">
					<dataTable url="/cashier/storeapi/member/memberaccountlist" :cols="balanceCols" ref="table" :option="option" :pagesize="pageSize"></dataTable>
				</scroll-view>
			</view>
		</uni-popup>

		<!-- 成长值列表 -->
		<uni-popup ref="growthListPop">
			<view class="pop-box coupon-list-pop-box">
				<view class="pop-header">
					<view class="pop-header-text">成长值</view>
					<view class="pop-header-close" @click="popClose('growthList')">
						<text class="iconguanbi1 iconfont"></text>
					</view>
				</view>

				<scroll-view scroll-y="true" class="common-scrollbar pop-content">
					<dataTable url="/cashier/storeapi/member/memberaccountlist" :cols="growthCols" ref="table" :option="option" :pagesize="pageSize"></dataTable>
				</scroll-view>
			</view>
		</uni-popup>

		<!-- 卡项 -->
		<ns-member-card-record ref="memberCardRecord" :option="option"/>
	</view>
</template>

<script>
import dataTable from '@/components/uni-data-table/uni-data-table.vue';
import nsMemberCardRecord from '@/components/ns-member-card-record/ns-member-card-record.vue';
import index from './index.js';
export default {
	components: {
		dataTable,
		nsMemberCardRecord
	},
	mixins: [index]
};
</script>

<style lang="scss" scoped>
	@import './index.scss';
</style>
<template>
	<base-page>
		<view class="uni-flex uni-row height-all page-height member-list-wrap">
			<view class="common-wrap">
				<view class="left-wrap">
					<view class="left-wrap-head">
						<view class="head-text">会员列表</view>
					</view>
					<view class="left-wrap-content">
						<view class="wrap-search-box">
							<view class="wrap-search">
								<input placeholder="请输入会员账号 昵称 手机号" v-model="searchMobile" @blur="searchMember()" placeholder-style="font-size:0.14rem" />
								<text class="iconfont icon31sousuo" @click="searchMember()"></text>
							</view>
						</view>
						<scroll-view :scroll-top="scrollTop" @scroll="scroll" @scrolltolower="getMemberListFn()" scroll-y="true" class="common-scrollbar content-list" v-show="!one_judge">
							<view class="content-item" :class="{ active: memberId == item.member_id }" v-for="(item, index) in memberList" :key="index" @click="selectMember(item.member_id)">
								<view class="item-img">
									<image mode="aspectFill" v-if="item.headimg" :src="$util.img(item.headimg)" @error="headError(item)"/>
									<image mode="aspectFill" v-else :src="$util.img(defaultImg.head)"/>
								</view>
								<view class="item-content">
									<view class="item-title">
										<view class="item-title-text">{{ item.nickname }}</view>
										<view class="item-label">{{ item.member_level_name && item.member_level ? item.member_level_name : '非会员' }}</view>
									</view>
									<view class="item-desc">
										<view>{{ item.mobile }}</view>
										<view>
											余额：
											<text>{{ parseFloat(parseFloat(item.balance) + parseFloat(item.balance_money)).toFixed(2) }}</text>
										</view>
									</view>
								</view>
							</view>
							<view v-if="memberList.length == 0" class="empty">
								<image src="@/static/member/member-empty.png" mode="widthFix" />
								<view class="tips">暂无会员</view>
							</view>
						</scroll-view>
						<view class="add-member">
							<button type="default" class="primary-btn" @click="$refs.addMemberPop.open()">添加会员</button>
						</view>
					</view>
				</view>
				<view class="right-wrap">
					<view class="right-wrap-head">
						<view class="head-text">会员详情</view>
					</view>
					<ns-member-detail v-if="!one_judge && memberId" ref="memberDetail" :member-id="memberId"/>
					<view class="empty" v-else-if="!one_judge && !memberId">
						<image src="@/static/member/member-empty.png" mode="widthFix"/>
						<view class="tips">暂无会员</view>
					</view>
				</view>
			</view>
		</view>

		<!-- 添加会员 -->
		<uni-popup ref="addMemberPop">
			<view class="pop-box add-member-pop-box">
				<view class="pop-header">
					<view class="pop-header-text">添加会员</view>
					<view class="pop-header-close" @click="$refs.addMemberPop.close()">
						<text class="iconguanbi1 iconfont"></text>
					</view>
				</view>
				<view class="common-scrollbar pop-content">
					<view class="form-content">
						<view class="form-item">
							<view class="form-label">
								<text class="required">*</text>
								手机号：
							</view>
							<view class="form-inline search-wrap">
								<input type="number" class="form-input" v-model="addMemberData.mobile" placeholder="请输入会员手机号" />
							</view>
						</view>

						<view class="form-item">
							<view class="form-label">
								<text class="required"></text>
								会员昵称：
							</view>
							<view class="form-inline search-wrap">
								<input type="text" class="form-input" v-model="addMemberData.nickname" placeholder="请输入会员昵称" />
							</view>
						</view>

						<view class="form-item">
							<view class="form-label">
								<text class="required"></text>
								性别：
							</view>
							<view class="form-inline search-wrap">
								<uni-data-checkbox v-model="addMemberData.sex" :localdata="sex"></uni-data-checkbox>
							</view>
						</view>

						<view class="form-item">
							<view class="form-label">
								<text class="required"></text>
								生日：
							</view>
							<view class="form-inline">
								<uni-datetime-picker :end="endTime" v-model="addMemberData.birthday" type="date" :clearIcon="false" />
							</view>
						</view>
					</view>
				</view>
				<view class="pop-bottom">
					<button class="primary-btn" @click="addMemberFn">确定</button>
				</view>
			</view>
		</uni-popup>

	</base-page>
</template>

<script>
import dataTable from '@/components/uni-data-table/uni-data-table.vue';
import nsMemberDetail from '@/components/ns-member-detail/ns-member-detail.vue';
import list from './public/js/list.js';

export default {
	components: { dataTable, nsMemberDetail },
	mixins: [list]
};
</script>

<style>
.member-list-wrap .right-wrap >>> .member-head {
	display: none;
}
</style>

<style lang="scss" scoped>
@import './public/css/member.scss';
</style>

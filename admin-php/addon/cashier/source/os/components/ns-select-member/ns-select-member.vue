<template>
	<view>
		<uni-popup ref="memberPopup" type="center" @maskClick="closedFn">
			<view class="member-inquire-wrap" :class="{ 'exact' : memberSearchWayConfig.way == 'exact','list' : memberSearchWayConfig.way == 'list' }" v-if="memberType == 'login'">
				<view class="member-header">
					<text class="title">{{  memberSearchWayConfig.way == 'exact' ? '会员查询' : '会员列表' }}</text>
					<text class="iconfont iconguanbi1" @click="closedFn"></text>
				</view>

				<view class="member-content" v-if="memberSearchWayConfig.way == 'exact'">
					<image class="member-img" mode="aspectFill" src="@/static/member/head.png" />
					<input type="number" class="member-input" focus placeholder="请输入手机号或手机号后四位" placeholder-style="font-size:0.14rem" v-model="searchText" @confirm="searchMemberByMobileFn()" :focus="inputFocus" @focus="inputFocus = true" @blur="searchMemberInputBlur" />
					<button class="switch primary-btn" @click="searchMemberByMobileFn()">查询</button>
					<view class="function-list">
						<view class="item-wrap" @click="stayTuned">
							<text class="item-icon iconfont iconmenpos"></text>
							<text>刷卡登录</text>
						</view>
						<view class="item-wrap" @click="stayTuned">
							<!-- <image class="item-img" mode="aspectFill" src="@/static/member/head.png" /> -->
							<text class="item-icon iconfont iconsaomiaoerweima"></text>
							<text>扫码登录</text>
						</view>
						<view class="item-wrap" @click="stayTuned">
							<text class="item-icon iconfont iconhuaxiangfenxi"></text>
							<text>人脸登录</text>
						</view>
						<view class="item-wrap" @click="memberType = 'register'">
							<text class="item-icon iconfont iconhuiyuanzhucedengluguanli"></text>
							<text>会员注册</text>
						</view>
					</view>
				</view>

				<view class="member-content" v-if="memberSearchWayConfig.way == 'list'">
					<view class="search-warp">
						<view class="search-input">
							<input focus placeholder="可查询会员账号、手机号、昵称" placeholder-style="font-size:0.14rem" v-model="searchText" @confirm="searchMemberByList()" :focus="inputFocus" @focus="inputFocus = true" @blur="searchMemberInputBlur" />
							<button class="switch primary-btn" @click="searchMemberByList()">查询 [Enter]</button>
							<button class="default-btn" plain="true" @click="memberType = 'register'">添加会员</button>
						</view>
					</view>
					<scroll-view @scrolltolower="getMemberListFn()" scroll-y="true" class="member-list">
						<view :class="['member-item', { active: item.member_id == memberId }]" v-for="(item, index) in memberList" :key="index" @click="getMemberInfo(item.member_id)">
							<image class="item-img" mode="aspectFill" v-if="item.headimg" :src="$util.img(item.headimg)" @error="item.headimg = defaultImg.head"/>
							<image class="item-img" mode="aspectFill" v-else :src="$util.img(defaultImg.head)"/>
							<view class="item-content">
								<view class="name">
									<text :title="item.nickname">{{ item.nickname }}</text>
								</view>
								<view class="phone">手机号：{{ item.mobile }}</view>
								<view class="other">
									<view>余额：{{ parseFloat(parseFloat(item.balance) + parseFloat(item.balance_money)).toFixed(2) }}</view>
								</view>
							</view>
						</view>
						<view v-show="memberList.length == 0" class="empty">
							<image :src="$util.img('public/uniapp/cashier/member-empty.png')" mode="widthFix"/>
							<view class="tips">暂无会员</view>
						</view>
					</scroll-view>
				</view>

			</view>
			
			<view class="member-entering-wrap" v-if="memberType == 'register'">
				<view class="header">
					<text class="iconfont iconqianhou1" @click="memberType = 'login'"></text>
					<text class="title">录入会员</text>
					<text class="iconfont iconguanbi1" @click="closedFn"></text>
				</view>
				<view class="form-content">
					<view>
						<view class="form-item">
							<view class="form-label">
								<text class="required">*</text>
								手机号：
							</view>
							<view class="form-inline">
								<input type="number" class="form-input" v-model="memberData.mobile" placeholder="请输入会员手机号" />
							</view>
						</view>
			
						<view class="form-item">
							<view class="form-label">
								<text class="required"></text>
								会员名称：
							</view>
							<view class="form-inline">
								<input type="text" class="form-input" v-model="memberData.nickname" placeholder="请输入会员昵称" />
							</view>
						</view>
			
						<view class="form-item">
							<view class="form-label">
								<text class="required"></text>
								性别：
							</view>
							<view class="form-inline">
								<uni-data-checkbox v-model="memberData.sex" :localdata="sex"/>
							</view>
						</view>
			
						<view class="form-item">
							<view class="form-label">
								<text class="required"></text>
								生日：
							</view>
							<view class="form-inline">
								<uni-datetime-picker v-model="memberData.birthday" type="date" :clearIcon="false" @change="changeTime" />
							</view>
						</view>
			
						<view class="form-item" v-if="memberLevelList.length">
							<view class="form-label">
								<text class="required"></text>
								会员等级：
							</view>
							<view class="form-inline">
								<select-lay :zindex="10" :value="memberData.member_level" name="names" placeholder="请选择会员等级" :options="memberLevelList" @selectitem="selectMemberLevel"/>
							</view>
						</view>
					</view>
					<view class="btn-wrap">
						<button type="primary" class="primary-btn" @click="addMemberFn">确定录入</button>
					</view>
				</view>
			</view>
		</uni-popup>

		<uni-popup ref="emptyPopup" type="center">
			<view class="member-empty">
				<view class="head">提示</view>
				<view class="content">未找到顾客{{searchText}}</view>
				<view class="btn-wrap">
					<button class="close-btn" @click="$refs.emptyPopup.close()">关闭</button>
					<button class="primary-btn" v-if="isPhone" @click="memberEmptyRegister()">注册</button>
				</view>
			</view>
		</uni-popup>

	</view>
</template>

<script>
	import index from './index.js';
	export default {
		mixins: [index]
	};
</script>

<style lang="scss" scoped>
	@import './index.scss';
</style>
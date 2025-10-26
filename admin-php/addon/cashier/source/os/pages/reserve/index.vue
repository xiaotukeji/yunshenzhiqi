<template>
	<base-page>
		<view class="uni-flex uni-row height-all">
			<view class="common-wrap uni-flex uni-column" style="-webkit-flex: 1;flex: 1;">
				<view class="common-tab-wrap" id="tab">
					<view class="tab-item" :class="{ 'active-bar': active == 0 }" @click="switchTab(0)">
						<text class="text">预约看板</text>
					</view>
					<view class="tab-item" :class="{ 'active-bar': active == 1 }" @click="switchTab(1)">
						<text class="text">预约列表</text>
					</view>
					<view class="active" :style="activeStyle"></view>
				</view>

				<swiper :interval="3000" :duration="300" :current="active" @change="swiperChange">
					<!-- 预约看板 -->
					<swiper-item>
						<view class="swiper-item common-scrollbar">
							<view class="uni-flex panel-head">
								<button type="default" class="primary-btn" @click="addYuyue()">添加预约</button>
								<view class="status uni-flex">
									<block v-for="(item, index) in status" :key="index">
										<view class="color" :class="item.state"></view>
										<view>{{ item.name }}</view>
									</block>
								</view>
							</view>

							<view class="panel-body">
								<view class="head-time uni-flex">
									<view @click="prevWeek()" class="item">
										<view class="iconfont iconqianhou1"></view>
									</view>
									<view class="time-box">{{ weekDate.start }} - {{ weekDate.end }}</view>
									<view @click="nextWeek()" class="item">
										<view class="iconfont iconqianhou2"></view>
									</view>
									<!-- <view class="head-time-switch">
										<view :class="yuYueDateType == 'week' ? 'active' : ''" @click="yuYueDateType = 'week'">周</view>
										<view :class="yuYueDateType == 'month' ? 'active' : ''" @click="yuYueDateType = 'month'">月</view>
									</view> -->
								</view>
								<block v-if="yuYueDateType == 'week'">
									<view class="head uni-flex">
										<view v-for="(item, index) in weeks" class="item" :key="index">
											<button type="default" class="default-btn" :class="{ active: item.currday }">
												{{ item.week }}
												<text>{{ item.date }}</text>
											</button>
										</view>
									</view>
									<view class="body uni-flex">
										<scroll-view scroll-y="true" @scrolltolower="getReserve(index)" class="common-scrollbar item" v-for="(item, index) in weeks" :key="index">
											<block v-if="item.data">
												<view class="panel-item" :class="reserve_item.reserve_state" v-for="(reserve_item, reserve_index) in item.data.list" :key="reserve_index">
													<view class="username">{{ reserve_item.nickname }}</view>
													<view class="time" :class="reserve_item.reserve_state">{{ $util.timeFormat(reserve_item.reserve_time, 'm-d H:i') }}</view>
													<view class="service" v-for="(server_item, server_index) in reserve_item.item" :key="server_index" @click="yuyueEvent('info', reserve_item)">{{ server_item.goods_name }}</view>
													<uni-dropdown>
														<view class="action" slot="dropdown-link">
															<text class="iconfont icongengduo"></text>
														</view>
														<view slot="dropdown">
															<view class="dropdown-menu">
																<view class="menu-item" @click="yuyueEvent('info', reserve_item)">详情</view>
																<view class="menu-item" v-for="(menu_item, menu_index) in operation[reserve_item.reserve_state]" :key="menu_index" @click="yuyueEvent(menu_item.event, reserve_item)">
																	{{ menu_item.title }}
																</view>
																<view class="arrow"></view>
															</view>
														</view>
													</uni-dropdown>
												</view>
												<view style="height: 1.5rem;"></view>
											</block>
										</scroll-view>
									</view>
								</block>
								<block v-if="yuYueDateType == 'month'">
									<view class="head uni-flex">
										<view v-for="(item, index) in week" class="item" :key="index">
											<button type="default" class="default-btn">{{ item }}</button>
										</view>
									</view>
								</block>
							</view>
						</view>
					</swiper-item>
					<!-- 预约列表 -->
					<swiper-item>
						<view class="yuyuelist">
							<view class="yuyuelist-box">
								<view class="yuyuelist-left">
									<view class="yuyue-title">预约客户</view>
									<view class="yuyue-search">
										<view class="search">
											<text class="iconfont icon31sousuo" @click="searchYuyueList()"></text>
											<input type="text" v-model="yuyueSearchText" placeholder="请输入会员手机号" />
										</view>
									</view>
									<scroll-view @scrolltolower="getYuyueList()" scroll-y="true" class="yuyue-list-scroll all-scroll">
										<view class="item" v-for="(item, index) in yuyueList" :key="index" @click="selectYuyue(item.reserve_id)" :class="{ active: item.reserve_id == reserveId }">
											<view class="item-head">
												<image mode="aspectFill" v-if="item.headimg" :src="$util.img(item.headimg)" @error="item.headimg = defaultImg.head"/>
												<image mode="aspectFill" v-else :src="$util.img(defaultImg.head)"/>
												<view class="item-right">
													<view class="yuyue-name" v-if="item.nickname">{{ item.nickname }}</view>
													<view class="yuyue-desc">{{ item.mobile }}</view>
												</view>
												<text>{{ item.reserve_state_name }}</text>
											</view>
											<view class="item-common">预约时间：{{ $util.timeFormat(item.create_time) }}
											</view>
											<view class="item-common yuyue-project">
												预约项目：
												<block v-for="(sItem, sIndex) in item.item" :key="sIndex">
													{{ sItem.goods_name }}{{ sIndex != item.item.length - 1 ? '；' : '' }}
												</block>
											</view>
										</view>
										<view v-if="yuyueList.length == 0" class="empty">
											<image src="@/static/member/member-empty.png" mode="widthFix"/>
											<view class="tips">暂无预约客户</view>
										</view>
									</scroll-view>
								</view>
								<view class="yuyuelist-right" v-if="yuyueInfo">
									<view class="yuyue-title">预约详情</view>
									<view class="yuyue-information common-scrollbar">
										<view class="title">预约信息</view>
										<view class="information-box">
											<view class="box-left">
												<view class="information">
													<view>预约客户：</view>
													<view>{{ yuyueInfo.nickname }}</view>
												</view>
												<view class="information">
													<view>客户手机号：</view>
													<view>{{ yuyueInfo.mobile }}</view>
												</view>
												<view class="information">
													<view>预约门店：</view>
													<view>{{ yuyueInfo.store_name }}</view>
												</view>
												<view class="information">
													<view>预约状态：</view>
													<view>{{ yuyueInfo.reserve_state_name }}</view>
												</view>
												<view class="information">
													<view>预约到店时间：</view>
													<view>{{ $util.timeFormat(yuyueInfo.reserve_time, 'Y-m-d H:i') }}
													</view>
												</view>
												<view class="information">
													<view>预约时间：</view>
													<view>{{ $util.timeFormat(yuyueInfo.create_time) }}</view>
												</view>
												<view class="information">
													<view>备注：</view>
													<view>{{ yuyueInfo.desc ? yuyueInfo.desc : '--' }}</view>
												</view>
											</view>
										</view>

										<view class="title title2">预约内容</view>
										<view class="table" v-if="yuyueInfo">
											<view class="table-th table-all">
												<view class="table-td" style="width:50%">项目</view>
												<view class="table-td" style="width:50%">员工</view>
											</view>
											<scroll-view class="table-tb" scroll-y="true">
												<view class="table-tr table-all" v-for="(item, index) in yuyueInfo.item" :key="index">
													<view class="table-td" style="width:50%">{{ item.goods_name }}
													</view>
													<view class="table-td" style="width:50%">
														{{ item.uid ? item.username : '--' }}
													</view>
												</view>
											</scroll-view>
										</view>
										<view class="button-box flex items-center justify-end" v-if="yuyueInfo && operation[yuyueInfo.reserve_state]">
											<button class="default-btn" v-for="(menu_item, menu_index) in operation[yuyueInfo.reserve_state]" :key="menu_index" @click="yuyueEvent(menu_item.event, yuyueInfo)">{{ menu_item.title }}</button>
										</view>
									</view>
								</view>
								<view class="yuyuelist-right empty" v-else>
									<image src="@/static/cashier/cart_empty.png" mode="widthFix"/>
									<view class="tips">暂无预约信息</view>
								</view>
							</view>
						</view>
					</swiper-item>
				</swiper>
			</view>
		</view>

		<!-- 添加/修改预约 -->
		<uni-popup ref="addYuyuePop" :maskClick="false">
			<view class="pop-box">
				<view class="pop-header">
					<view class="pop-header-text">{{ yuYueData.reserve_id ? '修改' : '添加' }}预约</view>
					<view class="pop-header-close" @click="closeYuyuePop">
						<text class="iconguanbi1 iconfont"></text>
					</view>
				</view>

				<scroll-view scroll-y="true" class="common-scrollbar pop-content">
					<view class="form-content">
						<view class="form-item" v-if="!yuYueData.reserve_id">
							<view class="form-label">
								<text class="required">*</text>
								手机号：
							</view>
							<view class="form-inline search-wrap">
								<input type="number" class="form-input" v-model="searchMobile" placeholder="请输入客户手机号" />
								<text class="iconfont icon31sousuo" @click="searchMember"></text>
							</view>
						</view>
						<view class="form-item">
							<view class="form-label">
								<text class="required">*</text>
								客户：
							</view>
							<view class="form-inline">
								<view class="member-info" v-if="yuYueData.member_id">
									<image :src="$util.img(yuYueData.member.headimg, { size: 'small' })" mode="widthFix" />
									<view class="info">
										<view class="name">{{ yuYueData.member.nickname }}</view>
										<view>
											<text>手机号：{{ yuYueData.member.mobile }}</text>
										</view>
									</view>
								</view>
							</view>
						</view>
						<view class="form-item">
							<view class="form-label">
								<text class="required">*</text>
								到店时间：
							</view>
							<view class="form-inline">
								<uni-datetime-picker :start="toDay" v-model="yuYueData.date" type="date" :clearIcon="false" @change="changeYuyueTime" />
							</view>
							<view class="form-inline">
								<select-lay :zindex="10" :value="yuYueData.time" name="names" placeholder="请选择到店时间" :options="yuYueTime" @selectitem="selectYuYueTime"/>
							</view>
						</view>
						<view class="form-item">
							<view class="form-label">
								<text class="required">*</text>
								预约门店：
							</view>
							<view class="form-inline">{{ globalStoreInfo.store_name }}</view>
						</view>
						<view class="form-item">
							<view class="form-label">
								<text class="required">*</text>
								项目：
							</view>
							<view>
								<view class="table">
									<view class="table-tr table-head">
										<view class="table-th">预约项目</view>
										<view class="table-th">员工</view>
										<view class="table-th">操作</view>
									</view>

									<view class="table-content table-tr" v-for="(goods_item, goods_index) in yuYueData.goods" :key="goods_index">
										<view class="table-td">
											<uni-dropdown>
												<view class="action" slot="dropdown-link">
													<view class="service-item">
														<view class="info" v-if="goods_item.goods_id">
															<view class="title">{{ goods_item.goods_name }}</view>
															<view class="desc">项目时长：{{ goods_item.service_length }}分钟 ￥{{ goods_item.price }}</view>
														</view>
														<view class="info" v-else>请选择项目</view>
														<text class="iconfont iconsanjiao_xia"></text>
													</view>
												</view>
												<view slot="dropdown">
													<view class="dropdown-content-box">
														<view class="select-service">
															<div class="service-wrap">
																<div class="flex-wrap">
																	<div class="item" v-for="(item, index) in goodsList" :key="index" @click="selectGoods(item, goods_index)">
																		<div class="title">{{ item.goods_name }}</div>
																		<div class="desc">项目时长：{{ item.service_length }}分钟 ￥{{ item.price }}</div>
																	</div>
																</div>
															</div>
														</view>
														<view class="arrow"></view>
													</view>
												</view>
											</uni-dropdown>
										</view>
										<view class="table-td">
											<uni-dropdown>
												<view class="action" slot="dropdown-link">
													<view class="service-item" @click="loadServicer(goods_index)">
														<view class="info">
															<view class="title" v-if="goods_item.uid && goods_item.uid > 0">
																{{ goods_item.username }}
															</view>
															<view class="title" v-else>不选择员工</view>
														</view>
														<text class="iconfont iconsanjiao_xia"></text>
													</view>
												</view>
												<view slot="dropdown">
													<view class="dropdown-content-box">
														<div class="select-servicer">
															<div class="select-item">
																<div class="title" @click="selectServicer({ uid: 0, username: '' }, goods_index)">不选择员工</div>
															</div>
															<div class="select-item" v-for="(item, index) in servicerList" :key="index" @click="selectServicer(item, goods_index)">
																<div class="title">{{ item.username }}</div>
															</div>
														</div>
														<view class="arrow"></view>
													</view>
												</view>
											</uni-dropdown>
										</view>
										<view class="table-td">
											<view class="action-btn" @click="deleteService(goods_index)">删除</view>
										</view>
									</view>
								</view>
								<button class="primary-btn select-btn" @click="addService">添加项目</button>
							</view>
						</view>
						<view class="form-item">
							<view class="form-label">
								<text class="required"></text>
								备注：
							</view>
							<view class="form-inline">
								<textarea class="form-textarea" v-model="yuYueData.desc"></textarea>
							</view>
						</view>
					</view>
				</scroll-view>
				<view class="pop-bottom">
					<button class="primary-btn" @click="yuYueSubmit">确定</button>
				</view>
			</view>
		</uni-popup>

		<!-- 预约详情 -->
		<uni-popup ref="yuyuePop">
			<view class="pop-box yuyue-info">
				<view class="pop-header">
					<view class="pop-header-text">预约详情</view>
					<view class="pop-header-close" @click="$refs.yuyuePop.close()">
						<text class="iconguanbi1 iconfont"></text>
					</view>
				</view>
				<scroll-view scroll-y="true" class="common-scrollbar pop-content">
					<view class="yuyue-pop form-content" v-if="yuYueDetail">
						<view class="form-item">
							<view class="form-label">预约客户：</view>
							<view class="form-inline search-wrap">
								<text>{{ yuYueDetail.member.nickname }}</text>
							</view>
						</view>

						<view class="form-item">
							<view class="form-label">客户手机号：</view>
							<view class="form-inline search-wrap">
								<text>{{ yuYueDetail.member.mobile }}</text>
							</view>
						</view>

						<view class="form-item">
							<view class="form-label">预约门店：</view>
							<view class="form-inline search-wrap">{{ yuYueDetail.store_name }}</view>
						</view>

						<view class="form-item">
							<view class="form-label">预约状态：</view>
							<view class="form-inline search-wrap">{{ yuYueDetail.reserve_state_name }}</view>
						</view>

						<view class="form-item">
							<view class="form-label">预约到店时间：</view>
							<view class="form-inline search-wrap">
								{{ $util.timeFormat(yuYueDetail.reserve_time, 'Y-m-d H:i') }}
							</view>
						</view>

						<view class="form-item">
							<view class="form-label">创建时间：</view>
							<view class="form-inline search-wrap">
								{{ $util.timeFormat(yuYueDetail.create_time, 'Y-m-d H:i:s') }}
							</view>
						</view>

						<view class="form-item">
							<view class="form-label">预约项目：</view>
							<scroll-view scroll-y="true" class="form-inline search-wrap make-server">
								<view class="table-container">
									<view class="thead">
										<view class="th">
											<view class="content">项目</view>
											<view class="content">员工</view>
											<view class="content">时长</view>
										</view>
									</view>
									<view class="tbody">
										<view class="tr" v-for="(item, index) in yuYueDetail.item" :key="index">
											<view class="td">
												<view class="content">{{ item.goods_name }}</view>
												<view class="content">{{ item.username ? item.username : '--' }}</view>
												<view class="content">{{ item.service_length }}分钟</view>
											</view>
										</view>
									</view>
								</view>
							</scroll-view>
						</view>

						<view class="form-item">
							<view class="form-label">备注：</view>
							<view class="form-inline search-wrap">{{ yuYueDetail.remark ? yuYueDetail.remark : '--' }}
							</view>
						</view>
					</view>
				</scroll-view>
				<view class="pop-bottom"><button class="primary-btn" @click="$refs.yuyuePop.close()">确定</button></view>
			</view>
		</uni-popup>

		<ns-loading :layer-background="{ background: 'rgba(255,255,255,.6)' }" :default-show="false" ref="loading"></ns-loading>

		<!-- #ifdef APP-PLUS -->
		<ns-update></ns-update>
		<!-- #endif -->
	</base-page>
</template>

<script>
	import uniDatetimePicker from '@/components/uni-datetime-picker/uni-datetime-picker.vue';
	import selectLay from '@/components/select-lay/select-lay.vue';
	import index from './public/js/index.js';

	export default {
		components: {
			uniDatetimePicker,
			selectLay
		},
		mixins: [index]
	};
</script>
<style scoped>
	.table-content>>>.tr .td .content.action {
		overflow: unset;
	}

	.form-inline>>>.uni-icons {
		height: 0.3rem;
	}
</style>

<style lang="scss" scoped>
	@import './public/css/index.scss';
</style>
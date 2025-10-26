<template>
	<view class="container">
		<uni-popup ref="memberCardPopup">

			<view class="pop-box member-info-wrap">

				<view class="pop-header">
					<view class="pop-header-text">会员卡项</view>
					<view class="pop-header-close" @click="$refs.memberCardPopup.close()">
						<text class="iconguanbi1 iconfont"></text>
					</view>
				</view>
				<view class="info-wrap" v-if="globalMemberInfo">
					<view class="headimg-content">
						<view class="headimg">
							<image :src="globalMemberInfo.headimg ? $util.img(globalMemberInfo.headimg) : $util.img(defaultImg.head)" @error="globalMemberInfo.headimg = defaultImg.head"/>
						</view>
						<view class="header-info">
							<view class="name">
								{{ globalMemberInfo.nickname }}
								<text v-if="globalMemberInfo.member_level">{{ globalMemberInfo.member_level_name }}</text>
							</view>
							<view class="header-info-item">
								<view>电话：{{ globalMemberInfo.mobile }}</view>
								<view>性别：{{ globalMemberInfo.sex == 0 ? '未知' : globalMemberInfo.sex == 1 ? '男' : '女' }}</view>
								<view>生日：{{ globalMemberInfo.birthday }}</view>
								<view>注册时间：{{ globalMemberInfo.reg_time | timeFormat }}</view>
							</view>
						</view>
					</view>
					<view class="member-card-wrap">
						<view class="card-wrap">
							<scroll-view scroll-y="true" class="card-list" @scrolltolower="getMemberCard()">
								<block v-if="memberCardData.list.length">
									<view class="card-item" :class="{ active: memberCardData.index == index }" v-for="(item, index) in memberCardData.list" :key="index" @click="selectMemberCard(item, index)">
										<view class="card-name">{{ item.goods_name }}</view>
										<view class="info">
											<view v-if="item.total_num > 0">可用{{ item.total_num - item.total_use_num }}次</view>
											<view v-else>不限次</view>
											<view v-if="item.end_time > 0">至{{ $util.timeFormat(item.end_time, 'Y/m/d') }}</view>
											<view v-else>长期有效</view>
										</view>
									</view>
								</block>
								<view v-else class="empty">
									<image src="@/static/card/card_empty.png" mode="widthFix"/>
									<view class="tips">暂无可用卡项</view>
								</view>
							</scroll-view>
							<view class="item-list">
								<view class="title">
									<view>可用服务/商品</view>
									<view v-if="memberCardData.currData.card_type == 'commoncard'">
										<text>以下服务/商品剩余可用</text>
										<text class="num">{{ memberCardData.currData.total_num - memberCardData.currData.total_use_num }}</text>
										<text>次</text>
									</view>
								</view>
								<scroll-view scroll-y="true" class="item-wrap">
									<view class="uni-flex justify-between content" v-if="memberCardData.currData.item_list">
										<view class="card-item" :class="{
												active: memberCardData.selected['item_' + item.item_id],
												'not-select': !checkStatus(item) && !memberCardData.selected['item_' + item.item_id]
											}" @click="selectMemberCardItem(item, index)" v-for="(item, index) in memberCardData.currData.item_list">
											<view class="image">
												<image v-if="item.sku_image == '@/static/goods/goods.png'" src="@/static/goods/goods.png" mode="widthFix"/>
												<image v-else :src="$util.img(item.sku_image.split(',')[0], { size: 'small' })" @error="item.sku_image = '@/static/goods/goods.png'" mode="widthFix"/>
											</view>
											<view class="info">
												<view>
													<view class="name">
														<text class="tag">{{ item.is_virtual ? '服务' : '商品' }}</text>
														<text>{{ item.sku_name }}</text>
													</view>
													<block v-if="memberCardData.currData.card_type != 'commoncard'">
														<view class="num" v-if="item.num > 0">剩余可用{{ item.num - item.use_num }}次</view>
														<view class="num" v-else>不限次</view>
													</block>
												</view>
												<view class="action-wrap">
													<view class="price">
														<text class="util">￥</text>
														{{ item.price }}
													</view>
													<view class="number-wrap" v-if="memberCardData.selected['item_' + item.item_id]">
														<text class="iconfont iconjian" @click.stop="itemDec(memberCardData.selected['item_' + item.item_id])"></text>
														<input type="number" v-model="memberCardData.selected['item_' + item.item_id].input_num" />
														<text class="iconfont iconjia" @click.stop="itemInc(memberCardData.selected['item_' + item.item_id])"></text>
													</view>
												</view>
											</view>
										</view>
									</view>
									<view class="empty" v-else>
										<image src="@/static/goods/goods_empty.png" mode="widthFix"/>
										<view class="tips">暂无相关数据</view>
									</view>
								</scroll-view>
								<view class="button-wrap">
									<button type="default" class="primary-btn" :disabled="memberCardData.itemIndex == -1" @click="selectGoods()">加入购物车</button>
								</view>
							</view>
						</view>
					</view>
				</view>

			</view>
		</uni-popup>
	</view>
</template>

<script>
	import uniDataCheckbox from '@/components/uni-data-checkbox/uni-data-checkbox.vue';
	import UniPopup from "../uni-popup/uni-popup";
	import index from './index.js';

	export default {
		name: 'nsMember',
		components: {
			UniPopup,
			uniDataCheckbox
		},
		mixins: [index]
	};
</script>

<style lang="scss" scoped>
	@import './index.scss';
</style>
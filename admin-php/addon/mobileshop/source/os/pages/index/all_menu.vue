<template>
	<view>
		<block v-if="handleMenu.length">
			<view class="menu_item" v-for="(item, index) in handleMenu" :key="index">
				<view class="menu_title">
					<text class="line color-base-bg margin-right"></text>
					{{ item.title }}
				</view>
				<view class="menu_content">
					<uni-grid :column="4" :showBorder="!1">
						<uni-grid-item v-for="(menuItem, menuIndex) in item.menu" :key="menuIndex">
							<view @click="$util.redirectTo(menuItem.page)" class="grid_item">
								<image class="image" :src="$util.img(menuItem.img)" mode="aspectFit" />
								<view class="text">{{ menuItem.title }}</view>
							</view>
						</uni-grid-item>
					</uni-grid>
				</view>
			</view>
		</block>
		<ns-empty v-else></ns-empty>
		
		<diy-bottom-nav :link-index='2'></diy-bottom-nav>
		<loading-cover ref="loadingCover"></loading-cover>
	</view>
</template>

<script>
	import {getUserPermission} from '@/api/user'
	import uniGrid from '@/components/uni-grid/uni-grid.vue';
	import uniGridItem from '@/components/uni-grid-item/uni-grid-item.vue';
	import diyBottomNav from '@/components/diy-bottom-nav/diy-bottom-nav.vue';
	import uCharts from '@/components/u-charts/u-charts.vue';
	export default {
		data() {
			return {
				permission: [],
				menuList: [
					{
						title: '店铺经营',
						menu: [
							{
								page: '/pages/goods/edit/index',
								img: 'public/uniapp/shop_uniapp/index/manage_good_send.png',
								name: 'PHYSICAL_GOODS_ADD',
								title: '商品发布'
							},
							{
								page: '/pages/goods/list',
								img: 'public/uniapp/shop_uniapp/index/manage_good.png',
								name: 'GOODS_MANAGE',
								title: '商品管理'
							},
							{
								page: '/pages/order/list',
								img: 'public/uniapp/shop_uniapp/index/manage_order.png',
								name: 'ORDER_MANAGE',
								title: '订单管理'
							},
							{
								page: '/pages/member/list',
								img: 'public/uniapp/shop_uniapp/index/member_card.png',
								name: 'MEMBER_LIST',
								title: '会员管理'
							},
							{
								page: '/pages/invoices/invoices',
								img: 'public/uniapp/shop_uniapp/index/invoice_setting.png',
								name: 'INVOICE_LIST',
								title: '发票管理'
							},
							{
								page: '/pages/storemanage/storemanage',
								img: 'public/uniapp/shop_uniapp/index/store_setting.png',
								name: 'STORE_LIST',
								title: '门店管理'
							}
						]
					},
					{
						title: '财务管理',
						menu: [
							{
								page: '/pages/property/dashboard/index',
								img: 'public/uniapp/shop_uniapp/index/finance_survey.png',
								name: 'ACCOUNT_DASHBOARD_INDEX',
								title: '财务概况'
							},
							{
								page: '/pages/property/withdraw/list',
								img: 'public/uniapp/shop_uniapp/index/tixian.png',
								name: 'MEMBER_WITHDRAW_LIST',
								title: '会员提现'
							},
							{
								page: '/pages/property/settlement/list_store',
								img: 'public/uniapp/shop_uniapp/index/store_jiesuan.png',
								name: 'ADDON_STORE_SHOP_STORE_SETTLEMENT',
								title: '门店结算'
							}
						]
					},
					{
						title: '营业数据',
						menu: [
							{
								page: '/pages/statistics/transaction',
								img: 'public/uniapp/shop_uniapp/index/tongji_jiaoyi.png',
								name: 'STAT_ORDER',
								title: '交易数据'
							},
							{
								page: '/pages/statistics/goods',
								img: 'public/uniapp/shop_uniapp/index/tongji_good.png',
								name: 'STAT_GOODS',
								title: '商品数据'
							},
							{
								page: '/pages/statistics/member',
								img: 'public/uniapp/shop_uniapp/index/tongji_shop.png',
								name: 'STAT_MEMBER',
								title: '会员数据'
							},
							{
								page: '/pages/statistics/store',
								img: 'public/uniapp/shop_uniapp/index/tongji_shop.png',
								name: 'STAT_STORE',
								title: '门店数据'
							},
							{
								page: '/pages/statistics/visit',
								img: 'public/uniapp/shop_uniapp/index/tongji_member.png',
								name: 'STAT_VISIT',
								title: '流量数据'
							}
						]
					},
					{
						title: '店铺设置',
						menu: [
							{
								page: '/pages/my/shop/config',
								img: 'public/uniapp/shop_uniapp/index/set_shop.png',
								name: 'SHOP_CONFIG',
								title: '店铺信息'
							},
							{
								page: '/pages/my/user/user',
								img: 'public/uniapp/shop_uniapp/index/set_member.png',
								name: 'USER_LIST',
								title: '用户管理'
							},
							{
								page: '/pages/my/statistics',
								img: 'public/uniapp/shop_uniapp/index/set_jiaoyi.png',
								name: 'ORDER_CONFIG_SETTING',
								title: '交易设置'
							},
							{
								page: '/pages/goods/config',
								img: 'public/uniapp/shop_uniapp/index/goods_setting.png',
								name: 'CONFIG_BASE_GOODS',
								title: '商品设置'
							},
							{
								page: '/pages/my/shop/contact',
								img: 'public/uniapp/shop_uniapp/index/set_address.png',
								name: 'SHOP_CONTACT',
								title: '联系地址'
							},
							{
								page: '/pages/verify/index',
								img: 'public/uniapp/shop_uniapp/index/verify.png',
								name: 'ORDER_VERIFY_CARD',
								title: '核销台'
							}
						]
					}
				]
			};
		},
		components: {
			uniGrid,
			uniGridItem,
			uCharts,
			diyBottomNav
		},
		onLoad() {
			if (uni.getStorageSync('menuPermission')){
				this.permission = uni.getStorageSync('menuPermission');
			}
			this.getPermission();
		},
		computed:{
			handleMenu(){
				if (this.permission.length == 0) return this.menuList;
				
				let menuList = [];
				this.menuList.forEach((item, index) => {
					let data = {title: item.title, menu: []};
					item.menu.forEach((menuItem, menuIndex) => {
						if (this.menuAuth(menuItem.name)) data.menu.push(menuItem);
					})
					if (data.menu.length) menuList.push(data);
				})
				return menuList;
			}
		},
		methods: {
			getPermission() {
				getUserPermission().then(res=>{
					if (res.code == 0) {
						this.permission = res.data;
						uni.setStorageSync('menuPermission', res.data);
						this.$refs.loadingCover.hide();
					}
				})
			},
			menuAuth(name) {
				return this.permission.length == 0 || this.$util.inArray(name, this.permission) != -1;
			}
		}
	};
</script>

<style lang="scss">
	page {
		overflow: auto !important;
	}

	.menu_item {
		background-color: #fff;
		padding: 25rpx $margin-both 0;

		.menu_title {
			font-size: $font-size-toolbar;
			font-weight: bold;
			margin-bottom: 10rpx;

			.line {
				display: inline-block;
				height: 28rpx;
				width: 4rpx;
				border-radius: 4rpx;
			}
		}

		.menu_content {
			.grid_item {
				text-align: center;

				.image {
					width: 50rpx;
					height: 50rpx;
					min-height: 50rpx;
				}

				.text {
					margin-top: 16rpx;
					color: $color-title;
				}
			}
		}
	}
</style>

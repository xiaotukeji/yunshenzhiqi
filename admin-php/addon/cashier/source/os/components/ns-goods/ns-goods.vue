<template>
	<view class="container goods-container">
		<view class="header-action common-wrap">
			<view class="flex items-center">
				<view class="header-action-left">
					<view class="flex-1" :class="{ active: type == 'goods' }" @click="switchItem('goods')">商品</view>
					<view class="flex-1" :class="{ active: type == 'service' }" @click="switchItem('service')">项目</view>
					<view class="flex-1" :class="{ active: type == 'money' }" @click="switchItem('money')">无码商品</view>
				</view>
				<view class="header-action-center flex" v-if="billingIsShowCashBox">
					<view class="flex-1" @click="openCashBox()">打开钱箱</view>
				</view>
			</view>

			<view class="header-action-right">
				<input placeholder="请输入商品/项目名称/编码" placeholder-style="font-size:0.14rem;color:#ACACAC;" v-model="searchText" @focus="setActive('inputSearchText')" @blur="setActive('')" @confirm="search" />
				<view class="search-btn" @click="search">
					<text class="iconfont icon31sousuo"></text>
				</view>
			</view>
		</view>
		
		<view class="content">
			<view class="type-switch common-wrap flex-shrink-0 flex uni-column" v-if="serviceCategory.length && type == 'service'">
				<view class="switch-item flex-shrink-0" :class="{ active: serviceCategoryId == 'all' }" @click.stop="setServiceCategoryShow('all',0)">所有分类</view>
				<scroll-view scroll-y="true" :show-scrollbar="false" :scroll-into-view="'serviceCategory-' + serviceCategoryIndex" class="list flex-shrink-0 common-scrollbar">
					<view :id="'serviceCategory-' + index" class="switch-item flex-shrink-0" :class="{ active: serviceCategoryId == item.category_id }" v-for="(item, index) in serviceCategory" :key="index" @click.stop="setServiceCategoryShow(item.category_id,index)">
						{{ item.category_name }}
					</view>
				</scroll-view>
				<view v-if="serviceCategory.length > 13" class="list-all common-wrap" :class="{ 'show': serviceCategoryShow }" @click.stop="() => { return false }">
					<view class="center flex content-start">
						<view class="switch-item flex-shrink-0" :class="{ active: serviceCategoryId == 'all' }" @click="setServiceCategoryShow('all', 0)">所有分类</view>
						<view class="switch-item flex-shrink-0" :class="{ active: serviceCategoryId == item.category_id }" v-for="(item, index) in serviceCategory" :key="index" @click="setServiceCategoryShow(item.category_id, index)">
							{{ item.category_name }}
						</view>
					</view>
				</view>
			</view>
			<view class="type-switch common-wrap flex-shrink-0 flex uni-column" v-if="goodsCategory.length && type == 'goods'">
				<view class="switch-item flex-shrink-0" :class="{ active: goodsCategoryId == 'all' }" @click.stop.prevent="setGoodsCategoryShow('all',0)">所有分类</view>
				<scroll-view scroll-y="true" :show-scrollbar="false" :scroll-into-view="'goodsCategory-' + goodsCategoryIndex" class="list flex-shrink-0 common-scrollbar" v-show="goodsCategory.length && type == 'goods'">
					<view :id="'goodsCategory-' + index" class="switch-item flex-shrink-0" :class="{ active: goodsCategoryId == item.category_id }" v-for="(item, index) in goodsCategory" :key="index" @click.stop="setGoodsCategoryShow(item.category_id,index)">
						{{ item.category_name }}
					</view>
				</scroll-view>
				<view v-if="goodsCategory.length > 13" class="list-all common-wrap" :class="{ 'show': goodsCategoryShow }" @click.stop="() => { return false }">
					<view class="center flex content-start">
						<view class="switch-item flex-shrink-0" :class="{ active: goodsCategoryId == 'all' }" @click="setGoodsCategoryShow('all')">所有分类</view>
						<view class="switch-item flex-shrink-0" :class="{ active: goodsCategoryId == item.category_id }" v-for="(item, index) in goodsCategory" :key="index" @click="setGoodsCategoryShow(item.category_id, index)">
							{{ item.category_name }}
						</view>
					</view>
				</view>
			</view>
			<scroll-view scroll-y="true" class="list-wrap goods" v-show="type == 'goods'">
				<view class="table-list" v-show="goodsData.list.length" data-focus="true">
					<view class="table-item goods-select-focus goods-focus"
						:class="{ 'yes-stock': item.stock>0, 'item-mum-2': itemNum == 2, 'item-mum-3': itemNum == 3, 'item-mum-4': itemNum == 4, active: billingGoodsIds.indexOf(item.goods_id) != -1, focus: indexFocus == index }"
						v-for="(item, index) in goodsData.list" :key="index" @click="goodsSelect(item, index)" tabindex="0" :data-tab-index="index">

						<view class="item-info">
							<view class="item-img">
								<image v-if="item.goods_image == '@/static/goods/goods.png'" src="@/static/goods/goods.png" mode="widthFix"/>
								<image v-else :src="$util.img(item.goods_image.split(',')[0], { size: 'small' })" @error="item.goods_image = '@/static/goods/goods.png'" mode="widthFix"/>
							</view>
							<view class="item-other flex-1">
								<view class="item-name multi-hidden">{{ item.goods_name }}</view>
								<view class="w-full flex justify-between items-center self-end">
									<view class="item-money">￥{{ item.price | moneyFormat }}</view>
									<view class="item-stock">库存：{{ item.stock }}</view>
								</view>
							</view>
						</view>
						<view class="no-stock" v-if="item.stock <= 0">
							<image src="@/static/stock/stock_empty.png" mode="heightFix"/>
						</view>
					</view>
				</view>
				<view class="empty" v-show="isGoodsLoad && !goodsData.list.length">
					<image src="@/static/goods/goods_empty.png" mode="widthFix"/>
					<view class="tips">暂无商品</view>
				</view>
				<ns-loading :layer-background="{ background: 'rgba(255,255,255,.6)' }" :default-show="false" ref="goodsLoading"></ns-loading>
			</scroll-view>

			<scroll-view scroll-y="true" class="list-wrap service" v-show="type == 'service'">
				<view class="table-list" v-show="serviceData.list.length">

					<view class="table-item goods-select-focus service-focus"
						:class="{ 'yes-stock': item.stock > 0, 'item-mum-2': itemNum == 2, 'item-mum-3': itemNum == 3, 'item-mum-4': itemNum == 4, active: billingGoodsIds.indexOf(item.goods_id) != -1, focus: indexFocus == index }"
						v-for="(item, index) in serviceData.list" :key="index" @click="goodsSelect(item, index)" tabindex="0" :data-tab-index="index">

						<view class="item-info">
							<view class="item-img">
								<image v-if="item.goods_image == '@/static/goods/goods.png'" src="@/static/goods/goods.png" mode="widthFix"/>
								<image v-else :src="$util.img(item.goods_image.split(',')[0], { size: 'small' })" @error="item.goods_image = '@/static/goods/goods.png'" mode="widthFix"/>
							</view>
							<view class="item-other">
								<view class="item-name multi-hidden">{{ item.goods_name }}</view>
								<view class="w-full flex justify-between items-center self-end">
									<view class="item-money">￥{{ item.price | moneyFormat }}</view>
									<view class="item-time" v-if="item.service_length">时长：{{ item.service_length }}分钟</view>
								</view>
							</view>
						</view>
						<view class="no-stock" v-if="item.stock <= 0">
							<image src="@/static/stock/stock_empty.png" mode="heightFix"/>
						</view>
					</view>
				</view>
				<view class="empty" v-show="!serviceData.list.length">
					<image src="@/static/goods/goods_empty.png" mode="widthFix"/>
					<view class="tips">暂无商品</view>
				</view>
				<ns-loading :layer-background="{ background: 'rgba(255,255,255,.6)' }" :default-show="false" ref="loading"></ns-loading>
			</scroll-view>

			<view class="table-pages" v-show="type == 'service' && serviceData.list.length">
				<uni-pagination :total="serviceData.total" :showIcon="true" @change="pageChange" :pageSize="serviceData.size" :value="serviceData.index" />
			</view>
			<view class="table-pages" v-show="type == 'goods' && goodsData.list.length">
				<uni-pagination :total="goodsData.total" :showIcon="true" @change="pageChange" :pageSize="goodsData.size" :value="goodsData.index" />
			</view>

			<view class="money-pages" v-show="type == 'money'">
				<view class="money-wrap">
					<view class="content-wrap">
						<view class="unit">￥</view>
						<input type="text" class="money" v-model="paymentMoney" @input="paymentMoneyChange" />
					</view>
					<view class="keyboard-wrap">
						<view class="num-wrap">
							<view class="key-item" @click="keydown('1')">1</view>
							<view class="key-item" @click="keydown('2')">2</view>
							<view class="key-item" @click="keydown('3')">3</view>
							<view class="key-item" @click="keydown('4')">4</view>
							<view class="key-item" @click="keydown('5')">5</view>
							<view class="key-item" @click="keydown('6')">6</view>
							<view class="key-item" @click="keydown('7')">7</view>
							<view class="key-item" @click="keydown('8')">8</view>
							<view class="key-item" @click="keydown('9')">9</view>
							<view class="key-item" @click="keydown('00')">00</view>
							<view class="key-item" @click="keydown('0')">0</view>
							<view class="key-item" @click="keydown('.')">.</view>
						</view>
						<view class="action-wrap">
							<view class="delete" @click="deleteCode">删除</view>
							<view class="delete" @click="paymentMoney = ''">清空</view>
							<view class="confirm" @click="paymentMoneyConfirm">确认</view>
						</view>
					</view>
				</view>
			</view>
		</view>

		<uni-popup ref="skuPopup" type="center">
			<view class="sku-wrap">
				<view class="header">
					<text class="title">{{ skuInfo && skuInfo.status == 'edit' ? '调整' : '选择' }}</text>
					<text class="iconfont iconguanbi1" @click="$refs.skuPopup.close()"></text>
				</view>
				<view class="body">
					<scroll-view scroll-y="true">
						<view class="goods-info" v-if="skuInfo">
							<view class="image">
								<image v-if="skuInfo.sku_image == '@/static/goods/goods.png'" src="@/static/goods/goods.png" mode="widthFix"/>
							<image v-else :src="$util.img(skuInfo.sku_image, { size: 'small' })" @error="skuInfo.sku_image = '@/static/goods/goods.png'" mode="widthFix"/>
							</view>
							<view class="info">
								<view class="multi-hidden">{{ skuInfo.goods_name }}</view>
								<view class="price">￥{{ skuInfo.adjust_price }} / {{ skuInfo.unit ? skuInfo.unit : '件' }}</view>
								<view>库存：{{ skuInfo.stock }}</view>
							</view>
							<view class="stockTransform" @click="stockTransform">库存转换</view>
						</view>
						<view v-for="(item, index) in goodsSpec" :key="index">
							<view class="spec">{{ item.spec_name }}</view>
							<view class="spec-value">
								<view class="value-item" :class="{ active: spec.selected, disabled: (!spec.selected && skuInfo.status == 'edit') }" v-for="(spec, sindex) in item.value" :key="sindex" @click="skuSelect(spec.sku_id)">
									{{ spec.spec_value_name }}
								</view>
							</view>
						</view>
						<block v-if="skuInfo">
							<view class="spec">单价</view>
							<view class="spec-value spec-value-form">
								<input type="text" class="spec-value-input" v-model="skuInfo.adjust_price" placeholder="请输入单价" />
								<text>元</text>
							</view>
							<block v-if="skuInfo.goods_class != 6">
								<view class="spec">数量</view>
								<view class="spec-value spec-value-form">
									<view class="num-dec" @click.stop="dec">-</view>
									<input type="text" class="spec-value-input" v-model="skuInfo.num" placeholder="请输入数量" :focus="inputFocus" @focus="inputFocus = true" @blur="inputFocus = false" />
									<view class="num-inc" @click.stop="inc">+</view>
									<text>{{ skuInfo.unit }}</text>
								</view>
							</block>
							<block v-if="skuInfo.goods_class == 6 && skuInfo.pricing_type == 'weight'">
								<view class="info">
									<view class="spec">剩余库存</view>
									<view>{{ skuInfo.stock }}<text>{{ skuInfo.unit }}</text></view>
								</view>
								<view>
									<view class="spec">称重</view>
									<view class="spec-value spec-value-form">
										<input type="text" class="spec-value-input" v-model="skuInfo.weigh" placeholder="请输入重量" :focus="inputFocus" @focus="inputFocus = true" @blur="inputFocus = false" />
										<text>{{ skuInfo.unit }}</text>
									</view>
								</view>
								<view class="flex scale-action" v-if="addon.includes('scale') && cashierScale">
									<button type="primary" class="default-btn" plain @click="zero">归零</button>
									<button type="primary" class="default-btn" plain @click="tare">去皮</button>
								</view>
							</block>
						</block>
					</scroll-view>
				</view>
				<view class="footer">
					<button type="default" class="primary-btn" @click="skuConfirm" :disabled="skuInfo && skuInfo.stock <= 0">确认</button>
				</view>
			</view>
		</uni-popup>
		<stock-transform ref="stockTransformRef" @saveStockTransform="saveStockTransform"/>
	</view>
</template>

<script>
	import index from './index.js';
	import stockTransform from '@/components/stock-transform/stock-transform.vue';
	export default {
		mixins: [index],
		components:{
			stockTransform
		}
	};
</script>

<style lang="scss" scoped>
	@import './index.scss';
</style>
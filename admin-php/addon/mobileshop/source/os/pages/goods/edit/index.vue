<template>
	<view class="container" :class="{ 'safe-area': isIphoneX }">
		<view class="goods-edit-wrap">
			<view class="item-wrap padding" v-if="goodsData.goods_id == 0">
				<view class="form-title color-title">商品类型</view>
				<view class="goods-type">
					<view :class="{ 'selected color-base-text color-base-border': goodsData.goods_class == 1 }" @click="goodsData.goods_class = 1">
						<text>实物商品</text>
						<text class="iconfont iconduigou1"></text>
					</view>
					<view :class="{ 'selected color-base-text color-base-border': goodsData.goods_class == 2 }" @click="goodsData.goods_class = 2">
						<text>虚拟商品</text>
						<text class="iconfont iconduigou1"></text>
					</view>
					<view :class="{ 'selected color-base-text color-base-border': goodsData.goods_class == 3 }" @click="goodsData.goods_class = 3" v-if="addonIsExit.virtualcard">
						<text>电子卡密</text>
						<text class="iconfont iconduigou1"></text>
					</view>
					<block v-if="addonIsExit.cardservice">
						<view @click="$util.showToast({ title: '请到PC端管理端进行添加' })">
							<text>服务项目</text>
							<text class="iconfont iconduigou1"></text>
						</view>
						<view @click="$util.showToast({ title: '请到PC端管理端进行添加' })">
							<text>卡项套餐</text>
							<text class="iconfont iconduigou1"></text>
						</view>
					</block>
				</view>
			</view>

			<view class="form-title">基础信息</view>
			<view class="item-wrap">
				<view class="form-wrap">
					<view class="label">
						<text class="required color-base-text">*</text>
						<text>商品名称</text>
					</view>
					<input class="uni-input" v-model="goodsData.goods_name" placeholder="请输入商品名称,不能超过100个字符" maxlength="100" />
				</view>
				<view class="form-wrap">
					<text class="label">促销语</text>
					<input class="uni-input" v-model="goodsData.introduction" placeholder="请输入促销语,不能超过100个字符" maxlength="100" />
				</view>
				<!-- <view class="form-wrap more-wrap" @click="openGoodsCategoryPop()">
					<view class="label">
						<text class="required color-base-text">*</text>
						<text>商品分类</text>
					</view>
					<text class="selected" :class="{ have: goodsData.category_name }">{{ goodsData.category_name ? goodsData.category_name : '请选择' }}</text>
					<text class="iconfont iconright"></text>
				</view> -->
				<view class="form-wrap goods-img" :style="{ height: goodsImgHeight + 'px' }">
					<view class="label">
						<text class="required color-base-text">*</text>
						<text>商品图片</text>
					</view>
					<view class="img-list">
						<shmily-drag-image
							ref="goodsShmilyDragImg"
							:list.sync="goodsData.goods_image"
							:imageWidth="170"
							:imageHeight="170"
							:number="10"
							uploadMethod="album"
							:openSelectMode="true"
							:isAWait="isAWait"
							@callback="refreshGoodsImgHeight"
						></shmily-drag-image>
						<view class="tips">建议尺寸：800*800，长按图片可拖拽排序，最多上传10张</view>
					</view>
				</view>
				<view class="form-wrap">
					<text class="label">关键词</text>
					<input class="uni-input" v-model="goodsData.keywords" placeholder="商品关键词搜索" maxlength="100" />
				</view>
			</view>
			<view class="form-title">
				<text>商品分类</text>
				<text @click="addShopCategory" class="color-base-text">添加</text>
			</view>
			<view class="item-wrap">
				<view class="form-wrap more-wrap" v-for="(item, index) in shopCategoryNumber" :key="index" @click="openGoodsCategoryPop(index)">
					<text class="action iconfont iconjian" @click.stop="deleteShopCategory(index)" v-if="index > 0"></text>
					<text class="label">商品分类</text>
					<text class="selected" :class="{ have: shopCategoryData['store_' + index] && shopCategoryData['store_' + index].category_name }">
						{{ shopCategoryData['store_' + index] && shopCategoryData['store_' + index].category_name ? shopCategoryData['store_' + index].category_name : '请选择' }}
					</text>
					<text class="iconfont iconright"></text>
				</view>
			</view>
			<block v-if="goodsData.goods_class == 2">
				<view class="form-title">收发货设置</view>
				<view class="item-wrap">
					<picker @change="virtualDeliverTypeChange" :value="goodsData.virtual_deliver_type" :range="virtualDeliverArray" range-key="name">
						<view class="form-wrap more-wrap">
							<text class="label">发货设置</text>
							<text class="selected color-title">{{ goodsData.virtual_deliver_type ? virtualDeliverValue[goodsData.virtual_deliver_type] : '' }}</text>
							<text class="iconfont iconright"></text>
						</view>
					</picker>
				</view>
				<view class="item-wrap" v-if="goodsData.virtual_deliver_type == 'auto_deliver' || goodsData.virtual_deliver_type == 'artificial_deliver'">
					<picker @change="virtualReceiveTypeChange" :value="goodsData.virtual_receive_type" :range="virtualReceiveArray" range-key="name">
						<view class="form-wrap more-wrap">
							<text class="label">收货设置</text>
							<text class="selected color-title">{{ goodsData.virtual_receive_type ? virtualReceiveValue[goodsData.virtual_receive_type] : '' }}</text>
							<text class="iconfont iconright"></text>
						</view>
					</picker>
				</view>
				<block v-if="goodsData.virtual_deliver_type == 'verify'">
					<view class="item-wrap">
						<picker @change="validityTypeChange" :value="goodsData.verify_validity_type" :range="validityTypeArray">
							<view class="form-wrap more-wrap validity-type">
								<text class="label">核销有效期</text>
								<text class="selected color-title">{{ validityTypeArray[goodsData.verify_validity_type] }}</text>
								<text class="iconfont iconright"></text>
							</view>
						</picker>
						<view class="form-wrap price" v-if="goodsData.verify_validity_type == 1">
							<view class="label">
								<text class="required color-base-text">*</text>
								<text>有效期</text>
							</view>
							<input class="uni-input" v-model="virtualIndate" type="number" placeholder="0" />
							<text class="unit">天</text>
						</view>

						<picker v-else-if="goodsData.verify_validity_type == 2" mode="date" @change="virtualTimeChange" :start="minDate" :value="virtualTime">
							<view class="form-wrap more-wrap validity-type">
								<view class="label">
									<text class="required color-base-text">*</text>
									<text>有效期</text>
								</view>
								<text class="selected color-title">{{ virtualTime == '' ? '请设置过期时间' : virtualTime }}</text>
								<text class="iconfont iconright"></text>
							</view>
						</picker>
					</view>
				</block>
			</block>
			<view class="form-title">规格、价格及库存</view>
			<view class="item-wrap">
				<view class="form-wrap more-wrap" @click="openGoodsSpec()">
					<text class="label">规格类型</text>
					<text class="selected color-title">{{ goodsData.goods_spec_format.length ? '多规格' : '单规格' }}</text>
					<text class="iconfont iconright"></text>
				</view>
				<view class="form-wrap more-wrap" @click="openCarmichaelEdit()" v-if="goodsData.goods_class == 3 && goodsData.goods_spec_format.length <= 0">
					<text class="label">卡密管理</text>
					<input class="uni-input" type="text" :placeholder="carmiLength" disabled="" />
					<text class="iconfont iconright"></text>
				</view>
				<view class="form-wrap more-wrap" v-if="goodsData.goods_spec_format.length" @click="openGoodsSpecEdit()">
					<text class="label">规格详情</text>
					<text class="selected color-title">价格、库存</text>
					<text class="iconfont iconright"></text>
				</view>
				<template v-else>
					<view class="form-wrap price">
						<view class="label">
							<text class="required color-base-text">*</text>
							<text>销售价</text>
						</view>
						<input class="uni-input" v-model="goodsData.price" type="digit" placeholder="0.00" />
						<text class="unit">元</text>
					</view>
					<view class="form-wrap price">
						<text class="label">划线价</text>
						<input class="uni-input" v-model="goodsData.market_price" type="digit" placeholder="0.00" />
						<text class="unit">元</text>
					</view>
					<view class="form-wrap price">
						<text class="label">成本价</text>
						<input class="uni-input" v-model="goodsData.cost_price" type="digit" placeholder="0.00" />
						<text class="unit">元</text>
					</view>
					<view class="form-wrap price" v-if="goodsData.goods_class != 3">
						<view class="label">
							<text class="required color-base-text">*</text>
							<text>库存</text>
						</view>
						<input class="uni-input" v-model="goodsData.goods_stock" type="number" placeholder="0" />
						<text class="unit">件</text>
					</view>
					<view class="form-wrap price">
						<text class="label">库存预警</text>
						<input class="uni-input" v-model="goodsData.goods_stock_alarm" type="number" placeholder="0" />
						<text class="unit">件</text>
					</view>
					<view class="form-wrap price" v-if="goodsData.goods_class == 2 && goodsData.virtual_deliver_type == 'verify'">
						<view class="label">
							<text class="required color-base-text">*</text>
							<text>核销次数</text>
						</view>
						<input class="uni-input" v-model="goodsData.verify_num" type="number" placeholder="0" />
						<text class="unit">次</text>
					</view>
					<view class="form-wrap price" v-if="goodsData.goods_class == 1">
						<text class="label">重量</text>
						<input class="uni-input" v-model="goodsData.weight" type="digit" placeholder="0.00" />
						<text class="unit">kg</text>
					</view>
					<view class="form-wrap price" v-if="goodsData.goods_class == 1">
						<text class="label">体积</text>
						<input class="uni-input" v-model="goodsData.volume" type="digit" placeholder="0.00" />
						<text class="unit">m³</text>
					</view>
					<view class="form-wrap price">
						<text class="label">商品编码</text>
						<input class="uni-input" v-model="goodsData.sku_no" placeholder="请输入商品编码" />
					</view>
				</template>
			</view>

			<view class="form-title">{{ goodsData.goods_class == 1 ? '配送及其他信息' : '其他信息' }}</view>
			<view class="item-wrap">
				<view class="form-wrap more-wrap" @click="openExpressFreight()" v-if="goodsData.goods_class == 1">
					<text class="label">快递运费</text>
					<text class="selected color-title">{{ goodsData.template_name ? goodsData.template_name : '免邮' }}</text>
					<text class="iconfont iconright"></text>
				</view>
				<view class="form-wrap join-member-discount">
					<text class="label">是否开启限购</text>
					<ns-switch class="balance-switch" @change="onLimit" :checked="goodsData.is_limit == 1"></ns-switch>
				</view>
				<view class="form-wrap" style="border: none;" v-if="goodsData.is_limit == 1">
					<view class="form-wrap" style="border: none;" @click="limitChange(1)">
						<view class="iconfont" style="margin-right: 10rpx;" :class="goodsData.limit_type == 1 ? 'iconyuan_checked color-base-text' : 'iconyuan_checkbox'"></view>
						<text class="label">单次限购</text>
					</view>
					<view class="form-wrap" style="border: none;" @click="limitChange(2)">
						<view class="iconfont" style="margin-right: 10rpx;" :class="goodsData.limit_type == 2 ? 'iconyuan_checked color-base-text' : 'iconyuan_checkbox'"></view>
						<text class="label">长期限购</text>
					</view>
				</view>
				<view class="form-wrap price" v-if="goodsData.is_limit == 1">
					<text class="label">限购数量</text>
					<input class="uni-input" type="number" v-model="goodsData.max_buy" placeholder="0" />
					<text class="unit">件</text>
				</view>
				<view class="form-wrap price">
					<text class="label">起售数量</text>
					<input class="uni-input" type="number" v-model="goodsData.min_buy" placeholder="0" />
					<text class="unit">件</text>
				</view>
				<view class="form-wrap price">
					<text class="label">单位</text>
					<input class="uni-input" v-model="goodsData.unit" placeholder="请输入单位" />
				</view>
				<view class="form-wrap price">
					<text class="label">虚拟销量</text>
					<input class="uni-input" v-model="goodsData.virtual_sale" placeholder="请设置虚拟销量" />
				</view>
				<!-- <view class="form-wrap price">
						<text class="label">排序</text>
						<input class="uni-input" type="number" v-model="goodsData.sort" placeholder="0" />
					</view> -->
				<view class="form-wrap more-wrap" @click="openGoodsState()">
					<text class="label">状态</text>
					<text class="selected color-title">{{ goodsData.goods_state == 1 ? '立刻上架' : '放入仓库' }}</text>
					<text class="iconfont iconright"></text>
				</view>
				<view class="form-wrap join-member-discount">
					<text class="label">是否参与会员折扣</text>
					<ns-switch class="balance-switch" @change="joinMemberDiscount" :checked="goodsData.is_consume_discount == 1"></ns-switch>
				</view>
				<picker @change="recommendWayChange" :value="goodsData.recommend_way" :range="recommendArray">
					<view class="form-wrap more-wrap">
						<text class="label">推荐方式</text>
						<text class="selected color-title">{{ recommendArray[goodsData.recommend_way] }}</text>
						<text class="iconfont iconright"></text>
					</view>
				</picker>

				<view class="form-wrap join-member-discount">
					<text class="label">商品详情显示库存</text>
					<ns-switch class="balance-switch" @change="switchBtn('stock_show')" :checked="goodsData.stock_show == 1"></ns-switch>
				</view>
				<view class="form-wrap join-member-discount">
					<text class="label">商品详情显示销量</text>
					<ns-switch class="balance-switch" @change="switchBtn('sale_show')" :checked="goodsData.sale_show == 1"></ns-switch>
				</view>
				<view class="form-wrap join-member-discount">
					<text class="label">商品详情显示弹幕</text>
					<ns-switch class="balance-switch" @change="switchBtn('barrage_show')" :checked="goodsData.barrage_show == 1"></ns-switch>
				</view>
				<view class="form-wrap join-member-discount">
					<text class="label">划线价显示</text>
					<ns-switch class="balance-switch" @change="switchBtn('market_price_show')" :checked="goodsData.market_price_show == 1"></ns-switch>
				</view>
				<picker @change="goodsFormChange" :value="goodsData.goods_form_index" :range="goodsFormArray" v-if="addonIsExit.form">
					<view class="form-wrap more-wrap">
						<text class="label">商品表单</text>
						<text class="selected color-title">{{ goodsFormArray[goodsData.goods_form_index] }}</text>
						<text class="iconfont iconright"></text>
					</view>
				</picker>
				<picker @change="supplyChange" :value="goodsData.supply_index" :range="supplyFormArray" v-if="addonIsExit.supply">
					<view class="form-wrap more-wrap">
						<text class="label">供应商</text>
						<text class="selected color-title">{{ supplyFormArray[goodsData.supply_index] }}</text>
						<text class="iconfont iconright"></text>
					</view>
				</picker>
			</view>

			<view class="form-title">商品详情</view>
			<view class="item-wrap">
				<view class="form-wrap more-wrap" @click="openGoodsContent()">
					<text class="label">商品详情</text>
					<text class="selected have">查看</text>
					<text class="iconfont iconright"></text>
				</view>
			</view>

			<view class="form-title">商品参数</view>
			<view class="item-wrap">
				<view class="form-wrap more-wrap" @click="openAttr()">
					<text class="label">商品参数</text>
					<text class="selected have">查看</text>
					<text class="iconfont iconright"></text>
				</view>
			</view>
		</view>

		<!-- 选择商品分类 -->
		<uni-popup ref="categoryPopup" type="bottom">
			<view class="popup category" @touchmove.prevent.stop>
				<view class="popup-header">
					<text class="tit">选择商品分类</text>
					<text class="iconfont iconclose" @click="closeGoodsCategoryPop()"></text>
				</view>
				<view class="popup-body" :class="{ 'safe-area': isIphoneX }">
					<view class="nav color-base-text">
						<text :class="{ 'selected color-base-text': item }" v-if="currentLevel >= index + 1" v-for="(item, index) in categoryName" :key="index" @click="changeShow(index + 1)">
							{{ item ? item : '请选择' }}
						</text>
					</view>
					<scroll-view scroll-y="true" class="category">
						<!-- 一级分类 -->
						<view v-if="showFisrt" class="item" v-for="(item, index) in categoryList" :key="index" @click="selectCategory(item)">
							<text :class="{ 'color-base-text': categoryId[0] == item.category_id }">{{ item.category_name }}</text>
							<text v-show="categoryId[0] == item.category_id" class="iconfont iconqueding_queren color-base-text"></text>
						</view>
						<!-- 二级分类 -->
						<view v-if="showSecond" class="item" v-for="(item, index) in secondCategory" :key="index" @click="selectCategory(item)">
							<text :class="{ 'color-base-text': categoryId[1] == item.category_id }">{{ item.category_name }}</text>
							<text v-show="categoryId[1] == item.category_id" class="iconfont iconqueding_queren color-base-text"></text>
						</view>
						<!-- 三级分类 -->
						<view v-if="showThird" class="item" v-for="(item, index) in thirdCategory" :key="index" @click="selectCategory(item)">
							<text :class="{ 'color-base-text': categoryId[2] == item.category_id }">{{ item.category_name }}</text>
							<text v-show="categoryId[2] == item.category_id" class="iconfont iconqueding_queren color-base-text"></text>
						</view>
					</scroll-view>
				</view>
			</view>
		</uni-popup>

		<view class="footer-wrap" :class="{ 'safe-area': isIphoneX }"><button type="primary" @click="save()">保存</button></view>
		<loading-cover ref="loadingCover"></loading-cover>
	</view>
</template>

<script>
import shmilyDragImage from '@/components/shmily-drag-image/shmily-drag-image.vue';
import nsSwitch from '@/components/ns-switch/ns-switch.vue';

import edit from '../js/edit.js';
export default {
	components: {
		shmilyDragImage,
		nsSwitch
	},
	mixins: [edit]
};
</script>

<style lang="scss">
@import '../css/edit.scss';
</style>
<style scoped>
.img-list >>> .con .area {
	/* height: 170rpx; */
}
</style>

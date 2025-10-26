var templateFloorStyle3 = `
<div class="floor-style-3">
	<div class="item-wrap">
		<div class="head-wrap">
			<div class="title-name">
				<span :style="{ backgroundColor : mData.title.value.color }"></span>
				<h2 @click="setTitle()" :style="{ color : mData.title.value.color }">{{ mData.title.value.text }}</h2>
			</div>
			<div class="category-wrap" @click="setCategory()">
				<li v-for="(item,index) in categoryLength" :key="index">
					<template v-if="mData.categoryList.value.list.length > index && mData.categoryList.value.list[index].category_name">
						<a href="javascript:;">{{mData.categoryList.value.list[index].category_name}}</a>
					</template>
					<template v-else>
						<a href="javascript:;">商品分类名称</a>
					</template>
				</li>
			</div>
		</div>
		<div class="body-wrap">
			<div class="left-img-wrap left-wrap-box">
				<div v-if="mData.leftImg.value.url" class="mask">
					<div class="left-img-replace" @click="uploadLeftImg()">
						<h4 @click.stop="delLeftImg()" class="iconfont iconshanchu">删除</h4>
						<div class="replace"><span>点击替换</span></div>
					</div>
			  	</div>
				<div class="left-img-wrap">
					<img v-if="mData.leftImg.value.url" :src="$parent.img(mData.leftImg.value.url)">
					<div v-else class="empty" @click="uploadLeftImg()"><span>点击上传图片<br/><br/>建议尺寸 190 x 360 像素</span></div>
				</div>
			</div>

			<ul class="right-goods-wrap" @click="selectedRightGoods()">
				<li v-for="(item,index) in rightGoodsLength" :key="index">
					<template v-if="mData.rightGoodsList.value.list.length > index && mData.rightGoodsList.value.list[index].goods_name">
						<h4>{{mData.rightGoodsList.value.list[index].goods_name}}</h4>
						<p class="text-color">{{mData.rightGoodsList.value.list[index].introduction}}</p>
						<div class="img-wrap">
							<img alt="商品图片" :src="$parent.img(mData.rightGoodsList.value.list[index].goods_image.split(',')[0], 'mid')">
						</div>
					</template>
					<template v-else>
						<h4>商品名称</h4>
						<p class="text-color">商品描述</p>
						<div class="img-wrap empty">商品图片</div>
					</template>
				</li>
			</ul>
			<ul class="bottom-goods-wrap" @click="selectedBottomGoods()">
				<li v-for="(item,index) in bottomGoodsLength" :key="index">
					<template v-if="mData.bottomGoodsList.value.list.length > index && mData.bottomGoodsList.value.list[index].goods_name">
						<div class="info-wrap">
							<h4>{{mData.bottomGoodsList.value.list[index].goods_name}}</h4>
							<p class="text-color">{{mData.bottomGoodsList.value.list[index].introduction}}</p>
						</div>
						<div class="img-wrap">
							<img alt="商品图片" :src="$parent.img(mData.bottomGoodsList.value.list[index].goods_image.split(',')[0], 'mid')">
						</div>
					</template>
					<template v-else>
						<div class="info-wrap">
							<h4>商品名称</h4>
							<p class="text-color">商品描述</p>
						</div>
						<div class="img-wrap empty">商品图片</div>
					</template>
				</li>
			</ul>
		</div>
	</div>
</div>`;

Vue.component('floor-style-3', {
	template: templateFloorStyle3,
	props: {
		data: {
			type: Object,
			required: true,
		},
	},
	data: function () {
		return {
			mData: {},
			categoryLength: 6,
			rightGoodsLength: 10,
			rightSelectGoodsId: [],
			bottomGoodsLength: 6,
			bottomSelectGoodsId: [],
			// brandLength: 8,
			// selectBrandsId: []
		};
	},
	created: function () {
		this.mData = this.data;
	},
	methods: {
		setTitle: function () {
			var self = this;
			this.$parent.setText(self.mData.title.value, function (data) {
				self.mData.title.value = data;
			});
		},
		delLeftImg:function(){
			this.mData.leftImg.value.url = '';
		},
		uploadLeftImg: function () {
			var self = this;
			this.$parent.uploadImg(self.mData.leftImg.value, function (data) {
				self.mData.leftImg.value = data;
			});
		},
		setCategory: function () {
			var self = this;
			this.$parent.setCategory(self.mData.categoryList.value, function (data) {
				self.mData.categoryList.value = data;
				vm.$forceUpdate();
			});
		},
		selectedRightGoods: function () {
			var self = this;
			goodsSelect(function (data) {

				self.rightSelectGoodsId = [];
				var goods_ids = [];
				self.mData.rightGoodsList.value.list = [];

				var i = 0;
				for (var key in data) {
					var item = data[key];
					delete item.sku_list;
					delete item.selected_sku_list;
					self.mData.rightGoodsList.value.list[i] = item;
					self.rightSelectGoodsId.push(item.goods_id);
					goods_ids.push(item.goods_id);
					i++;
				}
				self.mData.rightGoodsList.value.goods_ids = goods_ids.toString();
				vm.$forceUpdate();
			}, self.rightSelectGoodsId, {mode: "spu", max_num: self.rightGoodsLength, min_num: 1});
		},
		selectedBottomGoods: function () {
			var self = this;
			goodsSelect(function (data) {

				self.bottomSelectGoodsId = [];
				var goods_ids = [];
				self.mData.bottomGoodsList.value.list = [];

				var i = 0;
				for (var key in data) {
					var item = data[key];
					delete item.sku_list;
					delete item.selected_sku_list;
					self.mData.bottomGoodsList.value.list[i] = item;
					self.bottomSelectGoodsId.push(item.goods_id);
					goods_ids.push(item.goods_id);
					i++;
				}
				self.mData.bottomGoodsList.value.goods_ids = goods_ids.toString();
				vm.$forceUpdate();
			}, self.bottomSelectGoodsId, {mode: "spu", max_num: self.bottomGoodsLength, min_num: 1, disabled: 0});
		},
		// selectedBrand: function () {
		// 	var self = this;
		// 	brandSelect(function (res) {
		// 		self.selectBrandsId = [];
		// 		var brand_ids = [];
		// 		self.mData.brandList.value.list = [];
		// 		for (var i = 0; i < res.length; i++) {
		// 			var item = res[i];
		// 			self.mData.brandList.value.list[i] = item;
		// 			self.selectBrandsId.push(item.brand_id);
		// 			brand_ids.push(item.brand_id);
		// 		}
		// 		self.mData.brandList.value.brand_ids = brand_ids.toString();
		// 		vm.$forceUpdate();

		// 	}, self.selectBrandsId, {max_num: self.brandLength, min_num: 1});
		// }

	},
	watch: {
		mData: function (curr) {
			for (var i = 0; i < curr.rightGoodsList.value.list.length; i++) {
				this.rightSelectGoodsId.push(curr.rightGoodsList.value.list[i].goods_id);
			}
			for (var i = 0; i < curr.bottomGoodsList.value.list.length; i++) {
				this.bottomSelectGoodsId.push(curr.bottomGoodsList.value.list[i].goods_id);
			}
			// for (var i = 0; i < curr.brandList.value.list.length; i++) {
			// 	this.selectBrandsId.push(curr.brandList.value.list[i].brand_id);
			// }
		},
	},
});
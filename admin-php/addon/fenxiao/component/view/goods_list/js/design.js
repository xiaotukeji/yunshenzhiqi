var fenxiaoListHtml = '<div style="display:none;"></div>';

Vue.component("fenxiao-goods-list-sources", {
	template: fenxiaoListHtml,
	data: function () {
		return {
			data: this.$parent.data,
			goodsSources: {
				initial: {
					text: "默认",
					icon: "iconmofang"
				},
				category: {
					text: "商品分类",
					icon: "iconshangpinfenlei"
				},
				diy: {
					text: "手动选择",
					icon: "iconshoudongxuanze"
				},
			},
			templateList: {
				"row1-of1": {
					text: "单列",
					icon: "iconiPhone86",
					styleList: [
						{
							text: "样式1",
							value: "style-1",
							cartSupport: true, // 是否支持按钮
							lineSupport:true, // 是否支持划线价
						},
					],
				},
				"row1-of2": {
					text: "两列",
					icon: "iconyihanglianglie",
					styleList: [
						{
							text: "样式1",
							value: "style-1",
							cartSupport: true, // 是否支持按钮
							lineSupport:true, // 是否支持划线价
						},
					],
				},
			},
			ornamentList: [
				{
					type: 'default',
					text: '默认',
				},
				{
					type: 'shadow',
					text: '投影',
				},
				{
					type: 'stroke',
					text: '描边',
				},
			],
			nameLineModeList: [
				{
					text: "单行",
					value: "single"
				},
				{
					text: "多行",
					value: "multiple"
				}
			]
		}
	},
	created:function() {
		if(!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法

		this.$parent.data.ignore = ['textColor']; //加载忽略内容 -- 其他设置中的属性设置
		this.$parent.data.ignoreLoad = true; // 等待忽略数组赋值后加载

		var previewList = {};
		for (var i = 1; i < 5; i++) {
			previewList["goods_id_" + ns.gen_non_duplicate(i)] = {
				goods_name: "分销商品",
				discount_price: (Math.random() * 100 * i + 10).toFixed(2), // 随机价格
				fenxiao_price: (Math.random() * 10 * i + 10).toFixed(0), // 随机价格
			};
		}

		// 组件所需的临时数据
		this.$parent.data.tempData = {
			goodsSources: this.goodsSources,
			templateList: this.templateList,
			ornamentList: this.ornamentList,
			nameLineModeList: this.nameLineModeList,
			previewList: previewList,
			methods: {
				addGoods: this.addGoods,
				selectCategory: this.selectCategory,
				selectTemplate: this.selectTemplate
			},
		};
	},
	methods: {
		verify : function (index) {
			var res = { code : true, message : "" };
			if (vue.data[index].sources == 'category' && vue.data[index].categoryId == 0){
				res.code = false;
				res.message = "请选择商品分类";
			}
			return res;
		},
		addGoods: function () {
			var self = this;
			goodsSelect(function (res) {
				self.$parent.data.goodsId = res;
			}, self.$parent.data.goodsId, {mode: "spu", promotion: "fenxiao", disabled: 0, post: ns.appModule});
		},
		selectCategory() {
			var self = this;
			layui.use(['form'], function () {
				var form = layui.form;
				layer.open({
					type: 1,
					title: '选择分类',
					area: ['630px', '430px'],
					btn: ['确定', '返回'],
					content: $(".draggable-element[data-index='" + self.data.index + "'] .edit-attribute .goods-category-layer").html(),
					success: function (layero, index) {
						$(".js-switch").click(function () {
							var category_id = $(this).attr("data-category-id");
							var level = $(this).attr("data-level");
							var open = parseInt($(this).attr("data-open").toString());

							if (open) {
								$(".goods-category-list .layui-table tr[data-category-id-" + level + "='" + category_id + "']").hide();
								$(this).text("+");
							} else {
								$(".goods-category-list .layui-table tr[data-category-id-" + level + "='" + category_id + "']").show();
								$(this).text("-");
							}
							$(this).attr("data-open", (open ? 0 : 1));
						});

						form.on('checkbox(category_select_id)', function (data) {
							if (data.elem.checked) {
								$("input[name='category_select_id']:checked").prop("checked", false);
								$(data.elem).prop("checked", true);
								form.render();
							}
						});

						$("input[name='category_select_id']:checked").prop("checked", false);
						if (self.data.categoryId) {
							$('.layui-layer-content [data-category_select_id="' + self.data.categoryId + '"]').prop("checked", true);
						}

						form.render();
					},
					yes: function (index, layero) {
						var selected = $(".layui-layer-content input[name='category_select_id']:checked");
						if (selected.length === 0) {
							layer.msg('请选择商品分类');
							return;
						}
						self.data.categoryName = selected.parents('tr').find('.category-name').text();
						self.data.categoryId = selected.attr('data-category_select_id');
						layer.closeAll()
					}
				});
			});
		},
		selectTemplate(template, item) {
			if (template) {
				this.$parent.data.template = template;
				item = this.templateList[template].styleList[0];
			}
			this.$parent.data.style = item.value;
			this.$parent.data.btnStyle.support = item.cartSupport;
			this.$parent.data.btnStyle.control = item.cartSupport;
			this.$parent.data.priceStyle.lineSupport = item.lineSupport;
			this.$parent.data.priceStyle.lineControl = item.lineSupport;
		},
	}
});
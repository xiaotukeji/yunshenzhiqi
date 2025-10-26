requestAdd = 'cardservice://shop/card/addGoods';
requestEdit = 'cardservice://shop/card/editGoods';
goodsTag = '卡项';

var relationGoods = [];

// 追加刷新商品sku数据
appendRefreshGoodsSkuData = {
	service_length: 0
};

// 追加单规格数据
function appendSingleGoodsData(data) {
	return {
		service_length: data.field.service_length
	};
}

// 追加保存数据
function appendSaveData(data) {
	var card_type = $('.card-type-item.active').attr('data-value');
	if (card_type == 'oncecard') {
		relationGoods.forEach(function (item, index) {
			item.num = $('.relation-goods-table.oncecard .layui-table-body tr:eq(' + index + ') .num').val();
		})
	} else {
		relationGoods.forEach(function (item, index) {
			item.discount = $('.relation-goods-table.discountcard .layui-table-body tr:eq(' + index + ') .discount').val();
		})
	}
	return {
		card_type: card_type,
		validity_type: $('[name="validity_type"]:checked').val(),
		relation_goods: JSON.stringify(relationGoods) // 卡项参数格式
	};
}

// 编辑初始化数据回调
function initEditDataCallBack() {
	relationGoods = $('[name="relation_goods"]').val() ? JSON.parse($('[name="relation_goods"]').val()) : [];
}

$(function () {

	layui.use(['element', 'laytpl', 'form', 'laydate', 'table'], function () {
		form = layui.form;
		element = layui.element;
		laytpl = layui.laytpl;
		laydate = layui.laydate;
		laytable = layui.table;
		form.render();

		var time = new Date();
		var currentTime = time.toLocaleDateString + " " + time.getHours() + ":" + time.getMinutes() + ":" + time.getSeconds();
		//卡项有效期
		laydate.render({
			elem: '#validity_time', //指定元素
			type: 'datetime',
			min: currentTime
		});

		//核销有效期类型
		form.on('radio(validity_type)', function (data) {
			var value = parseInt(data.value);
			$('.validity-type').addClass('layui-hide');
			$('.validity-type.validity-type-' + value).removeClass('layui-hide');
		});

		// 选择卡项类型
		$('.card-type-item').click(function () {
			if ($(this).hasClass('active')) return;
			var value = $(this).attr('data-value');
			$(this).addClass('active').siblings('.card-type-item').removeClass('active');
			$('.card-type-content').hide();
			$('.card-type-content.' + value).show();
			fetchRelationGoods();
		});

		// 选择关联商品
		$('.add-relation-goods').click(function () {
			var skuids = [];
			relationGoods.forEach(function (item) {
				skuids.push(item.sku_id)
			});
			goodsSelect(function (data) {
				var skuList = [];

				for (var key in data) {
					for (var sku in data[key].selected_sku_list) {
						var item = data[key].selected_sku_list[sku];
						skuList.push(item);
					}
				}
				relationGoods = skuList;
				fetchRelationGoods();

			}, skuids, {mode: 'sku', goods_class: '1,4'});
		});

		function fetchRelationGoods() {
			var cardType = $('.card-type-item.active').attr('data-value');
			var elem = cardType == 'discountcard' ? '#relationDiscountGoods' : '#relationGoods';
			var cols = [
				{
					field: 'sku_name',
					title: '商品/项目名称',
					width: '35%'
				},
				{
					field: 'goods_class_name',
					title: '商品类型',
				},
				{
					field: 'sex',
					title: '售价',
					width: '15%',
					align: 'right',
					templet: function (data) {
						return '￥' + data.price;
					}
				},
				{
					title: '操作',
					align: 'right',
					templet: function (data) {
						return `<a href="javascript:;" class="text-color delete">删除</a>`;
					}
				}
			];
			switch (cardType) {
				case 'oncecard':
					cols.splice(2, 0, {
						title: '可用次数/数量',
						align: 'center',
						templet: function (data) {
							return `<input type="text" placeholder="0" lay-verify="use_num" value="` + (data.num ? data.num : '') + `" class="layui-input len-short num" autocomplete="off">`;
						}
					});
					break;
				case 'discountcard':
					cols.splice(2, 0, {
						title: '可享折扣',
						align: 'center',
						templet: function (data) {
							return `<input type="text" placeholder="0" lay-verify="discount" value="` + (data.discount ? data.discount : '') + `" class="layui-input len-short discount" autocomplete="off"> %`;
						}
					});
					break;
			}
			var _table = laytable.render({
				elem: elem,
				data: relationGoods,
				cols: [cols],
				skin: 'nob',
				done: function () {
					if (cardType == 'oncecard' || cardType == 'discountcard') $('.card-type-content .batch-set').show();
					else $('.card-type-content .batch-set').hide();
					$('body').off('click', '.relation-goods-table .delete').on('click', '.relation-goods-table .delete', function () {
						$(this).parents('tr').remove();
						relationGoods.splice($(this).parents('tr').index(), 1);
					})
				}
			});
			if (!relationGoods.length) {
				$(elem).next('.layui-table-view').remove();
				$('.card-type-content .batch-set').hide();
			}
		}

		fetchRelationGoods();

		// 关联商品批量设置
		$('.batch-set .set').click(function () {
			var parents = $(this).parents('.batch-set');
			parents.find('.set-item').hide();
			parents.find('.set-content-wrap').show();
		});

		$('.batch-set .cancel').click(function () {
			var parents = $(this).parents('.batch-set');
			parents.find('.value').val('');
			parents.find('.set-item').show();
			parents.find('.set-content-wrap').hide();
		});

		$('.batch-set .confirm').click(function () {
			var parents = $(this).parents('.batch-set');
			var value = parents.find('.value').val();
			var type = parents.find('.value').attr('data-type');

			if (!regExp.required.test(value)) {
				layer.msg('请输入要设置的值');
				return;
			}
			if (type == 'num') {
				if (!regExp.number.test(value)) {
					layer.msg('可用次数格式错误');
					return;
				}
				if (parseInt(value) < 1) {
					layer.msg('可用次数不能小于等于0');
					return;
				}
			}
			if (type == 'discount') {
				if (!regExp.number.test(value)) {
					layer.msg('折扣格式错误');
					return;
				}
				if (parseInt(value) < 1 || parseInt(value) > 99) {
					layer.msg('折扣需在[1-99]之间设置');
					return;
				}
			}
			// 批量设置值
			$(this).parents('.layui-form-item').find('.' + type).val(value);

			parents.find('.value').val('');
			parents.find('.set-item').show();
			parents.find('.set-content-wrap').hide();
		});

		form.on('radio(discount_goods_type)', function (data) {
			$('.discount-goods').hide();
			$('.discount-goods.' + data.value).show();
		});

		form.verify({
			//销售价
			price: function (value) {
				if (!$("input[name='spec_type']").is(":checked")) {
					if (value.length == 0) {
						element.tabChange('goods_tab', "price-stock");
						return "请输入卡项开卡价";
					}
					if (isNaN(value) || !regExp.digit.test(value)) {
						element.tabChange('goods_tab', "price-stock");
						return '[卡项开卡价]格式输入错误';
					}

				}
			},
			renew_price: function (value) {
				if (value.length == 0) {
					element.tabChange('goods_tab', "price-stock");
					return "请输入卡项续费价";
				}
				if (isNaN(parseInt(value)) || !regExp.digit.test(value)) {
					element.tabChange('goods_tab', "price-stock");
					return '[卡项续费价]格式输入错误';
				}
			},
			//有效期
			validity_day: function (value) {
				var verify_validity_type = $('[name="validity_type"]:checked').val();
				if (verify_validity_type == 1) {
					if (isNaN(value) || !regExp.number.test(value)) {
						element.tabChange('goods_tab', "basic");
						return '[卡项有效期]格式输入错误';
					}
					if (value < 1) {
						element.tabChange('goods_tab', "basic");
						return '卡项有效期不能小于1天';
					}
				}
			},
			validity_time: function (value) {
				var verify_validity_type = $('[name="validity_type"]:checked').val();
				if (verify_validity_type == 2 && value.length == 0) {
					element.tabChange('goods_tab', "basic");
					return "请输入有效期";
				}
			},
			relation_goods: function () {
				var cardType = $('.card-type-item.active').attr('data-value');
				if (cardType != 'discountcard' && !$('.relation-goods-table.oncecard .layui-table-body tr').length) {
					return '请选择卡项内容';
				}
			},
			relation_discount_goods: function () {
				var cardType = $('.card-type-item.active').attr('data-value');
				if (cardType == 'discountcard' && $('[name="discount_goods_type"]:checked').val() != 'all' && !$('.relation-goods-table.discountcard .layui-table-body tr').length) {
					return '请选择卡项内容';
				}
			},
			use_num: function (value) {
				var cardType = $('.card-type-item.active').attr('data-value');
				if (cardType == 'oncecard') {
					if (!regExp.required.test(value)) {
						return '请输入可用次数/数量';
					}
					if (!regExp.number.test(value)) {
						return '次数/数量格式错误';
					}
					if (parseInt(value) < 1) {
						return '可用次数/数量不能小于等于0';
					}
				}
			},
			discount: function (value) {
				var cardType = $('.card-type-item.active').attr('data-value');
				if (cardType == 'discountcard' && $('[name="discount_goods_type"]:checked').val() != 'all') {
					if (!regExp.required.test(value)) {
						return '请输入折扣卡折扣';
					}
					if (!regExp.number.test(value)) {
						return '折扣卡折扣格式错误';
					}
					if (parseInt(value) < 1 || parseInt(value) > 99) {
						return '折扣卡折扣需在[1-99]之间设置';
					}
				}
			},
			common_discount: function (value) {
				var cardType = $('.card-type-item.active').attr('data-value');
				if (cardType == 'discountcard' && $('[name="discount_goods_type"]:checked').val() == 'all') {
					if (!regExp.required.test(value)) {
						return '请输入折扣卡折扣';
					}
					if (!regExp.number.test(value)) {
						return '折扣卡折扣格式错误';
					}
					if (parseInt(value) < 1 || parseInt(value) > 99) {
						return '折扣卡折扣需在[1-99]之间设置';
					}
				}
			},
			common_num: function (value) {
				var cardType = $('.card-type-item.active').attr('data-value');
				if (cardType == 'commoncard') {
					if (!regExp.required.test(value)) {
						return '请输入卡项可用次数/数量';
					}
					if (!regExp.number.test(value)) {
						return '卡项可用次数/数量格式错误';
					}
					if (parseInt(value) < 1) {
						return '卡项可用次数/数量不能小于等于0';
					}
				}
			}
		});

	});

});
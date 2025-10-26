requestAdd = 'cardservice://shop/service/addGoods';
requestEdit = 'cardservice://shop/service/editGoods';
goodsTag = '项目';

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
	return {
		verify_validity_type: $('[name="verify_validity_type"]:checked').val()
	};
}

$(function () {

	layui.use(['element', 'laytpl', 'form', 'laydate'], function () {
		form = layui.form;
		element = layui.element;
		laytpl = layui.laytpl;
		laydate = layui.laydate;
		form.render();

		var time = new Date();
		var currentTime = time.toLocaleDateString + " " + time.getHours() + ":" + time.getMinutes() + ":" + time.getSeconds();

		//核销有效期
		laydate.render({
			elem: '#virtual_time', //指定元素
			type: 'datetime',
			min: currentTime
		});

		//卡项有效期类型
		form.on('radio(verify_validity_type)', function (data) {
			var value = parseInt(data.value);
			$('.validity-type').addClass('layui-hide');
			$('.validity-type.validity-type-' + value).removeClass('layui-hide');
		});

		form.verify({
			//有效期
			virtual_indate: function (value) {
				var verify_validity_type = $('[name="verify_validity_type"]:checked').val();
				if (verify_validity_type == 1) {
					if (isNaN(value) || !regExp.number.test(value)) {
						element.tabChange('goods_tab', "basic");
						return '[核销有效期]格式输入错误';
					}
					if (value < 1) {
						element.tabChange('goods_tab', "basic");
						return '核销有效期不能小于1天';
					}
				}
			},
			virtual_time: function (value) {
				var verify_validity_type = $('[name="verify_validity_type"]:checked').val();
				if (value.length == 0 && verify_validity_type == 2) {
					element.tabChange('goods_tab', "basic");
					return "请输入有效期";
				}
			},
			service_length: function (value) {
				if (value.length > 0) {
					value = parseInt(value);
					if (isNaN(value) || !regExp.number.test(value)) {
						element.tabChange('goods_tab', "price-stock");
						return '[服务时长]格式输入错误';
					}
					if (value < 0) {
						element.tabChange('goods_tab', "price-stock");
						return '[服务时长]不能小于0';
					}
				}
			},
			//销售价
			service_price: function (value) {
				if (!$("input[name='spec_type']").is(":checked")) {
					if (value.length == 0) {
						element.tabChange('goods_tab', "basic");
						return "请输入销售价";
					}

					if (isNaN(value) || !regExp.digit.test(value)) {
						element.tabChange('goods_tab', "basic");
						return '[销售价]格式输入错误';
					}

				}
			},
		});

	});

});
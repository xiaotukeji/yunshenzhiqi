layui.use(['form',], function () {
	var form = layui.form,
		repeat_flag = false;

	form.render();

	form.on('radio(printer_type)', function (e) {
		$('.printer-type').hide()
		$('.printer-type.' + e.value).show()
	})

	form.on('select(brand)', function (data) {
		var value = data.value;
		if (value == '365') {
			$('.feie').hide();
			$('.yilianyun').hide();

			$("input[name='user']").attr("lay-verify", "");
			$("input[name='ukey']").attr("lay-verify", "");

			$("input[name='open_id']").attr("lay-verify", "");
			$("input[name='apikey']").attr("lay-verify", "");
		}

		if (value == 'feie') {
			$('.feie').show();
			$('.yilianyun').hide();

			$("input[name='user']").attr("lay-verify", "cloudRequired");
			$("input[name='ukey']").attr("lay-verify", "cloudRequired");

			$("input[name='open_id']").attr("lay-verify", "");
			$("input[name='apikey']").attr("lay-verify", "");
		}

		if (value == 'yilianyun') {
			$('.yilianyun').show();
			$('.feie').hide();

			$("input[name='open_id']").attr("lay-verify", "cloudRequired");
			$("input[name='apikey']").attr("lay-verify", "cloudRequired");

			$("input[name='user']").attr("lay-verify", "");
			$("input[name='ukey']").attr("lay-verify", "");
		}
	});

	form.on('switch(recharge_open)', function (data) {
		if (data.elem.checked) {
            $(".layui-form-item.recharge_item").removeClass('layui-hide');
			$('.recharge-content').removeClass('layui-hide');
			$('.recharge-content [name="recharge_template_id"]').attr('lay-verify', 'required');
		} else {
            $(".layui-form-item.recharge_item").addClass('layui-hide');
			$('.recharge-content').addClass('layui-hide');
			$('.recharge-content [name="recharge_template_id"]').attr('lay-verify', '');
		}
	});

	form.on('switch(order_pay_open)', function (data) {
		if (data.elem.checked) {
			$('.order_pay_item').removeClass('layui-hide');
			$('.order_pay_item [name="order_pay_template_id"]').attr('lay-verify', 'required');
		} else {
			$('.order_pay_item').addClass('layui-hide');
			$('.order_pay_item [name="order_pay_template_id"]').attr('lay-verify', '');
		}
    });
    
    form.on('switch(change_shifts_open)', function (data) {
		if (data.elem.checked) {
			$('.change_shifts-content').removeClass('layui-hide');
			$('.change_shifts-box [name="change_shifts_template_id"]').attr('lay-verify', 'required');
		} else {
			$('.change_shifts-content').addClass('layui-hide');
			$('.change_shifts-box [name="change_shifts_template_id"]').attr('lay-verify', '');
		}
	});

	form.on('switch(take_delivery_open)', function (data) {
		if (data.elem.checked) {
			$('.take_delivery_item').removeClass('layui-hide');
			$('.take_delivery_item [name="take_delivery_template_id"]').attr('lay-verify', 'required');
		} else {
			$('.take_delivery_item').addClass('layui-hide');
			$('.take_delivery_item [name="take_delivery_template_id"]').attr('lay-verify', '');
		}
	});

	form.on('switch(manual_open)', function (data) {
		if (data.elem.checked) {
			$('.default_item').removeClass('layui-hide');
			$('.default_item [name="template_id"]').attr('lay-verify', 'required');
		} else {
			$('.default_item').addClass('layui-hide');
			$('.default_item [name="template_id"]').attr('lay-verify', '');
		}
	});

	var printer_type = $('input[name="printer_type"]:checked').val()
	$(`input[name="printer_type"][value="${printer_type}"]`).next('.layui-unselect.layui-form-radio').click()

	var brand = $('select[name="brand"] option:checked').val()
	$("select[name='brand']").siblings("div.layui-form-select").find("dl dd[lay-value='" + brand + "']").click();

	form.verify({
		time: function (value) {
			var now_time = (new Date()).getTime();
			var start_time = (new Date($("#start_time").val())).getTime();
			var end_time = (new Date(value)).getTime();
			if (now_time > end_time) {
				return '结束时间不能小于当前时间!'
			}
			if (start_time > end_time) {
				return '结束时间不能小于开始时间!';
			}
		},
		flnum: function (value) {
			var arrMen = value.split(".");
			var val = 0;
			if (arrMen.length == 2) {
				val = arrMen[1];
			}
			if (val.length > 2) {
				return '保留小数点后两位！'
			}
		},
		int: function (value) {
			if (value <= 1 || value % 1 != 0) {
				return '请输入大于1的正整数！'
			}
		},
		cloudRequired: function (value, ele) {
			if ($('[name="printer_type"]:checked').val() == 'cloud' && !/[\S]+/.test(value)) {
				var label = $(ele).parents('.layui-form-item').find('.layui-form-label').text().replace('：', '').replace('*', '')
				return label + '不能为空'
			}
		},
		localRequired: function (value, ele) {
			if ($('[name="printer_type"]:checked').val() == 'local' && !/[\S]+/.test(value)) {
				var label = $(ele).parents('.layui-form-item').find('.layui-form-label').text().replace('：', '').replace('*', '')
				return label + '不能为空'
			}
		},
		networkRequired: function (value, ele) {
			if ($('[name="printer_type"]:checked').val() == 'newwork' && !/[\S]+/.test(value)) {
				var label = $(ele).parents('.layui-form-item').find('.layui-form-label').text().replace('：', '').replace('*', '')
				return label + '不能为空'
			}
		}
	});

	form.on('submit(save)', function (data) {

		var field = data.field;
		if (field.brand == 'feie') {
			field.open_id = field.user;
			field.apikey = field.ukey;
		}

		var order_pay_order_type_arr = [];
		if (field.order_pay_open) {
			$(".order-pay-order-type").each(function () {
				if ($(this).is(":checked")) {
					order_pay_order_type_arr.push($(this).val());
				}
			});
			if (order_pay_order_type_arr == "") {
				layer.msg('请选择支付打印的订单类型');
				return false;
			}
		}
		field.order_pay_order_type = order_pay_order_type_arr.toString();

		var take_delivery_order_type_arr = [];
		if (field.order_pay_open) {
			$(".order-pay-order-type").each(function () {
				if ($(this).is(":checked")) {
					take_delivery_order_type_arr.push($(this).val());
				}
			});
			if (take_delivery_order_type_arr == "") {
				layer.msg('请选择收货打印的订单类型');
				return false;
			}

		}
		field.take_delivery_order_type = take_delivery_order_type_arr.toString();

		if (repeat_flag) return;
		repeat_flag = true;

		$.ajax({
			type: 'POST',
			dataType: 'JSON',
			url: field.printer_id ? ns.url("printer://shop/printer/edit") : ns.url("printer://shop/printer/add"),
			data: field,
			async: false,
			success: function (res) {
				repeat_flag = false;

				if (res.code == 0) {
					layer.confirm(field.printer_id ? '编辑成功' : '添加成功', {
						title: '操作提示',
						btn: ['返回列表', field.printer_id ? '继续编辑' : '继续添加'],
						closeBtn: 0,
						yes: function (index, layero) {
							location.hash = ns.hash("printer://shop/printer/lists");
							layer.close(index);
						},
						btn2: function (index, layero) {
							listenerHash(); // 刷新页面
							layer.close(index);
						}
					});
				} else {
					layer.msg(res.message);
				}
			}
		})
	});
});

function backPrinterList() {
	location.hash = ns.hash("printer://shop/printer/lists");
}
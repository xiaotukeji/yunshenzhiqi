var laytpl, form, repeat_flag = false, laydate;

layui.use(['form', 'laytpl', 'table', 'util', 'laydate'], function () {
	form = layui.form, laytpl = layui.laytpl, table = layui.table, util = layui.util, laydate = layui.laydate;
	form.render();
	fetch()
	$('.stock-search').focus()
	//门店选择
	form.on('select(store_list)', function (data) {
		var store_id = data.value;
		if (defaultStoreId > 0) {
			layer.confirm('更改门店，将清除已录入的单报明细，确定吗?\n', {
				title: '操作提示',
				btn: ['是', '否'],
				closeBtn: 0,
				yes: function () {
					defaultStoreId = store_id;
					reset();
					layer.closeAll()
					stockData = []
					fetch()
				},
				btn2: function () {
					form.val("formTest", {store_id: defaultStoreId})
				}
			});
		} else {
			defaultStoreId = store_id
		}

	});

	form.render('select');

	var timeTemplate = 'yyyy-MM-dd HH:mm:ss'
	laydate.render({
		elem: '#date_time'
		, type: 'datetime'
		, btns: ['now', 'confirm']
		, max: util.toDateString(new Date(), timeTemplate)
		, value: $('#date_time').val() ? $('#date_time').val() : util.toDateString(new Date(), timeTemplate)
	});

	form.on('submit(save)', function (data) {
		if (stockData.length === 0) {
			layer.msg('请选择产品');
			return;
		}

		var check_return = false;
		for (var i = 0; i < stockData.length; i++) {
			if (stockData[i].goods_num <= 0) {
				check_return = true;
				layer.msg('产品:' + stockData[i].sku_name + '数量不能为空');
				break;
			}
		}

		if (check_return) return;

		if (stockConfig.is_audit == 1) {
			var temp_index = layer.confirm('单据保存后将处于"待审核"状态，只有经办人可以编辑或删除等操作！是否确认保存？', {
				title: '操作提示',
				btn: ['确定', '取消'],
				yes: function () {
					layer.close(temp_index);
					save();
				}
			});
		} else {
			save();
		}
	});
});

function backStockAction() {
	location.hash = ns.hash("stock://shop/stock/" + stockAction.listRoute)
}

$(".stock-search").on('keyup', function (e) {//空白行回车
	if (e.keyCode == 13) {
		var val = $(this).val();
		$('.stock-search').blur()
		if (carriage(-1, val)) {
			goodsSelectByStockAction(function (res) {
				res.forEach(el => {
					el.goods_num = 1
					if (stockAction.listRoute === 'check') {
						el.goods_num = el.real_stock ? el.real_stock + 0 : 0
					}
					el.goods_price = el.price || 0
					//库存盘点空白行无需判断是否替换某行，只需判断是否存在
					var index = stockData.length ? stockData.findIndex(v => v.sku_id === el.sku_id) : -1
					if (index != -1) {
						stockData[index].goods_num += 1
					} else {
						stockData.push(el)
					}
				});
				fetch()
			}, [], {minNum: 1, search_text: val, store_id: defaultStoreId,})
		}

	}
});

//数据渲染（任何来源）
function fetch() {
	//重新渲染
	var template = $("#stock_goods_info").html();
	$('.stock-body tr').not('.stock-search-line').remove();

	if (ns.checkIsNotNull(stockData)) {
		$.each(stockData, function (index, value) {
			value.index = index
			laytpl(template).render(value, function (html) {
				$('.stock-search-line').before(html);
				$(".stock-search-" + value.sku_id).on('keyup', function (e) {//更新dom事件需要重新绑定
					if (e.keyCode == 13) {
						var val = $(this).val();
						$('.stock-search-' + value.sku_id).blur()
						$('.stock-search').blur()
						if (carriage(index, val)) {
							goodsSelectByStockAction(function (res) {
								res.forEach((el, resIndex) => {
									el.goods_num = 1
									if (stockAction.listRoute === 'check') {
										el.goods_num = el.real_stock ? el.real_stock + 0 : 0
									}
									el.goods_price = el.price || 0
									//库存盘点
									var indexs = stockData.findIndex(v => v.sku_id === el.sku_id)
									if (indexs != -1) {//库存不可出现相同类目
										stockData[indexs].goods_num += 1//列表已有数量加一
									} else if (!resIndex) {//选择的数据第一条在没有相同类目的情况下才替换列表选中行
										stockData.splice(index, 1, el)
									} else {
										stockData.push(el)//非第一条并且列表不存在直接加入列表
									}
								});
								fetch()
							}, [], {minNum: 1, search_text: val, store_id: defaultStoreId,})
						}

					}
				});
				$('.stock-search').val('').focus()
				form.render();
			})
		})
	}

	form.render();
	syncData();
}

//输入回车后的处理,查询到单条的处理
function carriage(index, val) {
	var num = true
	$.ajax({
		url: ns.url("stock://shop/stock/getskulist"),
		data: {search: val, store_id: defaultStoreId},
		dataType: 'JSON',
		type: 'POST',
		async: false,
		success: function (res) {

			if (res.data.length != 1) {//不是一条数据返回true，打开弹框
				num = true
				return false
			}
			num = false
			var data = res.data[0]
			data.goods_num = 1
			data.goods_price = data.price || 0
			var indexs = stockData.length ? stockData.findIndex(v => v.sku_id === data.sku_id) : -1
			if (index != -1) {//数据行


				if (indexs != -1) {//库存盘点/先判断是否存在
					stockData[indexs].goods_num += 1//存在数量累加
				} else {
					stockData.splice(index, 1, data)
				}

			} else {//空白行
				var indexs = stockData.length ? stockData.findIndex(v => v.sku_id === data.sku_id) : -1
				if (indexs != -1) {//库存盘点/先判断是否存在
					stockData[indexs].goods_num += 1//存在数量累加
				} else {
					stockData.push(data)
				}

			}
			fetch()
		}
	})
	return num
}

function dataChange(obj) {
	syncData();
}

function syncData() {
	var count_num = 0, goods_money = 0;
	var goods_up = 0, goods_down = 0, goods_same = 0;
	var kinds_data = [];
	$('.stock-tr').each(function (index) {
		var obj = $(this);
		var goods_sku_id = stockData[index].sku_id;
		var goods_num = parseFloat(obj.find('input[name=goods_num]').val()) || 0;
		var goods_price = parseFloat(obj.find('input[name=goods_price]').val()) || 0;
		if (kinds_data.indexOf(goods_sku_id) == -1) {
			kinds_data.push(goods_sku_id)
		}
		//重新采集数据
		stockData[index].goods_num = goods_num;//库存数量
		stockData[index].goods_price = goods_price;//库存价格
		count_num += goods_num;
		goods_money += goods_num * goods_price;
		obj.find('.total-cost-money').text(parseFloat(goods_num * goods_price).toFixed(2));
		if (stockAction.listRoute === 'check') {
			$('.compare-num').eq(index).html(`<span style="color:${stockData[index].goods_num - stockData[index].real_stock > 0 ? '#15eb26' : stockData[index].goods_num - stockData[index].real_stock < 0 ? 'red' : ''}">${parseInt(stockData[index].goods_num - stockData[index].real_stock)}</span>`)
			if (stockData[index].goods_num - stockData[index].real_stock > 0) goods_up++
			if (stockData[index].goods_num - stockData[index].real_stock < 0) goods_down++
			if (stockData[index].goods_num - stockData[index].real_stock == 0) goods_same++

		}
	});
	$(".kinds-num").text(parseInt(kinds_data.length));
	$(".count-num").text(parseFloat(count_num));
	$(".goods-money").text(parseFloat(goods_money).toFixed(2));
	$(".goods-up").text(parseInt(goods_up));
	$(".goods-down").text(parseInt(goods_down));
	$(".goods-same").text(parseInt(goods_same));

}

function reset() {
	stockData = [];
	fetch();
	syncData();
}

function delTr(obj) {
	var parent = $(obj).parents('.stock-tr');
	// parent.remove();

	var key = parent.data('key');//唯一值  (sku_id不承担)
	stockData.splice(key, 1);//由于key使用index，index不会随dom操作变换移除行会出错需要在删除时重新渲染
	fetch()
}

function editBtn(val) {
	goodsSelectByStockAction(function (res) {
		if (val === 'btn') {//空白行btn选择
			res.forEach(el => {
				el.goods_num = 1
				if (stockAction.listRoute === 'check') {
					el.goods_num = el.real_stock ? el.real_stock + 0 : 0
				}
				el.goods_price = el.price || 0

				var index = stockData.length ? stockData.findIndex(v => v.sku_id === el.sku_id) : -1
				if (index != -1) {//库存盘点/先判断是否存在
					//stockData[index].goods_num += 1//存在数量累加
				} else {
					stockData.push(el)
				}
			});
		} else {//数据行btn选择
			var parent = $(val).parents('.stock-tr');
			var key = parent.data('key');//唯一值  (sku_id不承担)
			res.forEach((el, index) => {//循环选中的数据
				el.goods_num = 1
				if (stockAction.listRoute === 'check') {
					el.goods_num = el.real_stock ? el.real_stock + 0 : 0
				}
				el.goods_price = el.price || 0
				//库存盘点
				var indexs = stockData.length ? stockData.findIndex(v => v.sku_id === el.sku_id) : -1
				if (indexs != -1) {//优先判断是否存在
					//stockData[indexs].goods_num += 1
				} else if (!index) {//不存在选中数据第一条替换btn点击行
					stockData.splice(key, 1, el)
				} else {
					stockData.push(el)//其它直接加入
				}
			});
		}
		fetch()
	}, [], {minNum: 1, search_text: '', store_id: defaultStoreId,})


}

function save() {
	let obj = {}
	let stock_check = true;
	stockData.forEach((el,key) => {
		if (ns.checkIsNotNull(obj[el.sku_id])) {
			obj[el.sku_id].goods_num += el.goods_num
			obj[el.sku_id].goods_price += el.goods_price
		} else {
			obj[el.sku_id] = el
			obj[el.sku_id].goods_sku_id = el.sku_id
		}
		//出库库存检测
		var error_msg = '';
		var goods_class = obj[el.sku_id].goods_class;
		if(goods_class == 6 && !(ns.getRegexp('>0float3')).test(obj[el.sku_id].goods_num)){
			error_msg = '数量必须为正数且最多保留三位小数';
		}else if(goods_class != 6 && !(ns.getRegexp('>0num')).test(obj[el.sku_id].goods_num)){
			error_msg = '数量必须为正整数';
		}else if(stockAction.saveRoute == 'stockout' && obj[el.sku_id].goods_num > obj[el.sku_id].real_stock){
			error_msg = '['+obj[el.sku_id].sku_name+']库存不足';
		}
		if(error_msg){
			stock_check = false;
			$("tbody.stock-body tr[data-key='"+key+"'] input[name='goods_num']").focus();
			layer.msg(error_msg);
		}
	})
	if(stock_check === false) return false;

	var data = {
		stock_json: JSON.stringify(obj),
		store_id: defaultStoreId,
	};
	data[stockAction.id] = $('input[name="' + stockAction.id + '"]').val() || 0;

	if (stockAction.params) {
		$.each(stockAction.params, (i, e) => {
			data[i] = $(e).val();
		})
	}

	if (repeat_flag) return false;
	repeat_flag = true;
	var btn = $('button[lay-filter="save"]')
	btn.addClass('layui-btn-disabled');
	btn.find('.layui-icon').addClass('layui-icon-loading');
	btn.prop('disabled', true);

	$.ajax({
		type: 'post',
		dataType: 'JSON',
		url: ns.url("stock://shop/stock/" + stockAction.saveRoute),
		async: true,
		data: data,
		success: function (res) {
			if (res.code >= 0) {
				repeat_flag = false;
				btn.removeClass('layui-btn-disabled');
				btn.find('.layui-icon').removeClass('layui-icon-loading');
				btn.prop('disabled', false);
				layer.confirm($('input[name="' + stockAction.id + '"]').val() ? '编辑成功' : '添加成功', {
					title: '操作提示',
					btn: ['返回列表', $('input[name="' + stockAction.id + '"]').val() ? '继续编辑' : '继续添加'],
					closeBtn: 0,
					yes: function (index, layero) {
						location.hash = ns.hash("stock://shop/stock/" + stockAction.listRoute);
						layer.close(index);
					},
					btn2: function (index, layero) {
						repeat_flag = false;
						listenerHash(); // 刷新页面
						layer.close(index);
					}
				});
			} else {
				repeat_flag = false;
				btn.removeClass('layui-btn-disabled');
				btn.find('.layui-icon').removeClass('layui-icon-loading');
				btn.prop('disabled', false);
				layer.msg(res.message);
			}
			return false
		}
	})
}

/**
 * 商品选择器
 * @param callback 回调函数
 * @param selectId 已选商品id
 * @param params mode：模式(spu、sku), max_num：最大数量，min_num 最小数量, is_virtual 是否虚拟 0 1, disabled: 开启禁用已选 0 1，promotion：营销活动标识 pintuan、groupbuy、fenxiao （module 表示组件） goods_class: 1 实物商品 2虚拟商品 3电子商品 不传查全部
 */
function goodsSelectByStockAction(callback, selectId, params = {}) {
	layui.use(['layer'], function () {
		localStorage.removeItem('goods_select_id'); // 删除选中id 本地缓存
		if (selectId.length) {
			localStorage.setItem('goods_select_id', selectId.toString());
		}

		params.mode = params.mode ? params.mode : 'spu';
		if (params.disabled == undefined || params.disabled == 0) {
			params.disabled = 0;
		} else {
			params.disabled = 1;
		}
		params.site_id = ns_url.siteId;
		params.app_module = ns_url.appModule;
		params.goods_class = params.goods_class || "";
		params.max_num = params.max_num || 200; // 最多选择数量
		params.search_text = params.search_text || ''
		// if(!params.post) params.post = 'shop';

		// if (params.post == 'store') params.post += '://store';

		var url = ns.url("stock://shop/stock/goodsSelect?request_mode=iframe", params);
		layer.open({
			title: "商品选择",
			type: 2,
			area: ['1000px', '720px'],
			fixed: false, //不固定
			btn: ['选中', '返回'],
			content: url,
			btn1: function (index, layero) {
				var iframeWin = document.getElementById(layero.find('iframe')[0]['name']).contentWindow;//得到iframe页的窗口对象，执行iframe页的方法：
				iframeWin.selectGoodsListener(function (obj) {
					if (typeof callback == "string") {
						try {
							eval(callback + '(obj)');
							if (!obj.length) return false
							layer.close(index);
						} catch (e) {
							console.error('回调函数' + callback + '未定义');
						}
					} else if (typeof callback == "function") {
						callback(obj);
						if (!obj.length) return false
						layer.close(index);
					}

				});
				return false
			},
			btn2: function (index, layero) {
				layer.close(index);
				return false
			}
		});

	});
}
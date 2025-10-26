var laytpl, form, element, repeat_flag, table, layCascader, goodsCategory = [];

//正则表达式
var regExp = {
	number: /^\d{0,10}$/,
	digit: /^\d{0,10}(.?\d{0,2})$/,
	special: /^\d{0,10}(.?\d{0,3})$/
};

$(function () {
	$("body").off("click", ".contraction").on("click", ".contraction", function () {
		var goods_id = $(this).attr("data-goods-id");
		var open = $(this).attr("data-open");
		var open_t = $(this).siblings('.title-content').children('.vips_price').attr("data-open") ? $(this).siblings('.title-content').children('.vips_price').attr("data-open") : 0;
		var tr = $(this).parent().parent().parent().parent();
		var index = tr.attr("data-index");
		if ((parseInt(open) + parseInt(open_t)) > 0) {
			$(this).children("span").text("+");
			$(".js-sku-list-" + index).remove();
		} else {
			$(this).children("span").text("-");
			$.ajax({
				url: ns.url("shop/goods/getGoodsSkuList"),
				data: {goods_id: goods_id},
				dataType: 'JSON',
				type: 'POST',
				async: false,
				success: function (res) {
					var list = res.data;
					var sku_list = $("#skuList").html();
					var data = {
						list: list,
						index: index,
						member_price_is_exit: member_price_is_exit
					};
					laytpl(sku_list).render(data, function (html) {
						tr.after(html);
					});

					layer.photos({
						photos: '.img-wrap',
						anim: 5
					});
				}
			});
		}
		$(this).attr("data-open", (open == 0 ? 1 : 0));
		$(this).siblings('.title-content').children('.vips_price').attr("data-open", (open == 0 ? 1 : 0));
	});

	$("body").off("click", ".vips_price").on("click", ".vips_price", function () {
		var goods_id = $(this).attr("data-goods-id");
		var open = $(this).attr("data-open");
		var open_t = $(this).parent().siblings('.contraction').attr("data-open");
		var tr = $(this).parent().parent().parent().parent().parent();
		var index = tr.attr("data-index");

		if ((parseInt(open)+parseInt(open_t)) > 0) {
			$(this).parent().siblings('.contraction').children("span").text("+");
			$(".js-sku-list-" + index).remove();
		} else {
			$(this).parent().siblings('.contraction').children("span").text("-");
			$.ajax({
				url: ns.url("shop/goods/getGoodsSkuList"),
				data: {goods_id: goods_id},
				dataType: 'JSON',
				type: 'POST',
				async: false,
				success: function (res) {
					var list = res.data;
					var sku_list = $("#skuList").html();
					var data = {
						list: list,
						index: index,
						member_price_is_exit: member_price_is_exit
					};
					laytpl(sku_list).render(data, function (html) {
						tr.after(html);
					});

					layer.photos({
						photos: '.img-wrap',
						anim: 5
					});
				}
			});
		}
		$(this).attr("data-open", (open == 0 ? 1 : 0));
		$(this).parent().siblings('.contraction').attr("data-open", (open == 0 ? 1 : 0))
	});

	layui.use(['form', 'laytpl', 'element', 'layCascader'], function () {
		form = layui.form;
		repeat_flag = false; //防重复标识
		element = layui.element;
		laytpl = layui.laytpl;
		layCascader = layui.layCascader;

		form.render();
		refreshTable();

		element.on('tab(goods_list_tab)', function () {
			var type = this.getAttribute('data-type');
			$("input[name='goods_state']").val("");
			if (type) {
				if(type == "goods_state"){
					$("input[name='stockalarm']").val("");
				}
				var id = this.getAttribute('lay-id');
				$("input[name='" + type + "']").val(id);
			}else{
				$("input[name='stockalarm']").val("");
			}

			var html = '<button class="layui-btn layui-btn-primary" lay-event="delete">批量删除</button>';
			if (type == "goods_state" && id == 1) {
				// 销售中状态：下架
				html += '<button class="layui-btn layui-btn-primary" lay-event="off_goods">批量下架</button>';
			} else if (type == "goods_state" && id == 0) {
				// 仓库中状态：上架
				html += '<button class="layui-btn layui-btn-primary" lay-event="on_goods">批量上架</button>';
			}

			html += '<button class="layui-btn layui-btn-primary" lay-event="batch_set">批量设置</button>';

			$("#toolbarOperation").html(html);
			$("#batchOperation").html(html);

			refreshTable();

		});
		
		// 监听工具栏操作
		table.tool(function (obj) {
			var data = obj.data;
			switch (obj.event) {
                case 'member_price':
                    var url = ns.url("memberprice://shop/goods/config", {request_mode: 'iframe',goods_id: data.goods_id});
					var layerIndex = layer.open({
						title: "自定义会员价",
						type: 2,
						area: ['1100px', '650px'],
						content: url,
						success: function(layero, index){
							form.render();
							var dom_save = layer.getChildFrame("#save_member_price", index);
							var dom_back = layer.getChildFrame("#back_goods_list", index);
							var iframeWin = document.getElementById(layero.find('iframe')[0]['name']).contentWindow;//得到iframe页的窗口对象，执行iframe页的方法
							$(dom_save).click(function(){
								setTimeout(function(){
									iframeWin.memberPriceConfigFormSubmitListener(function(e){
										if (e == 1) {
											layer.close(layerIndex);
											table.reload();
										}
									});
								},300)
							});
							$(dom_back).click(function(){
								layer.close(layerIndex);
							})
						}
					});
                    break;
				case 'spread': //推广
					goodsSpread(data);
					break;
				case 'preview': //预览
					window.open(ns.url('index/index/h5preview',{ id : data.goods_id,type:'goods' }));
					break;
				case 'edit':
					//编辑
					window.open(ns.href( goods_class[data.goods_class].edit_url, {"goods_id": data.goods_id}))
					break;
				case 'goods_card':
					//编辑
					window.open(ns.href( 'cardservice://shop/card/goodscard', {"goods_id": data.goods_id}))
					break;
				case 'copy':
					// 复制
					copyGoods(data.goods_id);
					break;
				case 'delete':
					//删除
					deleteGoods(data.goods_id);
					break;
				case 'off_goods':
					//下架
					offGoods(data.goods_id);
					break;
				case 'on_goods':
					//上架
					onGoods(data.goods_id);
					break;
				case 'editStock':
					editStock(data);
					break;
				case 'browse_records':
					location.hash = ns.hash("shop/goods/goodsBrowse", {goods_id:data.goods_id});
					break;
				case 'evaluate':
					location.hash = ns.hash("shop/goods/evaluate", {goods_id:data.goods_id});
					break;
                case 'more': //更多
                    $('.more-operation').css('display', 'none');
                    $(obj.tr).find('.more-operation').css('display', 'block');
                    break;
                case 'carmichael': //更多
                    location.hash = ns.hash("virtualcard://shop/goods/carmichael", {goods_id:data.goods_id});
                    break;
				case 'verify': //核销
					location.hash = ns.hash("shop/goods/verify", {goods_id:data.goods_id});
					break;
			}
		});

		// 提交修改后的库存
		form.on('submit(edit_stock)', function (obj) {
			var field = obj.field;
			var goods_id = field.goods_id;

			var sku_list = [];
			let all_sku_no_arr = [];
			let is_repeat = false;
			$('.js-edit-sku-list-stock tr').each(function (index,item) {
				var obj = {};
				$(this).find('input').each(function (cIndex,cItem) {
					var name = $(this).attr('name');
					obj[name] = $(this).val();
					if (name == 'is_delivery_restrictions') {
						var isChecked = $('#is_delivery_restrictions_' + obj[
							'sku_id']).is(':checked');
						if (isChecked) {
							obj['is_delivery_restrictions'] = 1
						} else {
							obj['is_delivery_restrictions'] = 0
							// switch 没有被选中
						}
					}
					if(name === 'sku_no' && obj[name]){
						let sku_no_arr = obj[name].split(',');
						sku_no_arr.forEach((sku_no)=>{
							if(all_sku_no_arr.indexOf(sku_no) === -1){
								all_sku_no_arr.push(sku_no);
							}else{
								layer.msg('编码['+sku_no+']不可重复录入');
								is_repeat = true;
								return false;
							}
						});
					}
				});
				sku_list.push(obj);
				if(is_repeat){
					return false;
				}
			});
			if(is_repeat) return false;
			if (all_sku_no_arr.length > 0 && verifySkuNo(all_sku_no_arr.join(','), goods_id)) {
				return;
			}

			if (repeat_flag) return false;
			repeat_flag = true;
			$.ajax({
				type: "POST",
				url: ns.url("shop/goods/editGoodsStock"),
				data: {
					'sku_list': sku_list
				},
				dataType: 'JSON',
				success: function (res) {
					layer.msg(res.message);
					repeat_flag = false;
					if (res.code == 0) {
						table.reload();
						layer.closeAll('page');
					}
				}
			});
		});

        $(document).click(function(event) {
            if ($(event.target).attr('lay-event') != 'more' && $('.more-operation').not(':hidden').length) {
                $('.more-operation').css('display', 'none');
            }
        });

		// 批量操作
		table.bottomToolbar(function (obj) {
			if (obj.data.length < 1) {
				layer.msg('请选择要操作的数据');
				return;
			}
			var id_array = new Array();
			for (i in obj.data) id_array.push(obj.data[i].goods_id);
			switch (obj.event) {
				case "delete":
					deleteGoods(id_array.toString());
					break;
				case 'off_goods':
					//下架
					offGoods(id_array);
					break;
				case 'on_goods':
					//上架
					onGoods(id_array);
					break;
				case 'batch_set':
					layer.open({
						title: "批量设置",
						type: 1,
						area: ['900px', '600px'],
						content: $('#batchSet').html(),
						success: function () {
							batchSetPriceInit();
							form.render();
							fetchCategory('.goods-cate .first .select-category', function (value, node) {
								var categoryId = [];
								node.path.forEach(function (item) {
									categoryId.push(item.value)
								});
								$('.goods-cate .first .category_id').val(categoryId.toString())
							})

							form.on('checkbox(support_trade_type)', function (data) {
								if (data.value == 'express') {
									if ($(data.elem).is(':checked')) {
										$('.shipping .support_trade_type_express').show()
									} else {
										$('.shipping .support_trade_type_express').hide()
									}
								}
							});
							form.on('radio(is_free_shipping)', function (data) {
								if (data.value == 1) {
									$('.support_trade_type_express .shipping_template').hide()
								} else {
									$('.support_trade_type_express .shipping_template').show()
								}
							});

						}
					});
					break;
			}
		});

		table.toolbar(function(obj){
			if (obj.data.length < 1) {
				layer.msg('请选择要操作的数据');
				return;
			}
			var id_array = new Array();
			for (i in obj.data) id_array.push(obj.data[i].goods_id);
			switch (obj.event) {
				case "delete":
					deleteGoods(id_array.toString());
					break;
				case 'off_goods':
					//下架
					offGoods(id_array);
					break;
				case 'on_goods':
					//上架
					onGoods(id_array);
					break;
				case 'batch_set':
				 	layer.open({
			            title: "批量设置",
			            type: 1,
			            area: ['900px', '600px'],
			            content: $('#batchSet').html(),
			            success: function(){
							batchSetPriceInit();
			            	form.render();

							fetchCategory('.goods-cate .first .select-category', function (value, node) {
								var categoryId = [];
								node.path.forEach(function (item) {
									categoryId.push(item.value)
								});
								$('.goods-cate .first .category_id').val(categoryId.toString())
							})

							form.on('checkbox(support_trade_type)', function (data) {
								if (data.value == 'express') {
									if ($(data.elem).is(':checked')) {
										$('.shipping .support_trade_type_express').show()
									} else {
										$('.shipping .support_trade_type_express').hide()
									}
								}
							});
							form.on('radio(is_free_shipping)', function (data) {
								if (data.value == 1) {
									$('.support_trade_type_express .shipping_template').hide()
								} else {
									$('.support_trade_type_express .shipping_template').show()
								}
							});
			            }
			        });
					break;
			}
		});

		table.on("sort",function (obj) {
			table.reload({
				page: {
					curr: 1
				},
				where: {
					order:obj.field,
					sort:obj.type
				}
			});
		});

		// 搜索功能
		form.on('submit(search)', function (data) {
			table.reload({
				page: {
					curr: 1
				},
				where: data.field
			});
			return false;
		});

		// 商品导出
		form.on('submit(batch_export_goods)', function(data){
			var id_array = [];
			var checkedData = table.checkStatus('goods_list').data;
			for (var i in checkedData) id_array.push(checkedData[i].goods_id);

			data.field.goods_ids = id_array.toString(); // 选择要导出的商品

			$.ajax({
				type: 'post',
				dataType: 'json',
				url: ns.url("shop/goods/exportGoods"),
				data: data.field,
				success: function (res) {
				}
			});
			window.open(ns.href("shop/goods/export",{}))
			return false;
		});

		// 验证
		form.verify({
			int: function (value) {
				if (value < 0) {
					return '销量不能小于0!'
				}
				if (value % 1 != 0) {
					return '销量不能为小数!'
				}
			},
		});

		new CategorySelect({
			elem : '#select_category',
			data : [[]],
			level : 'any',//可以选择任意等级
			callback: function(category_data){
				let category_names = [];
				let category_id = 0;
				category_data.forEach(function (item, index) {
					category_id = item.category_id;
					category_names.push(item.category_name);
				})
				$('#select_category').val(category_names.join(' / '));
				$('[name="category_id"]').val(category_id);
			}
		})
	});

	/**
	 * 批量设置添加分类
	 */
	$('body').off('click', '.goods-cate .goods-list-js-add-category').on('click', '.goods-cate .goods-list-js-add-category', function () {
		if ($('.goods-cate .layui-block').length >= 10) {
			layer.msg('最多添加十个分类');
			return;
		}
		var h = `
		<div class="layui-block">
			<div class="category-wrap">
				<input type="text" readonly lay-verify="required" autocomplete="off" class="layui-input select-category"/>
				<input type="hidden" class="category_id"/>
			</div>
			<a href="javascript:;" class="text-color delete-category">删除</a>
		</div>`;
		$('.goods-cate').append(h);

		fetchCategory($('.goods-cate .layui-block:last-child').find('.select-category'), function (value, node) {
			var categoryId = [];
			node.path.forEach(function (item) {
				categoryId.push(item.value)
			});
			$('.goods-cate .layui-block:last-child').find('.category_id').val(categoryId.toString());
		})
	});

	/**
	 * 批量设置删除分类
	 */
	$('body').off('click', '.goods-cate .delete-category').on('click', '.goods-cate .delete-category', function () {
		$(this).parents('.layui-block').remove();
	});

	/**
	 * 渲染分类选择
	 * @param elem
	 * @param callback
	 */
	function fetchCategory(elem, callback){
		if (!goodsCategory.length) {
			$.ajax({
				url : ns.url("shop/goodscategory/lists"),
				dataType: 'JSON',
				type: 'POST',
				async: false,
				success: function(res) {
					goodsCategory = res.data;
				}
			})
		}
		var _cascader = layCascader({
			elem: elem,
			options: goodsCategory,
			props: {
				value: 'category_id',
				label: 'category_name',
				children: 'child_list'
			}
		});
		_cascader.changeEvent(function (value, node) {
			typeof callback == 'function' && callback(value, node)
		});

		$("form").unbind().bind("reset", function(event){
			_cascader.clearCheckedNodes()
		});
	}

	$('body').off('click', '.batch-set-wrap .tab-wrap li').on('click', '.batch-set-wrap .tab-wrap li', function(){
		var type = $(this).attr('data-type');
		$(this).addClass('active').siblings('li').removeClass('active');
		$('.batch-set-wrap .content-wrap .tab-item.'+ type).addClass('tab-show').siblings('.tab-item').removeClass('tab-show');
		$('.batch-set-wrap .footer-wrap').show();
	});

	$('body').off('click', '.batch-set-wrap .shipping .layui-form-radio').on('click', '.batch-set-wrap .shipping .layui-form-radio', function(){
		if ($('[name="is_free_shipping"]:checked').val() == 1) {
			$('.batch-set-wrap .shipping .shipping_template').addClass('hide');
		} else {
			$('.batch-set-wrap .shipping .shipping_template').removeClass('hide');
		}
	});

	setTimeout(()=>{
		var html = "";
		if(goods_sort == "asc"){
			html += '<p>前台商品默认排序为排序号正序排列(即排序号越小越靠前)，如果序号相同，那么按照添加顺序排列，越新添加的越靠前</p>';
			$(".prompt .prompt-box .prompt-con>p").html(html);
		}else{
			html += '<p>前台商品默认排序为排序号倒序排列(即排序号越大越靠前)，如果序号相同，那么按照添加顺序排列，越新添加的越靠前</p>';
			$(".prompt .prompt-box .prompt-con>p").html(html);
		}
	},1000)
});

/**
 * 刷新表格列表
 */
function refreshTable() {
	var cols = [
		[{
			type: 'checkbox',
			unresize: 'false',
			width: '3%'
		}, {
			title: '商品信息',
			unresize: 'false',
			width: '20%',
			templet: '#goods_info'
		}, {
			field: 'goods_class_name',
			title: '商品类型',
			unresize: 'false',
			width: '9%',
		}, {
			field: 'price',
			title: '价格',
			unresize: 'false',
			width: '10%',
			align: 'right',
			templet: function (data) {
				return '￥' + data.price;
			},
			sort: true
		}, {
			field: 'goods_stock',
			title: '库存',
			unresize: 'false',
			width: '10%',
			templet: function (data) {
				if (data.goods_stock_alarm > 0 && data.goods_stock < data.goods_stock_alarm) {
					return `<span style='color: red;' class="goods-stock" lay-event="editStock">${data.goods_stock}${stockConfig.is_audit == 0 ? '<i class="layui-icon layui-icon-edit"></i>' : ''}</span>`;
				}
				return `<span lay-event="editStock" class="goods-stock">${data.goods_stock}${stockConfig.is_audit == 0 ? '<i class="layui-icon layui-icon-edit"></i>' : ''}</span>`;
			},
			sort: true
		}, {
			field: 'sale_num',
			title: '销量',
			unresize: 'false',
			width: '10%',
			sort: true
		},{
			field: 'sort',
			unresize:'false',
			title: `<div class="prompt-block">排序
							<div class="prompt">
								<i class="iconfont iconwenhao1 required growth"></i>
								<div class="growth-box reason-box reason-growth prompt-box">
									<div class="prompt-con">
										<p>前台商品默认排序为排序号正序排列(即排序号越小越靠前)，如果序号相同，那么按照添加顺序排列，越新添加的越靠前</p>
									</div>
								</div>
							</div>
						</div>`,
			width: '10%',
			align: 'center',
			templet: '#editSort',
			sort: true
		}, {
			title: '创建时间',
			unresize: 'false',
			width: '12%',
			templet: function (data) {
				return ns.time_to_date(data.create_time);
			}
		}, {
			title: '状态',
			unresize: 'false',
			width: '6%',
			templet: function (data) {
				var str = '';
				if (data.goods_state == 1) {
					str = '销售中';
				} else if (data.goods_state == 0) {
					str = '仓库中';
				}
				return str;
			}
		}, {
			title: '操作',
			toolbar: '#operation',
			unresize: 'false',
			align: 'right',
		}
		]
	];

	if(member_price_is_exit == 1){
		cols = [
			[{
				type: 'checkbox',
				unresize: 'false',
				width: '3%'
			}, {
				title: '商品信息',
				unresize: 'false',
				width: '20%',
				templet: '#goods_info'
			}, {
				field: 'goods_class_name',
				title: '商品类型',
				unresize: 'false',
				width: '9%',
			}, {
				field: 'price',
				title: '价格',
				unresize: 'false',
				width: '10%',
				align: 'right',
				templet: function (data) {
					return '￥' + data.price;
				},
				sort: true
			}, {
				field: 'goods_stock',
				title: '库存',
				unresize: 'false',
				width: '10%',
				templet: function (data) {
					if (data.goods_stock_alarm > 0 && data.goods_stock < data.goods_stock_alarm) {
						return `<span style='color: red;' class="goods-stock" lay-event="editStock">${data.goods_stock}${stockConfig.is_audit == 0 ? '<i class="layui-icon layui-icon-edit"></i>' : ''}</span>`;
					}
					return `<span lay-event="editStock" class="goods-stock">${data.goods_stock}${stockConfig.is_audit == 0 ? '<i class="layui-icon layui-icon-edit"></i>' : ''}</span>`;
				},
				sort: true
			}, {
				field: 'sale_num',
				title: '销量',
				unresize: 'false',
				width: '10%',
				sort: true
			},{
				field: 'sort',
				unresize:'false',
				title: `<div class="prompt-block">排序
							<div class="prompt">
								<i class="iconfont iconwenhao1 required growth"></i>
								<div class="growth-box reason-box reason-growth prompt-box">
									<div class="prompt-con">
										<p>前台商品默认排序为排序号正序排列(即排序号越小越靠前)，如果序号相同，那么按照添加顺序排列，越新添加的越靠前</p>
									</div>
								</div>
							</div>
						</div>`,
				width: '7%',
				align: 'center',
				templet: '#editSort',
				sort: true
			}, {
				title: '创建时间',
				unresize: 'false',
				width: '12%',
				templet: function (data) {
					return ns.time_to_date(data.create_time);
				}
			},
			// 	{
			// 	title: '会员等级折扣',
			// 	unresize: 'false',
			// 	width: '9%',
			// 	templet: function (data) {
			// 		var str='';
			// 		if(data.is_consume_discount == 1){
			// 			if(data.discount_config == 1){
			// 				if(data.discount_method == 'discount'){
			// 					str = '打折';
			// 				}else if(data.discount_method == 'manjian'){
			// 					str = '减现';
			// 				}else if(data.discount_method == 'fixed_price'){
			// 					str = '指定价格';
			// 				}
			// 			}else{
			// 				str ='默认规则';
			// 			}
			// 		}else{
			// 			str ='不参与';
			// 		}
			// 		return str;
			// 	}
			// },
			{
				title: '状态',
				unresize: 'false',
				width: '6%',
				templet: function (data) {
					var str = '';
					if (data.goods_state == 1) {
						str = '销售中';
					} else if (data.goods_state == 0) {
						str = '仓库中';
					}
					return str;
				}
			}, {
				title: '操作',
				toolbar: '#operation',
				unresize: 'false',
				align: 'right',
			}]
		];
	}

	table = new Table({
		id: 'goods_list',
		elem: '#goods_list',
		url: ns.url("shop/goods/lists"),
		cols: cols,
		toolbar: '#toolbarOperation',
		bottomToolbar: "#batchOperation",
		where: {
			search_text: $("input[name='search_text']").val(),
			goods_state: $("input[name='goods_state']").val(),
			start_sale: $("input[name='start_sale']").val(),
			end_sale: $("input[name='end_sale']").val(),
			category_id: $("input[name='category_id']").val(),
			goods_class: $("select[name='goods_class'] option:checked").val(),
			label_id: $("select[name='label_id'] option:checked").val(),
			promotion_type: $("select[name='promotion_type'] option:checked").val(),
			stockalarm: $("input[name='stockalarm']").val()
		}
	});
}

function add() {
	location.hash = ns.hash('shop/goods/addGoods');
}

function grab() {
	location.hash = ns.hash('goodsgrab://shop/goodsgrab/lists');
}

// 复制
function copyGoods(goods_id) {
	layer.confirm('确定要复制该商品吗?', function (index) {
		if (repeat_flag) return;
		repeat_flag = true;
		layer.close(index);

		$.ajax({
			url: ns.url("shop/goods/copyGoods"),
			data: {goods_id: goods_id},
			dataType: 'JSON',
			type: 'POST',
			success: function (res) {
				layer.msg(res.message);
				repeat_flag = false;
				if (res.code == 0) {
					table.reload();
				}
			}
		});
	});
}

// 删除
function deleteGoods(goods_ids) {
	layer.confirm('删除后进入回收站，确定删除吗?', function (index) {
		if (repeat_flag) return;
		repeat_flag = true;
		layer.close(index);

		$.ajax({
			url: ns.url("shop/goods/deleteGoods"),
			data: {goods_ids: goods_ids.toString()},
			dataType: 'JSON',
			type: 'POST',
			success: function (res) {
				layer.msg(res.message);
				repeat_flag = false;
				if (res.code == 0) {
					table.reload();
				}
			}
		});
	});
}

//商品下架
function offGoods(goods_ids) {
	if (repeat_flag) return;
	repeat_flag = true;

	$.ajax({
		url: ns.url("shop/goods/offGoods"),
		data: {goods_state: 0, goods_ids: goods_ids.toString()},
		dataType: 'JSON',
		type: 'POST',
		success: function (res) {
			layer.msg(res.message);
			repeat_flag = false;
			if (res.code == 0) {
				table.reload();
			}
		}
	});
}

//商品上架
function onGoods(goods_ids) {

	if (repeat_flag) return;
	repeat_flag = true;

	$.ajax({
		url: ns.url("shop/goods/onGoods"),
		data: {goods_state: 1, goods_ids: goods_ids.toString()},
		dataType: 'JSON',
		type: 'POST',
		success: function (res) {
			repeat_flag = false;
			if (res.code == 0) {
				layer.msg(res.data.error_reason || res.message);
				table.reload();
			}else{
				layer.msg(res.message);
			}
		}
	});
}

// 编辑库存
function editStock(data) {
	// 开启库存审核，禁止操作
	if(stockConfig.is_audit == 1) return;

	if (repeat_flag) return false;
	repeat_flag = true;

	$.ajax({
		type: "POST",
		url: ns.url("shop/goods/getGoodsSkuList"),
		data: {
			'goods_id': data.goods_id,
		},
		dataType: 'JSON',
		success: function (res) {
			data.sku_list = res.data;


			form.render();

			laytpl($("#edit_stock").html()).render(data, function (html) {
				layer_stock = layer.open({
					title: '修改库存',
					skin: 'layer-tips-class',
					type: 1,
					area: ['1000px'],
					content: html,
					success: function (){

						form.render();
						table.reload();

						repeat_flag = false;
						// 验证
						form.verify({
							//销售价
							price: function (value) {
								if (value.length == 0) {
									return "请输入销售价";
								}
								if (isNaN(value) || !regExp.digit.test(value) || parseFloat(value) < 0) {
									return '[销售价]格式输入错误';
								}

							},
							//划线价
							market_price: function (value) {
								if (value.length > 0) {
									if (isNaN(value) || !regExp.digit.test(value) || parseFloat(value) < 0) {
										return '[划线价]格式输入错误';
									}
								}
							},
							//成本价
							cost_price: function (value) {
								if (value.length > 0) {
									if (isNaN(value) || !regExp.digit.test(value) || parseFloat(value) < 0) {
										return '[成本价]格式输入错误';
									}
								}
							},
							//重量
							weight: function (value) {
								if (value.length > 0) {
									if (isNaN(value) || !ns.getRegexp('>=0float3').test(value)) {
										return '重量必须为正数，且最多保留三位小数';
									}
								}
							},
							//体积
							volume: function (value) {
								if (value.length > 0) {
									if (isNaN(value) || !ns.getRegexp('>=0float3').test(value)) {
										return '体积必须为正数，且最多保留三位小数';
									}
								}
							},
							// 总库存
							goods_stock: function (value) {
								if (value.length == 0) {
									return "请输入库存";
								}
								if(data.goods_class == 6){
									// 称重商品，三位小数点
									if (isNaN(value) || !regExp.special.test(value) || parseFloat(value) < 0) {
										return '[库存]格式输入错误';
									}
								}else {
									if (isNaN(value) || !regExp.number.test(value) || parseFloat(value) < 0) {
										return '[库存]格式输入错误';
									}
								}
							},
							//核销次数
							verify_num: function (value) {
								if (value.length == 0) {
									return "请输入核销次数";
								}

								if (isNaN(value) || !regExp.number.test(value) || parseFloat(value) < 0) {
									return '[核销次数]格式输入错误';
								}
							},

						});

						// 批量规格操作
						$(".batch-operation-sku span").click(function () {
							var field = $(this).attr("data-field");
							var verify = $(this).attr("data-verify") || "";
							var placeholder = $(this).text();
							$(".batch-val").attr("data-field", field).attr("placeholder", placeholder).attr("data-verify", verify).val("");
							$(".batch-operation-sku span").hide();
							$(".batch-operation-sku input, .batch-operation-sku button").show();
							$(".batch-operation-sku input").focus();
						});

						//批量操作sku输入框
						$(".batch-operation-sku input").keyup(function (event) {
							if (event.keyCode == 13) $(this).next().click();
						});

						//批量操作确定按钮
						$(" .batch-operation-sku .confirm").click(function () {
							var input = $(".batch-val");
							var field = input.attr("data-field");
							var verify = input.attr("data-verify");
							var placeholder = input.attr("placeholder");
							var value = input.val();

							if (value.length == 0) {
								layer.msg("请输入" + placeholder);
								$(this).focus();
								return;
							}

							if (verify) {
								var reg = "";
								switch (verify) {
									// 划线价
									case "market_price":
									// 销售价
									case "price":
									// 成本价
									case "cost_price":
									// 重量
									case "weight":
									// 体积
									case "volume":
										reg = regExp.digit;
										break;
									// 库存
									case "stock":
										// 称重商品，三位小数点
										if(data.goods_class == 6) {
											reg = regExp.special;
										}else{
											reg = regExp.number;
										}
										break;
									// 库存预警
									case "stock_alarm":
										reg = regExp.number;
										break;
								}
								if (!reg.test(value)) {
									layer.msg('[' + placeholder + ']格式输入错误');
									$(this).focus();
									return;
								}
							}
							$('#edit_stock_block .'+field).val(value);
							$(this).next().click();
						});

						//批量操作取消按钮
						$(".batch-operation-sku .cancel").click(function () {
							$(".batch-operation-sku input, .batch-operation-sku button").hide();
							$(".batch-operation-sku span").show();
						});
					},
					error: function (){
						repeat_flag = false;
					}
				});

			});
		}
	});

}

function goodsSpread(data){
	new PromoteShow({
		url:ns.url("shop/goods/goodsUrl"),
		param:{goods_id:data.goods_id},
	})
}

function closeStock() {
	layer.close(layer_stock);
}

// 批量设置
var setSub = false;
function batchSetting(){
	var id_array = new Array(),
		setType = $('.batch-set-wrap .tab-wrap .active').attr('data-type'),
		checkedData = table.checkStatus('goods_list').data,
		field = {},
		regExp = {
			number: /^\d{0,10}$/,
			digit: /^\d{0,10}(.?\d{0,2})$/
		};
		for (i in checkedData) id_array.push(checkedData[i].goods_id);

	switch(setType){
		case 'group':
			field.group = $('[name="batch_goods_label"]').val();
		break;
		case 'service':
			var service = [];
			$('[name="batch_goods_service"]:checked').each(function(e){
				service.push($(this).val());
			});
			field.server_ids = service.length ? service.toString() : '';
		break;
		case 'sale':
			field.sale = $('[name="batch_virtual_sale"]').val();
			if (isNaN(field.sale) || !regExp.number.test(field.sale)) {
				layer.msg('销量格式输入错误');
				return;
			}
			if (field.sale < 0) {
				layer.msg('销量不能小于0');
				return;
			}
		break;
		case 'purchase_limit':
			field.max_buy = $('[name="batch_max_buy"]').val();
			if (isNaN(field.max_buy) || !regExp.number.test(field.max_buy)) {
				layer.msg('限购数量格式输入错误');
				return;
			}
			if (field.max_buy < 0) {
				layer.msg('限购数量不能小于0');
				return;
			}
		break;
		case 'shipping':
			var supportTradeType = [];
			$('.shipping [name="support_trade_type"]:checked').each(function () {
				supportTradeType.push($(this).val())
			});
			field.support_trade_type = supportTradeType.toString();
			field.is_free_shipping = $('[name="is_free_shipping"]:checked').val();
			field.shipping_template = $('[name="batch_shipping_template"]').val();

			if(!field.support_trade_type) {
				layer.msg('请选择配送方式');
				return;
			}

			if (field.support_trade_type.indexOf('express') != -1 && field.is_free_shipping == 0 && field.shipping_template == 0) {
				layer.msg('请选择运费模板');
				return;
			}
		break;
		case 'category':
			var category_id = [];
			$(".goods-cate .layui-block").each(function () {
				var cate_id = $(this).find(".category_id").val();
				category_id.push(cate_id);
			});
			field.category_id = category_id;
			if (field.category_id == 0) {
				layer.msg('请选择商品分类');
				return;
			}
			break;
		case 'member_price':
			field.is_consume_discount = $('[name="is_consume_discount"]:checked').val();
			break;
		case 'stock':
			var stock_type = $('[name="stock_type"]:checked').val();
			field.stock = $('[name="stock_num"]').val();
			field.stock_type = stock_type;
			break;
		case 'price':
			var patent_obj = $(".batch-set-wrap");
			var type = patent_obj.find('[name="type"]:checked').val();
			field.type = type;
			field.price_type = patent_obj.find('[name="price_type"]').val();
			field.price = patent_obj.find('[name="price"]').val();
			if(type == 'calculate'){
				field.sign = patent_obj.find('[name="sign"]').val();
				field.precise = patent_obj.find('[name="precise"]:checked').val();
				field.calculate_price = patent_obj.find('[name="calculate_price"]').val();
				field.calculate_price_type = patent_obj.find('[name="calculate_price_type"]').val();
				if (!/[\S]+/.test(field.price)) {
					layer.msg(( (field.sign == 'multiply' || field.sign == 'division') ? '请输入调整的倍数' :'请输入调整的金额'));
					return;
				}
			} else {
				if (!/[\S]+/.test(field.price)) {
					layer.msg('请输入调整的金额');
					return;
				}
			}
			break;
		case 'goods_form':
			field.form_id = $('[name="batch_goods_form"]').val();
			break;
		case 'goods_brand':
			field.brand_id = $('[name="batch_goods_brand"]').val();
			break;
	}

	if (setSub) return;
	setSub = true;

	$.ajax({
		type: "POST",
		url: ns.url("shop/goods/batchSet"),
		data: {
			'type': setType,
			'goods_ids': id_array.toString(),
			'field' : JSON.stringify(field)
		},
		dataType: 'JSON',
		success: function (res) {
			setSub = false;
			if (res.code >= 0) {
				$('.batch-set-wrap .footer-wrap').hide();
				$('.batch-set-wrap .content-wrap .tab-item.result').addClass('tab-show').siblings('.tab-item').removeClass('tab-show');
				if(['group','price','stock','member_price'].indexOf(setType) != -1) {
					table.reload();
				}
			} else {
				layer.msg(res.message);
			}
		}
	})
}
function stockinput(obj){
	$(obj).val(Math.abs(parseInt($(obj).val())));
}

// 监听单元格编辑
function editSort(goods_id, event) {
	var data = $(event).val();

	if (data == '') {
		$(event).val(0);
		data = 0;
	}

	if (!new RegExp("^-?[0-9]\\d*$").test(data)) {
		layer.msg("排序号只能是整数");
		return;
	}
	/*if (data < 0) {
		layer.msg("排序号必须大于0");
		return;
	}*/
	$.ajax({
		type: 'POST',
		url: ns.url("shop/goods/modifySort"),
		data: {
			goods_id: goods_id,
			sort: data
		},
		dataType: 'JSON',
		success: function (res) {
			layer.msg(res.message);
			if (res.code == 0) {
				table.reload();
			}
		}
	});
}

$(".layui-colla-title").on("click", function(){
    if($(".layui-colla-title>i").hasClass("layui-icon-down") === false && $(".layui-colla-title>i").hasClass("layui-icon-up") === false){
        $(".layui-colla-title .put-open").html("展开");
    }else if($(".layui-colla-title>i").hasClass("layui-icon-down") === true){
        $(".layui-colla-title .put-open").html("展开");
    }else if($(".layui-colla-title>i").hasClass("layui-icon-up") === true){
        $(".layui-colla-title .put-open").html("收起");
    }
});

//批量设置价格初始化
function batchSetPriceInit(){
	form.on('radio(type)', function (data) {
		$(".price-calculate-block").hide();
		$('.price-unit').hide();
		$('.precise-block').hide();
		if(data.value != 'money'){
			$('.price-calculate-block').show();
			$('.calculate-price-unit').show();
			$('.precise-block').show();
		}else{
			$('.money-price-unit').show();
		}
	});
	form.on('select(sign)', function (data) {
		var unit_str = '';
		switch(data.value){
			case 'add':
				unit_str = '元';
				break;
			case 'subtract':
				unit_str = '元';
				break;
			case 'multiply':
				unit_str = '倍';
				break;
			case 'division':
				unit_str = '倍';
				break;
		}
		$('.calculate-price-unit').text(unit_str);
	});

}

//商品详情
function pc_url(sku_id) {
	window.open(pc_domain + '/sku/' + sku_id);
}

// 验证商品编码是否重复
function verifySkuNo(sku_no, goods_id) {
	var count = 0;
	$.ajax({
		url: ns.url("shop/goods/verifySkuNo"),
		dataType: 'JSON',
		type: 'POST',
		data:{
			sku_no : sku_no,
			goods_id: goods_id,
		},
		async: false,
		success: function (res) {
			if (res.code < 0) {
				count = res.data;
				layer.msg(res.message);
			}
		}
	});
	return count;
}
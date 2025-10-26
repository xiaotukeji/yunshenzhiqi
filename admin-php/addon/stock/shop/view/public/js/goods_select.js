// 商品选择弹出框
var form, laytpl, element, table;
var goodsSelectObj = {
	store_id: 0,
	selectList: [], // 选中商品所有数据res
	maxNum: 0, // 最大商品数量
	minNum: 0, // 最小商品数量
	disabled: 0, // 不可选中
	cols: [], // 列名数据源
	filterData: { goods_name: '' }, //筛选数据
	goodsIdArr: []
};
$(function () {

	$('.select-goods input[type="hidden"]').each(function () {
		goodsSelectObj[$(this).attr('name')] = $(this).val();
	});

	setCols();

	layui.use(['form', 'laytpl', 'element'], function () {
		form = layui.form, laytpl = layui.laytpl, element = layui.element;
		element.init();
		var where = {
			goods_class: goodsSelectObj.goods_class,
			search_text: goodsSelectObj.search_text,
			store_id: goodsSelectObj.store_id,
			callback: function () {
				// goodsSelectObj.selectList = [];
				// goodsSelectObj.goodsIdArr = [];
				$('div[lay-id="goods_list"] th[data-field="0"] input[type="checkbox"]').prop('checked', false);
				$('div[lay-id="goods_list"] th[data-field="0"] .layui-unselect').removeClass('layui-form-checked');
			}
		}

		table = new Table({
			elem: '#goods_list',
			url: ns.url('stock://shop/stock/goodsSelect'),
			cols: goodsSelectObj.cols,
			where
		});

		//修改一级分类箭头切换
		element.on('collapse(oneCategory)', function (data) {
			$(".layui-colla-title").removeClass("active");
			console.log(data.show)
			if (data.show) {
				$(data.title).addClass("active");
			}
		});

		//修改二级分类箭头切换
		element.on('collapse(twoCategory)', function (data) {
			$(".select-goods-classification .select-goods-classification .layui-colla-title").removeClass("active");
			if (data.show) {
				$(data.title).addClass("active");
			}
		});

		//搜索商品名称或编码
		form.on('submit(search)', function (data) {
			formSearch();
		});

		// 勾选商品
		form.on('checkbox(goods_checkbox_all)', function (data) {
			var all_checked = data.elem.checked;
			$("input[name='goods_checkbox']").each(function () {
				var checked = $(this).prop('checked');
				if (all_checked != checked) {
					$(this).next().click();
				}
			});
			dealWithTableSelectedNum();
		});

		// 勾选商品
		form.on('checkbox(goods_checkbox)', function (data) {
			var sku_id = $(data.elem).attr("data-sku-id"),
				json = {};

			form.render();

			var spuLen = $("input[name='goods_checkbox'][data-sku-id=" + sku_id + "]:checked").length;
			if (spuLen) {
				json = JSON.parse($("input[name='goods_json'][data-sku-id=" + sku_id + "]").val());

				delete json.LAY_INDEX;
				delete json.LAY_TABLE_INDEX;

				goodsSelectObj.selectList.push(json);
				goodsSelectObj.goodsIdArr.push(sku_id);

			} else {

				for (var i = 0; i < goodsSelectObj.selectList.length; i++) {
					if (goodsSelectObj.selectList[i].sku_id == sku_id) {
						goodsSelectObj.selectList.splice(i, 1);
						break;
					}
				}

				for (var i = 0; i < goodsSelectObj.goodsIdArr.length; i++) {
					if (goodsSelectObj.goodsIdArr[i] == sku_id) {
						goodsSelectObj.goodsIdArr.splice(i, 1);
						break;
					}
				}
			}
			$.unique(goodsSelectObj.goodsIdArr);
			dealWithTableSelectedNum();
		});

		$(".select-goods .select-goods-left dd").hover(function () {
			$(this).addClass("active");
		}, function () {
			$(this).removeClass("active");
		});

		$("body").off("click", ".select-goods-left dl").on("click", ".select-goods-left dl", function () {
			if ($(this).hasClass("fold")) {
				$(this).removeClass("fold");
			} else {
				$(this).addClass("fold");
			}
		});

		$("body").off("click", ".select-goods-left dd").on("click", ".select-goods-left dd", function (event) {
			$(this).parents("dl").removeClass("fold");
			$(this).parents("dl").siblings().addClass("fold");
			event.stopPropagation();
		});

		//分类切换
		$("body").off("click", ".classification-item").on("click", ".classification-item", function (event) {
			var categoryId = $(this).attr("data-category_id");
			$(".classification-item").removeClass("text-color border-after-color");
			$(this).addClass("text-color border-after-color");
			$("input[name='category_id']").val(categoryId);
			formSearch();
			event.stopPropagation();
		});

	});

});

// 设置列名
function setCols() {
	goodsSelectObj.cols = [
		[
			{
				title: '<input type="checkbox" name="goods_checkbox_all" lay-skin="primary" lay-filter="goods_checkbox_all">',
				unresize: 'false',
				width: '8%',
				templet: '#checkbox',
			},
			{
				title: '商品',
				unresize: 'false',
				width: '62%',
				templet: '#goods_info'
			},
			{
				templet: `<span>{{d.real_stock||0}}</span>`,
				title: '库存',
				unresize: 'false',
				width: '15%'
			},

			{
				title: '单位',
				unresize: 'false',
				width: '15%',
				templet: `<span>{{d.unit||'件'}}</span>`
			},
		]
	];

}

//公共搜索方法
function formSearch() {
	var data = {};
	data.search_text = $("input[name='search_text']").val();
	data.label_id = $("select[name='label_id']").val();
	data.goods_class = $("select[name='goods_class']").val();
	data.category_id = $("input[name='category_id']").val();
	data.goods_ids = goodsSelectObj.goodsIdArr.toString();
	table.reload({
		page: {
			curr: 1
		},
		where: data
	});

}
//在表格底部增加了一个容器
function dealWithTableSelectedNum() {
	$(".layui-table-bottom-left-container").html('已选择 ' + goodsSelectObj.goodsIdArr.length + ' 个商品');
}

function selectGoodsListener(callback) {
	var res = goodsSelectObj.selectList;
	var num = goodsSelectObj.goodsIdArr.length;
	if (num == 0) {
		layer.msg('请选择商品');
		return;
	}
	if (goodsSelectObj.maxNum && goodsSelectObj.maxNum > 0 && num > goodsSelectObj.maxNum) {
		layer.msg("所选商品数量不能超过" + goodsSelectObj.maxNum + '件');
		return;
	}

	if (goodsSelectObj.minNum && goodsSelectObj.minNum > 0 && num < goodsSelectObj.minNum) {
		layer.msg("所选商品数量不能少于" + goodsSelectObj.minNum + '件');
		return;
	}
	callback(res);
}

// 清除选中项
function clearSelection() {
	table.reload({
		selected: false
	});
	goodsSelectObj.selectList = [];
	goodsSelectObj.goodsIdArr = [];
	dealWithTableSelectedNum()
}
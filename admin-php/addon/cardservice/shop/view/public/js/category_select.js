var layCascader, goodsCategory = [];
layui.use(['layCascader'], function () {
	layCascader = layui.layCascader;

	$('.goods-category-con-wrap .layui-block').each(function () {
		var category_id = $(this).find('.category_id').val();
		var _this = this;

		fetchCategory({elem: $(this).find('.select-category'), value: category_id ? parseInt(category_id.split(',').splice(-1)) : ''},
			function (value, node) {
				var categoryId = [];
				node.path.forEach(function (item) {
					categoryId.push(item.value)
				});
				$(_this).find('.category_id').val(categoryId.toString())
			}
		)
	});
});

// 刷新项目分类，更新选择项目分类数据
$('body').off('click', '.goods-category-con-wrap .js-refresh-category').on('click', '.goods-category-con-wrap .js-refresh-category', function () {
	$.ajax({
		url : ns.url("cardservice://shop/servicecategory/lists"),
		dataType: 'JSON',
		type: 'POST',
		async: false,
		success: function(res) {
			goodsCategory = res.data;

			// 刷新商品分类下拉框数据
			$('.goods-category-con-wrap .layui-block').each(function () {
				$(this).find('.el-cascader').remove(); // 清空渲染
				var category_id = $(this).find('.category_id').val();
				var _this = this;

				fetchCategory({elem: $(this).find('.select-category'), value: category_id ? parseInt(category_id.split(',').splice(-1)) : ''},
					function (value, node) {
						var categoryId = [];
						node.path.forEach(function (item) {
							categoryId.push(item.value)
						});
						$(_this).find('.category_id').val(categoryId.toString())
					}
				)
			});

		}
	});
});

$('body').off('click', '.goods-category-wrap-box .js-add-category').on('click', '.goods-category-wrap-box .js-add-category', function () {
	if ($('.goods-category-con-wrap .layui-block').length >= 10) {
		layer.msg('最多添加十个分类');
		return;
	}
	var h = `<div class="layui-block">
		<div class="layui-input-inline cate-input-default">
			<input type="text" readonly lay-verify="required" autocomplete="off" class="layui-input len-mid select-category" />
			<input type="hidden" class="category_id" />
		</div>
		<a href="javascript:;" class="text-color js-delete-category">删除</a>
	</div>`;
	$('.goods-category-con-wrap').append(h);

	fetchCategory({elem: $('.goods-category-con-wrap .layui-block:last-child').find('.select-category')}, function (value, node) {
		var categoryId = [];
		node.path.forEach(function (item) {
			categoryId.push(item.value)
		});
		$('.goods-category-con-wrap .layui-block:last-child').find('.category_id').val(categoryId.toString());
	})
});

$('body').off('click', '.goods-category-con-wrap .js-delete-category').on('click', '.goods-category-con-wrap .js-delete-category', function () {
	$(this).parents('.layui-block').remove();
});

/**
 * 渲染分类选择
 * @param option
 * @param callback
 */
function fetchCategory(option, callback){
	if (!goodsCategory.length) {
		$.ajax({
			url : ns.url("cardservice://shop/servicecategory/lists"),
			dataType: 'JSON',
			type: 'POST',
			async: false,
			success: function(res) {
				goodsCategory = res.data;
			}
		})
	}
	var _option = {
		options: goodsCategory,
		props: {
			value: 'category_id',
			label: 'category_name',
			children: 'child_list'
		}
	};
	if (option) Object.assign(_option, option);
	var _cascader = layCascader(_option);
	_cascader.changeEvent(function (value, node) {
		typeof callback == 'function' && callback(value, node)
	});
}
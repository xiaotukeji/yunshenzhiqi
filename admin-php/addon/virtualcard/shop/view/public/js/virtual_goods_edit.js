// 单规格卡密
var carmichaelSingle = [];

requestAdd = 'virtualcard://shop/goods/addGoods';
requestEdit = 'virtualcard://shop/goods/editGoods';

// 追加刷新商品sku数据
appendRefreshGoodsSkuData = {
	verify_num: 1
};

// 追加单规格数据
function appendSingleGoodsData(data) {
	return {
		carmichael: carmichaelSingle
	};
}

$(function () {

	layui.use(['element', 'laytpl', 'form'], function () {
		form = layui.form;
		element = layui.element;
		laytpl = layui.laytpl;
		form.render();

	});

});

$('.spu-card-manage').click(function () {
	var textarea = '';
	if (carmichaelSingle.length) {
		for (var i = 0; i < carmichaelSingle.length; i++) {
			if (i == 0) {
				textarea += carmichaelSingle[i];
			} else {
				textarea += "\n" + carmichaelSingle[i];
			}
		}
	}
	laytpl($('#carmichaelTemplate').html()).render({textarea: textarea}, function (html) {
		var index = layer.open({
			title: '添加卡密',
			skin: 'layer-tips-class',
			type: 1,
			area: ['610px', '350px'],
			content: html,
			btn: ['保存', '关闭'],
			yes: function () {
				var carmichael = $('.add-carmichael .layui-textarea').val().trim();
				if (carmichael.length) {
					carmichaelSingle = carmichael.split("\n");
					carmichaelSingle = carmichaelSingle.filter(function (str) {
						return /[\S]+/.test(str);
					})
				} else {
					carmichaelSingle = [];
				}
				$('.spu-card-manage span').text('【' + carmichaelSingle.length + '】');
				layer.close(index);
			}
		})
	})
});

$('body').off('click', '.sku-card-manage').on('click', '.sku-card-manage', function () {
	var index = $(this).attr('data-index');
	var textarea = '';

	if (goodsSkuData[index].carmichael && goodsSkuData[index].carmichael.length) {
		for (var i = 0; i < goodsSkuData[index].carmichael.length; i++) {
			if (i == 0) {
				textarea += goodsSkuData[index].carmichael[i];
			} else {
				textarea += "\n" + goodsSkuData[index].carmichael[i];
			}
		}
	}
	laytpl($('#carmichaelTemplate').html()).render({textarea: textarea}, function (html) {
		var layerindex = layer.open({
			title: '添加卡密',
			skin: 'layer-tips-class',
			type: 1,
			area: ['610px', '350px'],
			content: html,
			btn: ['保存', '关闭'],
			yes: function () {
				var carmichael = $('.add-carmichael .layui-textarea').val().trim();
				if (/[\S]+/.test(carmichael)) {
					var carmichaelArr = carmichael.split("\n");
					goodsSkuData[index].carmichael = carmichaelArr.filter(function (str) {
						return /[\S]+/.test(str);
					})
				} else {
					goodsSkuData[index].carmichael = [];
				}
				$('.sku-card-manage:eq(' + index + ') span').text('【' + goodsSkuData[index].carmichael.length + '】')
				layer.close(layerindex);
			}
		})
	})
});
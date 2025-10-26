var floorForm, floorLayer, floorUpload, floorLaytpl, floorColorpicker, repeatFlag = false;
layui.use(['form', 'layer', 'upload', 'laytpl', 'colorpicker'], function () {
	floorForm = layui.form;
	floorLayer = layui.layer;
	floorUpload = layui.upload;
	floorLaytpl = layui.laytpl;
	floorColorpicker = layui.colorpicker;
	floorForm.render();

	if ($("#info").length > 0) {
		setTimeout(function () {
			vm.data = JSON.parse($("#info").val().toString());
			vm.blockId = parseInt($("#block_id").val().toString());
		}, 100);
	}

	floorForm.on('select(block_id)', function (data) {
		var value = $(data.elem).find("option:selected").attr("data-value");
		var blockId = $(data.elem).find("option:selected").attr("data-block-id");
		vm.blockId = blockId;
		if (value) {
			vm.data = JSON.parse(value);
		}
	});

	floorForm.verify({
		title: function (value) {
			if (value == '') {
				return '请输入楼层名称';
			}
			if (value.length > 100) {
				return '最多100个字符';
			}
		},
		block_id: function (value) {
			if (!value) return '请选择楼层模板';
		}
	});

	floorForm.on('submit(save)', function (data) {

		var value = JSON.parse(JSON.stringify(vm.data));
		for (var i in value) {
			if ($.inArray(value[i].type, ['goods', 'brand', 'category']) == -1) {
				value[i].value.list = [];
			}
		}
		data.field.value = JSON.stringify(value);
		if (repeatFlag) return;
		repeatFlag = true;

		$.ajax({
			url: ns.url("pc://shop/pc/editFloor"),
			data: data.field,
			dataType: 'JSON',
			type: 'POST',
			success: function (res) {
				floorLayer.msg(res.message);
				if (res.code == 0) {
					location.hash = ns.hash("pc://shop/pc/floor");
				}
				repeatFlag = false;
			}
		});
	});
});

var vm = new Vue({
	el: "#app",
	data: function () {
		return {
			data: null,
			blockId: 0
		};
	},
	created: function () {
	},
	methods: {
		img: function (url, type = '') {
			return url ? ns.img(url, type) : "";
		},
		/**
		 * 初始化链接下拉框
		 * @param select_tag
		 * @param link_tag
		 */
		initLink: function (select_tag, link_tag) {
			floorForm.on('select(' + select_tag + ')', function (data) {
				var title = $(data.elem).find("option:selected").text();
				if (data.value != 'diy') {
					$("input[name='" + link_tag + "']").val(JSON.stringify({
						"title": title,
						"url": data.value
					}));
				} else {
					floorLayer.prompt({
						formType: 2,
						value: $("input[name='" + link_tag + "']").val() ? JSON.parse($("input[name='" + link_tag + "']").val()).url : '',
						title: '自定义链接地址',
						area: ['450px', '100px'],
						cancel: function () {
							$("input[name='" + link_tag + "']").val("");
						}
					}, function (value, index, elem) {
						$("input[name='" + link_tag + "']").val(JSON.stringify({
							"title": title,
							"url": value
						}));
						floorLayer.close(index);
					});
				}
			});
		},
		/**
		 * 设置文本
		 * @param data 当前数据
		 * @param callback 回调
		 */
		setText: function (data, callback) {
			var self = this;
			var getTpl = $("#setTitleHtml").html();
			if (!data) data = {};
			floorLaytpl(getTpl).render(data, function (html) {
				var textLayer = floorLayer.open({
					type: 1,
					title: "编辑文本",
					content: html,
					area: ['400px', 'auto'],
					success: function (layero, index) {
						floorForm.render();
						self.initLink("pc_link_text", "text_link");

						// 文字颜色
						floorColorpicker.render({
							elem: '#text_color',  //绑定元素
							color: data.color ? data.color : "",
							done: function (color) {
								$("#text_color_input").attr("value", color);
							}
						});

						floorForm.on('submit(save_text)', function (data) {
							if (data.field.text_link) data.field.text_link = JSON.parse(data.field.text_link);
							if (callback) callback({
								text: data.field.text,
								link: data.field.text_link,
								color: data.field.text_color,
								textAlign: data.field.textAlign
							});
							floorLayer.close(textLayer);
						});
					}
				});
				floorForm.render();
			});
		},
		/**
		 * 上传图片
		 * @param data 当前数据
		 * @param callback 回调
		 */
		uploadImg: function (data, callback) {
			var self = this;
			var getTpl = $("#uploadImg").html();
			if (!data) data = {};
			floorLaytpl(getTpl).render(data, function (html) {
				var textLayer = floorLayer.open({
					type: 1,
					title: "上传图片",
					content: html,
					area: ['450px', '300px'],
					success: function (layero, index) {
						floorForm.render();
						floorUpload.render({
							elem: "#upload_image",
							url: ns.url("shop/upload/upload"),
							done: function (res) {
								$("input[name='upload_image']").val(res.data.pic_path);
								$("#upload_image").html("<img src=" + ns.img(res.data.pic_path) + " >");
							}
						});
						self.initLink("pc_link_upload", "upload_link");
						floorForm.on('submit(save_upload)', function (data) {
							if (data.field.upload_link) data.field.upload_link = JSON.parse(data.field.upload_link);
							if (callback) callback({
								url: data.field.upload_image,
								link: data.field.upload_link
							});
							floorLayer.close(textLayer);
						});
					}
				});
				floorForm.render();
			});
		},
		/**
		 * 设置商品分类
		 * @param data 当前数据
		 * @param callback 回调
		 */
		setCategory: function (data, callback) {
			var self = this;
			var getTpl = $("#setCategoryHtml").html();
			if (!data) data = {};
			floorLaytpl(getTpl).render(data, function (html) {
				var textLayer = floorLayer.open({
					type: 1,
					title: "编辑商品分类",
					content: html,
					area: ['600px', '400px'],
					success: function (layero, index) {
						floorForm.render();
						floorForm.on('select(goods_category)', function (categoryData) {
							var category_name = $.trim($(categoryData.elem).find("option:selected").text());
							var category_id = $(categoryData.elem).val();
							var isAdd = true;
							for (var i = 0; i < data.list.length; i++) {
								if (data.list[i].category_id == category_id) {
									isAdd = false;
									break;
								}
							}
							if (isAdd) {
								data.list.push({
									category_id: category_id,
									category_name: category_name
								});
								floorLaytpl(getTpl).render(data, function (html) {
									$(".set-category").html(html);
									floorForm.render();
								});
							}
						});

						floorForm.on('submit(save_category)', function (data) {
							if (data.field.category_ids) data.field.category_ids = data.field.category_ids.replace(/\s+/g, "");
							if (callback) callback({
								category_ids: data.field.category_ids,
								list: data.field.category_list ? JSON.parse(data.field.category_list) : [],
							});
							floorLayer.close(textLayer);
						});

                        $(document).on('click','.delete-category',function(){
							var catrgoryId = $(this).attr('data-id');
							var data_catrgoryIds = data.category_ids.split(',');
								for (var i = 0; i< data_catrgoryIds.length; i ++) {
									if(catrgoryId == data_catrgoryIds[i]){
										delete data_catrgoryIds[i];
									}
								}
                            data.category_ids = data_catrgoryIds.toString();
							if(data_catrgoryIds.length == 2){
                                data.category_ids = data.category_ids.substring(0, data.category_ids.length-1)
							}
							
							for (var y = 0; y < data.list.length; y ++ ){
                                if(data.list[y].category_id == catrgoryId){
                                    data.list.splice(y, 1);
                                }
							}

                            floorLaytpl(getTpl).render(data, function (html) {
                                $(".set-category").html(html);
                                floorForm.render();
                            });
                        });
					}
				});
				floorForm.render();
			});
		},
	}
});

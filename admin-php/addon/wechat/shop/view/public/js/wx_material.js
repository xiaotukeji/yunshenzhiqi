/**
 * 素材列表
 */
layui.use(['form', 'table', 'element'], function() {
	var form = layui.form,
		element = layui.element;

	form.render();

	element.on('tab(list_tab)', function() {
		table.reload({
			page: {
				curr: 1
			},
			where: {
				'type': this.getAttribute('lay-id')
			}
		});
	});

	var table = new Table({
		elem: '#graphic_message_list',
		filter: "graphic_message",
		url: ns.url('wechat://shop/material/lists'),
		cols: [[
			{
				field: 'value',
				width: '30%',
				title: '标题',
				align: 'center',
				templet: '#title',
				unresize : 'true'
			},
			{
				field: 'create_time',
				width: '30%',
				title: '创建时间',
				align: 'center',
				templet: '#create_time',
				unresize : 'true'
			},
			{
				field: 'update_time',
				width: '25%',
				title: '更新时间',
				align: 'center',
				templet: '#update_time',
				unresize : 'true'
			},
			{
				title: '操作',
				width: '15%',
				toolbar: '#operation',
				unresize : 'true',
				align:'right'
			}
		]]
	});
	//监听工具条
	table.tool(function(obj) {
		var data = obj.data;
		switch (obj.event) {
			case "edit":
				if(data.type == 1){
					location.hash = ns.hash('wechat://shop/material/edit', {"id": data.id});
				}else{
					location.hash = ns.hash('wechat://shop/material/edittextmaterial', {"id": data.id});
				}
				break;
			case "delete":
				delMaterial(data.id, 1, data.media_id);
				break;
		}
	});
	/**
	 * 删除素材
	 */
	function delMaterial(id, type, media_id) {
		layer.confirm(
			'确认删除？', {
				btn: ['确认', '取消'],
			},
			function(index, layero) {
				$.ajax({
					url: ns.url('wechat://shop/material/delete'),
					data: {
						id,
						media_id
					},
					dataType: "JSON",
					success: function(res) {
						layer.msg(res.message);
						if (res.code == 0) {
							table.reload()
						}
					}
				});
				layer.close(index);
			},
			function(index) {
				layer.close(index);
			}
		);
	
	}
	
});


/**
 * 图文消息预览
 */
function preview(id, index = 0) {
	var url = ns.href('wechat://shop/material/previewgraphicmessage', {
		"id": id,
		"i": index
	});
	window.open(url);
}

function previewtext(id, index = 0) {
	var url = ns.href('wechat://shop/material/previewtextmessage', {
		"id": id,
		"i": index
	});
	window.open(url);
}


var storeShowStyle = '<div>';
		storeShowStyle += '<div class="layui-form-item">';
			storeShowStyle += '<label class="layui-form-label sm">选择风格</label>';
			storeShowStyle += '<div class="layui-input-block">';
				storeShowStyle += '<div v-if="data.styleName" class="input-text text-color selected-style" @click="selectGoodsStyle()">{{data.styleName}} <i class="layui-icon layui-icon-right"></i></div>';
				storeShowStyle += '<div v-else class="input-text selected-style" @click="selectGoodsStyle()">选择 <i class="layui-icon layui-icon-right"></i></div>';
			storeShowStyle += '</div>';
		storeShowStyle += '</div>';
	storeShowStyle += '</div>';

Vue.component("store-show-style", {
	template: storeShowStyle,
	data: function() {
		return {
			data: this.$parent.data,
		}
	},
	created:function() {
		if(!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法

		this.$parent.data.ignore = ['elementAngle','componentAngle','elementBgColor','componentBgColor']; //加载忽略内容 -- 其他设置中的属性设置
		this.$parent.data.ignoreLoad = true; // 等待忽略数组赋值后加载
	},
	methods: {
		verify: function (index) {
			var res = { code: true, message: "" };
			return res;
		},
		selectGoodsStyle: function() {
			var self = this;
			layer.open({
				type: 1,
				title: '风格选择',
				area:['800px','270px'],
				btn: ['确定', '返回'],
				content: $(".draggable-element[data-index='" + self.data.index + "'] .edit-attribute .store-style").html(),
				success: function(layero, index) {
					$(".layui-layer-content input[name='style']").val(self.data.style);
					$(".layui-layer-content input[name='style_name']").val(self.data.styleName);
					$("body").off("click", ".layui-layer-content .style-list-con-store .item").on("click", ".layui-layer-content .style-list-con-store .item", function () {
						$(this).addClass("selected border-color").siblings().removeClass("selected border-color");
						$(".layui-layer-content input[name='style']").val($(this).index() + 1);
						$(".layui-layer-content input[name='style_name']").val($(this).find("span").text());
					});
				},
				yes: function (index, layero) {
					self.data.style = $(".layui-layer-content input[name='style']").val();
					self.data.styleName = $(".layui-layer-content input[name='style_name']").val();
					layer.closeAll()
				}
			});
		},
	}
});
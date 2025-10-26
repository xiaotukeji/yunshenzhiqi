var storeLabelHtml = '<div style="display:none;"></div>';

Vue.component("store-label-sources", {
	template: storeLabelHtml,
	data: function() {
		return {
			data: this.$parent.data,
			labelSources: {
				initial: {
					text: "默认",
					icon: "iconmofang"
				},
				diy: {
					text: "手动选择",
					icon: "iconshoudongxuanze"
				},
			},
			thicknessList: [
				{name: "加粗", src: "iconbold", value: "bold"},
				{name: "常规", src: "iconbold-copy", value: "normal"}
			]
		}
	},
	created:function() {
		if(!this.$parent.data.verify) this.$parent.data.verify = [];
		this.$parent.data.verify.push(this.verify);//加载验证方法

		this.$parent.data.ignore = ['elementAngle','elementBgColor']; //加载忽略内容 -- 其他设置中的属性设置
		this.$parent.data.ignoreLoad = true; // 等待忽略数组赋值后加载

		if(Object.keys(this.$parent.data.previewList).length == 0) {
			for (var i = 1; i < 4; i++) {
				this.$parent.data.previewList["label_id_" + ns.gen_non_duplicate(i)] = {
					label_name: "标签名称",
					label_id:0
				};
			}
		}

		// 组件所需的临时数据
		this.$parent.data.tempData = {
			labelSources:this.labelSources,
			thicknessList:this.thicknessList,
			methods: {
				addLabel: this.addLabel,
				iconStyle: this.iconStyle
			}
		};

		this.fetchIconColor();

	},
	methods: {
		verify: function (index) {
			var res = {code: true, message: ""};
			return res;
		},
		/**
		 * 选择图标风格
		 * @param event
		 */
		iconStyle(event) {
			var self = this;
			selectIconStyle({
				elem: event.currentTarget,
				icon: self.data.icon,
				callback: function (data) {
					if (data) {
						self.data.style = data;
					} else {
						iconStyleSet({
							style: JSON.stringify(self.data.style),
							query: {
								icon: self.data.icon
							}
						}, function (style) {
							self.data.style = style;
						})
					}
				}
			})
		},
		/**
		 * 渲染颜色组件
		 * @param id
		 * @param color
		 * @param callback
		 */
		colorRender(id, color, callback) {
			setTimeout(function () {
				Colorpicker.create({
					el: id,
					color: color,
					change: function (elem, hex) {
						callback(elem, hex)
					}
				});
			})
		},
		/**
		 * 渲染图标颜色选择器
		 */
		fetchIconColor() {
			var self = this;
			self.colorRender('store-label-color-' + self.data.index, '', function (elem, color) {
				if (self.data.style.iconBgColor.length || self.data.style.iconBgImg) {
					self.data.style.iconBgColor = [color];
				} else {
					self.data.style.iconColor = [color];
				}
				self.$forceUpdate();
			});
		},
		addLabel: function () {
			var self = this;
			self.labelSelect(function (res) {
				self.$parent.data.labelIds = [];
				self.$parent.data.previewList = {};
				for (var i = 0; i < res.length; i++) {
					self.$parent.data.labelIds.push(res[i].label_id);
					self.$parent.data.previewList["label_id_" + ns.gen_non_duplicate(i)] = {
						label_name: res[i].label_name,
						label_id: res[i].label_id
					};
				}
			}, self.$parent.data.labelIds);
		},
		labelSelect: function (callback, selectId) {
			var self = this;
			layui.use(['layer'], function () {
				var url = ns.url("store://shop/store/labelSelect", {request_mode: 'iframe', select_id: selectId.toString()});
				layer.open({
					title: "门店标签选择",
					type: 2,
					area: ['1000px', '600px'],
					fixed: false, //不固定
					btn: ['保存', '返回'],
					content: url,
					yes: function (index, layero) {
						var iframeWin = document.getElementById(layero.find('iframe')[0]['name']).contentWindow;//得到iframe页的窗口对象，执行iframe页的方法：
						iframeWin.selectStoreLabelListener(function (obj) {
							if (typeof callback == "string") {
								try {
									eval(callback + '(obj)');
									layer.close(index);
								} catch (e) {
									console.error('回调函数' + callback + '未定义');
								}
							} else if (typeof callback == "function") {
								callback(obj);
								layer.close(index);
							}
						});
					}
				});
			});
		},
	}
});
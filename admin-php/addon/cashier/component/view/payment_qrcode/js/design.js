var diyPaymentQrcodeHtml = '<div></div>';

Vue.component("diy-payment-qrcode-sources", {
    template: diyPaymentQrcodeHtml,
    data: function () {
        return {
            data: this.$parent.data,
            styleList: [
                {text: '样式一', value: 1},
                {text: '样式二', value: 2},
                {text: '样式三', value: 3},
                {text: '样式四', value: 4}
            ]
        };
    },
    created: function () {
        this.$parent.data.ignore = ['componentBgColor','componentAngle'];//加载忽略内容 -- 其他设置中的属性设置
        this.$parent.data.ignoreLoad = true; // 等待忽略数组赋值后加载

        // 组件所需的临时数据
        this.$parent.data.tempData = {
            styleList: this.styleList,
            methods: {
            },
        };
    },
    methods: {
        verify: function (index) {
            var res = {code: true, message: ""};
            return res;
        }
    }
});
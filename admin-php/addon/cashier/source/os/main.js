import App from './App'
import Util from './common/js/util.js'
import Http from './common/js/http.js'
import Config from './common/js/config.js'
import Mixin from './common/js/mixin.js'
import Pos from './common/js/pos.js'
import Store from './store'
import {uniStorage} from './common/js/storage.js'
Vue.prototype.$util = Util;
Vue.prototype.$pos = Pos;
Vue.prototype.$api = Http;
Vue.prototype.$config = Config;
Vue.prototype.$store = Store;

Vue.mixin(Mixin);
// 重写存储，增加前缀
uniStorage();

// 布局组件
import BasePage from "@/layout/index.vue";

Vue.component("base-page", BasePage);

// #ifndef VUE3
import Vue from 'vue'

Vue.config.productionTip = false;
App.mpType = 'app';

const app = new Vue({
	...App
});

app.$mount();

// #endif

// #ifdef VUE3
import {
	createSSRApp
} from 'vue'

export function createApp() {
	const app = createSSRApp(App);
	return {
		app
	};
}

// #endif

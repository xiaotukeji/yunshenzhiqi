import Vue from 'vue'
import Vuex from 'vuex'

import app from "./modules/app"
import billing from "./modules/billing"
import buycard from "./modules/buycard"
import recharge from "./modules/recharge"

import getters from "./getters"

Vue.use(Vuex);

const store = new Vuex.Store({
	modules: {
		app,
		billing,
		buycard,
		recharge
	},
	getters
});

export default store;
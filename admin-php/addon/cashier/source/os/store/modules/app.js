// 应用数据持久化存储

import {getAddonIsExist, getDefaultImg, getThemeConfig, getMemberSearchWayConfig} from '@/api/config.js'
import {getUserDetail, getUserGroupAuth} from '@/api/user.js'
import {getStoreInfo} from '@/api/store.js'
import util from '@/common/js/util';

const state = {
	rootSize: '100px',
	firstMenuIndex: 0,
	secondMenuIndex: -1,
	thirdMenuIndex: -1,
	currRoute: '',
	globalMemberInfo: null,
	globalStoreId: 0,
	globalStoreInfo: null,
	userInfo: null,
	addon: [],
	menu: [],
	defaultImg: {
		goods: '',
		head: '',
		store: '',
		article: ''
	},
	themeConfig: {
		title: '',
		name: '',
		color: ''
	},
	memberSearchWayConfig : {
		way : 'exact' // // exact：精确搜索，list：列表搜索
	},
	isSocketConnect:false,
	overallAudio:null,
	overallAudioSrc:'',
	overallAudioSrcConfig:{},
	overallAudioBindClientId:'',
	overallAudioIsPlay:false,
	overallAudioIsEnded:true,
};

const mutations = {
	setRootSize(state, value) {
		state.rootSize = value;
	},
	setMenuIndex(state, value) {
		state[value.level] = value.index;
	},
	setCurrRoute(state, value) {
		state.currRoute = value;
	},
	setGlobalMemberInfo(state, value) {
		state.globalMemberInfo = value;
		if (value) {
			uni.setStorageSync('globalMemberInfo', value);
		} else {
			uni.removeStorageSync('globalMemberInfo');
		}
	},
	setGlobalStoreId(state, value) {
		state.globalStoreId = value;
		if (value) {
			uni.setStorageSync('globalStoreId', value);
		} else {
			uni.removeStorageSync('globalStoreId');
		}
	},
	setGlobalStoreInfo(state, value) {
		state.globalStoreInfo = value;
		if (value) {
			uni.setStorageSync('globalStoreInfo', value);
		} else {
			uni.removeStorageSync('globalStoreInfo');
		}
	},
	setAddon(state, value) {
		state.addon = value;
		if (value) {
			uni.setStorageSync('addon', value);
		} else {
			uni.removeStorageSync('addon');
		}
	},
	setUserInfo(state, value) {
		state.userInfo = value;
		if (value) {
			uni.setStorageSync('userInfo', value);
		} else {
			uni.removeStorageSync('userInfo');
		}
	},
	setMenu(state, value) {
		state.menu = value;
		if (value) {
			uni.setStorageSync('menu', value);
		} else {
			uni.removeStorageSync('menu');
		}
	},
	setDefaultImg(state, value) {
		state.defaultImg = value;
		uni.setStorageSync('defaultImg', value);
	},
	setThemeConfig(state, value) {
		state.themeConfig = value;
		uni.setStorageSync('themeConfig', value);
	},
	setMemberSearchWayConfig(state, value) {
		state.memberSearchWayConfig = value;
		uni.setStorageSync('memberSearchWayConfig', value);
	},
	setIsSocketConnect(state,value){
		state.isSocketConnect = value
	},
	initOverallAudio(state,value){
		state.overallAudioSrc = util.img(value.order_pay_audio)
		state.overallAudioSrcConfig = value
		state.overallAudio = new Audio(state.overallAudioSrc);
		state.overallAudio.addEventListener('ended',()=>{
			state.overallAudioIsEnded = true
		});
	},
	setOverallAudioBindClientId(state,value){
		state.overallAudioBindClientId = value
	},
	overallAudioPlay(state,key){
		if(state.overallAudioIsPlay&&state.overallAudioIsEnded){
			if(state.overallAudioSrc != util.img(state.overallAudioSrcConfig[key])){
				state.overallAudioSrc = util.img(state.overallAudioSrcConfig [key])
				state.overallAudio.src = util.img(state.overallAudioSrcConfig [key])
				state.overallAudio.load();
			}
			state.overallAudio.play()
			state.overallAudioIsEnded = false
		}
	},
	setOverallAudioIsPlay(state,value){
		state.overallAudioIsPlay = value
	}
};

const actions = {
	getStoreInfoFn(context, params) {
		getStoreInfo().then((res) => {
			if (res.code >= 0) {
				if (res.data.is_frozen == 1) {
					uni.navigateTo({
						url: '/pages/store/close'
					});
					return;
				}
				context.commit('setGlobalStoreInfo', res.data);
				if (params && params.callback) params.callback(res.data);
			}
		});
	},
	getAddonIsExistFn(context) {
		getAddonIsExist().then((res) => {
			if (res.code == 0) {
				context.commit('setAddon', res.data)
			}
		})
	},
	getUserInfoFn(context) {
		getUserDetail().then((res) => {
			if (res.code == 0) {
				context.commit('setUserInfo', res.data)
			}
		})
	},
	// 查询菜单权限
	getUserGroupFn(context) {
		getUserGroupAuth().then((res) => {
			if (res.code == 0) {
				if (res.code == 0 && res.data) {
					let menu = require('@/common/menu/store.js').default ?? [];
					let addon = this.state.app.addon;
					const checkAuth = function (menu, auth) {
						let newMenu = [];
						menu.map(item => {
							if (item.children) {
								item.children = checkAuth(item.children, auth);
							}
							if (item.addon && addon.indexOf(item.addon) == -1)
								return;
							if (item.name && !res.data.is_admin && auth.length && auth.indexOf(item.name) == -1) {
								newMenu.push({});
								return;
							}

							newMenu.push(item);
						});
						return newMenu;
					};
					menu = checkAuth(JSON.parse(JSON.stringify(menu)), res.data.menu_array ? res.data.menu_array.split(',') : []);
					context.commit('setMenu', menu);
				}
			}
		});
	},
	getDefaultImgFn(context) {
		getDefaultImg().then((res) => {
			if (res.code == 0) {
				context.commit('setDefaultImg', res.data)
			}
		})
	},
	getThemeConfigFn(context) {
		getThemeConfig().then((res) => {
			if (res.code == 0) {
				context.commit('setThemeConfig', res.data)
			}else{
				context.commit('setThemeConfig', {
					title : '橙色', // 标题
					name : 'orange', // 标识
					color : '#FA6400' // 主色调
				})
			}
		});
	},
	getMemberSearchWayConfigFn(context){
		getMemberSearchWayConfig().then((res)=>{
			if (res.code == 0) {
				context.commit('setMemberSearchWayConfig', res.data)
			}else{
				context.commit('setMemberSearchWayConfig', {
					way : 'exact', // exact：精确搜索，list：列表搜索
				})
			}
		});
	},
	setIsSocketConnect(context,value){
		context.commit('setIsSocketConnect',value)
	},
	initOverallAudio(context,value){
		context.commit('initOverallAudio',value)
	},
	setOverallAudioBindClientId(context,value){
		context.commit('setOverallAudioBindClientId',value)
	},
	overallAudioPlay(context,key){
		context.commit('overallAudioPlay',key)
	},
	setOverallAudioIsPlay(context,value){
		context.commit('setOverallAudioIsPlay',value)
	}
};

export default {
	namespaced: true,
	state,
	mutations,
	actions
}

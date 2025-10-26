<script>
	import {checkPageAuth,getOrderRemind,pushBind,getPushStatus} from '@/api/config.js';
	import {mapGetters} from 'vuex';
	import config from 'common/js/config.js';
	export default {
		onLaunch: function (option) {

			// #ifdef APP-PLUS
			uni.getSystemInfo({
				success: res => {
					let fontsize = ((res.windowWidth * 16) / 1200) * 5.5 + 'px';
					this.$store.commit('app/setRootSize', fontsize);
				}
			});
			// #endif

			if (uni.getStorageSync('globalStoreInfo')) {
				this.$store.commit('app/setGlobalStoreInfo', uni.getStorageSync('globalStoreInfo'));
			}

			if (uni.getStorageSync('globalStoreId')) {
				this.$store.commit('app/setGlobalStoreId', uni.getStorageSync('globalStoreId'));
			}

			if (uni.getStorageSync('defaultImg')) {
				this.$store.commit('app/setDefaultImg', uni.getStorageSync('defaultImg'));
			}

			if (uni.getStorageSync('addon')) {
				this.$store.commit('app/setAddon', uni.getStorageSync('addon'));
			}

			if (uni.getStorageSync('themeConfig')) {
				this.$store.commit('app/setThemeConfig', uni.getStorageSync('themeConfig'));
			}

			this.$store.dispatch('app/getAddonIsExistFn');
			this.$store.dispatch('app/getDefaultImgFn');

			this.$store.dispatch('app/getThemeConfigFn');
			this.$store.dispatch('app/getMemberSearchWayConfigFn');

			if (uni.getStorageSync('cashierToken')) {
				this.$store.dispatch('app/getStoreInfoFn');
				this.$store.dispatch('app/getUserInfoFn');
				this.$store.dispatch('app/getUserGroupFn');
			}

			this.$store.commit('app/setCurrRoute', '/' + option.path);

			// #ifdef APP-PLUS
			plus.webview.open(this.$config.baseUrl + '/cashier/pages/index/loading', 'loading');
			uni.switchTab({
				url: '/pages/reserve/index',
				success: () => {
					uni.switchTab({
						url: '/pages/recharge/index',
						success: () => {
							uni.switchTab({
								url: '/pages/verify/index',
								success: () => {
									if (!uni.getStorageSync('cashierToken')) {
										uni.navigateTo({url: '/pages/login/login'});
									} else {
										uni.switchTab({url: '/pages/billing/index'});
									}
									plus.webview.close('loading');
								}
							});
						}
					});
				}
			});
			// #endif
			this.getOrderRemindFn()
			this.getPushStatusFn()
			this.initSocket()
		},
		onShow: function () {
			if (!uni.getStorageSync('cashierToken')) {

				// #ifdef H5
				if (location.href.indexOf('pages/index/loading') == -1) {
					this.$util.redirectTo('/pages/login/login', {}, 'redirectTo');
				}
				// #endif

				// #ifndef H5
				this.$util.redirectTo('/pages/login/login', {}, 'redirectTo');
				// #endif

			}
			// this.$nextTick(()=>{
			// 	window.addEventListener('beforeunload', this.handleBeforeUnload);
			// })
		},
		
		methods: {
			// handleBeforeUnload(e){
			// 	// 提示用户确认
			// 	    e.preventDefault();
			// },
			getOrderRemindFn(){
				getOrderRemind().then(res=>{
					if(res.code>=0){
						this.$store.dispatch('app/initOverallAudio',res.data);
					}
				})
			},
			getPushStatusFn(){
				getPushStatus().then(res=>{
					if(res.code>=0){
						this.$store.dispatch('app/setIsSocketConnect',true)
						
					}
				})
			},
			initSocket(){
				// 心跳机制
				
				// 可自定义成你的模式,这里只做基本处理
				let linkNumber = 1
				let socketInterval=setInterval(()=>{
					 let token = uni.getStorageSync('cashierToken');
					 try{
					 	if(token&&this.isSocketConnect){ 
							if(linkNumber>3){
								
								this.$util.showToast({
									title: 'webSocket连接异常，请联系管理员'
								});
								
								if(this.overallAudioIsPlay) this.$store.dispatch('app/setOverallAudioIsPlay',false);
								this.$store.dispatch('app/setIsSocketConnect',false)
								linkNumber = 1
								return false
							} 
							 uni.sendSocketMessage({
							   data: 'ping',
							   success: (res) => {	
								   linkNumber = 1
							   },
							   fail: (e) => {
								   linkNumber++  
								  uni.connectSocket({
									url:config.webSocket,
									complete: (res) => {
									},
									fail: (connectE) => {
										// console.log(e)
									}
								  })		
							   }
							 });
						 }		
					 }catch(e){
					 	//TODO handle the exception
					 }
					 		
				},1000)
				uni.onSocketMessage((res)=>{
					var data = JSON.parse(res.data);
					let token = uni.getStorageSync('cashierToken');
					switch(data.type){
						case 'init':
							if(token){
								this.$store.dispatch('app/setOverallAudioBindClientId',data.data.client_id)
								pushBind({client_id:data.data.client_id})
							}
							break;
						case 'ping':
							uni.sendSocketMessage({
							  data: 'ping',
							});
							break;
						default:
							this.$store.dispatch('app/overallAudioPlay',data.data.audio);
					}
				});
			},
			initRoute(route) {
				const search = function (menu, route, arr = []) {
					menu.find((item, index) => {
						if (item.path == route) {
							arr.push(index);
							return true;
						} else if (item.children) {
							arr = search(item.children, route, arr);
							if (arr.length) {
								arr.push(index);
								return true;
							} else {
								return false;
							}
						}
						return false;
					});
					return arr;
				};

				let menuIndex = search(this.menu, route).reverse();
				this.$store.commit('app/setMenuIndex', {level: 'firstMenuIndex', index: menuIndex[0]});
				this.$store.commit('app/setMenuIndex', {level: 'secondMenuIndex', index: menuIndex[1] ?? -1});
				this.$store.commit('app/setMenuIndex', {level: 'thirdMenuIndex', index: menuIndex[2] ?? -1});
			},
			/**
			 * 检测页面是否有权限
			 */
			checkPageAuthFn() {
				checkPageAuth(this.currRoute).then(res => {
					if (res.code && res.code == -10012) {
						this.$util.redirectTo('/pages/index/no_permission', {}, 'redirectTo');
					}
				});
			}
		},
		computed: {
			menu() {
				let menu = require('@/common/menu/store.js');
				return menu.default ?? [];
			},
			isSocketConnect(){
				return this.$store.state.app.isSocketConnect
			},
			overallAudioIsPlay(){
				return this.$store.state.app.overallAudioIsPlay
			},
			...mapGetters(['currRoute'])
		},
		watch: {
			currRoute: function (nVal, oVal) {
				if (nVal) {
					this.initRoute(nVal);
					this.checkPageAuthFn();
				}
			},
			menu: function (nVal) {
				if (nVal.length) {
					this.initRoute(this.currRoute);
				}
			}
		}
	};
</script>

<style lang="scss">
	/*每个页面公共css */
	@import url('/common/css/iconfont.css');
	@import '/common/css/common.scss';
	@import '/common/css/form.scss';

	uni-toast .uni-toast__content {
		font-size: 0.16rem !important;
	}
</style>

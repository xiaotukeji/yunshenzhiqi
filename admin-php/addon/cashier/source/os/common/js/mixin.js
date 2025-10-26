import {mapGetters} from 'vuex';

export default {
	data() {
		return {
			// 左侧菜单，支持触发的按键集合
			menuKeyCode: ['F6', 'F7', 'F8', 'F9', 'F10', 'F11'],
			themeColor: ''
		};
	},
	onShow() {

		this.setNavigationBarTitleText();

		// 监听键盘回调
		window.POS_HOTKEY_CALLBACK = (control, code) => {
			this.$store.commit('billing/setIsShowCashBox',true);
			// 触发左侧菜单按键回调
			this.menuTriggerKeyCodeCallBack(code);
		};
	},
	computed: {
		cashierToken() {
			return uni.getStorageSync('cashierToken');
		},
		...mapGetters([
			'rootSize', 'defaultImg', 'addon', 'menu', 'userInfo', 'themeConfig',
			'globalStoreId', 'globalStoreInfo', 'globalMemberId', 'globalMemberInfo'
		])
	},
	watch: {
		'globalStoreInfo.store_id': {
			handler(nval, oval) {
				if (oval && typeof this.switchStoreAfter == 'function') {
					this.switchStoreAfter();
					this.setNavigationBarTitleText();
				}
			},
			deep: true
		},
		themeConfig:{
			handler(nval,oval){
				if(nval && oval && oval.color && nval.color != oval.color) {
					this.loadThemeColor();
				}
			},
			deep:true
		}
	},
	methods: {
		/**
		 * 设置页面标题
		 */
		setNavigationBarTitleText() {
			let pages = getCurrentPages();
			let currentPage = pages[pages.length - 1];
			if (currentPage && currentPage.$holder && currentPage.$holder.navigationBarTitleText) {
				let title = currentPage.$holder.navigationBarTitleText;
				if (this.globalStoreInfo) title += '-' + this.globalStoreInfo.store_name;
				if (title != currentPage.$holder.navigationBarTitleText) uni.setNavigationBarTitle({
					title: title
				})
			} else {
				setTimeout(() => {
					this.setNavigationBarTitleText();
				}, 800)
			}
		},
		// 触发左侧菜单按键回调
		menuTriggerKeyCodeCallBack(code) {
			if (this.menuKeyCode.indexOf(code) != -1) {
				let data = null;
				for (let i = 0; i < this.menu.length; i++) {
					let item = this.menu[i];
					if (item.keyCode == code) {
						data = item;
						break;
					}
				}

				if (data) {

					// #ifdef H5
					if (data.path == this.$route.path) return;
					// #endif
					// #ifdef APP-PLUS
					if (data.path == '/' + this.$mp.page.route) return;
					// #endif

					this.$util.redirectTo(data.path, data.query ?? {});
				}
			}
		},
		themeColorSet() {
			let theme = this.themeConfig;
			this.themeColor = `--primary-color:${theme.color};`;
			for (let i = 9; i >= 1; i--) {
				let color = this.$util.colourBlend(theme.color, '#ffffff', (i / 10));
				this.themeColor += `--primary-color-light-${i}:${color};`;
			}
		},
		loadThemeColor(){
			let time = setInterval(() => {
				let theme = this.themeConfig;
				if (theme && theme.color) {
					this.themeColorSet();
					clearInterval(time);
				}
			}, 50);
		}
	},
	filters: {
		/**
		 * 金额格式化
		 * @param {Object} money
		 */
		moneyFormat(money) {
			if (isNaN(money)) return money;
			return parseFloat(money).toFixed(2);
		},
		/**
		 * 时间格式化
		 * @param {Object} time 时间戳
		 * @param {Object} format 输出格式
		 */
		timeFormat(time, format = 'Y-m-d H:i:s') {
			var date = new Date();
			date.setTime(time * 1000);

			var y = date.getFullYear();
			var m = date.getMonth() + 1;
			var d = date.getDate();
			var h = date.getHours();
			var i = date.getMinutes();
			var s = date.getSeconds();

			format = format.replace('Y', y);
			format = format.replace('m', (m < 10 ? '0' + m : m));
			format = format.replace('d', (d < 10 ? '0' + d : d));
			format = format.replace('H', (h < 10 ? '0' + h : h));
			format = format.replace('i', (i < 10 ? '0' + i : i));
			format = format.replace('s', (s < 10 ? '0' + s : s));

			return format;
		},
		mobileFormat(mobile) {
			return mobile.substring(0, 4 - 1) + '****' + mobile.substring(6 + 1);
		}
	}
}
export default {
	computed: {
		//插件是否存在
		addonIsExit() {
			return this.$store.state.addonIsExit
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
		}
	}
}

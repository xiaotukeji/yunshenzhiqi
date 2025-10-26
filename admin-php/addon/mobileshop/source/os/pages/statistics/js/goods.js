export default {
	data() {
		return {
			picker: [{
					date_type: 0,
					date_text: '今日实时',
				},
				{
					date_type: -1,
					date_text: '昨日',
				},
				{
					date_type: 1,
					date_text: '近7天',
				},
				{
					date_type: 2,
					date_text: '近30天'
				}
			],
			pickerCurr: 0,
			statTotal: {},
			numRanking: [],
			moneyRanking: [],
		}
	},
	onShow() {
		if (!this.$util.checkToken('/pages/statistics/transaction')) return;
		this.pickerChange({
			detail: {
				value: this.pickerCurr
			}
		});
	},
	methods: {
		getStatTotal(data = {}) {
			this.$api.sendRequest({
				url: '/shopapi/statistics/getstattotal',
				data: data,
				success: res => {
					if (res.code >= 0) {
						this.statTotal = res.data;
					}
					if (this.$refs.loadingCover) this.$refs.loadingCover.hide();
				}
			})
		},
		pickerChange(e) {
			this.pickerCurr = e.detail.value;
			let data = {};
			switch (this.picker[this.pickerCurr].date_type) {
				case -1:
					data.start_time = this.getTime(-1).startTime;
					data.end_time = this.getTime(-1).endTime;
					break;
				case 1:
					data.start_time = this.getTime(-7).startTime;
					data.end_time = this.getTime(0).endTime;
					break;
				case 2:
					data.start_time = this.getTime(-30).startTime;
					data.end_time = this.getTime(0).endTime;
					break;
			}
			this.getStatTotal(data);
			this.getCountGoodsSale(data);
			this.getCountGoodsSaleMoney(data);
		},
		/**
		 *获取几天前开始时间结束时间
		 * @param {Object} day
		 */
		getTime(day) {
			let date = new Date(new Date(new Date().toLocaleDateString()));
			date.setDate(date.getDate() + day);
			let startTime = parseInt(new Date(date).getTime() / 1000);
			let endTime = startTime + (24 * 60 * 60 - 1);
			return {
				startTime,
				endTime
			}
		},
		/**
		 * 获取销量排行
		 */
		getCountGoodsSale(data = {}) {
			this.$api.sendRequest({
				url: '/shopapi/statistics/countgoodssale',
				data: data,
				success: res => {
					if (res.code >= 0) {
						this.numRanking = res.data.list;
					}
				}
			})
		},
		/**
		 * 获取销量排行 按金额
		 */
		getCountGoodsSaleMoney(data = {}) {
			this.$api.sendRequest({
				url: '/shopapi/statistics/countgoodssalemoney',
				data: data,
				success: res => {
					if (res.code >= 0) {
						this.moneyRanking = res.data.list;
					}
				}
			})
		},
	}
}

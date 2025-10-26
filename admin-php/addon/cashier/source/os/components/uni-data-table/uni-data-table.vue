<template>
	<view class="table-container">
		<view class="thead">
			<view class="th" v-for="(th, thIndex) in cols" :key="thIndex" :style="{ flex: th.width, maxWidth: th.width + '%', textAlign: th.align ? th.align : 'center' }">
				<view class="content">
					<view v-if="th.checkbox" class="iconfont" @click="all" :class="{
						iconfuxuankuang2: selected.length == 0,
						iconcheckbox_weiquanxuan: selected.length != list.length,
						iconfuxuankuang1: selected.length > 0 && selected.length == list.length
					}"></view>
					<text v-else>{{ th.title }}</text>
				</view>
			</view>
		</view>
		<view class="tbody">
			<view class="tr" v-for="(d, index) in list" :key="index" v-if="list.length">
				<view class="td" v-for="(th, thIndex) in cols" :key="thIndex" :style="{ flex: th.width, maxWidth: th.width + '%', textAlign: th.align ? th.align : 'center' }">
					<view class="content" :class="{ action: th.action }">
						<view v-if="th.checkbox">
							<text v-if="typeof th.disabled == 'function' && th.disabled(d)" class="iconfont iconfuxuankuang2 disabled"></text>
							<text v-else @click="single(d, index)" class="iconfont" :class="{
								iconfuxuankuang2: selectedIndex.indexOf(index) == -1,
								iconfuxuankuang1: selectedIndex.indexOf(index) != -1
							}"></text>
						</view>
						<slot v-else-if="th.action" name="action" :value="d" :index="index"></slot>
						<view v-else-if="th.templet" v-html="th.templet(d)"></view>
						<view v-else-if="th.return">{{ th.return(d) }}</view>
						<view v-else>{{ d[th.field] }}</view>
					</view>
				</view>
			</view>
			<view class="tr empty" v-if="!list.length">
				<view class="td">
					<view class="iconfont iconwushuju"></view>
					<view>暂无数据</view>
				</view>
			</view>
		</view>
		<view class="tpage" v-if="list.length && classType == false">
			<view class="batch-action">
				<slot name="batchaction" :value="selected"></slot>
			</view>
			<uni-pagination :total="total" :showIcon="true" @change="pageChange" :pageSize="pagesize" :value="page" />
		</view>
	</view>
</template>

<script>
export default {
	name: 'uniDataTable',
	props: {
		cols: {
			type: Array
		},
		url: {
			type: String,
			default: ''
		},
		pagesize: {
			type: Number,
			default: 10
		},
		option: {
			type: Object,
			default: function () {
				return {};
			}
		},
		classType: {
			type: Boolean,
			default: false
		},
		data: {
			type: Object | Array,
			default: function () {
				return {};
			}
		}
	},
	created() {
		this.url && this.load();
	},
	data() {
		return {
			list: [],
			selected: [],
			selectedIndex: [],
			page: 1,
			total: 0
		};
	},
	watch: {
		data: {
			handler(nVal, oVal) {
				if (Object.keys(nVal).length) this.list = Object.values(nVal)
			},
			deep: true,
			immediate: true
		}
	},
	methods: {
		/**
		 * 全选
		 */
		all() {
			if (this.list.length) {
				if (this.selected.length == this.list.length) {
					this.selected = [];
					this.selectedIndex = [];
				} else {
					let selectedIndex = [],
						selected = [],
						firstTh = this.cols[0];
					this.list.forEach((item, index) => {
						if (typeof firstTh.disabled == 'function' && firstTh.disabled(item)) return;
						selectedIndex.push(index);
						selected.push(item);
					});
					this.selectedIndex = selectedIndex;
					this.selected = selected;
				}
				this.$emit('checkBox', this.selected);
			}
		},
		/**
		 * 单选
		 * @param {Object} item
		 * @param {Object} index
		 */
		single(item, index) {
			let _index = this.selectedIndex.indexOf(index);
			if (_index == -1) {
				this.selectedIndex.push(index);
				this.selected.push(item);
			} else {
				this.selectedIndex.splice(_index, 1);
				this.selected.splice(_index, 1);
			}
			this.$emit('checkBox', this.selected, this.selectedIndex);
		},
		/**
		 * 设置默认选中数据
		 */
		defaultSelectData(selected, selectedIndex) {
			this.selected = selected;
			this.selectedIndex = selectedIndex;
		},
		pageChange(e) {
			this.page = e.current;
			this.load();
			this.$emit('pageChange', this.page);
		},
		load(option = {}) {
			let data = {
				page: option.page || this.page,
				page_size: this.pagesize
			};
			if (this.option) Object.assign(data, this.option);
			if (option) Object.assign(data, option);

			this.$api.sendRequest({
				url: this.url,
				data: data,
				success: res => {
					if (res.code >= 0) {
						this.list = res.data.list;
						this.total = res.data.count;

						this.selected = [];
						this.selectedIndex = [];
						this.$emit('tableData', this.list);
						if (option.page) {
							this.page = option.page;
							delete option.page;
						}
					} else {
						this.$util.showToast({ title: res.message });
					}
				},
				fail: () => {
					this.$util.showToast({ title: '请求失败' });
				}
			});
		},
		clearCheck() {//库存页面清除选中
			this.selected = [];
			this.selectedIndex = [];
		}
	}
};
</script>

<style lang="scss">
.table-container {
	width: 100%;

	.iconcheckbox_weiquanxuan,
	.iconfuxuankuang1,
	.iconfuxuankuang2 {
		color: $primary-color;
		cursor: pointer;
		font-size: 0.16rem;
		transition: all 0.3s;
	}

	.iconfuxuankuang2 {
		color: #e6e6e6;

		&:hover {
			color: $primary-color;
		}
	}

	.disabled {
		background: #eee;
		cursor: not-allowed;

		&:hover {
			color: #e6e6e6;
		}
	}
}

.thead {
	display: flex;
	width: 100%;
	height: 0.5rem;
	background: #f7f8fa;
	align-items: center;

	.th {
		padding: 0 0.1rem;
		box-sizing: border-box;

		.content {
			white-space: nowrap;
			width: 100%;
			overflow: hidden;
			text-overflow: ellipsis;
		}
	}
}

.tr {
	display: flex;
	border-bottom: 0.01rem solid #e6e6e6;
	min-height: 0.5rem;
	align-items: center;
	transition: background-color 0.3s;
	padding: 0.1rem 0;
	box-sizing: border-box;

	&:hover {
		background: #f5f5f5;
	}

	.td {
		padding: 0 0.1rem;
		box-sizing: border-box;

		.content {
			width: 100%;
			white-space: normal;
		}
	}

	&.empty {
		justify-content: center;

		.td {
			text-align: center;
			color: #909399;

			.iconfont {
				font-size: 0.25rem;
				margin: 0.05rem;
			}
		}
	}
}

.tpage {
	display: flex;
	align-items: center;
	padding: 0.1rem 0;
	margin-bottom: 0.1rem;

	.uni-pagination {
		justify-content: flex-end;
		flex: 1;
	}
}
</style>

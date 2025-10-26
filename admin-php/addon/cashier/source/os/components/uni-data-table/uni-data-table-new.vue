<template>
	<view class="table-container">
		<view class="thead">
			<view class="th" v-for="(th, thIndex) in cols" :key="thIndex" :style="{ flex: th.width, maxWidth: th.width + '%', textAlign: th.align ? th.align : 'center' }">
				<view class="content">
					<block v-if="th.checkbox">
						<view class="all-select" @mouseenter="allSelectShow()" @mouseleave="allSelectHide()">
							<view class="all-select-label">
								<text>全选<text v-if="allSelect.num > 0">({{ allSelect.num }})</text></text>
								<text class="iconfont iconxiala"></text>
							</view>
							<view class="all-select-option" :style="allSelect.show? 'display:block;' : ''">
								<view 
								v-for="(oVal) in allSelect.optionList" 
								:class="allSelect.hoverOption==oVal.id? 'on' : ''"
								@mouseenter="allSelect.hoverOption = oVal.id" 
								@click="allSelectClick(oVal)"
								>{{ oVal.name }}
									<text v-if="(oVal.id == 'curr' && allSelect.pageSelected[page]) || (oVal.id == 'all' && allSelect.selected)" style="color:red;margin-left: 4px;">√</text> 
								</view>
							</view>
						</view>
					</block>
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
								iconfuxuankuang2: Boolean(selectedData[d[pk]]) == false,
								iconfuxuankuang1: Boolean(selectedData[d[pk]]) == true,
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
	name: 'uniDataTableNew',
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
		},
		pk: {//主键id
			type: String,
			default: ''
		},
	},
	created() {
		this.url && this.load({page:1});
	},
	data() {
		return {
			list: [],
			selected: [],
			selectedIndex: [],
			selectedData:{},
			unselectedData:{},
			page: 1,
			total: 0,
			pageCount:0,
			allSelect:{
				optionList:[
					{id:'curr',name:'当前页'},
					{id:'all',name:'所有页'},
				],
				show:false,
				num:0,
				hoverOption:'',
				selected:false,//所有页选中
				pageSelected:{},
			},
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
		allSelectShow(){
			this.allSelect.show = true;
		},
		allSelectHide(){
			this.allSelect.show = false;
			this.allSelect.hoverOption = '';
		},
		allSelectInitData(){
			this.allSelect.pageSelected = {};
			this.allSelect.selected = false;
			if(this.pageCount > 0){
				for(let i = 1; i < this.pageCount; i++){
					this.allSelect.pageSelected[i] = false;
				}
			}
			this.selectedData = {};
			this.unselectedData = {};
			this.allSelect.num = 0;
		},
		allSelectClick(item){
			if(item.id == 'curr'){
				this.allSelect.pageSelected[this.page] = !this.allSelect.pageSelected[this.page];
			}else{
				this.allSelect.selected = !this.allSelect.selected;
				for(let i in this.allSelect.pageSelected){
					this.allSelect.pageSelected[i] = this.allSelect.selected;
				}
				if(!this.allSelect.selected){
					this.selectedData = {};
					this.unselectedData = {};
				}
			}
			this.handleSelectData();
			this.allSelectNum();
			this.allSelectHide();
		},
		allSelectNum(){
			if(this.allSelect.selected){
				this.allSelect.num = this.total - Object.values(this.unselectedData).length;
			}else{
				this.allSelect.num = Object.values(this.selectedData).length;
			}
		},
		/**
		 * 全选
		 */
		handleSelectData() {
			if (this.list.length) {
				let firstTh = this.cols[0];
				if(this.allSelect.pageSelected[this.page]){
					this.list.forEach((item)=>{
						if (typeof firstTh.disabled == 'function' && firstTh.disabled(item)) return;
						if(!this.selectedData[item[this.pk]]){
							this.selectedData[item[this.pk]] = item;
						}
						if(this.unselectedData[item[this.pk]]){
							delete this.unselectedData[item[this.pk]];
						}
					})
				}else{
					this.list.forEach((item)=>{
						if (typeof firstTh.disabled == 'function' && firstTh.disabled(item)) return;
						if(!this.unselectedData[item[this.pk]]){
							this.unselectedData[item[this.pk]] = item;
						}
						if(this.selectedData[item[this.pk]]){
							delete this.selectedData[item[this.pk]];
						}
					})
				}
			}
		},
		/**
		 * 单选
		 * @param {Object} item
		 * @param {Object} index
		 */
		single(item, index) {
			if(this.selectedData[item[this.pk]]){
				delete this.selectedData[item[this.pk]];
			}else{
				this.selectedData[item[this.pk]] = item;
			}
			if(this.unselectedData[item[this.pk]]){
				delete this.unselectedData[item[this.pk]];
			}else{
				this.unselectedData[item[this.pk]] = item;
			}
			this.allSelectNum();
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
						this.pageCount = res.data.page_count;

						this.selected = [];
						this.selectedIndex = [];
						this.$emit('tableData', this.list);
						if (option.page) {
							this.page = option.page;
							if(option.page == 1){
								this.allSelectInitData();
							}
							delete option.page;
						}
						this.handleSelectData();
					} else {
						this.$util.showToast({ title: res.message });
					}
				},
				fail: () => {
					this.$util.showToast({ title: '请求失败' });
				}
			});
		},
		//库存页面清除选中
		clearCheck() {
			this.allSelectInitData();
		},
		//返回选择数据
		getSelectData(){
			return {
				selectedData: this.selectedData,
				unselectedData: this.unselectedData,
				allSelected: this.allSelect.selected,
				selectedNum: this.allSelect.num,
			}
		},
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
			/* overflow: hidden; */
			text-overflow: ellipsis;
			.all-select{
				position: relative;
				.all-select-label{
					cursor: pointer;
					padding:4px;
				}
				.all-select-option{
					position: absolute;
					display: none;
					left:50%;
					transform: translateX(-50%);
					background: #fff;
					padding: 4px 0;
					border-radius: 2px;
					box-shadow: 0px 0px 2px #ccc;
					view{
						color: #000;
						cursor:pointer;
						margin: 4px 0;
						padding:4px 20px;
						&.on{
							background: #eee;
						}
					}
				}
			}
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

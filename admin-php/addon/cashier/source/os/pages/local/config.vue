<template>
	<base-page>
		<view class="collect-money-config">
			<view class="common-wrap common-form fixd common-scrollbar">
				<view class="common-title">本机设置</view>
				<view class="common-form-item">
					<label class="form-label">打印机选择</label>
					<view class="form-inline">
						<radio-group @change="printerSelectTypeChange" class="form-radio-group">
							<label class="radio form-radio-item">
								<radio value="all" :checked="printerSelectType == 'all'" />
								全部
							</label>
							<label class="radio form-radio-item">
								<radio value="part" :checked="printerSelectType == 'part'" />
								部分
							</label>
						</radio-group>
					</view>
					<text class="form-word-aux-line" v-if="printerSelectType == 'all'">{{printerTips}}</text>
				</view>
				<view class="common-form-item" v-if="printerSelectType == 'part'">
					<label class="form-label"></label>
					<view class="form-inline">
						<checkbox-group class="form-checkbox-group" @change="printerSelectIdsChange">
							<label class="form-checkbox-item" v-for="(item, index) in printerList">
								<checkbox :value="item.printer_id" :checked="printerSelectIds.indexOf(item.printer_id) > -1"/>
								{{item.printer_name}}
							</label>
						</checkbox-group>
					</view>
					<text class="form-word-aux-line">{{printerTips}}</text>
				</view>
				<view class="common-btn-wrap">
					<button type="default" class="screen-btn" @click="saveFn">保存</button>
				</view>
			</view>
		</view>
	</base-page>
</template>

<script>
import {
	getPrinterList,
} from '@/api/printer.js'
export default {
	data() {
		return {
			isRepeat: false,
			printerList:[],
			printerSelectType:'all',
			printerSelectIds:[],
			printerTips:'如果一个门店有多个收银机，请为每个收银机设备选择要调用的打印机硬件',
		};
	},
	onLoad() {
		this.initData();
		this.getPrinterList();
	},
	onShow() { },
	methods: {
		initData(){
			var local_config = this.$util.getLocalConfig();
			this.printerSelectType = local_config.printerSelectType;
			this.printerSelectIds = local_config.printerSelectIds;
		},
		getPrinterList() {
			getPrinterList({
				page: 1,
				page_size: 0
			}).then(res => {
				if (res.code >= 0) {
					this.printerList = res.data.list;
					this.printerList.forEach((item, index)=>{
						item.printer_id = item.printer_id.toString();
					})
				}
			});
		},
		printerSelectTypeChange(e) {
			this.printerSelectType = e.detail.value;
		},
		printerSelectIdsChange(e) {
			this.printerSelectIds = e.detail.value;
		},
		saveFn() {
			this.$util.setLocalConfig({
				printerSelectType:this.printerSelectType,
				printerSelectIds:this.printerSelectIds,
			})
			this.$util.showToast({
				title: '设置成功'
			});
		}
	}
};
</script>

<style lang="scss" scoped>
.collect-money-config {
	position: relative;

	.common-btn-wrap {
		position: absolute;
		left: 0;
		bottom: 0;
		right: 0;
		padding: 0.24rem 0.2rem;

		.screen-btn {
			margin: 0;
		}
	}

	.common-wrap.fixd {
		padding: 30rpx;
		height: calc(100vh - 0.4rem);
		overflow-y: auto;
		// padding-bottom: 1rem !important;
		box-sizing: border-box;
	}

	.form-input {
		font-size: 0.16rem;
	}

	.form-input-inline.btn {
		height: 0.37rem;
		line-height: 0.35rem;
		box-sizing: border-box;
		border: 0.01rem solid #e6e6e6;
		text-align: center;
		cursor: pointer;
	}

	.common-title {
		font-size: 0.18rem;
		margin-bottom: 0.2rem;
	}

	.common-form .common-form-item .form-label {
		width: 1.5rem;
	}

	.common-form .common-form-item .form-word-aux-line {
		margin-left: 1.5rem;
	}
}
</style>

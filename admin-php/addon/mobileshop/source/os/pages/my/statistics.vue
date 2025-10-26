<template>
	<view class="statis">
		<view class="order-setuo">
			<view class="order-title">余额支付</view>
			<view class="order-list">
				<view class="list-left">是否启用</view>
				<view class="list-right">
					<ns-switch class="switch" :checked="statisticsData.balance_config.balance_show == 1" @change="balanceShow()"></ns-switch>
				</view>
			</view>
		</view>
		<view class="order-setuo">
			<view class="order-title">订单设置</view>
			<view class="order-list">
				<view class="list-left">未付款自动关闭时间</view>
				<view class="list-right"><input type="text" v-model="statisticsData.order_event_time_config.auto_close" placeholder="0" />分钟</view>
			</view>
			<view class="order-list">
				<view class="list-left">发货后自动收货时间</view>
				<view class="list-right"><input type="text" v-model="statisticsData.order_event_time_config.auto_take_delivery" placeholder="0" />天</view>
			</view>
			<view class="order-list">
				<view class="list-left">收货后自动完成时间</view>
				<view class="list-right"><input type="text" v-model="statisticsData.order_event_time_config.auto_complete" placeholder="0" />天</view>
			</view>
			<view class="order-list">
				<view class="list-left">完成后可维权时间</view>
				<view class="list-right"><input type="text" v-model="statisticsData.order_event_time_config.after_sales_time" placeholder="0" />天</view>
			</view>
		</view>
		
		<view class="order-setuo">
			<view class="order-title">评价设置</view>
			<view class="order-list">
				<view class="list-left">订单评价</view>
				<view class="list-right">
					<ns-switch class="switch" :checked="statisticsData.order_evaluate_config.evaluate_status == 1" @change="isFree()"></ns-switch>
				</view>
			</view>
			<view class="order-list">
				<view class="list-left">显示评价</view>
				<view class="list-right">
					<ns-switch class="switch" :checked="statisticsData.order_evaluate_config.evaluate_show == 1" @change="evaluateShow()"></ns-switch>
				</view>
			</view>
			<view class="order-list">
				<view class="list-left">评价审核</view>
				<view class="list-right">
					<ns-switch class="switch" :checked="statisticsData.order_evaluate_config.evaluate_audit == 1" @change="evaluateAudit()"></ns-switch>
				</view>
			</view>
		</view>
		
		<view class="order-setuo">
			<view class="order-title">发票设置</view>
			<view class="order-list">
				<view class="list-left">发票开关</view>
				<view class="list-right">
					<!-- <switch :checked="statisticsData.order_event_time_config.invoice_status == 1" style="transform: scale(0.8,0.8);" /> -->
					<ns-switch class="switch" :checked="statisticsData.order_event_time_config.invoice_status == 1" @change="invoiceStatus()"></ns-switch>
				</view>
			</view>
			<view class="order-list">
				<view class="list-left">发票税率</view>
				<view class="list-right"><input type="text" v-model="statisticsData.order_event_time_config.invoice_rate" placeholder="0" /></view>
			</view>
			<view class="order-list">
				<view class="list-left">发票内容</view>
				<view class="list-right" @click="onContent()">
					<!-- <input type="text" v-model="invoicecontent" placeholder="请填写发票内容" /> -->
					<view class="order-content">请填写发票内容</view>
					<text class="iconfont iconright"></text>
				</view>
			</view>
			<view class="order-list">
				<view class="list-left">邮寄费用</view>
				<view class="list-right"><input type="text" v-model="statisticsData.order_event_time_config.invoice_money" placeholder="0" />元</view>
			</view>
			<view class="order-list">
				<view class="list-left">支持发票类型</view>
				<view class="list-right">
					<checkbox-group @change="onOrdinary">
						<label>
							<checkbox style="transform:scale(0.7)" class="uni-checkbox-input" value="1" :checked="isOrdinary[0] == 1" />普通发票
						</label>
						<label style="margin-left:30rpx;">
							<checkbox style="transform:scale(0.7)" class="uni-checkbox-input" value="2" :checked="isOrdinary[1] == 2" />电子发票
						</label>
					</checkbox-group>
				</view>
			</view>
		</view>
		
		<!-- <button class="btn" type="default">保存</button> -->
		<view class="footer-wrap"><button type="primary" @click="save()">保存</button></view>
		<!-- <loading-cover ref="loadingCover"></loading-cover> -->
	</view>
</template>

<script>
import {getOrderConfig,setOrderConfig} from '@/api/config'
export default {
	data() {
		return {
			statisticsData: {
				balance_config:{
					balance_show:""
				},
				order_evaluate_config: {
					evaluate_audit: "",
					evaluate_show: "",
					evaluate_status: ""
				},
				order_event_time_config: {
					after_sales_time: "",
					auto_close: "",
					auto_complete: "",
					auto_take_delivery: "",
					invoice_content: [],
					invoice_money: "",
					invoice_rate: "",
					invoice_status: "",
					invoice_type: []
				}
			},
			unpaid: null,
			receiving: null,
			complete: null,
			protection: null,
			isOrderEvaluation: false,
			isDisplayEvaluation: false,
			isEvaluationExamine: false,
			invoiceFlag: false,
			taxrate: null,
			invoicecontent: '',
			mailingfee: null,
			isOrdinary: [],
			isElectronics: false,
		};
	},
	onShow() {
	},
	mounted(){
		this.nvoiceData()
	},
	methods: {
		onOrdinary(e){
			this.isOrdinary = e.detail.value
		},
		// onElectronics(){this.isElectronics = this.isElectronics == true ? false : true},
		balanceShow() {this.statisticsData.balance_config.balance_show = this.statisticsData.balance_config.balance_show == 1 ? 0 : 1},
		isFree() {this.statisticsData.order_evaluate_config.evaluate_status = this.statisticsData.order_evaluate_config.evaluate_status == 1 ? 0 : 1},
		evaluateShow(){this.statisticsData.order_evaluate_config.evaluate_show = this.statisticsData.order_evaluate_config.evaluate_show == 1 ? 0 : 1},
		evaluateAudit(){this.statisticsData.order_evaluate_config.evaluate_audit = this.statisticsData.order_evaluate_config.evaluate_audit == 1 ? 0 : 1},
		invoiceStatus(){this.statisticsData.order_event_time_config.invoice_status = this.statisticsData.order_event_time_config.invoice_status == 1 ? 0 : 1},
		nvoiceData(){
			getOrderConfig().then(res=>{
				if (res.code == 0 && res.data) {
					this.statisticsData = res.data
					let checkoutData = res.data.order_event_time_config.invoice_type
					this.isCheck = res.data.order_event_time_config.invoice_type
					for(let i=0;i<checkoutData.length;i++){
						// if(checkoutData[i] == 1){
							this.isOrdinary = checkoutData
						// }else if(checkoutData[i] == 2){
						// 	this.isElectronics = true
						// }
					}
				}
			})
		},
		save(){
			if(uni.getStorageSync('invoicecontent')){ 
				this.statisticsData.order_event_time_config.invoice_content = uni.getStorageSync('invoicecontent')
				for(let i=0;i<this.statisticsData.order_event_time_config.invoice_content.length;i++){
					if(this.statisticsData.order_event_time_config.invoice_content[i] == ''){
						this.statisticsData.order_event_time_config.invoice_content.splice(i,1)
					}
				}
			}
			setOrderConfig({
				balance_show: this.statisticsData.balance_config.balance_show,
				order_auto_close_time: this.statisticsData.order_event_time_config.auto_close,
				order_auto_take_delivery_time: this.statisticsData.order_event_time_config.auto_take_delivery,
				order_auto_complete_time: this.statisticsData.order_event_time_config.auto_complete,
				after_sales_time: this.statisticsData.order_event_time_config.after_sales_time,
				evaluate_status: this.statisticsData.order_evaluate_config.evaluate_status,
				evaluate_show: this.statisticsData.order_evaluate_config.evaluate_show,
				evaluate_audit: this.statisticsData.order_evaluate_config.evaluate_audit,
				invoice_status: this.statisticsData.order_event_time_config.invoice_status,
				invoice_rate: this.statisticsData.order_event_time_config.invoice_rate,
				invoice_content: this.statisticsData.order_event_time_config.invoice_content,
				invoice_money: this.statisticsData.order_event_time_config.invoice_money,
				invoice_type: this.isOrdinary,
			}).then(res=>{
				if (res.code ==0) {
					this.$util.showToast({
						title: '保存成功'
					});
					this.$util.redirectTo('/pages/index/all_menu')
				}
			})
		},
		onContent(){
			this.$util.redirectTo('/pages/my/nvoice/nvoice',{list: JSON.stringify(this.statisticsData.order_event_time_config.invoice_content)})
		}
	}
};
</script>

<style lang="scss">
	.statis{
		.order-setuo {
			margin:20rpx 30rpx;
			background: #fff;
			padding:15rpx 30rpx;
			border-radius: 10rpx;
			
			.order-list {
				display: flex;
				flex-direction: row;
				justify-content: space-between;
				align-items: center;
				border-bottom:1px solid #eee;
				padding:20rpx 0;
				
				.list-right {
					display: flex;
					flex-direction: row;
					align-items: center;
					font-size: 28rpx;
					font-family: PingFang SC;
					font-weight: 500;
					color: #303133;
					
					input {
						font-size: 28rpx;
						font-family: PingFang SC;
						font-weight: 500;
						color: #909399;
						text-align: right;
						margin-right:20rpx;
						max-width: 280rpx;
					}
					.order-content {
						font-size: 28rpx;
						font-family: PingFang SC;
						font-weight: 500;
						color: #909399;
						text-align: right;
						margin-right:20rpx;
					}
					
					switch, .uni-switch-wrapper, .uni-switch-input {
						width: 80rpx;
						height: 42rpx;
					}
					
					.iconfont {
						font-size: 30rpx;
						color: #909399;
					}
					
					label {
						font-size: 28rpx;
						color: #909399;
					}
				}
				.list-left {
					font-size: 28rpx;
					font-family: PingFang SC;
					font-weight: 500;
					color: #303133;
				}
			}
			.order-list:last-child {
				border:none;
			}
			.order-title {
				font-size: 32rpx;
				font-family: PingFang SC;
				font-weight: bold;
				color: #303133;
				margin-bottom: 10rpx;
			}
		}
		.footer-wrap {
			margin-top:80rpx;
			padding: 0 0 100rpx;
		}
	}
</style>

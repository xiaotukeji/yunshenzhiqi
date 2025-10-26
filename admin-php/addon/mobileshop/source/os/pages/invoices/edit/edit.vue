<template>
	<view class="edit">
		<view class="edit-box">
			<view class="edit-item">
				<view class="item-left">发票抬头</view>
				<view class="item-left">{{detailData.invoice_title}}</view>
			</view>
			<view class="edit-item">
				<view class="item-left">发票类型</view>
				<view class="item-left">{{detailData.order_type_name}}</view>
			</view>
			<view class="edit-item">
				<view class="item-left">纳税人识别号</view>
				<view class="item-left">{{detailData.invoice_title}}</view>
			</view>
			<view class="edit-item">
				<view class="item-left">发票抬头类型</view>
				<view class="item-left">{{detailData.invoice_title_type == 1 ? '个人' : '企业'}}</view>
			</view>
			<view class="edit-item">
				<view class="item-left">真实姓名</view>
				<view class="item-left">{{detailData.name}}</view>
			</view>
			<view class="edit-item">
				<view class="item-left">联系电话</view>
				<view class="item-left">{{detailData.mobile}}</view>
			</view>
			<view class="edit-item">
				<view class="item-left">邮箱地址</view>
				<view class="item-left">{{detailData.invoice_email}}</view>
			</view>
			<picker @change="bindPickerChange" :value="index" :range="arr">
				<view class="edit-item">
					<text class="item-left">开票状态</text>
					<view class="item-left">
						<text class="selected color-title">{{arr[index]}}</text>
						<text class="iconfont iconright"></text>
					</view>
				</view>
			</picker>
			<block v-if="index == 1">
				<view class="edit-item">
					<view class="item-left">发票编号</view>
					<input type="text" v-model="invoiceCode" placeholder="请输入发票编号" placeholder-class="intext" />
				</view>
				<view class="edit-item">
					<view class="item-left">发票备注</view>
					<input type="text" v-model="invoiceRemark" placeholder="请输入备注" placeholder-class="intext" />
				</view>
			</block>
		</view>
		<view class="btn">
			<button type="default">取消</button>
			<button type="default" class="btn-que" @click="onBtn()">确定</button>
		</view>
	</view>
</template>

<script>
	import {getOrderDetailById,editOrderInvoicelist} from '@/api/order'
	export default {
		data() {
			return {
				invoiceCode: '',
				invoiceRemark: '',
				arr:['未开票','已开票'],
				index: 0,
				order_id: '',
				detailData: {}
			}
		},
		onLoad(option){
			this.order_id= option.order_id
			this.ondetail()
		},
		methods: {
			ondetail(){
				getOrderDetailById(this.order_id).then(res=>{
					this.detailData = res.data
					this.index = res.data.invoice_status
					this.invoiceCode = res.data.invoice_code
					this.invoiceRemark = res.data.invoice_remark
				});
			},
			bindPickerChange: function(e) {
				this.index = e.target.value
			},
			onBtn(){
				editOrderInvoicelist({
					order_id: this.order_id,
					invoice_status: this.index,
					invoice_code: this.invoiceCode,
					invoice_remark: this.invoiceRemark
				}).then(res=>{
					let msg = res.message
					this.$util.showToast({
						title: msg
					});
				});
			},
		}
	}
</script>

<style lang="scss">
.edit {
	padding:20rpx 0 0;
}
.edit-box {
	background: #fff;
}
.edit-item {
	display: flex;
	flex-direction: row;
	justify-content: space-between;
	align-items: center;
	border-bottom: 1px solid #eee;
	background:#fff;
	padding: 26rpx 0;
	margin: 0 30rpx;
	
	.item-left {
		font-size: 28rpx;
		font-family: PingFang SC;
		font-weight: 500;
		color: #303133;
		
		.iconright {
			display: inline-block;
			margin-left: 10rpx;
			color: #cacbce;
		}
	}
	input {
		text-align: right;
		font-size: 28rpx;
		font-family: PingFang SC;
		font-weight: 500;
		color: #303133;
	}
	.intext {
		font-size: 28rpx;
		font-family: PingFang SC;
		font-weight: 500;
		color: #909399;
	}
}
.edit-item:last-child {
	border:none;
}
.btn {
	display: flex;
	padding:70rpx 0;
	
	button {
		width: 330rpx;
		height: 80rpx;
		border: 1px solid #CCCCCC;
		border-radius: 40rpx;
	}
	.btn-que {
		width: 330rpx;
		height: 80rpx;
		background: #FF6A00;
		border-radius: 40rpx;
		border: none;
		color: #fff;
	}
}
</style>

<template>
	<view>
		<view class="item-inner">
			<view class="content">
				<view class="content-list">
					<view class="online-ready" v-if="accountData == 1">当前积分</view>
					<view class="online-ready" v-if="accountData == 2">储值余额</view>
					<view class="online-ready" v-if="accountData == 3">现金余额</view>
					<view class="online-ready" v-if="accountData == 4">成长值</view>

					<input type="text" v-if="accountData == 1" :value="parseInt(numMsg.point)" style="height: 100rpx;text-align: right;" disabled="disabled" />
					<input type="text" v-if="accountData == 2" :value="numMsg.balance" style="height: 100rpx;text-align: right;" disabled="disabled" />
					<input type="text" v-if="accountData == 3" :value="numMsg.balance_money" style="height: 100rpx;text-align: right;" disabled="disabled" />
					<input type="text" v-if="accountData == 4" :value="parseInt(numMsg.growth)" style="height: 100rpx;text-align: right;" disabled="disabled" />
				</view>

				<view class="content-list">
					<view class="online-ready">调整数额</view>
					<input type="number" v-model="adjust_num" style="height: 100rpx;text-align: right;" @blur="onKeyNumberInput($event)" v-if="accountData == 1" />
					<input type="digit" v-model="adjust_num" style="height: 100rpx;text-align: right;" @blur="onKeyNumberInput($event)" v-else />
				</view>

				<view class="remark-list">
					<view class="online-ready">备注</view>
					<input type="text" v-model="remark" style="height: 100rpx;text-align: right;" @blur="onKeyNameInput($event)" />
				</view>
			</view>

			<view class="explain">
				说明：调整数额与当前
				<text v-if="accountData == 1">积分</text>
				<text v-if="accountData == 2">储值余额</text>
				<text v-if="accountData == 3">现金余额</text>
				<text v-if="accountData == 4">成长值</text>
				数相加不能小于0；正数表示增加，负数表示减少
			</view>
			<view class="bottom-btn" @click="save()">保存</view>
		</view>
	</view>
</template>

<script>
import {getMemberInfoById,modifyPoint,modifyBalance,modifyBalanceMoney,modifyGrowth} from '@/api/member'
export default {
	data() {
		return {
			accountData: 1,
			member_id: '',
			numMsg: {},
			adjust_num: '',
			remark: ''
		};
	},
	onLoad: function(option) {
		this.accountData = option.type;
		this.member_id = option.member_id;
		getMemberInfoById(option.member_id).then(res=>{
			let msg = res.message;
			if (res.code == 0 && res.data) {
				this.numMsg = res.data.member_info;
			} else {
				this.$util.showToast({
					title: msg
				});
			}
		});
	},
	onShow() {
		if (!this.$util.checkToken('/pages/member/list')) return;
		this.$store.dispatch('getShopInfo');
	},
	methods: {
		onKeyNumberInput: function(event) {
			this.adjust_num = event.detail.value;
		},
		onKeyNameInput: function(event) {
			this.remark = event.detail.value;
		},
		verify() {
			let flag = true;
			let number = /^(\-?)\d{0,10}$/;
			if ((this.accountData == 1 || this.accountData == 4) && (isNaN(this.adjust_num) || !number.test(this.adjust_num))) {
				this.$util.showToast({ title: `格式输入错误` });
				flag = false;
			}
			return flag;
		},
		save: function() {
			if (!this.verify()) return;
			var accountData = this.accountData;
			var api = null;
			switch (parseInt(accountData)) {
				case 1:
					api = modifyPoint;
					break;
				case 2:
					api = modifyBalance;
					break;
				case 3:
					api = modifyBalanceMoney;
					break;
				case 4:
					api = modifyGrowth;
					break;
			}
			api({
				adjust_num: this.adjust_num,
				remark: this.remark,
				member_id: this.numMsg.member_id
			}).then(res=>{
				let newArr = [];
				let msg = res.message;
				this.$util.showToast({
					title: msg
				});
				setTimeout(() => {
					this.$util.redirectTo('/pages/member/list');
				}, 500);
			});
		}
	}
};
</script>

<style>
.content {
	/* width: 750rpx; */
	height: 302rpx;
	background: #ffffff;
	margin: 20rpx auto;
}

.online-ready {
	line-height: 100rpx;
}

.content-list {
	display: flex;
	/* width: 690rpx; */
	justify-content: space-between;
	border-bottom: 1rpx solid #eeeeee;
	margin: auto;
	margin-left: 30rpx;
	margin-right: 30rpx;
}

.remark-list {
	display: flex;
	/* width: 690rpx; */
	justify-content: space-between;
	margin: auto;
	margin-left: 30rpx;
	margin-right: 30rpx;
}

.explain {
	/* width: 684rpx; */
	height: 59rpx;
	font-size: 24rpx;
	font-family: PingFang SC;
	font-weight: 500;
	color: #909399;
	line-height: 36rpx;
	margin-top: 20rpx;
	margin-left: 30rpx;
	margin-right: 30rpx;
}

.bottom-btn {
	width: 690rpx;
	height: 80rpx;
	background: #ff6a00;
	color: #ffffff;
	border-radius: 40rpx;
	text-align: center;
	line-height: 80rpx;
	position: absolute;
	left: 30rpx;
	bottom: 40rpx;
}
</style>

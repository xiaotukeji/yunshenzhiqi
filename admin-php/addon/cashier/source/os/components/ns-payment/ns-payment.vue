<template>
	<view class="container" v-if="payInfo">
		<view class="uni-flex uni-column payment-wrap" v-show="payStatus == 'pay'">
			<view class="header">结算</view>
			<view class="body">
				<view class="info-wrap">
					<scroll-view scroll-y="true" class="info">
						<view class="payment-money">费用总额：￥{{ payInfo.original_money | moneyFormat }}</view>
						<block v-if="promotionShow">
							<view class="title">营销优惠</view>
							<view class="uni-flex">
								<view class="type-item" :class="{ disabled: payInfo.offset.coupon_array.member_coupon_list.length == 0, active: discount.coupon_id }" @click="selectCoupon" v-if="payInfo.offset.coupon_array">
									<view class="iconfont iconyouhuiquan"></view>
									<view class="name" v-show="!discount.coupon_id">
										优惠券
										<text class="text" v-if="payInfo.offset.coupon_array.member_coupon_list.length">
											（{{ payInfo.offset.coupon_array.member_coupon_list.length }}张可用）
										</text>
									</view>
									<view class="name" v-show="discount.coupon_id">
										优惠券抵扣
										<text class="text">{{ payInfo.coupon_money }}元</text>
									</view>
									<view class="iconfont iconxuanzhong"></view>
								</view>
								<view class="type-item" :class="{ active: discount.reduction }" @click="reduction" v-if="payInfo.collectmoney_config.reduction == 1">
									<view class="iconfont iconjianmianjine"></view>
									<view class="name" v-if="discount.reduction" @click.stop="openMoneyPopup({ title: '减免金额', money: $util.moneyFormat(discount.reduction), type: 'reduction' })">
										减免
										<text class="text">{{ discount.reduction }}元</text>
									</view>
									<view v-else class="name">减免金额</view>
									<view class="iconfont iconxuanzhong"></view>
								</view>
							</view>
						</block>

						<block v-if="payInfo.offset.point_array || payInfo.offset.balance">
							<view class="title">账户余额</view>
							<view class="uni-flex">
								<view class="type-item account" :class="{ active: discount.is_use_balance, disabled: balance == 0 }" @click="useBalance" v-if="payInfo.offset.balance">
									<view class="iconfont iconyue"></view>
									<view class="name" v-if="discount.is_use_balance">
										余额支付
										<text class="text">{{ payInfo.total_balance | moneyFormat }}元</text>
									</view>
									<view class="name" v-else>
										账户余额
										<text class="text" v-if="balance > 0">{{ balance | moneyFormat }}元</text>
									</view>
									<view class="iconfont iconxuanzhong"></view>
								</view>
								<view class="type-item account" :class="{ active: discount.is_use_point, disabled: payInfo.offset.point_array.point == 0 }" @click="usePoint" v-if="payInfo.offset.point_array">
									<view class="iconfont iconjifen1"></view>
									<view class="name" v-if="discount.is_use_point">
										积分抵扣
										<text class="text">{{ payInfo.point_money | moneyFormat }}元（{{ parseInt(payInfo.offset.point_array.point) }}积分）</text>
									</view>
									<view class="name" v-else>
										账户积分
										<text class="text" v-if="globalMemberInfo.point">{{ globalMemberInfo.point }}积分</text>
									</view>
									<view class="iconfont iconxuanzhong"></view>
								</view>
							</view>
						</block>

						<view class="title">支付方式</view>
						<view class="uni-flex pay-type">
							<block v-for="(item, key,index) in payType" :key="key">
								<view class="type-item" @click="switchPayType(item.type)" :class="{ active: item.type == type }">
									<view class="pay-icon iconfont" :style="{ background: item.background }" :class="item.icon"></view>
									<view class="name">{{ item.name }} [{{ item.hotKey }}]</view>
									<view class="iconfont iconxuanzhong"></view>
								</view>
							</block>
							<view class="type-item" @click="switchMemberCode()" :class="{ active: discount.is_use_balance}">
								<view class="pay-icon iconfont iconhuiyuanma" :style="{ background: '#F7861E' }"></view>
								<view class="name">
									<text>会员码 [M]</text>
									<template v-if="discount.is_use_balance">
										<text style="margin-left: 0.05rem;">(</text>
										<text style="margin-left: 0.05rem;">使用余额</text>
										<text class="text">{{ payInfo.total_balance | moneyFormat }}元</text>
										<text style="margin-left: 0.05rem;">)</text>
									</template>
								</view>
								<!-- <view class="iconfont iconxuanzhong"></view> -->
							</view>
						</view>
						<view class="remark-info" v-if="payInfo.remark">备注：{{ payInfo.remark }}</view>
					</scroll-view>
					<view class="button-wrap">
						<view class="print-ticket">
							<checkbox-group @change="autoPrintTicket = !autoPrintTicket">
								<label>
									<checkbox :checked="autoPrintTicket" style="transform:scale(0.7)" />
									<text>打印小票</text>
								</label>
							</checkbox-group>
						</view>
						<button class="default-btn" @click="openRemark">备注</button>
						<button class="default-btn cancel-btn" plain @click="cancelPayment">取消 [Esc]</button>
						<button class="primary-btn" @click="confirm()" v-if="type != 'third' || payInfo.pay_money == 0">收款 [Enter]</button>
						<button class="primary-btn" @click="thirdConfirm()" v-else>收款 [Enter]</button>
					</view>
				</view>
				<scroll-view scroll-y="true" class="bill-wrap">
					<view class="title">支付明细</view>
					<view class="body">
						<view class="bill-info">
							<view>费用总额</view>
							<view>￥{{ payInfo.original_money | moneyFormat }}</view>
						</view>
						<view class="block-title"><text>营销优惠</text></view>
						<view class="bill-info">
							<view>减免金额</view>
							<view class="text">
								-￥{{ payInfo.offset.reduction ? $util.moneyFormat(payInfo.offset.reduction) : '0.00' }}
							</view>
						</view>
						<view class="bill-info" v-if="payInfo.offset.coupon_array">
							<view>优惠券</view>
							<view class="text">-￥{{ $util.moneyFormat(payInfo.coupon_money) }}</view>
						</view>
						<view class="bill-info" v-if="payInfo.offset.hongbao_array">
							<view>红包</view>
							<view class="text">-￥{{ $util.moneyFormat(payInfo.hongbao_money) }}</view>
						</view>
						<view class="bill-info" v-if="payInfo.offset.point_array">
							<view>积分抵扣</view>
							<view class="text">-￥{{ $util.moneyFormat(payInfo.point_money) }}</view>
						</view>
						<block v-if="payInfo.offset.balance">
							<view class="block-title"><text>余额抵扣</text></view>
							<view class="bill-info">
								<view>余额支付</view>
								<view>-￥{{ $util.moneyFormat(payInfo.total_balance) }}</view>
							</view>
						</block>
						<view class="block-title"><text>支付方式</text></view>
						<view class="bill-info">
							<view>{{ payType[type].name }}</view>
							<view v-show="type == 'cash'">
								￥{{ payInfo.cash > 0 ? $util.moneyFormat(payInfo.cash) : $util.moneyFormat(payInfo.pay_money) }}
							</view>
							<view v-show="type != 'cash'">￥{{ payInfo.pay_money | moneyFormat }}</view>
						</view>
						<view class="block-title"></view>
						<view class="bill-info">
							<view>需支付</view>
							<view>￥{{ payInfo.pay_money | moneyFormat }}</view>
						</view>
						<view class="bill-info">
							<view>实付</view>
							<view v-show="type == 'cash'">
								￥{{ payInfo.cash > 0 ? $util.moneyFormat(payInfo.cash) : $util.moneyFormat(payInfo.pay_money) }}
							</view>
							<view v-show="type != 'cash'">￥{{ payInfo.pay_money | moneyFormat }}</view>
						</view>
						<view class="bill-info" v-if="payInfo.cash_change > 0">
							<view>找零</view>
							<view>￥{{ payInfo.cash_change | moneyFormat }}</view>
						</view>
					</view>
				</scroll-view>
			</view>
		</view>

		<!-- 支付结果 -->
		<view class="uni-flex uni-column pay-result" v-show="payStatus == 'success'">
			<view class="body status">
				<view class="iconfont iconchenggong"></view>
				<view class="msg">收款成功</view>
			</view>
			<view class="footer">
				<button class="primary-btn" @click="paySuccess">继续收款 [Enter]（{{ autoComplete.time }}s）</button>
			</view>
		</view>

		<uni-popup ref="moneyPopup" type="center">
			<view class="money-wrap">
				<view class="head">
					<text>{{ moneyPopup.title }}</text>
					<text class="iconfont iconguanbi1" @click="$refs.moneyPopup.close()"></text>
				</view>
				<view class="content-wrap">
					<view class="unit">￥</view>
					<view class="money">{{ moneyPopup.money }}</view>
				</view>
				<view class="keyboard-wrap">
					<view class="num-wrap">
						<view class="key-item" @click="keydown('1')">1</view>
						<view class="key-item" @click="keydown('2')">2</view>
						<view class="key-item" @click="keydown('3')">3</view>
						<view class="key-item" @click="keydown('4')">4</view>
						<view class="key-item" @click="keydown('5')">5</view>
						<view class="key-item" @click="keydown('6')">6</view>
						<view class="key-item" @click="keydown('7')">7</view>
						<view class="key-item" @click="keydown('8')">8</view>
						<view class="key-item" @click="keydown('9')">9</view>
						<view class="key-item" @click="keydown('00')">00</view>
						<view class="key-item" @click="keydown('0')">0</view>
						<view class="key-item" @click="keydown('.')">.</view>
					</view>
					<view class="action-wrap">
						<view class="delete" @click="deleteCode">删除</view>
						<view class="delete" @click="moneyPopup.money = ''">清空</view>
						<view class="confirm" @click="moneyPopupConfirm()">确认</view>
					</view>
				</view>
			</view>
		</uni-popup>

		<uni-popup ref="couponPopup" type="center" v-if="payInfo.offset.coupon_array && payInfo.offset.coupon_array.member_coupon_list.length">
			<view class="coupon-wrap">
				<view class="head">
					<text>选择优惠券</text>
					<text class="iconfont iconguanbi1" @click="$refs.couponPopup.close()"></text>
				</view>
				<scroll-view scroll-y="true" class="body">
					<view class="list">
						<view class="item" :class="{ active: discount.coupon_id && discount.coupon_id == item.coupon_id }" v-for="(item, index) in payInfo.offset.coupon_array.member_coupon_list" :key="index" @click="selectCouponItem(item)">
							<view class="money" v-show="item.type == 'discount'">
								{{ item.discount }}
								<text class="unit">折</text>
							</view>
							<view class="money" v-show="item.type != 'discount'">
								<text class="unit">￥</text>
								{{ item.money }}
							</view>
							<view class="info">
								<view class="title">{{ item.coupon_name }}</view>
								<view class="limit">
									{{ item.at_least == 0 ? '无门槛券' : '满' + item.at_least + '可用' }}
									{{ item.type == 'discount' && item.discount_limit > 0 ? ',最多优惠' + item.discount_limit : '' }}
								</view>
								<view class="time" v-if="item.end_time">{{ $util.timeFormat(item.end_time, 'y-m-d') }}前可用
								</view>
								<view class="time" v-else>长期有效</view>
							</view>
							<view class="iconfont iconxuanzhong"></view>
						</view>
					</view>
				</scroll-view>
			</view>
		</uni-popup>

		<!-- 扫码枪支付弹窗 -->
		<uni-popup ref="thirdPopup" type="center" @change="popupChange">
			<view class="third-popup">
				<view class="head">
					<text>请选择扫码方式</text>
					<text class="iconfont iconguanbi1" @click="$refs.thirdPopup.close();thirdPopupOpen = false;"></text>
				</view>
				<view class="money">扫码收款￥{{ payInfo.pay_money | moneyFormat }}</view>
				<view class="scan-code-type" v-if="type == 'third'">
					<view class="type-item" :class="{ active: scanCodeType == 'scancode' }" @click="scanCodeType = 'scancode'">扫码枪</view>
					<view class="type-item" :class="{ active: scanCodeType == 'qrcode' }" @click="scanCodeType = 'qrcode'">二维码</view>
				</view>
				<view class="content-wrap">
					<view class="qrcode-wrap" v-show="scanCodeType == 'qrcode'">
						<block v-if="payQrcode.length">
							<view class="qrcode-item" v-for="(item, index) in payQrcode" :key="index">
								<image :src="item.qrcode.replace(/[\r\n]/g, '')" mode="widthFix" class="qrcode" v-if="item.qrcode.indexOf('data:image') != -1" />
								<image :src="$util.img(item.qrcode)" mode="widthFix" class="qrcode" v-else />
								<image :src="$util.img(item.logo)" mode="widthFix" class="logo" />
							</view>
						</block>
						<view class="empty" v-else>没有可用的收款二维码</view>
					</view>
					<view class="scancode-wrap" v-show="scanCodeType == 'scancode'">
						<block v-if="scancodeList.length">
							<view>
								<input type="number" v-model="authCode" :class="{ focus: scanCodeFocus }"
									:focus="scanCodeFocus" placeholder="请点击输入框聚焦扫码或输入付款码" @confirm="scanCode"
									@focus="scanCodeFocus = true" @blur="scanCodeInputBlur()" />
								<text class="iconfont icondelete" v-show="authCode.length > 0" @click="clearAuthCode"></text>
							</view>
							<image src="@/static/cashier/scan_code_tip.png" mode="widthFix" />
						</block>
						<view class="empty" v-else>没有可用的支付方式</view>
					</view>
				</view>
			</view>
		</uni-popup>

		<!-- 使用账号余额，验证会员码/手机号 -->
		<uni-popup ref="safeVerifyPopup" type="center">
			<view class="safe-verify-popup">
				<view class="header">
					<view class="type-wrap" v-if="active == 'memberCodePopup'">
						<view class="item">会员码</view>
					</view>
					<view class="type-wrap" v-else-if="active == 'safeVerifyPopup' && payInfo.collectmoney_config.sms_verify == 1">
						<view class="item" :class="{ active: safeVerifyType == 'payment_code' }" @click="changeSafeVerifyType('payment_code')">会员码</view>
						<view class="item" :class="{ active: safeVerifyType == 'sms_code' }" @click="changeSafeVerifyType('sms_code')">短信验证码</view>
					</view>
					<text class="iconfont iconguanbi1" @click="$refs.safeVerifyPopup.close()"></text>
				</view>
				<view class="content" v-show="safeVerifyType == 'payment_code'">
					<view class="scancode-wrap">
						<view class="input-wrap">
							<view>
								<input type="number" v-model="paymentCode" :class="{ focus: scanCodeFocus }"
									:focus="scanCodeFocus" placeholder="请点击输入框聚焦扫码或输入会员码" @confirm="verifyPaymentCode"
									@focus="scanCodeFocus = true" @blur="scanCodeInputBlur()"
									placeholder-class="placeholder" />
								<text class="iconfont icondelete" v-show="paymentCode.length > 0" @click="clearPaymentCode"></text>
							</view>
							<button class="primary-btn" @click="verifyPaymentCode">确认</button>
						</view>
						<image src="@/static/cashier/scan_code_tip.png" mode="widthFix" />
						<!-- <view class="member-code-hint">打开手机端 --》个人中心 --》 会员码</view> -->
					</view>
				</view>
				<view class="content" v-show="safeVerifyType == 'sms_code' && active == 'safeVerifyPopup'">
					<block v-if="payInfo.member_account">
						<view class="tip">将发送验证码到该手机</view>
						<view class="mobile">
							{{ payInfo.member_account.mobile.replace(/^(\d{3})\d{4}(\d{4})$/, '$1****$2') }}
						</view>
						<view class="sms-code">
							<view>
								<input type="number" v-model="smsCode" class="sms-code" placeholder="请输入验证码"
									:focus="scanCodeFocus" placeholder-class="placeholder" @focus="scanCodeFocus = true"
									@blur="scanCodeFocus = false" />
								<text class="iconfont icondelete" v-show="smsCode.length > 0" @click="clearSmsCode"></text>
							</view>
							<text class="send-tip" @click="sendMobileCode" :class="{ disabled: dynacodeData.isSend }">{{ dynacodeData.codeText }}</text>
						</view>
						<button class="primary-btn" @click="verifySmsCode">确认</button>
					</block>
					<view v-else>该会员尚未绑定手机号，无法使用该验证方式</view>
				</view>
			</view>
		</uni-popup>

		<uni-popup ref="remarkPopup" type="center">
			<view class="remark-wrap">
				<view class="header">
					<text class="title">备注</text>
					<text class="iconfont iconguanbi1" @click="$refs.remarkPopup.close()"></text>
				</view>
				<view class="body">
					<textarea v-model="remark" placeholder="填写备注信息" placeholder-class="placeholder-class" @keydown.enter="remarkConfirm" />
				</view>
				<view class="footer">
					<button type="default" class="primary-btn" @click="remarkConfirm">确认</button>
				</view>
			</view>
		</uni-popup>
	</view>
</template>

<script>
	import index from './index.js';
	export default {
		name: 'nsPayment',
		mixins: [index]
	};
</script>

<style lang="scss" scoped>
	@import './index.scss';
</style>
<template>
	<base-page>
		<view class="printer ">
			<view class="common-wrap common-form body-overhide">
			<view class="common-title">打印机设置</view>
			<view class="common-form-item">
				<label class="form-label">
					<text class="required">*</text>
					打印机名称
				</label>
				<view class="form-input-inline">
					<input type="text" v-model="savedata.printer_name" class="form-input" />
				</view>
				<text class="form-word-aux"></text>
			</view>
			<view class="common-form-item">
				<label class="form-label">
					<text class="required">*</text>
					打印机类型
				</label>
				<view class="form-inline">
					<radio-group @change="printerTypeChange" class="form-radio-group">
						<label class="radio form-radio-item">
							<radio value="cloud" :checked="savedata.printer_type == 'cloud'" />
							云打印机
						</label>
						<label class="radio form-radio-item">
							<radio value="local" :checked="savedata.printer_type == 'local'" />
							本地打印机
						</label>
						<label class="radio form-radio-item">
							<radio value="network" :checked="savedata.printer_type == 'network'" />
							网络打印机
						</label>
					</radio-group>
				</view>
			</view>
			<view v-show="savedata.printer_type == 'cloud'">
				<view class="common-form-item">
					<label class="form-label">
						<text class="required">*</text>
						打印机品牌
					</label>
					<view class="form-input-inline">
						<view class="form-input">易联云</view>
					</view>
					<text class="form-word-aux"></text>
				</view>
				<view class="common-form-item">
					<label class="form-label">
						<text class="required">*</text>
						打印机编号
					</label>
					<view class="form-input-inline">
						<input type="text" v-model="savedata.printer_code" class="form-input" />
					</view>
					<text class="form-word-aux"></text>
				</view>
				<view class="common-form-item">
					<label class="form-label">
						<text class="required">*</text>
						打印机密钥
					</label>
					<view class="form-input-inline">
						<input type="text" v-model="savedata.printer_key" class="form-input" />
					</view>
					<text class="form-word-aux"></text>
				</view>

				<view class="common-form-item">
					<label class="form-label">
						<text class="required">*</text>
						应用id
					</label>
					<view class="form-input-inline">
						<input type="text" v-model="savedata.open_id" class="form-input" />
					</view>
					<text class="form-word-aux-line">应用id（易联云-开发者中心后台应用中心里获取）</text>
				</view>

				<view class="common-form-item">
					<label class="form-label">
						<text class="required">*</text>
						apiKey
					</label>
					<view class="form-input-inline">
						<input type="text" v-model="savedata.apikey" class="form-input" />
					</view>
					<text class="form-word-aux-line">apiKey（易联云-开发者中心后台应用中心里获取）</text>
				</view>
			</view>

			<view v-show="savedata.printer_type == 'local'">
				<view class="common-form-item">
					<label class="form-label">
						<text class="required">*</text>
						打印机端口
					</label>
					<view class="form-input-inline">
						<view class="form-input">
							<input type="text" v-model="savedata.host" class="form-input" /></view>
					</view>
					<text class="form-word-aux">打印机端口 (可以填写打印机端口号：如LPT1 或 本地网络共享打印机：如\\192.168.1.100\POS_NAME)</text>
				</view>
			</view>

			<view v-show="savedata.printer_type == 'network'">
				<view class="common-form-item">
					<label class="form-label">
						<text class="required">*</text>
						打印机IP
					</label>
					<view class="form-input-inline">
						<view class="form-input">
							<input type="text" v-model="savedata.ip" class="form-input" /></view>
					</view>
					<text class="form-word-aux"></text>
				</view>
				<view class="common-form-item">
					<label class="form-label">
						<text class="required">*</text>
						打印机端口
					</label>
					<view class="form-input-inline">
						<view class="form-input">
							<input type="text" v-model="savedata.port" class="form-input" /></view>
					</view>
					<text class="form-word-aux"></text>
				</view>
			</view>

			<view v-show="savedata.printer_type == 'local' || savedata.printer_type == 'network'">
				<view class="common-form-item">
					<label class="form-label">
						<text class="required">*</text>
						打印宽度
					</label>
					<view class="form-inline">
						<radio-group @change="printWidthChange" class="form-radio-group">
							<label class="radio form-radio-item">
								<radio value="58mm" :checked="savedata.print_width == '58mm'" />
								58mm
							</label>
							<label class="radio form-radio-item">
								<radio value="80mm" :checked="savedata.print_width == '80mm'" />
								80mm
							</label>
						</radio-group>
					</view>
				</view>
			</view>

			<view class="common-title">支付打印</view>
			<view class="common-form-item">
				<label class="form-label">支付打印</label>
				<view class="form-inline">
					<radio-group @change="orderPayChange" class="form-radio-group">
						<label class="radio form-radio-item">
							<radio value="1" :checked="savedata.order_pay_open == 1" />
							开启
						</label>
						<label class="radio form-radio-item">
							<radio value="0" :checked="savedata.order_pay_open == 0" />
							关闭
						</label>
					</radio-group>
				</view>
			</view>

			<view class="common-form-item" v-if="savedata.order_pay_open == 1">
				<label class="form-label">打印模板</label>
				<view class="form-input-inline " v-if="template.goodsorder && template.goodsorder.length">
					<uni-data-select v-model="orderPayTempIndex" :localdata="template.goodsorder"></uni-data-select>
				</view>
			</view>

			<view class="common-form-item" v-if="savedata.order_pay_open == 1">
				<label class="form-label">打印联数</label>
				<view class="form-inline">
					<radio-group @change="orderPayNumChange" class="form-radio-group">
						<label class="radio form-radio-item">
							<radio value="1" :checked="savedata.order_pay_print_num == 1" />
							1
						</label>
						<label class="radio form-radio-item">
							<radio value="2" :checked="savedata.order_pay_print_num == 2" />
							2
						</label>
						<label class="radio form-radio-item">
							<radio value="3" :checked="savedata.order_pay_print_num == 3" />
							3
						</label>
						<label class="radio form-radio-item">
							<radio value="4" :checked="savedata.order_pay_print_num == 4" />
							4
						</label>
					</radio-group>
				</view>
			</view>

			<view class="common-form-item" v-if="savedata.order_pay_open == 1">
				<label class="form-label">订单类型</label>
				<view class="form-block">
					<checkbox-group class="form-checkbox-group" @change="orderPayTypeChange">
						<label class="form-checkbox-item" v-for="(item, index) in orderType">
							<checkbox :value="item.type.toString()" :checked="savedata.order_pay_order_type.includes(item.type.toString()) || savedata.order_pay_order_type.includes(parseInt(item.type))" />
							{{ item.name }}
						</label>
					</checkbox-group>
				</view>
			</view>

			<view class="common-title">收货打印</view>
			<view class="common-form-item">
				<label class="form-label">收货打印</label>
				<view class="form-inline">
					<radio-group @change="takeDeliveryChange" class="form-radio-group">
						<label class="radio form-radio-item">
							<radio value="1" :checked="savedata.take_delivery_open == 1" />
							开启
						</label>
						<label class="radio form-radio-item">
							<radio value="0" :checked="savedata.take_delivery_open == 0" />
							关闭
						</label>
					</radio-group>
				</view>
			</view>

			<view class="common-form-item" v-if="savedata.take_delivery_open == 1">
				<label class="form-label">打印模板</label>
				<view class="form-input-inline" v-if="template.goodsorder && template.goodsorder.length">
					<uni-data-select v-model="takeDeliveryTempIndex" :localdata="template.goodsorder"></uni-data-select>
				</view>
			</view>

			<view class="common-form-item" v-if="savedata.take_delivery_open == 1">
				<label class="form-label">打印联数</label>
				<view class="form-inline">
					<radio-group @change="takeDeliveryNumChange" class="form-radio-group">
						<label class="radio form-radio-item">
							<radio value="1" :checked="savedata.take_delivery_print_num == 1" />
							1
						</label>
						<label class="radio form-radio-item">
							<radio value="2" :checked="savedata.take_delivery_print_num == 2" />
							2
						</label>
						<label class="radio form-radio-item">
							<radio value="3" :checked="savedata.take_delivery_print_num == 3" />
							3
						</label>
						<label class="radio form-radio-item">
							<radio value="4" :checked="savedata.take_delivery_print_num == 4" />
							4
						</label>
					</radio-group>
				</view>
			</view>

			<view class="common-form-item" v-if="savedata.take_delivery_open == 1">
				<label class="form-label">订单类型</label>
				<view class="form-block">
					<checkbox-group class="form-checkbox-group" @change="takeDeliveryTypeChange">
						<label class="form-checkbox-item" v-for="(item, index) in orderType">
							<checkbox :value="item.type.toString()" :checked="savedata.take_delivery_order_type.includes(item.type.toString()) || savedata.take_delivery_order_type.includes(parseInt(item.type))" />
							{{ item.name }}
						</label>
					</checkbox-group>
				</view>
			</view>

			<view class="common-title">手动打印</view>
			<view class="common-form-item">
				<label class="form-label">手动打印</label>
				<view class="form-inline">
					<radio-group @change="manualChange" class="form-radio-group">
						<label class="radio form-radio-item">
							<radio value="1" :checked="savedata.manual_open == 1" />
							开启
						</label>
						<label class="radio form-radio-item">
							<radio value="0" :checked="savedata.manual_open == 0" />
							关闭
						</label>
					</radio-group>
				</view>
			</view>

			<view class="common-form-item" v-if="savedata.manual_open == 1">
				<label class="form-label">打印模板</label>
				<view class="form-input-inline" v-if="template.goodsorder && template.goodsorder.length">
					<uni-data-select v-model="manualTempIndex" :localdata="template.goodsorder"></uni-data-select>
				</view>
			</view>

			<view class="common-form-item" v-if="savedata.manual_open == 1">
				<label class="form-label">打印联数</label>
				<view class="form-inline">
					<radio-group @change="manualNumChange" class="form-radio-group">
						<label class="radio form-radio-item">
							<radio value="1" :checked="savedata.print_num == 1" />
							1
						</label>
						<label class="radio form-radio-item">
							<radio value="2" :checked="savedata.print_num == 2" />
							2
						</label>
						<label class="radio form-radio-item">
							<radio value="3" :checked="savedata.print_num == 3" />
							3
						</label>
						<label class="radio form-radio-item">
							<radio value="4" :checked="savedata.print_num == 4" />
							4
						</label>
					</radio-group>
				</view>
			</view>

			<view class="common-title">充值打印</view>
			<view class="common-form-item">
				<label class="form-label">充值打印</label>
				<view class="form-inline">
					<radio-group @change="rechargeChange" class="form-radio-group">
						<label class="radio form-radio-item">
							<radio value="1" :checked="savedata.recharge_open == 1" />
							开启
						</label>
						<label class="radio form-radio-item">
							<radio value="0" :checked="savedata.recharge_open == 0" />
							关闭
						</label>
					</radio-group>
				</view>
			</view>

			<view class="common-form-item" v-if="savedata.recharge_open == 1">
				<label class="form-label">打印模板</label>
				<view class="form-input-inline" v-if="template.recharge && template.recharge.length">
					<uni-data-select v-model="rechargeTempIndex" :localdata="template.recharge"></uni-data-select>
				</view>
			</view>

			<view class="common-form-item" v-if="savedata.recharge_open == 1">
				<label class="form-label">打印联数</label>
				<view class="form-inline">
					<radio-group @change="rechargeNumChange" class="form-radio-group">
						<label class="radio form-radio-item">
							<radio value="1" :checked="savedata.recharge_print_num == 1" />
							1
						</label>
						<label class="radio form-radio-item">
							<radio value="2" :checked="savedata.recharge_print_num == 2" />
							2
						</label>
						<label class="radio form-radio-item">
							<radio value="3" :checked="savedata.recharge_print_num == 3" />
							3
						</label>
						<label class="radio form-radio-item">
							<radio value="4" :checked="savedata.recharge_print_num == 4" />
							4
						</label>
					</radio-group>
				</view>
			</view>

			<view class="common-title">收银交班打印</view>
			<view class="common-form-item">
				<label class="form-label">收银交班打印</label>
				<view class="form-inline">
					<radio-group @change="changeShiftsChange" class="form-radio-group">
						<label class="radio form-radio-item">
							<radio value="1" :checked="savedata.change_shifts_open == 1" />
							开启
						</label>
						<label class="radio form-radio-item">
							<radio value="0" :checked="savedata.change_shifts_open == 0" />
							关闭
						</label>
					</radio-group>
				</view>
			</view>

			<view class="common-form-item" v-if="savedata.change_shifts_open == 1">
				<label class="form-label">打印模板</label>
				<view class="form-input-inline" v-if="template.change_shifts && template.change_shifts.length">
					<uni-data-select v-model="changeShiftsTempIndex" :localdata="template.change_shifts"></uni-data-select>
				</view>
			</view>

			<view class="common-form-item" v-if="savedata.change_shifts_open == 1">
				<label class="form-label">打印联数</label>
				<view class="form-inline">
					<radio-group @change="changeShiftsNumChange" class="form-radio-group">
						<label class="radio form-radio-item">
							<radio value="1" :checked="savedata.change_shifts_print_num == 1" />
							1
						</label>
						<label class="radio form-radio-item">
							<radio value="2" :checked="savedata.change_shifts_print_num == 2" />
							2
						</label>
						<label class="radio form-radio-item">
							<radio value="3" :checked="savedata.change_shifts_print_num == 3" />
							3
						</label>
						<label class="radio form-radio-item">
							<radio value="4" :checked="savedata.change_shifts_print_num == 4" />
							4
						</label>
					</radio-group>
				</view>
			</view>
			</view>
			<view class="common-btn-wrap">
				<button type="default" class="primary-btn" @click="saveFn">保存</button>
				<button type="default" class="default-btn" @click="back">返回</button>
			</view>
		</view>
	</base-page>
</template>

<script>
	import uniDataSelect from '@/components/uni-data-select/uni-data-select.vue';
	import {
		getPrinterInfo,
		getTemplate,
		getOrderType,
		editPrinter,
		addPrinter
	} from '@/api/printer.js'

	export default {
		components: {
			uniDataSelect
		},
		data() {
			return {
				printer_id: 0,
				savedata: {
					printer_name: '',
					brand: 'yilianyun',
					printer_code: '',
					printer_key: '',
					open_id: '',
					apikey: '',
					printer_type: 'cloud',

					order_pay_open: 0,
					order_pay_template_id: 0,
					order_pay_print_num: 1,
					order_pay_order_type: [],

					take_delivery_open: 0,
					take_delivery_template_id: 0,
					take_delivery_print_num: 1,
					take_delivery_order_type: [],

					manual_open: 0,
					template_id: 0,
					print_num: 1,

					recharge_open: 0,
					recharge_template_id: 0,
					recharge_print_num: 1,

					change_shifts_open: 0,
					change_shifts_template_id: 0,
					change_shifts_print_num: 1,
					host: '',
					ip: '',
					port: '',
					print_width: '58mm'
				},
				time: {
					start: '08:30',
					end: '23:30'
				},
				interval: 30,
				advance: '',
				max: '',
				week: [],
				flag: false,
				template: {},
				orderPayTempIndex: 0,
				takeDeliveryTempIndex: 0,
				manualTempIndex: 0,
				rechargeTempIndex: 0,
				changeShiftsTempIndex: 0,
				orderType: []
			};
		},
		onLoad(option) {
			if (option.printer_id) {
				this.printer_id = option.printer_id;
			}
		},
		onShow() {
			uni.setLocale('zh-Hans');
			this.getTemplate();
			this.getOrderTypeFn();
		},
		methods: {
			getData() {
				getPrinterInfo(this.printer_id).then(res => {
					if (res.code >= 0) {
						this.savedata = res.data;
						this.orderPayTempIndex = this.savedata.order_pay_template_id;
						this.takeDeliveryTempIndex = this.savedata.take_delivery_template_id;
						this.manualTempIndex = this.savedata.template_id;
						this.rechargeTempIndex = this.savedata.recharge_template_id;
						this.changeShiftsTempIndex = this.savedata.change_shifts_template_id;
					} else {
						this.$util.showToast({
							title: res.message
						});
					}
				})
			},
			back() {
				this.$util.redirectTo('/pages/printer/list');
			},
			getTemplate() {
				getTemplate().then(res => {
					if (res.code == 0) {
						let template = {};
						res.data.map((item, index) => {
							if (!template[item.type]) template[item.type] = [];
							var obj = {};
							obj.text = item.template_name;
							obj.value = item.template_id;
							template[item.type].push(obj);
						});
						this.template = template;
						if (this.printer_id) {
							this.getData();
						}
					}
				})
			},
			getOrderTypeFn() {
				getOrderType().then(res => {
					if (res.code == 0) {
						this.orderType = res.data;
					}
				});
			},
			printerTypeChange(e) {
				this.savedata.printer_type = e.detail.value;
			},
			printWidthChange(e) {
				this.savedata.print_width = e.detail.value;
			},
			orderPayChange(e) {
				this.savedata.order_pay_open = e.detail.value;
			},
			orderPayNumChange(e) {
				this.savedata.order_pay_print_num = e.detail.value;
			},
			orderPayTypeChange(e) {
				this.savedata.order_pay_order_type = e.detail.value;
			},
			takeDeliveryChange(e) {
				this.savedata.take_delivery_open = e.detail.value;
			},
			takeDeliveryNumChange(e) {
				this.savedata.take_delivery_print_num = e.detail.value;
			},
			takeDeliveryTypeChange(e) {
				this.savedata.take_delivery_order_type = e.detail.value;
			},
			manualChange(e) {
				this.savedata.manual_open = e.detail.value;
			},
			manualNumChange(e) {
				this.savedata.print_num = e.detail.value;
			},
			rechargeChange(e) {
				this.savedata.recharge_open = e.detail.value;
			},
			rechargeNumChange(e) {
				this.savedata.recharge_print_num = e.detail.value;
			},
			changeShiftsChange(e) {
				this.savedata.change_shifts_open = e.detail.value;
			},
			changeShiftsNumChange(e) {
				this.savedata.change_shifts_print_num = e.detail.value;
			},
			check() {
				let data = Object.assign({}, this.savedata);
				if (!data.printer_name) {
					this.$util.showToast({
						title: '请输入打印机名称'
					});
					return false;
				}
				if (data.printer_type == 'cloud') {
					if (!data.printer_code) {
						this.$util.showToast({
							title: '请输入打印机编号'
						});
						return false;
					}

					if (!data.printer_key) {
						this.$util.showToast({
							title: '请输入打印机密钥'
						});
						return false;
					}

					if (!data.open_id) {
						this.$util.showToast({
							title: '请输入应用id'
						});
						return false;
					}

					if (!data.apikey) {
						this.$util.showToast({
							title: '请输入apikey'
						});
						return false;
					}
				}

				if (data.printer_type == 'local') {
					if (!data.host) {
						this.$util.showToast({
							title: '请输入打印机打印机端口'
						});
						return false;
					}
				}

				if (data.printer_type == 'network') {
					if (!data.ip) {
						this.$util.showToast({
							title: '请输入打印机打印机地址'
						});
						return false;
					}
					if (!data.port) {
						this.$util.showToast({
							title: '请输入打印机打印机端口'
						});
						return false;
					}
				}

				return true;
			},
			saveFn() {
				if (this.check()) {
					let data = this.savedata;
					data.take_delivery_order_type = data.take_delivery_order_type.toString();
					data.order_pay_order_type = data.order_pay_order_type.toString();

					data.order_pay_template_id = this.orderPayTempIndex;
					data.take_delivery_template_id = this.takeDeliveryTempIndex;
					data.template_id = this.manualTempIndex;
					data.recharge_template_id = this.rechargeTempIndex;
					data.change_shifts_template_id = this.changeShiftsTempIndex;

					let action = '';
					if (this.printer_id > 0) {
						data.printer_id = this.printer_id;
						action = editPrinter(data)
					} else {
						action = addPrinter(data)
					}

					if (this.flag) return false;
					this.flag = true;
					action.then(res => {
						this.flag = false;
						this.$util.showToast({
							title: res.message
						});
						if (res.code >= 0) {
							setTimeout(() => {
								this.$util.redirectTo('/pages/printer/list');
							}, 1500);
						}
					});
				}
			},
			timeTurnTimeStamp(time) {
				let data = time.split(':');
				return data[0] * 3600 + data[1] * 60;
			},
			timeFormat(time) {
				let h = time / 3600;
				let i = (time % 3600) / 60;
				h = h < 10 ? '0' + h : h;
				i = i < 10 ? '0' + i : i;
				return h + ':' + i;
			}
		}
	};
</script>

<style lang="scss" scoped>
	.common-title {
		font-size: 0.18rem;
		margin-bottom: 0.2rem;
	}
	/deep/ .uni-select{
		border-width: 0;
		border-radius: 0;
	}
	.printer{
		position: relative;
		height: 100%;
		.common-wrap {
			background-color: #fff;
			@extend %body-overhide;
			padding: 0.2rem 0.2rem 0.88rem 0.2rem;
			
		}
		.common-btn-wrap{
			width: 100%;
			position: absolute;
			left: 0;
			bottom: 0;
			padding: 0.24rem 0.2rem;
			display: flex;
			justify-content: space-between;
			margin: 0;
			box-sizing: border-box;
			background-color: #fff;
			button{
				line-height: 0.4rem;
				height: 0.4rem;
				margin: 0;
				flex: 1;
				&.primary-btn{
					margin-right: 0.1rem;
				}
			}
		}
	}
</style>
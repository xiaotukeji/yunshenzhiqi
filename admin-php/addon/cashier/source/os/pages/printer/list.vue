<template>
	<base-page>
		<view class="printerlist">
			<view class="printerlist-box">
				<view class="printerlist-left">
					<view class="printer-title">
						打印机
						<text class="iconfont icongengduo1"></text>
					</view>
					<view class="printer-list-wrap">
						<block v-if="list.length > 0">
							<scroll-view scroll-y="true" class="printer-list-scroll all-scroll" @scrolltolower="getPrinterListFn">
								<view class="item" @click="printerSelect(item, index)" v-for="(item, index) in list" :key="index" :class="index == selectprinterKeys ? 'itemhover' : ''">
									<view class="item-right">
										<view class="printer-name">{{ item.printer_name }}</view>
										<view class="printer-money">{{ printerType(item.printer_type) }}</view>
									</view>
								</view>
							</scroll-view>
						</block>
						<view class="notYet" v-else-if="!one_judge && list.length == 0">暂无打印机</view>
					</view>
					<view class="add-printer">
						<button type="default" class="primary-btn" @click="addprinter">添加打印机</button>
					</view>
				</view>
				<view class="printerlist-right" v-show="!one_judge">
					<view class="printer-title">打印机详情</view>
					<view class="printer-information">
						<block v-if="JSON.stringify(detail) != '{}'">
							<view class="title">基本信息</view>
							<view class="information-box">
								<view class="box-left">
									<view class="information">
										<view>名称：</view>
										<view>{{ detail.printer_name }}</view>
									</view>
									<view class="information">
										<view>打印机类型：</view>
										<view>{{ printerType(detail.printer_type) }}</view>
									</view>
									<block v-if="detail.printer_type == 'cloud'">
										<view class="information">
											<view>品牌：</view>
											<view>{{ brandList[detail.brand] }}</view>
										</view>
										<view class="information">
											<view>打印机编号：</view>
											<view>{{ detail.printer_code }}</view>
										</view>
										<view class="information">
											<view>打印机秘钥：</view>
											<view>{{ detail.printer_key }}</view>
										</view>
										<view class="information">
											<view>应用id：</view>
											<view>{{ detail.open_id }}</view>
										</view>
										<view class="information">
											<view>apiKey：</view>
											<view>{{ detail.apikey }}</view>
										</view>
									</block>
									<block v-if="detail.printer_type == 'local'">
										<view class="information">
											<view>打印机端口：</view>
											<view>{{ detail.host }}</view>
										</view>
									</block>
									<block v-if="detail.printer_type == 'network'">
										<view class="information">
											<view>打印机地址：</view>
											<view>{{ detail.ip }}</view>
										</view>
										<view class="information">
											<view>打印机端口：</view>
											<view>{{ detail.host }}</view>
										</view>
									</block>
									<view class="information">
										<view>添加时间：</view>
										<view>{{ detail.create_time ? $util.timeFormat(detail.create_time) : '--' }}</view>
									</view>
								</view>
							</view>

							<view class="title">支付打印</view>
							<view class="information-box">
								<view class="box-left">
									<view class="information">
										<view>支付打印：</view>
										<view>{{ detail.order_pay_open ? '开启' : '关闭' }}</view>
									</view>
									<view class="information" v-if="detail.order_pay_open">
										<view>打印模板：</view>
										<view>
											{{ template[detail.order_pay_template_id] ? template[detail.order_pay_template_id].template_name : '--' }}
										</view>
									</view>
									<view class="information" v-if="detail.order_pay_open">
										<view>打印联数：</view>
										<view>{{ detail.order_pay_print_num }}</view>
									</view>
									<view class="information" v-if="detail.order_pay_open">
										<view>订单类型：</view>
										<view>
											<block v-for="(item, index) in detail.order_pay_order_type" :key="index">
												<text v-if="item" class="order-type">{{ detail['order_type_list'][item]['name'] }}</text>
											</block>
										</view>
									</view>
								</view>
							</view>
							<view class="title">收货打印</view>
							<view class="information-box">
								<view class="box-left">
									<view class="information">
										<view>收货打印：</view>
										<view>{{ detail.take_delivery_open ? '开启' : '关闭' }}</view>
									</view>
									<view class="information" v-if="detail.take_delivery_open">
										<view>打印模板：</view>
										<view>
											{{ template[detail.take_delivery_template_id] ? template[detail.take_delivery_template_id].template_name : '--' }}
										</view>
									</view>
									<view class="information" v-if="detail.take_delivery_open">
										<view>打印联数：</view>
										<view>{{ detail.take_delivery_print_num }}</view>
									</view>
									<view class="information" v-if="detail.take_delivery_open">
										<view>订单类型：</view>
										<view>
											<block v-for="(item, index) in detail.take_delivery_order_type" :key="index">
												<text v-if="item" class="order-type">{{ detail['order_type_list'][item]['name'] }}</text>
											</block>
										</view>
									</view>
								</view>
							</view>

							<view class="title">手动打印</view>
							<view class="information-box">
								<view class="box-left">
									<view class="information">
										<view>手动打印：</view>
										<view>{{ detail.manual_open ? '开启' : '关闭' }}</view>
									</view>
									<view class="information" v-if="detail.manual_open">
										<view>打印模板：</view>
										<view>
											{{ template[detail.template_id] ? template[detail.template_id].template_name : '--' }}
										</view>
									</view>
									<view class="information" v-if="detail.manual_open">
										<view>打印联数：</view>
										<view>{{ detail.print_num }}</view>
									</view>
								</view>
							</view>
							<view class="title">充值打印</view>
							<view class="information-box">
								<view class="box-left">
									<view class="information">
										<view>充值打印：</view>
										<view>{{ detail.recharge_open ? '开启' : '关闭' }}</view>
									</view>
									<view class="information" v-if="detail.recharge_open">
										<view>打印模板：</view>
										<view>
											{{ template[detail.recharge_template_id] ? template[detail.recharge_template_id].template_name : '--' }}
										</view>
									</view>
									<view class="information" v-if="detail.recharge_open">
										<view>打印联数：</view>
										<view>{{ detail.recharge_print_num }}</view>
									</view>
								</view>
							</view>

							<view class="title">收银交班打印</view>
							<view class="information-box">
								<view class="box-left">
									<view class="information">
										<view>收银交班打印：</view>
										<view>{{ detail.change_shifts_open ? '开启' : '关闭' }}</view>
									</view>
									<view class="information" v-if="detail.change_shifts_open">
										<view>打印模板：</view>
										<view>
											{{ template[detail.change_shifts_template_id] ? template[detail.change_shifts_template_id].template_name : '--' }}
										</view>
									</view>
									<view class="information" v-if="detail.change_shifts_open">
										<view>打印联数：</view>
										<view>{{ detail.change_shifts_print_num }}</view>
									</view>
								</view>
							</view>
						</block>
						<block v-else>
							<image class="cart-empty" src="@/static/cashier/cart_empty.png" mode="widthFix" />
						</block>
					</view>
					<view class="button-box" v-if="JSON.stringify(detail) != '{}'">
						<button class="default-btn" @click="$refs.deletePop.open()">删除</button>
						<button class="default-btn" @click="editprinter(detail.printer_id)">修改</button>
					</view>
				</view>
			</view>
		</view>
		<!-- 删除 -->
		<uni-popup ref="deletePop" type="center">
			<view class="confirm-pop">
				<view class="title">确定要删除吗？</view>
				<view class="btn">
					<button type="primary" class="default-btn btn save" @click="$refs.deletePop.close()">取消</button>
					<button type="primary" class="primary-btn btn" @click="deletePrinterFn(detail.printer_id)">确定</button>
				</view>
			</view>
		</uni-popup>
	</base-page>
</template>

<script>
import {
	getPrinterList,
	getTemplate,
	getPrinterInfo,
	deletePrinter
} from '@/api/printer.js'

export default {
	data() {
		return {
			selectprinterKeys: 0,
			search_text: '',
			page: 1,
			// 每次返回数据数
			page_size: 8,
			// 第一次请求列表做详情渲染判断
			one_judge: true,
			//详情数据
			detail: {},
			brandList: {
				yilianyun: '易联云',
				'365': '365'
			},
			template: {},
			list: [],
			repeatFlag: false,
		};
	},
	onLoad() {
		// 初始化请求打印机列表数据
		this.getTemplateFn();
	},
	methods: {
		printerType(printerType) {
			var str = '';
			switch (printerType) {
				case 'cloud':
					str = '云打印机';
					break;
				case 'local':
					str = '本地打印机';
					break;
				case 'network':
					str = '网络打印机';
					break;
			}
			return str;
		},
		switchStoreAfter() {
			this.search()
		},
		printerSelect(item, keys) {
			this.selectprinterKeys = keys;
			this.getPrinterDetail(item.printer_id);
		},
		// 搜索员工
		search() {
			this.page = 1;
			this.list = [];
			this.one_judge = true;
			this.getPrinterListFn();
		},
		addprinter() {
			this.$util.redirectTo('/pages/printer/add');
		},
		editprinter(printer_id) {
			this.$util.redirectTo('/pages/printer/add', {
				printer_id: printer_id
			});
		},
		/**
		 * 请求的列表数据
		 */
		getPrinterListFn() {
			getPrinterList({
				page: this.page,
				page_size: this.page_size
			}).then(res => {
				if (res.data.list.length == 0 && this.one_judge) {
					this.detail = {};
					this.one_judge = false;
				}
				if (res.code >= 0 && res.data.list.length != 0) {
					this.page += 1;
					if (this.list.length == 0) {
						this.list = res.data.list;
					} else {
						this.list = this.list.concat(res.data.list);
					}

					//初始时加载一遍详情数据
					if (this.one_judge) {
						this.getPrinterDetail(this.list[0].printer_id);
					}
				}
			})
		},
		getTemplateFn() {
			getTemplate().then(res => {
				if (res.code == 0) {
					let template = {};
					res.data.forEach(item => {
						template[item.template_id] = item;
					})
					this.template = template;
					this.getPrinterListFn();
				}
			})
		},
		getPrinterDetail(printer_id) {
			getPrinterInfo(printer_id).then(res => {
				if (res.code == 0) {
					this.detail = res.data;
					this.one_judge = false;
				}
			})
		},
		deletePrinterFn(printer_id) {
			if (this.repeatFlag) return;
			this.repeatFlag = true;
			deletePrinter(printer_id).then(res => {
				this.repeatFlag = false;
				if (res.code >= 0) {
					this.page = 1;
					this.list = [];
					this.one_judge = true;
					this.$refs.deletePop.close()
					this.getPrinterListFn();
				} else {
					this.$util.showToast({
						title: res.message
					});
				}
			})
		}
	}
};
</script>

<style scoped lang="scss">
	@import './public/css/printer.scss';
</style>
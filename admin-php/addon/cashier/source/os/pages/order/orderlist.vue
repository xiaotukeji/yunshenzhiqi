<template>
	<base-page>
		<view class="goodslist">
			<view class="goodslist-box">
				<view class="goodslist-left">
					<view class="goods-title">
						订单管理
						<view class="screen-btn" @click="showScreen = !showScreen">{{showScreen ? '关闭':'筛选'}}
						</view>
					</view>
					
					<view class="screen-content" v-if="showScreen">
						<scroll-view scroll-y="true" class="screen-box">
							<view class="screen-item">
								<view class="tit">创建时间</view>
								<view class="values">
									<view class="value" :class="(!conditions.start_time_val && !conditions.end_time_val ) && conditions.time_type == '' ? 'active' : ''" @click="changeCondition('time_type','')">全部</view>
									<view class="value" :class="(!conditions.start_time_val && !conditions.end_time_val ) && conditions.time_type == '7' ? 'active' : ''" @click="changeCondition('time_type','7')">近7天</view>
									<view class="value" :class="(!conditions.start_time_val && !conditions.end_time_val ) && conditions.time_type == '30' ? 'active' : ''" @click="changeCondition('time_type','30')">近30天</view>
									<view class="time-range">
										<uni-datetime-picker class="time-value" :inputDisabled="false" v-model="conditions.start_time_val" type="datetime" placeholder="开始时间"  />
										<view class="line">-</view>
										<uni-datetime-picker class="time-value" :inputDisabled="false" v-model="conditions.end_time_val" type="datetime" placeholder="结束时间"  />
									</view>
								</view>
							</view>
							<view v-if="currOrderList == 'online'">
								<view class="screen-item">
									<view class="tit">订单类型</view>
									<view class="values">
										<view class="value" :class="conditions.order_type == vItem.type ? 'active' : ''" v-for="(vItem,vIndex) in orderConditionList.order_type_list" :key="vIndex" @click="changeCondition('order_type',vItem.type)">{{vItem.name}}</view>
									</view>
								</view>
								<view class="screen-item">
									<view class="tit">订单状态</view>
									<view class="values">
										<view class="value" :class="conditions.order_status == '' ? 'active' : ''" @click="changeCondition('order_status','')">全部</view>
										<view class="value" :class="conditions.order_status == vItem.type ? 'active' : ''" v-for="(vItem,vIndex) in orderConditionList.order_status_list" :key="vIndex" @click="changeCondition('order_status',vItem.type)">{{vItem.name}}</view>
									</view>
								</view>
								<view class="screen-item">
									<view class="tit">付款方式</view>
									<view class="values">
										<view class="value" :class="conditions.pay_type == '' ? 'active' : ''" @click="changeCondition('pay_type','')">全部</view>
										<view class="value" :class="conditions.pay_type == vItem.type ? 'active' : ''" v-for="(vItem,vIndex) in orderConditionList.pay_type_list" :key="vIndex" @click="changeCondition('pay_type',vItem.type)">{{vItem.name}}</view>
									</view>
								</view>
								<view class="screen-item">
									<view class="tit">订单来源</view>
									<view class="values">
										<view class="value" :class="conditions.order_from == '' ? 'active' : ''" @click="changeCondition('order_from','')">全部</view>
										<view class="value" :class="conditions.order_from == vItem.type ? 'active' : ''" v-for="(vItem,vIndex) in orderConditionList.order_from_list" :key="vIndex" @click="changeCondition('order_from',vItem.type)">{{vItem.name}}</view>
									</view>
								</view>
							</view>
							<view v-else>
								<view class="screen-item">
									<view class="tit">订单类型</view>
									<view class="values">
										<view class="value" :class="conditions.order_type == 'all' ? 'active' : ''" @click="changeCondition('order_type','all')">全部</view>
										<view class="value" :class="conditions.order_type == vItem.type ? 'active' : ''" v-for="(vItem,vIndex) in orderConditionList.cashier_order_type_list" :key="vIndex" @click="changeCondition('order_type',vItem.type)">{{vItem.name}}</view>
									</view>
								</view>
								<view class="screen-item">
									<view class="tit">订单状态</view>
									<view class="values">
										<view class="value" :class="conditions.order_status == '' ? 'active' : ''" @click="changeCondition('order_status','')">全部</view>
										<view class="value" :class="conditions.order_status == vItem.type ? 'active' : ''" v-for="(vItem,vIndex) in orderConditionList.cashier_order_status_list" :key="vIndex" @click="changeCondition('order_status',vItem.type)">{{vItem.name}}</view>
									</view>
								</view>
								<view class="screen-item">
									<view class="tit">付款方式</view>
									<view class="values">
										<view class="value" :class="conditions.pay_type == '' ? 'active' : ''" @click="changeCondition('pay_type','')">全部</view>
										<view class="value" :class="conditions.pay_type == vItem.type ? 'active' : ''" v-for="(vItem,vIndex) in orderConditionList.cashier_pay_type_list" :key="vIndex" @click="changeCondition('pay_type',vItem.type)">{{vItem.name}}</view>
									</view>
								</view>
							</view>
						</scroll-view>
						<view class="search-btn">
							<view class="btn" @click="resetCondition()">重置</view>
							<view class="btn" @click="searchOrder()">确定</view>
						</view>
					</view>
					<view v-if="!showScreen" class="goods-search">
						<view class="search">
							<text class="iconfont icon31sousuo" @click="search('')"></text>
							<input type="text" v-model="search_text" @keydown.enter="search('enter')" placeholder="输入订单号/商品名称/收货人姓名/手机号/留言/备注" />
						</view>
					</view>
					<view v-if="!showScreen" class="order-type-list">
						<view class="class-item" :class="{ active: currOrderList == 'cashier' }" @click="selectOrderList('cashier')">收银订单</view>
						<view class="class-item" :class="{ active: currOrderList == 'online' }" @click="selectOrderList('online')">商城订单</view>
					</view>
					<block v-if="!showScreen && !one_judge && order_list.length > 0">
						<scroll-view :scroll-top="scrollTop" @scroll="scroll" scroll-y="true" class="goods-list-scroll" :show-scrollbar="false" @scrolltolower="getOrderListFn">
							<view class="item" @click="getOrderDetailFn(item.order_id, index)" v-for="(item, index) in order_list" :key="index" :class="index == selectGoodsKeys ? 'itemhover' : ''">
								<view class="title">
									<view>订单编号：{{ item.order_no }}</view>
									<view v-if="item.order_type == 5">{{ item.cashier_order_type_name }}</view>
									<view v-else="item.order_type != 5">{{ item.order_type_name }}</view>
								</view>
								<view class="total-money-num">
									<view class="flex-shrink-0">{{ item.order_status_name }}</view>
									<view class="member-info">
										<view>买家：</view>
										<view class="member-info-name" :title="item.nickname" v-if="item.member_id">{{ item.nickname }}</view>
										<view class="member-info-name" v-else>散客</view>
									</view>

									<view class="box">
										<view>实付金额</view>
										<view>￥{{ item.pay_money }}</view>
									</view>
									<view class="refund-state flex-shrink-0" v-if="parseFloat(item.refund_money) > 0">退款</view>
								</view>
							</view>
						</scroll-view>
					</block>
					<view class="notYet" v-else-if="!showScreen && !one_judge && order_list.length == 0">暂无数据</view>
				
				</view>
				<view class="goodslist-right" v-show="type == 'detail'">
					<view class="goods-title">订单详情</view>
					<view class="order-information tab-wrap" v-show="!one_judge">
						<view class="tab-head">
							<text v-for="(item, index) in tabObj.list" :key="index" :class="{ active: tabObj.index == item.value }" @click="tabObj.index = item.value" v-if="(item.value == 3 && order_detail.order_log && order_detail.order_log.length > 0) || item.value != 3">
								{{ item.name }}
							</text>
						</view>
						<view class="tab-content" v-if="JSON.stringify(order_detail) != '{}'">
							<view class="other-information" v-if="tabObj.index == 1">
								<view class="item-info">
									<view class="info-tit">收货信息</view>
									<view class="infos">
										<view class="info">收货人：{{order_detail.name}}</view>
										<view class="info">收货电话：{{order_detail.mobile}}</view>
										<view class="info">收货地址：{{order_detail.full_address}}{{ order_detail.address}}</view>
									</view>
								</view>
								<view class="item-info">
									<view class="info-tit">用户信息</view>
									<view class="infos">
										<view class="info" v-if="order_detail.member_id">
											用户昵称：{{ order_detail.nickname }}
											<text class="look" @click="viewMember()">查看会员</text>
										</view>
										<view class="info" v-else>用户昵称：散客</view>
									</view>
								</view>
								<view class="item-info">
									<view class="info-tit">订单信息</view>
									<view class="infos">
										<view class="info">订单类型：{{ order_detail.order_type_name }}</view>
										<view class="info">订单状态：{{ order_detail.order_status_name }}</view>
										<view class="info">{{ order_detail.order_type == 2 || order_detail.order_type == 3 ? ( order_detail.order_type == 2 ? '买家预计上门时间：' : '买家要求送达时间：' ) : ''}}{{ ( order_detail.order_type == 2 || order_detail.order_type == 3 ) ? order_detail.buyer_ask_delivery_time : '' }}</view>
										<view class="info">订单编号：{{order_detail.order_no}}</view>
										<view class="info">外部交易号：{{order_detail.out_trade_no}}</view>
										<view class="info">订单来源：{{order_detail.order_from_name}}</view>
										<view class="info">创建时间：{{$util.timeFormat(order_detail.create_time)}}</view>
										<view class="info">支付时间：{{$util.timeFormat(order_detail.pay_time)}}</view>
										<view class="info">支付方式：{{order_detail.pay_type_name}}</view>
										<view class="info">商品总价：￥{{order_detail.goods_money}}</view>
										<view class="info">店铺优惠：-￥{{order_detail.promotion_money}}</view>
										<view class="info">订单减免：-￥{{order_detail.reduction}}</view>
										<view class="info">优惠券：-￥{{order_detail.coupon_money}}</view>
										<view class="info">积分抵扣：-￥{{order_detail.point_money}}</view>
										<view class="info" v-show="order_detail.online_money > 0">线上支付：￥{{ order_detail.online_money | moneyFormat }}</view>
										<view class="info" v-show="order_detail.cash > 0">现金支付：￥{{ order_detail.cash | moneyFormat }}</view>
										<view class="info" v-show="order_detail.cash_change > 0">找零：-￥{{ order_detail.cash_change | moneyFormat }}</view>
										<view class="info" v-show="parseFloat(order_detail.balance_money) > 0">余额抵扣：￥{{ $util.moneyFormat(order_detail.balance_money) }}</view>
										<view class="info" v-show="order_detail.own_wechatpay > 0">个人微信收款：￥{{ order_detail.own_wechatpay | moneyFormat }}</view>
										<view class="info" v-show="order_detail.own_alipay > 0">个人支付宝收款：￥{{ order_detail.own_alipay | moneyFormat }}</view>
										<view class="info" v-show="order_detail.own_pos > 0">个人POS收款：￥{{ order_detail.own_pos | moneyFormat }}</view>
										<view class="info" >实付金额：￥{{ order_detail.pay_money }}</view>
										<view class="info" v-if="parseFloat(order_detail.refund_money) > 0">退款金额：￥{{ order_detail.refund_money }}</view>
									</view>
								</view>
								<view class="item-info">
									<view class="info-tit">订单备注</view>
									<view class="infos remark">
										<view class="info">买家留言：{{order_detail.buyer_message}}</view>
										<view class="info">商家备注：{{ order_detail.remark }}</view>
									</view>
								</view>
							</view>

							<view class="goods-info" v-if="tabObj.index == 2">
								<view class="table">
									<view class="table-th table-all">
										<view class="table-td" style="width:55%">商品（元）</view>
										<view class="table-td" style="width:15%">价格</view>
										<view class="table-td" style="width:10%">数量</view>
										<view class="table-td" style="width:15%;justify-content: flex-end;">小计（元）</view>
									</view>
									<view class="table-tr table-all" v-for="(item, index) in order_detail.order_goods" :key="index">
										<view class="table-td" style="width:55%">
											<image v-if="item.sku_image == '@/static/goods/goods.png'" src="@/static/goods/goods.png" mode="widthFix"/>
											<image v-else :src="$util.img(item.sku_image, { size: 'small' })" @error="item.sku_image = '@/static/goods/goods.png'" mode="widthFix"/>
											<view class="content-text">
												<view>
													<text v-if="item.is_gift" class="gift-tag">赠品</text>
													{{ item.goods_name }}
												</view>
												<view class="text-color-gray">
													{{ item.spec_name }}
												</view>
												<view v-if="item.refund_status != 0">
													<text class="refun-status">{{ item.refund_status_name }}</text>
												</view>
											</view>
										</view>
										<view class="table-td" style="width:15%">{{ item.price }}</view>
										<view class="table-td" style="width:10%">{{ item.num }}</view>
										<view class="table-td uni-column" style="width:15%;align-items: flex-end;">
											<view>{{ item.goods_money }}</view>
											<view class="refund-success" v-if="item.refund_status == 'refund_complete'">退款成功</view>
										</view>
									</view>
								</view>
							</view>

							<view class="other-information journal" v-if="order_detail.order_log && order_detail.order_log.length > 0 && tabObj.index == 3">
								<ns-order-log :list="order_detail.order_log"></ns-order-log>
							</view>

						</view>
						<view class="notYet" v-if="JSON.stringify(order_detail) == '{}'">暂无数据</view>
						<ns-loading ref="detailLoading"></ns-loading>
					</view>
					<view class="remarks-box" v-if="JSON.stringify(order_detail) != '{}'">
						
						<button type="primary" class="default-btn comp-btn remarks" @click="printTicket">打印小票</button>
						<button type="primary" class="default-btn comp-btn remarks" @click="type = 'refund'" v-if="order_detail.is_enable_refund">退款</button>
						<button type="primary" class="default-btn comp-btn remarks" @click="open('remark')">备注</button>
						<!-- <button type="primary" class="default-btn comp-btn remarks">调整价格</button> -->
						<block v-if="order_detail.order_status_action.action">
							<block v-for="(item, index) in order_detail.order_status_action.action" :key="index">
								<button type="primary" class="primary-btn btn remarks" @click="open(item['action'])" v-if="item['action'] == 'orderLocalDelivery' || item['action'] == 'orderDelivery' || item['action'] == 'orderClose'||item['action'] == 'orderAdjustMoney'">{{ item.title }}</button>
							</block>
						</block>
							<button v-if="order_detail.order_type == 2 && order_detail.order_status == 2" type="primary" class="primary-btn btn remarks" @click="open('storeOrderTakeDelivery')">提货</button>
						<button v-for="(item, index) in order_detail.order_action" :key="index" type="primary" class="primary-btn btn remarks" @click="open(item['action'])">{{ item.title }}</button>
					</view>
				</view>
				<!-- 订单退款 -->
				<view class="goodslist-right refund-wrap" v-show="type == 'refund'">
					<view class="goods-title">退款</view>
					<view class="content common-scrollbar">
						<view v-show="refundStep == 0">
							<block v-for="(item, index) in order_detail.order_goods" :key="index">
								<view class="goods-item" v-if="item.refund_status == 0">
									<view class="iconfont" :class="refundGoods.indexOf(item.order_goods_id) == -1 ? 'iconyuan_checkbox' : 'iconyuan_checked'" @click="selectOrderGoods(item)"></view>
									<view class="image">
										<image v-if="item.sku_image == '@/static/goods/goods.png'" src="@/static/goods/goods.png" mode="widthFix"/>
										<image v-else :src="$util.img(item.sku_image, { size: 'small' })" @error="item.sku_image = '@/static/goods/goods.png'" mode="widthFix"/>
									</view>
									<view class="info">
										<view class="content-text">{{ item.goods_name }}</view>
									</view>
									<view>
										<view class="price">￥{{ item.price }}</view>
										<view class="num">x {{ item.num }}</view>
									</view>
								</view>
							</block>
						</view>
						<block v-if="refundDetail">
							<view class="bg-grey" v-show="refundStep == 1">
								<block v-for="(refundItem, refundIndex) in refundDetail.refund_list" :key="refundIndex">
									<view class="refund-goods-item">
										<view class="row">
											<view class="title">退款商品</view>
											<view class="cont">
												<view class="goods-item">
													<view class="image">
														<image v-if="refundItem.order_goods_info.sku_image == '@/static/goods/goods.png'" src="@/static/goods/goods.png" mode="widthFix"/>
														<image v-else :src="$util.img(refundItem.order_goods_info.sku_image, { size: 'small' })" @error="refundItem.order_goods_info.sku_image = '@/static/goods/goods.png'" mode="widthFix"/>
													</view>
													<view class="info">
														<view class="content-text">{{ refundItem.order_goods_info.sku_name }}</view>
													</view>
												</view>
											</view>
										</view>
										<view class="row">
											<view class="title">退款金额</view>
											<view class="cont">
												<view class="money-box">
													<input type="number" v-model="refundApply.refund_array[refundItem.order_goods_info.order_goods_id].refund_money" />
													元
												</view>
												<view class="refund-money">
													可退金额：
													<text>￥{{ refundItem.order_goods_info.refund_apply_money | moneyFormat }}</text>
												</view>
											</view>
										</view>
										<view class="row" style="margin-top:8px;">
											<view class="title">是否返还库存</view>
											<view class="cont">
												<radio-group @change="changeIsRefundStock(refundItem,$event)" class="form-radio-group">
													<label class="radio form-radio-item">
														<radio value="1" :checked="refundApply.refund_array[refundItem.order_goods_info.order_goods_id].is_refund_stock == 1" />
														是
													</label>
													<label class="radio form-radio-item">
														<radio value="0" :checked="refundApply.refund_array[refundItem.order_goods_info.order_goods_id].is_refund_stock == 0" />
														否
													</label>
												</radio-group>
											</view>
										</view>
										<view class="row" v-if="refundApply.refund_array[refundItem.order_goods_info.order_goods_id].is_refund_stock == 1" style="margin-top:8px;">
											<view class="title">退还数量</view>
											<view class="cont">
												<view class="money-box">
													<input type="number" :max="Number(refundItem.order_goods_info.num)" v-model="refundApply.refund_array[refundItem.order_goods_info.order_goods_id].refund_stock_num" />
												</view>
											</view>
										</view>
										<view class="row" style="margin-top:8px;">
											<view class="title">完成状态</view>
											<view class="cont">
												<radio-group @change="refundApply.refund_array[refundItem.order_goods_info.order_goods_id].refund_status = $event.detail.value" class="form-radio-group">
													<label class="radio form-radio-item">
														<radio value="PARTIAL_REFUND" :checked="refundApply.refund_array[refundItem.order_goods_info.order_goods_id].refund_status == 'PARTIAL_REFUND'" />
														部分退款状态
													</label>
													<label class="radio form-radio-item">
														<radio value="REFUND_COMPLETE" :checked="refundApply.refund_array[refundItem.order_goods_info.order_goods_id].refund_status == 'REFUND_COMPLETE'" />
														退款完成状态
													</label>
												</radio-group>
											</view>
										</view>
										<view class="row">
											<view class="title"></view>
											<view class="cont tips">
												<view>1、如果是退部分金额，退款后可以是部分退款状态或退款完成状态</view>
												<view>2、如果是退全部金额，则退款后一定是退款完成状态</view>
												<view>3、退款完成才会执行相关业务如核销码失效，卡包失效等操作</view>
											</view>
										</view>
									</view>
								</block>

								<view class="refund-goods-item">
									<view class="row">
										<view class="title">退款说明</view>
										<view class="cont">
											<textarea placeholder="请输入退款说明" v-model="refundApply.refund_remark" />
										</view>
									</view>
								</view>
							</view>
							<view v-show="refundStep == 2">
								<view class="refund-type" :class="{ active: refundApply.refund_transfer_type == index }" @click="refundApply.refund_transfer_type = index" v-for="(item, index) in refundDetail.refund_transfer_type" :key="index">
									<view class="title">{{ item.name }}</view>
									<view class="desc">{{ item.desc }}</view>
								</view>
							</view>
						</block>
					</view>
					<view class="remarks-box">
						<button type="primary" class="default-btn comp-btn remarks" @click="type = 'detail'">取消</button>
						<button type="primary" class="primary-btn comp-btn remarks" v-show="refundStep > 0" @click="refundStep -= 1">上一步</button>
						<button type="primary" class="primary-btn comp-btn remarks" @click="refundNext">{{ refundStep == 2 ? '确认退款' : '下一步' }}</button>
					</view>
				</view>
			</view>

			<!-- 留言 -->
			<unipopup ref="remark" type="center">
				<view class="message">
					<view class="title">
						备注
						<text class="iconfont iconguanbi1" @click="$refs.remark.close()"></text>
					</view>
					<view class="textarea-box">
						<textarea v-model="order_detail.remark" class="textarea" maxlength="200" placeholder="输入请不多于200字"/>
					</view>
					<button @click="saveRemark" type="primary" class="primary-btn btn save">保存</button>
				</view>
			</unipopup>

			<!-- 订单关闭 -->
			<unipopup ref="orderClose" type="center">
				<view class="order-close">
					<view class="title">是否要关闭订单？</view>
					<view class="btn">
						<button type="primary" class="default-btn btn save" @click="orderOperation('close')">取消</button>
						<button type="primary" class="primary-btn btn" @click="orderOperation('save')">确定</button>
					</view>
				</view>
			</unipopup>
			<!-- 门店提货 -->
			<unipopup ref="storeOrderTakeDelivery" type="center">
				<view class="order-close">
					<view class="title">确定要直接提货吗？</view>
					<view class="btn">
						<button type="primary" class="default-btn btn save" @click="$refs.storeOrderTakeDelivery.close()">取消</button>
						<button type="primary" class="primary-btn btn" @click="storeOrderTakeDelivery()">确定</button>
					</view>
				</view>
			</unipopup>

			<unipopup ref="orderLocalDelivery" type="center">
				<view class="order-delivery local">
					<view class="title">订单发货</view>
					<view class="content">
						<view class="content-item">
							<view class="title">收货地址：</view>
							<view class="info">
								<text>{{ order_detail.name }}</text>
								<text>{{ order_detail.mobile }}</text>
								<text>{{ order_detail.full_address }}{{ order_detail.address }}</text>
							</view>
						</view>
						<view class="content-item">
							<view class="title">配送员：</view>
							<view class="info">
								<view class="select">
									<select-lay :zindex="10" :value="localDelivery.deliverer" name="names" placeholder="请选择配送员" :options="deliverer" @selectitem="selectDeliverer"/>
								</view>
							</view>
						</view>
						<view class="content-item">
							<view class="title">配送员手机号：</view>
							<view class="info">
								<input type="text" class="input" v-model="localDelivery.deliverer_mobile" />
							</view>
						</view>
					</view>
					<view class="btn">
						<button type="primary" class="default-btn btn save" @click="$refs.orderLocalDelivery.close()">取消</button>
						<button type="primary" class="primary-btn btn" @click="orderLocalDeliveryFn()">确定</button>
					</view>
				</view>
			</unipopup>

			<unipopup ref="orderDelivery" type="center">
				<view class="order-delivery express">
					<view class="title">订单发货</view>
					<view class="content">
						<view class="content-item">
							<view class="title">收货地址：</view>
							<view class="info">
								<text>{{ order_detail.name }}</text>
								<text>{{ order_detail.mobile }}</text>
								<text>{{ order_detail.full_address }}{{ order_detail.address }}</text>
							</view>
						</view>
						<view class="content-item">
							<view class="title">发货方式：</view>
							<view class="info">
								<radio-group @change="expresDelivery.delivery_type = $event.detail.value" class="form-radio-group">
									<label class="radio form-radio-item">
										<radio value="1" :checked="expresDelivery.delivery_type == 1" />物流发货
									</label>
									<label class="radio form-radio-item">
										<radio value="0" :checked="expresDelivery.delivery_type == 0" />无需物流
									</label>
								</radio-group>
							</view>
						</view>
						<view v-show="expresDelivery.delivery_type == 1">
							<view class="content-item">
								<view class="title">物流公司：</view>
								<view class="info">
									<view class="select">
										<select-lay :zindex="10" :value="expresDelivery.express_company_id" name="names" placeholder="请选择物流公司" :options="expressCompany" @selectitem="selectExpressCompany"/>
									</view>
								</view>
							</view>
							<view class="content-item">
								<view class="title">快递单号：</view>
								<view class="info">
									<input type="text" class="input" v-model="expresDelivery.delivery_no" />
								</view>
							</view>
						</view>
						<view class="content-item">
							<view class="info goods-info common-scrollbar">
								<view class="table">
									<checkbox-group @change="expresDelivery.order_goods_ids = $event.detail.value">
										<view class="table-th table-all">
											<view class="table-td" style="width:5%"></view>
											<view class="table-td" style="width:45%">商品（元）</view>
											<view class="table-td" style="width:10%">数量</view>
											<view class="table-td" style="width:20%;justify-content: flex-end;">物流单号</view>
											<view class="table-td" style="width:20%;justify-content: flex-end;">物流状态</view>
										</view>
										<view class="table-tr table-all" v-for="(item, index) in order_detail.order_goods" :key="index">
											<view class="table-td" style="width:5%">
												<checkbox :value="String(item.order_goods_id)" :disabled="item.delivery_status != 0" />
											</view>
											<view class="table-td" style="width:45%">
												<image v-if="item.sku_image == '@/static/goods/goods.png'" src="@/static/goods/goods.png" mode="widthFix"/>
												<image v-else :src="$util.img(item.sku_image, { size: 'small' })" @error="item.sku_image = '@/static/goods/goods.png'" mode="widthFix"/>
												<view class="content-text">
													<view>{{ item.goods_name }} {{ item.spec_name }}</view>
												</view>
											</view>
											<view class="table-td" style="width:10%">{{ item.num }}</view>
											<view class="table-td uni-column" style="width:20%;align-items: flex-end;">{{ item.delivery_no }}</view>
											<view class="table-td uni-column" style="width:20%;align-items: flex-end;">{{ item.delivery_status_name }}</view>
										</view>
									</checkbox-group>
								</view>
							</view>
						</view>
					</view>
					<view class="btn">
						<button type="primary" class="default-btn btn save" @click="$refs.orderDelivery.close()">取消</button>
						<button type="primary" class="primary-btn btn" @click="orderDelivery()">确定</button>
					</view>
				</view>
			</unipopup>
			<!-- 调整价格 -->
			<unipopup ref="orderAdjustMoney" type="center">
				<view class="order-adjust-money">
					<view class="title">
						<text>调整价格</text>
						<text class="iconfont iconguanbi1" @click="clear"></text>
					</view>
					<view class="tip">注意 : 只有订单未付款时才支持改价,改价后请联系买家刷新订单核实订单金额后再支付。</view>
					<view class="table">
						<view class="table-th table-all">
							<view class="table-td" style="width:10%;padding: 0 0.07rem;">商品信息</view>
							<view class="table-td" style="width:5%;padding: 0 0.07rem;">单价</view>
							<view class="table-td" style="width:5%;padding: 0 0.07rem;">数量</view>
							<view class="table-td" style="width:5%;padding: 0 0.07rem;">小计</view>
							<view class="table-td" style="width:9%;padding: 0 0.07rem;">商品总额</view>
							<view class="table-td" style="width:5%;padding: 0 0.07rem;">优惠</view>
							<view class="table-td" style="width:8%;padding: 0 0.07rem;">优惠券</view>
							<view class="table-td" style="width:8%;padding: 0 0.07rem;">积分抵现</view>
							<view class="table-td" style="width:9%;padding: 0 0.07rem;">发票费用</view>
							<view class="table-td" style="width:10%;padding: 0 0.07rem;">发票邮寄费用</view>
							<view class="table-td" style="width:10%;padding: 0 0.07rem;">调整金额</view>
							<view class="table-td" style="width:10%;padding: 0 0.07rem;">运费</view>
							<view class="table-td" style="width:6%;padding: 0 0.07rem;">总计</view>
						</view>
						<view class="table-tr table-all">
							<view class="table-td" style="width:25%">
								<view class="table-tr table-all" v-for="(item, index) in order_detail.order_goods" :key="index">
									<view class="table-td" style="width:40%;padding: 0 0.07rem;">{{ item.sku_name }} </view>
									<view class="table-td" style="width:20%;padding: 0 0.07rem;">{{ item.price }} </view>
									<view class="table-td" style="width:20%;padding: 0 0.07rem;">{{ item.num }} </view>
									<view class="table-td" style="width:20%;padding: 0 0.07rem;">{{ item.goods_money }} </view>
								</view>
							</view>
							<view class="table-td left" style="width:9%;padding: 0 0.07rem;">{{order_detail.goods_money}}</view>
							<view class="table-td" style="width:5%;padding: 0 0.07rem;">{{ order_detail.promotion_money }}</view>
							<view class="table-td" style="width:8%;padding: 0 0.07rem;">{{ order_detail.coupon_money }}</view>
							<view class="table-td" style="width:8%;padding: 0 0.07rem;">{{ order_detail.point_money }}</view>
							<view class="table-td" style="width:9%;padding: 0 0.07rem;">{{order_detail.invoice_money}}</view>
							<view class="table-td" style="width:10%;padding: 0 0.07rem;">{{order_detail.invoice_delivery_money}}</view>
							<view class="table-td" style="width:10%;padding: 0 0.07rem;"><input type="number" v-model="adjustParams.adjust_money" /></view>
							<view class="table-td" style="width:10%;padding: 0 0.07rem;"><input type="number" min="0" v-model="adjustParams.delivery_money" /></view>
							<view class="table-td" style="width:6%;padding: 0 0.07rem;">{{parseFloat(parseFloat(order_detail.goods_money)-parseFloat(order_detail.promotion_money||0)-parseFloat(order_detail.coupon_money||0) -parseFloat(order_detail.point_money||0)+ parseFloat(adjustParams.adjust_money||0) + parseFloat(adjustParams.delivery_money||0)).toFixed(2)}}</view>
						</view>
					</view>
					<view class="tip m-0"><text class="Highlight">{{'实际商品金额 '}}</text>= 商品总额 - 优惠金额 - 优惠券金额 - 积分抵现 + 调价</view>
					<view class="tip m-0"><text class="Highlight">{{'发票费用 '}}</text>= 实际商品金额 * 发票比率</view>
					<view class="tip m-0">订单总额 =<text class="Highlight">{{' 实际商品金额 '}}</text>+ <text class="Highlight">{{'发票费用 '}}</text>+ 运费 + 发票邮寄费用</view>
					<view class="footer">
						<button type="primary" class="primary-btn btn remarks" @click="adjustSave">确定</button>
						<button class="default-btn comp-btn remarks clear" @click="clear">取消</button>
					</view>
				</view>
			</unipopup>
		</view>
	</base-page>
</template>

<script>
	import nsOrderLog from '@/components/ns-order-log/ns-order-log.vue';
	import nsLoading from '@/components/ns-loading/ns-loading.vue';
	import unipopup from '@/components/uni-popup/uni-popup.vue';
	import orderList from './public/js/order_list.js';

	export default {
		components: {
			nsOrderLog,
			nsLoading,
			unipopup,
		},
		mixins: [orderList]
	};
	/**
	 * 打印回调
	 * @param {Object} text
	 */
	window.POS_PRINT_CALLBACK = function(text) {
		uni.showToast({
			title: text,
			icon: 'none'
		})
	}
</script>

<style scoped lang="scss">
	@import './public/css/orderlist.scss';

	.tab-wrap {
		padding: 0 !important;
		background-color: #fff !important;

		.tab-head {
			display: flex;
			background-color: #f8f8f8;

			text {
				width: 1.15rem;
				height: 0.55rem;
				line-height: 0.55rem;
				text-align: center;
				font-size: $uni-font-size-lg;

				&.active {
					background-color: #fff;
				}
			}
		}
	}

	.item-box {
		padding: 0.1rem;
	}

	.form-radio-item {
		margin-right: .1rem;
		display: inline-flex;
		align-items: center;
	}

	.message {
		width: 5.2rem;
		min-height: 3.2rem;
		border-radius: 0.06rem;
		background: #ffffff;
		padding-bottom: 0.15rem;
		.title {
			width: 100%;
			height: 0.5rem;
			border-bottom: 0.01rem solid #e6e6e6;
			text-align: center;
			line-height: 0.5rem;
			font-size: 0.16rem;
			font-weight: bold;
			position: relative;
			.iconguanbi1 {
				position: absolute;
				top: 50%;
				transform: translateY(-50%);
				right: 0.15rem;
				font-size: 0.18rem;
			}
		}
		.textarea-box {
			margin: 0.15rem;
			height: 2.2rem;
			border: 0.01rem solid #e6e6e6;
			border-radius: 0.06rem;
			padding: 0.15rem;
			box-sizing: border-box;
			.textarea {
				width: 100%;
				height: 100%;
			}
		}
		.save {
			width: auto !important;
			float: right;
			margin-right: 0.15rem;
		}
	}
	.message:after {
		overflow: hidden;
		display: block;
		content: '';
		height: 0;
		clear: both;
	}
</style>
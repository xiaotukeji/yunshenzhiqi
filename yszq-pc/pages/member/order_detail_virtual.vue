<template>
  <div class="box">
    <div class="null-page" v-show="yes"></div>

    <el-card class="box-card order-detail">
      <div slot="header" class="clearfix">
        <el-breadcrumb separator="/">
          <el-breadcrumb-item :to="{ path: '/member/order_list' }">订单列表</el-breadcrumb-item>
          <el-breadcrumb-item>订单详情</el-breadcrumb-item>
        </el-breadcrumb>
      </div>
      <div v-loading="loading">
        <template v-if="orderDetail">
          <div class="order-status">
            <h4 style="position: relative;">
              订单状态：
              <span class="ns-text-color">{{ orderDetail.order_status_name }}</span>
              <div class="edit-time" v-if="orderDetail.order_status == 0">
                <img src="../../assets/images/order_time.png" style="width: 15px;height: 15px;margin-right: 6px;" />距离订单自动关闭，剩余
                <count-down
                  style="color: #f00;margin-left: 10px;"
                  class="count-down"
                  v-on:start_callback="countDownS_cb()"
                  v-on:end_callback="countDownE_cb()"
                  :currentTime="orderDetail.currentTime"
                  :startTime="orderDetail.startTime"
                  :endTime="orderDetail.endTime"
                  :dayTxt="':'"
                  :hourTxt="':'"
                  :minutesTxt="':'"
                  :secondsTxt="''"
                ></count-down>
              </div>
            </h4>
            <div v-if="orderDetail.order_status == 0" class="go-pay">
              <p>
                需付款：
                <span>￥{{ orderDetail.pay_money }}</span>
              </p>
            </div>

            <div class="operation" v-if="orderDetail.action.length > 0">
              <el-button type="primary" size="mini" plain v-if="orderDetail.is_evaluate == 1" @click="operation('memberOrderEvaluation')">
                <template v-if="orderDetail.evaluate_status == 0">评价</template>
                <template v-else-if="orderDetail.evaluate_status == 1">追评</template>
              </el-button>

              <el-button type="primary" size="mini" :plain="operationItem.action == 'orderPay' ? false : true" v-for="(operationItem, operationIndex) in orderDetail.action" :key="operationIndex" @click="operation(operationItem.action)">{{ operationItem.title }}</el-button>
            </div>
            <div class="operation" v-else-if="orderDetail.action.length == 0 && orderDetail.is_evaluate == 1">
              <el-button type="primary" size="mini" plain v-if="orderDetail.is_evaluate == 1" @click="operation('memberOrderEvaluation')">
                <template v-if="orderDetail.evaluate_status == 0">评价</template>
                <template v-else-if="orderDetail.evaluate_status == 1">追评</template>
              </el-button>
            </div>
          </div>

          <div class="verify-code-wrap" v-if="orderDetail.virtual_goods">
            <template v-if="orderDetail.goods_class == 2">
              <h4>核销码</h4>
              <div class="virtual-code">
                <img :src="$img(orderDetail.virtualgoods)" />
                <div class="tips">请将二维码出示给核销员</div>
                <div>核销码：{{ orderDetail.virtual_code }}</div>
              </div>
              <h4>核销信息</h4>
              <ul>
                <li>核销次数：剩余{{ orderDetail.virtual_goods.total_verify_num - orderDetail.virtual_goods.verify_num }}次/共{{ orderDetail.virtual_goods.total_verify_num }}次</li>
                <li>有效期：
                  <span v-if="orderDetail.virtual_goods.expire_time > 0">	{{ $util.timeStampTurnTime(orderDetail.virtual_goods.expire_time) }}</span>
                  <span v-else>永久有效</span>
                </li>
              </ul>
              <template v-if="orderDetail.virtual_goods.verify_record.length">
                <h4>核销记录</h4>
                <ul v-for="(item, index) in orderDetail.virtual_goods.verify_record" :key="index">
                  <li>核销人：{{ item.verifier_name }}</li>
                  <li>核销时间：{{ $util.timeStampTurnTime(item.verify_time) }}</li>
                </ul>
              </template>
            </template>
            <template v-if="orderDetail.goods_class == 3">
              <h4>卡密信息</h4>
              <ul v-for="(item, index) in orderDetail.virtual_goods" :key="index">
                <li>
                  <span>卡号：{{ item.card_info.cardno }}</span>
                </li>
                <li>
                  <span>密码：{{ item.card_info.password }}</span>
                </li>
              </ul>
            </template>
            <!-- <h4>核销信息</h4>
						<ul>
							<li>
								核销码：
								<span class="ns-text-color">{{ orderDetail.virtual_code }}</span>
							</li>
							<template v-if="orderDetail.virtual_goods.is_veirfy">
								<li>核销状态：已核销</li>
								<li>核销时间：{{ $util.timeStampTurnTime(orderDetail.virtual_goods.verify_time) }}</li>
							</template>
						</ul>
						<img :src="$img(orderDetail.virtualgoods)" /> -->
          </div>

          <div class="order-info">
            <h4>订单信息</h4>
            <ul>
              <!-- <li>
								<i class="iconfont iconmendian"></i>
								店铺：
								<router-link :to="'/shop-' + orderDetail.site_id" target="_blank">{{ orderDetail.site_name }}</router-link>
							</li> -->
              <li>订单类型：{{ orderDetail.order_type_name }}</li>
              <li>订单编号：{{ orderDetail.order_no }}</li>
              <li>订单交易号：{{ orderDetail.out_trade_no }}</li>
              <li>创建时间：{{ $util.timeStampTurnTime(orderDetail.create_time) }}</li>
              <li v-if="orderDetail.close_time > 0">关闭时间：{{ $util.timeStampTurnTime(orderDetail.close_time) }}</li>
              <template v-if="orderDetail.pay_status > 0">
                <li>支付方式：{{ orderDetail.pay_type_name }}</li>
                <li>支付时间：{{ $util.timeStampTurnTime(orderDetail.pay_time) }}</li>
              </template>
              <li v-if="orderDetail.promotion_type_name != ''">店铺活动：{{ orderDetail.promotion_type_name }}</li>
              <li v-if="orderDetail.buyer_message != ''">买家留言：{{ orderDetail.buyer_message }}</li>
            </ul>
          </div>
          <div class="order-info" v-if="orderDetail.pay_type=='offlinepay'&&orderDetail.offline_pay_info">
            <h4>线下支付</h4>
            <ul>
              <li v-if="orderDetail.offline_pay_info.status_info.const=='WAIT_AUDIT'">
                支付状态：审核中
              </li>
              <li v-if="orderDetail.offline_pay_info.status_info.const=='AUDIT_REFUSE'">
                支付状态：审核被拒
              </li>
              <li v-if="orderDetail.offline_pay_info.status_info.const=='AUDIT_REFUSE'">
                审核备注：{{orderDetail.offline_pay_info.audit_remark}}
              </li>
            </ul>
          </div>
          <!-- 发票信息 -->
          <div class="take-delivery-info" v-if="orderDetail.is_invoice ==1">
            <h4>发票信息</h4>
            <ul>
              <li>发票类型：{{ orderDetail.invoice_type == 1 ? '纸质发票' : '电子发票' }}</li>
              <li>发票抬头类型：{{ orderDetail.invoice_title_type ==1 ? '个人' : '企业' }}</li>
              <li>发票抬头：{{ orderDetail.invoice_title }}</li>
              <li>发票内容：{{ orderDetail.invoice_content }}</li>
              <li v-if="orderDetail.invoice_type == 1">发票邮寄地址地址：{{ orderDetail.invoice_full_address }}</li>
              <li v-else>发票接收邮箱：{{ orderDetail.invoice_email }}</li>
            </ul>
          </div>

          <nav>
            <li :class="{ 'no-operation': !orderDetail.is_enable_refund }">商品信息</li>
            <li>单价</li>
            <li>数量</li>
            <li>小计</li>
            <li v-if="orderDetail.is_enable_refund">操作</li>
          </nav>

          <!-- 订单项·商品 -->
          <div class="list">
            <ul class="item" v-for="(goodsItem, goodsIndex) in orderDetail.order_goods" :key="goodsIndex">
              <li :class="{ 'no-operation': !orderDetail.is_enable_refund }">
                <div class="img-wrap" @click="$util.pushToTab('/sku/' + goodsItem.sku_id)">
                  <img :src="$img(goodsItem.sku_image, { size: 'mid' })" @error="imageError(goodsIndex)" />
                </div>
                <div class="info-wrap">
                  <h5 @click="$util.pushToTab('/sku/' + goodsItem.sku_id)">{{ goodsItem.sku_name }}</h5>
                  <!-- <span>规格：规格值</span> -->
                </div>
              </li>
              <li>
                <span>￥{{ goodsItem.price }}</span>
              </li>
              <li>
                <span>{{ goodsItem.num }}</span>
              </li>
              <li>
                <span>￥{{ (goodsItem.price * goodsItem.num).toFixed(2) }}</span>
              </li>
              <li v-if="orderDetail.is_enable_refund">
                <el-button type="primary" plain size="mini" v-if="goodsItem.refund_status == 0 || goodsItem.refund_status == -1" @click="$router.push({ path: '/order/refund', query: { order_goods_id: goodsItem.order_goods_id } })">退款</el-button>
                <el-button type="primary" plain size="mini" v-else @click="$router.push({ path: '/order/refund_detail', query: { order_goods_id: goodsItem.order_goods_id } })">查看退款</el-button>
              </li>
            </ul>
          </div>

          <!-- 订单总计 -->
          <ul class="total">
            <li>
              <label>商品金额：</label>
              <span>￥{{ orderDetail.goods_money }}</span>
            </li>
            <li v-if="orderDetail.member_card_money > 0">
              <label>会员卡：</label>
              <span>￥{{ orderDetail.member_card_money }}</span>
            </li>
            <li v-if="orderDetail.invoice_money > 0">
              <label>税费：</label>
              <span>￥{{ orderDetail.invoice_money }}</span>
            </li>
            <li v-if="orderDetail.invoice_delivery_money > 0">
              <label>发票邮寄费：</label>
              <span>￥{{ orderDetail.invoice_delivery_money }}</span>
            </li>
            <li v-if="orderDetail.adjust_money != 0">
              <label>订单调整：</label>
              <span>
								<template v-if="orderDetail.adjust_money < 0">-</template>
								<template v-else>+</template>
								￥{{ orderDetail.adjust_money | abs }}
							</span>
            </li>
            <li v-if="orderDetail.promotion_money > 0">
              <label>优惠：</label>
              <span>￥{{ orderDetail.promotion_money }}</span>
            </li>
            <li v-if="orderDetail.coupon_money > 0">
              <label>优惠券：</label>
              <span>￥{{ orderDetail.coupon_money }}</span>
            </li>
            <li v-if="orderDetail.point_money > 0">
              <label>积分抵扣：</label>
              <span>￥{{ orderDetail.point_money }}</span>
            </li>
            <li v-if="orderDetail.balance_money > 0">
              <label>使用余额：</label>
              <span>￥{{ orderDetail.balance_money }}</span>
            </li>
            <li class="pay-money">
              <label>实付款：</label>
              <span>￥{{ orderDetail.pay_money }}</span>
            </li>
          </ul>
        </template>
      </div>
    </el-card>
  </div>
</template>

<script>
  import {apiOrderDetail} from '@/api/order/order';
  import orderMethod from '@/utils/orderMethod';
  import {mapGetters} from 'vuex';
  import CountDown from "vue2-countdown"

  export default {
    name: 'order_detail_virtual',
    components: {
      CountDown
    },
    mixins: [orderMethod],
    data: () => {
      return {
        orderId: 0,
        orderDetail: null,
        loading: true,
        yes: true
      };
    },
    created() {
      this.orderId = this.$route.query.order_id;
      this.getOrderDetail();
    },
    mounted() {
      let self = this;
      setTimeout(function () {
        self.yes = false
      }, 300)
    },
    computed: {
      ...mapGetters(['token', 'defaultGoodsImage'])
    },
    layout: 'member',
    methods: {
      countDownS_cb() {
      },
      countDownE_cb() {
      },
      getOrderDetail() {
        apiOrderDetail({
          order_id: this.orderId
        }).then(res => {
          if (res.code >= 0) {
            let date = (Date.parse(new Date())) / 1000;
            res.data.currentTime = date
            res.data.startTime = date
            res.data.endTime = res.data.create_time + res.data.auto_close
            this.orderDetail = res.data;
            if (this.orderDetail.delivery_store_info != '') this.orderDetail.delivery_store_info = JSON.parse(this.orderDetail.delivery_store_info);
            this.loading = false;
          } else {
            this.$message({
              message: '未获取到订单信息',
              type: 'warning',
              duration: 2000,
              onClose: () => {
                this.$router.push({path: '/member/order_list'});
              }
            });
          }
        }).catch(err => {
          this.loading = false;
          this.$message.error({
            message: err.message,
            duration: 2000,
            onClose: () => {
              this.$router.push({path: '/member/order_list'});
            }
          });
        });
      },
      operation(action) {
        switch (action) {
          case 'orderPay': // 支付
            this.orderPay(this.orderDetail);
            break;
          case 'orderClose': //关闭
            this.orderClose(this.orderDetail.order_id, () => {
              this.getOrderDetail();
            });
            break;
          case 'memberOrderEvaluation': //评价
            this.$util.pushToTab({path: '/order/evaluate', query: {order_id: this.orderDetail.order_id}});
            break;
            case 'orderOfflinePay': //线下支付
            this.$router.push({
              path: '/pay',
              query: {
                code: this.orderDetail.offline_pay_info.out_trade_no
              }
            });
            break;
        }
      },
      imageError(index) {
        this.orderDetail.order_goods[index].sku_image = this.defaultGoodsImage;
      }
    },
    filters: {
      abs(value) {
        return Math.abs(parseFloat(value)).toFixed(2);
      }
    }
  };
</script>
<style lang="scss" scoped>
  .box {
    width: 100%;
    position: relative;
  }

  .null-page {
    width: 100%;
    height: 730px;
    background-color: #FFFFFF;
    position: absolute;
    top: 0;
    left: 0;
    z-index: 9;
  }

  .order-detail {
    .order-status {
      background-color: #fff;
      margin-bottom: 20px;

      h4 {
        margin: 10px 0 20px;
        border-bottom: 1px solid #eeeeee;
        padding-bottom: 10px;

        .edit-time {
          position: absolute;
          left: 160px;
          top: 2px;
          display: flex;
          align-items: center;
          font-size: 10px;
        }
      }

      .go-pay {
        p {
          display: inline-block;
          vertical-align: middle;

          span {
            font-weight: bold;
            color: $base-color;
            font-size: 18px;
          }
        }
      }

      .operation {
        margin-top: 10px;
      }
    }

    .order-info {
      background-color: #fff;
      margin-bottom: 10px;

      h4 {
        margin: 10px 0 20px;
        border-bottom: 1px solid #eeeeee;
        padding-bottom: 10px;
      }

      ul {
        display: flex;
        flex-wrap: wrap;

        li {
          flex: 0 0 33.3333%;
          margin-bottom: 10px;

          &:last-child {
            flex: initial;
          }
        }
      }
    }

    .verify-code-wrap {
      background-color: #fff;
      margin-bottom: 10px;

      h4 {
        margin: 10px 0 20px;
        border-bottom: 1px solid #eeeeee;
        padding-bottom: 10px;
      }

      ul {
        display: flex;
        flex-wrap: wrap;

        li {
          flex: 0 0 33.3333%;
          margin-bottom: 10px;
        }
      }

      img {
        width: 100px;
      }

      .virtual-code {
        text-align: center;

        .tips {
          color: #999;
          font-size: 12px;
          margin-top: 5px;
        }

      }
    }

    nav {
      overflow: hidden;
      padding: 10px 0;
      background: #fff;
      border-bottom: 1px solid #eeeeee;

      li {
        float: left;

        &:nth-child(1) {
          width: 50%;

          &.no-operation {
            width: 60%;
          }
        }

        &:nth-child(2) {
          width: 15%;
        }

        &:nth-child(3) {
          width: 10%;
        }

        &:nth-child(4) {
          width: 15%;
        }

        &:nth-child(5) {
          width: 10%;
        }
      }
    }

    .list {
      border-bottom: 1px solid #eeeeee;

      .item {
        background-color: #fff;
        padding: 10px 0;
        overflow: hidden;

        li {
          float: left;
          line-height: 60px;

          &:nth-child(1) {
            width: 50%;
            line-height: inherit;

            &.no-operation {
              width: 60%;
            }

            .img-wrap {
              width: 60px;
              height: 60px;
              float: left;
              margin-right: 10px;
              cursor: pointer;
            }

            .info-wrap {
              margin-left: 70px;

              h5 {
                font-weight: normal;
                font-size: $ns-font-size-base;
                display: -webkit-box;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 2;
                overflow: hidden;
                margin-right: 10px;
                display: inline-block;
                cursor: pointer;

                &:hover {
                  color: $base-color;
                }
              }

              span {
                font-size: $ns-font-size-sm;
                color: #9a9a9a;
              }
            }
          }

          &:nth-child(2) {
            width: 15%;
          }

          &:nth-child(3) {
            width: 10%;
          }

          &:nth-child(4) {
            width: 15%;
          }

          &:nth-child(5) {
            width: 10%;
          }
        }
      }
    }

    // 总计
    .total {
      padding: 20px;
      background-color: #fff;
      text-align: right;

      li {
        span {
          width: 150px;
          display: inline-block;
        }

        &.pay-money {
          font-weight: bold;

          span {
            color: $base-color;
            font-size: 16px;
            vertical-align: middle;
          }
        }
      }
    }
  }

  .take-delivery-info {
    background-color: #fff;
    margin-bottom: 10px;

    h4 {
      margin: 10px 0 20px;
      border-bottom: 1px solid #eeeeee;
      padding-bottom: 10px;
    }

    ul {
      display: flex;
      flex-wrap: wrap;

      li {
        flex: 0 0 33.3333%;
        margin-bottom: 10px;

        &:last-child {
          flex: initial;
        }
      }
    }
  }
</style>

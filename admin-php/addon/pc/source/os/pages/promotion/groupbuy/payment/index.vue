<template>
  <div class="payment-wrap" v-loading="fullscreenLoading">

    <!--购买虚拟类商品需填写您的手机号-->
    <div class="item-block" v-if="orderPaymentData.is_virtual == 1">
      <div class="block-text">购买虚拟类商品需填写您的手机号，以方便商家与您联系</div>

      <el-form ref="form" size="mini" class="mobile-wrap" label-width="80px">
        <el-form-item label="手机号码">
          <el-input placeholder="请输入您的手机号码" maxlength="11" v-model="orderCreateData.member_address.mobile" />
        </el-form-item>
      </el-form>
    </div>

    <!--收货地址-->
    <div class="item-block" v-if="orderPaymentData.is_virtual == 0">
      <div class="block-text">收货地址</div>

      <div class="address-list">
        <div class="address-item" @click="addAddressShow">
          <div class="add-address">
            <i class="el-icon-circle-plus-outline"></i>
            添加收货地址
          </div>
        </div>

        <div class="address-item" v-for="(item, key) in memberAddress" :key="item.id" :class="addressId == item.id ? 'active' : ''" v-if="key < 3 || (addressShow && key >= 3)">
          <div class="address-info">
            <div class="options">
              <div @click="editAddress(item.id)">编辑</div>
              <template v-if="item.is_default == 0">
                <el-popconfirm title="确定要删除该地址吗？" @onConfirm="deleteMemberAddress(item.id)">
                  <div slot="reference">删除</div>
                </el-popconfirm>
              </template>
            </div>
            <div class="address-name">{{ item.name }}</div>
            <div class="address-mobile" @click="setMemberAddress(item.id)">{{ item.mobile }}</div>
            <div class="address-desc" @click="setMemberAddress(item.id)">{{ item.full_address }} {{ item.address }}
            </div>
          </div>
        </div>

        <div v-if="memberAddress.length > 3 && !addressShow" @click="addressShow = true" class="el-button--text address-open">
          <i class="el-icon-arrow-down"></i>
          更多收货地址
        </div>

        <div class="clear"></div>
      </div>
    </div>

    <!--收货地址添加-->
    <el-dialog :title="addressForm.id == 0 ? '添加收货地址' : '编辑收货地址'" :visible.sync="dialogVisible" width="32%">
      <el-form ref="form" :rules="addressRules" :model="addressForm" label-width="80px">
        <el-form-item label="姓名" prop="name">
          <el-input v-model="addressForm.name" placeholder="收货人姓名" />
        </el-form-item>

        <el-form-item label="手机" prop="mobile">
          <el-input v-model="addressForm.mobile" maxlength="11" placeholder="收货人手机号" />
        </el-form-item>

        <el-form-item label="电话">
          <el-input v-model="addressForm.telephone" placeholder="收货人固定电话（选填）" />
        </el-form-item>

        <el-form-item class="area" label="地区" prop="area">
          <el-row :gutter="10">
            <el-col :span="7">
              <el-select prop="province" ref="province" v-model="addressForm.province_id" @change="getAddress(1)" placeholder="请选择省">
                <el-option label="请选择省" value="0"></el-option>
                <el-option v-for="item in pickerValueArray" :key="item.id" :label="item.name" :value="item.id"></el-option>
              </el-select>
            </el-col>
            <el-col :span="7">
              <el-select ref="city" prop="city" v-model="addressForm.city_id" @change="getAddress(2)" placeholder="请选择市">
                <el-option label="请选择市" value="0"></el-option>
                <el-option v-for="item in cityArr" :key="item.id" :label="item.name" :value="item.id"></el-option>
              </el-select>
            </el-col>
            <el-col :span="7">
              <el-select ref="district" prop="district" v-model="addressForm.district_id" placeholder="请选择区/县">
                <el-option label="请选择区/县" value="0"></el-option>
                <el-option v-for="item in districtArr" :key="item.id" :label="item.name" :value="item.id"></el-option>
              </el-select>
            </el-col>
          </el-row>
        </el-form-item>

        <el-form-item label="详细地址" prop="address">
          <el-input v-model="addressForm.address" placeholder="定位小区、街道、写字楼" />
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false">取 消</el-button>
        <el-button type="primary" @click="addmemberAddress('form')">确 定</el-button>
      </span>
    </el-dialog>

    <!--使用余额-->
    <div class="item-block" v-if="orderPaymentData.member_account.balance_total > 0 && balance_show == 1">
      <div class="block-text">是否使用余额</div>
      <div class="pay-type-list">
        <div class="pay-type-item" :class="orderCreateData.is_balance ? '' : 'active'" @click="useBalance(0)">不使用余额</div>
        <div class="pay-type-item" :class="orderCreateData.is_balance ? 'active' : ''" @click="useBalance(1)">使用余额</div>
        <div class="clear"></div>
      </div>
    </div>

    <!-- 支付密码 -->
    <el-dialog title="使用余额" :visible.sync="dialogpay" width="350px">
      <template v-if="orderPaymentData.member_account.is_pay_password == 0">
        <p>为了您的账户安全,请您先设置的支付密码</p>
        <p>可到"会员中心","账号安全","支付密码"中设置</p>
        <span slot="footer" class="dialog-footer">
          <el-button size="small" @click="dialogpay = false">暂不设置</el-button>
          <el-button size="small" type="primary" @click="setPayPassword">立即设置</el-button>
        </span>
      </template>
      <el-form v-else status-icon ref="ruleForm" label-width="100px">
        <el-form-item label="支付密码" class="pay-password-item">
          <!--添加一个不可见的input,欺骗浏览器自动填充-->
          <el-input type="password" class="pay-password hide-password" :maxlength="6"></el-input>
          <el-input type="password" class="pay-password" :maxlength="6" v-model="password" @input="input"></el-input>
        </el-form-item>
        <p class="ns-text-color forget-password" @click="setPayPassword">忘记密码</p>
      </el-form>
    </el-dialog>

    <!-- 配送方式 -->
    <div class="item-block padd-bom-20" v-if="orderPaymentData.delivery.express_type.length > 0">
      <div class="block-text">
        <span>配送方式</span>
        <span class="distribution" v-if="orderCreateData.delivery.delivery_type == 'store'">{{ orderCreateData.delivery.store_name }}</span>
      </div>
      <div class="pay-type-item" v-for="(item, index) in orderPaymentData.delivery.express_type" :key="index" @click="selectDeliveryType(item)" :class="item.name == orderCreateData.delivery.delivery_type ? 'active' : ''">
        {{ item.title }}
      </div>
    </div>

    <!--配送方式  门店 -->
    <el-dialog title="选择门店" :visible.sync="dialogStore" width="50%">
      <el-table ref="singleTable" :data="storeList" highlight-current-row @row-click="selectStore" class="cursor-pointer">
        <el-table-column label="" width="55">
          <template slot-scope="scope">
            <el-radio v-model="storeRadio" :label="scope.row"><i></i></el-radio>
          </template>
        </el-table-column>
        <el-table-column prop="store_name" label="名称" width="160"></el-table-column>
        <el-table-column prop="store_address" label="地址"></el-table-column>
        <el-table-column prop="open_date" label="营业时间"></el-table-column>
      </el-table>
    </el-dialog>

    <div class="item-block" v-if="orderPaymentData.invoice && orderPaymentData.invoice.invoice_status == 1">
      <div class="block-text">发票信息</div>
      <div class="pay-type-list">
        <div class="pay-type-item" :class="orderCreateData.is_invoice == 0 ? 'active' : ''" @click="changeIsInvoice">无需发票</div>
        <div class="pay-type-item" :class="orderCreateData.is_invoice == 1 ? 'active' : ''" @click="changeIsInvoice">需要发票</div>
        <div class="clear"></div>
      </div>
      <div class="invoice-information" v-if="orderCreateData.is_invoice == 1">
        <div class="invoice-title">
          <div class="invoice-type-box invoice-title-box">
            <span class="invoice-name">发票类型：</span>
            <label class="invoice-to-type">
              <i class="invoice-i-input" :class="orderCreateData.invoice_type == 1 ? 'active' : ''" @click="clickType(1)"></i>
              <span>纸质</span>
            </label>
            <label class="invoice-to-type">
              <i class="invoice-i-input" :class="orderCreateData.invoice_type == 2 ? 'active' : ''" @click="clickType(2)"></i>
              <span>电子</span>
            </label>
          </div>
          <div class="invoice-type-box invoice-title-box">
            <span class="invoice-name">抬头类型：</span>
            <label class="invoice-to-type">
              <i class="invoice-i-input" :class="orderCreateData.invoice_title_type == 1 ? 'active' : ''" @click="clickTitleType(1)"></i>
              <span>个人</span>
            </label>
            <label class="invoice-to-type">
              <i class="invoice-i-input" :class="orderCreateData.invoice_title_type == 2 ? 'active' : ''" @click="clickTitleType(2)"></i>
              <span>企业</span>
            </label>
          </div>
        </div>
        <div class="invoice-type-box">
          <span class="invoice-name">发票信息：</span>
          <div class="invoice-box-form">
            <input type="text" placeholder="请填写抬头名称" v-model.trim="orderCreateData.invoice_title" />
            <input type="text" placeholder="请填写纳税人识别号" v-model.trim="orderCreateData.taxpayer_number" v-if="orderCreateData.invoice_title_type == 2" />
            <input type="text" placeholder="请填写邮寄地址" v-model.trim="orderCreateData.invoice_full_address" v-show="orderCreateData.invoice_type == 1" />
            <input type="text" placeholder="请填写邮箱" v-model.trim="orderCreateData.invoice_email" v-show="orderCreateData.invoice_type == 2" />
          </div>
        </div>
        <div class="invoice-condition">
          <span class="invoice-name">发票内容：</span>
          <div class="invoice-box-form">
            <span class="option-item" :key="index" v-for="(item, index) in orderPaymentData.invoice.invoice_content_array" @click="changeInvoiceContent(item)" :class="{ 'color-base-bg active': item == orderCreateData.invoice_content }">
              {{ item }}
            </span>
          </div>
        </div>
        <div class="invoice-tops">发票内容将以根据税法调整，具体请以展示为准，发票内容显示详细商品名 称及价格信息</div>
      </div>
    </div>

    <!--商品信息-->
    <div class="item-block">
      <div class="goods-list">
        <table>
          <tr>
            <td width="50%">商品</td>
            <td width="12.5%">价格</td>
            <td width="12.5%">数量</td>
            <td width="12.5%">小计</td>
          </tr>
        </table>
      </div>
    </div>
    <div>
      <div class="item-block" v-if="calculateData">
        <div class="goods-list">
          <table>
            <tr v-for="(goodsItem, goodsIndex) in calculateData.goods_list" :key="goodsIndex">
              <td width="50%">
                <div class="goods-info">
                  <div class="goods-info-left">
                    <router-link :to="{ path: '/sku/' + goodsItem.sku_id }" target="_blank">
                      <img class="goods-img" :src="$img(goodsItem.sku_image, { size: 'mid' })" @error="imageError(goodsIndex)" />
                    </router-link>
                  </div>
                  <div class="goods-info-right">
                    <router-link :to="{ path: '/sku/' + goodsItem.sku_id }" target="_blank">
                      <div class="goods-name">{{ goodsItem.goods_name }}</div>
                    </router-link>
                    <!-- 规格 -->
                    <div class="goods-spec" v-if="goodsItem.sku_spec_format">
                      <span v-for="(x, i) in goodsItem.sku_spec_format" :key="i">{{ x.spec_value_name }}</span>
                    </div>
                  </div>
                </div>
              </td>
              <td width="12.5%" class="goods-price">￥{{ goodsItem.price }}</td>
              <td width="12.5%" class="goods-num">{{ goodsItem.num }}</td>
              <td width="12.5%" class="goods-money">￥{{ (goodsItem.price * goodsItem.num).toFixed(2) }}</td>
            </tr>
          </table>
        </div>
      </div>
    </div>

    <!-- 活动优惠 -->
    <div class="item-block" v-if="promotionInfo">
      <div class="block-text">{{ promotionInfo.title }}</div>
      <div class="order-cell platform-coupon">
        <div class="box ns-text-color" v-html="promotionInfo.content"></div>
      </div>
    </div>

    <!-- 买家留言 -->
    <div class="item-block padd-bom-10">
      <div class="block-text">买家留言：</div>
      <el-input rows="3" type="textarea" placeholder="留言前建议先与商家协调一致" v-model="orderCreateData.buyer_message" class="buyer-message" @input="textarea" maxlength="140" show-word-limit resize="none" />
    </div>

    <!-- 总计 -->
    <div class="item-block" v-if="calculateData">
      <div class="order-statistics">
        <table>
          <tr>
            <td align="right">商品金额：</td>
            <td align="left">￥{{ calculateData.goods_money | moneyFormat }}</td>
          </tr>
          <tr v-if="calculateData.is_virtual == 0 && calculateData.delivery_money > 0">
            <td align="right">运费：</td>
            <td align="left">￥{{ calculateData.delivery_money | moneyFormat }}</td>
          </tr>
          <tr v-if="calculateData.invoice_money > 0">
            <td align="right">发票税费<span class="ns-text-color">({{ calculateData.invoice.invoice_rate }}%)</span>：</td>
            <td align="left">￥{{ calculateData.invoice_money | moneyFormat }}</td>
          </tr>
          <tr v-if="calculateData.invoice_delivery_money > 0">
            <td align="right">发票邮寄费：</td>
            <td align="left">￥{{ calculateData.invoice_delivery_money | moneyFormat }}</td>
          </tr>
          <tr v-if="calculateData.promotion_money > 0">
            <td align="right">优惠：</td>
            <td align="left">￥{{ calculateData.promotion_money | moneyFormat }}</td>
          </tr>
          <tr v-if="calculateData.balance_money > 0">
            <td align="right">使用余额：</td>
            <td align="left">￥{{ calculateData.balance_money | moneyFormat }}</td>
          </tr>
        </table>
      </div>
      <div class="clear"></div>
    </div>

    <!--结算-->
    <div class="item-block" v-if="calculateData">
      <div class="order-submit">
        <div class="order-money">
          共{{ calculateData.goods_num }}件，应付金额：
          <div class="ns-text-color">￥{{ calculateData.pay_money | moneyFormat }}</div>
        </div>
        <el-button type="primary" class="el-button--primary" @click="orderCreate">订单结算</el-button>
      </div>
      <div class="clear"></div>
    </div>
  </div>
</template>

<script>
  import detail from '@/assets/js/promotion/payment_groupbuy.js';

  export default {
    name: 'groupbuy_payment',
    mixins: [detail]
  };
</script>

<style lang="scss" scoped>
  @import '@/assets/css/promotion/payment_groupbuy.scss';
</style>

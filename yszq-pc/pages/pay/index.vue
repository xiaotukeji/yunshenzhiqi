<template>
  <div class="pay-wrap" v-loading="loading">
    <div class="item-block">
      <div class="payment-detail">
        <div class="payment-media">
          <el-row>
            <el-col :span="4">
              <div class="media-left"><i class="el-icon-circle-check ns-text-color"></i></div>
            </el-col>

            <el-col :span="16">
              <div class="media-body">
                <el-row>
                  <el-col :span="12">
                    <div class="payment-text">您的订单已提交成功，正在等待处理！</div>
                    <div>
                      <span>应付金额：</span>
                      <span class="payment-money ns-text-color">￥{{ payInfo.pay_money }}元</span>
                    </div>
                  </el-col>
                  <el-col :span="12"></el-col>
                </el-row>
              </div>
            </el-col>

            <el-col :span="4">
              <div class="media-right">
                <div class="el-button--text" @click="orderOpen ? (orderOpen = false) : (orderOpen = true)">
                  订单信息
                  <i :class="orderOpen ? 'rotate' : ''" class="el-icon-arrow-down"></i>
                </div>
              </div>
            </el-col>
          </el-row>
        </div>

        <div class="order-info" v-if="orderOpen">
          <el-row>
            <el-col :span="4" class="order-info-left"></el-col>
            <el-col :span="20">
              <div class="line"></div>
              <div class="order-item">
                <div class="item-label">交易单号：</div>
                <div class="item-value">{{ payInfo.out_trade_no }}</div>
              </div>
              <div class="order-item">
                <div class="item-label">订单内容：</div>
                <div class="item-value">{{ payInfo.pay_detail }}</div>
              </div>
              <div class="order-item">
                <div class="item-label">订单金额：</div>
                <div class="item-value">￥{{ payInfo.pay_money }}</div>
              </div>
              <div class="order-item">
                <div class="item-label">创建时间：</div>
                <div class="item-value">{{ $timeStampTurnTime(payInfo.create_time) }}</div>
              </div>
            </el-col>
          </el-row>
        </div>
      </div>
    </div>

    <div class="item-block">
      <div class="block-text">支付方式</div>
      <div class="pay-type-list" v-if="payTypeList.length">
        <template v-for="(item, index) in payTypeList">
          <div class="pay-type-item" :key="index" v-if="item.type!='offlinepay'|| (item.type=='offlinepay'&&payInfo.event=='OrderPayNotify')" :class="{'active' : payIndex == index,'disable':type=='edit'&&item.type!='offlinepay'}" @click="payTypeChange(index)">
            {{ item.name }}
          </div>
        </template>

        <div class="clear"></div>
      </div>
      <div class="no-pay-type" v-else>
        <p>商家未配置支付方式</p>
      </div>
      <div class="offlinepay" v-if="payTypeList[payIndex].type==='offlinepay'">
        <template  v-for="item in offlinepayConfig">
          <div class="offlinepay-item bank" v-if="item.key=='bank'" :key="item.key">
            <div class="title">
            银行卡
            <!-- {{item.key=='bank'?'银行卡':item.key=='wechat'?'微信支付':'支付宝支付'}} -->
          </div>
            <div class="item">
              <div class="item-label">银行名称：</div>
              <div class="item-value">{{ item.bank_name }}</div>
            </div>
            <div class="item">
              <div class="item-label">账号名称：</div>
              <div class="item-value">{{ item.account_name }}</div>
            </div>
            <div class="item">
              <div class="item-label">银行账号：</div>
              <div class="item-value">
                <span>{{ item.account_number }}</span>
                <span class="copy ns-text-color" @click="copy(item.account_number)">复制</span>
              </div>
            </div>
            <div class="item">
              <div class="item-label">开户支行：</div>
              <div class="item-value">{{ item.branch_name }}</div>
            </div>
          </div>
          <div class="offlinepay-item" v-else>
            <div class="title">
              {{item.key=='wechat'?'微信支付':'支付宝支付'}}
            </div>
            <div class="code">
              <div class="centent">
                <el-image :src="$util.img(item.payment_code)" :key="item.key"  fit="cover" class="qrcode" />
              </div>
              <div class="bottom">
                {{ item.account_name }}
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>
    <div class="item-block " v-if="payTypeList[payIndex].type==='offlinepay'">
      <div class="offlinepay-form">
          <div class="upload-wrap-title">
            <div class="title">上传支付凭证</div>
            <div class="title-tips">最多还可上传{{5 - offlinepayInfo.imgList.length }}张</div>
          </div>
          <div class="upload-wrap">
            <el-upload ref="upload" :class="{ 'ishide': hide}" :action="uploadActionUrl"
            :file-list="imgList"
                      list-type="picture-card" :on-success="
              (file, fileList) => {
                return handleSuccess(file, fileList);
              }
            " :on-preview="handlePictureCardPreview" :on-remove="
              (file, fileList) => {
                return handleRemove(file, fileList);
              }
            " :on-exceed="handleExceed" multiple drag :limit="5">
              <i class="el-icon-plus"></i>
            </el-upload>
            <el-dialog :visible.sync="imgDialogVisible"><img width="100%" :src="dialogImageUrl" alt="" /></el-dialog>
          </div>
          <div class="bottom">
            <el-input v-model="offlinepayInfo.desc" placeholder="请详细说明您的支付情况" type="textarea" class="remark" :maxlength="200"/>
          </div>

      </div>
    </div>
    <div class="item-block" v-if="payTypeList.length">
      <div class="order-submit">
        <el-button type="primary" class="el-button--primary" @click="pay">立即支付</el-button>
      </div>
      <div class="clear"></div>
    </div>

    <el-dialog title="请确认支付是否完成" :visible.sync="dialogVisible" width="23%" top="30vh" class="confirm-pay-wrap">
      <div class="info-wrap">
        <i class="el-message-box__status el-icon-warning"></i>
        <span>完成支付前请根据您的情况点击下面的按钮</span>
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button @click="goIndex" size="small">返回首页</el-button>
        <el-button type="primary" @click="goOrderList" size="small">已完成支付</el-button>
      </span>
    </el-dialog>

    <!-- 微信支付弹框 -->
    <el-dialog title="请用微信扫码支付" :visible.sync="openQrcode" width="300px" center>
      <div class="wechatpay-box"><img :src="payQrcode" /></div>
    </el-dialog>
  </div>
</template>

<script>
  import {
    getPayInfo,
    getPayType,
    checkPayStatus,
    pay,
    getOfflinepayConfig,
    offlinepay,
    getOfflinepayPayInfo
  } from '@/api/pay';
  import Config from '@/plugins/config';

  export default {
    name: 'pay',
    components: {},
    data: () => {
      return {
        orderOpen: false,
        outTradeNo: '',
        payInfo: {
          pay_money: 0
        },
        payIndex: 0,
        payTypeList: [{
          name: '支付宝支付',
          icon: 'iconzhifubaozhifu-',
          type: 'alipay'
        },
          {
            name: '微信支付',
            icon: 'iconweixinzhifu',
            type: 'wechatpay'
          },
          {
            name: '线下支付',
            icon: '',
            type: 'offlinepay'
          }
        ],
        payUrl: '',
        timer: null,
        payQrcode: '',
        openQrcode: false,
        loading: true,
        test: null,
        dialogVisible: false,
        offlinepayConfig: [],
        offlinepayInfo:{
					out_trade_no:'',
					imgs:'',
					imgList:[],
					desc:'',
				},
        type:'add',
        dialogImageUrl:'',
        imgDialogVisible:false,
        uploadActionUrl: Config.baseUrl + '/offlinepay/api/pay/uploadimg',
        hide:false,
        imgList:[],
        allRequestSuccess: 0,
      };
    },
    created() {
      if (!this.$route.query.code) {
        this.$router.push({
          path: '/'
        });
        return;
      }
      this.outTradeNo = this.$route.query.code;
      this.getPayType();
      this.getPayInfo();
    },
    watch: {
      allRequestSuccess(newValue, oldValue) {
        if ( newValue == 2 ) {
          if ( this.payInfo.pay_type == 'offlinepay' ) {
            this.payTypeList = [{icon: '', name: '线下支付', type: "offlinepay"}];
          }
        }
      }
    },
    methods: {
      getPayInfo() {
        getPayInfo({
          out_trade_no: this.outTradeNo,
          forceLogin: true
        }).then(res => {
          const {
            code,
            message,
            data
          } = res;
          if (data) {
            this.payInfo = res.data;
            this.allRequestSuccess ++ ;
            if ( res.data.pay_type == 'offlinepay' ) {
              this.getOfflinepayPayInfoFn(this.outTradeNo);
            }
          }
          this.loading = false;
        }).catch(err => {
          this.loading = false;
          this.$message.error({
            message: err.message,
            duration: 2000,
            onClose: () => {
              this.$router.push({
                path: '/member/order_list'
              });
            }
          });
        });
      },
      getPayType() {
        getPayType({}).then(res => {
          const {
            code,
            message,
            data
          } = res;
          if (code >= 0) {
            if (res.data.pay_type == '') {
              this.payTypeList = [];
            } else {
              this.payTypeList = this.payTypeList.filter((val, key) => {
                  return res.data.pay_type.indexOf(val.type) != -1
              });
              if(this.payTypeList.some(el=>el.type=='offlinepay')){
                this.getOfflinepayConfigFn()
              }
            }
            this.allRequestSuccess ++ ;
          }
        }).catch(err => {
          this.$message.error(err.message);
        });
      },
      //切换支付方式
      payTypeChange(index){
        if(this.type=='edit') return
        this.payIndex = index
        this.offlinepayInfo={
					out_trade_no:'',
					imgs:'',
					imgList:[],
					desc:'',
				}
      },
      //获取线下支付配置
      getOfflinepayConfigFn(){
        getOfflinepayConfig({}).then(res => {
          const {
            code,
            message,
            data
          } = res;
          if (code >= 0) {
            let config = data.value
            Object.keys(config).forEach(key=>{
              if(config[key].status=='1'){
                config[key].key = key
                this.offlinepayConfig.push(config[key])
              }
            });
          }
        }).catch(()=>{})
      },
      //复制银行账号
      copy(val){
        this.$copy(val)
      },
      //获取线下支付信息
      getOfflinepayPayInfoFn(out_trade_no){
        getOfflinepayPayInfo({out_trade_no}).then(res => {
          const {
            code,
            message,
            data
          } = res;
          if (code >= 0&&data) {
            this.type='edit'
            // this.payIndex = 2

            this.offlinepayInfo = data
            this.offlinepayInfo.imgList = this.offlinepayInfo.imgs?this.offlinepayInfo.imgs.split(',').map(el=>{return {url:el}}):[]
            this.imgList = this.$util.deepClone(this.offlinepayInfo.imgList);
            this.$forceUpdate();
          }else{
            this.type = 'add'
            this.offlinepayInfo={
              out_trade_no,
              imgs:'',
              imgList:[],
              desc:'',
            }
          }
        }).catch(()=>{})
      },
      handleSuccess(file, fileList) {
        // 上传成功
        this.offlinepayInfo.imgList = this.offlinepayInfo.imgList.concat({url:file.data.pic_path});
				this.offlinepayInfo.imgs = this.offlinepayInfo.imgList.map(el=>el.url).toString()
        if ( this.offlinepayInfo.imgList.length >= 5) {
          this.hide = true;
        }
      },
      handleRemove(file, fileList) {
        this.offlinepayInfo.imgList = fileList.map(el=>{return {url:el.url}});

      },
      handlePictureCardPreview(file) {
        // 点开大图
        this.dialogImageUrl = file.url;
        this.imgDialogVisible = true;
      },
      handleExceed(file, fileList) {
        // 图片数量大于5
        this.$message.warning('上传图片最大数量为5张');
      },
      checkPayStatus() {
        this.timer = setInterval(() => {
          checkPayStatus({
            out_trade_no: this.outTradeNo
          }).then(res => {
            const {
              code,
              message,
              data
            } = res;
            if (code >= 0) {
              if (code == 0) {
                if (data.pay_status == 2) {
                  clearInterval(this.timer);
                  this.dialogVisible = false;
                  this.$router.push({
                    path: '/pay/result?code=' + this.payInfo.out_trade_no
                  });
                }
              } else {
                clearInterval(this.timer);
              }
            }
          }).catch(err => {
            clearInterval(this.timer);
            this.$router.push({
              path: '/'
            });
          });
        }, 2000);
      },
      pay() {
        var payType = this.payTypeList[this.payIndex];
        if (!payType) return;

        let return_url = encodeURIComponent(Config.webDomain + '/pay/result?code=' + this.outTradeNo);
        if(payType.type!='offlinepay'){
          pay({
            out_trade_no: this.payInfo.out_trade_no,
            pay_type: payType.type,
            app_type: 'pc',
            return_url
          }).then(res => {
            const {
              code,
              message,
              data
            } = res;
            if (code >= 0) {
              this.checkPayStatus();
              switch (payType.type) {
                case 'alipay':
                  this.payUrl = res.data.data;
                  window.open(this.payUrl)
                  this.open();
                  break;
                case 'wechatpay':
                  this.payQrcode = res.data.qrcode;
                  this.openQrcode = true;
                  break;
              }
            } else {
              this.$message({
                message: message,
                type: 'warning'
              });
            }
          }).catch(err => {
            this.$message.error(err.message);
          });
        }else{
          if(!this.offlinepayInfo.imgList.length){
            this.$message({
                message: '请至少上传一张凭证',
                type: 'warning'
              });
            return;
          }
          this.offlinepayInfo.out_trade_no = this.outTradeNo;
          offlinepay(this.offlinepayInfo).then(res => {
              const {
                code,
                message,
                data
              } = res;
              if (code >= 0) {
                let payPath = {
                  1: '/member/order_detail',
                  2: '/member/order_detail_pickup',
                  3: '/member/order_detail_local_delivery',
                  4: '/member/order_detail_virtual',
                }
                this.$router.push({
                  path: payPath[this.payInfo.order_type],
                  query: {
                    order_id: this.payInfo.order_id
                  }
                });

              } else {
                this.$message({
                  message: message,
                  type: 'warning'
                });
              }
            }).catch(()=>{})
        }

      },
      open() {
        this.dialogVisible = true;
      },
      goIndex() {
        clearInterval(this.timer);
        this.dialogVisible = false;
        this.$router.push({
          path: '/'
        });
      },
      goOrderList() {
        clearInterval(this.timer);
        this.dialogVisible = false;
        this.$router.push({
          path: '/member/order_list'
        });
      }
    }
  };
</script>

<style lang="scss" scoped>
  .pay-wrap {
    width: 1210px;
    margin: 20px auto;
  }

  .clear {
    clear: both;
  }

  .item-block {
    padding:20px;
    margin: 10px 0;
    border-radius: 0;
    border: none;
    background: #ffffff;

    .block-text {
      border-color: #eeeeee;
      color: $ns-text-color-black;
      height: 22px;
      line-height: 22px;
      border-bottom: 1px;
    }
  }

  .media-left {
    text-align: center;
    i {
      font-size: 65px;
    }
  }

  .payment-detail {
    padding: 30px 0;
    transition: 2s;
  }

  .media-right {
    text-align: center;
    line-height: 65px;
    cursor: pointer;

    i.rotate {
      transform: rotate(180deg);
      transition: 0.3s;
    }
  }

  .payment-text {
    font-size: 20px;
  }

  .payment-time {
    font-size: 12px;
    line-height: 65px;
    color: #999;
  }

  //支付方式
  .order-submit {
    float: right;
    padding: 10px;
  }

  .pay-type-list {
    // padding: 20px 0;
    margin-top: 16px;
  }

  .no-pay-type {
    padding: 30px 0;
    text-align: center;
  }

  .pay-type-item {
    display: inline-block;
    border: 2px solid #eeeeee;
    padding: 5px 20px;
    margin-right: 20px;
    cursor: pointer;
  }

  .pay-type-item.active {
    border-color: $base-color;
  }
  .pay-type-item.disable{
    // pointer-events: none;
    cursor: not-allowed;
  }
  .mobile-wrap {
    width: 300px;
  }

  .order-info {
    .order-item {
      padding: 1px 0;

      .item-label {
        display: inline-block;
        width: 100px;
      }

      .item-value {
        display: inline-block;
      }
    }

    .line {
      width: 100%;
      height: 1px;
      background: #f2f2f2;
      margin: 20px 0 10px 0;
    }

    .order-info-left {
      height: 1px;
    }
  }

  .wechatpay-box {
    text-align: center;

    img {
      width: 80%;
    }
  }

  .confirm-pay-wrap {
    .el-dialog__body {
      padding: 10px 15px;
    }

    .info-wrap {
      i {
        position: initial;
        vertical-align: middle;
        transform: initial;
      }

      span {
        vertical-align: middle;
        padding: 0 10px;
      }
    }
  }
</style>
<style lang="scss">
  .confirm-pay-wrap {
    .el-dialog__body {
      padding: 10px 15px;
    }

    .el-dialog__footer {
      padding-top: 0;
      padding-bottom: 10px;
    }
  }
  .offlinepay{
    padding-top: 30px;
    margin-top: 20px;
    padding-bottom: 10px;
    border-top:1px solid #E2E6F0;
    display: flex;
    width: 100%;
    .offlinepay-item{
      width: 380px;
      height: 305px;
      padding: 20px;
      margin-right: 15px;
      background: linear-gradient( 180deg, #F8F9FD 0%, #FFFFFF 21%);
      border-radius: 16px 16px 16px 16px;
      border: 1px solid #EFF2FF;
      box-sizing: border-box;
      &:last-child{
        margin-right: 0;
      }
      .title{
        height: 25px;
        line-height: 25px;
        font-weight: 500;
        font-size: 18px;
        text-align: center;
        margin-bottom: 20px;
      }
      &.bank{
        .title{
          margin-bottom: 30px;
        }
        .item{
          display: flex;
          height: 22px;
          font-weight: 400;
          font-size: 16px;
          margin-bottom: 20px;
          .item-label{
            color: #626779;
          }
          .copy{
            margin-left: 10px;
            cursor: pointer;
          }
        }
      }
      .code{
        width: 100%;
        display: flex;
        justify-content: center;
        justify-content: center;
				align-items: center;
				flex-direction: column;
        .centent{
          border-radius: 8px;
					border: 1px solid #DEDEDE;
					padding: 14px;
          width: 180px;
          height: 180px;
          box-sizing: border-box;

          .qrcode{
            width: 150px;
            height: 150px;
            img{
              width: 100%;
              height: 100%;
            }
          }
        }
        .bottom{
          height: 20px;
          line-height: 20px;
          font-weight: 500;
          font-size: 14px;
          margin-top: 10px;
        }
      }

    }

  }
  .offlinepay-form{
      .upload-wrap-title{
        display: flex;
        align-items: baseline;
        margin-bottom: 16px;
        .title{
          height: 22px;
          line-height: 22px;
          font-weight: 500;
          font-size: 16px;
        }
        .title-tips{
          height: 17px;
          font-weight: 400;
          font-size: 12px;
          color: #626779;
          margin-left: 10px;
        }
      }
      .upload-wrap{
        height: 100px;
        >div:first-child{
          height: 100px;
        }
        .tips{
          margin-top: 5px;
        }
      }
      .el-upload--picture-card {
        border: none;
      }

      .el-upload--picture-card,
      .el-upload-list--picture-card .el-upload-list__item {
        width: 100px;
        height: 100px;
        line-height: 80px;
        position: relative;
      }

      .el-upload-list--picture-card .el-upload-list__item-thumbnail {
        width: 100%;
        height: auto;
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
      }

      .el-upload-list__item.is-success .el-upload-list__item-status-label {
        display: none;
      }

      .ishide .el-upload--picture-card {
        display: none;
      }

      .upload-wrap .el-upload-dragger {
        width: 100px;
        height: 100px;
        display: flex;
        justify-content: center;
        align-items: center;
      }

      .el-dialog {
        .el-dialog__body {
          text-align: center;
        }
      }
      .bottom{
        margin-top: 20px;
        padding-top: 16px;
        font-size: 14px !important;
        border-top: 1px dashed #E2E6F0;
        .remark{
          .el-textarea__inner{
            border-width: 0;
            padding: 0;
          }
        }
      }

    }

</style>
<style lang="sss" scoped>

</style>

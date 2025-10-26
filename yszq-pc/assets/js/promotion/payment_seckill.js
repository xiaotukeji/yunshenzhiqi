import {
  checkPayPassword,
  balanceConfig
} from "@/api/order/payment"
import {
  addressList,
  saveAddress,
  setDefault,
  deleteAddress,
  addressInfo
} from "@/api/member/member"
import {
  payment,
  calculate,
  orderCreate
} from "@/api/seckill"
import {
  getArea
} from "@/api/address"
import {
  mapGetters
} from "vuex"

export default {
  name: "seckill_payment",
  components: {},
  middleware: 'auth',
  data: () => {
    var checkMobile = (rule, value, callback) => {
      if (value === "") {
        callback(new Error("请输入手机号"))
      } else if (!/^\d{11}$/.test(value)) {
        callback(new Error("手机号格式错误"))
      } else {
        callback()
      }
    }

    return {
      dialogVisible: false,
      memberAddress: [], //收货地址列表
      addressId: 0, //收货地址
      addressForm: {
        id: 0,
        name: "",
        mobile: "",
        telephone: "",
        province_id: "",
        city_id: "",
        district_id: "",
        community_id: "",
        address: "",
        full_address: "",
        is_default: 1,
        longitude: "",
        latitude: ""
      },
      pickerValueArray: {},
      cityArr: {},
      districtArr: {},
      addressRules: {
        name: [{
          required: true,
          message: "请输入收货人",
          trigger: "blur"
        },
          {
            min: 1,
            max: 20,
            message: "长度在 1 到 20 个字符",
            trigger: "blur"
          }
        ],
        mobile: [{
          required: true,
          validator: checkMobile,
          trigger: "change"
        }],
        province: [{
          required: true,
          message: "请选择省",
          trigger: "change"
        }],
        city: [{
          required: true,
          message: "请选择市",
          trigger: "change"
        }],
        district: [{
          required: true,
          message: "请选择区/县",
          trigger: "change"
        }],
        address: [{
          required: true,
          message: "请输入详细地址",
          trigger: "change"
        }]
      },
      isSend: false,
      orderCreateData: {
        is_balance: 0,
        pay_password: '',

        // 发票
        is_invoice: 0, // 是否需要发票 0 无发票  1 有发票
        invoice_type: 1, // 发票类型  1 纸质 2 电子
        invoice_title_type: 1, // 抬头类型  1 个人 2 企业
        is_tax_invoice: 0, // 是否需要增值税专用发票  0 不需要 1 需要
        invoice_title: '', // 发票抬头
        taxpayer_number: '', // 纳税人识别号
        invoice_content: '', // 发票内容
        invoice_full_address: '', // 发票邮寄地址
        invoice_email: '', //发票邮箱
        member_address: {
          mobile: ''
        },
        delivery: {},
        order_key: '',
        store_id: 0
      },
      orderPaymentData: {
        member_account: {
          balance: 0,
          is_pay_password: 0
        },
        delivery: {
          express_type: [],
        },
      },
      dialogStore: false,
      storeList: {},
      isSub: false,
      dialogpay: false,
      password: "",
      fullscreenLoading: true,
      deliveryTime: false,
      timeTip: "选择配送时间",
      time: null,
      addressShow: false,
      storeRadio: false,
      balance_show: 1,
      addresNextType: true,

      modules: [],
      promotionInfo: null,
      calculateData: null,
      storeConfig: null,
      storeId: 0,
    }
  },
  computed: {
    ...mapGetters(["seckillOrderCreateData", "defaultGoodsImage", "city"])
  },
  mounted() {
  },
  created() {
    this.getMemberAddress()
    this.getOrderPaymentData()
    this.getBalanceConfig()
  },

  filters: {
    /**
     * 金额格式化输出
     * @param {Object} money
     */
    moneyFormat(money) {
      if (!money) money = 0
      return parseFloat(money).toFixed(2)
    },
    /**
     * 店铺优惠摘取
     */
    promotion(data) {
      let promotion = ""
      if (data) {
        Object.keys(data).forEach(key => {
          promotion += data[key].content + "　"
        })
      }
      return promotion
    }
  },
  methods: {
    //获取余额支付配置
    getBalanceConfig() {
      balanceConfig().then(res => {
        const {
          code,
          message,
          data
        } = res
        if (code >= 0) {
          this.balance_show = data.balance_show;
        }
      })
    },
    //获取收货地址
    getMemberAddress() {
      addressList({
        page_size: 0,
        type: 1
      }).then(res => {
        const {
          code,
          message,
          data
        } = res
        if (data && data.list) {
          let that = this
          this.memberAddress = data.list
          data.list.forEach(function (e) {
            if (e.is_default == 1) {
              that.addressId = e.id
            }
          })
        }
      }).catch(err => {
        const {
          code,
          message,
          data
        } = err
        this.$message.error(message)
      })
    },
    //设置会员收货地址
    setMemberAddress(params) {
      this.addressId = params
      setDefault({
        id: params
      }).then(res => {
        const {
          code,
          message,
          data
        } = res
        this.orderCalculate()
      }).catch(err => {
        const {
          code,
          message,
          data
        } = err
        this.$message.error(message)
      })
    },
    //删除会员收货地址
    deleteMemberAddress(params) {
      deleteAddress({
        id: params
      }).then(res => {
        const {
          code,
          message,
          data
        } = res
        if (data) {
          this.$message({
            message: message,
            type: "success"
          })
          this.getMemberAddress()
        } else {
          this.$message({
            message: message,
            type: "warning"
          })
        }
      }).catch(err => {
        this.$message.error(err.message)
      })
    },
    //打开添加收货地址弹出层
    addAddressShow() {
      this.dialogVisible = true
      this.addressForm.id = 0
      this.addressForm.name = ""
      this.addressForm.mobile = ""
      this.addressForm.telephone = ""
      this.addressForm.province_id = ""
      this.addressForm.city_id = ""
      this.addressForm.district_id = ""
      this.addressForm.community_id = ""
      this.addressForm.address = ""
      this.addressForm.full_address = ""
      this.addressForm.is_default = 1
      this.addressForm.longitude = ""
      this.addressForm.latitude = ""
      // this.$nextTick(() => {
      //   this.$refs.form.resetFields()
      // })
      this.cityArr = {}
      this.districtArr = {}
      this.getAddress(0)
    },
    //获取地址
    getAddress(type) {
      let pid = 0
      let that = this
      switch (type) {
        case 0:
          //加载省
          pid = 0
          break
        case 1:
          //加载市
          pid = this.addressForm.province_id
          that.cityArr = {}
          that.districtArr = {}
          this.addressForm.city_id = ""
          this.addressForm.district_id = ""
          break
        case 2:
          //加载区县
          pid = this.addressForm.city_id
          that.districtArr = {}
          this.addressForm.district_id = ""
          break
      }

      getArea({
        pid: pid
      }).then(res => {
        const {
          code,
          message,
          data
        } = res
        if (data) {
          switch (type) {
            case 0:
              that.pickerValueArray = data
              break
            case 1:
              //加载市
              that.cityArr = data
              break
            case 2:
              //加载区县
              that.districtArr = data
              break
          }
        }
        if (data.length == 0) {
          this.addresNextType = false;
        }
      }).catch(err => {
        const {
          code,
          message,
          data
        } = err
        this.$message.error(message)
      })
    },
    //编辑地址 初始化
    initAddress(type) {
      let pid = 0
      let that = this
      switch (type) {
        case 0:
          //加载省
          pid = 0
          break
        case 1:
          //加载市
          pid = this.addressForm.province_id
          that.cityArr = {}
          that.districtArr = {}
          break
        case 2:
          //加载区县
          pid = this.addressForm.city_id
          that.districtArr = {}
          break
      }

      getArea({
        pid: pid
      }).then(res => {
        const {
          code,
          message,
          data
        } = res
        if (data) {
          switch (type) {
            case 0:
              that.pickerValueArray = data
              break
            case 1:
              //加载市
              that.cityArr = data
              break
            case 2:
              //加载区县
              that.districtArr = data
              break
          }
        }
        if (data.length == 0) {
          this.addresNextType = false;
        }
      }).catch(err => {
        const {
          code,
          message,
          data
        } = err
        this.$message.error(message)
      })
    },
    addmemberAddress(formName) {
      this.$refs[formName].validate(valid => {
        if (valid) {
          if (this.isSend) {
            return false
          }

          if (!this.addressForm.id) {
            this.addressForm.full_address = this.$refs.province.selectedLabel + "-" + this.$refs.city.selectedLabel + "-" + this.$refs.district.selectedLabel
            let data = {
              name: this.addressForm.name,
              mobile: this.addressForm.mobile,
              telephone: this.addressForm.telephone,
              province_id: this.addressForm.province_id,
              city_id: this.addressForm.city_id,
              district_id: this.addressForm.district_id,
              community_id: "",
              address: this.addressForm.address,
              full_address: this.addressForm.full_address,
              longitude: this.addressForm.longitude,
              latitude: this.addressForm.latitude,
              is_default: this.addressForm.is_default,
              url: 'add'
            }

            if (!data.province_id || data.province_id <= 0) {
              this.$message({
                message: "请选择省",
                type: "warning"
              })
              return false
            }
            if (!data.city_id || data.city_id <= 0) {
              this.$message({
                message: "请选择市",
                type: "warning"
              })
              return false
            }
            if ((!data.district_id || data.district_id <= 0) && this.addresNextType == true) {
              this.$message({
                message: "请选择区/县",
                type: "warning"
              })
              return false
            }
            this.isSend = true

            saveAddress(data).then(res => {
              const {
                code,
                message,
                data
              } = res
              if (data) {
                this.setMemberAddress(data)

                this.$message({
                  message: message,
                  type: "success"
                })
                this.dialogVisible = false
                this.getMemberAddress()
                this.getOrderPaymentData()
              } else {
                this.$message({
                  message: message,
                  type: "warning"
                })
              }
              this.isSend = false
            }).catch(err => {
              const {
                code,
                message,
                data
              } = err
              this.$message.error(message)
            })
          } else {
            this.addressForm.full_address = this.$refs.province.selectedLabel + "-" + this.$refs.city.selectedLabel + "-" + this.$refs.district.selectedLabel
            let data = this.addressForm
            if (!data.province_id) {
              this.$message({
                message: "请选择省",
                type: "warning"
              })
              return false
            }
            if (!data.city_id) {
              this.$message({
                message: "请选择市",
                type: "warning"
              })
              return false
            }
            if (!data.district_id) {
              this.$message({
                message: "请选择区/县",
                type: "warning"
              })
              return false
            }
            this.isSend = true
            this.setMemberAddress(data.id);
            data.url = "edit";
            saveAddress(data).then(res => {
              const {
                code,
                message,
                data
              } = res
              if (data) {
                this.$message({
                  message: message,
                  type: "success"
                })
                this.dialogVisible = false
                this.getMemberAddress()
                this.getOrderPaymentData()
              } else {
                this.$message({
                  message: message,
                  type: "warning"
                })
              }
              this.isSend = false
            }).catch(err => {
              const {
                code,
                message,
                data
              } = err
              this.$message.error(message)
            })
          }
        } else {
          return false
        }
      })
    },
    //编辑收货地址
    editAddress(id) {
      addressInfo({
        id: id
      }).then(res => {
        const {
          code,
          message,
          data
        } = res
        this.addressForm = {
          id: data.id,
          name: data.name,
          mobile: data.mobile,
          telephone: data.telephone,
          province_id: data.province_id,
          city_id: "",
          district_id: "",
          community_id: "",
          address: data.address,
          full_address: data.full_address,
          is_default: data.is_default,
          longitude: data.longitude,
          latitude: data.latitude
        }
        this.initAddress(0)
        this.initAddress(1)
        this.addressForm.city_id = data.city_id
        this.initAddress(2)
        this.addressForm.district_id = data.district_id

        this.dialogVisible = true
      }).catch(err => {
        const {
          code,
          message,
          data
        } = err
        this.$message.error(message)
      })
    },
    /**
     * 获取订单初始化数据
     */
    getOrderPaymentData() {
      this.orderCreateData = this.seckillOrderCreateData

      if (!this.orderCreateData) {
        this.$message({
          message: "未获取到创建订单所需数据！", //提示的信息
          type: "warning",
          offset: 225,
          duration: 3000,
          onClose: () => {
            this.$router.go(-1)
            return false
          }
        })
        return
      }

      this.orderCreateData.web_city = this.city ? this.city.id : 0

      payment(this.orderCreateData).then(res => {
        const {
          code,
          message,
          data
        } = res

        if (code >= 0 && data) {
          if (data.delivery.express_type && data.delivery.express_type.length) {
            data.delivery.express_type = data.delivery.express_type.filter(item => item.name != 'local');
          }
          this.orderPaymentData = res.data

          this.handlePaymentData()
        } else {
          this.$message({
            message: "未获取到创建订单所需数据！", //提示的信息
            type: "warning",
            offset: 225,
            duration: 3000,
            onClose: () => {
              this.$router.go(-1)
              return false
            }
          })
        }
      }).catch(err => {
        const {
          code,
          message,
          data
        } = err
        this.$message.error(message)
      })
    },
    /**
     * 处理结算订单数据
     */
    handlePaymentData() {
      this.orderCreateData.delivery = {}
      this.orderCreateData.buyer_message = ""
      this.orderCreateData.is_balance = 0
      this.orderCreateData.pay_password = ""

      this.orderCreateData.is_invoice = 0; // 是否需要发票 0 无发票  1 有发票
      this.orderCreateData.invoice_type = 1; // 发票类型  1 纸质 2 电子
      this.orderCreateData.invoice_title_type = 1; // 发票抬头类型 1 个人 2企业
      this.orderCreateData.is_tax_invoice = 0; // 是否需要增值税专用发票  0 不需要 1 需要
      this.orderCreateData.invoice_title = '';
      this.orderCreateData.taxpayer_number = '';
      this.orderCreateData.invoice_content = '';
      this.orderCreateData.invoice_full_address = '';
      this.orderCreateData.invoice_email = '';

      var data = this.orderPaymentData

      // 记录订单key
      this.orderCreateData.order_key = data.order_key;

      // 店铺的配送
      if (data.delivery.express_type.length) {
        // 默认选择第一个配送方式
        let delivery = data.delivery.express_type[0];
        data.delivery.express_type.forEach(item => {
          if (item.name == 'store') this.storeConfig = item;
        })
        this.selectDeliveryType(delivery, false, data.member_account);
      }

      if (data.is_virtual == 1) {
        this.orderCreateData.member_address = {mobile: ''};
      }

      this.modules = data.modules;

      // 该方法在父级组件中
      this.promotionInfo = this.promotion(data);

      if (this.orderPaymentData.invoice.invoice_status == 1) {
        var invoice_content_array = this.orderPaymentData.invoice.invoice_content_array;
        if (invoice_content_array.length) this.orderCreateData.invoice_content = invoice_content_array[0];
      }

      this.orderCalculate()
    },
    /**
     * 处理活动信息 如不需要则定义为空方法
     */
    promotion(data) {
      if (data.seckill_info) {
        return {title: '限时秒杀', content: data.seckill_info.seckill_name}
      }
    },
    clickType(type) {
      this.orderCreateData.invoice_type = type;
      this.orderCalculate();
      this.$forceUpdate();
    },
    clickTitleType(type) {
      this.orderCreateData.invoice_title_type = type;
      this.orderCalculate();
      this.$forceUpdate();
    },
    // 选择发票内容
    changeInvoiceContent(invoice_content) {
      this.orderCreateData.invoice_content = invoice_content;
      this.$forceUpdate();
    },
    // 切换发票开关
    changeIsInvoice() {
      if (this.orderCreateData.is_invoice == 0) {
        this.orderCreateData.is_invoice = 1;
      } else {
        this.orderCreateData.is_invoice = 0;
      }
      this.orderCalculate();
      this.$forceUpdate();
    },
    // 发票验证
    invoiceVerify() {
      if (!this.orderCreateData.invoice_title) {
        this.$message({
          message: "请填写发票抬头",
          type: "warning"
        })
        return false;
      }
      if (!this.orderCreateData.taxpayer_number && this.orderCreateData.invoice_title_type == 2) {
        this.$message({
          message: "请填写纳税人识别号",
          type: "warning"
        })
        return false;
      }
      if (this.orderCreateData.invoice_type == 1 && !this.orderCreateData.invoice_full_address) {
        this.$message({
          message: "请填写发票邮寄地址",
          type: "warning"
        })
        return false;
      }
      if (this.orderCreateData.invoice_type == 2 && !this.orderCreateData.invoice_email) {
        this.$message({
          message: "请填写邮箱",
          type: "warning"
        })
        return false;
      }
      if (this.orderCreateData.invoice_type == 2) {
        var reg = /^([a-zA-Z]|[0-9])(\w|\-)+@[a-zA-Z0-9]+\.([a-zA-Z]{2,4})$/;
        if (!reg.test(this.orderCreateData.invoice_email)) {
          this.$message({
            message: "请填写正确的邮箱",
            type: "warning"
          })
          return false;
        }
      }
      if (!this.orderCreateData.invoice_content) {
        this.$message({
          message: "请选择发票内容",
          type: "warning"
        })
        return false;
      }
      return true;
    },
    /**
     * 订单计算
     */
    orderCalculate() {
      this.fullscreenLoading = true
      var data = this.$util.deepClone(this.orderCreateData)
      data.delivery = JSON.stringify(data.delivery)
      data.member_address = JSON.stringify(data.member_address);

      calculate(data).then(res => {
        const {
          code,
          message,
          data
        } = res
        if (code >= 0 && data) {
          this.calculateData = data;
          this.calculateData.goods_list.forEach((v) => {
            if (v.sku_spec_format) {
              v.sku_spec_format = JSON.parse(v.sku_spec_format);
            } else {
              v.sku_spec_format = [];
            }
          });

          if (data.delivery) {
            if (data.delivery.delivery_type == 'express') {
              this.orderCreateData.member_address = data.delivery.member_address;
            }
          }
        } else {
          this.$message({
            message: message, //提示的信息
            type: "warning",
            offset: 225,
            duration: 3000,
            onClose: () => {
              this.$router.go(-1)
              return false
            }
          })
          return
        }
        this.fullscreenLoading = false
      }).catch(err => {
        const {
          code,
          message,
          data
        } = err
        this.$message.error(message)
        this.fullscreenLoading = false
      })
    },

    /**
     * 选择配送方式
     */
    selectDeliveryType(data, calculate = true, member_account = null) {
      if (this.orderCreateData.delivery && this.orderCreateData.delivery.delivery_type == data.name) return;

      let delivery = {
        delivery_type: data.name,
        delivery_type_name: data.title
      }

      if (data.name == "store") {
        // 门店配送
        if (data.store_list[0]) {
          delivery.store_id = data.store_list[0].store_id;
        }
        this.storeId = delivery.store_id ? delivery.store_id : 0;
        this.orderCreateData.store_id = delivery.store_id ? delivery.store_id : 0;
        if (member_account) {
          this.orderCreateData.member_address = {
            name: member_account.nickname,
            mobile: member_account.mobile
          };
        } else {
          this.orderCreateData.member_address = {
            name: this.orderPaymentData.member_account.nickname,
            mobile: this.orderPaymentData.member_account.mobile
          };
        }

        data.store_list.forEach(function (e, i) {
          data.store_list[i]["store_address"] = e.full_address + e.address
        })

        this.dialogStore = true
        this.storeList = data.store_list;

      }

      this.$set(this.orderCreateData, 'delivery', delivery);

      if (calculate) this.orderCalculate()
      this.$forceUpdate()
    },
    /**
     * 选择自提点
     * @param {Object} item
     */
    selectStore(item) {
      if (!item) return;
      let store_id = item.store_id
      this.dialogStore = false
      this.orderCreateData.delivery.store_id = store_id
      this.orderCreateData.store_id = store_id
      this.orderCreateData.delivery.store_name = item.store_name
      this.storeRadio = item
      this.orderCalculate()
      this.$forceUpdate()
    },
    /**
     * 是否使用余额
     */
    useBalance(type) {
      if (this.orderCreateData.is_balance) this.orderCreateData.is_balance = 0
      else this.orderCreateData.is_balance = 1
      this.orderCalculate()
      this.$forceUpdate()
    },
    orderCreate() {
      if (this.verify()) {
        if (this.isSub) return
        if (this.orderCreateData.is_invoice == 1) {
          if (this.invoiceVerify() === false) return;
        }
        this.isSub = true

        var loading = this.$loading({
          lock: true,
          text: "订单提交中...",
          spinner: "el-icon-loading",
          background: "rgba(0, 0, 0, 0.7)"
        })

        var data = this.$util.deepClone(this.orderCreateData);
        data.delivery = JSON.stringify(data.delivery);
        data.member_address = JSON.stringify(data.member_address);

        orderCreate(data).then(res => {
          const {
            code,
            message,
            data
          } = res
          loading.close()
          if (code >= 0) {
            this.$store.dispatch("order/removeSeckillOrderCreateData", "")
            if (this.calculateData.pay_money == 0) {
              this.$router.push({
                path: "/pay/result",
                query: {
                  code: data
                }
              })
            } else {
              this.$router.push({
                path: "/pay",
                query: {
                  code: data
                }
              })
            }
          } else {
            this.$message({
              message: message,
              type: "warning"
            })
          }
        }).catch(err => {
          loading.close()
          this.isSub = false
          const {
            code,
            message,
            data
          } = err
          this.$message.error(message)
        })
      }
    },
    /**
     * 订单验证
     */
    verify() {
      if (this.orderPaymentData.is_virtual == 1) {
        if (!this.orderCreateData.member_address.mobile.length) {
          this.$message({
            message: "请输入您的手机号码",
            type: "warning"
          })
          return false
        }
        if (!this.$util.verifyMobile(this.orderCreateData.member_address.mobile)) {
          this.$message({
            message: "请输入正确的手机号码",
            type: "warning"
          })
          return false
        }
      }

      if (this.orderPaymentData.is_virtual == 0) {
        if (!this.orderCreateData.member_address) {
          this.$message({
            message: "请先选择您的收货地址",
            type: "warning"
          })

          return false
        }

        if (!this.orderCreateData.delivery || !this.orderCreateData.delivery.delivery_type) {
          this.$message({
            message: '商家未设置配送方式',
            type: "warning"
          })
          return false;
        }

        if (this.orderCreateData.delivery.delivery_type == 'store') {
          if (!this.orderCreateData.delivery.store_id) {
            this.$message({
              message: '没有可提货的门店,请选择其他配送方式',
              type: "warning"
            })
            return false;
          }
        }

      }

      // if (this.orderCreateData.is_balance == 1 && this.orderCreateData.pay_password == "") {
      // 	this.dialogpay = true
      // 	return false
      // }
      return true
    },
    /**
     * 支付密码输入
     */
    input() {
      if (this.password.length == 6) {
        var loading = this.$loading({
          lock: true,
          text: "支付中",
          spinner: "el-icon-loading",
          background: "rgba(0, 0, 0, 0.7)"
        })

        checkPayPassword({
          pay_password: this.password
        }).then(res => {
          const {
            code,
            message,
            data
          } = res
          loading.close()
          if (code >= 0) {
            this.orderCreateData.pay_password = this.password
            this.orderCreate()
            this.dialogpay = false
          } else {
            this.$message({
              message: message,
              type: "warning"
            })
          }
        }).catch(err => {
          loading.close()
          const {
            code,
            message,
            data
          } = err
          this.$message.error(message)
        })
      }
    },
    textarea() {
      this.$forceUpdate()
    },
    imageError(index) {
      this.calculateData.goods_list[index].sku_image = this.defaultGoodsImage
    },
    setPayPassword() {
      this.$util.pushToTab("/member/security");
    }
  }
}

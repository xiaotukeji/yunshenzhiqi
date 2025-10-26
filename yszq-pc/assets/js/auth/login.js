import {
  mobileCode,
  registerConfig,
  wechatMobileCode,
} from "@/api/auth/login"
import {
  adList,
  captcha
} from "@/api/website"
import {
  isWechatLogin
} from "@/api/wechat"

export default {
  data: () => {
    var isMobile = (rule, value, callback) => {
      if (!value) {
        return callback(new Error("手机号不能为空"))
      } else {

        if (/^\d{11}$/.test(value)) {
          callback()
        } else {
          callback(new Error("请输入正确的手机号"))
        }
      }
    }

    return {
      qrcodeData: {
        time: 0,
        timer: 0,
      },
      wx_key: '',
      expire_time: '',
      ischecked: false,
      ischecked1: false,
      activeName: "first", // tab切换

      // 表单数据
      formData: {
        account: "",
        password: "",
        vercode: "",
        mobile: "",
        dynacode: "",
        key: "",
        checked: false,
        autoLoginRange: 7
      },
      // 验证码
      captcha: {
        id: "",
        img: ""
      },
      // 动态码
      dynacodeData: {
        seconds: 120,
        timer: null,
        codeText: "获取动态码",
        isSend: false
      },
      // 提交防重复
      isSub: false,
      registerConfig: {
        is_enable: 1,
        register: '',
        login: ''
      },
      accountRules: {
        account: [{
          required: true,
          message: "请输入登录账号",
          trigger: "blur"
        }],
        password: [{
          required: true,
          message: "请输入登录密码",
          trigger: "blur"
        }],
        vercode: [{
          required: true,
          message: "请输入验证码",
          trigger: "blur"
        }]
      },
      mobileRules: {
        mobile: [{
          required: true,
          validator: isMobile,
          trigger: "blur"
        }],
        vercode: [{
          required: true,
          message: "请输入验证码",
          trigger: "blur"
        }],
        dynacode: [{
          required: true,
          message: "请输入短信动态码",
          trigger: "blur"
        }]
      },
      wechatRules: {
        mobile: [{
          required: true,
          validator: isMobile,
          trigger: "blur"
        }],
        vercode: [{
          required: true,
          message: "请输入验证码",
          trigger: "blur"
        }],
        dynacode: [{
          required: true,
          message: "请输入短信动态码",
          trigger: "blur"
        }]
      },
      codeRules: {
        mobile: [{
          required: true,
          validator: isMobile,
          trigger: "blur"
        }],
        vercode: [{
          required: true,
          message: "请输入验证码",
          trigger: "blur"
        }]
      },
      loadingAd: true,
      adList: [],
      backgroundColor: '',
      img: '',
      third_party: 0,
      wechatConfigStatus: 0,
    }
  },
  created() {
    this.ischecked = this.$route.params.third_party;
    if (this.ischecked) {
      this.weixinLogin()
    }
    this.getAdList()
    this.getCaptcha()
    this.getRegisterConfig()
    this.getIsWechatLogin()
  },
  head() {
    return {
      title: '登录-' + this.$store.state.site.siteInfo.site_name
    }
  },
  watch: {
    "dynacodeData.seconds": {
      handler(newValue, oldValue) {
        if (newValue == 0) {
          clearInterval(this.dynacodeData.timer)
          this.dynacodeData = {
            seconds: 120,
            timer: null,
            codeText: "获取动态码",
            isSend: false
          }
        }
      },
      immediate: true,
      deep: true
    }
  },
  methods: {
    getIsWechatLogin() {
      isWechatLogin().then(res => {
        if (res.code == 0) {
          this.wechatConfigStatus = res.data.wechat_config_status;
        }
      })
    },
    getAdList() {
      adList({
        keyword: "NS_PC_LOGIN"
      }).then(res => {
        if (res.code == 0 && res.data.adv_list) {
          this.adList = res.data.adv_list
          for (let i = 0; i < this.adList.length; i++) {
            if (this.adList[i].adv_url) this.adList[i].adv_url = JSON.parse(this.adList[i].adv_url)
          }
          this.backgroundColor = this.adList[0].background
        }

        this.loadingAd = false
      }).catch(err => {
        this.loadingAd = false
      })
    },
    handleClick(tab, event) {
    },
    handleChange(curr, pre) {
      this.backgroundColor = this.adList[curr].background
    },
    /**
     * 账号登录
     */
    accountLogin(formName) {
      this.$refs[formName].validate(valid => {
        if (valid) {
          var data = {
            username: this.formData.account,
            password: this.formData.password
          }

          if (this.captcha.id != "") {
            data.captcha_id = this.captcha.id
            data.captcha_code = this.formData.vercode
          }

          if (this.formData.checked) {
            data.autoLoginRange = this.formData.autoLoginRange
          }

          if (this.isSub) return
          this.isSub = true

          this.$store
            .dispatch("member/login", data)
            .then(res => {
              if (res.code >= 0) {
                this.$message({
                  message: "登录成功！",
                  type: "success"
                })
                if (this.$route.query.redirect) {
                  const a = this.$route.query.redirect
                  const b = this.$route.query
                  this.$router.push(this.$route.query.redirect)

                } else {
                  this.$router.push({
                    name: "member"
                  })
                }
              } else {
                this.isSub = false
                this.getCaptcha()
                this.$message({
                  message: res.message,
                  type: "warning"
                })
              }
            })
            .catch(err => {
              this.isSub = false
              this.$message.error(err.message)
              this.getCaptcha()
            })
        } else {
          return false
        }
      })
    },
    /**
     * 手机号登录
     */
    mobileLogin(formName) {
      this.$refs[formName].validate(valid => {
        if (valid) {
          var data = {
            mobile: this.formData.mobile,
            key: this.formData.key,
            code: this.formData.dynacode
          }

          if (this.captcha.id != "") {
            data.captcha_id = this.captcha.id
            data.captcha_code = this.formData.vercode
          }

          if (this.isSub) return
          this.isSub = true

          this.$store.dispatch("member/mobile_login", data).then(res => {
            if (res.code >= 0) {
              this.$message({
                message: "登录成功！",
                type: "success"
              })
              if (this.$route.query.redirect) {
                this.$router.push(this.$route.query.redirect)
              } else {
                this.$router.push({
                  name: "member"
                })
              }
            } else {
              this.isSub = false
              this.getCaptcha()
              this.$message({
                message: res.message,
                type: "warning"
              })
            }
          }).catch(err => {
            this.isSub = false
            this.$message.error(err.message)
            this.getCaptcha()
          })
        } else {
          return false
        }
      })
    },
    /**
     * 微信登录
     */
    wechatLogin(formName) {
      this.$refs[formName].validate(valid => {
        if (valid) {
          var data = {
            mobile: this.formData.mobile,
            key: this.formData.key,
            code: this.formData.dynacode
          }

          if (this.captcha.id != "") {
            data.captcha_id = this.captcha.id
            data.captcha_code = this.formData.vercode
          }

          if (this.isSub) return
          this.isSub = true

          this.$store.dispatch("wechat/wechatLogin", data).then(res => {

            if (res.code >= 0) {
              this.$message({
                message: "登录成功！",
                type: "success"
              })
              if (this.$route.query.redirect) {
                this.$router.push(this.$route.query.redirect)
              } else {
                this.$router.push({
                  name: "member"
                })
              }
            } else {
              this.isSub = false
              this.getCaptcha()
              this.$message({
                message: res.message,
                type: "warning"
              })
            }
          }).catch(err => {
            this.isSub = false
            this.$message.error(err.message)
            this.getCaptcha()
          })
        } else {
          return false
        }
      })
    },
    weixinLogin() {
      this.ischecked = true;
      this.$store.dispatch("wechat/loginCode").then(res => {
        if (res.code >= 0) {
          this.img = res.data.qrcode;
          this.wx_key = res.data.key;
          this.expire_time = res.data.expire_time;
          this.qrcodeData.timer = setInterval(() => {
            this.checkLogin()
          }, 2000);
        }
      })
    },

    // 检测是否扫码
    checkLogin() {
      this.qrcodeData.time += 2;
      if (this.qrcodeData.time > this.expire_time) {
        clearInterval(this.qrcodeData.timer);
        return;
      }
      var data = {
        key: this.wx_key
      };
      this.$store.dispatch("wechat/checkLogin", data).then(res => {
        if (res.code >= 0) {
          if (res.data.token != undefined) {
            this.$message({
              message: "登录成功！",
              type: "success"
            })
            if (this.$route.query.redirect) {
              this.$router.push(this.$route.query.redirect)
            } else {
              this.$router.push({
                name: "member"
              })
            }
          } else {
            this.ischecked1 = true;
          }

          clearInterval(this.qrcodeData.timer);
        }
      })
    },
    closeWx() {
      this.ischecked = false;
    },
    closeWx1() {
      this.ischecked = false;
      this.ischecked1 = false;
    },
    /**
     * 获取注册配置
     */
    getRegisterConfig() {
      registerConfig()
        .then(res => {
          if (res.code >= 0) {
            this.registerConfig = res.data.value
            if (this.registerConfig.login.indexOf('username') != -1) {
              this.activeName = 'first';
            } else {
              this.activeName = 'second';
            }

          }
        })
    },
    /**
     * 获取验证码
     */
    getCaptcha() {
      captcha({
        captcha_id: this.captcha.id
      }).then(res => {
        if (res.code >= 0) {
          this.captcha.id = res.data.id
          this.captcha.img = res.data.img
          this.captcha.img = this.captcha.img.replace(/\r\n/g, "")
        }
      }).catch(err => {
        this.$message.error(err.message)
      })
    },
    /**
     * 发送手机动态码
     */
    sendMobileCode(formName) {
      if (this.dynacodeData.seconds != 120) return
      this.$refs[formName].clearValidate("dynacode")

      this.$refs[formName].validateField("mobile", valid => {
        if (valid) {
          return false
        }
      })
      this.$refs[formName].validateField("vercode", valid => {
        if (!valid) {
          mobileCode({
            mobile: this.formData.mobile,
            captcha_id: this.captcha.id,
            captcha_code: this.formData.vercode
          }).then(res => {
            if (res.code >= 0) {
              this.formData.key = res.data.key
              if (this.dynacodeData.seconds == 120 && this.dynacodeData.timer == null) {
                this.dynacodeData.timer = setInterval(() => {
                  this.dynacodeData.seconds--
                  this.dynacodeData.codeText = this.dynacodeData.seconds + "s后可重新获取"
                }, 1000)
              }
            }
          }).catch(err => {
            this.$message.error(err.message)
          })
        } else {
          return false
        }
      })
    },
    /**
     * 发送微信绑定手机动态码
     */
    sendWechatMobileCode(formName) {
      if (this.dynacodeData.seconds != 120) return
      this.$refs[formName].clearValidate("dynacode")

      this.$refs[formName].validateField("mobile", valid => {
        if (valid) {
          return false
        }
      })
      this.$refs[formName].validateField("vercode", valid => {
        if (!valid) {
          wechatMobileCode({
            mobile: this.formData.mobile,
            captcha_id: this.captcha.id,
            captcha_code: this.formData.vercode
          }).then(res => {
            if (res.code >= 0) {
              this.formData.key = res.data.key
              if (this.dynacodeData.seconds == 120 && this.dynacodeData.timer == null) {
                this.dynacodeData.timer = setInterval(() => {
                  this.dynacodeData.seconds--
                  this.dynacodeData.codeText = this.dynacodeData.seconds + "s后可重新获取"
                }, 1000)
              }
            }
          }).catch(err => {
            this.$message.error(err.message)
          })
        } else {
          return false
        }
      })
    },
    keypress(e) {
      let that = this;
      var keycode = e.all ? e.keyCode : e.which;
      if (keycode == 13) {
        if (that.activeName == "first") {
          that.accountLogin('ruleForm'); // 登录方法名
        } else {
          that.mobileLogin('mobileRuleForm'); // 登录方法名
        }
        return false;
      }
    }
  }
}

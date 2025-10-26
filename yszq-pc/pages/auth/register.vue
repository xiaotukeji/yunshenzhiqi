<template>
  <div class="register">
    <div class="box-card">
      <el-tabs v-model="activeName" @tab-click="handleClick">
        <el-tab-pane label="用户注册" name="first" v-if="registerConfig.register.indexOf('username') != -1">
          <el-form v-if="activeName == 'first'" :model="registerForm" :rules="registerRules" ref="registerRef" label-width="80px" label-position="right" show-message>
            <el-form-item label="用户名" prop="username">
              <el-input v-model="registerForm.username" placeholder="请输入用户名"></el-input>
            </el-form-item>
            <el-form-item label="密码" prop="password">
              <el-input v-model="registerForm.password" placeholder="请输入密码" type="password"></el-input>
            </el-form-item>
            <el-form-item label="确认密码" prop="checkPass">
              <el-input v-model="registerForm.checkPass" placeholder="请输入确认密码" type="password"></el-input>
            </el-form-item>
            <el-form-item label="验证码" prop="code">
              <el-input v-model="registerForm.code" placeholder="请输入验证码" maxlength="4">
                <template slot="append">
                  <img :src="captcha.img" mode class="captcha" @click="getCode" />
                </template>
              </el-input>
            </el-form-item>
          </el-form>
          <div class="xy" @click="check">
            <div class="xy-wrap">
              <div class="iconfont" :class="ischecked ? 'icon-xuanze-duoxuan' : 'icon-xuanze'"></div>
              <div class="content">
                阅读并同意
                <b @click.stop="getAggrement">《服务协议》</b>
              </div>
            </div>
            <div class="toLogin" @click="toLogin">已有账号，立即登录</div>
          </div>
          <el-button @click="register">立即注册</el-button>
        </el-tab-pane>

        <el-tab-pane label="手机动态码注册" name="second" v-if="registerConfig.register.indexOf('mobile') != -1">
          <el-form v-if="activeName == 'second'" :model="registerForm" :rules="mobileRules" ref="mobileRuleForm">
            <el-form-item prop="mobile">
              <el-input v-model="registerForm.mobile" placeholder="请输入手机号">
                <template slot="prepend">
                  <i class="iconfont icon-shouji-copy"></i>
                </template>
              </el-input>
            </el-form-item>

            <el-form-item prop="vercode">
              <el-input v-model="registerForm.vercode" autocomplete="off" placeholder="请输入验证码" maxlength="4">
                <template slot="prepend">
                  <i class="iconfont icon-yanzhengma"></i>
                </template>
                <template slot="append">
                  <img :src="captcha.img" mode class="captcha" @click="getCode" />
                </template>
              </el-input>
            </el-form-item>

            <el-form-item prop="dynacode">
              <el-input v-model="registerForm.dynacode" maxlength="4" placeholder="请输入短信动态码">
                <template slot="prepend">
                  <i class="iconfont icon-dongtaima"></i>
                </template>
                <template slot="append">
                  <div class="dynacode" :class="dynacodeData.seconds == 120 ? 'ns-text-color' : 'ns-text-color-gray'" @click="sendMobileCode('mobileRuleForm')">
                    {{ dynacodeData.codeText }}
                  </div>
                </template>
              </el-input>
            </el-form-item>
          </el-form>
          <div class="xy" @click="check">
            <div class="xy-wrap">
              <div class="iconfont" :class="ischecked ? 'icon-xuanze-duoxuan' : 'icon-xuanze'"></div>
              <div class="content">
                阅读并同意
                <b @click.stop="getAggrement">《服务协议》</b>
              </div>
            </div>
            <div class="toLogin" @click="toLogin">已有账号，立即登录</div>
          </div>

          <el-button @click="registerMobile">立即注册</el-button>
        </el-tab-pane>
      </el-tabs>
      <el-dialog :title="agreement.title" :visible.sync="aggrementVisible" width="60%" :before-close="aggrementClose" :lock-scroll="false" center>
        <div v-html="agreement.content" class="xyContent"></div>
      </el-dialog>
    </div>
    <!-- 浮层区 -->
    <div class="floatLayer-wrap" v-show="is_show && reward" :style="{ width: bgWidth, height: bgHeight }">
      <div class="reward-wrap">
        <img :src="$util.img('public/uniapp/register_reward/register_reward_img.png')" mode="widthFix" class="bg-img-head" />
        <img :src="$util.img('public/uniapp/register_reward/register_reward_money.png')" mode="widthFix" class="bg-img-money" />
        <img :src="$util.img('public/uniapp/register_reward/register_reward_head.png')" mode="widthFix" class="bg-img" />
        <div class="wrap">
          <div>
            <div class="reward-content">
              <div class="reward-item" v-if="reward && reward.point > 0">
                <div class="head">积分奖励</div>
                <div class="content">
                  <div class="info">
                    <div>
                      <span class="num">{{ reward.point }}</span>
                      <span class="type">积分</span>
                    </div>
                    <div class="desc">用于下单时抵现或兑换商品等</div>
                  </div>
                  <div class="tip" @click="closeRewardPopup('point')">立即查看</div>
                </div>
              </div>
              <div class="reward-item" v-if="reward && reward.growth > 0">
                <div class="head">成长值</div>
                <div class="content">
                  <div class="info">
                    <div>
                      <span class="num">{{ reward.growth }}</span>
                      <span class="type">成长值</span>
                    </div>
                    <div class="desc">用于提升会员等级</div>
                  </div>
                  <div class="tip" @click="closeRewardPopup('growth')">立即查看</div>
                </div>
              </div>
              <div class="reward-item" v-if="reward && reward.balance > 0">
                <div class="head">红包奖励</div>
                <div class="content">
                  <div class="info">
                    <div>
                      <span class="num">{{ reward.balance }}</span>
                      <span class="type">元</span>
                    </div>
                    <div class="desc">不可提现下单时可用</div>
                  </div>
                  <div class="tip" @click="closeRewardPopup('balance')">立即查看</div>
                </div>
              </div>
              <div class="reward-item" v-if="reward && reward.coupon_list.length > 0">
                <div class="head">优惠券奖励</div>
                <div class="content" v-for="(item, index) in reward.coupon_list" :key="index">
                  <div class="info">
                    <div>
                      <span class="num coupon-name">{{ item.coupon_name }}</span>
                    </div>
                    <div class="desc" v-if="item.at_least > 0">满{{ item.at_least }}{{ item.type == 'discount' ? '打' + item.discount + '折' : '减' + item.money }}</div>
                    <div class="desc" v-else>无门槛，{{ item.type == 'discount' ? '打' + item.discount + '折' : '减' + item.money }}</div>
                  </div>
                  <div class="tip" @click="closeRewardPopup('coupon')">立即查看</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="close-btn" @click="closeRewardPopup()"><i class="iconfont icon-guanbi"></i></div>
      </div>
    </div>
  </div>
</template>

<script>
  import {getRegisterAgreement, registerConfig, registerMobileCode, getRegisterReward} from '@/api/auth/register';
  import {captcha} from '@/api/website';

  export default {
    name: 'register',
    layout: 'login',
    components: {},
    data() {
      var checkPassValidata = (rule, value, callback) => {
        if (value === '') {
          callback(new Error('请再次输入密码'));
        } else if (value !== this.registerForm.password) {
          callback(new Error('两次输入密码不一致!'));
        } else {
          callback();
        }
      };
      let self = this;
      var passwordValidata = function (rule, value, callback) {
        let regConfig = self.registerConfig;
        if (!value) {
          return callback(new Error('请输入密码'));
        } else {
          if (regConfig.pwd_len > 0) {
            if (value.length < regConfig.pwd_len) {
              return callback(new Error('密码长度不能小于' + regConfig.pwd_len + '位'));
            } else {
              callback();
            }
          }

          if (regConfig.pwd_complexity != '') {
            let passwordErrorMsg = '密码需包含',
              reg = '';
            if (regConfig.pwd_complexity.indexOf('number') != -1) {
              reg += '(?=.*?[0-9])';
              passwordErrorMsg += '数字';
            } else if (regConfig.pwd_complexity.indexOf('letter') != -1) {
              reg += '(?=.*?[a-z])';
              passwordErrorMsg += '、小写字母';
            } else if (regConfig.pwd_complexity.indexOf('upper_case') != -1) {
              reg += '(?=.*?[A-Z])';
              passwordErrorMsg += '、大写字母';
            } else if (regConfig.pwd_complexity.indexOf('symbol') != -1) {
              reg += '(?=.*?[#?!@$%^&*-])';
              passwordErrorMsg += '、特殊字符';
            } else {
              reg += '';
              passwordErrorMsg += '';
            }
            reg = new RegExp(reg);
            if (reg.test(value)) {
              return callback(new Error(passwordErrorMsg));
            } else {
              callback();
            }
          }
        }
      };
      var isMobile = (rule, value, callback) => {
        if (!value) {
          return callback(new Error('手机号不能为空'));
        } else {

          if (/^\d{11}$/.test(value)) {
            callback();
          } else {
            callback(new Error('请输入正确的手机号'));
          }
        }
      };
      return {
        registerForm: {
          username: '',
          password: '',
          checkPass: '',
          code: '',
          mobile: '',
          vercode: '',
          dynacode: '',
          key: ''
        },
        registerRules: {
          username: [
            {
              required: true,
              message: '请输入用户名',
              trigger: 'blur'
            }
          ],
          password: [
            {
              required: true,
              validator: passwordValidata,
              trigger: 'blur'
            }
          ],
          checkPass: [
            {
              required: true,
              validator: checkPassValidata,
              trigger: 'blur'
            }
          ],
          code: [
            {
              required: true,
              message: '请输入验证码',
              trigger: 'blur'
            }
          ]
        },
        mobileRules: {
          mobile: [
            {
              required: true,
              validator: isMobile,
              trigger: 'blur'
            }
          ],
          vercode: [
            {
              required: true,
              message: '请输入验证码',
              trigger: 'blur'
            }
          ],
          dynacode: [
            {
              required: true,
              message: '请输入短信动态码',
              trigger: 'blur'
            }
          ]
        },
        dynacodeData: {
          seconds: 120,
          timer: null,
          codeText: '获取动态码',
          isSend: false
        }, // 动态码
        ischecked: false,
        agreement: '',
        aggrementVisible: false,
        captcha: {
          // 验证码
          id: '',
          img: ''
        },
        registerConfig: {
          register: ''
        },
        activeName: 'first', // tab切换
        reward: null,
        is_show: false,
        bgWidth: '',
        bgHeight: ''
      };
    },
    created() {
      this.getCode();
      this.registerAggrement();
      this.getRegisterConfig();
      this.getRegisterReward();
      if (process.client) {
        this.bgWidth = document.documentElement.clientWidth + 'px';
        this.bgHeight = document.documentElement.clientHeight + 'px';
      }
    },
    head() {
      return {
        title: '注册-' + this.$store.state.site.siteInfo.site_name
      };
    },
    methods: {
      closeRewardPopup(type) {
        this.is_show = false;

        switch (type) {
          case 'point':
            this.$router.push('/member/my_point');
            break;
          case 'balance':
            this.$router.push('/member/account');
            break;
          case 'growth':
            this.$router.push('/member');
            break;
          case 'coupon':
            this.$router.push('/member/coupon');
            break;
          default:
            this.$router.push('/member');
            this.is_show = false;
        }
      },
      getRegisterReward() {
        getRegisterReward()
          .then(res => {
            if (res.code >= 0) {
              let data = res.data;
              if (data.is_use == 1 && (data.value.point > 0 || data.value.balance > 0 || data.value.growth > 0 || data.value.coupon_list.length > 0)) {
                this.reward = data.value;
              }
            }
          })
      },
      sendMobileCode(formName) {
        if (this.dynacodeData.seconds != 120) return;
        this.$refs[formName].clearValidate('dynacode');

        this.$refs[formName].validateField('mobile', valid => {
          if (valid) {
            return false;
          }
        });
        this.$refs[formName].validateField('vercode', valid => {
          if (!valid) {
            registerMobileCode({
              mobile: this.registerForm.mobile,
              captcha_id: this.captcha.id,
              captcha_code: this.registerForm.vercode
            })
              .then(res => {
                if (res.code >= 0) {
                  this.registerForm.key = res.data.key;
                  if (this.dynacodeData.seconds == 120 && this.dynacodeData.timer == null) {
                    this.dynacodeData.timer = setInterval(() => {
                      this.dynacodeData.seconds--;
                      this.dynacodeData.codeText = this.dynacodeData.seconds + 's后可重新获取';
                    }, 1000);
                  }
                }
              })
              .catch(err => {
                this.$message.error(err.message);
              });
          } else {
            return false;
          }
        });
      },
      handleClick(tab, event) {
      },
      check() {
        this.ischecked = !this.ischecked;
      },
      toLogin() {
        this.$router.push('/auth/login');
      },
      //  获取注册配置
      getRegisterConfig() {
        registerConfig().then(res => {
          if (res.code >= 0) {
            this.registerConfig = res.data.value;
            if (this.registerConfig.register == '') {
              this.$message({
                message: '平台未启用注册',
                type: 'warning',
                duration: 2000,
                onClose: () => {
                  this.$router.push({name: 'login', params: {third_party: true}});
                }
              });
            } else if (this.registerConfig.register.indexOf('username') != -1) {
              this.activeName = 'first';
            } else {
              this.activeName = 'second';
            }
          }
        });
      },
      // 账号密码注册
      register() {
        this.$refs.registerRef.validate(valid => {
          if (valid) {
            if (!this.ischecked) {
              return this.$message({
                message: '请先阅读协议并勾选',
                type: 'warning'
              });
            }

            var data = {
              username: this.registerForm.username.trim(),
              password: this.registerForm.password
            };

            var user_test = /^[A-Za-z0-9]+$/;
            if (!user_test.test(data.username)) {
              return this.$message({
                message: '用户名只能输入数字跟英文',
                type: 'warning'
              });
            }

            if (this.captcha.id != '') {
              data.captcha_id = this.captcha.id;
              data.captcha_code = this.registerForm.code;
            }
            this.$store
              .dispatch('member/register_token', data)
              .then(res => {
                if (res.code >= 0) {
                  if (this.reward) {
                    this.is_show = true;
                  } else {
                    this.$router.push('/member');
                  }
                }
              })
              .catch(err => {
                this.$message.error(err.message);
                this.getCode();
              });
          } else {
            return false;
          }
        });
      },
      // 手机号注册
      registerMobile() {
        this.$refs.mobileRuleForm.validate(valid => {
          if (valid) {
            if (!this.ischecked) {
              return this.$message({
                message: '请先阅读协议并勾选',
                type: 'warning'
              });
            }
            var data = {
              mobile: this.registerForm.mobile,
              key: this.registerForm.key,
              code: this.registerForm.dynacode
            };
            if (this.captcha.id != '') {
              data.captcha_id = this.captcha.id;
              data.captcha_code = this.registerForm.code;
            }
            this.$store
              .dispatch('member/registerMobile_token', data)
              .then(res => {
                if (res.code >= 0) {
                  if (this.reward) {
                    this.is_show = true;
                  } else {
                    this.$router.push('/member');
                  }
                }
              })
              .catch(err => {
                this.$message.error(err.message);
                this.getCode();
              });
          } else {
            return false;
          }
        });
      },
      aggrementClose() {
        this.aggrementVisible = false;
      },
      // 获取协议
      registerAggrement() {
        getRegisterAgreement().then(res => {
          if (res.code >= 0) {
            this.agreement = res.data;
          }
        });
      },
      getAggrement() {
        this.aggrementVisible = true;
      },
      // 获取验证码
      getCode() {
        captcha({
          captcha_id: 'this.captcha.id'
        })
          .then(res => {
            if (res.code >= 0) {
              this.captcha = res.data;
              this.captcha.img = this.captcha.img.replace(/\r\n/g, '');
            }
          })
          .catch(err => {
            this.$message.error(err.message);
          });
      }
    }
  };
</script>
<style lang="scss" scoped>
  .register {
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 20px 0;
  }

  .box-card {
    width: 500px;
    margin: 0 auto;
    display: flex;
    background-color: #ffffff;
    padding: 0 30px 30px 30px;
    flex-direction: column;
    padding-bottom: 256px;

    .register-title {
      border-bottom: 1px solid #f1f1f1;
      text-align: left;
      margin-bottom: 20px;
      font-size: 16px;
      color: $base-color;
      padding: 10px 0;
    }

    .register-account {
      width: 100%;
      text-align: center;
    }

    .code {
      width: 80%;
      text-align: left;
    }

    .el-form {
      margin: 0 30px;

      .captcha {
        vertical-align: top;
        max-width: inherit;
        max-height: 38px;
        line-height: 38px;
        cursor: pointer;
      }
    }

    .xyContent {
      height: 600px;
      overflow-y: scroll;
    }

    .xy {
      margin-left: 110px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      text-align: left;
      margin-right: 30px;

      .toLogin {
        cursor: pointer;
      }

      .xy-wrap {
        display: flex;
        align-items: center;
        font-size: $ns-font-size-base;
        cursor: pointer;

        .iconfont {
          display: flex;
          align-content: center;
        }

        .content {
          margin-left: 3px;

          b {
            color: $base-color;
          }
        }
      }

      .icon-xuanze-duoxuan {
        color: $base-color;
      }
    }

    .el-button {
      margin: 20px 0 0 25px;
      background-color: $base-color;
      color: #ffffff;
      width: calc(100% - 60px);
    }
  }

  .floatLayer-wrap {
    height: 100%;
    width: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 999;
    position: absolute;

    .reward-wrap {
      width: 400px;
      height: auto;
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);

      .bg-img {
        width: 100%;
        will-change: transform;
      }

      .bg-img-head {
        position: absolute;
        top: -90px;
        width: 100%;
      }

      .bg-img-money {
        position: absolute;
        width: 100%;
        left: -20px;
        top: 80px;
        z-index: 10;
      }

      .wrap {
        width: calc(100% - 1px);
        height: 100%;
        background-color: #ef3030;
        margin-top: -40px;
        padding-bottom: 30px;
        border-bottom-left-radius: 5px;
        border-bottom-right-radius: 5px;

        & > div {
          position: relative;
        }
      }

      .reward-content {
        margin: 0 25px 0 25px;
      }

      .reward-item {
        .head {
          color: #fff;
          text-align: center;
          line-height: 1;
          margin: 10px 0;
        }

        .content {
          display: flex;
          padding: 8px 13px;
          background: #fff;
          border-radius: 5px;
          margin-bottom: 5px;
          width: auto;

          .info {
            flex: 1;
          }

          .tip {
            color: #ff222d;
            padding: 5px 0 5px 15px;
            width: 70px;
            letter-spacing: 1px;
            border-left: 1px dashed #e5e5e5;
            height: 40px;
            line-height: 40px;
          }

          .num {
            font-size: 26px;
            color: #ff222d;
            font-weight: bolder;
            line-height: 1;
          }

          .coupon-name {
            font-size: 19px;
          }

          .type {
            font-size: $ns-font-size-base;
            margin-left: 5px;
            line-height: 1;
          }

          .desc {
            margin-top: 4px;
            color: $base-color;
            font-size: $ns-font-size-base;
            line-height: 1;
          }
        }
      }

      .btn {
        position: absolute;
        width: calc(100% - 50px);
        bottom: 20px;
        left: 25px;

        .btn-img {
          width: 100%;
        }
      }
    }
  }

  .close-btn {
    text-align: center;
    margin-top: 20px;

    .iconfont {
      color: #fff;
      font-size: 20px;
    }
  }

  .clear {
    content: '';
    display: block;
    clear: both;
  }
</style>

<template>
  <div class="ns-login-wrap" :style="{ background: backgroundColor }" v-loading="loadingAd" @keypress="keypress">
    <div class="el-row-wrap el-row-wrap-login">
      <el-row>
        <el-col :span="13">
          <el-carousel height="460px" class="ns-login-bg" @change="handleChange" v-if="adList.length">
            <el-carousel-item v-for="item in adList" :key="item.adv_id">
              <el-image :src="$img(item.adv_image)" fit="cover" @click="$util.pushToTab(item.adv_url.url)" />
            </el-carousel-item>
          </el-carousel>
        </el-col>
        <el-col :span="11" class="ns-login-form" style="float:right;margin-right:70px">
          <div class="grid-content bg-purple">
            <el-tabs v-model="activeName" @tab-click="handleClick">
              <el-tab-pane label="账号登录" name="first" v-if="registerConfig.login.indexOf('username') != -1">
                <el-form v-if="activeName == 'first'" :model="formData" :rules="accountRules" ref="ruleForm">
                  <el-form-item prop="account">
                    <el-input v-model="formData.account" placeholder="请输入账号">
                      <template slot="prepend">
                        <i class="iconfont icon-zhanghao"></i>
                      </template>
                    </el-input>
                  </el-form-item>
                  <el-form-item prop="password">
                    <el-input type="password" v-model="formData.password" autocomplete="off" placeholder="请输入登录密码">
                      <template slot="prepend">
                        <i class="iconfont icon-mima"></i>
                      </template>
                    </el-input>
                  </el-form-item>
                  <el-form-item prop="vercode">
                    <el-input v-model="formData.vercode" autocomplete="off" placeholder="请输入验证码" maxlength="4">
                      <template slot="prepend">
                        <i class="iconfont icon-yanzhengma"></i>
                      </template>
                      <template slot="append">
                        <img :src="captcha.img" mode class="captcha" @click="getCaptcha" />
                      </template>
                    </el-input>
                  </el-form-item>
                  <el-form-item>
                    <el-row>
                      <el-col :span="12">
                        <el-checkbox v-model="formData.checked">七天自动登录</el-checkbox>
                      </el-col>
                      <el-col :span="12" class="ns-forget-pass">
                        <router-link to="/auth/find" class>忘记密码</router-link>
                      </el-col>
                    </el-row>
                  </el-form-item>
                  <el-form-item style="margin-bottom: 18px;">
                    <el-button type="primary" @click="accountLogin('ruleForm')">登录</el-button>
                  </el-form-item>

                  <el-form-item>
                    <el-row>
                      <el-col :span="24">
                        <div class="bg-purple-light" style="padding: 0 0 10px 0;" :style="wechatConfigStatus ? 'border-bottom: 1px solid #ebebeb' : ''">
                          没有账号？
                          <router-link to="/auth/register"><p style="color: #fd274a;">立即注册</p></router-link>
                          <!-- <i class="iconfont icon-arrow-right"></i> -->
                        </div>
                      </el-col>
                    </el-row>
                    <el-col :span="18" v-if="wechatConfigStatus">
                      <div style="margin-left: 100px; margin-top: 20px; position: relative;" class="go-wx-login iconfont icon-weixin-copy" @click="weixinLogin()">
                        <p style="font-size: 14px; text-indent: 10px; position: absolute;top: 1px;">使用微信扫码登录</p>
                      </div>
                    </el-col>
                  </el-form-item>
                </el-form>
              </el-tab-pane>

              <el-tab-pane label="手机动态码登录" name="second" v-if="registerConfig.login.indexOf('mobile') != -1">
                <el-form v-if="activeName == 'second'" :model="formData" :rules="mobileRules" ref="mobileRuleForm" class="ns-login-mobile">
                  <el-form-item prop="mobile">
                    <el-input v-model="formData.mobile" placeholder="请输入手机号">
                      <template slot="prepend">
                        <i class="iconfont icon-shouji-copy"></i>
                      </template>
                    </el-input>
                  </el-form-item>

                  <el-form-item prop="vercode">
                    <el-input v-model="formData.vercode" autocomplete="off" placeholder="请输入验证码" maxlength="4">
                      <template slot="prepend">
                        <i class="iconfont icon-yanzhengma"></i>
                      </template>
                      <template slot="append">
                        <img :src="captcha.img" mode class="captcha" @click="getCaptcha" />
                      </template>
                    </el-input>
                  </el-form-item>

                  <el-form-item prop="dynacode">
                    <el-input v-model="formData.dynacode" maxlength="4" placeholder="请输入短信动态码">
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

                  <el-form-item>
                    <el-button type="primary" @click="mobileLogin('mobileRuleForm')">登录</el-button>
                  </el-form-item>

                  <el-form-item>
                    <el-row>
                      <el-col :span="24">
                        <div class="bg-purple-light" style="padding: 0 0 10px 0;" :style="wechatConfigStatus ? 'border-bottom: 1px solid #ebebeb' : ''">
                          <router-link to="/auth/register">立即注册</router-link>
                          <i class="iconfont icon-arrow-right"></i>
                        </div>
                      </el-col>
                    </el-row>
                    <el-col :span="18" v-if="wechatConfigStatus">
                      <div style="margin-left: 100px; margin-top: 20px; position: relative;" class="go-wx-login iconfont icon-weixin-copy" @click="weixinLogin()">
                        <p style="font-size: 14px; text-indent: 10px; position: absolute;top: 1px;">使用微信扫码登录</p>
                      </div>
                    </el-col>
                  </el-form-item>
                </el-form>
              </el-tab-pane>
            </el-tabs>
          </div>
        </el-col>
        <div class="wx-login" :class="ischecked == true ? 'wx-login-display' : ''">
          <p class="wx-login-title">微信扫码登录</p>
          <div class="qrcode"><img :src="img" /></div>
          <div style="display: flex;">
            <p class="iconfont icon-arrowLeft" @click="closeWx()"></p>
            <p class="wx-login-footer" @click="closeWx()">使用账号密码登录</p>
          </div>
        </div>

        <div class="wx-login1" :class="ischecked1 == true ? 'wx-login-display1' : ''">
          <p class="wx-login-title1">扫码成功</p>

          <el-col :span="11" class="ns-login-form ns-login-form2">
            <div class="grid-content bg-purple">
              <el-form :model="formData" :rules="wechatRules" ref="wechatRuleForm">
                <el-form-item prop="mobile">
                  <el-input v-model="formData.mobile" placeholder="请输入手机号">
                    <template slot="prepend">
                      <i class="iconfont icon-shouji-copy"></i>
                    </template>
                  </el-input>
                </el-form-item>

                <el-form-item prop="vercode">
                  <el-input v-model="formData.vercode" autocomplete="off" placeholder="请输入验证码" maxlength="4">
                    <template slot="prepend">
                      <i class="iconfont icon-yanzhengma"></i>
                    </template>
                    <template slot="append">
                      <img :src="captcha.img" mode class="captcha" @click="getCaptcha" />
                    </template>
                  </el-input>
                </el-form-item>

                <el-form-item prop="dynacode">
                  <el-input v-model="formData.dynacode" maxlength="4" placeholder="请输入短信动态码">
                    <template slot="prepend">
                      <i class="iconfont icon-dongtaima"></i>
                    </template>
                    <template slot="append">
                      <div class="dynacode" :class="dynacodeData.seconds == 120 ? 'ns-text-color' : 'ns-text-color-gray'" @click="sendWechatMobileCode('wechatRuleForm')">
                        {{ dynacodeData.codeText }}
                      </div>
                    </template>
                  </el-input>
                </el-form-item>

                <el-form-item>
                  <el-button type="primary" @click="wechatLogin('wechatRuleForm')">确定</el-button>
                </el-form-item>

                <el-form-item>
                  <el-row>
                    <el-col :span="12">
                      <div class="go-wx-login iconfont" @click="closeWx1()">
                        <p>使用其他方式登录</p>
                      </div>
                    </el-col>
                    <el-col :span="12">
                      <div class="bg-purple-light">
                        <router-link to="/auth/register">立即注册</router-link>
                        <i class="iconfont icon-arrow-right"></i>
                      </div>
                    </el-col>
                  </el-row>
                </el-form-item>
              </el-form>
            </div>
          </el-col>
        </div>
      </el-row>
    </div>
  </div>
</template>

<script>
  import login from '~/assets/js/auth/login.js';

  export default {
    name: 'login',
    layout: 'login',
    mixins: [login]
  };
</script>
<style lang="scss" scoped>
  .icon-arrowLeft {
    line-height: 55px;
    font-size: 30px;
    margin-right: 10px;
    margin-left: 130px;
  }

  .ns-login-wrap {
    width: 100%;
    height: 500px;
    min-width: $width;

    .el-row-wrap-login {
      width: 1200px;
      margin: 0 auto;

      .ns-login-bg {
        margin-top: 40px;
      }

      .ns-login-form {
        width: 400px;
        margin-left: 50px;
        background: #ffffff;
        margin-top: 25px;

        .el-form {
          .captcha {
            vertical-align: top;
            max-width: inherit;
            max-height: 38px;
            line-height: 38px;
            cursor: pointer;
          }

          .dynacode {
            cursor: pointer;
          }

          [class*=' el-icon-'],
          [class^='el-icon-'] {
            font-size: 16px;
          }
        }

        .grid-content {
          padding: 10px 20px;
        }

        .el-form-item__error {
          padding-left: 50px;
        }

        button {
          width: 100%;
        }

        .ns-forget-pass {
          text-align: right;
        }

        i {
          font-size: 18px;
        }

        .bg-purple-light {
          display: flex;
          justify-content: flex-end;
          align-items: center;

          i {
            width: 16px;
            height: 16px;
            line-height: 16px;
            text-align: center;
            border-radius: 50%;
            background-color: $base-color;
            color: #ffffff;
            font-size: 12px;
            margin-left: 8px;
          }
        }
      }

      .ns-login-form2 {
        margin-left: 9px;
      }
    }
  }

  .icon-weixin-copy {
    color: #09bb07;
    font-size: 26px;

    p {
      font-size: 16px;
      color: #000;
      display: inline-block;
      padding-right: 15px;
    }
  }

  .go-wx-login {
    cursor: pointer;
  }

  .wx-login-display {
    display: block !important;
  }

  .wx-login {
    width: 410px;
    height: 460px;
    background: #fff;
    position: absolute;
    top: 22px;
    right: 90px;
    z-index: 10;
    display: none;

    .wx-login-title {
      text-align: center;
      margin-top: 30px;
      font-weight: 600;
      font-size: 16px;
      color: #fc183e;
    }

    img {
      width: 200px;
      margin: 45px 105px;
    }

    .wx-login-footer {
      margin-top: 15px;
      text-align: center;
      cursor: pointer;
    }
  }

  .wx-login-display1 {
    display: block !important;
  }

  .wx-login1 {
    width: 410px;
    height: 460px;
    background: #fff;
    position: absolute;
    top: 22px;
    right: 90px;
    z-index: 11;
    display: none;

    .wx-login-title1 {
      text-align: center;
      margin-top: 30px;
      font-weight: 600;
    }

    .wx-login-footer1 {
      margin-top: 15px;
      text-align: center;
      cursor: pointer;
    }
  }
</style>

<style lang="scss">
  .ns-login-form {
    .el-form-item__error {
      /* 错误提示信息 */
      padding-left: 57px;
    }

    .el-tabs__active-bar,
    .el-tabs__nav-wrap::after {
      /* 清除tab标签底部横线 */
      height: 0;
    }

    /* 立即注册 */
    .el-form-item__content {
      line-height: 20px;
    }
  }
</style>

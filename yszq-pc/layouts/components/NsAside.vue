<template>
  <aside class="main-sidebar clearfix">
    <div class="main-sidebar-body">
      <ul>
        <li @click="$router.push('/goods/cart')">
          <div class="li-item">
            <img src="~/assets/images/index/index_card.png" />
            <span>购物车</span>
            <em v-show="cartCount">{{ cartCount }}</em>
          </div>
        </li>
        <li class="mobile">
          <div class="mobile-qrcode-wrap">
            <div class="mobile-qrcode" v-if="qrcode">
              <img :src="$img(qrcode)" />
            </div>
          </div>
          <div class="li-item">
            <img src="~/assets/images/index/index_code.png" />
            <span>手机购买</span>
          </div>
        </li>
        <li class="kefuTip" v-if="serviceConfig.type != 'none'">
          <div class="li-item" @click="showServiceFn()">
            <img src="~/assets/images/index/index_service.png" />
            <span>联系客服</span>
          </div>
        </li>
        <li @click="$router.push('/member')">
          <div class="li-item">
            <img src="~/assets/images/index/index_member.png" />
            <span>会员中心</span>
          </div>
        </li>
      </ul>
      <div :class="['back-top',{ showBtn: visible }]" @click="toTop">
        <img src="~/assets/images/index/index_top.png">
        <span>回到顶部</span>
      </div>
    </div>
    <div class="main-sidebar-right">
      <div id="mainSidebarHistoryProduct" class="history-product"></div>
    </div>
    <!--联系客服弹窗-->
    <servicerMessage ref="servicer" class="kefu" :shop="{shop_id:0,logo:siteInfo.logo,shop_name:'平台客服'}">
    </servicerMessage>
  </aside>
</template>

<script>
  import servicerMessage from '@/components/message/servicerMessage.vue'
  import {
    mapGetters
  } from "vuex"
  import {
    shopServiceOpen
  } from "@/api/website.js"
  export default {
    props: {},
    data() {
      return {
        visible: false,
        hackReset: false,
        serviceConfig: {
          type: 'nome'
        },
      }
    },
    components: {
      servicerMessage
    },
    computed: {
      ...mapGetters(["wapQrcode", "cartCount", "siteInfo", 'member']),
      qrcode: function() {
        return this.wapQrcode === "" ? "" : this.wapQrcode.path.h5.img
      },
      logined: function() {
        return this.member !== undefined && this.member !== "" && this.member !== {}
      }
    },
    created() {
      this.$store.dispatch("site/qrCodes");
      this.shopServiceOpen();
    },
    mounted() {
      window.addEventListener("scroll", this.handleScroll)
    },
    beforeDestroy() {
      window.removeEventListener("scroll", this.handleScroll)
    },
    watch: {},
    methods: {
      handleScroll() {
        this.visible = window.pageYOffset > 300
      },
      shopServiceOpen() {
        shopServiceOpen().then((res) => {
          if (res && res.code == 0) {
            this.serviceConfig = res.data.pc;
          }
        })
      },
      toTop() {
        let timer = setInterval(function() {
          let osTop = document.documentElement.scrollTop || document.body.scrollTop
          let ispeed = Math.floor(-osTop / 5)
          document.documentElement.scrollTop = document.body.scrollTop = osTop + ispeed
          this.isTop = true
          if (osTop === 0) {
            clearInterval(timer)
          }
        }, 20)
      },
      // 打开客服弹窗
      showServiceFn() {
        if (this.logined) {
          switch (this.serviceConfig.type) {
            case 'third':
              window.open(this.serviceConfig.third_url, "_blank");
              break;
            case 'niushop':
              this.hackReset = true;
              this.$refs.servicer.show()
              break;
          }
        } else {
          this.$message({
            message: "您还未登录",
            type: "warning"
          })
        }

      }
    },
  }
</script>

<style scoped lang="scss">
  // 公共代码部分
  %flex-center {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
  }

  %li-temp {
    cursor: pointer;

    img {
      width: 23px;
      height: 23px;
    }

    span {
      margin-top: 9px;
      color: #000;
      line-height: 1;
      font-size: 12px;
    }
  }

  %arrows {
    &::after {
      content: "";
      position: absolute;
      right: 0px;
      top: 50%;
      transform: translate(-8px, -50%) rotate(45deg);
      height: 10px;
      width: 10px;
      background-color: #ff4649;
    }
  }

  // 具体代码
  .main-sidebar {
    position: fixed;
    top: 50%;
    transform: translateY(-50%);
    right: 148px;
    z-index: 400;

    .main-sidebar-body {
      ul {
        width: 88px;
        background-color: #fff;
        box-shadow: 0px 2px 12px 0px rgba(102, 128, 153, 0.16);
        border-radius: 8px;
        padding: 2px 0;

        li {
          .li-item {
            @extend %flex-center;
            position: relative;
            height: 85px;
            text-align: center;
            @extend %li-temp;

            em {
              position: absolute;
              top: 10px;
              right: 19px;
              min-width: 17px;
              height: 15px;
              line-height: 15px;
              display: inline-block;
              padding: 0 2px;
              color: #ffffff;
              font-size: 10px;
              font-style: normal;
              text-align: center;
              border-radius: 8px;
              background-color: $base-color;
            }

            &::after {
              content: "";
              position: absolute;
              left: 50%;
              transform: translateX(-50%);
              bottom: 0;
              width: 60px;
              height: 1px;
              background: #ECECEC;
            }
          }

          &:last-of-type>.li-item::after {
            background-color: transparent;
          }

          &.kefuTip {
            position: relative;
          }

          &.mobile {
            position: relative;

            .mobile-qrcode-wrap {
              display: none;
              position: absolute;
              left: -160px;
              top: -28px;
            }

            .mobile-qrcode {
              position: relative;
              margin-right: 10px;
              width: 150px;
              height: 150px;
              padding: 10px;
              background-color: #fff;
              border: 8px;
              box-sizing: border-box;
              box-shadow: 0px 2px 12px 0px rgba(102, 128, 153, 0.16);
              @extend %arrows;

              &::after {
                right: -13px;
                background-color: #fff;
                box-shadow: 0px 2px 12px 0px rgba(102, 128, 153, 0.16);
              }
            }

            &:hover .mobile-qrcode-wrap {
              display: block;

              img {
                width: 100%;
                height: 100%;
              }
            }
          }
        }
      }

      a,
      .cart,
      .el-button {
        width: 40px;
        height: 40px;
        line-height: 40px;
        display: block;
        margin-bottom: 10px;
        color: #ffffff;
        text-align: center;
        -webkit-transition: background-color 0.3s;
        transition: background-color 0.3s;
        padding: 0;
        border: none;
        background: transparent;

        &:hover {
          background-color: $base-color;
        }
      }

      .back-top {
        @extend %flex-center;
        display: none;
        margin-top: 10px;
        width: 88px;
        height: 88px;
        background-color: #fff;
        box-shadow: 0px 2px 12px 0px rgba(102, 128, 153, 0.16);
        border-radius: 8px;
        @extend %li-temp;
      }

      .showBtn {
        display: flex;
        opacity: 1;
        cursor: pointer;
      }

      i {
        font-size: 16px;
      }
    }
  }

  @media screen and (max-width: 1750px) {
    .main-sidebar {
      right: 100px;
    }
  }

  @media screen and (max-width: 1620px) {
    .main-sidebar {
      right: 10px;
    }
  }

  .kefuTip .tip {
    display: none;
  }

  .kefuTip:hover .tip {
    display: block;
    position: absolute;
    right: 85px;
    top: -20px;

    .tip_item {
      border-top-left-radius: 118px;
      border-top-right-radius: 118px;
      margin-right: 13px;
      width: 100px;
      background: #FF4649;
      color: #fff;
      padding-bottom: 1px;
      @extend %arrows;
    }

    .kefu_logo {
      width: 78px;

      margin: 0 auto 10px;
      border-radius: 50%;

      img {
        margin-top: 14px;
        background: linear-gradient(to top right, #e4e4e4, #FFF);
        border-radius: 50%;
        width: 100%;
        height: 78px
      }
    }

    .text {
      padding: 0 !important;
      background: #fff;
      margin: 0 10px 10px;
      color: #FF4649;
      border-radius: 3px;
      text-align: center;
      line-height: 30px;

    }
  }
</style>
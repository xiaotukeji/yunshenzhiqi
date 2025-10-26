<template>
  <div>
    <div class="header-top">
      <div class="top-content">
      <div class="top-left">
        <div class="left-item collect" @click="goHome">
          <span class="iconfont icon-shouye"></span>
          <span>首页</span>
        </div>
      </div>
       <div class="top-right">
          <div class="member-info" v-if="logined">
            <router-link to="/member">{{ member.nickname || member.username }}</router-link>
            <a @click="logout">退出</a>
          </div>
          <div class="member-info" v-if="!logined">
            <router-link to="/auth/login">登录</router-link>
            <router-link to="/auth/register">注册</router-link>
          </div>
          <router-link to="/cms/notice/list"><span class="announcement iconfont icon-xiaoxi"></span></router-link>
          <router-link to="/member/order_list">我的订单</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import {
    mapGetters
  } from "vuex"
  import {
    getToken
  } from "@/utils/auth"

  export default {
    props: {},
    data() {
      return {}
    },
    created() {
      this.$store.dispatch("member/member_detail")
      this.$store.dispatch("site/defaultFiles")
      this.$store.dispatch("site/addons")
    },
    mounted() {},
    watch: {},
    methods: {
      logout() {
        this.$store.dispatch("member/logout")
        this.$router.push('/');
      },
      //跳转首页
      goHome() {
        this.$router.push('/');
      }
    },
    components: {},
    computed: {
      ...mapGetters(["wapQrcode", "member", "addonIsExit"]),
      logined: function() {
        return this.member !== undefined && this.member !== "" && this.member !== {}
      }
    }
  }
</script>

<style scoped lang="scss">
  // 公共部分
  %line{
    content: "";
    position: absolute;
    left: -2px;
    top: 50%;
    transform: translateY(-50%);
    width: 1px;
    height: 12px;
  }
  .header-top {
    width: 100%;
    height: 44px;
    font-size: 12px;
    background-color: #242424;

    .el-dropdown {
      font-size: $ns-font-size-sm;
    }

    .top-content {
      display: flex;
      align-items: center;
      width: $width;
      height: 100%;
      margin: 0 auto;
      .left-item{
        &.collect{
		  display: flex;
		  align-items: center;
          cursor: pointer;
          color: #b4b4b4;
          span:nth-child(1){
            font-weight: bold;
			line-height: 1;
          }
          span:nth-child(2){
			margin-left: 5px;
			font-size: 14px;
			line-height: 1;
          }
		  &:hover{
			  color: $base-color;
		  }
        }
      }
      .top-right {
        display: flex;
        align-items: center;
        margin-left: auto;
        a {
          position: relative;
          padding: 20px 18px;
          color: #b4b4b4;
          font-size: 14px;
          &:hover {
            color: $base-color;
          }
          .iconfont{
            font-size: 18px;
            font-weight: bold;
            color: #fff;
          }
          .announcement{
            position: absolute;
            animation:mymove 3s infinite;
            position: absolute;
            top: 12px;
            left: 9px;
            line-height: 1;
          }
          @keyframes mymove {
           	5%,25%,45% {
              transform:rotate(8deg)
            }
            0%,10%,30%,50% {
              transform:rotate(-8deg)
            }
            15%,35%,55% {
              transform:rotate(4deg)
            }
            20%,40%,60% {
              transform:rotate(-4deg)
            }
            65%,to {
              transform:rotate(0deg)
            }
          }
        }

        &>a:nth-last-child(1):after,
        &>a:nth-last-child(2):after {
          @extend %line;
          background-color: #404040;
        }

        &>a:nth-last-child(2):after {
          left: 1px;
        }

        .member-info {
          padding: 0 10px;
          > a{
            position: relative;
            padding: 0;
            &:last-of-type{
              padding-left: 8px;
              margin-left: 2px;
              &::after{
                @extend %line;
                left: 0;
                background-color: #b4b4b4;
              }
            }
          }
        }
      }
    }
  }

  .mobile-qrcode {
    padding: 10px 0;
  }

  .router-link-active {
    color: $base-color;
  }
</style>

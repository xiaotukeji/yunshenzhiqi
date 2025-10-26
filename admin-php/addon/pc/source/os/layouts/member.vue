<template>
  <el-container>
    <!-- 头部 -->
    <el-header height="auto" class="header">
      <member-header />
    </el-header>
    <transition name="slide">
      <el-container class="member-content-wrap">
        <el-aside width="200px">
          <el-menu :default-active="activeIndex" class="menu" router @open="handleOpen"
            :default-openeds="defaultOpeneds" unique-opened>
            <el-submenu index="1" title>
              <template slot="title">
                <span>会员中心</span>
              </template>
              <el-menu-item index="/member">欢迎页</el-menu-item>
              <el-menu-item index="/member/info">个人信息</el-menu-item>
              <el-menu-item index="/member/security">账户安全</el-menu-item>
              <el-menu-item index="/member/delivery_address">收货地址</el-menu-item>
              <el-menu-item index="/member/collection">我的关注</el-menu-item>
              <el-menu-item index="/member/footprint">我的足迹</el-menu-item>
            </el-submenu>
            <el-submenu index="2" title>
              <template slot="title">
                <span>交易中心</span>
              </template>
              <el-menu-item index="/member/order_list">我的订单</el-menu-item>
              <el-menu-item index="/member/activist">退款/售后</el-menu-item>
            </el-submenu>
            <el-submenu index="3" title>
              <template slot="title">
                <span>账户中心</span>
              </template>
              <el-menu-item index="/member/account">账户余额</el-menu-item>
              <el-menu-item index="/member/withdrawal">提现记录</el-menu-item>
              <el-menu-item index="/member/coupon">我的优惠券</el-menu-item>
              <el-menu-item index="/member/my_point">我的积分</el-menu-item>
              <el-menu-item index="/member/account_list">账户列表</el-menu-item>
              <!-- <el-menu-item index="level">会员等级</el-menu-item> -->
            </el-submenu>
          </el-menu>
        </el-aside>
        <el-main class="member">
          <transition name="slide">
            <nuxt />
          </transition>
        </el-main>
      </el-container>
    </transition>

    <!-- 右侧栏 -->
    <ns-aside />
    <!-- 底部 -->
    <el-footer>
      <ns-footer />
    </el-footer>
  </el-container>
</template>
<script>
  import MemberHeader from "./components/MemberHeader"
  import NsHeader from "./components/NsHeader"
  import NsAside from "./components/NsAside"
  import NsFooter from "./components/NsFooter"
  export default {
    created() {
      this.activeIndex = this.$route.meta.parentRouter || this.$route.path
    },
    data: () => {
      return {
        defaultOpeneds: ["1"],
        activeIndex: "member",
      }
    },
    mounted() {},
    computed: {},
    watch: {
      $route(curr) {
        this.activeIndex = curr.meta.parentRouter || this.$route.path;
      }
    },
    methods: {
      handleOpen(key, keyPath) {
        this.defaultOpeneds = keyPath
      },
    },
    components: {
      MemberHeader,
      NsAside,
      NsFooter
    },
    middleware: 'auth',
    head() {
      return {
        title: this.$store.state.site.siteInfo.site_name
      }
    }
  }
</script>
<style lang="scss">
  html,
  body {
    background: #f7f7f7 !important;
  }
</style>
<style lang="scss" scoped>
  .header {
    padding: 0;
  }

  .member-content-wrap {
    max-width: $width;
    padding: 0;
    display: flex !important;
    margin: 20px auto;
  }

  .el-footer {
    padding: 0;
    height: auto !important;
    background-color: #fff;
  }

  .el-main {
    border-top: none;
  }

  .menu {
    min-height: 730px;
  }

  .member {
    margin-left: 15px;
    width: 0 !important;
    flex: 1;
  }
</style>

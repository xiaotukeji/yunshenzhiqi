<template>
  <el-container class="auth-container">
    <!-- 头部 -->
    <el-header class="ns-login-header">
      <div class="header-in">
        <!-- <ns-header-mid /> -->
        <el-row>
          <el-col :span="6">
            <router-link to="/" class="logo-wrap"><img :src="$img(siteInfo.logo)" alt /></router-link>
          </el-col>
        </el-row>
      </div>
    </el-header>
    <el-main>
      <transition name="slide"><nuxt /></transition>
    </el-main>
    <!-- 底部 -->
    <!-- <el-footer class="login-footer"><copy-right /></el-footer> -->
  </el-container>
</template>
<script>
import CopyRight from './components/CopyRight';
import NsHeaderMid from './components/NsHeaderMid';
import { mapGetters } from 'vuex';

export default {
  components: {
    CopyRight,
    NsHeaderMid
  },
  created() {
    this.$store.dispatch('site/siteInfo');
  },
  mounted() {},
  computed: {
    ...mapGetters(['siteInfo'])
  },
  watch: {},
  methods: {},
  head() {
    return {
      title: this.$store.state.site.siteInfo.site_name
    };
  }
};
</script>
<style lang="scss">
html,
body {
  background: #fff !important;
}
</style>
<style lang="scss" scoped>
.ns-login-header {
  height: 109px !important;
}
.header-in {
  width: $width;
  height: 89px;
  margin: 0 auto;
  padding-top: 20px;
  .logo-wrap {
    width: 240px;
    height: 68px;
    display: block;
    line-height: 68px;
    img {
      max-width: 100%;
      max-height: 100%;
    }
  }
}

.login-footer .footer-bottom p .footer-link,
.login-footer .footer-bottom p .el-link.el-link--default {
  color: #303133;
}

.login-footer .footer-bottom p .footer-link:hover {
  color: $base-color;
}
.el-main {
  width: 100% !important;
}
.auth-container {
  background: #fff;
  min-height: 100vh;
}
</style>

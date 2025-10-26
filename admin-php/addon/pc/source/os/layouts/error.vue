<template>
  <div class="error-wrap">
    <img src="@/assets/images/index/closed.png" />
    <h1 v-if="error.statusCode === 404">页面不存在</h1>
    <h1 v-else>应用发生错误异常</h1>
    <router-link to="/" class="ns-text-color">返回首页</router-link>
  </div>
</template>

<script>
  export default {
    props: ['error'],
    layout: 'empty',
    created() {},
    methods: {}
  }
</script>
<style lang="scss" scoped>
  .error-wrap {
    text-align: center;
    padding: 40px;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);

    i {
      font-size: 60px;
    }

    h2 {
      margin: 0 0 30px;
    }
  }
</style>
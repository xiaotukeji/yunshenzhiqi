<template>
  <nuxt />
</template>

<script>
  export default {
    head() {
      return {
        title: this.$store.state.site.siteInfo.site_name
      }
    }
  }
</script>

<style>
</style>

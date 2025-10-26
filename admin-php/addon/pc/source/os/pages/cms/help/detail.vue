<template>
  <div class="detail-wrap">
    <el-breadcrumb separator="/" class="path">
      <el-breadcrumb-item :to="{ path: '/' }" class="path-home">首页</el-breadcrumb-item>
      <el-breadcrumb-item :to="{ path: '/cms/help/list' }">帮助列表</el-breadcrumb-item>
      <el-breadcrumb-item class="path-help">帮助详情</el-breadcrumb-item>
    </el-breadcrumb>
    <div class="help-detail" v-loading="loading">
      <div class="title" @click="toLink">{{ detail.title }}</div>
      <div class="info">
        <div class="time">{{ $util.timeStampTurnTime(detail.create_time) }}</div>
      </div>
      <div class="content" v-html="detail.content"></div>
    </div>
  </div>
</template>

<script>
  import {
    mapGetters
  } from 'vuex';
  import {
    helpDetail
  } from '@/api/cms/help';

  export default {
    name: 'help_detail',
    components: {},
    data: () => {
      return {
        detail: [],
        loading: true
      };
    },
    created() {
      this.id = this.$route.query.id;
      this.getDetail();
    },
    computed: {
      ...mapGetters(['siteInfo'])
    },
    watch: {
      $route(curr) {
        this.id = curr.query.id;
        this.getDetail();
      }
    },
    methods: {
      getDetail() {
        helpDetail({
          id: this.id
        }).then(res => {
            if (res.code == 0) {
              if (res.data) {
                this.loading = false;
                this.detail = res.data;

                window.document.title = `${this.detail.title} - ${this.siteInfo.site_name}`;
              } else {
                this.$router.push({
                  path: '/cms/help/list'
                });
              }
            }
          }).catch(err => {
            this.loading = false;
            this.$message.error(err.message);
          });
      },
      toLink() {
        if (this.detail.link_address) {
          window.open(this.detail.link_address);
        }
      }
    }
  };
</script>
<style lang="scss" scoped>
  .detail-wrap {
    width: 1210px;
    margin: 20px auto;
    background-color: #fff;
  }

  .path {
    padding: 15px;
  }

  .help-detail {
    background-color: #ffffff;
    padding: 10px;
    border-radius: 5px;
    margin: 10px 0;

    .title {
      text-align: center;
      font-size: 18px;
      margin: 10px 0;
      cursor: pointer;
    }

    .info {
      // margin: 0 43px;
      border-bottom: 1px dotted #e9e9e9;

      .time {
        text-align: center;
        color: #838383;
        margin-bottom: 17px;
      }
    }

    .content {
      padding-top: 10px;
      // margin: 0 65px;
    }
  }
</style>

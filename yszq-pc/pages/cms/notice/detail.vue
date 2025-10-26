<template>
  <div class="notice-wrap">
    <el-breadcrumb separator="/" class="path">
      <el-breadcrumb-item :to="{ path: '/' }" class="path-home">首页</el-breadcrumb-item>
      <el-breadcrumb-item :to="{ path: '/cms/notice/list' }">公告列表</el-breadcrumb-item>
      <el-breadcrumb-item class="path-help">公告详情</el-breadcrumb-item>
    </el-breadcrumb>
    <div class="notice-detil" v-loading="loading">
      <div class="notice-info">
        <div class="title">{{ info.title }}</div>
        <div class="time">{{ $util.timeStampTurnTime(info.create_time) }}</div>
      </div>
      <div class="content" v-html="info.content"></div>
    </div>
  </div>
</template>

<script>
  import {mapGetters} from 'vuex';
  import {noticeDetail} from '@/api/cms/notice';

  export default {
    name: 'notice_detail',
    components: {},
    data: () => {
      return {
        info: {},
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
        noticeDetail({
          id: this.id
        }).then(res => {
          if (res.data) {
            this.info = res.data;
            this.loading = false;
            window.document.title = `${this.info.title} - ${this.siteInfo.site_name}`;
          } else {
            this.$router.push({
              path: '/cms/notice/list'
            });
          }
        }).catch(err => {
          this.loading = false;
          this.$message.error(err.message);
        });
      }
    }
  };
</script>
<style lang="scss" scoped>
  .notice-wrap {
    width: $width;
    margin: 0 auto 45px;
  }

  .notice-detil {
    background-color: #ffffff;
    min-height: 300px;
    margin: 10px 0;
    padding: 10px;

    .title {
      text-align: center;
      font-size: 18px;
      margin: 10px 0;
    }

    .time {
      text-align: center;
      color: #838383;
      margin-bottom: 17px;
    }

    .notice-info {
      margin: 0 43px;
      border-bottom: 1px dotted #e9e9e9;
    }

    .content {
      padding-top: 10px;
      margin: 0 65px;
    }
  }

  .path {
    padding: 15px 0;
  }
</style>

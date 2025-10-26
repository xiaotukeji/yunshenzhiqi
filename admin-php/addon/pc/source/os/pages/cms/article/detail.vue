<template>
  <div class="article-wrap">
    <el-breadcrumb separator="/" class="path">
      <el-breadcrumb-item :to="{ path: '/' }" class="path-home">首页</el-breadcrumb-item>
      <el-breadcrumb-item :to="{ path: '/cms/article/list' }">文章列表</el-breadcrumb-item>
      <el-breadcrumb-item class="path-help">文章详情</el-breadcrumb-item>
    </el-breadcrumb>
    <div class="article-detil" v-loading="loading">
      <div class="article-info">
        <div class="title">{{ info.article_title }}</div>
        <div class="flex-wrap">
          <div class="time">{{ $util.timeStampTurnTime(info.create_time) }}</div>
          <div class="num-wrap" v-if="info.is_show_read_num == 1">
            <img :src="$img('public/static/img/read.png')" />
            {{ info.initial_read_num + info.read_num }}
          </div>
          <div class="num-wrap" v-if="info.is_show_dianzan_num == 1">
            <img :src="$img('public/static/img/dianzan.png')" />
            <span>{{ info.initial_dianzan_num + info.dianzan_num }}</span>
          </div>
        </div>
      </div>
      <div class="content" v-html="info.article_content"></div>
    </div>
  </div>
</template>

<script>
  import {mapGetters} from 'vuex';
  import {articleDetail} from '@/api/cms/article';

  export default {
    name: 'article_detail',
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
        articleDetail({
          article_id: this.id
        }).then(res => {
          if (res.data) {
            this.info = res.data;
            this.loading = false;
            window.document.title = `${this.info.article_title} - ${this.siteInfo.site_name}`;
          } else {
            this.$router.push({
              path: '/cms/article/list'
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
  .article-wrap {
    width: $width;
    margin: 20px auto;
  }

  .article-detil {
    background-color: #ffffff;
    min-height: 300px;
    margin: 20px 0;
    padding: 10px;

    .title {
      text-align: center;
      font-size: 18px;
      margin: 10px 0;
    }

    .flex-wrap {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-bottom: 15px;

      .time {
        text-align: center;
        color: #838383;
      }

      .num-wrap {
        display: flex;
        align-items: center;
        color: #999;

        img {
          margin-left: 25px;
          width: 16px;
          height: 16px;
          margin-right: 3px;
          margin-bottom: 3px;
        }
      }
    }

    .article-info {
      margin: 0 43px;
      border-bottom: 1px dotted #e9e9e9;
    }

    .content {
      padding-top: 10px;
      margin: 0 65px;
    }
  }
</style>

<template>
  <div class="article-wrap">
    <el-breadcrumb separator="/" class="path">
      <el-breadcrumb-item :to="{ path: '/' }" class="path-home">首页</el-breadcrumb-item>
      <el-breadcrumb-item class="path-help">文章</el-breadcrumb-item>
    </el-breadcrumb>
    <div class="article" v-loading="loading">
      <div class="category-wrap">
        <div class="item" :class="{ 'ns-text-color': queryinfo.category_id == 0 }" @click="search(0)">全部</div>
        <div class="item" :class="{ 'ns-text-color': queryinfo.category_id == item.category_id }" v-for="item in categoryList" :key="item.category_id" @click="search(item.category_id)">{{ item.category_name }}</div>
      </div>
      <div class="list-wrap">
        <template v-if="articleList.length">
          <div class="item" v-for="(item, index) in articleList" :key="item.article_id" @click="toDetail(item.article_id)">
            <div class="info">
              <div class="title">{{ item.article_title }}</div>
              <div class="desc">{{ item.article_abstract }}</div>
              <div class="bottom-wrap">
                <div class="left" v-if="item.is_show_release_time == 1">
                  <span>{{ $util.timeStampTurnTime(item.create_time) }}</span>
                </div>
                <div class="right">
                  <div v-if="item.is_show_read_num == 1">
                    <img :src="$img('public/static/img/read.png')" />
                    {{ item.initial_read_num + item.read_num }}
                  </div>
                  <div v-if="item.is_show_dianzan_num == 1">
                    <img :src="$img('public/static/img/dianzan.png')" />
                    <span>{{ item.initial_dianzan_num + item.dianzan_num }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="img-wrap" v-if="item.cover_img">
              <img :src="$img(item.cover_img)" @error="imageError(index)" />
            </div>
          </div>
        </template>
        <template v-else>
          <div class="empty">暂无文章</div>
        </template>
      </div>
    </div>
    <div class="page">
      <el-pagination
        background
        :pager-count="5"
        :total="total"
        prev-text="上一页"
        next-text="下一页"
        :current-page.sync="queryinfo.page"
        :page-size.sync="queryinfo.page_size"
        @size-change="handlePageSizeChange"
        @current-change="handleCurrentPageChange"
        hide-on-single-page
      ></el-pagination>
    </div>
  </div>
</template>

<script>
  import {mapGetters} from 'vuex';
  import {articleCategoryList, getArticleList} from '@/api/cms/article';

  export default {
    name: 'article',
    components: {},
    data: () => {
      return {
        queryinfo: {
          page: 1,
          page_size: 10,
          category_id: 0
        },
        categoryList: [],
        articleList: [],
        total: 0,
        loading: true
      };
    },
    head() {
      return {
        title: '文章列表-' + this.$store.state.site.siteInfo.site_name
      };
    },
    created() {
      this.getCategoryList();
      this.getList();
    },
    computed: {
      ...mapGetters(['defaultArticleImage'])
    },
    methods: {
      toDetail(id) {
        this.$router.push({
          path: '/cms/article/detail',
          query: {id: id}
        });
      },
      getCategoryList() {
        articleCategoryList().then(res => {
          if (res.code == 0 && res.data) {
            this.categoryList = res.data;
          }
        });
      },
      search(category_id) {
        this.queryinfo.category_id = category_id;
        this.getList();
      },
      getList() {
        getArticleList(this.queryinfo).then(res => {
          if (res.code == 0 && res.data) {
            this.articleList = res.data.list;
            this.total = res.data.count;
          }
          this.loading = false;
        }).catch(err => {
          this.loading = false;
          this.$message.error(err.message);
        });
      },
      handlePageSizeChange(newsize) {
        this.queryinfo.page_size = newsize;
        this.getList();
      },
      handleCurrentPageChange(newnum) {
        this.queryinfo.page = newnum;
        this.getList();
      },
      imageError(index) {
        this.articleList[index].cover_img = '';
      }
    }
  };
</script>
<style lang="scss" scoped>
  .article-wrap {
    width: $width;
    margin: 20px auto;
  }

  .article {
    padding: 20px 0;
    min-height: 300px;
    display: flex;

    .category-wrap {
      width: 210px;
      min-height: 300px;
      background: #ffffff;
      flex-shrink: 0;
      padding-left: 25px;
      padding-right: 10px;

      .item {
        font-size: 15px;
        cursor: pointer;
        line-height: 40px;
        border-top: 1px solid #f1f1f1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        height: 40px;
        background: #ffffff;

        &:hover {
          color: $base-color;
        }

        &:first-child {
          border-top: none;
        }
      }
    }

    .list-wrap {
      padding: 0 20px;
      margin-left: 20px;
      background-color: #fff;
      flex: 1;

      .item {
        background: #fff;
        padding: 20px 0;
        cursor: pointer;
        border-bottom: 1px solid #eeeeee;
        display: flex;
        justify-content: space-between;

        .info {
          width: 100%;
        }

        .title {
          font-weight: 600;
          font-size: 20px;
          word-break: break-all;
          display: -webkit-box;
          -webkit-line-clamp: 2;
          -webkit-box-orient: vertical;
          overflow: hidden;

          &:hover {
            color: $base-color;
          }
        }

        .desc {
          line-height: 29px;
          font-size: 16px;
          word-break: break-all;
          display: -webkit-box;
          -webkit-line-clamp: 2;
          -webkit-box-orient: vertical;
          overflow: hidden;
          margin-top: 5px;
          color: #666;
          height: 55px;
        }

        .bottom-wrap {
          display: flex;
          margin-top: 15px;

          .left {
            display: flex;
            align-items: center;
            color: #eee;
            margin-right: 10px;

            span {
              color: #999;
              font-size: 14px;
            }
          }

          .right {
            display: flex;
            align-items: center;

            & > div {
              margin-left: 25px;
              color: #999;
            }

            img {
              width: 16px;
              height: 16px;
              margin-right: 3px;
              margin-bottom: 3px;
            }
          }
        }

        .img-wrap {
          display: flex;
          margin-left: 15px;

          img {
            width: 256px;
            height: 160px;
            border-radius: 4px;
          }
        }
      }
    }

    .empty {
      font-size: 18px;
      text-align: center;
      line-height: 300px;
    }
  }
</style>

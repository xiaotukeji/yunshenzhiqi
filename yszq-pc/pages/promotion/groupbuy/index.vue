<template>
  <div class="ns-groupbuy">
    <div class="ns-groupbuy-head">
      <el-carousel height="420px" v-loading="loadingAd" v-if="adList.length">
        <el-carousel-item v-for="item in adList" :key="item.adv_id">
          <el-image :src="$img(item.adv_image)" fit="cover" @click="$util.pushToTab(item.adv_url.url)" />
        </el-carousel-item>
      </el-carousel>
    </div>

    <!-- 商品列表 -->
    <div class="ns-groupbuy-box" v-loading="loading">
      <div class="ns-groupbuy-title" v-if="goodsList.length">
        <span class="groupbuy-title-left">团购专区</span>
      </div>

      <div class="goods-list" v-if="goodsList.length">
        <div class="goods" v-for="(item,index) in goodsList" :key="item.groupbuy_id" @click="$router.push('/promotion/groupbuy/' + item.groupbuy_id)">
          <!-- 商品图片区 -->
          <div class="img">
            <el-image fit="scale-down" :src="$img(item.goods_image, { size: 'mid' })" lazy @error="imageError(index)"/>
          </div>

          <!-- 商品名称 -->
          <div class="name">
            <p :title="item.goods_name">{{ item.goods_name }}</p>
          </div>

          <!-- 价格展示区 -->
          <div class="price">
            <div class="curr-price">
              <span>团购价</span>
              <span>￥</span>
              <span class="main_price">{{ item.groupbuy_price }}</span>
            </div>
            <span class="primary_price">￥{{ item.price }}</span>
          </div>
          <el-button>立即拼购</el-button>
        </div>
      </div>
      <div v-else-if="!loading" class="empty-wrap">
        <img src="~assets/images/goods_empty.png">
        <span>暂无正在进行团购的商品，<router-link to="/" class="ns-text-color">去首页</router-link>看看吧</span>
      </div>

      <div class="pager">
        <el-pagination
          background
          :pager-count="5"
          :total="total"
          prev-text="上一页"
          next-text="下一页"
          :current-page.sync="currentPage"
          :page-size.sync="pageSize"
          @size-change="handlePageSizeChange"
          @current-change="handleCurrentPageChange"
          hide-on-single-page
        ></el-pagination>
      </div>
    </div>
  </div>
</template>

<script>
  import {goodsPage} from '@/api/groupbuy';
  import {mapGetters} from 'vuex';
  import {adList} from '@/api/website';

  export default {
    name: 'groupbuy',
    data: () => {
      return {
        loading: true,
        goodsList: [],
        total: 0,
        currentPage: 1,
        pageSize: 25,
        loadingAd: true,
        adList: []
      };
    },
    created() {
      if (this.addonIsExit && this.addonIsExit.groupbuy != 1) {
        this.$message({
          message: '团购插件未安装',
          type: 'warning',
          duration: 2000,
          onClose: () => {
            this.$route.push('/');
          }
        });
      } else {
        this.getAdList();
        this.getGoodsList();
      }
    },
    computed: {
      ...mapGetters(['defaultGoodsImage', 'addonIsExit'])
    },
    head() {
      return {
        title: '团购专区-' + this.$store.state.site.siteInfo.site_name,
        meta: [{
          name: 'description',
          content: this.$store.state.site.siteInfo.seo_description
        }, {
          name: 'keyword',
          content: this.$store.state.site.siteInfo.seo_keywords
        }]
      }
    },
    watch: {
      addonIsExit() {
        if (this.addonIsExit.groupbuy != 1) {
          this.$message({
            message: '团购插件未安装',
            type: 'warning',
            duration: 2000,
            onClose: () => {
              this.$route.push('/');
            }
          });
        }
      }
    },
    methods: {
      getAdList() {
        adList({keyword: 'NS_PC_GROUPBUY'}).then(res => {
          this.adList = res.data.adv_list;
          for (let i = 0; i < this.adList.length; i++) {
            if (this.adList[i].adv_url) this.adList[i].adv_url = JSON.parse(this.adList[i].adv_url);
          }
          this.loadingAd = false;
        }).catch(err => {
          this.loadingAd = false;
        });
      },
      /**
       * 团购商品
       */
      getGoodsList() {
        goodsPage({
          page_size: this.pageSize,
          page: this.currentPage
        }).then(res => {
          this.goodsList = res.data.list;
          this.total = res.data.count;
          this.loading = false;
        }).catch(err => {
          this.loading = false;
          this.$message.error(err.message);
        });
      },
      handlePageSizeChange(size) {
        this.pageSize = size;
        this.refresh();
      },
      handleCurrentPageChange(page) {
        this.currentPage = page;
        this.refresh();
      },
      refresh() {
        this.loading = true;
        this.getGoodsList();
      },
      /**
       * 图片加载失败
       */
      imageError(index) {
        this.goodsList[index].goods_image = this.defaultGoodsImage;
      }
    }
  };
</script>
<style lang="scss" scoped>
  .ns-groupbuy {
    .ns-groupbuy-box {
      padding: 48px 0;
      width: $width;
      margin: 0 auto;

      .ns-groupbuy-title {
        display: flex;
        align-items: center;
        border-bottom: 1px solid #e8e8e8;

        .groupbuy-title-left {
          font-size: 30px;
          color: #333;
          border-bottom: 4px solid #F42424;
        }
      }
    }

    .goods-list {
      display: flex;
      flex-direction: row;
      flex-wrap: wrap;
      justify-content: flex-start;
      margin-top: 32px;

      .goods {
        margin-right: 17px;
        margin-bottom: 18px;
        width: 228px;
        background-color: #ffffff;
        overflow: hidden;
        color: #303133;
        transition: 0.3s;
        padding: 18px;
        box-sizing: border-box;
        cursor: pointer;

        &:nth-child(5n+5) {
          margin-right: 0;
        }
      }

      .img {
        width: 192px;
        height: 192px;

        .el-image {
          max-width: 100%;
          max-height: 100%;
        }
      }

      .price {
        display: flex;
        align-items: baseline;

        .curr-price {
          display: flex;
          align-items: baseline;
          height: 24px;
          font-weight: bold;
          color: $base-color;

          span:first-child {
            font-size: 14px;
            margin-right: 1px;
          }

          span:nth-child(2) {
            font-size: 12px;
          }
        }

        .main_price {
          color: $base-color;
          font-size: 14px;
          line-height: 24px;
        }

        .primary_price {
          text-decoration: line-through;
          color: $base-color-info;
          margin-left: 5px;
          font-size: 12px;
        }
      }

      .el-button {
        width: 100%;
        height: 42px;
        line-height: 42px;
        background: $base-color;
        color: #ffffff;
        margin-top: 10px;
        border: none;
        border-radius: 0;
        padding: 0;
        font-size: 16px;
      }

      .name {
        margin-top: 12px;
        font-size: 14px;
        line-height: 1.4;
        margin-bottom: 5px;
        white-space: normal;
        overflow: hidden;

        p {
          line-height: 24px;
          display: -webkit-box;
          -webkit-box-orient: vertical;
          -webkit-line-clamp: 2;
          overflow: hidden;
          height: 50px;
        }
      }
    }

    .empty-wrap {
      padding: 50px 0 60px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      box-sizing: border-box;

      img {
        width: 400px;
        height: 244px;
      }

      span {
        font-size: 14px;
        color: #4A4A4A;
      }
    }
  }
</style>
<style lang="scss">
  .ns-groupbuy {
    .ns-groupbuy-head {
      width: $width;
      margin: auto;
    }

    .el-carousel {
      .el-image {
        height: 100%;
      }

      .el-image__inner {
        width: auto;
      }
    }

    .el-carousel__arrow--right {
      right: 60px;
    }
  }
</style>

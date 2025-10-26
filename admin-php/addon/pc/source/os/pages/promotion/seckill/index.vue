<template>
  <div class="ns-seckill">
    <div class="ns-seckill-head" v-loading="loadingAd">
      <el-carousel height="420px" v-if="adList.length" indicator-position="none">
        <el-carousel-item v-for="item in adList" :key="item.adv_id">
          <el-image :src="$img(item.adv_image)" @click="$util.pushToTab(item.adv_url.url)" />
        </el-carousel-item>
      </el-carousel>

      <div class="ns-seckill-time-box" v-if="timeList.length > 0">
        <span v-if="timeList.length > 4" class="left-btn el-icon-arrow-left" @click="changeThumbImg('prev')"></span>
        <span v-if="timeList.length > 4" class="right-btn el-icon-arrow-right" @click="changeThumbImg('next')"></span>
        <div class="ns-seckill-time-list" ref="seckillTime">
          <ul class="seckill-time-ul" :style="{ left: thumbPosition + 'px' }">
            <!-- 商品缩率图 -->
            <li v-for="(item, key) in timeList" :key="key" slot="label" class="seckill-time-li" :class="{ 'selected-tab': seckillId == item.id }" @click="handleSelected(key, item)">
              <div>{{ item.seckill_start_time_show + " - " + item.seckill_end_time_show }}</div>
              <div v-if="item.type == 'today'">
                <p class="em font-size-tag " v-if="!item.isNow">即将开始</p>
                <p class="em font-size-tag " v-else-if="item.isNow">抢购中</p>
              </div>
              <div v-else>
                <p>敬请期待</p>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- 商品列表 -->
    <div class="ns-seckill-box" v-loading="loading" ref="seckillGoods" v-if="timeList.length > 0 && goodsList.length > 0">
      <div class="ns-seckill-title">
        <div class="seckill-title-left">
          <span class="name">限时秒杀</span>
          <span class="desc">限时秒杀，每款限购一件</span>
        </div>

        <div class="ns-seckill-right" v-show="seckillIndex == index && isTrue && isNoClick == false">
          <span>{{ seckillText }}</span>
          <count-down class="count-down" v-on:start_callback="countDownS_cb()" v-on:end_callback="countDownE_cb()"
                      :currentTime="seckillTimeMachine.currentTime" :startTime="seckillTimeMachine.startTime"
                      :endTime="seckillTimeMachine.endTime" :dayTxt="'：'" :hourTxt="'：'" :minutesTxt="'：'"
                      :secondsTxt="''">
          </count-down>
        </div>
      </div>

      <div>
        <div class="goods-list">
          <div class="goods" v-for="(item, key) in goodsList" :key="key" @click="toGoodsDetail(item.id)">
            <div class="img">
              <el-image fit="scale-down" :src="$img(item.goods_image, { size: 'mid' })" lazy @error="imageError(index)"/>
            </div>
            <div class="name">
              <p :title="item.goods_name">{{ item.goods_name }}</p>
            </div>

            <!-- 价格展示区 -->
            <div class="price">
              <div class="curr-price">
                <span>秒杀价</span>
                <span>￥</span>
                <span class="main_price">{{ item.seckill_price }}</span>
              </div>
              <span class="primary_price">￥{{ item.price }}</span>
            </div>

            <el-button v-if="seckillIndex == index && timeList[index].isNow && shouType == true">立即抢购</el-button>
          </div>
        </div>
      </div>

      <div class="pager">
        <el-pagination background :pager-count="5" :total="total" prev-text="上一页" next-text="下一页"
                       :current-page.sync="currentPage" :page-size.sync="pageSize" @size-change="handlePageSizeChange"
                       @current-change="handleCurrentPageChange" hide-on-single-page></el-pagination>
      </div>
    </div>
    <div class="empty-wrap" v-else-if="!loading">
      <img src="~assets/images/goods_empty.png">
      <span>暂无正在进行秒杀的商品，<router-link to="/" class="ns-text-color">去首页</router-link>看看吧</span>
    </div>
  </div>
</template>

<script>
  import list from "@/assets/js/promotion/list_seckill.js"

  export default {
    name: "seckill",
    components: {},
    mixins: [list]
  }
</script>
<style lang="scss" scoped>
  @import "@/assets/css/promotion/list_seckill.scss";
</style>

<style lang="scss">
  .seckill-time {
    .el-tabs__nav-wrap {
      height: 56px;

      .el-tabs__nav {
        height: 56px;
      }

      .el-tabs__nav-next,
      .el-tabs__nav-prev {
        line-height: 56px;
      }

      .el-tabs__item {
        width: 150px;
        height: 56px;
        padding: 0;
      }
    }

    .el-tabs__nav-wrap::after {
      height: 0;
    }
  }

  .ns-seckill {
    .el-carousel {
      .el-image__inner {
        width: auto;
      }
    }

    .el-carousel__arrow--right {
      right: 60px;
    }

    .count-down {
      span {
        display: inline-block;
        width: 32px;
        height: 32px;
        line-height: 32px;
        text-align: center;
        background: #383838;
        color: #fff;
        border-radius: 6px;
        font-size: 20px;
      }
    }
  }
</style>

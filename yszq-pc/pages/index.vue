<template>
  <div>
    <!-- 首页固定区 -->
    <div class="index-wrap" :style="{ background: backgroundColor }" v-if="adList.length">
      <div class="index">
        <div class="banner">
          <el-carousel height="500px" arrow="never" v-loading="loadingAd" @change="handleChange">
            <el-carousel-item v-for="item in adList" :key="item.adv_id">
              <el-image :src="$img(item.adv_image)" fit="cover" @click="$util.pushToTab(item.adv_url.url)" />
            </el-carousel-item>
          </el-carousel>
        </div>
      </div>
    </div>

    <div class="index-content-wrap">
      <!-- 首页中部广告位 -->
      <ul class="adv-middle" v-if="adCenterList.length">
        <li class="adv-middle-item" v-for="(item,index) in adCenterList" :key="index">
          <el-image :src="$img(item.adv_image)" fit="cover" @error="adCenterImageError(index)" @click="$util.pushToTab(item.adv_url.url)" />
        </li>
      </ul>

      <!-- 广告 -->
      <div class="content-div" v-if="adLeftList.length > 0 || adRightList.length > 0">
        <div class="ad-wrap">
          <div class="ad-big" v-if="adLeftList.length > 0">
            <div class="ad-big-img" v-for="(item, index) in adLeftList" :key="index">
              <el-image :src="$img(item.adv_image)" fit="cover" @error="adLeftImageError(index)" @click="$util.pushToTab(item.adv_url.url)" />
            </div>
          </div>
          <div class="ad-small" v-if="adRightList.length > 0">
            <div class="ad-small-img" v-for="(item, index) in adRightList" :key="index">
              <el-image :src="$img(item.adv_image)" fit="cover" @error="adRightImageError(index)" @click="$util.pushToTab(item.adv_url.url)" />
            </div>
          </div>
        </div>
      </div>

      <!-- 限时秒杀 -->
      <div class="content-div" v-if="addonIsExit.seckill == 1 && listData.length > 0">
        <div class="seckill-wrap">
          <div class="seckill-time">
            <div class="seckill-time-left">
              <i class="iconfont icon-miaosha1 ns-text-color"></i>
              <span class="seckill-time-title ns-text-color">限时秒杀</span>
              <span>{{ seckillText }}</span>
              <count-down class="count-down" v-on:start_callback="countDownS_cb()" v-on:end_callback="countDownE_cb()"
                          :currentTime="seckillTimeMachine.currentTime" :startTime="seckillTimeMachine.startTime"
                          :endTime="seckillTimeMachine.endTime" :dayTxt="'：'" :hourTxt="'：'" :minutesTxt="'：'"
                          :secondsTxt="''">
              </count-down>
            </div>
            <div class="seckill-time-right" @click="$router.push('/promotion/seckill')">
              <span>更多商品</span>
              <i class="iconfont icon-arrow-right"></i>
            </div>
          </div>
          <div class="seckill-content">
            <vue-seamless-scroll :data="listData" :class-option="optionLeft" class="seamless-warp2">
              <ul class="item" :style="{ width: 250 * listData.length + 'px' }">
                <li v-for="(item, index) in listData" :key="index">
                  <div class="seckill-goods" @click="$router.push('/promotion/seckill/' + item.id)">
                    <div class="seckill-goods-img">
                      <img :src="$img(item.goods_image.split(',')[0], { size: 'mid' })" @error="imageError(index)" />
                    </div>
                    <p>{{ item.goods_name }}</p>
                    <div class="seckill-price-wrap">
                      <p class="ns-text-color">
                        ￥
                        <span>{{ item.seckill_price }}</span>
                      </p>
                      <p class="primary-price">￥{{ item.price }}</p>
                    </div>
                  </div>
                </li>
              </ul>
            </vue-seamless-scroll>
          </div>
        </div>
      </div>

      <!-- 楼层区 -->
      <div class="content-div">
        <div class="floor">
          <div v-for="(item, index) in floorList" :key="index" class="floor_item">
            <floor-style-1 v-if="item.block_name == 'floor-style-1'" :data="item" />
            <floor-style-2 v-if="item.block_name == 'floor-style-2'" :data="item" />
            <floor-style-3 v-if="item.block_name == 'floor-style-3'" :data="item" />
            <floor-style-4 v-if="item.block_name == 'floor-style-4'" :data="item" />
          </div>
        </div>
      </div>

      <!-- 浮层区 - 已移除弹出广告 -->

      <!-- 悬浮搜索 -->
      <div class="fixed-box" :style="{ display: isShow ? 'block' : 'none' }">
        <ns-header-mid />
      </div>
    </div>
  </div>
</template>

<script>
  import {
    websiteInfo
  } from "@/api/website"
  import floorStyle1 from './index/components/floor-style-1.vue';
  import floorStyle2 from './index/components/floor-style-2.vue';
  import floorStyle3 from './index/components/floor-style-3.vue';
  import floorStyle4 from './index/components/floor-style-4.vue';
  import index from '@/assets/js/index/index.js';
  import NsHeaderMid from "@/layouts/components/NsHeaderMid.vue"
  import vueSeamlessScroll from 'vue-seamless-scroll'
  import {
    mapGetters
  } from 'vuex';

  export default {
    components: {
      floorStyle1,
      floorStyle2,
      floorStyle3,
      floorStyle4,
      NsHeaderMid,
      vueSeamlessScroll
    },
    computed: {
      ...mapGetters(['siteInfo'])
    },
    mixins: [index],
    async fetch({store, params}) {
      await store.dispatch('site/siteInfo')
    },
    head() {
      return {
        title: this.siteInfo.seo_title ? this.siteInfo.seo_title : this.siteInfo.site_title,
        meta: [{
          name: 'description',
          content: this.siteInfo.seo_description
        },
          {
            name: 'keyword',
            content: this.siteInfo.seo_keywords
          },
          {
            property: 'og:title',
            content: this.siteInfo.seo_title
          },
          {
            property: 'og:description',
            content: this.siteInfo.seo_description
          },
          {
            property: 'og:type',
            content: 'website'
          }
        ]
      }
    }
  }
</script>

<style lang="scss" scoped>
  @import '@/assets/css/index/index.scss';
  .count-down {
    span {
      display: inline-block;
      width: 22px;
      height: 22px;
      line-height: 22px;
      text-align: center;
      background: #383838;
      color: #ffffff;
      border-radius: 2px;
    }
  }
</style>

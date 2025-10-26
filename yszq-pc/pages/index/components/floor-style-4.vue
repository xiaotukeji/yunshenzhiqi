<template>
  <div class="floor-style-4">
    <div class="head-wrap" v-if="data.value.title.value.text">
      <h2 @click="$util.pushToTab(data.value.title.value.link.url)" :style="{ color: data.value.title.value.color }">{{ data.value.title.value.text }}</h2>
      <div class="more" @click="$util.pushToTab(data.value.more.value.link.url)" :style="{color: data.value.more.value.color}">
        <span>{{data.value.more.value.text}}</span>
        <i class="el-icon-arrow-right"></i>
      </div>
    </div>
    <div class="body-wrap">
      <div class="left-wrap" v-if="data.value.leftImg.value.url">
        <img :src="$img(data.value.leftImg.value.url)" @click="$util.pushToTab(data.value.leftImg.value.link.url)" />
      </div>
      <el-carousel trigger="click" height="324px" indicator-position="none" arrow="never">
        <el-carousel-item v-for="num in itemNum" :key="num">
          <ul class="goods-list">
            <li v-for="(item, index) in itemList(num)" :key="index" :title="item.goods_name" @click="goSku(item.sku_id)">
              <div class="img-wrap">
                <img alt="商品图片" :src="$img(item.goods_image.split(',')[0], { size: 'mid' })" @error="imageError(index)" />
              </div>
              <div class="price">
                <span class="num">￥{{ item.discount_price }}</span>
                <del v-show="Number.parseInt(item.market_price)">￥{{ item.market_price }}</del>
              </div>
              <h3 class="name">{{ item.goods_name }}</h3>
              <div class="other-info" v-if="item.sale_num">
                <span>已售{{ item.sale_num }}件</span>
              </div>
            </li>
          </ul>
        </el-carousel-item>
      </el-carousel>
    </div>
    <div class="bottom-wrap" v-if="data.value.bottomImg.value.url">
      <img :src="$img(data.value.bottomImg.value.url)" @click="$util.pushToTab(data.value.bottomImg.value.link.url)" />
    </div>
  </div>
</template>

<script>
  import {mapGetters} from 'vuex';

  export default {
    name: 'floor-style-4',
    props: {
      data: {
        type: Object
      }
    },
    data() {
      return {};
    },
    created() {
    },
    computed: {
      ...mapGetters(['defaultGoodsImage']),
      goodsList() {
        let arr = [];
        try {
          arr = this.data.value.goodsList.value.list;
        } catch (e) {
          arr = [];
        }
        return arr;
      },
      itemNum() {
        let [num, listLen] = [0, this.goodsList.length];
        num = parseInt(listLen / 3);
        if (parseInt(listLen % 3) > 0) num += 1;
        return num;
      }
    },
    methods: {
      goSku(skuId) {
        this.$util.pushToTab('/sku/' + skuId);
      },
      imageError(index) {
        this.data.value.goodsList.value.list[index].goods_image = this.defaultGoodsImage;
      },
      itemList(index) {
        index -= 1;
        if (!this.goodsList.length) return [];
        let [start, end, arr] = [index * 3, index * 3 + 3, []];
        arr = this.goodsList.slice(start, end);
        return arr;
      }
    }
  };
</script>

<style lang="scss" scoped>
  .floor-style-4 {
    .head-wrap {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .head-wrap h2 {
      line-height: 30px;
      color: #333;
      padding: 10px;
      font-size: 18px;
      cursor: pointer;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .body-wrap {
      display: flex;

      .left-wrap {
        margin-right: 15px;
        width: 478px;
        height: 324px;
        cursor: pointer;
        transition: all 0.2s linear;

        &:hover {
          z-index: 2;
          -webkit-box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
          box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
          -webkit-transform: translate3d(0, -2px, 0);
          transform: translate3d(0, -2px, 0);
        }

        img {
          max-width: 100%;
          cursor: pointer;
        }
      }

      .el-carousel {
        flex: 1;
      }

      .goods-list {
        display: flex;

        li {
          position: relative;
          width: 228px;
          height: 324px;
          margin-right: 15px;
          background-color: #fff;
          cursor: pointer;
          padding: 10px 18px;
          transition: all 0.2s linear;
          box-sizing: border-box;

          &:last-of-type {
            margin-right: 0;
          }

          &:hover {
            z-index: 2;
            -webkit-box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            -webkit-transform: translate3d(0, -2px, 0);
            transform: translate3d(0, -2px, 0);
          }

          .img-wrap {
            width: 168px;
            height: 168px;
            line-height: 168px;
            margin: 0 auto 18px;

            img {
              max-width: 100%;
              max-height: 100%;
            }
          }

          .name {
            margin-top: 5px;
            font-size: 14px;
            color: #444;
            line-height: 1.5;
            font-weight: normal;
            @extend .multi-hidden;
          }

          .other-info {
            position: absolute;
            bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;

            span {
              font-size: 14px;
              color: #aaa;
            }
          }

          .price {
            .num {
              color: $base-color;
              font-size: 20px;
              font-weight: bold;
            }

            del {
              font-size: 14px;
              margin-left: 15px;
              color: #aaa;
            }
          }
        }
      }
    }

    .bottom-wrap {
      margin-top: 35px;
      width: $width;
      cursor: pointer;
      overflow: hidden;

      img {
        max-width: 100%;
      }
    }
  }
</style>

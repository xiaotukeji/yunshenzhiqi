<template>
  <div class="box">
    <div class="null-page" v-show="yes"></div>

    <div class="collection" v-loading="loading">
      <el-tabs v-model="activeName" @tab-click="handleClick">
        <el-tab-pane label="宝贝" name="goods">
          <div v-if="goodsList.length > 0">
            <div class="goods">
              <div class="goods-wrap" v-for="(item, index) in goodsList" :key="item.goods_id">
                <div class="goods-item">
                  <div class="img" @click="$util.pushToTab({ path: '/sku/' + item.sku_id })">
                    <img :src="$img(item.goods_image.split(',')[0], { size: 'mid' })" @error="imageError(index)" />
                    <i class="del el-icon-delete" @click.stop="deleteGoods(item.goods_id)"></i>
                  </div>
                  <div class="goods-name">{{ item.goods_name }}</div>
                  <div class="price">￥{{ item.price }}</div>
                </div>
              </div>
            </div>
            <div class="pager">
              <el-pagination @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page="goodsInfo.page" :page-size="goodsInfo.page_size" background :pager-count="5" prev-text="上一页" next-text="下一页" hide-on-single-page :total="goodsTotal"></el-pagination>
            </div>
          </div>
          <div v-else-if="!loading && !goodsList.length" class="empty">您还没有关注商品哦</div>
        </el-tab-pane>
      </el-tabs>
    </div>

    <div class="goods-recommended">
      <div class="youLike">
        <span>猜你喜欢</span>
      </div>
      <div class="body-wrap">
        <ul class="goods-list">
          <li v-for="(item, index) in list" :key="index" :title="item.goods_name" @click="$util.pushToTab({ path: '/sku/' + item.sku_id })">
            <div class="img-wrap">
              <img alt="商品图片" :src="$img(item.goods_image.split(',')[0], {size: 'mid'})" @error="imageImgError(index)" />
            </div>
            <h3>{{ item.goods_name }}</h3>
            <p class="price">
              <span class="num">{{ item.discount_price }}元</span>
              <del>{{ item.market_price }}元</del>
            </p>
          </li>
        </ul>
      </div>
    </div>

  </div>
</template>

<script>
  import {
    goodsCollect,
    deleteGoods
  } from "@/api/member/collection"
  import {
    mapGetters
  } from "vuex"
  import {
    goodsRecommend
  } from '@/api/goods/goods';

  export default {
    name: "collection",
    layout: "member",
    components: {},
    data() {
      return {
        goodsInfo: {
          page: 1,
          page_size: 10
        },
        shopInfo: {
          page: 1,
          page_size: 10
        },
        activeName: "goods",
        goodsTotal: 0,
        goodsList: [],
        loading: true,
        yes: true,
        list: [],
        page: 1,
        pageSize: 5
      }
    },
    created() {
      this.getGoodsCollect()
      this.getGoodsRecommend()
    },
    computed: {
      ...mapGetters(["defaultGoodsImage"])
    },
    mounted() {
      let self = this;
      setTimeout(function () {
        self.yes = false
      }, 300)
    },
    methods: {
      // 获取推荐商品列列表
      getGoodsRecommend() {
        goodsRecommend({
          page: this.page,
          page_size: this.pageSize
        }).then(res => {
          if (res.code == 0) this.list = res.data.list;
          this.loading = false;
        }).catch(res => {
          this.loading = false;
        });
      },
      //获取关注商品
      getGoodsCollect() {
        goodsCollect(this.goodsInfo).then(res => {
          this.goodsTotal = res.data.count
          this.goodsList = res.data.list
          this.loading = false
        }).catch(err => {
          this.loading = false
          this.$message.error(err.message)
        })
      },
      //删除关注商品
      deleteGoods(id) {
        deleteGoods({
          goods_id: id
        }).then(res => {
          if (res.code == 0) {
            this.$message({
              message: "取消关注成功",
              type: "success"
            })
            this.getGoodsCollect()
          }
        }).catch(err => {
          this.$message.error(err.message)
        })
      },
      handleClick(tab, event) {
        if (tab.index == "0") {
          this.loading = true
          this.getGoodsCollect()
        }
      },
      handleSizeChange(size) {
        this.goodsInfo.page_size = size
        this.loading = true
        this.getGoodsCollect()
      },
      handleCurrentChange(page) {
        this.goodsInfo.page = page
        this.loading = true
        this.getGoodsCollect()
      },
      imageError(index) {
        this.goodsList[index].sku_image = this.defaultGoodsImage
      },
      imageImgError(index) {
        this.list[index].sku_image = this.defaultGoodsImage;
      }
    }
  }
</script>
<style lang="scss" scoped>
  .goods-recommended {
    width: 100%;
    margin-top: 15px;
    background-color: #fff;

    .youLike {
      width: 955px;
      box-sizing: border-box;
      border-bottom: 2px solid #dedede;
      margin: 0 20px 20px;

      span {
        display: inline-block;
        font-size: 14px;
        padding: 10px 0;
      }
    }

    .body-wrap {
      .goods-list {
        display: flex;
        flex-wrap: wrap;

        li {
          width: 23%;
          margin-left: 19px;
          margin-bottom: 20px;
          background: #fff;
          cursor: pointer;
          padding: 10px 0;
          transition: all 0.2s linear;

          &:hover {
            z-index: 2;
            -webkit-box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            -webkit-transform: translate3d(0, -2px, 0);
            transform: translate3d(0, -2px, 0);
          }

          .img-wrap {
            width: 160px;
            height: 160px;
            margin: 0 auto 18px;
            text-align: center;
            line-height: 160px;

            img {
              max-width: 100%;
              max-height: 100%;
            }
          }

          h3 {
            font-size: 14px;
            text-align: center;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
            margin: 5px 15px;
          }

          .desc {
            margin: 0 30px 10px;
            height: 20px;
            font-size: 12px;
            color: #b0b0b0;
            text-align: center;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
          }

          .price {
            margin: 0 10px 14px;
            text-align: center;
            color: $base-color;

            del {
              margin-left: 0.5em;
              color: #b0b0b0;
            }
          }
        }
      }
    }

    .bottom-wrap {
      margin-top: 10px;
      width: $width;
      height: 118px;
      cursor: pointer;
      overflow: hidden;

      img {
        max-width: 100%;
      }
    }

  }

  .box {
    width: 100%;
    position: relative;
  }

  .null-page {
    width: 100%;
    height: 730px;
    background-color: #FFFFFF;
    position: absolute;
    top: 0;
    left: 0;
    z-index: 9;
  }

  .collection {
    background: #ffffff;
    padding: 10px 20px;

    .goods {
      display: flex;
      flex-wrap: wrap;

      .goods-wrap {
        width: 19%;
        margin-right: 1.25%;
        margin-bottom: 20px;

        &:nth-child(5n) {
          margin-right: 0;
        }

        .goods-item {
          border: 1px solid #f1f1f1;
          box-sizing: border-box;
          padding: 10px;

          .img {
            width: 100%;
            height: 160px;
            cursor: pointer;
            position: relative;

            img {
              width: 100%;
              height: 100%;
            }

            .del {
              font-size: 20px;
              position: absolute;
              top: 2px;
              right: 2px;
              padding: 3px;
              background: rgba($color: #000000, $alpha: 0.3);
              display: none;
              color: #ffffff;
            }

            &:hover {
              .del {
                display: block;
              }
            }
          }

          .goods-name {
            width: 100%;
            margin-top: 10px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            height: 55px;
          }

          .price {
            color: $base-color;
          }
        }
      }
    }

    .shop {
      display: flex;
      flex-wrap: wrap;

      .shop-wrap {
        margin: 0 15px 20px 0;

        &:nth-child(5n) {
          margin-right: 0;
        }

        .shop-item {
          width: 156px;
          height: 227px;
          border: 1px solid #eeeeee;
          padding: 0 10px;
          cursor: pointer;

          .head-wrap {
            text-align: center;
            padding: 10px 0;
            border-bottom: 1px solid #eeeeee;
            position: relative;

            .del {
              font-size: 20px;
              position: absolute;
              top: 0px;
              right: 0px;
              padding: 3px;
              background: rgba($color: #000000, $alpha: 0.3);
              display: none;
              color: #ffffff;
              cursor: pointer;
            }

            &:hover {
              .del {
                display: block;
              }
            }

            .img-wrap {
              width: 60px;
              height: 60px;
              line-height: 60px;
              display: inline-block;
            }

            .name {
              display: block;
              width: 100%;
              height: 24px;
              line-height: 24px;
              overflow: hidden;
              text-overflow: ellipsis;
              white-space: nowrap;
            }

            .tag {
              margin-left: 5px;
            }
          }

          .info-wrap {
            padding: 10px 0;
          }
        }
      }
    }

    .empty {
      text-align: center;
    }

    .page {
      text-align: center;
    }
  }
</style>

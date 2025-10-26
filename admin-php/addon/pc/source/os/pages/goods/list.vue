<template>
  <div class="goods-container">
    <div class="goods-list" v-loading="loading">
      <!-- 搜索关键字 -->
      <div class="goods-nav" v-if="keyword">
        <router-link to="/">首页</router-link>
        <span>/</span>
        <span class="keyword">{{ keyword }}</span>
      </div>
      <div class="goods-nav" v-else-if="catewords">
        <router-link to="/">首页</router-link>
        <span>/</span>
        <router-link :to="{ path: '/goods/list', query: $util.handleLink({ category_id: first_index, level: 1, brand_id: filters.brand_id }) }">{{ catewords.split('$_SPLIT_$')[0] }}</router-link>
        <span v-if="(filters.category_level == 2 || filters.category_level == 3) && catewords.split('$_SPLIT_$')[1]">/</span>
        <span v-if="(filters.category_level == 2 || filters.category_level == 3) && catewords.split('$_SPLIT_$')[1]" class="keyword">{{ catewords.split('$_SPLIT_$')[1] }}</span></div>

      <!-- 品牌过滤记录区 -->
      <div class="attr_filter" v-if="choosedBrand">
        <el-tag type="info" closable @close="closeBrand" effect="plain">
          <span v-if="choosedBrand" class="title">品牌：</span>
          {{ choosedBrand.brand_name }}
        </el-tag>
      </div>
      <div class="goods-screen-wrap">
        <div v-if="!keyword" class="goods-screen-item classify-info">
          <span class="screen-item-name">分类：</span>
          <ul class="screen-item-content">
            <li :class="{ active: categoryAll.isAllow && (categoryAll.id == 0 || categoryAll.id == filters.category_id) }">
              <router-link :to="{ path: '/goods/list', query: $util.handleLink({ category_id: categoryAll.id, level: categoryAll.level, brand_id: filters.brand_id }) }">全部</router-link>
            </li>
            <li v-for="item in categoryList" :class="{ active: item.category_id == selectCategoryId }">
              <router-link :to="{ path: '/goods/list', query: $util.handleLink({ category_id: item.category_id, level: item.level, brand_id: filters.brand_id }) }">{{ item.category_name }}</router-link>
            </li>
          </ul>
        </div>

        <!-- 品牌 -->
        <div class="brand" v-if="brandList.length > 0">
          <div class="table_head">品牌：</div>
          <div class="table_body" :class="{ 'more' : isShowMoreBrand }">
            <!-- <div class="initial">
              <span type="info" effect="plain" hit @mouseover="handleChangeInitial('')">所有品牌</span>
              <span type="info" effect="plain" hit v-for="item in brandInitialList" :key="item" @mouseover="handleChangeInitial(item)">{{ (item || '').toUpperCase() }}</span>
            </div> -->
            <el-card v-for="item in brandList" :key="item.id" body-style="padding: 0;height: 100%;" shadow="hover" v-show="currentInitial === '' || item.brand_initial === currentInitial" class="brand-item">
              <el-image :src="$img(item.image_url || defaultGoodsImage)" :alt="item.brand_name" :title="item.brand_name" fit="contain" @click="onChooseBrand(item)" />
            </el-card>
          </div>
          <div class="more-wrap" v-if="brandList.length>14" @click="isShowMoreBrand=!isShowMoreBrand">
            {{ isShowMoreBrand ? '收起' : '更多' }}
          </div>
        </div>

        <div class="goods-screen-item other-screen-info">
          <span class="screen-item-name">筛选：</span>
          <div class="screen-item-content">
            <div class="item" @click="changeSort('')">
              <div class="item-name">综合</div>
            </div>
            <div :class="['item', 'search-arrow', salesArrowDirection]" @click="changeSort('sale_num')">销量</div>
            <div :class="['item', 'search-arrow', priceArrowDirection]" @click="changeSort('discount_price')">价格</div>
            <div class="item-other">
              <el-checkbox label="包邮" v-model="is_free_shipping"></el-checkbox>
            </div>
            <div class="input-wrap">
              <div class="price_range">
                <el-input placeholder="最低价格" v-model="filters.min_price"></el-input>
                <span>—</span>
                <el-input placeholder="最高价格" v-model="filters.max_price"></el-input>
              </div>
              <el-button plain size="mini" @click="handlePriceRange">确定</el-button>
            </div>
          </div>
        </div>
      </div>

      <div class="list-wrap">
        <!--   <div class="goods-recommended" v-if="goodsList.length">
          <goods-recommend :page-size="goodsList.length < 5 ? 2 : 5" />
        </div> -->
        <div class="list-right">
          <!-- 排序筛选区 -->
          <div class="cargo-list" v-if="goodsList.length">
            <div class="goods-info">
              <div class="item" v-for="(item, index) in goodsList" :key="item.goods_id" @click="$router.push({ path: '/sku/' + item.sku_id })">
                <img class="img-wrap" :src="$img(item.goods_image, { size: 'mid' })" @error="item.goods_image = defaultGoodsImage" />
                <div class="price-wrap">
                  <div class="price">
                    <span>￥</span>
                    <span>{{ showPrice(item) }}</span>
                    <div class="price-icon-wrap">
                      <img v-if="item.member_price && item.member_price == showPrice(item)" :src="$img('public/uniapp/index/VIP.png')" />
                      <img v-else-if="item.promotion_type == 1" :src="$img('public/uniapp/index/discount.png')" />
                    </div>
                  </div>
                  <div v-if="parseInt(item.market_price)" class="market-price">￥{{ item.market_price }}</div>
                </div>
                <div class="goods-name">{{ item.goods_name }}</div>
                <div class="other-info">
                  <span class="sale-num">{{ item.sale_num || 0 }}人付款</span>
                  <div class="saling">
                    <div v-if="item.is_free_shipping == 1" class="free-shipping">包邮</div>
                    <div v-if="item.promotion_type == 1" class="free-shipping">限时折扣</div>
                  </div>
                </div>
              </div>
            </div>
            <div class="pager">
              <el-pagination background :pager-count="5" :total="total" prev-text="上一页" next-text="下一页"
                             :current-page.sync="currentPage" :page-size.sync="pageSize"
                             @size-change="handlePageSizeChange"
                             @current-change="handleCurrentPageChange" hide-on-single-page></el-pagination>
            </div>
          </div>
          <div class="empty" v-else-if="!loading">
            <img src="~assets/images/goods_empty.png" />
            <span>没有找到您想要的商品。换个条件试试吧</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import BreadCrumbs from '@/components/BreadCrumbs';
  import GoodsRecommend from '@/components/GoodsRecommend';
  import list from '@/assets/js/goods/list';

  export default {
    name: 'list',
    components: {
      BreadCrumbs,
      GoodsRecommend
    },
    mixins: [list]
  };
</script>

<style lang="scss" scoped>
  .goods-container {
    width: 100%;
    background-color: #f9f9f9;
  }

  .goods-list {
    width: $width;
    margin: 0 auto;
  }

  %flex-center {
    display: flex;
    align-items: center;
  }

  .padd-10 {
    padding: 0 10px;
  }

  .goods-nav {
    padding: 15px 0 0 0 !important;

    span,
    .nuxt-link-active {
      color: #666;
    }

    .keyword {
      color: $base-color;
    }
  }

  .goods-screen-wrap {
    margin-top: 20px;
    background-color: #fff;

    .goods-screen-item {
      display: flex;
      align-items: center;
      padding: 0 20px;
      box-sizing: border-box;

      .screen-item-name {
        width: 42px;
        margin-right: 35px;
        font-size: 14px;
        color: #999;
      }

      .screen-item-content {
        flex: 1;
        display: flex;
        align-items: center;
        height: 100%;
        border-bottom: 1px dashed #ebeff3;
        padding: 10px 0;
        flex-wrap: wrap;
      }

      &:last-of-type .screen-item-content {
        border-bottom: none;
      }
    }

    .classify-info {
      .screen-item-content > li {
        padding-right: 30px;

        a {
          font-size: 14px;
          color: #4a4a4a;
        }

        &:last-of-type {
          padding-right: 0;
        }

        &.active a,
        & a:hover {
          color: $base-color;
        }
      }
    }

    .other-screen-info {
      .screen-item-content > div {
        cursor: pointer;
        margin-right: 40px;

        &:last-of-type {
          margin-right: 0;
        }
      }

      .input-wrap {
        @extend %flex-center;

        .price_range {
          @extend %flex-center;

          & > span {
            overflow: hidden;
            margin: 0 5px;
            width: 10px;
          }

          .el-input__inner {
            border: none;
            width: 90px;
            height: 28px;
            background-color: #f3f5f7;
            border-radius: 6px;
          }
        }

        .el-button {
          margin-left: 12px;
          width: 56px;
          height: 28px;
          border-radius: 6px;

          &.is-plain:focus,
          &.is-plain:hover {
            border-color: $base-color;
            color: $base-color;
          }
        }
      }

      .search-arrow {
        position: relative;

        &::after,
        &::before {
          content: '';
          position: absolute;
          right: -12px;
          border: 5px solid transparent;
        }

        &::after {
          top: 1px;
          border-bottom-color: #999;
        }

        &::before {
          border-top-color: #999;
          bottom: 2px;
        }

        &.arrow-down::before {
          border-top-color: $base-color;
        }

        &.arrow-up::after {
          border-bottom-color: $base-color;
        }
      }

      .item-other {
        .el-checkbox__inner {
          &:hover {
            border-color: $base-color;
          }
        }

        .el-checkbox__input.is-checked .el-checkbox__inner,
        .el-checkbox__input.is-indeterminate .el-checkbox__inner {
          background-color: $base-color;
          border-color: $base-color;
        }

        .el-checkbox__input.is-checked + .el-checkbox__label {
          color: $base-color;
        }
      }
    }
  }

  .search_bread {
    display: inline-block;
    font-size: 14px;
    margin: 0px auto;
    width: 100%;
    padding: 10px;

    p {
      float: left;
    }

    li {
      float: left;
      padding: 0 10px;
    }

    .active a {
      color: #ff547b !important;
    }
  }

  .selected_border {
    border: 1px solid $base-color;
  }

  .attr_filter {
    margin-top: 10px;

    .el-tag {
      margin-right: 5px;
      margin-bottom: 10px;
      border-radius: 0;

      .title {
        color: $base-color;
        font-size: 15px;
      }
    }
  }

  .category {
    margin: 0 auto 10px auto;
    border: 1px solid #eeeeee;
  }

  .brand {
    border-bottom: 1px solid #eeeeee;
    display: flex;
    flex-direction: row;
    padding: 10px 20px 10px;

    &:last-child {
      border-bottom: none;
    }

    .table_head {
      color: #999;
      margin-right: 15px;
      width: 60px;
    }

    .table_body {
      display: flex;
      flex-direction: row;
      flex-wrap: wrap;
      flex: 1;
      height: 105px;
      overflow: hidden;

      &.more {
        height: 170px;
        overflow-y: auto;
      }

      .initial {
        margin: 5px auto 10px 10px;

        span {
          border: 0;
          margin: 0;
          padding: 5px 10px;
          border-radius: 0;

          &:hover {
            background-color: $base-color;
            color: #ffffff;
          }
        }
      }

      .brand-item {
        margin-right: 10px;
        margin-bottom: 10px;
      }

      .el-card {
        width: 125px;
        height: 45px;

        &:hover {
          border: 1px solid $base-color;
          cursor: pointer;
        }
      }

      span {
        margin: auto 25px;
      }
    }

    .table_op {
      margin-top: 5px;
      margin-right: 5px;
    }

    .more-wrap {
      cursor: pointer;
      margin-left: 15px;
    }

    .el-image {
      width: 100%;
      height: 100%;
    }
  }

  .list-wrap {
    overflow: hidden;
  }

  .goods-recommended {
    width: 200px;
    background-color: #fff;
    float: left;
    margin-right: 10px;
  }

  .sort {
    display: flex;
    align-items: center;

    .item {
      display: flex;
      align-items: center;
      padding: 5px 15px;
      border: 1px solid #f1f1f1;
      border-left: none;
      cursor: pointer;

      &:hover {
        background: $base-color;
        color: #fff;
      }
    }

    .item-other {
      display: flex;
      align-items: center;
      padding: 5px 15px;
      border: 1px solid #f1f1f1;
      border-left: none;
      cursor: pointer;
    }

    .input-wrap {
      display: flex;
      align-items: center;

      .price_range {
        margin-left: 60px;
      }

      span {
        padding-left: 10px;
      }

      .el-input {
        width: 150px;
        margin-left: 10px;
      }

      .el-button {
        margin: 0 17px;
      }
    }

    > div:first-child {
      border-left: 1px solid #f1f1f1;
    }
  }

  .cargo-list {
    padding-bottom: 40px;
  }

  .goods-info {
    margin-top: 20px;
    display: flex;
    flex-wrap: wrap;

    .item {
      width: 228px;
      margin: 0 17px 18px 0;
      padding: 18px;
      position: relative;
      background-color: #fff;
      box-sizing: border-box;

      &:nth-child(5n + 5) {
        margin-right: 0;
      }

      .img-wrap {
        width: 192px;
        height: 192px;
        cursor: pointer;
      }

      .goods-name {
        margin-top: 5px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        word-break: break-all;
        cursor: pointer;
        line-height: 1.4;

        &:hover {
          color: $base-color;
        }
      }

      .other-info {
        margin-top: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
      }

      .price-wrap {
        margin-top: 10px;
        display: flex;
        align-items: center;

        .price {
          display: flex;
          align-items: center;
          color: $base-color;

          & > span {
            font-weight: bold;
            line-height: 1;
          }

          span:nth-child(1) {
            font-size: 12px;
          }

          span:nth-child(2) {
            font-size: 16px;
          }
        }

        .market-price {
          color: #838383;
          text-decoration: line-through;
          margin-left: 10px;
          line-height: 1;
        }
      }

      .sale-num {
        display: flex;
        color: #999999;
        font-size: $ns-font-size-sm;

        p {
          color: #4759a8;
        }
      }

      .shop_name {
        padding: 0 10px;
        display: flex;
        color: #838383;
      }

      .saling {
        display: flex;
        font-size: $ns-font-size-sm;
        line-height: 1;

        .free-shipping {
          background: $base-color;
          color: #ffffff;
          padding: 3px 4px;
          border-radius: 2px;
          margin-right: 5px;
        }

        .promotion-type {
          color: $base-color;
          border: 1px solid $base-color;
          display: flex;
          align-items: center;
          padding: 1px 3px;
        }
      }

      .item-bottom {
        display: flex;
        margin-top: 5px;

        .collection {
          flex: 1;
          border: 1px solid #e9e9e9;
          border-right: none;
          border-left: none;
          height: 40px;
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
        }

        .cart {
          flex: 2;
          border: 1px solid #e9e9e9;
          border-right: none;
          height: 40px;
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
        }
      }
    }
  }

  .price-icon-wrap {
    display: inline-block;
    max-width: 35px;
    margin-left: 3px;
    line-height: 1;
    padding-bottom: 3px;

    img {
      max-width: 100%;
    }
  }

  .empty {
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
      color: #4a4a4a;
    }
  }

  .pager {
    text-align: center;
    margin-top: 30px;
  }

  .el-pagination.is-background .el-pager li:not(.disabled).active {
    background-color: $base-color;
  }

  .el-pagination.is-background .el-pager li:not(.disabled):hover {
    color: $base-color;
  }
</style>

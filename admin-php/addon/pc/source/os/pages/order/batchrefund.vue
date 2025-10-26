<template>
  <div class="box">
    <div class="null-page" v-show="yes"></div>

    <el-card class="box-card">
      <div slot="header" class="title clearfix">
        <el-breadcrumb separator="/">
          <el-breadcrumb-item :to="{ path: '/member/order_list' }">我的订单</el-breadcrumb-item>
          <el-breadcrumb-item :to="{ path: '/member/order_detail?order_id=' + order_id }">订单详情</el-breadcrumb-item>
          <el-breadcrumb-item>批量退款</el-breadcrumb-item>
        </el-breadcrumb>
      </div>
      <div slot="header" class="shopings clearfix">
        <span>选择退款商品</span>
      </div>
      <el-table ref="multipleTable" :data="orderData" tooltip-effect="dark" style="width: 100%" @selection-change="handleSelectionChange">
        <el-table-column type="selection" width="55"></el-table-column>
        <el-table-column label="商品图片" width="100" prop="goods_image">
          <template slot-scope="scope">
            <img class="box-img" :src="$img(scope.row.sku_image, { size: 'mid' })" alt="" @error="scope.row.sku_image = defaultGoodsImage" />
          </template>
        </el-table-column>
        <el-table-column label="商品名称" prop="goods_name"></el-table-column>
        <el-table-column label="价格" width="180" prop="price"></el-table-column>
      </el-table>

      <div class="flooter">
        <div class="flooter-left">
          <!-- <el-checkbox v-model="checked">全选</el-checkbox> -->
        </div>
        <div class="flooter-right">
          共计选中{{order_goods_ids.length}}件商品
          <el-button v-if="order_goods_ids.length" class="but" type="primary" @click="next">下一步</el-button>
          <el-button v-else class="but" type="info">请选择退款商品</el-button>
        </div>
      </div>
    </el-card>
  </div>
</template>

<script>
  import {
    apiOrderDetail
  } from "@/api/order/order"
  import {
    mapGetters
  } from 'vuex';

  export default {
    name: "account_edit",
    components: {},
    data() {
      return {
        yes: true,
        order_id: 0,
        orderData: [],
        checked: false,
        order_goods_ids: []
      }
    },
    created() {
      this.order_id = this.$route.query.order_id
      this.getOrderInfo()
    },
    mounted() {
    },
    computed: {
      ...mapGetters(['defaultGoodsImage'])
    },
    layout: 'member',
    methods: {
      /**
       * 获取商品数据
       */
      getOrderInfo() {
        apiOrderDetail({
          order_id: this.order_id
        }).then((res) => {
          if (res.code >= 0) {
            this.orderData = [];
            res.data.order_goods.forEach((item) => {
              if (item.refund_status == 0) {
                this.orderData.push(item);
              }
            })
          }
        })
      },
      handleSelectionChange(e) {
        this.order_goods_ids = e.map((item, index) => {
          return item.order_goods_id;
        });
      },
      next() {
        this.$router.push({
          path: '/order/orderbatch_refund',
          query: {
            order_goods_id: this.order_goods_ids.join(','),
            order_id: this.order_id
          }
        });
      },
    }
  }
</script>

<style lang="scss" scoped>
  .box-img {
    width: 70px;
    height: auto;
  }

  .flooter {
    padding: 18px 20px;
    box-sizing: border-box;
    display: flex;
    align-content: center;
    justify-content: space-between;

    .but {
      padding: 10px 20px;
      margin-left: 10px;
    }
  }

  .title {
    padding: 0 0 18px;
    border-bottom: 1px solid #EBEEF5;
  }

  .shopings {
    padding: 18px 0 0;
  }
</style>

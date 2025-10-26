import {
  goodsSkuPage,
  brandPage
} from "@/api/goods/goods"
import {
  mapGetters
} from "vuex"
import {
  goodsCategoryInfo,
  goodsCategoryList
} from "@/api/goods/goodscategory"

export default {
  data: () => {
    return {
      goodsList: [],
      total: 0,
      keyword: "",
      catewords: '',
      currentPage: 1,
      pageSize: 25,
      is_free_shipping: 0,
      filters: {
        category_id: 0,
        category_level: 0,
        brand_id: 0,
        min_price: "",
        max_price: "",
        order: "",
        sort: "desc",
        coupon: 0
      },
      loading: true,
      first_index: 0,
      categoryList: [],
      selectCategoryId: 0,
      // 全部商品分类链接
      categoryAll: {
        id: 0,
        level: 0,
        isAllow: true // 是否允许选中
      },

      brandList: [], // 品牌列表
      brandInitialList: [],
      currentInitial: "", // 当前选择品牌分区
      choosedBrand: "", // 已选择的品牌,
      isShowMoreBrand: false // 是否展开更多品牌
    }
  },
  created() {
    this.keyword = this.$route.query.keyword || ""
    if (this.$route.query.keyword && process.client) window.document.title = `${this.$route.query.keyword} - ${this.siteInfo.site_name}`

    this.filters.category_id = this.$route.query.category_id || ""
    this.filters.category_level = this.$route.query.level || ""
    this.filters.brand_id = this.$route.query.brand_id || ""
    this.filters.coupon = this.$route.query.coupon || 0

    this.getBrandList();
    this.getGoodsList();

    if (this.$route.query.category_id && this.$route.query.category_id > 0) {
      this.categorySearch();
    } else {
      // 查询一级商品分类
      this.getGoodsCategoryList();
    }
  },
  computed: {
    salesArrowDirection() {
      let className = this.filters.order === 'sale_num' && this.filters.sort === 'desc' ? 'arrow-down' : 'arrow-up';
      return className;
    },
    priceArrowDirection() {
      let className = this.filters.order === 'discount_price' && this.filters.sort === 'desc' ? 'arrow-down' :
        'arrow-up';
      return className;
    },
    ...mapGetters(["defaultGoodsImage", "siteInfo"])
  },
  methods: {
    // 商品分类搜索
    categorySearch() {
      goodsCategoryInfo({
        category_id: this.filters.category_id
      }).then(res => {
        if (res.code == 0 && res.data) {
          let data = res.data;
          this.catewords = data.category_full_name;
          this.first_index = data.category_id_1;
          this.categoryList = data.child_list;
          this.selectCategoryId = this.filters.category_id;

          // 设置全部的链接地址
          switch (data.level) {
            case 1:
              this.categoryAll.level = 1;
              this.categoryAll.id = data.category_id_1;
              this.categoryAll.isAllow = true;
              break;
            case 2:
              this.categoryAll.level = 1;
              this.categoryAll.id = data.category_id_1;
              this.categoryAll.isAllow = true;
              if (this.categoryList[0].level == 3) {
                this.categoryAll.level = 2;
                this.categoryAll.id = data.category_id_2;
              }
              break;
            case 3:
              this.categoryAll.level = 2;
              this.categoryAll.id = data.category_id_2;
              this.categoryAll.isAllow = false;
              break;
          }

          for (let i = 0; i < this.categoryList.length; i++) {
            let item = this.categoryList[i];
            if (item.category_id == this.categoryAll.id) {
              this.categoryAll.id = 0;
              this.categoryAll.isAllow = false; // 匹配到了分类，禁止选中 全部
              break;
            }
          }

          if (process.client) window.document.title = `${data.category_name} - ${this.siteInfo.site_name}`
        }
      })

    },
    // 一级商品分类列表
    getGoodsCategoryList() {
      goodsCategoryList({}).then(res => {
        if (res.code == 0 && res.data) {
          // 设置全部的链接地址
          this.categoryAll.level = 1;
          this.categoryAll.id = 0;
          this.categoryAll.isAllow = true;
          this.categoryList = res.data;
        }
      })

    },
    getGoodsList() {
      const params = {
        page: this.currentPage,
        page_size: this.pageSize,
        keyword: this.keyword,
        ...this.filters
      }
      goodsSkuPage(params || {}).then(res => {
        const {
          count,
          page_count,
          list
        } = res.data
        this.total = count
        this.goodsList = list
        this.loading = false
      }).catch(err => {
        this.loading = false
      })
    },
    handlePageSizeChange(size) {
      this.pageSize = size
      this.getGoodsList()
    },
    handleCurrentPageChange(page) {
      this.currentPage = page
      this.getGoodsList()
    },
    handlePriceRange() {
      if (Number(this.filters.min_price) > Number(this.filters.max_price)) {
        // es6解构赋值
        [this.filters.min_price, this.filters.max_price] = [this.filters.max_price, this.filters.min_price]
      }
      this.getGoodsList()
    },
    changeSort(type) {
      if (this.filters.order === type) {
        this.$set(this.filters, "sort", this.filters.sort === "desc" ? "asc" : "desc")
      } else {
        this.$set(this.filters, "order", type)
        this.$set(this.filters, "sort", "desc")
      }

      this.getGoodsList()
    },
    getBrandList() {
      brandPage({
        page: 1,
        page_size: 0
      }).then(res => {
        if (res.code >= 0 && res.data) {
          this.brandList = res.data.list;
          if (this.filters.brand_id) {
            for (var i = 0; i < this.brandList.length; i++) {
              if (this.brandList[i].brand_id == this.filters.brand_id) {
                this.choosedBrand = this.brandList[i];
              }
            }

          }
        }
      });
    },
    handleChangeInitial(initial) {
      this.currentInitial = initial
    },
    onChooseBrand(brand) {
      this.choosedBrand = brand
      this.filters.brand_id = brand.brand_id
      this.getGoodsList()
    },
    closeBrand() {
      this.choosedBrand = ""
      this.filters.brand_id = ""
      this.getGoodsList()
    },
    showPrice(data) {
      let price = data.discount_price;
      if (data.member_price && parseFloat(data.member_price) < parseFloat(price)) price = data.member_price;
      return price;
    }
  },
  watch: {
    is_free_shipping: function (val) {
      this.filters.is_free_shipping = val ? 1 : ""
      this.getGoodsList()
    },
    $route: function (curr) {
      this.currentPage = 1
      if (curr.query.keyword && process.client) window.document.title = `${curr.query.keyword} - ${this.siteInfo.site_name}`

      if (curr.query.level && curr.query.category_id > 0) {
        this.filters.category_level = curr.query.level
        this.filters.category_id = curr.query.category_id
        this.getGoodsList()
        this.categorySearch()
      } else {
        this.getGoodsCategoryList();
        this.first_index = 0
        this.selectCategoryId = 0
        if (process.client) window.document.title = `${this.siteInfo.site_name}`
      }

      if (curr.query.category_id == undefined || curr.query.category_id == 0) {
        this.catewords = ""
        this.keyword = curr.query.keyword
        this.filters.category_id = curr.query.category_id || ""
        this.filters.category_level = curr.query.level || ""
        this.filters.brand_id = curr.query.brand_id || ""
        this.getGoodsList()
      }
    }
  }
}

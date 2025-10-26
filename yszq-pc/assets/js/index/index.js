import {
  adList
} from "@/api/website"
import {
  noticesList
} from "@/api/cms/notice"
import {
  floors,
  floatLayer,
  apiDefaultSearchWords
} from "@/api/pc"
import {
  mapGetters
} from "vuex"
import {
  goodsPage,
  timeList
} from "@/api/seckill"
import CountDown from "vue2-countdown"

export default {
  name: "index",
  components: {
    CountDown
  },
  data: () => {
    return {
      loadingAd: true,
      loadingFloor: true,
      adList: [],
      adLeftList: [],
      adRightList: [],
      adCenterList: [],
      floorList: [],
      floatLayer: {
        is_show: false,
        link: {
          url: ""
        }
      },
      isSub: false,
      siteId: 0,
      listData: [],
      seckillTimeMachine: {
        currentTime: 0,
        startTime: 0,
        endTime: 0
      },
      seckillText: "距离结束",
      backgroundColor: "", // 顶部banner背景颜色
      keyword: "",
      defaultSearchWords: "",
      isShow: false
    }
  },
  watch: {
    addonIsExit: {
      handler: function () {
        if (this.addonIsExit && this.addonIsExit.seckill == 1) {
          this.getTimeList()
        }
      },
      deep: true
    }
  },
  created() {
    this.getAdList()
    this.getBigAdList()
    this.getSmallAdList()
    this.getCategoryBelowList()
    this.getFloors()
    this.getFloatLayer()
    if (this.addonIsExit && this.addonIsExit.seckill == 1) {
      this.getTimeList()
    }
  },
  mounted() {
    window.addEventListener("scroll", this.handleScroll)
  },
  computed: {
    ...mapGetters(["defaultHeadImage", "addonIsExit", "defaultGoodsImage", "member", "siteInfo", "cartCount"]),
    optionLeft() {
      return {
        direction: 2,
        limitMoveNum: 2
      }
    },
    indexFloatLayerNum() {
      let num = localStorage.getItem('indexFloatLayerNum') || 0;
      return parseInt(num);
    }
  },
  methods: {
    countDownS_cb() {
    },
    countDownE_cb() {
      this.seckillText = "活动已结束"
    },
    getAdList() {
      adList({
        keyword: "NS_PC_INDEX"
      }).then(res => {
        this.adList = res.data.adv_list
        this.$store.dispatch("app/is_show", {
          is_show: this.adList.length
        }).then(res => {
        })
        for (let i = 0; i < this.adList.length; i++) {
          if (this.adList[i].adv_url) this.adList[i].adv_url = JSON.parse(this.adList[i].adv_url)
        }
        this.backgroundColor = this.adList[0].background
        this.loadingAd = false
      }).catch(err => {
        this.loadingAd = false
      })
    },
    handleChange(curr, pre) {
      this.backgroundColor = this.adList[curr].background
    },
    /**
     * 广告位大图
     */
    getBigAdList() {
      adList({
        keyword: "NS_PC_INDEX_MID_LEFT"
      }).then(res => {
        this.adLeftList = res.data.adv_list
        for (let i = 0; i < this.adLeftList.length; i++) {
          if (this.adLeftList[i].adv_url) this.adLeftList[i].adv_url = JSON.parse(this.adLeftList[i].adv_url)
        }
        this.loadingAd = false
      }).catch(err => {
        this.loadingAd = false
      })
    },
    /**
     * 广告位小图
     */
    getSmallAdList() {
      adList({
        keyword: "NS_PC_INDEX_MID_RIGHT"
      }).then(res => {
        this.adRightList = res.data.adv_list
        for (let i = 0; i < this.adRightList.length; i++) {
          if (this.adRightList[i].adv_url) this.adRightList[i].adv_url = JSON.parse(this.adRightList[i].adv_url)
        }
        this.loadingAd = false
      }).catch(err => {
        this.loadingAd = false
      })
    },
    getCategoryBelowList() {
      adList({
        keyword: "NS_PC_INDEX_CATEGORY_BELOW"
      }).then(res => {
        this.adCenterList = res.data.adv_list
        for (let i = 0; i < this.adCenterList.length; i++) {
          if (this.adCenterList[i].adv_url) this.adCenterList[i].adv_url = JSON.parse(this.adCenterList[i].adv_url)
        }
        this.loadingAd = false
      }).catch(err => {
        this.loadingAd = false
      })
    },
    /**
     * 限时秒杀
     */
    getTimeList() {
      timeList().then(res => {
        if (res.code == 0 && res.data) {
          let time = new Date(res.timestamp * 1000)
          let currentTimes = time.getHours() * 60 * 60 + time.getMinutes() * 60 + time.getSeconds()

          res.data.list.forEach((v, k) => {
            if (v.seckill_start_time <= currentTimes && currentTimes < v.seckill_end_time) {
              let seckillId = v.id
              this.getGoodsList(seckillId)

              let endTime = parseInt(time.getTime() / 1000) + (v.seckill_end_time - currentTimes)
              this.seckillTimeMachine = {
                currentTime: res.timestamp,
                startTime: res.timestamp,
                endTime: endTime
              }
            }
          })
        }
      })
    },
    /**
     * 秒杀商品
     */
    getGoodsList(id) {
      goodsPage({
        page_size: 0,
        seckill_time_id: id,
        site_id: this.siteId
      }).then(res => {
        if (res.code == 0 && res.data.list) {
          this.listData = res.data.list
        }
      })
    },
    /**
     * 图片加载失败
     */
    imageError(index) {
      this.listData[index].sku_image = this.defaultGoodsImage
    },
    /**
     * 图片加载失败
     */
    adLeftImageError(index) {
      this.adLeftList[index].adv_image = this.defaultGoodsImage
    },
    /**
     * 图片加载失败
     */
    adRightImageError(index) {
      this.adRightList[index].adv_image = this.defaultGoodsImage
    },
    adCenterImageError(index) {
      this.adCenterList[index].adv_image = this.defaultGoodsImage
    },
    getFloors() {
      floors().then(res => {
        this.floorList = res.data;
      })
    },
    getFloatLayer() {
      floatLayer().then(res => {
        if (res.code == 0 && res.data) {
          this.floatLayer = res.data
          if (this.floatLayer.is_show == 1) {
            this.floatLayer.link = JSON.parse(this.floatLayer.url)
            // 弹框形式，首次弹出 1，每次弹出 0
            if (!this.floatLayer.img_url) return
            if (parseInt(this.floatLayer.number) >= 1) {
              //缓存计数 == 弹出总数   禁止弹出
              if (this.indexFloatLayerNum >= parseInt(this.floatLayer.number)) {
                this.floatLayer.is_show_type = false
              } else {
                this.floatLayer.is_show_type = true
              }
            } else if (parseInt(this.floatLayer.number) == 0) {
              this.floatLayer.is_show_type = true
            }
          } else {
            this.floatLayer.is_show_type = false
          }

        }
      })
    },
    closeFloat() {
      if (parseInt(this.floatLayer.number) == 0) {
        this.$store.commit("app/SET_FLOAT_LAYER", 0)
      } else if (parseInt(this.floatLayer.number) >= 1 && this.indexFloatLayerNum != parseInt(this.floatLayer.number)) {
        var count_num = this.indexFloatLayerNum + 1;
        this.$store.commit("app/SET_FLOAT_LAYER", count_num)
      } else if (this.indexFloatLayerNum == parseInt(this.floatLayer.number)) {
        this.$store.commit("app/SET_FLOAT_LAYER", this.floatLayer.number)
      }
      this.floatLayer.is_show_type = false
      this.$forceUpdate()
      // this.$store.commit("app/SET_FLOAT_LAYER", -1)
    },
    // 监听滚动条
    handleScroll() {
      var scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop

      if (scrollTop >= 680) {
        this.isShow = true
      } else {
        this.isShow = false
      }
    }
  },
  destroyed() {
    // 离开该页面需要移除这个监听的事件，不然会报错
    console.log('// 离开该页面需要移除这个监听的事件，不然会报错');
    window.removeEventListener("scroll", this.handleScroll)
  }
}

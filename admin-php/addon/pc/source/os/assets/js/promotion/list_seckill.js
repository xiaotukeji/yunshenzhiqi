import {goodsPage, timeList} from '@/api/seckill';
import {mapGetters} from 'vuex';
import {adList} from '@/api/website';
import CountDown from 'vue2-countdown';

export default {
  name: 'groupbuy',
  components: {CountDown},
  data: () => {
    return {
      loading: true,
      timeList: [], //时间列表
      seckillId: null, //选中的时间块
      seckillName: null, //选中的时间块的名称
      seckillIndex: null, //选中时间块的index
      goodsList: [], //选中时间块的商品列表
      index: null, //当前正在抢购的index
      siteId: 0,
      total: 0,
      currentPage: 1,
      pageSize: 25,
      loadingAd: true,
      adList: [],
      seckillTimeMachine: {
        currentTime: 0,
        startTime: 0,
        endTime: 0
      },
      seckillText: '距离结束仅剩',
      thumbPosition: 0,
      // 是否可以移动
      moveThumbLeft: false,
      moveThumbRight: false,
      shouType: true,
      isNoClick: false,
      key: 0
    };
  },
  watch: {
    seckillId(newName, oldName) {
      if (newName && newName != oldName) {
        this.refresh();
      }
    },
    addonIsExit() {
      if (this.addonIsExit.seckill != 1) {
        this.$message({
          message: '秒杀插件未安装',
          type: 'warning',
          duration: 2000,
          onClose: () => {
            this.$route.push('/');
          }
        });
      }
    }
  },
  created() {
    if (this.addonIsExit && this.addonIsExit.seckill != 1) {
      this.$message({
        message: '秒杀插件未安装',
        type: 'warning',
        duration: 2000,
        onClose: () => {
          this.$route.push('/');
        }
      });
    } else {
      this.getAdList();
      this.getTimeList();
    }
  },
  computed: {
    isTrue() {
      let num = 0;
      if (this.timeList && this.timeList[this.index]) {
        num = this.timeList[this.index].isNow;
      }
      return num;
    },
    ...mapGetters(['defaultGoodsImage', 'addonIsExit'])
  },
  methods: {
    /**
     * 点击某个时间段
     */
    handleSelected(i, item) {
      this.key = i;
      let text = this.timeList[i].name
      if (i < this.index) {
        this.$message.warning(text + '秒杀已结束')
      } else if (i > this.index) {
        this.shouType = false;
        this.seckillId = item.id;
        this.isNoClick = true;
        this.seckillName = item.name;
        // this.$message.warning(text+ '秒杀未开始')
        //this.getGoodsList();
        this.getTimeList();
      } else {
        this.seckillId = item.id;
        this.shouType = true;
        this.isNoClick = false;
        this.seckillName = item.name;
        //this.getGoodsList();
        this.getTimeList();
      }
    },
    /**
     * 点击前后箭头
     */
    changeThumbImg(tag) {
      let _div = this.$refs.seckillTime.clientWidth;
      let _i = document.querySelector('.seckill-time-ul').style.left.indexOf('px')
      let _li = document.querySelector('.seckill-time-ul').style.left.substring(0, _i)

      if (this.timeList.length < 4) return
      let page = this.timeList.length % 4 // 可见数量4个
      let position = 302.5
      if (page == 0) page = this.timeList.length - 4 // 可见数量4个
      else if (page != 0 && page != 1 && page < 2) return

      if (tag == "prev") {
        if (this.thumbPosition != 0 && Math.round(this.thumbPosition, 2) != position && position < Math.abs(this.thumbPosition)) {
          this.thumbPosition += position
        } else {
          this.thumbPosition = 0
        }
      } else if (tag == "next") {

        if (Math.round(this.thumbPosition, 2) != -Math.round(position * page, 2)) {

          let _ul = this.timeList.length * position
          let _left = _ul - _div
          if (Math.abs(this.thumbPosition) - _left >= 0) {
            this.thumbPosition = -_left
          } else if (Math.abs(this.thumbPosition) - _left < -150) {
            this.thumbPosition -= position
          } else {
            this.thumbPosition = -_left
          }
        } else {
        }
      }
    },
    countDownS_cb() {
    },
    countDownE_cb() {
      this.seckillText = '活动已结束';
    },
    getAdList() {
      adList({keyword: 'NS_PC_SECKILL'}).then(res => {
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
     * 秒杀时间段
     */
    getTimeList() {
      timeList().then(res => {
        let data = res.data;
        if (!data) return;
        let time = new Date(res.timestamp * 1000);
        let newTimes = time.getHours() * 60 * 60 + time.getMinutes() * 60 + time.getSeconds();
        data.list.forEach((v, k) => {
          if (v.seckill_start_time <= newTimes && newTimes < v.seckill_end_time) {
            v.isNow = true;
            if (this.shouType == true) {
              this.seckillId = v.id;
              this.seckillName = v.name;
              this.index = k;
              this.seckillIndex = k;

              let endTime = parseInt(time.getTime() / 1000) + (v.seckill_end_time - newTimes);
              this.seckillTimeMachine = {
                currentTime: res.timestamp,
                startTime: res.timestamp,
                endTime: endTime
              };
            }
          } else {
            v.isNow = false;
          }
          // 处理时间格式
          v.seckill_end_time_show = v.seckill_end_time_show.slice(0, 5);
          v.seckill_start_time_show = v.seckill_start_time_show.slice(0, 5);
        });

        this.timeList = data.list;
        if (!this.seckillId) {
          for (let i = 0; i < data.list.length; i++) {
            if (newTimes < data.list[i].seckill_start_time && i == 0) {
              this.seckillId = data.list[i].id;
              this.index = i;
              this.seckillIndex = i;
            } else if (newTimes < data.list[i].seckill_start_time && newTimes > data.list[i - 1].seckill_end_time && i != 0) {
              this.seckillId = data.list[i].id;
              this.index = i;
              this.seckillIndex = i;
            } else if (i == data.list.length - 1 && newTimes > data.list[i].seckill_end_time) {
              this.seckillId = data.list[i].id;
              this.index = i;
              this.seckillIndex = i;
            }
          }
        }

        // this.$nextTick(function() {
        // 	if (this.timeList.length > 0) {
        // 		let _div = this.$refs.seckillTime.clientWidth;
        // 		let _li = document.querySelector('.seckill-time-li').clientWidth
        // 		let leftWidth = this.index * _li; // 抢购中的时间段距左边的位置
        // 		let offsetWidth = leftWidth - (_li) * 4; // 需要左偏移的距离
        // 		this.thumbPosition = -offsetWidth
        // 	}
        // });
      }).catch(err => {
        this.$message.error(err.message);
      });
    },
    /**
     * 秒杀商品
     */
    getGoodsList() {
      goodsPage({
        page_size: this.pageSize,
        page: this.currentPage,
        seckill_time_id: this.seckillId,
        site_id: this.siteId
      }).then(res => {
        this.goodsList = res.data.list;
        this.goodsList.forEach(v => {
          v.goods_image = v.goods_image.split(',')[0]
        })
        this.total = res.data.count;
        this.loading = false;
      }).catch(err => {
        this.loading = false;
        this.$message.error(err.message);
      });
    },
    /**
     * 商品详情
     */
    toGoodsDetail(id, key) {
      let time = new Date();
      let newTimes = time.getHours() * 60 * 60 + time.getMinutes() * 60 + time.getSeconds();
      if (this.timeList[this.key].seckill_start_time <= newTimes && newTimes < this.timeList[this.key].seckill_end_time) {
        this.timeList[this.key].isNow = true;
      } else {
        this.timeList[this.key].isNow = false;
      }
      if (!this.timeList[this.key].isNow) {
        this.$message.error('秒杀活动还未开启，敬请期待！')
        return;
      }
      this.$router.push('/promotion/seckill/' + id);
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
  },
  head() {
    return {
      title: '秒杀专区-' + this.$store.state.site.siteInfo.site_name,
      meta: [{
        name: 'description',
        content: this.$store.state.site.siteInfo.seo_description
      },
        {
          name: 'keyword',
          content: this.$store.state.site.siteInfo.seo_keywords
        }
      ]
    }
  }
};

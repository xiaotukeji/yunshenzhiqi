<template>
  <div class="footer">
    <el-tabs v-model="activeName" class="friendly-link" v-if="linkList.length > 0">
      <el-tab-pane label="友情链接" name="first">
        <div class="link-item" v-for="(link_item, link_index) in linkList" :key="link_index"
          :title="link_item.link_title" @click="linkUrl(link_item.link_url, link_item.is_blank)">
          <img :src="$img(link_item.link_pic)" />
        </div>
      </el-tab-pane>
    </el-tabs>
    <div class="footer-top" v-if="shopServiceList.length > 0">
      <ul class="service">
        <li v-for="(item, index) in shopServiceList" :key="index">
          <div class="item-head">
            <img :src="$img(item.icon.imageUrl)" alt="" v-if="item.icon.iconType == 'img'">
            <span v-else :class="item.icon.icon"></span>
          </div>
          <div class="item-content">
            <p class="name">{{ item.service_name }}</p>
            <p class="desc">{{ item.desc }}</p>
          </div>
        </li>
      </ul>
    </div>

    <div class="footer-bot">
      <copy-right />
    </div>
  </div>
</template>

<script>
  import {
    copyRight,
    shopServiceLists,
    friendlyLink,
    weQrcode,
  } from "@/api/website"
  import CopyRight from "./CopyRight"
  import {
    mapGetters
  } from "vuex"
  import {
    helpList
  } from "@/api/cms/help"

  export default {
    props: {},
    data() {
      return {
        shopServiceList: [],
        linkList: [],
        helpList: [],
        ishide: false,
        activeName: "first",
        qrcode: "",
      }
    },
    computed: {},
    created() {
      this.getShopServiceLists();
      this.link();
      this.getHelpList();
      this.getqrcodeimg();
    },
    mounted() {},
    watch: {},
    methods: {
      getqrcodeimg() {
        weQrcode({}).then(res => {
            if (res.code == 0 && res.data) {
              this.qrcode = res.data
            }
          })
          .catch(err => {
            this.$message.error(err.message)
          })
      },
      getShopServiceLists() {
        shopServiceLists({}).then(res => {
            if (res.code == 0 && res.data) {
              this.shopServiceList = res.data
            }
          })
          .catch(err => {
            this.shopServiceList = []
          })
      },
      link() {
        friendlyLink({})
          .then(res => {
            if (res.code == 0 && res.data) {
              this.linkList = res.data
            }
          })
          .catch(err => {
            this.$message.error(err.message)
          })
      },
      linkUrl(url, target) {
        if (!url) return
        if (url.indexOf("http") == -1 && url.indexOf("https") == -1) {
          if (target) this.$util.pushToTab({
            path: url
          })
          else this.$router.push({
            path: url
          })
        } else {
          if (target) window.open(url)
          else window.location.href = url
        }
      },
      /**
       * 获取帮助列表
       */
      getHelpList() {
        helpList()
          .then(res => {
            if (res.code == 0 && res.data) {
              var arr = [];
              arr = res.data.slice(0, 4)
              for (let i = 0; i < arr.length; i++) {
                arr[i].child_list = arr[i].child_list.slice(0, 4);
              }

              this.helpList = arr
            }
          })
          .catch(err => {})
      },
      /**
       * 跳转到帮助列表
       */
      clickToHelp(id) {
        this.$router.push("/cms/help/listother-" + id)
      },
      /**
       * 跳转到帮助详情
       */
      clickToHelpDetail(id) {
        this.$router.push("/cms/help-" + id)
      },
      /**
       * 跳转到帮助详情
       */
      clickJump(address) {
        location.href = address;
      }
    },
    components: {
      CopyRight
    }
  }
</script>

<style scoped lang="scss">
  .footer {
    .footer-top {
      background-color: #f2f2f2;
      border-bottom: 1px solid #e2e2e2;

      .service {
        width: $width;
        margin: 0 auto;
        padding: 20px 0 40px;
        box-sizing: border-box;
        border-bottom: 1px solid #F5F5F5;
        display: flex;
        flex-wrap: wrap;
        align-items: center;

        li {
          margin-top: 30px;
          display: flex;
          align-items: center;
          width: 25%;
          list-style: none;
          padding-right: 30px;
          box-sizing: border-box;

          &:nth-child(4n) {
            padding-right: 0;
          }

          .item-head {
            width: 58px;
            height: 58px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;

            img {
              max-width: 100%;
              max-height: 100%;
            }

            span {
              font-size: 58px
            }
          }

          .item-content {
            flex: 1;
            display: flex;
            flex-direction: column;

            .name {
              font-size: 19px;
              font-weight: bold;
              color: #444;
              line-height: 1;
              margin-bottom: 8px;
              @extend .using-hidden;
            }

            .desc {
              font-size: 15px;
              color: #444;
              line-height: 1;
              @extend .using-hidden;
            }
          }
        }
      }
    }

    .footer-bot {
      background: #f2f2f2;
      color: #444;
    }

    .friendly-link {
      width: $width;
      margin: 0 auto;
      padding-bottom: 48px;

      .link-title {
        line-height: 30px;
        padding: 10px 0 5px;
        margin: 0px 0 15px;
        border-bottom: 1px solid #e8e8e8;
      }

      .link-item {
        width: 192px;
        height: 80px;
        line-height: 80px;
        text-align: center;
        margin: 14px 12px 4px 0;
        cursor: pointer;
        background-color: #fff;

        img {
          max-width: 100%;
          max-height: 100%;
        }
      }

      .link-item:hover {
        width: 192px;
        position: relative;
        opacity: 1;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      }
    }
  }
</style>
<style lang="scss">
  .friendly-link {
    .el-tabs__nav-scroll {
      border-left: 1px solid #d8d8d8;
    }

    .el-tabs__header {
      margin-bottom: 14px;
    }

    .el-tabs__content {
      .el-tab-pane {
        display: flex;
        flex-wrap: wrap;
      }
    }

    .el-tabs__nav-wrap::after {
      height: 1px;
    }

    .el-tabs__item {
      padding: 0 10px !important;
      height: 52px;
      line-height: 52px;
      width: 168px;
      text-align: center;
      box-sizing: border-box;
      border-top: 1px solid #d8d8d8;
      border-right: 1px solid #d8d8d8;
      font-size: 18px;

      &.is-active {
        color: $base-color;
      }
    }

    .el-tabs__active-bar {
      margin-left: -10px;
      width: 168px !important;
      bottom: 50px;
      background-color: $base-color;

      &::after {
        content: "";
        position: absolute;
        bottom: -50px;
        left: 0;
        height: 1px;
        width: 100%;
        background-color: #f9f9f9;
      }
    }
  }
</style>

<template>
  <div class="header-in">
    <router-link to="/" class="header-left"><img v-if="siteInfo.logo" :src="$img(siteInfo.logo)" /></router-link>
    <div class="header-content">
      <nav>
        <ul>
          <li v-for="(nav_item, nav_index) in navList" :key="nav_index"
            :class="nav_item.url == navSelect ? 'router-link-active' : ''"
            @click="navUrl(nav_item.url, nav_item.is_blank)">
            {{ nav_item.nav_title }}
          </li>
        </ul>
      </nav>
    </div>
    <div class="header-right">
      <span class="iconfont icon-xiaosuo"></span>
      <input type="text" :placeholder="defaultSearchWords" v-model="keyword" @keyup.enter="search" maxlength="50" />
      <el-button size="small" @click="search">搜索</el-button>
    </div>
  </div>
</template>

<script>
  import {
    mapGetters
  } from 'vuex';
  import {
    apiDefaultSearchWords
  } from '@/api/pc';
  import {
    navList
  } from '@/api/website';
  export default {
    props: {},
    data() {
      return {
        keyword: '',
        defaultSearchWords: '请输入您要查询的商品',
        cartTotalPrice: 0,
        navList: [],
        navSelect: '',
        searchType: 'goods'
      };
    },
    components: {},
    computed: {
      ...mapGetters(['siteInfo', 'defaultGoodsImage', 'member'])
    },
    created() {
      this.keyword = this.$route.query.keyword || '';
      this.$store.dispatch('site/siteInfo');
      this.getDefaultSearchWords();
      this.nav();
    },
    watch: {
      $route(curr) {
        this.initNav(curr.path);
        if (this.keyword !== curr.query.keyword) {
          this.keyword = curr.query.keyword;
        }
        if (curr.path == '/goods/list') {
          this.navSelect = '';
        }
      },
      member() {
        if (!this.member) {
          this.$store.commit('cart/SET_CART_COUNT', 0);
          this.cartTotalPrice = 0;
        }
      }
    },
    methods: {
      search() {
        if (this.searchType == 'goods') {
          this.defaultSearchWords = this.defaultSearchWords == '请输入您要查询的商品' ? '' : this.defaultSearchWords;
          let keyword = this.keyword ? this.keyword : this.defaultSearchWords;
          let query = {};
          if (keyword) {
            query.keyword = keyword;
          }
          this.$router.push({
            path: '/goods/list',
            query
          });
        } else {
          this.$router.push({
            path: '/street',
            query: {
              keyword: this.keyword
            }
          });
        }
      },
      getDefaultSearchWords() {
        apiDefaultSearchWords({}).then(res => {
          if (res && res.code == 0 && res.data.words) {
            this.defaultSearchWords = res.data.words;
          }
        });
      },
      nav() {
        navList({})
          .then(res => {
            if (res.code == 0 && res.data) {
              this.navList = res.data;
              for (let i in this.navList) {
                this.navList[i]['url'] = JSON.parse(this.navList[i]['nav_url']).url;
              }
              this.initNav(this.$route.path);
            }
          })
          .catch(err => {
            this.$message.error(err.message);
          });
      },
      initNav(path) {
        for (let i in this.navList) {
          if (this.navList[i]['url'] == path) {
            this.navSelect = path;
            continue;
          }
        }
      },
      navUrl(url, target) {
        if (!url) return;
        if (url.indexOf('http') == -1 || url.indexOf('https') == -1) {
          if (target) {
            let routeUrl = this.$router.resolve({
              path: url
            });
            window.open(routeUrl.href, '_blank');
          } else
            this.$router.push({
              path: url
            });
        } else {
          if (target) window.open(url);
          else window.location.href = url;
        }
      }
    }
  };
</script>

<style scoped lang="scss">
  .header-in {
    display: flex;
    align-items: center;
    width: $width;
    height: 100px;
    margin: auto;

    .header-left {
      max-width: 200px;
      max-height: 60px;
      margin-right: 40px;
      overflow: hidden;

      img {
        max-width: 100%;
        max-height: 100%;
      }
    }

    .header-content {
      overflow: hidden;
      flex: 1;

      &::-webkit-scrollbar {
        width: 10px;
        height: 5px;
      }

      &::-webkit-scrollbar-track {
        background: rgb(179, 177, 177);
        border-radius: 10px;
      }

      &::-webkit-scrollbar-thumb {
        background: rgb(136, 136, 136);
        border-radius: 10px;
      }
    }

    .header-right {
      display: flex;
      align-items: center;
      margin-left: 20px;
      width: 250px;
      height: 36px;
      border-bottom: 1px solid #f2f2f2;
      box-sizing: border-box;

      .iconfont {
        color: #999;
        margin-left: 10px;
        font-size: 18px;
      }

      input {
        height: 22px;
        background: none;
        outline: none;
        border: none;
        padding: 0 10px;
        font-size: 14px;
      }

      button {
        border: none;
        color: $base-color;
        font-size: 16px;
        padding: 0;
      }
    }

    nav {
      ul {
        margin: 0;
        padding: 0;
        width: 100%;
        height: 28px;
        overflow: hidden;

        li {
          cursor: pointer;
          list-style: none;
          margin-right: 20px;
          font-size: 16px;
          float: left;

          &:last-of-type {
            margin-right: 0;
          }
        }

        li:hover {
          color: $base-color;
        }

        .router-link-active {
          color: $base-color;
        }
      }
    }
  }
</style>
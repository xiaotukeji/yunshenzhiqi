<template>
  <div class="help-wrap">
    <el-breadcrumb separator="/" class="path">
      <el-breadcrumb-item :to="{ path: '/' }" class="path-home">首页</el-breadcrumb-item>
      <el-breadcrumb-item class="path-help">帮助</el-breadcrumb-item>
    </el-breadcrumb>
    <div class="help" v-loading="loading">
      <div class="menu">
        <div class="title">帮助列表</div>
        <div class="item" v-for="(item, index) in helpList" :key="index">
          <div :class="currentId == item.class_id ? 'active item-name' : 'item-name'" @click="menuOther(item.class_id)">{{ item.class_name }}</div>
        </div>
      </div>
      <div class="list-other">
        <div class="item-info">
          <div class="item" v-for="(item, index) in helpOther.list" :key="index" @click="detail(item.id)">
            <div class="item-title">{{ item.title }}</div>
            <div class="info">
              <div class="time">{{ $util.timeStampTurnTime(item.create_time) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import {
    helpList,
    helpOther
  } from '@/api/cms/help';

  export default {
    name: 'help',
    components: {},
    data: () => {
      return {
        helpList: [],
        helpOther: [],
        currentId: 0,
        loading: true
      };
    },
    head() {
      return {
        title: '帮助列表-' + this.$store.state.site.siteInfo.site_name
      };
    },
    created() {
      this.getInfo();
    },
    methods: {
      menuOther(id) {
        this.currentId = id;
        this.getHelpOtherInfo();
      },
      getInfo() {
        helpList().then(res => {
          if (res.code == 0 && res.data.length > 0) {
            this.currentId = res.data[0].class_id;
            this.helpList = res.data;
            this.getHelpOtherInfo();
          }
          this.loading = false;
        }).catch(err => {
          this.loading = false;
          this.$message.error(err.message);
        });
      },
      getHelpOtherInfo() {
        helpOther({
          class_id: this.currentId
        }).then(res => {
          if (res.code == 0 && res.data) {
            this.helpOther = res.data;
          }
        }).catch(err => {
          this.$message.error(err.message);
        });
      },
      detail(id) {
        this.$router.push({
          path: '/cms/help/detail',
          query: {
            id: id
          }
        });
      }
    }
  };
</script>
<style lang="scss" scoped>
  .help-wrap {
    background: #ffffff;
    width: 1210px;
    margin: 20px auto;

    .path {
      padding: 15px;
    }
  }

  .help {
    display: flex;
    padding-bottom: 20px;

    .menu {
      width: 210px;
      min-height: 300px;
      background: #ffffff;
      border: 1px solid #f1f1ff;
      flex-shrink: 0;

      .title {
        padding-left: 16px;
        background: #f8f8f8;
        font-size: $ns-font-size-base;
        height: 40px;
        line-height: 40px;
        cursor: pointer;
        color: #666666;
      }

      .item-name {
        font-size: $ns-font-size-base;
        cursor: pointer;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 40px;
        border-top: 1px solid #f1f1f1;
        padding-left: 25px;
        padding-right: 10px;
        height: 40px;
        background: #ffffff;
        color: #666666;

        &:hover {
          color: $base-color;
        }
      }

      .active {
        color: $base-color;
      }
    }
  }

  .list-other {
    margin-left: 20px;
    width: 80%;

    .item-info {
      padding: 10px;
      background-color: #ffffff;
      height: 300px;
      // border: 1px solid #e9e9e9;

      .item {
        border-bottom: 1px #f1f1f1 solid;
        padding: 10px 0;
        display: flex;
        justify-content: space-between;

        &:last-child {
          border-bottom: none;
        }

        &:first-child {
          padding-top: 0px;
        }

        .item-title {
          font-size: $ns-font-size-base;
          color: #333333;
          display: inline-block;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
          cursor: pointer;

          &:hover {
            color: $base-color;
          }
        }

        .info {
          padding-left: 5px;
          flex-shrink: 0;
        }
      }
    }
  }
</style>

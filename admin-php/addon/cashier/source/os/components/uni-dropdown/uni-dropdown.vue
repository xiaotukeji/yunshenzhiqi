<template>
  <view class="dropdown">
    <view class="dropdown-link" @click="open">
      <slot name="dropdown-link"></slot>
    </view>
    <uni-transition key="1" name="mask" mode-class="fade" :styles="maskClass" :duration="300" :show="showTrans" @click="close" />
    <uni-transition key="2" :mode-class="direction" class="dropdown-box" name="content" :style="dropdownClass" :duration="300" :show="showTrans">
      <view class="dropdown-content" @click="close">
        <slot name="dropdown"></slot>
      </view>
    </uni-transition>
  </view>
</template>

<script>
export default {
  name: "uniDropdown",
  props: {
    direction: {
      type: String,
      default: "slide-top",
    },
  },
  data() {
    return {
      showTrans: false,
      maskClass: {
        position: "fixed",
        zIndex: 1,
        bottom: 0,
        top: 0,
        left: 0,
        right: 0,
        backgroundColor: "rgba(0, 0, 0, 0)",
      },
      dropdownClass: {},
    };
  },
  methods: {
    open() {
      this.showTrans = true;
      const query = uni.createSelectorQuery().in(this);
      query.select(".dropdown-link").boundingClientRect((data) => {
        switch (this.direction) {
          case "slide-top":
            this.dropdownClass = {
              position: "absolute",
              zIndex: 1,
              top: "100%",
              right: "0px",
            };
            break;
          case "slide-bottom":
            this.dropdownClass = {
              position: "absolute",
              zIndex: 1,
              bottom: "100%",
              left: "0px",
            };
            break;

        }
      }).exec();
    },
    close() {
      this.showTrans = false;
    },
  },
};
</script>

<style lang="scss">
.dropdown-mask {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: none;
  z-index: 9999;
}

.dropdown {
  position: relative;
}

.dropdown-box {
  z-index: 999 !important;
}
</style>

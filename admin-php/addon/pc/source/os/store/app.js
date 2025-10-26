import {
  getCity
} from '@/api/address';

const state = {
  // 城市id
  city: "",

  // 语言包
  lang: "zh-cn",

  //首页浮层广告弹出次数
  indexFloatLayerNum: 0,

  //广告显示次数
  indexTopAdNum: 0,

  // 定位区域
  locationRegion: null,

  //判断首页广告显示
  is_show: false

}

const mutations = {
  SET_CITY: (state, value) => {
    state.city = value;
  },
  SET_LANG: (state, value) => {
    state.lang = value;
  },
  SET_FLOAT_LAYER: (state, value) => {
    state.indexFloatLayerNum = value;
    localStorage.setItem('indexFloatLayerNum', value);
  },
  SET_INDEXTOPADNUM: (state, value) => {
    state.indexTopAdNum = value;
  },
  SET_LOCATION_REGION: (state, value) => {
    state.locationRegion = value;
  },
  IS_SHOW_JUDGE: (state, value) => {
    state.is_show = value;
  }
}

const actions = {
  setCity({
    commit
  }, value) {
    commit('SET_CITY', value)
  },
  lang({
    commit
  }, value) {
    commit('SET_LANG', value)
  },
  get_city({
    commit
  }, item) {
    return new Promise((resolve, reject) => {
      return getCity({}).then(res => {
        resolve(res)
      }).catch(err => {
        reject(err)
      })
    })
  },

  is_show({
    commit
  }, item) {
    commit('IS_SHOW_JUDGE', item.is_show)
  },
}

export default {
  namespaced: true,
  state,
  mutations,
  actions
}
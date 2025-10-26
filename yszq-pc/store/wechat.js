import {
  loginCode,
  checkLogin,
  wechatLogin
} from "@/api/wechat"
import {
  setToken,
  getToken
} from "@/utils/auth"

const state = {
  token: getToken(),
  autoLoginRange: 0,
  member: ""
}

const mutations = {
  SET_TOKEN: (state, token) => {
    state.token = token
  }
}

const actions = {
  loginCode({
    commit
  }) {
    return new Promise((resolve, reject) => {
      return loginCode()
        .then(res => {
          const {
            code,
            message,
            data
          } = res

          if (code == 0) {

            resolve(res)
          }

          reject()
        })
        .catch(_err => {
          reject(_err)
        })
    })
  },
  checkLogin({
    commit
  }, userInfo) {
    const {
      key
    } = userInfo
    return new Promise((resolve, reject) => {
      return checkLogin(userInfo)
        .then(res => {
          const {
            code,
            message,
            data
          } = res

          if (code == 0) {
            commit("SET_TOKEN", data.token)
            setToken(data.token, userInfo.autoLoginRange)
            resolve(res)
          }

          reject()
        })
        .catch(_err => {
          reject(_err)
        })
    })
  },
  wechatLogin({
    commit
  }, userInfo) {
    const {
      mobile,
      key,
      code,
      captcha_id,
      captcha_code
    } = userInfo
    return new Promise((resolve, reject) => {
      return wechatLogin(userInfo)
        .then(res => {
          const {
            code,
            message,
            data
          } = res

          if (code == 0) {
            commit("SET_TOKEN", data.token)
            setToken(data.token, userInfo.autoLoginRange)
            resolve(res)
          }

          reject()
        })
        .catch(_err => {
          reject(_err)
        })
    })
  },

}

export default {
  namespaced: true,
  state,
  mutations,
  actions
}

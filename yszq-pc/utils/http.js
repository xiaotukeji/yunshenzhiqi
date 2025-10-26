import Config from "@/plugins/config.js"
import axios from "axios"
import {
  getToken
} from "./auth"

axios.defaults.baseURL = Config.baseUrl
axios.defaults.headers = {
  "X-Requested-With": "XMLHttpRequest",
  "content-type": "application/json"
}
axios.defaults.responseType = "json"
axios.defaults.timeout = 60 * 1000

/**
 * http单个请求
 * @param {object} params 请求参数
 * @param {integer} successCode 接口正常返回结果标识
 *
 * @returns Promise
 */
export default function request(params, successCode = 0, method = "POST") {

  var url = params.url // 请求路径
  var data = {
    app_type: "pc",
    app_type_name: "PC"
  }

  var token = getToken()
  if (token) data.token = token

  // 参数
  if (params.data != undefined) Object.assign(data, params.data)

  //异步
  return axios({
    url,
    method,
    data
  })
    .then(res => {
      const {
        code
      } = res.data || {}
      if (process.client && code == -3 && window.$nuxt.$route.name != 'close') {
        window.$nuxt.$router.push('/close');
        return;
      }
      if (code == successCode) return res.data
      else return Promise.reject(res.data)
    })
    .catch(error => {
      const {
        error_code
      } = error

      if (error_code === "TOKEN_ERROR") {
        error.message = '登录错误'
        vue.$store.dispatch("member/remove_token")
        if (params.forceLogin) {
          vue.$router.push(`/auth/login?redirect=${encodeURIComponent(vue.$router.history.current.fullPath)}`)
        }
      }

      return Promise.reject(error)
    })
}

/**
 * 并发请求
 * @param {array} params 并发请求参数数组，传入数组中对象的顺序要匹配 data 中 url
 * @var 该方法为并发请求，数据会在全部请求完之后才返回
 * 该方法不建议使用。
 */
export function conRequest(params) {
  if (Object.prototype.toString.call(params) != "[object Array]") {
    return Promise.reject({
      code: -1,
      msg: "参数必须为数组"
    })
  }

  //同步并发
  var quest = []
  for (var i = 0; i < url.length; i++) {
    quest.push(
      axios({
        url: params[i].url,
        method: method,
        params: params[i].data
      })
    )
  }

  axios
    .all(quest)
    .then(
      axios.spread(() => {
        // 请求全部完成后执行
        var res = []
        for (let i = 0; i < arguments.length; i++) {
          res.push(arguments[i].data)
        }

        return res
      })
    )
    .catch(error => {
      const {
        error_code
      } = error
      if (error_code === "TOKEN_ERROR") {
        error.message = '登录错误'
        vue.$store.dispatch("member/remove_token")
        if (params.forceLogin) {
          vue.$router.push(`/auth/login?redirect=${encodeURIComponent(vue.$router.history.current.fullPath)}`)
        }
      }

      return Promise.reject(error)
    })
}

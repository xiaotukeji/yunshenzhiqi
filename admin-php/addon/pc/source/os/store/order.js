const state = {
  // 普通待付款订单数据
  orderCreateGoodsData: "",

  //团购待付款订单数据
  groupbuyOrderCreateData: "",

  //秒杀待付款订单数据
  seckillOrderCreateData: "",

  //组合套餐待付款订单数据
  comboOrderCreateData: ""
}

const mutations = {
  SET_ORDER_CREATE_DATA: (state, value) => {
    state.orderCreateGoodsData = value
    if (value) {
      localStorage.setItem('orderCreateGoodsData', JSON.stringify(value));
    } else {
      localStorage.removeItem('orderCreateGoodsData');
    }
  },
  SET_GROUPBUY_ORDER_CREATE_DATA: (state, value) => {
    state.groupbuyOrderCreateData = value
    if (value) {
      localStorage.setItem('groupbuyOrderCreateData', JSON.stringify(value));
    } else {
      localStorage.removeItem('groupbuyOrderCreateData');
    }
  },
  SET_SECKILL_ORDER_CREATE_DATA: (state, value) => {
    state.seckillOrderCreateData = value
    if (value) {
      localStorage.setItem('seckillOrderCreateData', JSON.stringify(value));
    } else {
      localStorage.removeItem('seckillOrderCreateData');
    }

  },
  SET_COMBO_ORDER_CREATE_DATA: (state, value) => {
    state.comboOrderCreateData = value
    if (value) {
      localStorage.setItem('comboOrderCreateData', JSON.stringify(value));
    } else {
      localStorage.removeItem('comboOrderCreateData');
    }
  }
}

const actions = {
  setOrderCreateData({
    commit,
    state
  }, data) {
    commit("SET_ORDER_CREATE_DATA", data)
  },
  removeOrderCreateData({
    commit
  }) {
    commit("SET_ORDER_CREATE_DATA", "")
  },
  setGroupbuyOrderCreateData({
    commit,
    state
  }, data) {
    commit("SET_GROUPBUY_ORDER_CREATE_DATA", data)
  },
  removeGroupbuyOrderCreateData({
    commit
  }) {
    commit("SET_GROUPBUY_ORDER_CREATE_DATA", "")
  },

  setSeckillOrderCreateData({
    commit,
    state
  }, data) {
    commit("SET_SECKILL_ORDER_CREATE_DATA", data)
  },
  removeSeckillOrderCreateData({
    commit
  }) {
    commit("SET_SECKILL_ORDER_CREATE_DATA", "")
  },
  setComboOrderCreateData({
    commit,
    state
  }, data) {
    commit("SET_COMBO_ORDER_CREATE_DATA", data)
  },
  removeComboOrderCreateData({
    commit
  }) {
    commit("SET_COMBO_ORDER_CREATE_DATA", "")
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions
}

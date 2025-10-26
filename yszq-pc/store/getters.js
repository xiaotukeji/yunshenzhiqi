const getters = {
  // 用户TOKRN
  token: state => state.member.token,
  lang: state => state.app.lang,
  city: state => state.app.city,
  locationRegion: state => state.app.locationRegion,
  // 自动登录时长
  autoLoginRange: state => state.member.autoLoginRange,
  wapQrcode: state => state.site.siteQrCode,
  // 会员详情
  member: state => state.member.member,

  copyRight: state => state.site.copyRight,
  siteInfo: state => state.site.siteInfo,
  addonIsExit: state => state.site.addons,

  // 购物车商品总数
  cartCount: state => state.cart.cartCount,
  //
  is_show: state => state.app.is_show,

  defaultGoodsImage: state => state.site.defaultFiles.goods,
  defaultHeadImage: state => state.site.defaultFiles.head,
  defaultShopImage: state => state.site.defaultFiles.store,
  defaultCityImage: state => state.site.defaultFiles.default_city_img,
  defaultSupplyImage: state => state.site.defaultFiles.default_supply_img,
  defaultStoreImage: state => state.site.defaultFiles.store,
  defaultCategoryImage: state => state.site.defaultFiles.goods,
  defaultBrandImage: state => state.site.defaultFiles.goods,
  defaultArticleImage: state => state.site.defaultFiles.article,

  // 普通待付款订单
  // orderCreateGoodsData: state => {
  //   let storage = localStorage.getItem('orderCreateGoodsData');
  //   if(storage) storage = JSON.parse(storage);
  //   return storage;
  // },
  orderCreateGoodsData: state => state.order.orderCreateGoodsData,


  //团购待付款订单
  groupbuyOrderCreateData: state => {
    let storage = localStorage.getItem('groupbuyOrderCreateData');
    if(storage) storage = JSON.parse(storage);
    return storage;
  },

  //秒杀待付款订单
  seckillOrderCreateData: state => {
    let storage = localStorage.getItem('seckillOrderCreateData');
    if(storage) storage = JSON.parse(storage);
    return storage;
  },

  //组合套餐待付款订单
  comboOrderCreateData: state => {
    let storage = localStorage.getItem('comboOrderCreateData');
    if(storage) storage = JSON.parse(storage);
    return storage;
  }
}

export default getters;

export default [{
		title: '开单',
		icon: 'iconkaidan',
		path: '/pages/billing/index',
		name: 'billing',
		keyCode: 'F6' // 触发按键
	},
	{
		title: '售卡',
		icon: 'iconqia',
		path: '/pages/buycard/index',
		name: 'buycard',
		keyCode: 'F7' // 触发按键
	},
	{
		title: '充值',
		icon: 'iconchongzhidingdan',
		path: '/pages/recharge/index',
		name: 'recharge',
		keyCode: 'F8' // 触发按键
	},
	{
		title: '订单',
		icon: 'icondingdan',
		path: '/pages/order/orderlist',
		name: 'order_list',
		keyCode: 'F9' // 触发按键
	},
	{
		title: '会员',
		icon: 'iconkehuguanli',
		path: '/pages/member/list',
		name: 'member_list',
		keyCode: 'F10' // 触发按键
	},
	{
		title: '核销',
		icon: 'iconhexiao',
		path: '/pages/verify/index',
		name: 'verify_index',
		keyCode: 'F11' // 触发按键
	},
	{
		title: '更多',
		icon: 'iconicon_yingyongguanli',
		childshow: true,
		children: [{
				title: '收银',
				children: [{
						title: '开单',
						icon: 'iconkaidan',
						path: '/pages/billing/index',
						name: 'billing'
					},
					{
						title: '售卡',
						icon: 'iconqia',
						path: '/pages/buycard/index',
						name: 'buycard'
					},
					{
						title: '核销',
						icon: 'iconhexiao',
						path: '/pages/verify/index',
						name: 'verify_index'
					},
					{
						title: '预约',
						icon: 'iconyuyueguanli',
						path: '/pages/reserve/index',
						name: 'reserve_index',
						addon: 'store'
					},
					{
						title: '充值',
						icon: 'iconchongzhidingdan',
						path: '/pages/recharge/index',
						name: 'recharge'
					},
					{
						title: '交班',
						icon: 'icon12jiaobanbiao',
						path: '/pages/index/change_shifts',
					}
				]
			},
			{
				title: '管理',
				children: [{
						title: '商品管理',
						icon: 'iconshangpinguanli',
						path: '/pages/goods/goodslist',
						name: 'goods_list'
					},
					{
						title: '会员管理',
						icon: 'iconkehuguanli',
						path: '/pages/member/list',
						name: 'member_list'
					},
					{
						title: '员工管理',
						icon: 'iconjishi',
						path: '/pages/user/list',
						name: 'user_list'
					},
					{
						title: '订单管理',
						icon: 'icondingdan',
						path: '/pages/order/orderlist',
						name: 'order_list'
					},
					{
						title: '退款维权',
						icon: 'iconjishi',
						path: '/pages/order/orderrefund',
						name: 'order_refund_list'
					},
					{
						title: '交班记录',
						icon: 'icon12jiaobanbiao',
						path: '/pages/index/change_shiftsrecord',
						name: 'change_shifts_record_list'
					}
				]
			},
			{
				title: '营销',
				name : 'promotion',
				children:[{
					title:'优惠券',
					icon:'icon31hongbao',
					path:'/pages/marketing/coupon_list',
					name:'coupon_list'
				}]
			},
			{
				title: '库存',
				name: 'stock',
				children: [{
						title: '出库单',
						icon: 'iconchukudan',
						path: '/pages/stock/wastage',
						name: 'stock_wastage',
						addon: 'stock'
					},
					{
						title: '入库单',
						icon: 'iconrukudan',
						path: '/pages/stock/storage',
						name: 'stock_storage',
						addon: 'stock'
					},
					{
						title: '调拨单',
						icon: 'icontiaobodan',
						path: '/pages/stock/allocate',
						name: 'stock_allocate',
						addon: 'stock'
					},
					{
						title: '库存盘点',
						icon: 'iconkucunpandian',
						path: '/pages/stock/check',
						name: 'stock_check',
						addon: 'stock'
					},
					{
						title: '库存管理',
						icon: 'iconkucunguanli',
						path: '/pages/stock/manage',
						name: 'stock_manage',
						addon: 'stock'
					}
				]
			},
			{
				title: '数据',
				children: [{
						title: '门店结算',
						icon: 'iconshourujiesuan',
						path: '/pages/store/settlement',
						addon: 'store',
						name: 'store_settlement'
					},
					{
						title: '营业数据',
						icon: 'iconyingyeshujuguanliputong',
						path: '/pages/stat/index',
					}
				]
			},
			{
				title: '设置',
				children: [{
						title: '门店设置',
						icon: 'icongongyingshang',
						path: '/pages/store/index',
						name: 'store_config_root',
					},
					{
						title: '收款设置',
						icon: 'iconshoukuan-',
						path: '/pages/collectmoney/config',
						// name: 'collectmoney_config',
					},
					{
						title: '预约设置',
						icon: 'iconyuyue',
						path: '/pages/reserve/config',
						name: 'reserve_config',
						addon: 'store'
					},
					{
						title: '小票打印',
						icon: 'icondayin',
						name: 'printer_config',
						path: '/pages/printer/list',
					},
					{
						title: '配送员',
						icon: 'iconpeisong',
						path: '/pages/store/deliver',
						name: 'store_deliver_config'
					},
					{
						title: '电子秤管理',
						icon: 'icondianzicheng',
						name: 'heavt_config',
						path: '/pages/scale/list',
						addon: 'scale'
					},
					{
						title: '本机设置',
						icon: 'iconbenji',
						path: '/pages/local/config',
						name: 'local_config',
					},
				]
			}
		]
	}
]
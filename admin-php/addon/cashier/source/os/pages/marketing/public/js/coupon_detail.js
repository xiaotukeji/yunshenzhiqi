import {getCouponDetail } from '@/api/marketing.js'
export default {
    data() {
        return {
            couponsData: {
                coupon_type_id:'',
                coupon_name: "",
                type: "reward",
                money: "",
                discount: "",
                discount_limit: "",
                at_least: "",
                is_show: 1,
                count: "",
                max_fetch: "",
                image: "",
                validity_type: 0,
                end_time: this.$util.timeFormat(Date.parse(new Date()) / 1000),
                fixed_term: 0,
                goods_type:1,
                lead_count:0,
                used_count:0,
                use_channel:'',
                use_store:'',
                use_store_list:[],
                goods_list:[]
            },
            loading:false,
            cols: [{
				field: 'account_data',
				width: 20,
				title: '会员信息',
				align: 'left',
				templet: data => {
					let img = this.$util.img(data.headimg);
					let html = `
							<view class="member-content flex">
								<image class="member-img" src="${img}" mode="aspectFit"/>
                                <view class="flex flex-col justify-between">
                                    <text class="member-nickname multi-hidden">${data.nickname}</text>
                                    <text class="member-mobile multi-hidden">${data.mobile}</text>
                                </view>
							</view>
						`;
					return html;
				}
			}, {
				field: 'coupon_name',
				width: 15,
				title: '优惠券',
				align: 'left',
			},{
                title: '类型',
                width: 10,
                unresize: 'false',
                templet: data=> {
                    return data.type == 'reward' ? '满减券' : '折扣券';
                }
            },{
				field: 'get_type_name',
				width: 15,
				title: '获取方式',
				align: 'left',
			},{
                title: '状态',
                width: 10,
                unresize: 'false',
                templet: data=> {
                    var str = '';
                    switch (data.state) {
                        case 1:
                            str = '已领取';
                            break;
                        case 2:
                            str = '已使用';
                            break;
                        case 3:
                            str = '已过期';
                            break;
                    }
                    return str;
                }
            },{
                title: '领取时间',
                width: 15,
                unresize: 'false',
                templet: data=> {
                    return this.$util.timeFormat(Date.parse(new Date(data.fetch_time)));
                }
            }, {
                title: '使用时间',
                width: 15,
                templet: data=> {
                    return data.use_time ? this.$util.timeFormat(Date.parse(new Date(data.use_time))) : '';
                }
            },],
            statusList: [{
				value: '',
				label: '全部'
			},{
				value: '1',
				label: '已领取'
			}, {
				value: '2',
				label: '已使用'
			},
			{
				value: '3',
				label: '已过期'
			}],
            option:{
                page_size: 9,
                coupon_type_id:'',
                state: '',
            },
            goodsListCols:[{
				field: 'goods_name',
				width: 60,
				title: '商品名称',
				align: 'left',
			},{
                title: '价格',
                width: 20,
                unresize: 'false',
                templet: data=> {
                    return data.price || '0.00';
                }
            },{
                title: '库存',
                width: 20,
                unresize: 'false',
                templet: data=> {
                    return data.goods_stock || 0;
                }
            },]
        }
    },
    onLoad(option) {
        if(option.coupon_type_id){
            this.couponsData.coupon_type_id = option.coupon_type_id
            this.option.coupon_type_id = option.coupon_type_id
            this.getData(option.coupon_type_id)
        }
    },
    methods: {
        getData(coupon_type_id) {
            this.loading = true
            getCouponDetail(coupon_type_id).then(res=>{
            	let data = res.data;
            	if(res.code>=0 && data) {
					Object.keys(this.couponsData).forEach(key => {
						this.couponsData[key] = data.info[key]
						if (key == 'end_time') this.couponsData[key] = this.couponsData.end_time = this.$util.timeFormat(Date.parse(new Date(data.info[key])))
					})
				}
                this.loading = false
            })
        },
        queryRecord(val){
            this.option.state = val
            this.$refs.couponListTable.load({
				page: 1
			});
        },
        backFn() {
			this.$util.redirectTo('/pages/marketing/coupon_list');
		},
    }
}
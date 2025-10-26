<template>
    <unipopup ref="dialogRef" type="center" :maskClick="false">
        <view class="stock-dialog-wrap">
            <view class="stock-dialog-head">
                <text>商品选择</text>
                <text class="iconfont iconguanbi1" @click="$emit('change', false)"></text>
            </view>
            <view class="stock-dialog-body">
                <view class="tree">
                    <scroll-view scroll-y="true" class="list-wrap">
                        <view class="item" :class="{ 'active': option.category_id === '' }" @click="itemClick({ category_id: '', child_num: 0 })">
                            <view class="icon"></view>
                            <view>全部分类</view>
                        </view>
                        <view v-for="(item, key) in goodsCategoryList" :key="key">
                            <view class="item" :class="{ 'active': option.category_id === item.category_id }" @click="itemClick(item)">
                                <view class="icon" :class="{ 'active': activeList.indexOf(item.category_id) != -1 }">
                                    <text v-if="item.child_num" class="iconfont iconsanjiao_xia"></text>
                                </view>
                                <view>{{ item.title }}</view>
                            </view>
                            <template v-if="item.child_num">
                                <view v-show="activeList.indexOf(item.category_id) != -1" v-for="(item2, key2) in item.children" :key="key2" class="level">
                                    <view class="item" :class="{ 'active': option.category_id === item2.category_id }" @click="itemClick(item2)">
                                        <view class="icon" :class="{ 'active': activeList.indexOf(item2.category_id) != -1 }">
                                            <text v-if="item2.child_num" class="iconfont iconsanjiao_xia"></text>
                                        </view>
                                        <view>{{ item2.title }}</view>
                                    </view>
                                    <template>
                                        <view v-show="activeList.indexOf(item2.category_id) != -1" v-for="(item3, key3) in item2.children" :key="key3" class="level">
                                            <view class="item item2" @click="itemClick(item3)">
                                                <view class="icon"></view>
                                                <view>{{ item3.title }}</view>
                                            </view>
                                        </view>
                                    </template>
                                </view>
                            </template>
                        </view>
                    </scroll-view>
                </view>
                <view class="stock-dialog-table">
                    <view class="search  common-form">
                        <view class="common-form-item">
                            <view class="form-inline">
                                <view class="form-input-inline">
                                    <input type="text" v-model="option.search_text" @confirm="getStoreGoods" placeholder="请输入产品名称/规格/编码" class="form-input" />
                                </view>
                            </view>
                            <view class="form-inline common-btn-wrap">
                                <button type="default" class="screen-btn" @click="getStoreGoods">筛选</button>
                            </view>
                        </view>
                    </view>
                    <uniDataTable :url="url" :option="option" :cols="cols" :pagesize="8" ref="goodsListTable" @checkBox="checkBox" @tableData="tableDataChange"></uniDataTable>
                </view>

            </view>
            <view class="btn">
                <button type="primary" class="default-btn submit" @click="submit('close')">选中</button>
                <button type="primary" class="default-btn" @click="$emit('change', false)">取消</button>
            </view>
        </view>
    </unipopup>
</template>
<script>
import unipopup from '@/components/uni-popup/uni-popup.vue';
import uniDataTable from '@/components/uni-data-table/uni-data-table.vue';
import {getManageGoodsCategory} from '@/api/goods.js';

export default {
    name: 'stockDialog',
    components: {
        unipopup,
        uniDataTable
    },
    model: {
        prop: 'value',
        event: 'change'
    },
    props: {
        value: {
            type: Boolean,
            default: false
        },
        params: {
            type: Object,
            default: ()=>{
                return {}
            }
        },
		apiType: {
            type: String,
            default: 'sku' //选择是sku 还是 spu
        }
    },
    data() {
        return {
            goodsCategoryList: {},
            activeList: [],//下拉激活
            option: {
                category_id: '',
                search_text: '',
                is_weigh: 0,
                page_size: 8,
            },
            cols: [{
                width: 6,
                align: 'center',
                checkbox: true
            }, {
                field: 'account_data',
                width: 50,
                title: '商品信息',
                align: 'left',
                templet: data => {
                    let img = this.$util.img(data.sku_image);
                    let html = `
							<view class="goods-content">
								<image class="goods-img" src="${img}" mode="aspectFit"/>
								<text class="goods-name multi-hidden"  title="${data.sku_name}">${data.sku_name}</text>
							</view>
						`;
                    return html;
                }
            }, {
                field: 'real_stock',
                width: 22,
                title: '库存',
                align: 'center',
                templet: data => {
                    return (data.real_stock || 0);
                }
            }, {
                width: 22,
                title: '单位',
                templet: data => {
                    return (data.unit || '件');
                }
            }],
			checkList: {},
			url: '/stock/storeapi/manage/getStoreGoods'
        }
    },
    watch: {
        value: {
            handler: function (val) {
                if (val) {
                    this.$nextTick(() => {
                        this.option = Object.assign(this.option, this.params);
                        if (this.params.temp_store_id && this.params.temp_store_id == '') {
                            delete this.option.temp_store_id
                        }
                        this.$refs.dialogRef.open()
                    })

                } else {
                    this.$nextTick(() => {
                        this.option = Object(this.option, {
                            category_id: '',
                            search_text: '',
                            is_weigh: 0,
                            page: 1,
                            page_size: 8,
                        });
						this.checkList = {};
                        this.$refs.dialogRef.close()
                    })
                }
            },
            immediate: true
        },
		apiType: {
			handler: function (val) {
			    if(val == 'sku'){
					this.cols = [{
					    width: 6,
					    align: 'center',
					    checkbox: true
					}, {
					    field: 'account_data',
					    width: 50,
					    title: '商品信息',
					    align: 'left',
					    templet: data => {
					        let img = this.$util.img(data.sku_image);
					        let html = `
									<view class="goods-content">
										<image class="goods-img" src="${img}" mode="aspectFit"/>
										<text class="goods-name multi-hidden"  title="${data.sku_name}">${data.sku_name}</text>
									</view>
								`;
					        return html;
					    }
					}, {
					    field: 'real_stock',
					    width: 22,
					    title: '库存',
					    align: 'center',
					    templet: data => {
					        return (data.real_stock || 0);
					    }
					}, {
					    width: 22,
					    title: '单位',
					    templet: data => {
					        return (data.unit || '件');
					    }
					}];
					this.url = '/stock/storeapi/manage/getStoreGoods';
				}else if(val == 'spu'){
					this.cols = [{
					    width: 6,
					    align: 'center',
					    checkbox: true
					}, {
					    field: 'account_data',
					    width: 50,
					    title: '商品信息',
					    align: 'left',
					    templet: data => {
					        let img = this.$util.img(data.goods_image);
					        let html = `
									<view class="goods-content">
										<image class="goods-img" src="${img}" mode="aspectFit"/>
										<text class="goods-name multi-hidden"  title="${data.goods_name}">${data.goods_name}</text>
									</view>
								`;
					        return html;
					    }
					}, {
					    field: 'goods_stock',
					    width: 22,
					    title: '库存',
					    align: 'center',
					    templet: data => {
					        return (data.goods_stock || 0);
					    }
					}, {
					    width: 22,
					    title: '商品类型',
					    templet: data => {
					        return (data.goods_class_name || '--');
					    }
					}];
					this.url = '/cashier/storeapi/goods/getGoodsListBySelect';
				}
			},
			immediate: true
		}
    },
    mounted() {
        this.getGoodsCategory()
    },
    methods: {
        getGoodsCategory() {
			getManageGoodsCategory().then(res=>{
				uni.hideLoading();
				if (res.data && Object.keys(res.data)) {
				    this.goodsCategoryList = res.data
				} else {
				    this.$util.showToast({
				        title: res.message
				    });
				}
			})
        },
        itemClick(item) {//tree点击
            this.option.category_id = item.category_id;
            var index = this.activeList.indexOf(item.category_id);
            if (item.child_num && index === -1) {
                this.activeList.push(item.category_id);
            } else if (item.child_num && index != -1) {
                this.activeList.splice(index, 1);
            }
            this.$forceUpdate();
            this.getStoreGoods();
        },
        getStoreGoods() {//表格查询
            this.$refs.goodsListTable.load({
                page: 1
            });
        },
        checkBox(list, listIndex) {
			this.checkList[this.$refs.goodsListTable.page] = {};
			this.checkList[this.$refs.goodsListTable.page].data = list;
			this.checkList[this.$refs.goodsListTable.page].index = listIndex;
        },
        tableDataChange(){
			if(this.checkList[this.$refs.goodsListTable.page])
				this.$refs.goodsListTable.defaultSelectData(this.checkList[this.$refs.goodsListTable.page].data, this.checkList[this.$refs.goodsListTable.page].index);
        },
        submit(val) {
            if (!Object.values(this.checkList).length) {
                this.$util.showToast({
                    title: '请选择商品'
				});
                return false
            }
			let data = [];
			Object.values(this.checkList).forEach((item,index)=>{
				data = data.concat(item.data)
			});
            this.$emit('selectGoods', data);
            if(val !='submit'){
                this.$emit('change', false);
            }else{
                this.$refs.goodsListTable.clearCheck();
            }
			this.checkList = [];
        }
    }
}
</script>
<style lang="scss" scoped>
.stock-dialog-wrap {
    background-color: #fff;
    border-radius: 0.05rem;
    width: 9rem;

    .stock-dialog-head {
        padding: 0 0.15rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.15rem;
        height: 0.45rem;
        border-bottom: 0.01rem solid #e8eaec;

        .iconguanbi1 {
            font-size: $uni-font-size-lg;
        }
    }

    .stock-dialog-body {
        width: 100%;
        height: 7.3rem;
        padding: 0.1rem 0.2rem 0 0.2rem;
        box-sizing: border-box;
        display: flex;

        .tree {
            width: 1.8rem;
            height: 7.1rem;
            overflow-y: auto;
            border-right: 0.01rem solid #e8eaec;
            flex-shrink: 0;
            flex-basis: auto;
            flex-grow: 0;
            box-sizing: border-box;

            .list-wrap {
                width: 100%;
                height: 100%;

                >view {
                    box-sizing: border-box;
                    width: 100%;
                }

                view.item {
                    display: flex;
                    align-items: center;
                    width: 100%;
                    box-sizing: border-box;
                    line-height: 0.3rem;
                    min-height: 0.3rem;
                    font-weight: 500;

                    &.active {

                        .icon,
                        view {
                            color: $primary-color !important;
                        }

                        background-color: #f7f7f7;
                    }

                    &:hover {
                        background-color: #f7f7f7;
                    }

                    .icon {
                        width: 0.2rem;
                        height: 0.3rem;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        transform: rotate(-90deg);
                        transition: all ease 0.5s;

                        &.active {
                            transform: rotate(-45deg);
                        }
                    }
                }

                .level {
                    width: 100%;
                    box-sizing: border-box;

                    .item {
                        padding-left: 0.2rem;
                    }

                    .item2 {
                        padding-left: 0.4rem;
                    }

                }
            }

        }

        .stock-dialog-table {
            width: 6.6rem;
            margin-left: 0.2rem;

            .search {
                display: flex;
                justify-content: flex-end;
            }

        }

    }

    .btn {
        display: flex;
        justify-content: flex-end;
        border-top: 0.01rem solid #e8eaec;
        padding: 0.1rem 0.2rem 0.1rem 0.2rem;
        height: 0.38rem;

        .default-btn,
        .primary-btn {
            margin: 0;
        }

        .default-btn {
            
            border: 0.01rem solid #e8eaec !important;
        }
        .submit{
            margin-right: 0.15rem;
        }
        .default-btn::after {
            display: none;

        }
    }

    .common-form .common-btn-wrap {
        margin-left: 0;
    }

    .common-form .common-btn-wrap .screen-btn {
        margin-right: 0;
    }

    .common-form .common-form-item {
        margin-bottom: 0.1rem;
    }

    /deep/ .goods-content {
        display: flex;

        .goods-img {
            margin-right: 0.1rem;
            width: 0.5rem;
            height: 0.5rem;
            flex-shrink: 0;
            flex-basis: auto;
            flex-grow: 0;
        }
    }
}
</style>

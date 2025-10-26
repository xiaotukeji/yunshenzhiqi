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
							<view class="form-input-inline" v-if="isInstallSupply == 1">
								<select-lay :zindex="10" :value="option.supplier_id" name="supplier_id" placeholder="请选择供应商" :options="supplierList" @selectitem="selectSupplier"/>
							</view>
							<view class="form-input-inline">
								<select-lay :zindex="10" :value="option.brand_id" name="brand_id" placeholder="请选择品牌" :options="brandList" @selectitem="selectBrand"/>
							</view>
							<view class="form-input-inline">
								<select-lay :zindex="10" :value="option.goods_class" name="goods_class" placeholder="请选择类型" :options="goodsClassList" @selectitem="selectGoodsClass"/>
							</view>
                            <view class="form-inline">
                                <view class="form-input-inline">
                                    <input type="text" v-model="option.search_text" @confirm="getStoreGoods" placeholder="请输入名称/编码" class="form-input" />
                                </view>
                            </view>
                            <view class="form-inline common-btn-wrap">
                                <button type="default" class="screen-btn" @click="getStoreGoods">筛选</button>
                            </view>
                        </view>
                    </view>
                    <uniDataTable class="goods-table" pk="sku_id" :url="url" :option="option" :cols="cols" :pagesize="8" ref="goodsListTable"></uniDataTable>
                </view>

            </view>
            <view class="btn">
				<button type="primary" class="primary-btn submit" @click="submit('close')">选中</button>
                <button type="primary" class="default-btn" @click="$emit('change', false)">取消</button>
            </view>
        </view>
    </unipopup>
</template>
<script>
import unipopup from '@/components/uni-popup/uni-popup.vue';
import uniDataTable from '@/components/uni-data-table/uni-data-table-new.vue';
import {getManageGoodsCategory,getGoodsSceen,getSkuListBySelect} from '@/api/goods.js';

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
		goodsClass:{
			type: Array,
			default: ()=>{
				return [1,4,5,6];
			},
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
				goods_class_all:'',
				goods_class:'',
				supplier_id:'',
				brand_id:'',
            },
			checkList: {},
			cols: [],
			url: '',
			goodsClassList: [],
			isInstallSupply:0,
			supplierList:[],
			brandList:[],
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
    },
    mounted() {
		this.skuConfig();
        this.getGoodsCategory();
		this.getScreen();
		this.getGoodsClassList();
    },
    methods: {
		skuConfig(){
			this.cols = [{
			    width: 20,
			    align: 'center',
			    checkbox: true,
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
			    field: 'stock',
			    width: 22,
			    title: '库存',
			    align: 'center',
			    templet: data => {
			        return (data.stock || 0);
			    }
			}, {
			    width: 22,
			    title: '单位',
			    templet: data => {
			        return (data.unit || '件');
			    }
			}];
			this.url = '/cashier/storeapi/goods/getSkuListBySelect';
		},
		selectGoodsClass(index) {
			this.option.goods_class = index == -1 ? '' : this.goodsClassList[index].value.toString();
			this.getStoreGoods();
		},
		selectBrand(index){
			this.option.brand_id = index == -1 ? '' : this.brandList[index].value.toString();
			this.getStoreGoods();
		},
		selectSupplier(index){
			this.option.supplier_id = index == -1 ? '' : this.supplierList[index].value.toString();
			this.getStoreGoods();
		},
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
		getScreen(){
			getGoodsSceen().then(res=>{
				this.isInstallSupply = res.data.is_install_supply;
				(res.data.supplier_list || []).forEach((item) => {
					this.supplierList.push({
						value : item.supplier_id,
						label : item.title,
					})
				})
				res.data.brand_list.forEach((item) => {
					this.brandList.push({
						value : item.brand_id,
						label : item.brand_name,
					})
				})
			})
		},
		getGoodsClassList(){
			let goodsClassList = [
				{
					value: this.$util.goodsClassDict.real,
					label: '实物商品'
				}, {
					value: this.$util.goodsClassDict.service,
					label: '服务项目'
				}, {
					value: this.$util.goodsClassDict.card,
					label: '卡项套餐'
				}, {
					value: this.$util.goodsClassDict.weigh,
					label: '称重商品'
				}
			];
			let goods_class_all = [];
			goodsClassList.forEach((item)=>{
				if(this.goodsClass.indexOf(item.value) > -1){
					this.goodsClassList.push(item);
					goods_class_all.push(item.value);
				}
			})
			this.option.goods_class_all = goods_class_all.toString();
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
        submit(action) {
			let res = this.$refs.goodsListTable.getSelectData();
			if(res.selectedNum == 0){
				this.$util.showToast({
				    title: '请选择商品'
				});
				return false
			}
			
			if(res.allSelected){
				let option = this.$util.deepClone(this.option);
				option.unselected_sku_ids = Object.keys(res.unselectedData).toString();
				option.page_size = 0;
				uni.showLoading({
					title: '数据获取中'
				});
				getSkuListBySelect(option).then(res=>{
					uni.hideLoading();
					this.$emit('selectGoods', res.data.list);
					this.$refs.goodsListTable.clearCheck();
					if(action == 'close'){
						this.$emit('change', false);
					}
				})
			}else{
				this.$emit('selectGoods', Object.values(res.selectedData));
				this.$refs.goodsListTable.clearCheck();
				if(action == 'close'){
					this.$emit('change', false);
				}
			}
        }
    }
}
</script>
<style lang="scss" scoped>
.stock-dialog-wrap {
    background-color: #fff;
    border-radius: 0.05rem;
    width: 100%;
	height: 75vh;
	

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
        // height: 7.3rem;
		height: calc(100% - 0.45rem - 0.58rem);
        padding: 0.1rem 0.2rem 0 0.2rem;
        box-sizing: border-box;
        display: flex;

        .tree {
            width: 1.8rem;
            // height: 7.1rem;
			height: 100%;
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
			display: flex;
			flex-direction: column;
			height: 100%;
            .search {
                display: flex;
                justify-content: flex-end;
            }
			.goods-table /deep/{
				flex: 1;
				height: 0;
				.tbody{
					height: calc( 100% - 0.5rem - 0.5rem );
					overflow-y: auto;
					&::-webkit-scrollbar{
						width: 0.06rem;
						height: 0.06rem;
						background-color: rgba(0, 0, 0, 0);
					}
					&::-webkit-scrollbar-button{
						display: none;
					}
					&::-webkit-scrollbar-thumb{
						border-radius: 0.06rem;
						box-shadow: inset 0 0 0.06rem rgba(45, 43, 43, 0.45);
						background-color: #ddd;
					}
					&::-webkit-scrollbar-track{
						background-color: transparent;
					}
				}
			}
        }

    }

    .btn {
        display: flex;
        justify-content: flex-end;
        border-top: 0.01rem solid #e8eaec;
        padding: 0.1rem 0.2rem 0.1rem 0.2rem;
		box-sizing: border-box;
		height: 0.58rem;

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
	
	.common-form{
		.common-btn-wrap {
		    margin-left: 0;
			.screen-btn {
			    margin-right: 0;
				padding-left:14px;
				padding-right:14px;
			}
		}
		.common-form-item {
		    margin-bottom: 0.1rem;
			.form-input-inline {
			    width: 1.3rem;
			}
		}
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

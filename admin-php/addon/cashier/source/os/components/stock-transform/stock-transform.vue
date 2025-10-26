<template>
	<unipopup ref="stockTransformRef" type="center">
		<view class="stockTransform-detail-wrap">
			<view class="detail-head">
				库存转换
				<text class="iconfont iconguanbi1" @click="$refs.stockTransformRef.close()"></text>
			</view>
			<view class="stockTransform-body">
				<view class="table">
					<view class="table-th table-all">
						<view class="table-td" style="width:19%">操作</view>
						<view class="table-td" style="width:24%">规格</view>
						<view class="table-td" style="width:19%">基本单位</view>
						<view class="table-td" style="width:19%">当前库存</view>
						<view class="table-td" style="width:19%">变动库存</view>
					</view>
					<view class="table-tr table-all">
						<view class="table-td" style="width:19%">出库</view>
						<view class="table-td" style="width:24%">
							<select-lay :zindex="60" :value="stockTransformParams.output_sku_id" name="output_sku_id" placeholder="请选择商品规格" :options="goodsSkuList" @selectitem="outputSelect"/>
						</view>
						<view class="table-td" style="width:19%">
							<text>{{stockTransformParams.output_sku_stock_transform_unit||'--'}}</text>
						</view>
						<view class="table-td" style="width:19%">
							<text>{{stockTransformParams.output_sku_stock||'--'}}</text>
						</view>
						<view class="table-td" style="width:19%">
							<text>{{stockTransformParams.output_sku_num||'--'}}</text>
						</view>
					</view>
					<view class="table-tr table-all">
						<view class="table-td" style="width:19%">入库</view>
						<view class="table-td" style="width:24%">
							<select-lay :zindex="10" :value="stockTransformParams.input_sku_id" name="input_sku_id" placeholder="请选择商品规格" :options="goodsSkuList" @selectitem="inputSelect"/>
						</view>
						<view class="table-td" style="width:19%">
							<text>{{stockTransformParams.input_sku_stock_transform_unit||'--'}}</text>
						</view>
						<view class="table-td" style="width:19%">
							<text>{{stockTransformParams.input_sku_stock||'--'}}</text>
						</view>
						<view class="table-td" style="width:19%">
							<text>{{stockTransformParams.input_sku_num||'--'}}</text>
						</view>
					</view>
				</view>
				<view class="flex stock_transform_least_common_multiple_change_num">
					<view>变动数量：最小公倍数为 <text class="text">{{stockTransformParams.stock_transform_least_common_multiple_change_num||0}}</text> ，变动</view>
					<input type="text" v-model="stockTransformParams.least_common_multiple_change_num" @input="inputChange" placeholder="" class="input" />
					<view>个最小公倍数</view>
				</view>
				<view class="flex remark">
					<view>变动说明：</view>
					<textarea v-model="stockTransformParams.remark" placeholder="填写备注信息" placeholder-class="placeholder-class" class="textarea" />
				</view>	
				<view class="pop-bottom">
					<button class="primary-btn" @click="saveStockTransform">确定</button>
				</view>
			</view>
		</view>
	</unipopup>
</template>
<script>
	import unipopup from '@/components/uni-popup/uni-popup.vue';
	import { getGoodsSkuList,getStocktransform } from '@/api/goods.js';
	export default{
		data(){
			return {
				stockTransformParams: {
					input_sku_id: '',
					input_sku_name: '',
					input_sku_stock_transform_unit: 0,
					input_sku_stock: 0,
					input_sku_num: 0,
					output_sku_id: '',
					output_sku_name: '',
					ouput_sku_stock_transform_unit: 0,
					output_sku_stock: 0,
					output_sku_num: 0,
					remark: '',
					stock_transform_least_common_multiple_change_num: 0,
					least_common_multiple_change_num:''
				},
				goodsSkuList: []
			}
		},
		components:{
			unipopup
		},
		methods:{
			open(id,sku_id='') {
				this.goodsSkuList = []
				this.stockTransformParams = this.$util.deepClone({
					input_sku_id: '',
					input_sku_name: '',
					input_sku_stock_transform_unit: 0,
					input_sku_stock: 0,
					input_sku_num: 0,
					output_sku_id: '',
					output_sku_name: '',
					ouput_sku_stock_transform_unit: 0,
					output_sku_stock: 0,
					output_sku_num: 0,
					remark: '',
					stock_transform_least_common_multiple_change_num: 0,
					least_common_multiple_change_num:''
				})
				this.$refs.stockTransformRef.open();
				getGoodsSkuList(id).then((res) => {
					let Index =-1
					this.goodsSkuList = res.data.map((el,index) => {
						el.label = el.spec_name
						el.value = el.sku_id
						if(sku_id!=''&&el.sku_id===sku_id) Index = index
						return el
					})
					if(Index != -1) this.inputSelect(Index)
				})
			},
			inputChange(){
				this.calcLeastCommonMultiple()
				this.calcStockTransformData()
			},
			//库存转换，选择出库规格
			outputSelect(index) {
				this.stockTransformParams.output_sku_id = index != -1 ? this.goodsSkuList[index].value : ''
				this.stockTransformParams.output_sku_name = index != -1 ? this.goodsSkuList[index].label : ''
				this.stockTransformParams.output_sku_stock = index != -1 ? this.goodsSkuList[index].stock : 0
				this.stockTransformParams.output_sku_stock_transform_unit = index != -1 ? this.goodsSkuList[index]
					.stock_transform_unit : 0
					this.calcLeastCommonMultiple()
					this.calcStockTransformData()
			},
			//库存转换，选择入库规格
			inputSelect(index) {
				this.stockTransformParams.input_sku_id = index != -1 ? this.goodsSkuList[index].value : ''
				this.stockTransformParams.input_sku_name = index != -1 ? this.goodsSkuList[index].label : ''
				this.stockTransformParams.input_sku_stock = index != -1 ? this.goodsSkuList[index].stock : 0
				this.stockTransformParams.input_sku_stock_transform_unit = index != -1 ? this.goodsSkuList[index]
					.stock_transform_unit : 0
				this.calcLeastCommonMultiple()
				this.calcStockTransformData()
			},
			calcLeastCommonMultiple() {
				if (this.stockTransformParams.input_sku_stock_transform_unit && this.stockTransformParams.output_sku_stock_transform_unit) {
					this.stockTransformParams.stock_transform_least_common_multiple_change_num = this.getLeastCommonMultiple(this.stockTransformParams.input_sku_stock_transform_unit, this.stockTransformParams.output_sku_stock_transform_unit);
				}else{
					this.stockTransformParams.stock_transform_least_common_multiple_change_num = 0
				}
			},
			
			calcStockTransformData() {
				if (this.stockTransformParams.input_sku_stock_transform_unit && this.stockTransformParams.output_sku_stock_transform_unit && this.stockTransformParams.least_common_multiple_change_num) {
					 this.stockTransformParams.input_sku_num = this.stockTransformParams.stock_transform_least_common_multiple_change_num * this.stockTransformParams.least_common_multiple_change_num / this.stockTransformParams.input_sku_stock_transform_unit;
					 this.stockTransformParams.output_sku_num = this.stockTransformParams.stock_transform_least_common_multiple_change_num * this.stockTransformParams.least_common_multiple_change_num / this.stockTransformParams.output_sku_stock_transform_unit;
				}else{
					this.stockTransformParams.input_sku_num = 0
					this.stockTransformParams.output_sku_num = 0
				}
			},
			//最小公倍数
			getLeastCommonMultiple(a, b) {
				return a * b / this.getGreatestCommonDivisor(a, b);
			},
			//最大公约数
			getGreatestCommonDivisor(a, b) {
				if (b === 0) return a;
				return this.getGreatestCommonDivisor(b, a % b);
			},
			saveStockTransform(){
				if(!this.stockTransformParams.input_sku_id){
					this.$util.showToast({
						title: '请选择入库规格'
					});
					return false;
				}
				if(!this.stockTransformParams.output_sku_id){
					this.$util.showToast({
						title: '请选择出库规格'
					});
					return false;
				}
				if(!this.stockTransformParams.least_common_multiple_change_num){
					this.$util.showToast({
						title: '变动数量不能为空'
					});
					return false;
				}
				if(this.stockTransformParams.input_sku_id == this.stockTransformParams.output_sku_id){
					this.$util.showToast({
						title: '入库与出库不能是同一规格'
					});
					return false;
				}
				if(this.stockTransformParams.output_sku_num > this.stockTransformParams.output_sku_stock){
					this.$util.showToast({
						title: '['+this.stockTransformParams.output_sku_name+']库存不足'
						});
					return false;
				}
				uni.showLoading({
					title: '请求处理中'
				});
				getStocktransform(this.stockTransformParams).then(res=>{
					uni.hideLoading();
					this.$util.showToast({
						title: res.message
					});
					this.$emit('saveStockTransform')
					this.$refs.stockTransformRef.close()
				}).catch(()=>{
					uni.hideLoading();
				})
			}
		}
	}
</script>

<style lang="scss" scoped>
	.stockTransform-detail-wrap {
	  background-color: #fff;
	  border-radius: 0.05rem;
	  padding-bottom: 0.15rem;
	
	  .detail-head {
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
	}
	.stockTransform-body{
		width:8rem;
		background-color: #fff;
		padding: 0.2rem;
		.table {
		    width: 100%;
		    max-height: 2.7rem;
		    box-sizing: border-box;
		
		    .single-specification {
		      width: 100%;
		      max-height: 100%;
		      padding-left: 0.1rem;
		      box-sizing: border-box;
		
		      .item {
		        width: 100%;
		        margin-bottom: 0.15rem;
		        display: flex;
		        align-items: center;
		
		        image {
		          width: 0.5rem;
		        }
		
		        .name {
		          display: flex;
		          align-items: center;
		          margin-right: 0.16rem;
		          width: 0.7rem;
		          text-align: right;
		        }
		
		        .message {
		          width: 74%;
		          text-overflow: ellipsis;
		          overflow: hidden;
		          white-space: nowrap;
		        }
		      }
		    }
		
		    .table-all {
		      width: 100%;
		      display: flex;
		      align-items: center;
		      justify-content: space-between;
		      padding: 0 0.38rem;
		      box-sizing: border-box;
		
		      .table-td {
		        font-size: 0.14rem;
		        text-align: left;
		      }
		    }
		
		    .table-th {
		      height: 0.56rem;
		      background: #f7f8fa;
		    }
		
		    
		
		    .table-tr {
		        height: 0.7rem;
		        border-bottom: 0.01rem solid #e6e6e6;
		        box-sizing: border-box;
		
		        .table-td {
				  box-sizing: border-box;
		          text-overflow: -o-ellipsis-lastline;
				  padding:0 0.07rem;
		        }
		    }
		}
		.stock_transform_least_common_multiple_change_num,.remark{
			margin: 0.1rem 0;
			line-height: 0.3rem;
			.input{
				height: 0.3rem;
				line-height: 0.3rem;
				border: 0.01rem solid #e5e5e5;
				border-radius: 0.02rem;
				margin: 0 0.1rem;
				padding: 0 0.1rem;
				font-size: 0.14rem;
			}
			.text{
				margin: 0 0.1rem;
				color: var(--primary-color);
			}
			
		}
		
		.textarea{
			line-height: 0.3rem;
			border: 0.01rem solid #e5e5e5;
			border-radius: 0.02rem;
			padding: 0 0.1rem;
			font-size: 0.14rem;
			flex:1;
		}
	}
	
</style>
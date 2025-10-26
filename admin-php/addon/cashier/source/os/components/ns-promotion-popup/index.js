import {getAddonIsExist,getPromotionQrcode} from '@/api/promotion.js';
export default {
	name: 'nsPromotionPopup',
	props: {
		pageName: {
			type: String,
			default: 'COUPON_DETAIL'
		},
		
	},
	data() {
		return {
			qrParams:{
				page_name:'',
				option:'',
				app_type:'h5'
			},
			APPType:'h5',
			appTypeArray: [{
				text: 'H5',
				value: 'h5'
			}],
			qrData:{}
		}
	},
	mounted() {
		this.qrParams.page_name = this.pageName
		this.getAddonIsExistFn()
	},
	methods:{
		getAddonIsExistFn(){
			getAddonIsExist().then(res=>{
				if(res.data.weapp){
					this.appTypeArray.push({text:'微信小程序',value:'weapp'})
				}
				if(res.data.aliapp){
					this.appTypeArray.push({text:'支付宝小程序',value:'aliapp'})
				}
			})
		},
		getPromotionQrcodeFn(){
			getPromotionQrcode(this.qrParams).then(res=>{
				this.qrData = Object.assign(this.qrData,res.data)
				this.$forceUpdate();
			})
		},
		open(option){
			this.qrParams.option = JSON.stringify(option)
			this.$refs.promotionPop.open()
			this.qrData={} 
			this.appTypeArray.forEach((el)=>{
				this.qrParams.app_type = el.value
				this.getPromotionQrcodeFn()
			})
			
		},
		//复制链接
		copyTextToClipboard(text) {
		  uni.setClipboardData({
		    data: text,
		    success: function () {
		      // 可以添加用户友好的提示，例如使用uni.showToast提示复制成功
		      uni.showToast({
		        title: '复制成功',
		        icon: 'success',
		        duration: 2000
		      });
		    },
		    fail: function () {
		      console.log('复制失败');
		      // 可以添加错误处理或用户友好的提示
		    }
		  });
		},
		//下载二维码
		download(url){
			var oA = document.createElement("a");
			oA.innerHTML = '123'
			oA.download = ''; // 设置下载的文件名，默认是'下载'
			oA.target = "_blank"
			oA.href = url; //临时路径再保存到本地
			document.body.appendChild(oA);
			oA.click();
			oA.remove(); // 下载之后把创建的元素删除

		}
		// download(url){
		//     //下载文档
		// 	uni.downloadFile({
		// 		url: url,//下载地址接口返回
		// 		success: (data) => {
		// 			if (data.statusCode === 200) {
		// 				//文件保存到本地
		// 				uni.saveFile({
		// 					tempFilePath: data.tempFilePath, //临时路径
		// 					success: function(res) {
		// 						uni.showToast({
		// 							icon: 'none',
		// 							mask: true,
		// 							title: '文件已保存：' + res.savedFilePath, //保存路径
		// 							duration: 3000,
		// 						});
		// 						setTimeout(() => {
		// 							//打开文档查看
		// 							uni.openDocument({
		// 								filePath: res.savedFilePath,
		// 								success: function(res) {
		// 									// console.log('打开文档成功');
		// 								}
		// 							});
		// 						}, 3000)
		// 					}
		// 				});
		// 			}
		// 		},
		// 		fail: (err) => {
		// 			console.log(err);
		// 			uni.showToast({
		// 				icon: 'none',
		// 				mask: true,
		// 				title: '失败请重新下载',
		// 			});
		// 		},
		// 	});
		// }
	}
}
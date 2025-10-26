// 动态获取当前域名，强制使用HTTPS
function getCurrentDomain() {
	if (typeof window !== 'undefined') {
		return 'https://' + window.location.host;
	}
	return '';
}

var config = {

	// api请求地址 - 动态使用当前域名
	baseUrl: getCurrentDomain(),

	// 图片域名 - 动态使用当前域名
	imgDomain: getCurrentDomain(),

	// H5端域名 - 动态使用当前域名
	h5Domain: getCurrentDomain() + '/h5',



	// 腾讯地图key
	mpKey: '{{$mpKey}}',

	//客服地址
	webSocket: '{{$webSocket}}',

	//本地端主动给服务器ping的时间, 0 则不开启 , 单位秒
	pingInterval: 1500,

	// 版本号
	version: '5.5.2'

};

export default config;
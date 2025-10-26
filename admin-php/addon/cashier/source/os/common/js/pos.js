// import { ipcRenderer } from 'electron';
export default {
	send(call, param) {
		if (window.ipcRenderer != undefined) {
			window.ipcRenderer.send(call, param);
		}
		if (window.POS_ != undefined) {
			window.POS_.send(call, param);
		}
		//window.ipcRenderer.send('Print', '渲染进程')
		//window.ipcRenderer.invoke('Print', '渲染进程')
	},

}
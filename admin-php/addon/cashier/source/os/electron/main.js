
//防止控制台中文乱码， package.json 中启动 "electron": "chcp 65001&&electron .", 
//.npmrc 文件添加，防止安装electron卡死 electron_mirror=https://npmmirror.com/mirrors/electron/
const { app, BrowserWindow, KeyboardEvent, ipcMain } = require('electron')
//var globalShortcut = require('electron').globalShortcut;
//const electronLocalshortcut = require('electron-localshortcut');
const path = require('path')
const url = require('url')

let win, dev

function createWindow() {
    win = new BrowserWindow({
        width: 1220,
        height: 800,
        icon: './static/logo.png', //
		webPreferences: {
		      contextIsolation: false,
		      // eslint-disable-next-line no-undef
		      preload:  path.join(__dirname, 'preload.js')
		},
        autoHideMenuBar: false, //隐藏菜单栏
    })

    if (app.isPackaged) {
        let url = "http://localhost:8080"; // 本地启动的vue项目路径
        win.loadURL(url);
        return;
        //加载本地文件 index.html
        win.loadFile(path.join(__dirname, './index.html'))
    } else {
        let url = "http://localhost:8081"; // 本地启动的vue项目路径
        win.loadURL(url);
    }

    win.webContents.on("before-input-event", (event, input) => {
        var keys = ['F1','F2','F3','F4','F5','F6','F7','F8','F9','F10','F11','F12', 'Print', 'PageUp', 'PageDown' , 'Backspace', 'Delete' ];
        if (input.type === 'keyUp' && keys.includes(input.key)) {  // && input.control && input.key.toLowerCase() === 'enter'
            if (input.key === 'F12') {
                dev = win.webContents.openDevTools();               
            } else {
                var js = "POS_HOTKEY_CALLBACK('" + input.alt + "','" + input.key.toUpperCase() + "');"
                win.webContents.executeJavaScript(js);
                console.log(input.key);
            }
        } else if (input.type === 'keyUp' && (input.key.toUpperCase() === 'X' && input.alt)){
            var js = "POS_HOTKEY_CALLBACK('ALT','" + input.key.toUpperCase() + "');"
            win.webContents.executeJavaScript(js);
        }
    });

    //全局快捷键
    // electronLocalshortcut.register(win, 'F12', () => {
    //     // Open DevTools
    // });
    win.on('closed', () => {
        win = null
    })
}

app.on('ready', createWindow)
app.on('window-all-closed', () => {
    if (process.platform !== 'darwin') {
        app.quit()
    }
})

app.on('activate', () => {
    if (win === null) {
        createWindow()
    }
})

//接收window.ipcRenderer.invoke('Print', '渲染进程')
ipcMain.handle('Print', function (e, ...args) {
    console.log('Print', args);
})

//接收window.ipcRenderer.send('Print', '渲染进程')
ipcMain.on('Print', function (e, ...args) {
    console.log('Print1', args );
})

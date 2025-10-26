import Vue from "vue"
import Element from "element-ui"
import "element-ui/lib/theme-chalk/index.css"
import { Message } from 'element-ui'

let messageInstance = null;
const overrideMessage = (options) => {
    if(messageInstance) {
        messageInstance.close()
    }
    messageInstance = Message(options)
}
['error','success','info','warning'].forEach(type => {
    overrideMessage[type] = options => {
        if(typeof options === 'string') {
            options = {
                message:options
            }
        }
        options.type = type
        return overrideMessage(options)
    }
})

Vue.use(Element)
Vue.prototype.$message = overrideMessage;

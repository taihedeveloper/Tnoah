import Vue from 'vue'
import Vuex from 'vuex'

import servicetree from './modules/servicetree.js'
import groupconfig from './modules/groupconfig.js'
import events from './modules/events.js'

const debug = process.env.NODE_ENV !== 'production'
Vue.use(Vuex)
Vue.config.debug = debug


//导出store对象
export default new Vuex.Store({
    //组合各个模块
    modules:{
		servicetree,
		groupconfig,
		events
    },
    strict: debug

})

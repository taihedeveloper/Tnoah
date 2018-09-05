import Vue from 'vue'
import VueRouter from 'vue-router'
import VueResource from 'vue-resource'
import Vuex from 'vuex'
import App from './Components/App.vue'
import routerConfig from './router.config.js'
import store from './Store/store.js'
import $ from 'jquery'
//import {DatePicker, Breadcrumb, BreadcrumbItem,Input,Table,TableColumn} from 'element-ui'
import ElementUI from 'element-ui'
import 'element-ui/lib/theme-default/index.css'

// import ECharts from 'vue-echarts/components/ECharts.vue'


Vue.use(VueRouter)
Vue.use(VueResource)
Vue.use(Vuex)
Vue.use(ElementUI)
// Vue.use(ECharts)
// Vue.use(DatePicker)
// Vue.use(Breadcrumb)
// Vue.use(BreadcrumbItem)
// Vue.use(Input)

const router = new VueRouter(routerConfig);

// require('./Js/onload.js')

new Vue({
	router,
	store,
	el: "#app",
	render: h => h(App)
});


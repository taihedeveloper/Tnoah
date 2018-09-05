import Home from './Components/pages/Home.vue'
import ServiceLine from './Components/pages/ServiceLine.vue'
import Config from './Components/pages/Config.vue'
import Machine from './Components/pages/Machine.vue'
import Template from './Components/pages/Template.vue'
import Strategy from './Components/pages/Strategy.vue'
import Metric from './Components/pages/Metric.vue'
import Spark from './Components/pages/Spark.vue'
import Permission from './Components/pages/Permission.vue'
import Events from './Components/pages/Events.vue'

//import Graph from './Components/pages/Graph.vue'

export default{
	routes:[
		{path:'/index',component:Home},
		{path:'/',component:Home},
		{path:'*',redirect:'/index'},
		{path:'/serviceline',component:ServiceLine},
		{path:'/group',component:Config},
		{path:'/group/:groupid',redirect:'/group'},
		{path:'/group/:groupid/machine',component:Machine},
		{path:'/group/:groupid/template',component:Template},
		{path:'/group/:groupid/template/:templateid/strategy',component:Strategy},
		{path:'/group/:groupid/template/:templateid',redirect:'/group/:groupid/template'},
		{path:'/group/:groupid/metric',component:Metric},
		{path:'/spark',component:Spark},
		{path:'/permission',component:Permission},
		{path:'/events',component:Events},

		//{path:'/graph',component:Graph},
	]
}
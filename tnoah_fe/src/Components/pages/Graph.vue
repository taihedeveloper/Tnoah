<template>
	<div>
		<div v-for="(index,item) in graphList" id="gBox">
			<div class="graph" :id="'graphbox'+item">
				<!-- <div class="graphClose" @click="closeGraph(item)"></div> -->
				<div :id="'graph'+item" class="echartsGraph">
				</div>
			</div>
		</div>
	</div>
</template>
<script>
import {mapGetters,mapActions} from 'vuex'
import vueGetData from "../../Js/vueGetData.js"
import echarts from "../../Js/plugins/echarts.js"

//var ecConfig = require('echarts/config');

	export default{
		name:'home',
		data () {
			return {
				paramData:{
					startTime:'',
					endTime:'',
					selectIps:'',
					selectItems:[],
					type:'sec',
					graphList:[0,1,2,3,4],
				},
			}
		},
		mounted: function(){
			for(let i=0;i<this.graphList.length;i++){
				this.drawGraph(i);
			}
			// var id = 'graph'+index;
			// var myChart = echarts.init(document.getElementById(id));
			// var myChart = echarts.init(document.getElementById("graph"));

			// // 初始 option
			// var option = {
			//     xAxis: {
			//         type: 'category',
			//         data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
			//     },
			//     yAxis: {
			//         type: 'value'
			//     },
			//     series: [{
			//         data: [820, 932, 901, 934, 1290, 1330, 1320],
			//         type: 'line'
			//     }]
			// };

			// myChart.setOption(option);
		},
		computed:mapGetters({
			treeId:"treeId",
		}),
		methods: {
			drawGraph:function(index){
				var id = 'graph'+index;
				var myChart = echarts.init(document.getElementById(id));

				// 初始 option
				var option = {
				    xAxis: {
				        type: 'category',
				        data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
				    },
				    yAxis: {
				        type: 'value'
				    },
				    series: [{
				        data: [820, 932, 901, 934, 1290, 1330, 1320],
				        type: 'line'
				    }]
				};

				myChart.setOption(option);
			}

		},
		created(){

		},
	}
</script>

<style>
.test{
	width:300px;
	height:300px;
}
.echartsGraph{
	padding-left:20px;
	height:300px;
	width:100%;
	float:left;
}

</style>

<template>
	<!-- sideLeft start -->
	<div class="sideLeft" id="sideLeft">
		<div id="sideLeftbox">
			<h3>{{topname}}</h3>
			<div class="listwrap">
				<div class="searchbox clearfix" v-show="true">
					<input type="text" id="tree-search" placeholder="输入集群名、机器名、机器id" v-model="treeSearch" @keyup.enter="search"><span id="ztree-search-button" @click="search"></span>
				</div>
				<div class="treeWrap">
					<div class="menu" v-for="(menu,index) in menutestData">
						<h4 class="firsrmenu" @click="isshowSecendmenu"  :data-index="index">{{menu.firstmenu.name}}</h4>
						<div class="secendmenu" v-if="menu.firstmenu.isshowSecond">
							<ul class="treelist">
								<li v-for="(item,i) in menu.secondmenu" :data-index="i" :data-treeid="item.id" @click="getId"><a href="javascript:;" id="treeItem">{{item.name}}</a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- sideLeft end -->
</template>
<script>
import {mapGetters,mapActions} from 'vuex'
import vueGetData from "../Js/vueGetData.js"
import $ from 'jquery'

	export default {
		name: 'SideLeft',
		data () {
			return {
				treeSearch: '',
				topname: '服务树',
				treeid: -1,
				currentEvent:'',
				menutestData: [
				],
				copyMenutestData:[],
				allMachineInfos:[],
			}
		},
		methods: {
			getId:function(event){
				if(this.currentEvent!=""){
					this.currentEvent.setAttribute("class","")
				}
				let ele = event.currentTarget;
				this.currentEvent = ele;
				let id = ele.getAttribute("data-treeid");
				this.treeid = id;
				this.$store.dispatch('pushTreeId',{"id":this.treeid});
				ele.setAttribute("class","cur");
			},
			getH: function(){
				document.getElementById("sideLeft").style.height = vueGetData.getContHeight(68) + "px";
			},
			isshowSecendmenu: function(event){
				let ele = event.currentTarget;
				let index = ele.getAttribute("data-index");
				let a = this.menutestData[index].firstmenu.isshowSecond;
				if(!a){
					ele.setAttribute("class","firsrmenu showchild")
				}else{
					ele.setAttribute("class","firsrmenu")
				}
				this.menutestData[index].firstmenu.isshowSecond = !a;
			},
			search:function(){
				if(this.treeSearch==''){
					this.menutestData=this.copyMenutestData;
				}else{
					var servicelineTable = [];
					for(let i=0;i<this.allMachineInfos.length;i++){
						var groupTable = [];
						for(let j=0;j<this.allMachineInfos[i]["group"].length;j++){
							var mechineTable = [];
							for(let k=0;k<this.allMachineInfos[i]["group"][j]["mechines"].length;k++){
								let temp = this.allMachineInfos[i]["group"][j]["mechines"][k];
								if(temp["ip_addr"].indexOf(this.treeSearch)>=0 || temp["name"].indexOf(this.treeSearch)>=0){
									mechineTable.push(temp)
								}
							}

							if(mechineTable.length!=0 || this.allMachineInfos[i]["group"][j]["group_name"].indexOf(this.treeSearch)>=0){
								let second = {"id":this.allMachineInfos[i]["group"][j]["id"],"name":this.allMachineInfos[i]["group"][j]["group_name"]}
								groupTable.push(second)
							}
						}
						if(groupTable.length!=0 || this.allMachineInfos[i]["group"].indexOf(this.treeSearch)>=0){
							let first = {"isshowSecond":true, "name":this.allMachineInfos[i].service_line_name}
							let second = groupTable
							servicelineTable.push({"firstmenu":first,"secondmenu":second})
						}
					}

					if(servicelineTable.length==0){
						vueGetData.creatTips("没有搜索到数据")
						this.menutestData=this.copyMenutestData;
					}else{
						this.menutestData=servicelineTable;
					}
				}
			}
		},
		computed:mapGetters({
			// treelist:"treelist",
		}),

		// watch:{
		// 	treelist: function(){
		// 		for(var i=0;i<this.treelist.length;i++){
		// 			//获取一级目录
		// 			var name = this.treelist[i].service_line_name;
		// 			let firstmenu ={"name":name,"isshowSecond":false};
		// 			let data = {"service_line_id":this.treelist[i].id};
		// 			//获取二级目录
		// 			let secondmenu = [];
		// 			vueGetData.getData("getgroupinfos",data,function(jsondata){
		// 	        	if(jsondata.body.error_code === 22000){
		// 	        		let list = jsondata.body.data;
		// 	        		for (var j=0;j<list.length;j++){
		// 	        			let tempData = {"name":list[j].group_name,"id":list[j].id};
		// 	        			secondmenu.push(tempData);
		// 	        		}
		// 	        	}
		// 	        }.bind(this),function(){

		// 	        }.bind(this));

		// 			let result = {"firstmenu":firstmenu,"secondmenu":secondmenu};
		// 			this.menutestData.push(result);
		// 		}
		// 		this.copyMenutestData=this.menutestData;
		// 	}
		// },
		created(){
			// this.$store.dispatch('getTreeList',{});
			vueGetData.getData("getallmachineinfos",{},function(jsondata){
	        	if(jsondata.body.error_code === 22000){
	        		this.allMachineInfos = jsondata.body.data;
	        		for(let i=0;i<this.allMachineInfos.length;i++){
	        			//获取一级目录
						var name = this.allMachineInfos[i].service_line_name;
						let firstmenu ={"name":name,"isshowSecond":false};
						let data = {"service_line_id":this.allMachineInfos[i].id};
						//获取二级目录
						let secondmenu = [];
						let list = this.allMachineInfos[i].group;
						for (var j=0;j<list.length;j++){
		        			let tempData = {"name":list[j].group_name,"id":list[j].id};
		        			secondmenu.push(tempData);
		        		}
						let result = {"firstmenu":firstmenu,"secondmenu":secondmenu};
						this.menutestData.push(result);
	        		}
	        		this.copyMenutestData=this.menutestData;
	        	}
	        }.bind(this),function(){

	        }.bind(this));
		}

	}
</script>
<style lang="less" >
@import "../Css/mixin.less";

.sideLeft{
	position: absolute;
	top: 50px;
	left: 0;
	width: 240px;
	height:90%;
	//min-height: 550px;
	border-right: none;
	background: #20335d;
	border-top: none;
	border-bottom: none;
	overflow-y: auto;

	h3{
		@h3H: 50px;
		padding-left: 10px;
		line-height: 60px;
		border-bottom: 1px solid rgba(255,255,255,0);
		.fontSizes(16px);
		color:#6e7a94;
	}

	.listwrap {
	}
	.searchbox {
		position: relative;
		@h: 25px;
		border-bottom: 1px solid #3c5876;
		padding: 5px 0 10px;

		#tree-search {
			position: relative;
			height: @h;
			width: 210px;
			line-height: @h;
			border: 1px solid #3c5876;
			color: #fff;
			background: #718aa1;
			font-size: 13px;
			&::-webkit-input-placeholder { /* WebKit browsers */
				color:#fff;
			}
			&::-moz-input-placeholder {
				color:#fff;
			}
		}
		#ztree-search-button {
			position: absolute;
			top: 10px;
			right: 18px;
			width: 20px;
			height: 20px;
			background:url(@searchico) no-repeat;
			background-size:20px;
			cursor:pointer;
		}
	}

	.treeWrap {
		width: 100%;
		overflow: hidden;
		color: #bdc0d1;
		.treeName {
			overflow: hidden;
			padding: 10px 0;
			margin: 0 20px 0 10px;
			position: relative;
			i{
				position: absolute;
				top: 10px;
				left: 10px;
			}
			a{
				display: inline-block;
				padding-left: 30px;
				font-size: 16px;
			}
			&.triangleR {
				i{
					.triangleR(@color: @hoverColor);
				}
				a{
					color: @hoverColor;
				}
			}
			&.triangleD {
				i{
					.triangleD();
				}
			}
		}

		.treelist {
			li{
				padding-left:20px;
				line-height: 30px;
				background: rgba(40,202,240,0.1);
				color: #2bc8f3;
				border-bottom: 1px solid rgba(40,202,240,0.1);
				border-right: 4px solid rgba(40,202,240,0.5);
				font-size:11px;
				a {
					color: #2bc8f3;
				}
			}
		}

		.menu {
			.firsrmenu {
				position: relative;
				height: 40px;
				line-height: 40px;
				padding-left: 35px;
				border-bottom: 1px solid rgba(40,202,240,0.2);
				border-right: 4px solid #20335d;
				cursor: pointer;
				&:after {
					position: absolute;
					top: -3px;
					left: 10px;
					content: "+";
					font-size: 24px;
					color: #bdc0d1;
					font-weight: bold;
				}
				&.showchild{
					&:after {
						content: "-";
						color: #2bc8f3;
					}
					background: #1b2f54;
					color: #2bc8f3;
					border-right: 4px solid #2bc8f3;
				}
			}
			/**
			.secendmenu {
				max-height: 300px;
				overflow-y: auto;
			}
			**/
		}
	}
	.cur{
		font-weight: bold;
		text-decoration: underline;
	}
}

</style>

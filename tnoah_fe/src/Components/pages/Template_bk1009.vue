<template>
	<div class="templatehome">
		<div class="navigation">
			<el-breadcrumb separator="/">
				<el-breadcrumb-item :to="{ path: '/' }">首页</el-breadcrumb-item>
				<el-breadcrumb-item :to="{ path: '/group' }">集群配置 {{groupName}}</el-breadcrumb-item>
				<el-breadcrumb-item>模板配置</el-breadcrumb-item>
			</el-breadcrumb>
		</div>
		<div class="operation">
			<!-- <div class="currentItem">
				当前集群：{{groupName}}
			</div> -->
			<!-- 操作 -->
			<div class="operationBox">
				模板名
				<input type="text" id="item-name" class="addInput" v-model="addData.name">
				<i class="el-icon-plus" @click="addTemplate"></i>
			</div>
		</div>
		<div class="configList">
			<div class="table-div">
				<table class="manage-table">
					<thead>
						<tr>
							<th width="9%"><input type="checkbox" name="check-all"></th>
							<th width="8%">模板id</th>
							<th width="15%">模板名</th>
							<th width="8%">父模板id</th>
							<th width="15%">报警组</th>
							<th width="15%">作者</th>
							<th width="15%">创建时间</th>
							<th width="15%">操作</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item,index) in templateList">
							<td><input type="checkbox" name="check-one"></td>
							<td>{{item.id}}</td>
							<td>
								<template>
									<el-input v-show="updateData[index].edit" v-model="item.tpl_name"></el-input>
									<span v-show="!updateData[index].edit">{{item.tpl_name}}</span>
								</template>
							</td>
							<td>{{item.parent_id}}</td>
							<td>
								<template>
									<el-input v-show="updateData[index].edit" v-model="item.uic"></el-input>
									<span v-show="!updateData[index].edit">{{item.uic}}</span>
								</template>
							</td>
							<td>{{item.create_user}}</td>
							<td>{{item.create_at | formatDate}}</td>
							<td>
								<a href="javascript:;" :data-id="item.id" :data-index="index" @click="editStrategy(item.id,item.tpl_name)">策略</a>
								<a href="javascript:;" :data-id="item.id" :data-index="index" @click="updateTemplate(item,index)">{{updateData[index].edit?'完成':'编辑'}}</a>
								<a href="javascript:;" :data-id="item.id" :data-index="index" @click="deleteTemplate(item.id)">删除</a>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<Strategy></Strategy>
	</div>

</template>
<script>
import {mapGetters,mapActions} from 'vuex'
import vueGetData from "../../Js/vueGetData.js"
import {Breadcrumb, BreadcrumbItem,Input} from 'element-ui'
import Strategy from "../dialogs/Strategy.vue"

	export default{
		name:'home',
		data () {
			return {
				groupId:0,
				groupName:'',
				templateList:[],
				serviceLines:{},
				serviceLineIndex:0,
				serviceLineId:0,
				addData:{
					ip:'',
				},
				searchData:{
					ip:'',
					name:''
				},
				updateData:[],
			}
		},
		computed:mapGetters({
		}),
		created () {
			this.fetchData();
		},
		watch:{
			'$route': 'fetchData',
			templateList:function(){
				let len = this.templateList.length;
				this.updateData=[];
				for(let i=0;i<len;i++){
					let data = {"edit":false}
					this.updateData.push(data)
				}
			}
		},
		methods: {
			fetchData(){
				//获取路由id
				this.groupId = this.$route.params.groupid;
				//获取集群名
				vueGetData.getData("getgroupbyid",{"group_id":this.groupId},function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		if(jsondata.body.data.length==1){
		        			this.groupName = jsondata.body.data[0].group_name;
		        			console.log(this.groupName)
		        		}
		        	}else{
		        		console.log("error_code:22001")
		        	}
		        }.bind(this),function(){

		        }.bind(this));
				//更新列表
				this.getTemplateList()
			},
			getTemplateList:function(){
				vueGetData.getData("gettemplates",{"group_id":this.groupId},function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		this.templateList = jsondata.body.data;
			        	console.log(this.templateList)
		        	}else{
			        	console.log(jsondata.body.msg)
			        }
		        }.bind(this),function(){

		        }.bind(this));
			},
			addTemplate:function(){
				if(!this.addData.name){
					vueGetData.creatTips("请填写模板名");
					return false;
				}
				let data={}
				data["group_id"] = this.groupId;
				data["tpl_name"] = vueGetData.trim(this.addData.name);

				vueGetData.getData("addTemplate",data,function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		vueGetData.creatTips("添加模板成功");
		        		//刷新列表
		        		this.addData.name="";
		        		this.getTemplateList()
		        	}else{
		        		vueGetData.creatTips("添加模板失败");
			        }
		        }.bind(this),function(){

		        }.bind(this));
			},
			// searchMechine:function(){
			// 	if(!this.searchData.name){
			// 		vueGetData.creatTips("请填写模板名");
			// 		return false;
			// 	}
			// 	// let data={};
			// 	// data["group_name"]=vueGetData.trim(this.searchData.name);
			// 	// data["service_line_id"] = this.serviceLineId;

			// 	// vueGetData.getData("groupinservline",data,function(jsondata){
			// 	// 	console.log(jsondata);
		 //  //       	if(jsondata.body.data.length == 0){
		 //  //       		this.groupList = {};
		 //  //       	}else{
			//  //        	this.groupList = jsondata.body.data;
			//  //        }
			//  //        this.searchData.name="";
		 //  //       }.bind(this),function(){

		 //  //       }.bind(this));
			// },
			updateTemplate:function(item,index){
				if(!this.updateData[index].edit){
					this.updateData[index].edit=!this.updateData[index].edit;
				}else{
					if(!item.tpl_name){
						vueGetData.creatTips("请填写模板名");
						return false;
					}
					let data={};
					data["tpl_id"]=item.id;
					data["tpl_name"]=vueGetData.trim(item.tpl_name);
					data["uic"]=vueGetData.trim(item.uic);

					vueGetData.getData("updatetemplate",data,function(jsondata){
						console.log(jsondata);
			        	if(jsondata.body.error_code == 22000){
			        		console.log(this.groupList)
			        		if(jsondata.body.data == 1){
			        			vueGetData.creatTips("修改模板成功");
				        		// this.getTemplateList()
			        		}else{
			        			vueGetData.creatTips("未做修改");
			        		}
			        		this.updateData[index].edit=!this.updateData[index].edit;
			        	}
			        }.bind(this),function(){

			        }.bind(this));
				}
			},
			deleteTemplate:function(id){
				let data={};
				data["group_id"] = this.groupId;
				data["tpl_id"] = id;
 				vueGetData.getData("deletetemplate",data,function(jsondata){
					console.log(jsondata);
		        	if(jsondata.body.data == 1){
		        		vueGetData.creatTips("删除模板成功");
						this.getTemplateList()
		        	}else{
		        		vueGetData.creatTips("删除模板失败");
			        }
		        }.bind(this),function(){

		        }.bind(this));
			},
			showDialog: function(){
				document.getElementsByClassName("dialog")[0].style.display = "block";
			},
			editStrategy:function(id,name){
				console.log("editStrategy")
				console.log(id)
				console.log(name)
				this.$store.dispatch('getTemplateMes',{"id":id,"name":name})
				this.showDialog();
			}
		},
		components: {
			// 'el-breadcrumb': Breadcrumb,
			// 'el-breadcrumb-item': BreadcrumbItem,
			// 'el-input':Input
			Strategy
		}
	}
</script>
<style lang="less" >
@import "../../Css/rightside.less";

.templatehome {
	width: 100%;
	.navigation {
		padding:20px 20px 5px 20px;
		//background: @bgGray;
		//border-bottom: 1px solid rgba(78,105,130,.2);
	}
	.add-input{
		width: 12%;
		margin-right:40px;
	}
	.el-icon-plus{
		position:absolute;
		left:230px;
		top:28px;
		cursor:pointer;
	}
	.currentItem{
		font-size:20px ;
		font-weight:dold;
		padding-bottom:10px;
	}
	.operation{
		padding: 10px 20px;
		background:#f9f9f9;
		position:relative;

		select {
	        &.manage-select {
	            width: 15%;
	            background-position: 95% center;
	        }
	    }
	    input {
	        &.manage-input {
	            width: 20%;
	            //margin-top:5px;
	            margin-left: 0;
	        }
	        &.addInput{
		        //margin-top:5px;
		        margin-left: 0;
			}
		}
		.operationBox{
			margin-top:10px;
			margin-right:20px;
		}
	}
}
</style>
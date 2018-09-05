<template>
	<div class="producthome">
		<div class="navigation">
			<el-breadcrumb separator="/">
				<el-breadcrumb-item :to="{ path: '/' }">首页</el-breadcrumb-item>
				<el-breadcrumb-item>计算任务配置</el-breadcrumb-item>
			</el-breadcrumb>
		</div>
		<div class="operation">
			<div class="operationBox">
				<template v-if="canWrite">
					集群id
					<input type="text" id="item-name" class="addInput" v-model="addData.id">
					集群名
					<input type="text" id="item-name" class="addInput" v-model="addData.name">
					<i class="el-icon-plus" @click="addTask"></i>
				</template>
				<template v-else>
					集群id
					<input type="text" id="item-name" class="addInput" v-model="addData.id" disabled="disabled">
					集群名
					<input type="text" id="item-name" class="addInput" v-model="addData.name" disabled="disabled">
					<i class="el-icon-plus" @click="noPermission"></i>
				</template>
			</div>
		</div>
		<div class="configList">
			<div class="table-div">
				<table class="manage-table">
					<thead>
						<tr>
							<th width="5%"><input type="checkbox" name="check-all" disabled="disabled"></th>
							<!-- <th width="10%">id</th> -->
							<th width="5%">集群id</th>
							<th width="15%">集群名称</th>
							<th width="15%">任务id</th>
							<th width="10%">任务状态</th>
							<th width="10%">任务名</th>
							<th width="10%">数据表名</th>
							<th width="10%">jar包名</th>
							<th width="20%">操作</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item,index) in taskList">
							<td><input type="checkbox" name="check-one" disabled="disabled"></td>
							<!-- <td>{{item.id}}</td> -->
							<td>{{item.pid}}</td>
							<td>{{item.pname}}</td>
							<td>{{item.taskid}}</td>
							<td>
								<template v-if="item.status==0">未启动</template>
								<template v-else-if="item.status==1">已启动</template>
								<template v-else>已挂掉</template>
							</td>
							<td>{{item.app_name}}</td>
							<td>{{item.table_name}}</td>
							<td>{{item.jar_name}}</td>
<!-- 							<td>
								<template>
									<el-input v-show="updateData[index].edit" v-model="item.service_line_name"></el-input>
									<span v-show="!updateData[index].edit">{{item.service_line_name}}</span>
								</template>
							</td> -->
							<td>
								<template v-if="item.status==1">
									<template v-if="canWrite">
										<a href="javascript:;" :data-id="item.id" :data-index="index" @click="stopTask(item.pid)">杀死</a>
									</template>
									<template v-else>
										<a href="javascript:;" style="color:#c5c5c5"  @click="noPermission">杀死</a>
									</template>
								</template>
								<template v-else>
									<template v-if="canWrite">
										<a href="javascript:;" :data-id="item.id" :data-index="index" @click="submitTask(item)">启动</a>
									</template>
									<template v-else>
										<a href="javascript:;" style="color:#c5c5c5"  @click="noPermission">启动</a>
									</template>
								</template>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

</template>
<script>
import {mapGetters,mapActions} from 'vuex'
import vueGetData from "../../Js/vueGetData.js"
import {Breadcrumb, BreadcrumbItem,Input,Select} from 'element-ui'

	export default{
		name:'home',
		data () {
			return {
				taskList:[],
				addData:{
					id:'',
					name:'',
				},
				updateData:[],
				canWrite:false,//是否有写权限
			}
		},
		// computed:mapGetters({
		// 	serviceLineName:"serviceLineName"
		// }),
		created () {
			this.getTaskList();

			//获取用户业务线配置权限
			vueGetData.getAlertData("user/getPermission",{"user_name":vueGetData.username()},function(jsondata){
	        	if(jsondata.body.error_code === 22000){
	        		for(let item in jsondata.body.result){
	        			if(item == "tnoah"){
	        				for(let childitem in jsondata.body.result[item]){
								if(childitem == "spark"){
									this.canWrite = true;
								}
							}
	        			}
	        		}
	        	}else{
	        		console.log(jsondata.body.error_message);
	        	}
	        }.bind(this),function(){

	        }.bind(this));
		},
		watch:{
			taskList:function(){
				let len = this.taskList.length;
				this.updateData=[];

				for(let i=0;i<len;i++){
					let data = {"edit":false}
					this.updateData.push(data)
				}
			}
		},
		methods: {
			noPermission:function(){
				vueGetData.creatTips("无操作权限");
			},
			getTaskList:function(){
				vueGetData.getData("GetSparkTask",{},function(jsondata){

		        	if(jsondata.body.error === 22000){
		        		this.taskList = jsondata.body.data;
		        	}else{
			        	console.log(jsondata.body.msg)
			        }
		        }.bind(this),function(){

		        }.bind(this));
			},
			addTask:function(){
				if(!this.addData.id){
					vueGetData.creatTips("请填写集群id");
					return false;
				}
				if(!this.addData.name){
					vueGetData.creatTips("请填写集群名称");
					return false;
				}
				let data={}
				data["pid"] = this.addData.id;
				data["pname"] = this.addData.name;

				vueGetData.getData("addsparktask",data,function(jsondata){
		        	if(jsondata.body.error === 22000){
		        		vueGetData.creatTips("添加计算任务成功");
		        		//刷新列表
		        		this.addData.id="";
		        		this.addData.name="";
		        		this.getTaskList()
		        	}else{
		        		vueGetData.creatTips("添加计算任务失败");
			        }
		        }.bind(this),function(){

		        }.bind(this));
			},
			submitTask:function(item){
				let data={}
				data["pid"] = item.pid;
				// data["appArgs"] = item.table_name + "," + item.app_name + "," + table_name;
				// dara["taskname"] = item.jar_name;

				vueGetData.getData("sparksubmit",data,function(jsondata){
		        	if(jsondata.body.error === 22000){
		        		vueGetData.creatTips("计算任务启动成功");
		        		//刷新列表
		        		this.getTaskList()
		        	}else{
		        		vueGetData.creatTips("计算任务启动失败");
			        }
		        }.bind(this),function(){

		        }.bind(this));
			},
			stopTask:function(pid){
				let data={}
				data["pid"] = pid;
				vueGetData.getData("sparkstop",data,function(jsondata){
		        	if(jsondata.body.error === 22000){
		        		vueGetData.creatTips("计算任务杀死成功");
		        		//刷新列表
		        		this.getTaskList()
		        	}else{
		        		vueGetData.creatTips("计算任务杀死失败 "+jsondata.body.message);
			        }
		        }.bind(this),function(){

		        }.bind(this));
			}

		},
		components: {
		}
	}
</script>
<style lang="less" >
@import "../../Css/rightside.less";

.producthome {
	width: 100%;
	height:85%;
	position:absolute;
	overflow-y: auto;
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
		margin-left:10px;
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
	.el-select .el-tag {
	    height: 24px;
	    line-height: 24px;
	    box-sizing: border-box;
	    margin: 3px 0 3px 13px;
	}
}
</style>
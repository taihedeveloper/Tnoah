<template>
	<div class="confighome">
		<div class="navigation">
			<el-breadcrumb separator="/">
				<el-breadcrumb-item :to="{ path: '/' }">首页</el-breadcrumb-item>
				<el-breadcrumb-item :to="{ path: '/group' }">集群配置 {{groupName}}</el-breadcrumb-item>
				<el-breadcrumb-item>机器配置</el-breadcrumb-item>
			</el-breadcrumb>
		</div>
		<div class="operation">
<!-- 			<div class="currentItem">
				当前集群：{{groupName}}
			</div> -->
			<!-- 操作 -->
			<div class="operationBox">
				<input type="text" id="item-name" placeholder="机器名" class="manage-input" v-model="searchData.name">
				<i class="el-icon-search" @click="searchMachine"></i>
				<template v-if="canWrite">
					<span class="addOperation">
						机器ip
						<input type="text" id="item-name" class="addInput" v-model="addData.ip">
						机器名
						<input type="text" id="item-name" class="addInput" v-model="addData.name">
					</span>
					<i class="el-icon-plus" @click="addMachine"></i>
				</template>
				<template v-else>
					<span class="addOperation">
						机器ip
						<input type="text" id="item-name" class="addInput" v-model="addData.ip" disabled="disabled">
						机器名
						<input type="text" id="item-name" class="addInput" v-model="addData.name" disabled="disabled">
					</span>
					<i class="el-icon-plus" @click="noPermission"></i>
				</template>
			</div>
		</div>
		<div class="configList">
			<div class="table-div">
				<table class="manage-table">
					<thead>
						<tr>
							<th width="10%"><input type="checkbox" name="check-all" disabled="disabled"></th>
							<th width="20%">机器id</th>
							<th width="20%">机器ip</th>
							<th width="30%">机器名</th>
							<th width="20%">操作</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item,index) in machineList">
							<td><input type="checkbox" disabled="disabled"></td>
							<td>{{item.id}}</td>
							<!-- <td>{{item.ip_addr}}</td> -->
							<!-- <td>{{item.name}}</td> -->
							<td>
								<template>
									<el-input v-show="updateData[index].edit" v-model="item.ip_addr"></el-input>
									<span v-show="!updateData[index].edit">{{item.ip_addr}}</span>
								</template>
							</td>
							<td>
								<template>
									<el-input v-show="updateData[index].edit" v-model="item.name"></el-input>
									<span v-show="!updateData[index].edit">{{item.name}}</span>
								</template>
							</td>
							<td>
								<template v-if="canWrite">
									<a href="javascript:;" :data-id="item.id" :data-index="index" @click="updateMachine(item,index)">{{updateData[index].edit?'完成':'编辑'}}</a>
									<a href="javascript:;" :data-id="item.id" :data-index="index" @click="deleteMachine(item.id)">删除</a>
								</template>
								<template v-else>
									<a href="javascript:;" style="color:#c5c5c5"  @click="noPermission">编辑</a>
									<a href="javascript:;" style="color:#c5c5c5"  @click="noPermission">删除</a>
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
import {Breadcrumb, BreadcrumbItem,Input} from 'element-ui'

	export default{
		name:'home',
		data () {
			return {
				groupId:0,
				groupName:'',
				machineList:[],
				serviceLines:{},
				serviceLineIndex:0,
				serviceLineId:0,
				addData:{
					ip:'',
					name:'',
				},
				searchData:{
					name:''
				},
				updateData:[],
				canWrite:false,//是否有写权限
			}
		},
		computed:mapGetters({
			serviceLineName: "serviceLineName",
		}),
		created () {
			this.$store.dispatch("getServiceLineName")
			this.fetchData();
		},
		watch:{
			'$route': 'fetchData',
			machineList:function(){
				let len = this.machineList.length;
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
			fetchData(){
				//获取路由id
				this.groupId = this.$route.params.groupid;
				//获取集群名
				vueGetData.getData("getgroupbyid",{"group_id":this.groupId},function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		if(jsondata.body.data.length==1){
		        			this.groupName = jsondata.body.data[0].group_name;
		        		}
		        	}else{
		        		console.log("error_code:22001")
		        	}
		        }.bind(this),function(){

		        }.bind(this));

		        //获取用户业务线配置权限
				vueGetData.getAlertData("user/getPermission",{"user_name":vueGetData.username()},function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		for(let item in jsondata.body.result){
		        			if(item == "tnoah"){
		        				for(let childitem in jsondata.body.result[item]){
		        					if(childitem == 'strategy'){
		        						this.canWrite = true;
										return;
		        					}
									else if(childitem == this.serviceLineName){
										this.canWrite = true;
										return;
									}
								}
		        			}
		        			this.canWrite = false;
		        		}
		        	}else{
		        		console.log(jsondata.body.error_message);
		        	}
		        }.bind(this),function(){

		        }.bind(this));

				//更新列表
				this.getMachinelist()
			},
			getMachinelist:function(){
				vueGetData.getData("getmachineinfos",{"group_id":this.groupId},function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		this.machineList = jsondata.body.data;
		        	}else{
			        	console.log(jsondata.body.msg)
			        }
		        }.bind(this),function(){

		        }.bind(this));
			},
			addMachine:function(){
				if(!this.addData.name){
					vueGetData.creatTips("请填写机器名");
					return false;
				}else if(!this.addData.ip){
					vueGetData.creatTips("请填写机器ip");
					return false;
				}
				let data={}
				data["name"] = vueGetData.trim(this.addData.name);
				data["ip_addr"] = vueGetData.trim(this.addData.ip);
				data["group_id"] = this.groupId;

				vueGetData.getData("addmachine",data,function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		vueGetData.creatTips("添加机器成功");
		        		//刷新列表
		        		this.getMachinelist()
		        	}else{
		        		vueGetData.creatTips("添加机器失败");
			        }
			        this.addData.name="";
			        this.addData.ip="";
		        }.bind(this),function(){

		        }.bind(this));
			},
			searchMachine:function(){
				if(!this.searchData.name){
					vueGetData.creatTips("请填写机器名");
					return false;
				}
				let data={};
				data["machine_name"]=vueGetData.trim(this.searchData.name);
				data["group_id"] = this.groupId;

				vueGetData.getData("machineingroup",data,function(jsondata){
					if(jsondata.body.error_code === 22000){
						if(jsondata.body.data.length == 0){
			        		this.machineList = [];
			        	}else{
				        	this.machineList = jsondata.body.data;
				        }
					}
			        this.searchData.name="";
		        }.bind(this),function(){

		        }.bind(this));
			},
			updateMachine:function(item,index){
				if(!this.updateData[index].edit){
					this.updateData[index].edit=!this.updateData[index].edit;
				}else{
					if(!item.ip && !item.name ){
						vueGetData.creatTips("请填写要修改的项：机器ip或机器名");
						return false;
					}
					let data={};
					data["id"]=item.id;
					if(item.ip){
						data["ip_addr"]=vueGetData.trim(item.ip);
					}
					if(item.name){
						data["name"]=vueGetData.trim(item.name);
					}

					vueGetData.getData("updatemachine",data,function(jsondata){
			        	if(jsondata.body.error_code == 22000){
			        		if(jsondata.body.data == 1){
			        			vueGetData.creatTips("修改机器成功");
			        		}else{
			        			vueGetData.creatTips("未做任何修改");
			        		}
			        		this.updateData[index].edit=!this.updateData[index].edit;
			        	}
			        }.bind(this),function(){

			        }.bind(this));
				}
			},
			deleteMachine:function(id){
				let data={};
				data["id"] = id;

				let _self = this;

				let str = '<div class="popCreat" id="deleteLogBox">'
		            +'<h3>确定要删除吗？</h3>'
		            +'<div class="btns"><span class="surebtn" id="surebtn">确定</span><span class="cancelbtn" id="cancelbtn">取消</span></div></div>';
				vueGetData.creatPop(str);

				let surebtn = document.getElementById("surebtn");
				let cancelbtn = document.getElementById("cancelbtn");

		        surebtn.onclick = function(){
					vueGetData.getData("deletemachine",data,function(jsondata){
			        	if(jsondata.body.data == 1){
			        		vueGetData.creatTips("删除机器成功");
							_self.getMachinelist()
			        	}else{
			        		vueGetData.creatTips("删除机器失败");
				        }
			        }.bind(this),function(){

			        }.bind(this));
					vueGetData.closePop();
		        }
		        cancelbtn.onclick = function(){
		        	vueGetData.closePop();
		        }
			},
		},
		components: {
			// 'el-breadcrumb': Breadcrumb,
			// 'el-breadcrumb-item': BreadcrumbItem,
			// 'el-input':Input
		}
	}
</script>
<style lang="less" >
@import "../../Css/rightside.less";

.confighome {
	width: 100%;
	height:85%;
	position:absolute;
	overflow-y: auto;
	.navigation {
		padding:30px 20px 10px;
		//background: @bgGray;
		//border-bottom: 1px solid rgba(78,105,130,.2);
	}
	.add-input{
		width: 12%;
		margin-right:40px;
	}
	.currentItem{
		font-size:20px ;
		font-weight:dold;
		padding-bottom:10px;
	}
	.el-icon-search{
		cursor:pointer;
	}
	.el-icon-plus{
		position:absolute;
		right:0px;
		top:10px;
		cursor:pointer;
	}
	.el-input__inner{
		margin:0;
		width:auto;
	}
}


</style>
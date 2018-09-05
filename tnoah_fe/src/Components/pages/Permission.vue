<template>
	<div class="metrichome">
		<div class="navigation">
			<el-breadcrumb separator="/">
				<el-breadcrumb-item :to="{ path: '/' }">首页</el-breadcrumb-item>
				<el-breadcrumb-item>通用权限服务</el-breadcrumb-item>
			</el-breadcrumb>
		</div>
		<div class="operation">
			<span class="nowrap">
				<label for="monitor-type">业务线</label>
				<select id="monitor-type" class="manage-select" v-model="serviceLineIndex">
					<option v-for="(option,index) in serviceLines" :value="index">{{option.service_line_name}}</option>
				</select>
			</span>
			<el-tabs v-model="tabName">
				<el-tab-pane label="权限" name="permission">
					<div class="operationBox">
						<template v-if="canWrite">
							权限名
							<input type="text" id="item-name" class="addInput" v-model="permissionAddData.name">
							权限描述
							<input type="text" id="item-name" class="addInput" v-model="permissionAddData.des">
							<i class="el-icon-plus" @click="addPermission"></i>
						</template>
						<template v-else>
							权限名
							<input type="text" id="item-name" class="addInput" v-model="permissionAddData.name" disabled="disabled">
							权限描述
							<input type="text" id="item-name" class="addInput" v-model="permissionAddData.des" disabled="disabled">
							<i class="el-icon-plus" @click="noPermission"></i>
						</template>
					</div>
			    </el-tab-pane>
			    <el-tab-pane label="权限组/角色" name="role">
			    	<div class="operationBox">
			    		<template v-if="canWrite">
			    			权限组名
							<input type="text" id="item-name" class="addInput" v-model="roleAddData.name">
							<i class="el-icon-plus" @click="addRole"></i>
			    		</template>
			    		<template v-else>
			    			权限组名
							<input type="text" id="item-name" class="addInput" v-model="roleAddData.name" disabled="">
							<i class="el-icon-plus" @click="noPermission"></i>
			    		</template>
					</div>
			    </el-tab-pane>
			    <el-tab-pane label="用户组" name="usergroup">
			    	<div class="operationBox">
			    		<template v-if="canWrite">
			    			用户组名
							<input type="text" id="item-name" class="addInput" v-model="groupAddData.name">
							<i class="el-icon-plus" @click="addUsergroup"></i>
			    		</template>
			    		<template v-else>
			    			用户组名
							<input type="text" id="item-name" class="addInput" v-model="groupAddData.name" disabled="disabled">
							<i class="el-icon-plus" @click="noPermission"></i>
			    		</template>
					</div>
			    </el-tab-pane>
	  		</el-tabs>
		</div>
		<div class="configList">
			<div class="table-div">
				<table class="manage-table" v-if="tabName=='permission'">
					<thead>
						<tr>
							<th width="10%"><input type="checkbox" name="check-all" disabled="disabled"></th>
							<th width="20%">权限id</th>
							<th width="25%">权限标示</th>
							<th width="25%">权限描述</th>
							<th width="20%">操作</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item,index) in permissionList">
							<td><input type="checkbox" disabled="disabled"></td>
							<td>{{item.permission_id}}</td>
							<td>{{item.indentity}}</td>
							<td>
								<template>
									<el-input v-show="permissionUpdateDate[index].edit" v-model="item.des"></el-input>
									<span v-show="!permissionUpdateDate[index].edit">{{item.des}}</span>
								</template>
							</td>
							<td>
								<template v-if="canWrite">
									<a href="javascript:;" :data-index="index" @click="updatePermission(item,index)">{{permissionUpdateDate[index].edit?'完成':'编辑'}}</a>
									<a href="javascript:;" :data-index="index" @click="deletePermission(item)">删除</a>
								</template>
								<template v-else>
									<a href="javascript:;"  style="color:#c5c5c5"  @click="noPermission">编辑</a>
									<a href="javascript:;" style="color:#c5c5c5"  @click="noPermission">删除</a>
								</template>
							</td>
						</tr>
					</tbody>
				</table>
				<table class="manage-table" v-else-if="tabName=='role'">
					<thead>
						<tr>
							<th width="10%"><input type="checkbox" name="check-all" disabled="disabled"></th>
							<th width="30%">权限组id</th>
							<th width="40%">权限组名</th>
							<th width="20%">操作</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item,index) in roleList">
							<td><input type="checkbox" disabled="disabled"></td>
							<td>{{item.role_id}}</td>
							<td>
								<template>
									<el-input v-show="roleUpdateDate[index].edit" v-model="item.role_name"></el-input>
									<span v-show="!roleUpdateDate[index].edit">{{item.role_name}}</span>
								</template>
							</td>
							<td>
								<template v-if="canWrite">
									<a href="javascript:;" :data-id="item.id" :data-index="index" @click="relatePermission(item)">绑定权限</a>
									<a href="javascript:;" :data-index="index" @click="updateRole(item,index)">{{roleUpdateDate[index].edit?'完成':'编辑'}}</a>
									<a href="javascript:;" :data-index="index" @click="deleteRole(item)">删除</a>
								</template>
								<template v-else>
									<a href="javascript:;" style="color:#c5c5c5"  @click="noPermission">绑定权限</a>
									<a href="javascript:;"  style="color:#c5c5c5"  @click="noPermission">编辑</a>
									<a href="javascript:;" style="color:#c5c5c5"  @click="noPermission">删除</a>
								</template>
							</td>
						</tr>
					</tbody>
				</table>
				<table class="manage-table" v-else-if="tabName=='usergroup'">
					<thead>
						<tr>
							<th width="10%"><input type="checkbox" name="check-all" disabled="disabled"></th>
							<th width="30%">报警组id</th>
							<th width="40%">报警组名</th>
							<th width="20%">操作</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item,index) in usergroupList">
							<td><input type="checkbox" disabled="disabled"></td>
							<td>{{item.group_id}}</td>
							<td>
								<template>
									<el-input v-show="groupUpdateDate[index].edit" v-model="item.group_name"></el-input>
									<span v-show="!groupUpdateDate[index].edit">{{item.group_name}}</span>
								</template>
							</td>
							<td>
								<template v-if="canWrite">
									<a href="javascript:;" :data-index="index" @click="getUsers(item)">用户</a>
									<a href="javascript:;" :data-id="item.id" :data-index="index" @click="relateRole(item)">关联角色</a>
									<a href="javascript:;" :data-index="index" @click="updateUsergroup(item,index)">{{groupUpdateDate[index].edit?'完成':'编辑'}}</a>
									<a href="javascript:;" :data-index="index" @click="deleteUsergroup(item)">删除</a>
								</template>
								<template v-else>
									<a href="javascript:;" style="color:#c5c5c5"  @click="noPermission">用户</a>
									<a href="javascript:;" style="color:#c5c5c5"  @click="noPermission">关联角色</a>
									<a href="javascript:;" style="color:#c5c5c5"  @click="noPermission">编辑</a>
									<a href="javascript:;" style="color:#c5c5c5"  @click="noPermission">删除</a>
								</template>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<Users :userFormData="userFormData"></Users>
		<Permission :permissionFormData="permissionFormData"></Permission>
		<Role :roleFormData="roleFormData"></Role>
	</div>

</template>
<script>
import {mapGetters,mapActions} from 'vuex'
import vueGetData from "../../Js/vueGetData.js"
import {Breadcrumb, BreadcrumbItem,Input} from 'element-ui'
import Users from "../dialogs/Users.vue"
import Permission from "../dialogs/Permission.vue"
import Role from "../dialogs/Role.vue"

	export default{
		name:'home',
		data () {
			return {
				serviceLines:[],
				serviceLineIndex:0,
				servicelineId:0,
				permissionList:[],
				roleList:[],
				usergroupList:[],
				tabName: 'permission',
				userFormData:{},	//用户组编辑用户时数据
				permissionFormData:{},	//绑定权限时数据
				roleFormData:{}, //关联角色时数据
				permissionAddData:{
					name:'',
					des:''
				},
				roleAddData:{
					name:'',
				},
				groupAddData:{
					name:'',
				},
				permissionUpdateDate:[],
				roleUpdateDate:[],
				groupUpdateDate:[],
				canWrite:false,//是否有写权限
			}
		},
		computed:mapGetters({
			productId:"serviceLineIndex"
		}),
		created () {
			vueGetData.getData("getallserviceline",{},function(jsondata){
	        	if(jsondata.body.error_code === 22000){
	        		this.serviceLines = jsondata.body.data;
	        		if(window.localStorage.getItem("serviceLineIndex")){
	        			this.$store.dispatch("getServiceLineIndex")
	        			this.serviceLineIndex = this.productId;
	        		}
	        	}
	        }.bind(this),function(){

	        }.bind(this));

	        //获取用户通用权限服务权限
			vueGetData.getAlertData("user/getPermission",{"user_name":vueGetData.username()},function(jsondata){
	        	if(jsondata.body.error_code === 22000){
	        		for(let item in jsondata.body.result){
	        			if(item == "tnoah"){
	        				for(let childitem in jsondata.body.result[item]){
								if(childitem == "permission"){
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
		},
		watch:{
			serviceLineIndex:function(){
				window.localStorage.setItem("serviceLineIndex", this.serviceLineIndex);
				window.localStorage.setItem("serviceLineName", this.serviceLines[this.serviceLineIndex].service_line_name);
        		this.servicelineId = this.serviceLines[this.serviceLineIndex].id

        		this.getPermissionList()
        		this.getRoleList()
        		this.getUsergroupList()

			},
		},
		methods: {
			noPermission:function(){
				vueGetData.creatTips("无操作权限");
			},
			getPermissionList:function(){
				let data = {}
        		data["product"] = this.serviceLines[this.serviceLineIndex].service_line_name
				//获取权限列表
        		vueGetData.getAlertData("permission/getPermissionList",data,function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		this.permissionList = jsondata.body.result;
		        		let len = this.permissionList.length;
						this.permissionUpdateDate=[];
						for(let i=0;i<len;i++){
							let data = {"edit":false}
							this.permissionUpdateDate.push(data)
						}
		        	}else{
		        		console.log(jsondata.body.error_message)
		        	}
		        }.bind(this),function(){

		        }.bind(this));
			},
			addPermission:function(){
				if(!this.permissionAddData.name){
					vueGetData.creatTips("请填写权限名称");
					return false;
				}
				if(!this.permissionAddData.des){
					vueGetData.creatTips("请填写权限描述");
					return false;
				}
				let data={}
				data["product"] = this.serviceLines[this.serviceLineIndex].service_line_name;
				data["permission_name"] = vueGetData.trim(this.permissionAddData.name);
				data["des"] = vueGetData.trim(this.permissionAddData.des);

				vueGetData.getAlertData("permission/createPermission",data,function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		vueGetData.creatTips("添加权限成功");
		        		this.permissionAddData.name = '';
		        		this.permissionAddData.des = '';
		        		this.getPermissionList()
		        	}else{
		        		vueGetData.creatTips(jsondata.body.error_message);
		        	}
		        }.bind(this),function(){

		        }.bind(this));
			},
			updatePermission:function(item,index){
				if(!this.permissionUpdateDate[index].edit){
					this.permissionUpdateDate[index].edit=!this.permissionUpdateDate[index].edit;
				}else{
					if(!item.des){
						vueGetData.creatTips("请填写权限描述");
						return false;
					}
					let data={};
					data["permission_id"]=item.permission_id;
					data["indentity"]=vueGetData.trim(item.indentity);
					data["des"]=vueGetData.trim(item.des);

					vueGetData.getAlertData("permission/updatePermission",data,function(jsondata){
			        	if(jsondata.body.error_code == 22000){
			        		vueGetData.creatTips("修改权限成功");
			        		this.permissionUpdateDate[index].edit=!this.permissionUpdateDate[index].edit;
			        	}else{
			        		vueGetData.creatTips(jsondata.body.error_message);
			        	}
			        }.bind(this),function(){

			        }.bind(this));
				}
			},
			deletePermission:function(item){
				let data={}
				data["permission_id"] = item.permission_id;

				let _self = this;

				let str = '<div class="popCreat" id="deleteLogBox">'
		            +'<h3>确定要删除吗？</h3>'
		            +'<div class="btns"><span class="surebtn" id="surebtn">确定</span><span class="cancelbtn" id="cancelbtn">取消</span></div></div>';
				vueGetData.creatPop(str);

				let surebtn = document.getElementById("surebtn");
				let cancelbtn = document.getElementById("cancelbtn");

		        surebtn.onclick = function(){
					vueGetData.getAlertData("permission/delPermission",data,function(jsondata){
			        	if(jsondata.body.error_code === 22000){
			        		vueGetData.creatTips("删除权限组成功");
			        		_self.getPermissionList()
			        	}else{
			        		vueGetData.creatTips(jsondata.body.error_message);
			        	}
			        }.bind(this),function(){

			        }.bind(this));
					vueGetData.closePop();
		        }
		        cancelbtn.onclick = function(){
		        	vueGetData.closePop();
		        }
			},
			getRoleList:function(){
				let data = {}
        		data["product_id"] = this.serviceLines[this.serviceLineIndex].id
				//获取权限组列表
        		vueGetData.getAlertData("role/getRoleList",data,function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		this.roleList = jsondata.body.result;
						this.roleUpdateDate=[];
						let len = this.roleList.length;
						for(let i=0;i<len;i++){
							let data = {"edit":false}
							this.roleUpdateDate.push(data)
						}
		        	}else{
		        		console.log(jsondata.body.error_message)
		        	}
		        }.bind(this),function(){

		        }.bind(this));
			},
			addRole:function(){
				if(!this.roleAddData.name){
					vueGetData.creatTips("请填写权限组名称");
					return false;
				}
				let data={}
				data["product"] = this.serviceLines[this.serviceLineIndex].service_line_name;
				data["role_name"] = vueGetData.trim(this.roleAddData.name);
				
				vueGetData.getAlertData("role/createRole",data,function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		vueGetData.creatTips("添加权限组成功");
		        		this.roleAddData.name = ''
		        		this.getRoleList()
		        	}else{
		        		vueGetData.creatTips(jsondata.body.error_message);
		        	}
		        }.bind(this),function(){

		        }.bind(this));
			},
			relatePermission:function(item){
				this.permissionFormData = item;
				this.permissionFormData["product"] = this.serviceLines[this.serviceLineIndex].service_line_name;
				this.showDialog(1);
			},
			updateRole:function(item,index){
				if(!this.roleUpdateDate[index].edit){
					this.roleUpdateDate[index].edit=!this.roleUpdateDate[index].edit;
				}else{
					if(!item.role_name ){
						vueGetData.creatTips("请填写权限组名");
						return false;
					}
					let data={};
					data["role_id"]=item.role_id;
					data["role_name"]=vueGetData.trim(item.role_name);

					vueGetData.getAlertData("role/updateRole",data,function(jsondata){
			        	if(jsondata.body.error_code == 22000){
			        		vueGetData.creatTips("修改权限组成功");
			        		this.roleUpdateDate[index].edit=!this.roleUpdateDate[index].edit;
			        	}else{
			        		vueGetData.creatTips(jsondata.body.error_message);
			        	}
			        }.bind(this),function(){

			        }.bind(this));
				}
			},
			deleteRole:function(item){
				let data={}
				data["role_id"] = item.role_id;

				let _self = this;

				let str = '<div class="popCreat" id="deleteLogBox">'
		            +'<h3>确定要删除吗？</h3>'
		            +'<div class="btns"><span class="surebtn" id="surebtn">确定</span><span class="cancelbtn" id="cancelbtn">取消</span></div></div>';
				vueGetData.creatPop(str);

				let surebtn = document.getElementById("surebtn");
				let cancelbtn = document.getElementById("cancelbtn");

		        surebtn.onclick = function(){
					vueGetData.getAlertData("role/delRole",data,function(jsondata){
			        	if(jsondata.body.error_code === 22000){
			        		vueGetData.creatTips("删除权限组成功");
			        		_self.getRoleList()
			        	}else{
			        		vueGetData.creatTips(jsondata.body.error_message);
			        	}
			        }.bind(this),function(){

			        }.bind(this));
					vueGetData.closePop();
		        }
		        cancelbtn.onclick = function(){
		        	vueGetData.closePop();
		        }
			},
			getUsergroupList:function(){
				let data = {}
        		data["product"] = this.serviceLines[this.serviceLineIndex].service_line_name
				//获取报警组列表
        		vueGetData.getAlertData("product/getGroups",data,function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		this.usergroupList = jsondata.body.result;
		        		let len = this.usergroupList.length;
						this.groupUpdateDate=[];
						for(let i=0;i<len;i++){
							let data = {"edit":false}
							this.groupUpdateDate.push(data)
						}
		        	}else{
		        		console.log(jsondata.body.error_message)
		        	}
		        }.bind(this),function(){

		        }.bind(this));
			},
			addUsergroup:function(){
				if(!this.groupAddData.name){
					vueGetData.creatTips("请填写用户组名称");
					return false;
				}
				let data={}
				data["product"] = this.serviceLines[this.serviceLineIndex].service_line_name;
				data["group_name"] = vueGetData.trim(this.groupAddData.name);

				vueGetData.getAlertData("product/createGroup",data,function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		vueGetData.creatTips("添加用户组成功");
		        		this.groupAddData.name = ''
		        		this.getUsergroupList()
		        	}else{
		        		vueGetData.creatTips(jsondata.body.error_message);
		        	}
		        }.bind(this),function(){

		        }.bind(this));
			},
			relateRole:function(item){
				this.roleFormData = item;
				this.showDialog(2);
			},
			updateUsergroup:function(item,index){
				if(!this.groupUpdateDate[index].edit){
					this.groupUpdateDate[index].edit=!this.groupUpdateDate[index].edit;
				}else{
					if(!item.group_name ){
						vueGetData.creatTips("请填写报警组名");
						return false;
					}
					let data={};
					data["group_id"]=item.group_id;
					data["group_name"]=vueGetData.trim(item.group_name);

					vueGetData.getAlertData("product/updateGroup",data,function(jsondata){
			        	if(jsondata.body.error_code == 22000){
			        		vueGetData.creatTips("修改报警组成功");
			        		this.groupUpdateDate[index].edit=!this.groupUpdateDate[index].edit;
			        	}else{
			        		vueGetData.creatTips(jsondata.body.error_message);
			        	}
			        }.bind(this),function(){

			        }.bind(this));
				}
			},
			deleteUsergroup:function(item){
				let data={}
				data["group_id"] = item.group_id;

				let _self = this;

				let str = '<div class="popCreat" id="deleteLogBox">'
		            +'<h3>确定要删除吗？</h3>'
		            +'<div class="btns"><span class="surebtn" id="surebtn">确定</span><span class="cancelbtn" id="cancelbtn">取消</span></div></div>';
				vueGetData.creatPop(str);

				let surebtn = document.getElementById("surebtn");
				let cancelbtn = document.getElementById("cancelbtn");

		        surebtn.onclick = function(){
					vueGetData.getAlertData("product/deleteGroups",data,function(jsondata){
			        	if(jsondata.body.error_code === 22000){
			        		vueGetData.creatTips("删除用户组成功");
			        		_self.getUsergroupList()
			        	}else{
			        		vueGetData.creatTips(jsondata.body.error_message);
			        	}
			        }.bind(this),function(){

			        }.bind(this));
					vueGetData.closePop();
		        }
		        cancelbtn.onclick = function(){
		        	vueGetData.closePop();
		        }
			},
			showDialog: function(i){
				document.getElementsByClassName("dialog")[i].style.display = "block";
			},
			getUsers:function(item){
				this.userFormData = item;
				this.showDialog(0);
			},
		},
		components: {
			Users,
			Permission,
			Role
		}
	}
</script>
<style lang="less" >
@import "../../Css/rightside.less";

.metrichome {
	width: 100%;
	height:85%;
	position:absolute;
	overflow-y: auto;
	.navigation {
		padding:20px 20px 5px 20px;
		//background: @bgGray;
		//border-bottom: 1px solid rgba(78,105,130,.2);
	}
	.el-icon-plus{
		
		cursor:pointer;
	}
	.el-input__inner{
		margin:0px;
		width:300px;
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
			position:relative;
			.addOperation{
				padding-right:25px;
				margin-top:10px;
				margin-bottom:10px;
			}
			.addTitle{
				min-width:60px;
			}
		}
	}
}


</style>
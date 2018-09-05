<template>
	<div class="dialog">
		<div class="dialogOverlay"></div>
		<div class="dialogContent">
			<div class="dialogClose" @click="hideDialog"></div>
			<div class="dialogDetail">
				<div class="dialogTitle">{{permissionData.roleName}}</div>
				<div class="dialogDiv">
					<div class="configList">
						<div class="table-div">
							<table class="manage-table">
								<thead>
									<tr>
										<th width="10%"><input type="checkbox" v-model='checked' v-on:click='checkedAll' name="check-all"></th>
										<th width="10%">权限id</th>
										<th width="40%">权限标示</th>
										<th width="40%">权限描述</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="(item,index) in permissionList">
										<td><input type="checkbox" name="check-one" v-model='selectList' :value="item.permission_id"></td>
										<td>{{item.permission_id}}</td>
										<td>{{item.indentity}}</td>
										<td>{{item.des}}</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					<div class="dialogButtonDiv">
						<a href="javascript:;" class="dialog-blue-button" @click="save">保存</a>
						<a href="javascript:;" class="dialog-gray-button" @click="hideDialog">取消</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
<script>
import {mapGetters,mapActions} from 'vuex'
import vueGetData from "../../Js/vueGetData.js"

export default {
	data () {
		return {
			permissionData:{
				addData:{
					name:'',
				},
				roleId:0,
				roleName:'',
				productName:'',
			},
			permissionList:[],
			selectList:[],
			checked:false,
			copySelectList:[],

			copypermissionData:{},
		}
	},
	props: ["permissionFormData"],
	computed:mapGetters({
	}),
	watch: {
		permissionFormData:function(){
			let json = this.permissionFormData;
			this.permissionData.roleName = json["role_name"];
			this.permissionData.roleId = json["role_id"];
			this.permissionData.productName = json["product"]

			this.getPermissionList()
		},
		selectList:function(){
			if (this.selectList.length === this.permissionList.length) {
				this.checked=true;
			}else{
				this.checked=false;
			}
		},
	},

	methods: {
		checkedAll: function() {
		    var _this = this;
		    if (!this.checked) {//实现反选
		        _this.selectList = [];
		    }else{//实现全选
		        _this.selectList = [];
		        _this.permissionList.forEach(function(item) {
		        	_this.selectList.push(item.permission_id);
		        });
		    }
		},
		getPermissionList:function(){
			//获取业务线下的权限列表
			vueGetData.getAlertData("permission/getPermissionList",{"product":this.permissionData.productName},function(jsondata){
	        	if(jsondata.body.error_code === 22000){
	        		this.permissionList = jsondata.body.result;
	        	}else{
	        		console.log(jsondata.body.error_message)
	        	}
	        }.bind(this),function(){

	        }.bind(this));

	        //获取角色的当前权限列表
			vueGetData.getAlertData("role_permission/getPermission",{"role_id":this.permissionData.roleId},function(jsondata){
	        	if(jsondata.body.error_code === 22000){
	        		this.selectList=[]
	        		for(let i=0;i<jsondata.body.result.length;i++){
	        			this.selectList.push(jsondata.body.result[i]["permission_id"])
	        		}
	        		this.copySelectList = this.selectList;
	        	}else{
	        		console.log(jsondata.body.error_message)
	        	}
	        }.bind(this),function(){

	        }.bind(this));
		},
		save:function(){
			let data = {};
			data["role_id"] = this.permissionData.roleId;
			this.copySelectList = this.selectList;

			var str="";
			for(let i=0;i<this.selectList.length;i++){
				str=str+this.selectList[i]+",";
			}
            data["permission_id"] = str.substring(0,str.length-1);

            vueGetData.getAlertData("role_permission/relate",data,function(jsondata){
	        	if(jsondata.body.error_code === 22000){
	        		vueGetData.creatTips("绑定成功");
	        	}else{
	        		vueGetData.creatTips(jsondata.body.error_message);
	        	}
	        }.bind(this),function(){

	        }.bind(this));

			this.hideDialog();
		},
		hideDialog: function(){
			this.selectList = this.copySelectList;
			// //重置初始化数据
			// for(let key in this.copypermissionData){
			// 	this.permissionData[key] = this.copypermissionData[key];
			// }
			// this.$store.dispatch("getGroupUsers",{"tpl_id":this.templateId})
			document.getElementsByClassName("dialog")[1].style.display = "none";
		},
	},
	created: function(){
		//保存初始化数据
		for(let key in this.permissionData){
			this.copypermissionData[key] = this.permissionData[key]
		}
	},

}
</script>
<style>
.subDivContent {
	font-size: 14px;
}
.dialog .dialogContent .dialogSubDiv input[type=text]{
	
}
.validtime{
	width:200px!important;
}
</style>
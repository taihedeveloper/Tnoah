<template>
	<div class="dialog">
		<div class="dialogOverlay"></div>
		<div class="dialogContent">
			<div class="dialogClose" @click="hideDialog"></div>
			<div class="dialogDetail">
				<div class="dialogTitle">{{userData.groupName}}</div>
				<div class="dialogDiv">
					<div class="operationBox">
						<label><input type="radio" name="add_type" value="name" v-model="userData.addData.type" id="name"><span for="name">用户名</span></label>
						<label><input type="radio" name="add_type" value="id" v-model="userData.addData.type" id="id"><span for="id">用户id</span></label>
						<input type="text" id="item-name" class="addInput" v-model="userData.addData.value">
						<i class="el-icon-plus" @click="addUser"></i>
					</div>
					<div class="configList">
						<div class="table-div">
							<table class="manage-table">
								<thead>
									<tr>
										<th width="5%"><input type="checkbox" name="check-all" disabled="disabled"></th>
										<th width="15%">用户id</th>
										<th width="30%">邮箱</th>
										<th width="30%">电话</th>
										<th width="20%">操作</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="(item,index) in userData.userList">
										<td><input type="checkbox" disabled="disabled"></td>
										<td>{{item.uid}}</td>
										<td>{{item.email}}</td>
										<td>{{item.phone}}</td>
										<td>
											<a href="javascript:;" @click="deleteUser(item)">删除</a>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
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
			userData:{
				addData:{
					type:'name',
					value:'',
				},
				groupId:0,
				groupName:'',
				userList:[],
			},
			copyuserData:{},
		}
	},
	props: ["userFormData"],
	computed:mapGetters({
			productId:"serviceLineIndex"
	}),
	watch: {
		userFormData:function(){
			let json = this.userFormData;
			this.userData.groupName = json["group_name"];
			this.userData.groupId = json["group_id"];

			this.getUserList()
		}
	},

	methods: {
		getUserList:function(){
			vueGetData.getAlertData("user_group/getGroupUsers",{"group_id":this.userData.groupId},function(jsondata){
	        	if(jsondata.body.error_code === 22000){
	        		this.userData.userList = jsondata.body.result;
	        	}else{
	        		console.log(jsondata.body.error_message)
	        	}
	        }.bind(this),function(){

	        }.bind(this));
		},
		hideDialog: function(){
			// //重置初始化数据
			// for(let key in this.copyuserData){
			// 	this.userData[key] = this.copyuserData[key];
			// }
			// this.$store.dispatch("getGroupUsers",{"tpl_id":this.templateId})
			document.getElementsByClassName("dialog")[0].style.display = "none";
		},
		addUser: function(){
			let data={};
			data["group_id"] = this.userData.groupId;
			data["product_id"] = this.productId;
			if(this.userData.addData.type == "name"){
				if(!this.userData.addData.value){
					vueGetData.creatTips("请填写用户名");
					return false;
				}
				data["user_name"] = this.userData.addData.value;
			}else{
				if(!this.userData.addData.value){
					vueGetData.creatTips("请填写用户id");
					return false;
				}
				data["user_id"] = this.userData.addData.value;
			}

			vueGetData.getAlertData("user_group/relate",data,function(jsondata){
	        	if(jsondata.body.error_code === 22000){
	        		vueGetData.creatTips("添加用户成功");
	        		this.userData.addData.value="";
	        		this.getUserList()
	        	}else if(jsondata.body.error_code === 22005){
	        		vueGetData.creatTips("该用户不存在");
	        	}else if(jsondata.body.error_code === 22001){
	        		vueGetData.creatTips("该用户已存在");
	        	}
	        }.bind(this),function(){

	        }.bind(this));

		},
		deleteUser: function(item){
			let data = {};
			data["group_id"] = this.userData.groupId;
			data["user_id"] = item.uid;

			vueGetData.getAlertData("user_group/unrelate",data,function(jsondata){
	        	if(jsondata.body.error_code === 22000){
	        		vueGetData.creatTips("删除用户成功");
	        		this.getUserList()
	        	}else{
	        		vueGetData.creatTips(jsondata.body.error_message);
	        	}
	        }.bind(this),function(){

	        }.bind(this));
		}
	},
	created: function(){

		//保存初始化数据
		for(let key in this.userData){
			this.copyuserData[key] = this.userData[key]
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
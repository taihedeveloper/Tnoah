<template>
	<div class="dialog">
		<div class="dialogOverlay"></div>
		<div class="dialogContent">
			<div class="dialogClose" @click="hideDialog"></div>
			<div class="dialogDetail">
				<div class="dialogTitle">{{roleData.usergroupName}}</div>
				<div class="dialogDiv">
					<div class="configList">
						<div class="table-div">
							<table class="manage-table">
								<thead>
									<tr>
										<th width="20%"><input type="checkbox" v-model='checked' v-on:click='checkedAll' name="check-all"></th>
										<th width="30%">角色id</th>
										<th width="50%">角色名</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="(item,index) in roleList">
										<td><input type="checkbox" name="check-one" v-model='selectList' :value="item.role_id"></td>
										<td>{{item.role_id}}</td>
										<td>{{item.role_name}}</td>
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
			roleData:{
				addData:{
					name:'',
				},
				usergroupId:0,
				usergroupName:'',
				productId:0,
			},
			roleList:[],
			selectList:[],
			checked:false,
			copySelectList:[],

			copyroleData:{},
		}
	},
	props: ["roleFormData"],
	computed:mapGetters({
	}),
	watch: {
		roleFormData:function(){
			let json = this.roleFormData;
			this.roleData.usergroupName = json["group_name"];
			this.roleData.usergroupId = json["group_id"];
			this.roleData.productId = json["product_id"]

			this.getRoleList()
		},
		selectList:function(){
			if (this.selectList.length === this.roleList.length) {
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
		        _this.roleList.forEach(function(item) {
		        	_this.selectList.push(item.role_id);
		        });
		    }
		},
		getRoleList:function(){
			//获取业务线下的角色列表
			vueGetData.getAlertData("role/getRoleList",{"product_id":this.roleData.productId},function(jsondata){
	        	if(jsondata.body.error_code === 22000){
	        		this.roleList = jsondata.body.result;
	        	}else{
	        		console.log(jsondata.body.error_message)
	        	}
	        }.bind(this),function(){

	        }.bind(this));

	        //获取角色的当前角色列表
			vueGetData.getAlertData("group_role/getRole",{"group_id":this.roleData.usergroupId},function(jsondata){
	        	if(jsondata.body.error_code === 22000){
	        		this.selectList=[]
	        		for(let i=0;i<jsondata.body.result.length;i++){
	        			this.selectList.push(jsondata.body.result[i]["role_id"])
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
			data["group_id"] = this.roleData.usergroupId;
			this.copySelectList = this.selectList;

			var str="";
			for(let i=0;i<this.selectList.length;i++){
				str=str+this.selectList[i]+",";
			}
            data["role_id"] = str.substring(0,str.length-1);

            vueGetData.getAlertData("group_role/relate",data,function(jsondata){
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
			// for(let key in this.copyroleData){
			// 	this.roleData[key] = this.copyroleData[key];
			// }
			// this.$store.dispatch("getGroupUsers",{"tpl_id":this.templateId})
			document.getElementsByClassName("dialog")[2].style.display = "none";
		},
	},
	created: function(){
		//保存初始化数据
		for(let key in this.roleData){
			this.copyroleData[key] = this.roleData[key]
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
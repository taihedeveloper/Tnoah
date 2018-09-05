<template>
	<div class="servicehome">
		<div class="navigation">
			<el-breadcrumb separator="/">
				<el-breadcrumb-item :to="{ path: '/' }">首页</el-breadcrumb-item>
				<el-breadcrumb-item>业务线配置</el-breadcrumb-item>
			</el-breadcrumb>
		</div>
		<div class="operation">
			<div class="operationBox">
				<template v-if="canWrite">
					业务线名
					<input type="text" id="item-name" class="addInput" v-model="addData.name">
					<i class="el-icon-plus" @click="addServiceLine"></i>
				</template>
				<template v-else>
					业务线名
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
							<th width="10%"><input type="checkbox" name="check-all" disabled="disabled"></th>
							<th width="30%">业务线id</th>
							<th width="30%">业务线名</th>
							<th width="30%">操作</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item,index) in servicelineList">
							<td><input type="checkbox" name="check-one" disabled="disabled"></td>
							<td>{{item.id}}</td>
							<td>
								<template>
									<el-input v-show="updateData[index].edit" v-model="item.service_line_name"></el-input>
									<span v-show="!updateData[index].edit">{{item.service_line_name}}</span>
								</template>
							</td>
							<td>
								<template v-if="canWrite">
									<a href="javascript:;" :data-id="item.id" :data-index="index" @click="updateServiceLine(item,index)">{{updateData[index].edit?'完成':'编辑'}}</a>
									<a href="javascript:;" :data-id="item.id" :data-index="index" @click="deleteServiceLine(item.id)">删除</a>
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
import {Breadcrumb, BreadcrumbItem,Input,Select} from 'element-ui'

	export default{
		name:'home',
		data () {
			return {
				servicelineList:[],
				addData:{
					name:'',
				},
				updateData:[],
				canWrite:false,//是否有写权限
			}
		},
		created () {
			this.getServiceLineList();

			//获取用户业务线配置权限
			vueGetData.getAlertData("user/getPermission",{"user_name":vueGetData.username()},function(jsondata){
	        	if(jsondata.body.error_code === 22000){
	        		for(let item in jsondata.body.result){
	        			if(item == "tnoah"){
	        				for(let childitem in jsondata.body.result[item]){
								if(childitem == "serviceline"){
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
		computed:mapGetters({
			permissionList:"permissionList"
		}),
		watch:{
			servicelineList:function(){
				let len = this.servicelineList.length;
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
			getServiceLineList:function(){
				vueGetData.getData("getallserviceline",{},function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		this.servicelineList = jsondata.body.data;
		        	}else{
			        	console.log(jsondata.body.msg)
			        }
		        }.bind(this),function(){

		        }.bind(this));
			},
			addServiceLine:function(){
				if(!this.addData.name){
					vueGetData.creatTips("请填写业务线名");
					return false;
				}
				let data={}
				data["service_line_name"] = this.addData.name;

				vueGetData.getData("addserviceLine",data,function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		vueGetData.creatTips("添加业务线成功");
		        		//刷新列表
		        		this.addData.name="";
		        		this.addData.uic=[];
		        		this.getServiceLineList()
		        	}else{
		        		vueGetData.creatTips("添加业务线失败");
			        }
		        }.bind(this),function(){

		        }.bind(this));
			},
			updateServiceLine:function(item,index){
				if(!this.updateData[index].edit){
					this.updateData[index].edit=!this.updateData[index].edit;
				}else{
					if(!item.service_line_name){
						vueGetData.creatTips("请填写业务线名");
						return false;
					}

					let data={};
					data["id"]=item.id;
					data["service_line_name"]=vueGetData.trim(item.service_line_name);

					vueGetData.getData("updateserviceline",data,function(jsondata){
			        	if(jsondata.body.error_code == 22000){
			        		if(jsondata.body.data == 1){
			        			vueGetData.creatTips("修改业务线成功");
				        		this.getServiceLineList()
			        		}else{
			        			vueGetData.creatTips("未做修改");
			        		}
			        		this.updateData[index].edit=!this.updateData[index].edit;
			        	}
			        }.bind(this),function(){

			        }.bind(this));
				}
			},
			deleteServiceLine:function(id){
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
					vueGetData.getData("deleteserviceline",data,function(jsondata){
	 					if(jsondata.body.error_code == 22000){
				        	if(jsondata.body.data == 1){
				        		vueGetData.creatTips("删除业务线成功");
								_self.getServiceLineList()
				        	}else{
				        		vueGetData.creatTips("删除业务线失败");
					        }
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
		}
	}
</script>
<style lang="less" >
@import "../../Css/rightside.less";

.servicehome {
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
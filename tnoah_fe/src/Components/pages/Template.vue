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
				<template v-if="canWrite">
					模板名
					<input type="text" id="item-name" class="addInput" v-model="addData.name">
					报警组
					<el-select v-model="addData.uic" multiple placeholder="请选择">
						<el-option v-for="child in alertList" :key="child.group_id" :label="child.group_name" :value="child.group_id">
						</el-option>
					</el-select>
					<i class="el-icon-plus" @click="addTemplate"></i>
				</template>
				<template v-else>
					模板名
					<input type="text" id="item-name" class="addInput" v-model="addData.name" disabled="disabled">
					报警组
					<el-select v-model="addData.uic" multiple placeholder="请选择" disabled="disabled">
						<el-option v-for="child in alertList" :key="child.group_id" :label="child.group_name" :value="child.group_id">
						</el-option>
					</el-select>
					<i class="el-icon-plus" @click="noPermission"></i>
				</template>

			</div>
		</div>
		<div class="configList">
			<div class="table-div">
				<table class="manage-table">
					<thead>
						<tr>
							<th width="9%"><input type="checkbox" name="check-all" disabled="disabled"></th>
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
							<td><input type="checkbox" name="check-one" disabled="disabled"></td>
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
									<el-select v-model="updateData[index].uic" multiple placeholder="请选择" v-show="updateData[index].edit">
										<el-option v-for="child in alertList" :key="child.group_id" :label="child.group_name" :value="child.group_id">
										</el-option>
									</el-select>
									<span v-show="!updateData[index].edit">{{updateData[index].uicStr}}</span>
								</template>
							</td>
							<td>{{item.create_user}}</td>
							<td>{{item.create_at | formatDate}}</td>
							<td>
								<a href="javascript:;" :data-id="item.id" :data-index="index"></a><router-link :to="'/group/'+groupId+'/template/'+item.id+'/strategy'">报警策略</router-link>
								<a href="javascript:;" :data-id="item.id" :data-index="index" @click="copyTemplate(item.id)">复制</a>
								<template v-if="item.tpl_name=='agent检活报警模板(通用)' || !canWrite">
									<a href="javascript:;" style="color:#c5c5c5"  @click="noPermission">编辑</a>
									<a href="javascript:;" style="color:#c5c5c5" @click="noPermission">删除</a>
								</template>
								<template v-else>
									<a href="javascript:;" :data-id="item.id" :data-index="index" @click="updateTemplate(item,index)">{{updateData[index].edit?'完成':'编辑'}}</a>
									<a href="javascript:;" :data-id="item.id" :data-index="index" @click="deleteTemplate(item.id)">删除</a>
								</template>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<Templates :editinitformdata="editGetFormData"></Templates>
	</div>

</template>
<script>
import {mapGetters,mapActions} from 'vuex'
import vueGetData from "../../Js/vueGetData.js"
import {Breadcrumb, BreadcrumbItem,Input,Select} from 'element-ui'
import Templates from "../dialogs/Templates.vue"

	export default{
		name:'home',
		data () {
			return {
				groupId:0,
				groupName:'',
				templateList:[],
				alertList:[],
				addData:{
					ip:'',
					uic:[],
				},
				searchData:{
					ip:'',
					name:''
				},
				updateData:[],
				value5: [],
				editGetFormData:{},	//编辑时数据
				canWrite:false,//是否有写权限
			}
		},
		computed:mapGetters({
			serviceLineName:"serviceLineName"
		}),
		created () {
			this.$store.dispatch("getServiceLineName");
			this.fetchData();
		},
		watch:{
			'$route': 'fetchData',
			templateList:function(){
				let len = this.templateList.length;
				this.updateData=[];

				for(let i=0;i<len;i++){
					let data = {"edit":false}
					let uic=[];
					if(this.templateList[i].uicArr!=[]){
						let uicStr="";
						for(let j=0;j<this.templateList[i].uicArr.length;j++){
							uic.push(this.templateList[i].uicArr[j].group_id)
							uicStr += this.templateList[i].uicArr[j].group_name + ",";
						}
						data["uic"] = uic;
						data["uicStr"] = uicStr.substring(0,uicStr.length-1);
					}
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
				this.getTemplateList()
				// //获取报警组列表
				vueGetData.getAlertData("product/getGroups",{"product":this.serviceLineName},function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		this.alertList = jsondata.body.result;
		        	}else{
		        	}
		        }.bind(this),function(){

		        }.bind(this));
			},
			getTemplateList:function(){
				vueGetData.getData("gettemplates",{"group_id":this.groupId},function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		this.templateList = jsondata.body.data;
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
				if(this.addData.uic.length==0){
					vueGetData.creatTips("请选择报警组");
					return false;
				}
				let data={}
				data["group_id"] = this.groupId;
				data["tpl_name"] = vueGetData.trim(this.addData.name);
				data["create_user"] = vueGetData.username();

				if(this.addData.uic.length>0){
					let tempUic = this.addData.uic[0];
					for(let i=1;i<this.addData.uic.length;i++){
						tempUic += "," + this.addData.uic[i];
					}
					data["uic"] = tempUic;
				}

				vueGetData.getData("addTemplate",data,function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		vueGetData.creatTips("添加模板成功");
		        		//刷新列表
		        		this.addData.name="";
		        		this.addData.uic=[];
		        		this.getTemplateList()
		        	}else{
		        		vueGetData.creatTips("添加模板失败");
			        }
		        }.bind(this),function(){

		        }.bind(this));
			},
			updateTemplate:function(item,index){
				if(!this.updateData[index].edit){
					this.updateData[index].edit=!this.updateData[index].edit;
				}else{
					if(!item.tpl_name){
						vueGetData.creatTips("请填写模板名");
						return false;
					}
					if(this.updateData[index].uic.length==0){
						vueGetData.creatTips("请选择报警组");
						return false;
					}
					let data={};
					data["tpl_id"]=item.id;
					data["tpl_name"]=vueGetData.trim(item.tpl_name);

					let uicStr="";
					for(let i=0;i<this.updateData[index].uic.length;i++){
						uicStr += this.updateData[index].uic[i]+ ",";
					}
					data["uic"] = uicStr.substring(0,uicStr.length-1);

					vueGetData.getData("updatetemplate",data,function(jsondata){
			        	if(jsondata.body.error_code == 22000){
			        		if(jsondata.body.data == 1){
			        			vueGetData.creatTips("修改模板成功");
				        		this.getTemplateList()
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

				let _self = this;

				let str = '<div class="popCreat" id="deleteLogBox">'
		            +'<h3>确定要删除吗？</h3>'
		            +'<div class="btns"><span class="surebtn" id="surebtn">确定</span><span class="cancelbtn" id="cancelbtn">取消</span></div></div>';
				vueGetData.creatPop(str);

				let surebtn = document.getElementById("surebtn");
				let cancelbtn = document.getElementById("cancelbtn");

		        surebtn.onclick = function(){
					vueGetData.getData("deletetemplate",data,function(jsondata){
			        	if(jsondata.body.data == 1){
			        		vueGetData.creatTips("删除模板成功");
							_self.getTemplateList()
			        	}else{
			        		vueGetData.creatTips("删除模板失败");
				        }
			        }.bind(this),function(){

			        }.bind(this));
					vueGetData.closePop();
		        }
		        cancelbtn.onclick = function(){
		        	vueGetData.closePop();
		        }
			},
			showDialog: function(){
				this.$store.dispatch('getTreeList',{});
				document.getElementsByClassName("dialog")[0].style.display = "block";
			},
			copyTemplate:function(id){
				let data={};
				data["group_id"] = this.groupId;
				data["tpl_id"] = id;

				this.editGetFormData = data;
				this.showDialog();
			}
		},
		components: {
			Templates
		}
	}
</script>
<style lang="less" >
@import "../../Css/rightside.less";

.templatehome {
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
<template>
	<div class="confighome">
		<div class="navigation">
			<el-breadcrumb separator="/">
				<el-breadcrumb-item :to="{ path: '/' }">首页</el-breadcrumb-item>
				<el-breadcrumb-item :to="{ path: '/group' }">集群配置</el-breadcrumb-item>
			</el-breadcrumb>
		</div>
		<div class="operation">
			<span class="nowrap">
				<label for="monitor-type">业务线</label>
				<select id="monitor-type" class="manage-select" v-model="serviceLineIndex">
					<option v-for="(option,index) in serviceLines" :value="index">{{option.service_line_name}}</option>
				</select>
			</span>
			<!-- 操作 -->
			<div class="operationBox">
				<input type="text" id="item-name" placeholder="集群名" class="manage-input" v-model="searchData.name">
				<i class="el-icon-search" @click="searchGroup"></i>

				<template v-if="canWrite">
					<span class="addOperation">
						集群名
						<input type="text" id="item-name" class="addInput" v-model="addData.name">
						配置
						<input type="text" id="item-name" class="addInput" v-model="addData.config">
						备注
						<input type="text" id="item-name" class="addInput" v-model="addData.remarks">
					</span>
					<i class="el-icon-plus" @click="addGroup"></i>
				</template>
				<template v-else>
					<span class="addOperation">
						集群名
						<input type="text" id="item-name" class="addInput" v-model="addData.name" disabled="disabled">
						配置
						<input type="text" id="item-name" class="addInput" v-model="addData.config" disabled="disabled">
						备注
						<input type="text" id="item-name" class="addInput" v-model="addData.remarks" disabled="disabled">
					</span>
					<i class="el-icon-plus" @click="noPermission"></i>
				</template>
				<!-- <a href="javascript:;" class="white-button" @click="searchGroup">查询</a> -->
			</div>
		</div>
			<!-- 列表部分 -->
		<div class="configList">
			<div class="table-div">
				<table class="manage-table">
					<thead>
						<tr>
							<th width="3%"><input type="checkbox" name="check-all" disabled="disabled"></th>
							<th width="6%">集群id</th>
							<th width="17%">集群名</th>
							<th width="10%">同步状态</th>
							<th width="24%">设置</th>
							<th width="10%">备注</th>
							<th width="15%">配置</th>
							<th width="15%">操作</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item,index) in groupList">
							<td><input type="checkbox" disabled="disabled"></td>
							<td>{{item.id}}</td>
							<td>
								<template>
									<el-input v-show="updateData[index].edit" v-model="item.group_name"></el-input>
									<span v-show="!updateData[index].edit">{{item.group_name}}</span>
								</template>
							</td>
							<!-- <td>{{item.group_name}}</td> -->
							<td>
								<template v-if="item.sync_status==0">未同步</template>
								<template v-else>已同步</template>
							</td>
							<td>
								<template>
									<el-input v-show="updateData[index].edit" v-model="item.config"></el-input>
									<el-tooltip placement="top-start" effect="light">
										<div slot="content">{{item.config}}</div>
										<span v-show="!updateData[index].edit">{{item.config}}</span>
									</el-tooltip>
								</template>
							</td>
							<!-- <td>{{item.config}}</td> -->
							<td>
								<template>
									<el-input v-show="updateData[index].edit" v-model="item.remarks"></el-input>
									<el-tooltip placement="top-start" effect="light">
										<div slot="content">{{item.remarks}}</div>
										<span v-show="!updateData[index].edit">{{item.remarks}}</span>
									</el-tooltip>
								</template>
							</td>
							<!-- <td>{{item.remarks}}</td> -->
							<td>
								<a href="javascript:;" :data-id="item.id" :data-index="index"></a><router-link :to="'/group/'+item.id+'/metric'">采集项</router-link>
								<a href="javascript:;" :data-id="item.id" :data-index="index"></a><router-link :to="'/group/'+item.id+'/machine'">机器</router-link>
								<a href="javascript:;" :data-id="item.id" :data-index="index"></a><router-link :to="'/group/'+item.id+'/template'">报警模板</router-link>
							</td>
							<td>
								<template v-if="canWrite">
									<a href="javascript:;" :data-id="item.id" :data-index="index" @click="updateGroup(item,index)">{{updateData[index].edit?'完成':'编辑'}}</a>
									<a href="javascript:;" :data-id="item.id" :data-index="index" @click="deleteGroup(item.id)">删除</a>
									<a href="javascript:;" :data-id="item.id" :data-index="index" @click="synData(item.id)">同步数据</a>
								</template>
								<template v-else>
									<a href="javascript:;" style="color:#c5c5c5"  @click="noPermission">编辑</a>
									<a href="javascript:;" style="color:#c5c5c5"  @click="noPermission">删除</a>
									<a href="javascript:;"  style="color:#c5c5c5"  @click="noPermission">同步数据</a>
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
				serviceLines:[],
				serviceLineIndex:0,
				servicelineId:0,
				groupList:[],
				addData:{
					name:'',
					config:'',
					remarks:'',
				},
				searchData:{
					name:''
				},
				updateData:[],
				canWrite:false,//是否有写权限
			}
		},
		computed:mapGetters({
			productId:"serviceLineIndex"
		}),
		watch:{
			serviceLineIndex:function(){
				window.localStorage.setItem("serviceLineIndex", this.serviceLineIndex);
				window.localStorage.setItem("serviceLineName", this.serviceLines[this.serviceLineIndex].service_line_name);
				// this.$store.dispatch("pushServiceLineName",{"name":this.serviceLines[this.serviceLineIndex].service_line_name})
        		this.servicelineId = this.serviceLines[this.serviceLineIndex].id
        		this.getGrouplist()

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
									else if(childitem == this.serviceLines[this.serviceLineIndex].service_line_name){
										this.canWrite = true;
										return;
									}
								}
		        			}
		        		}
		        		this.canWrite = false;
		        	}else{
		        		console.log(jsondata.body.error_message);
		        	}
		        }.bind(this),function(){

		        }.bind(this));
			},
			groupList:function(){
				let len = this.groupList.length;
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
			getGrouplist:function(){
				vueGetData.getData("getgroupinfos",{"service_line_id":this.servicelineId},function(jsondata){
			        if(jsondata.body.error_code === 22000){
		        		this.groupList = jsondata.body.data;
		        	}
		        }.bind(this),function(){

		        }.bind(this));
		    },
			// getTableList:function() {
			// 	// window.localStorage.setItem("serviceLineIndex", this.serviceLineIndex);
			// 	// window.localStorage.setItem("serviceLineName", this.serviceLineIndex);
			// 	// this.$store.dispatch("pushServiceLineName",{"name":this.serviceLines[this.serviceLineIndex]["service_line_name"]})
			// 	// this.servicelineId = this.serviceLines[this.serviceLineIndex]["id"];
			// 	// this.getGrouplist()
			// },
			addGroup:function(){
				if(!this.addData.name){
					vueGetData.creatTips("请填写集群名");
					return false;
				}
				let data={}
				data["group_name"] = vueGetData.trim(this.addData.name);
				data["service_line_id"] = this.servicelineId;
				data["service_line_name"] = this.serviceLines[this.serviceLineIndex]["service_line_name"];
				data["config"] = vueGetData.trim(this.addData.config);
				data["remarks"] = vueGetData.trim(this.addData.remarks);

				vueGetData.postData("addgroup",data,function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		vueGetData.creatTips("添加集群成功");
		        		this.addData.name="";
				        this.addData.config="";
				        this.addData.remarks="";
		        		this.getGrouplist()
		        	}else if(jsondata.body.error_code === 22005){
		        		vueGetData.creatTips("添加集群失败，请确认设置是否为json格式");
			        }
		        }.bind(this),function(){

		        }.bind(this));
			},
			searchGroup:function(){
				if(!this.searchData.name){
					vueGetData.creatTips("请填写集群名");
					return false;
				}
				let data={};
				data["group_name"]=vueGetData.trim(this.searchData.name);
				data["service_line_id"] = this.servicelineId;

				vueGetData.getData("groupinservline",data,function(jsondata){
		        	if(jsondata.body.data.length == 0){
		        		vueGetData.creatTips("不存在集群"+data["group_name"]);
		        		this.getGrouplist()
		        	}else{
			        	this.groupList = jsondata.body.data;
			        }
			        this.searchData.name="";
		        }.bind(this),function(){

		        }.bind(this));
			},
			updateGroup:function(item,index){
				if(!this.updateData[index].edit){
					this.updateData[index].edit=!this.updateData[index].edit;
				}else{
					if(!item.group_name){
						vueGetData.creatTips("请填写集群名");
						return false;
					}
					let data={};
					data["id"]=item.id;
					if(item.name){
						data["group_name"]=vueGetData.trim(item.name);
					}
					if(item.config){
						data["config"]=vueGetData.trim(item.config);
					}
					if(item.remarks){
						data["remarks"]=vueGetData.trim(item.remarks);
					}

					vueGetData.postData("updategroup",data,function(jsondata){
			        	if(jsondata.body.error_code == 22000){
			        		if(jsondata.body.data == 1){
			        			vueGetData.creatTips("修改集群成功");
			        			// this.getGrouplist()
			        		}else{
			        			vueGetData.creatTips("未做任何修改");
			        		}
			        		this.updateData[index].edit=!this.updateData[index].edit;
			        	}else if(jsondata.body.error_code == 22005){
			        		vueGetData.creatTips("修改集群失败，请确认设置是否为json格式");
			        	}
			        }.bind(this),function(){

			        }.bind(this));
				}
			},
			deleteGroup:function(id){
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
					vueGetData.getData("deletegroup",data,function(jsondata){
			        	if(jsondata.body.data == 1){
			        		vueGetData.creatTips("删除集群成功");
			        		_self.getGrouplist()
			        	}else{
			        		vueGetData.creatTips("删除集群失败");
				        }
			        }.bind(this),function(){

			        }.bind(this));
					vueGetData.closePop();
		        }
		        cancelbtn.onclick = function(){
		        	vueGetData.closePop();
		        }
			},
			synData:function(id){
				let data={};
				data["id"] = id;
				vueGetData.getData("distgroupconf",data,function(jsondata){
		        	if(jsondata.body.error_code == 22000){
		        		vueGetData.creatTips("同步数据成功");
		        		this.getGrouplist()
		        	}else{
		        		vueGetData.creatTips(jsondata.body.error_msg);
			        }
		        }.bind(this),function(){

		        }.bind(this));
			}
		},
		created(){
			//解决问题：选择完具体集群后切换集群报警配置再回服务树，点之前选择的集群没反应
			this.$store.dispatch('pushTreeId',{"id":1});

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
		padding:20px 20px 5px 20px;
		//background: @bgGray;
		//border-bottom: 1px solid rgba(78,105,130,.2);
	}
	.operation{
		padding: 10px 20px 20px;
		background:#f9f9f9;;
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
				padding-right:20px;
				//position:relative;
				float:right;

			}
		}
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
	.el-tooltip__popper.is-light {
	    background: #fff;
	    border: 1px solid #1f2d3d;
	    margin:30px;
	}


</style>
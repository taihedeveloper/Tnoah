<template>
	<div class="metrichome">
		<div class="navigation">
			<el-breadcrumb separator="/">
				<el-breadcrumb-item :to="{ path: '/' }">首页</el-breadcrumb-item>
				<el-breadcrumb-item :to="{ path: '/group' }">集群配置 {{groupName}}</el-breadcrumb-item>
				<el-breadcrumb-item>采集项配置</el-breadcrumb-item>
			</el-breadcrumb>
		</div>
		<div class="operation">
			<el-tabs v-model="tabName">
				<el-tab-pane label="进程类" name="process">
					<div class="operationBox">
						<template v-if="canWrite">
							名称
							<input type="text" id="item-name" class="addInput" v-model="processAddData.name">
							路径
							<input type="text" id="item-addition" class="addInput" style="width:260px;" v-model="processAddData.value">
							监控资源使用情况
							<input type="checkbox" id="item-radio" v-model="processAddData.monitor">
							<i class="el-icon-plus" @click="addProcess"></i>
							<span style="color:red;margin-left:10px;">(进程类命名以"PROC_"为前缀)</span>
						</template>
						<template v-else>
							名称
							<input type="text" id="item-name" class="addInput" v-model="processAddData.name" disabled="disabled">
							路径
							<input type="text" id="item-addition" class="addInput" style="width:260px;" v-model="processAddData.value" disabled="disabled">
							监控资源使用情况
							<input type="checkbox" id="item-radio" v-model="processAddData.monitor" disabled="disabled">
							<i class="el-icon-plus" @click="noPermission"></i>
							<span style="color:red;margin-left:10px;">(进程类命名以"PROC_"为前缀)</span>
						</template>
					</div>
			    </el-tab-pane>
			    <el-tab-pane label="业务类" name="business">
			    	<template v-if="canWrite">
			    		<button class="add-blue-button" @click="showDialog"><span></span>新建采集项</button>
			    	</template>
			    	<template v-else>
			    		<button class="add-gray-button" @click="noPermission"><span></span>新建采集项</button>
			    	</template>
			    </el-tab-pane>
			    <el-tab-pane label="端口类" name="port">
			    	<div class="operationBox">
			    		<template v-if="canWrite">
			    			名称
							<input type="text" id="item-name" class="addInput" v-model="portAddData.name">
							端口号
							<input type="text" id="item-addition" class="addInput" v-model="portAddData.value">
							<i class="el-icon-plus" @click="addPort"></i>
							<span style="color:red;margin-left:10px;">(端口类命名以"PORT_"为前缀)</span>
			    		</template>
						<template v-else>
			    			名称
							<input type="text" id="item-name" class="addInput" v-model="portAddData.name" disabled="disabled">
							端口号
							<input type="text" id="item-addition" class="addInput" v-model="portAddData.value" disabled="disabled">
							<i class="el-icon-plus" @click="noPermission"></i>
							<span style="color:red;margin-left:10px;">(端口类命名以"PORT_"为前缀)</span>
			    		</template>
					</div>
			    </el-tab-pane>
	  		</el-tabs>
		</div>
		<div class="configList">
			<div class="table-div">
				<table class="manage-table" v-if="tabName=='process'">
					<thead>
						<tr>
							<th width="5%"><input type="checkbox" name="check-all" disabled="disabled"></th>
							<th width="25%">名称</th>
							<th width="40%">路径</th>
							<th width="10%">监控资源</th>
							<th width="20%">操作</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item,index) in processList">
							<td><input type="checkbox" disabled="disabled"></td>
							<td>{{item.item_name_prefix}}</td>
							<td>
								<template>
									<el-input v-show="processUpdateData[index].edit" v-model="item.proc_path"></el-input>
									<span v-show="!processUpdateData[index].edit">{{item.proc_path}}</span>
								</template>
							</td>
							<td>
								<template v-if="item.proc_deeply_path=='' || !item.proc_deeply_path">
									否
								</template>
								<template v-else>
									是
								</template>
							</td>
							<td>
								<template v-if="canWrite">
									<a href="javascript:;" :data-id="item.id" :data-index="index" @click="updateProcess(item,index)">{{processUpdateData[index].edit?'完成':'编辑'}}</a>
									<a href="javascript:;" :data-id="item.id" :data-index="index" @click="deleteProcess(item)">删除</a>
								</template>
								<template v-else>
									<a href="javascript:;" style="color:#c5c5c5"  @click="noPermission">编辑</a>
									<a href="javascript:;" style="color:#c5c5c5"  @click="noPermission">删除</a>
								</template>
							</td>
						</tr>
					</tbody>
				</table>
				<table class="manage-table" v-else-if="tabName=='business'">
					<thead>
						<tr>
							<th width="3%"><input type="checkbox" name="check-all" disabled="disabled"></th>
							<th width="25%">名称</th>
							<th width="20%">日志路径</th>
							<th width="6%">日志风格</th>
							<th width="10%">时间格式</th>
							<th width="6%">匹配方式</th>
							<th width="20%">匹配内容</th>
							<th width="10%">操作</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item,index) in businessList">
							<td><input type="checkbox" disabled="disabled"></td>
							<td>
								<el-tooltip placement="top-start" effect="light">
									<div slot="content">{{item.item_name_prefix}}</div>
									<span>{{item.item_name_prefix}}</span>
								</el-tooltip>
							</td>
							<td>
								<el-tooltip placement="top-start" effect="light">
									<div slot="content">{{item.log_path}}</div>
									<span>{{item.log_path}}</span>
								</el-tooltip>
							</td>
							<td>{{item.log_style}}</td>
							<td>{{item.log_format}}</td>
							<td>
								<template v-if="item.match_str">match_str
								</template>
								<template v-if="item.awk_str">awk_str
								</template>
								<template v-if="item.search_str">search_str
								</template>
							</td>
							<td>
								<el-tooltip placement="top-start" effect="light">
									<div slot="content">{{item.match_str}}{{item.awk_str}}{{item.search_str}}</div>
									<span>{{item.match_str}}{{item.awk_str}}{{item.search_str}}</span>
								</el-tooltip>
							</td>
							<td>
								<template v-if="item.item_name_prefix=='BUS_Tagent_Heartbeat' || !canWrite">
									<a href="javascript:;" style="color:#c5c5c5"  @click="noPermission">编辑</a>
									<a href="javascript:;" style="color:#c5c5c5"  @click="noPermission">删除</a>
								</template>
								<template v-else>
									<a href="javascript:;" :data-id="item.id" :data-index="index" @click="editBusiness(item)">编辑</a>
									<a href="javascript:;" :data-id="item.id" :data-index="index" @click="deleteBusiness(item)">删除</a>
								</template>
							</td>
						</tr>
					</tbody>
				</table>
				<table class="manage-table" v-else-if="tabName=='port'">
					<thead>
						<tr>
							<th width="5%"><input type="checkbox" name="check-all" disabled="disabled"></th>
							<th width="45%">名称</th>
							<th width="30%">端口号</th>
							<th width="20%">操作</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item,index) in portList">
							<td><input type="checkbox" disabled="disabled"></td>
							<!-- <td>{{item.ip_addr}}</td> -->
							<!-- <td>{{item.name}}</td> -->
							<td>{{item.item_name_prefix}}</td>
							<td>
								<template>
									<el-input v-show="portUpdateData[index].edit" v-model="item.port_num"></el-input>
									<span v-show="!portUpdateData[index].edit">{{item.port_num}}</span>
								</template>
							</td>
							<td>
								<template v-if="canWrite">
									<a href="javascript:;" :data-id="item.id" :data-index="index" @click="updatePort(item,index)">{{portUpdateData[index].edit?'完成':'编辑'}}</a>
									<a href="javascript:;" :data-id="item.id" :data-index="index" @click="deletePort(item)">删除</a>
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
		<Business></Business>
	</div>

</template>
<script>
import {mapGetters,mapActions} from 'vuex'
import vueGetData from "../../Js/vueGetData.js"
import {Breadcrumb, BreadcrumbItem,Input} from 'element-ui'
import Business from "../dialogs/Business.vue"

	export default{
		name:'home',
		data () {
			return {
				tabName: 'process',
				groupId:0,
				groupName:'',
				processList:[],
				//businessList:[],
				portList:[],
				serviceLines:{},
				serviceLineIndex:0,
				serviceLineId:0,
				processAddData:{
					name:'',
					value:'',
					monitor:false
				},
				portAddData:{
					name:'',
					value:'',
				},
				processUpdateData:[],
				portUpdateData:[],
				// editGetFormData:{},	//编辑时数据
				canWrite:false,//是否有写权限
			}
		},
		computed:mapGetters({
			serviceLineName: "serviceLineName",
			businessList:"businesslist"
		}),
		created () {
			this.$store.dispatch("getServiceLineName")
			this.fetchData();
		},
		watch:{
			'$route': 'fetchData',
			processList:function(){
				this.processUpdateData=[];
				for(let i=0;i<this.processList.length;i++){
					let data = {"edit":false}
					this.processUpdateData.push(data)
				}
			},
			portList:function(){
				this.portUpdateData=[];
				for(let i=0;i<this.portList.length;i++){
					let data = {"edit":false}
					this.portUpdateData.push(data)
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
				this.getMetricList()
			},
			getMetricList:function(){
				let _self=this;
				vueGetData.getData("getgroupconf",{"group_id":this.groupId,"tag":'PROC'},function(jsondata){
		            if(jsondata.body.error_code === 22000){
		                _self.processList = jsondata.body.data;
		            }else{
		                console.log(jsondata.body.msg)
		            }
		        },function(err){
		            console.log(err)
		        })

				this.$store.dispatch("getBusinessList",{"group_id":this.groupId,"tag":'BUSINESS'});

		        vueGetData.getData("getgroupconf",{"group_id":this.groupId,"tag":'PORT'},function(jsondata){
		            if(jsondata.body.error_code === 22000){
		                _self.portList = jsondata.body.data;
		            }else{
		                console.log(jsondata.body.msg)
		            }
		        },function(err){
		            console.log(err)
		        })
			},
			addProcess:function(){
				if(!this.processAddData.name){
					vueGetData.creatTips("请填写采集项名称");
					return false;
				}
				if(!this.processAddData.value){
					vueGetData.creatTips("请填写路径");
					return false;
				}
				let data={}
				data["tag"]='PROC';
				data["name"] = vueGetData.trim(this.processAddData.name);
				data["val"] = vueGetData.trim(this.processAddData.value);
				if(this.processAddData.monitor){
					data["proc_deeply_path"] = vueGetData.trim(this.processAddData.value);
				}
				data["group_id"] = this.groupId;

				vueGetData.getData("addgroupconf",data,function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		if(jsondata.body.data ==1){
		        			vueGetData.creatTips("添加采集项成功");
			        		//刷新列表
			        		this.getMetricList()
			        		this.processAddData.name="";
			        		this.processAddData.value="";
			        		this.processAddData.monitor=false;
		        		}else{
		        			vueGetData.creatTips("添加采集项失败,存在重复采集项");
		        		}
		        	}else if(jsondata.body.error_code === 22005){
		        		vueGetData.creatTips("添加采集项失败,参数错误");
		        	}else if(jsondata.body.error_code === 22003){
		        		vueGetData.creatTips("采集项重名，请修改后重试");
		        	}
		        }.bind(this),function(){

		        }.bind(this));
			},
			addPort:function(){
				if(!this.portAddData.name){
					vueGetData.creatTips("请填写采集项名称");
					return false;
				}
				if(!this.portAddData.value){
					vueGetData.creatTips("请填写端口号");
					return false;
				}
				let data = {}
				data["tag"]='PORT';

				data["name"] = vueGetData.trim(this.portAddData.name);
				data["val"] = vueGetData.trim(this.portAddData.value);
				data["group_id"] = this.groupId;

				vueGetData.getData("addgroupconf",data,function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		if(jsondata.body.data ==1){
		        			vueGetData.creatTips("添加采集项成功");
			        		//刷新列表
			        		this.getMetricList()
			        		this.portAddData.name=""
			        		this.portAddData.value=""
		        		}else{
		        			vueGetData.creatTips("添加采集项失败,存在重复采集项");
		        		}
		        	}else if(jsondata.body.error_code === 22005){
		        		vueGetData.creatTips("添加采集项失败,参数错误");
		        	}else if(jsondata.body.error_code === 22003){
		        		vueGetData.creatTips("采集项重名，请修改后重试");
		        	}
		        }.bind(this),function(){

		        }.bind(this));
			},
			updateProcess:function(item,index){
				if(!this.processUpdateData[index].edit){
					this.processUpdateData[index].edit=!this.processUpdateData[index].edit;
				}else{
					let data={};
					data["group_id"] = this.groupId;
					if(!item.proc_path){
						vueGetData.creatTips("请填写路径");
						return false;
					}
					data["tag"]='PROC';
					data["name"]=item.item_name_prefix;
					data["val"]=vueGetData.trim(item.proc_path);

					vueGetData.getData("setgroupconf",data,function(jsondata){

			        	if(jsondata.body.error_code == 22000){
			        		if(jsondata.body.data == 1){
			        			vueGetData.creatTips("修改采集项成功");
				        		this.getMetricList();
			        		}else{
			        			vueGetData.creatTips("修改采集项数据未发生变化");
			        		}
			        		this.processUpdateData[index].edit=!this.processUpdateData[index].edit;
			        	}else if(jsondata.body.error_code == 22005){
			        		vueGetData.creatTips("参数错误");
			        	}
			        }.bind(this),function(){

			        }.bind(this));
				}
			},
			updatePort:function(item,index){
				if(!this.portUpdateData[index].edit){
					this.portUpdateData[index].edit=!this.portUpdateData[index].edit;
				}else{
					let data={};
					data["group_id"] = this.groupId;
					if(!item.port_num){
						vueGetData.creatTips("请填写端口号");
						return false;
					}
					data["tag"]='PORT';
					data["name"]=item.item_name_prefix;
					data["val"]=vueGetData.trim(item.port_num);

					vueGetData.getData("setgroupconf",data,function(jsondata){
			        	if(jsondata.body.error_code == 22000){
			        		if(jsondata.body.data == 1){
			        			vueGetData.creatTips("修改采集项成功");
				        		this.getMetricList();
			        		}else{
			        			vueGetData.creatTips("修改采集项数据未发生变化");
			        		}
			        		this.portUpdateData[index].edit=!this.portUpdateData[index].edit;
			        	}else if(jsondata.body.error_code == 22005){
			        		vueGetData.creatTips("参数错误");
			        	}
			        }.bind(this),function(){

			        }.bind(this));
				}
			},
			deleteProcess:function(item){
				let data={};
				data["group_id"] = this.groupId;
				data["name"] = item.item_name_prefix;
				data["tag"]='PROC';

				let _self = this;

				let str = '<div class="popCreat" id="deleteLogBox">'
		            +'<h3>确定要删除吗？</h3>'
		            +'<div class="btns"><span class="surebtn" id="surebtn">确定</span><span class="cancelbtn" id="cancelbtn">取消</span></div></div>';
				vueGetData.creatPop(str);

				let surebtn = document.getElementById("surebtn");
				let cancelbtn = document.getElementById("cancelbtn");

		        surebtn.onclick = function(){
					vueGetData.getData("deletegroupconf",data,function(jsondata){
			        	if(jsondata.body.error_code == 22000){
			        		vueGetData.creatTips("删除采集项成功");
							_self.getMetricList()
			        	}else{
			        		vueGetData.creatTips("删除采集项失败");
				        }
			        }.bind(this),function(){

			        }.bind(this));
					vueGetData.closePop();
		        }
		        cancelbtn.onclick = function(){
		        	vueGetData.closePop();
		        }
			},
			deleteBusiness:function(item){
				let data={};
				data["group_id"] = this.groupId;
				data["name"] = item.item_name_prefix;
				data["tag"]='BUSINESS';
				data["val"]=item.match_str;
				data["log_path"]=item.log_path;
				data["log_style"]=item.log_style;

				let _self = this;

				let str = '<div class="popCreat" id="deleteLogBox">'
		            +'<h3>确定要删除吗？</h3>'
		            +'<div class="btns"><span class="surebtn" id="surebtn">确定</span><span class="cancelbtn" id="cancelbtn">取消</span></div></div>';
				vueGetData.creatPop(str);

				let surebtn = document.getElementById("surebtn");
				let cancelbtn = document.getElementById("cancelbtn");

		        surebtn.onclick = function(){
					vueGetData.getData("deletegroupconf",data,function(jsondata){
			        	if(jsondata.body.error_code == 22000){
			        		vueGetData.creatTips("删除采集项成功");
							_self.getMetricList()
			        	}else{
			        		vueGetData.creatTips("删除采集项失败");
				        }
			        }.bind(this),function(){

			        }.bind(this));
					vueGetData.closePop();
		        }
		        cancelbtn.onclick = function(){
		        	vueGetData.closePop();
		        }
			},
			deletePort:function(item){
				let data={};
				data["group_id"] = this.groupId;
				data["name"] = item.item_name_prefix;
				data["tag"]='PORT';

				let _self = this;

				let str = '<div class="popCreat" id="deleteLogBox">'
		            +'<h3>确定要删除吗？</h3>'
		            +'<div class="btns"><span class="surebtn" id="surebtn">确定</span><span class="cancelbtn" id="cancelbtn">取消</span></div></div>';
				vueGetData.creatPop(str);

				let surebtn = document.getElementById("surebtn");
				let cancelbtn = document.getElementById("cancelbtn");

		        surebtn.onclick = function(){
					vueGetData.getData("deletegroupconf",data,function(jsondata){
			        	if(jsondata.body.error_code == 22000){
			        		vueGetData.creatTips("删除采集项成功");
							_self.getMetricList()
			        	}else{
			        		vueGetData.creatTips("删除采集项失败");
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
				document.getElementsByClassName("dialog")[0].style.display = "block";
			},
			editBusiness:function(item){
				this.$store.dispatch('pushBusinessData',item);

				this.showDialog();
			}
		},
		components: {
			Business
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
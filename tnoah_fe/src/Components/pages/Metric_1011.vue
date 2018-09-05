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
			<el-tabs v-model="metricName" @tab-click="handleClick">
				<el-tab-pane label="进程类" name="process">
					<div class="operationBox">
						名称
						<input type="text" id="item-name" class="addInput" v-model="addData.name">
						路径
						<input type="text" id="item-addition" class="addInput" style="width:260px;" v-model="addData.value">
						<i class="el-icon-plus" @click="addMetric"></i>
					</div>
			    </el-tab-pane>
			    <el-tab-pane label="业务类" name="business">
			    	<div class="operationBox">
			    		<span class="addOperation">
			    			<label class="addTitle">名称</label>
							<input type="text" id="item-name" class="addInput" v-model="addData.name">
			    		</span>
						<span class="addOperation">
			    			<label class="addTitle">匹配方式</label>
							<label><input type="radio" name="match" value="match_str" v-model="addData.match_type" id="match0"><span for="match0">match_str (正则匹配)</span></label>
							<label><input type="radio" name="match" value="awk_str" v-model="addData.match_type" id="match1"><span for="match1">awk_str (列匹配)</span></label>
			    		</span>
			    		<span class="addOperation">
							<label class="addTitle">匹配内容</label>
							<input type="text" id="item-addition" class="addInput" v-model="addData.value">
						</span>
					</div>
					<div class="operationBox">
						<span class="addOperation">
							<label class="addTitle">日志路径</label>
							<input type="text" id="item-addition" class="addInput" v-model="addData.log_path">
						</span>
						<span class="addOperation">
							<label class="addTitle">日志风格</label>
							<label><input type="radio" name="style" value="newly" v-model="addData.log_style" id="style0"><span for="style0">newly (整点新建)</span></label>
							<label><input type="radio" name="style" value="split" v-model="addData.log_style" id="style1"><span for="style1">split (整点覆盖)</span></label>
						</span>
						<span class="addOperation" v-show="addData.log_style=='newly'">
							<label class="addTitle">时间格式</label>
							<input type="text" id="item-addition" class="addInput" v-model="addData.log_format">
						</span>
						<button class="white-button" @click="addMetric"><span></span>添加采集项</button>
					</div>
			    </el-tab-pane>
			    <el-tab-pane label="端口类" name="port">
			    	<div class="operationBox">
						名称
						<input type="text" id="item-name" class="addInput" v-model="addData.name">
						端口号
						<input type="text" id="item-addition" class="addInput" v-model="addData.value">
						<i class="el-icon-plus" @click="addMetric"></i>
					</div>
			    </el-tab-pane>
	  		</el-tabs>
		</div>
		<div class="configList">
			<div class="table-div">
				<table class="manage-table" v-if="metricName=='process'">
					<thead>
						<tr>
							<th width="10%"><input type="checkbox" name="check-all" disabled="disabled"></th>
							<th width="30%">名称</th>
							<th width="40%">路径</th>
							<th width="20%">操作</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item,index) in metricList">
							<td><input type="checkbox" disabled="disabled"></td>
							<td>{{item.item_name_prefix}}</td>
							<td>
								<template>
									<el-input v-show="updateData[index].edit" v-model="item.proc_path"></el-input>
									<span v-show="!updateData[index].edit">{{item.proc_path}}</span>
								</template>
							</td>
							<td>
								<a href="javascript:;" :data-id="item.id" :data-index="index" @click="updateMetric(item,index)">{{updateData[index].edit?'完成':'编辑'}}</a>
								<a href="javascript:;" :data-id="item.id" :data-index="index" @click="deleteMetric(item)">删除</a>
							</td>
						</tr>
					</tbody>
				</table>
				<table class="manage-table" v-else-if="metricName=='business'">
					<thead>
						<tr>
							<th width="3%"><input type="checkbox" name="check-all" disabled="disabled"></th>
							<th width="17%">名称</th>
							<th width="22%">日志路径</th>
							<th width="8%">日志风格</th>
							<th width="10%">时间格式</th>
							<th width="10%">匹配方式</th>
							<th width="20%">匹配内容</th>
							<th width="10%">操作</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item,index) in metricList">
							<td><input type="checkbox" disabled="disabled"></td>
							<td>{{item.item_name_prefix}}</td>
							<td>
								<template>
									<el-input v-show="updateData[index].edit" v-model="item.log_path"></el-input>
									<span v-show="!updateData[index].edit">{{item.log_path}}</span>
								</template>
							</td>
							<td>
								<template>
									<el-input v-show="updateData[index].edit" v-model="item.log_style"></el-input>
									<span v-show="!updateData[index].edit">{{item.log_style}}</span>
								</template>
							</td>
							<td>
								<template>
									<el-input v-show="updateData[index].edit" v-model="item.log_format"></el-input>
									<span v-show="!updateData[index].edit">{{item.log_format}}</span>
								</template>
							</td>
							<td>
								<template v-if="item.match_str">match_str
								</template>
								<template v-if="item.awk_str">awk_str
								</template>
							</td>
							<td>
								<template>
									<el-input v-show="updateData[index].edit" v-model="item.match_str"></el-input>
									<span v-show="!updateData[index].edit">{{item.match_str}}{{item.awk_str}}</span>
								</template>
							</td>
							<td>
								<a href="javascript:;" :data-id="item.id" :data-index="index" @click="updateMetric(item,index)">{{updateData[index].edit?'完成':'编辑'}}</a>
								<a href="javascript:;" :data-id="item.id" :data-index="index" @click="deleteMetric(item)">删除</a>
							</td>
						</tr>
					</tbody>
				</table>
				<table class="manage-table" v-else>
					<thead>
						<tr>
							<th width="10%"><input type="checkbox" name="check-all" disabled="disabled"></th>
							<th width="40%">名称</th>
							<th width="30%">端口号</th>
							<th width="20%">操作</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item,index) in metricList">
							<td><input type="checkbox" disabled="disabled"></td>
							<!-- <td>{{item.ip_addr}}</td> -->
							<!-- <td>{{item.name}}</td> -->
							<td>{{item.item_name_prefix}}</td>
							<td>
								<template>
									<el-input v-show="updateData[index].edit" v-model="item.port_num"></el-input>
									<span v-show="!updateData[index].edit">{{item.port_num}}</span>
								</template>
							</td>
							<td>
								<a href="javascript:;" :data-id="item.id" :data-index="index" @click="updateMetric(item,index)">{{updateData[index].edit?'完成':'编辑'}}</a>
								<a href="javascript:;" :data-id="item.id" :data-index="index" @click="deleteMetric(item)">删除</a>
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
				metricName: 'process',
				groupId:0,
				groupName:'',
				metricList:[],
				serviceLines:{},
				serviceLineIndex:0,
				serviceLineId:0,
				addData:{
					name:'',
					value:'',
					log_path:'',
					match_type:'match_str',
					log_style:'newly',
					log_format:''
				},
				searchData:{
					name:''
				},
				updateData:[]
			}
		},
		computed:mapGetters({
			// groupList: "grouplist",
			// metricList:"metricList"
		}),
		created () {
			this.fetchData();
		},
		watch:{
			'$route': 'fetchData',
			metricList:function(){
				let len = this.metricList.length;
				this.updateData=[];
				for(let i=0;i<len;i++){
					let data = {"edit":false}
					this.updateData.push(data)
				}
			}
		},
		methods: {
			handleClick(tab, event) {
				this.getMetricList();
				this.clearAddData();
      		},
      		clearAddData(){
      			for(var item in this.addData){
      				if(item=='match_type'){
      					this.addData[item]='match_str'
      				}else if(item=='log_style'){
      					this.addData[item]='newly'
      				}else{
      					this.addData[item]=''
      				}
				}
      		},
			fetchData(){
				//获取路由id
				this.groupId = this.$route.params.groupid;
				//更新列表
				this.getMetricList()
			},
			getMetricList:function(){
				let data={};
				data["group_id"] = this.groupId;
				if(this.metricName == 'process'){
					data["tag"] = 'PROC';
				}else if(this.metricName == 'business'){
					data["tag"] = 'BUSINESS';
				}else if(this.metricName == 'port'){
					data["tag"] = 'PORT';
				}
				vueGetData.getData("getgroupconf",data,function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		this.metricList = jsondata.body.data;
		        	}else{
			        	console.log(jsondata.body.msg)
			        }
		        }.bind(this),function(){

		        }.bind(this));
			},
			addMetric:function(){
				if(!this.addData.name){
					vueGetData.creatTips("请填写采集项名称");
					return false;
				}
				let data={}
				if(this.metricName=='process'){
					if(!this.addData.value){
						vueGetData.creatTips("请填写路径");
						return false;
					}
					data["tag"]='PROC';
				}else if(this.metricName=='business'){
					if(!this.addData.value){
						vueGetData.creatTips("请填写匹配内容");
						return false;
					}
					if(!this.addData.log_path){
						vueGetData.creatTips("请填写日志路径");
						return false;
					}
					if(this.addData.log_style=='newly'){
						if(!this.addData.log_format){
							vueGetData.creatTips("请填写时间格式");
							return false;
						}
						data["log_format"] = this.addData.log_format;
					}
					data["tag"]='BUSINESS';
					data["log_path"] = vueGetData.trim(this.addData.log_path);
					data["log_style"] = this.addData.log_style;
					data["match_type"] = this.addData.match_type;
				}else{
					if(!this.addData.name){
						vueGetData.creatTips("请填写端口号");
						return false;
					}
					data["tag"]='PORT';
				}
				data["name"] = vueGetData.trim(this.addData.name);
				data["val"] = vueGetData.trim(this.addData.value);
				data["group_id"] = this.groupId;

				vueGetData.getData("addgroupconf",data,function(jsondata){
					console.log(jsondata);
		        	if(jsondata.body.error_code === 22000){
		        		if(jsondata.body.data ==1){
		        			vueGetData.creatTips("添加采集项成功");
		        			this.clearAddData();
			        		//刷新列表
			        		this.getMetricList()
		        		}else{
		        			vueGetData.creatTips("添加采集项失败,存在重复采集项");
		        		}
		        	}else if(jsondata.body.error_code === 22005){
		        		vueGetData.creatTips("添加采集项失败,参数错误");
		        	}
		        }.bind(this),function(){

		        }.bind(this));
			},
			updateMetric:function(item,index){
				if(!this.updateData[index].edit){
					this.updateData[index].edit=!this.updateData[index].edit;
				}else{
					console.log("ooooooooooooooooooo")
					console.log(item)

					let data={};
					data["group_id"] = this.groupId;
					if(this.metricName=='process'){
						if(!item.proc_path){
							vueGetData.creatTips("请填写路径");
							return false;
						}
						data["tag"]='PROC';
						data["name"]=item.item_name_prefix;
						data["val"]=vueGetData.trim(item.proc_path);
					}else if(this.metricName=='business'){
						
					}else{
						if(!item.port_num){
							vueGetData.creatTips("请填写端口号");
							return false;
						}
						data["tag"]='PORT';
						data["name"]=item.item_name_prefix;
						data["val"]=vueGetData.trim(item.port_num);
					}

					vueGetData.getData("setgroupconf",data,function(jsondata){
						console.log(jsondata);
			        	if(jsondata.body.error_code == 22000){
			        		console.log(this.groupList)
			        		if(jsondata.body.data == 1){
			        			vueGetData.creatTips("修改采集项成功");
				        		this.getMetricList();
			        		}else{
			        			vueGetData.creatTips("修改采集项数据未发生变化");
			        		}
			        		this.updateData[index].edit=!this.updateData[index].edit;
			        	}else if(jsondata.body.error_code == 22005){
			        		vueGetData.creatTips("参数错误");
			        	}
			        }.bind(this),function(){

			        }.bind(this));
				}
			},
			deleteMetric:function(item){
				console.log("============")
				console.log(item)
				let data={};
				data["group_id"] = this.groupId;
				data["name"] = item.item_name_prefix;

				if(this.metricName=='process'){
					data["tag"]='PROC';
				}else if(this.metricName=='business'){
					data["tag"]='BUSINESS';
					data["val"]=item.match_str;
					data["log_path"]=item.log_path;
					data["log_style"]=item.log_style;
				}else{
					data["tag"]='PORT';
				}

				vueGetData.getData("deletegroupconf",data,function(jsondata){
					console.log(jsondata);
		        	if(jsondata.body.error_code == 22000){
		        		vueGetData.creatTips("删除采集项成功");
						this.getMetricList()
		        	}else{
		        		vueGetData.creatTips("删除采集项失败");
			        }
		        }.bind(this),function(){

		        }.bind(this));
			},
		},
		components: {
			'el-breadcrumb': Breadcrumb,
			'el-breadcrumb-item': BreadcrumbItem,
			'el-input':Input
		}
	}
</script>
<style lang="less" >
@import "../../Css/rightside.less";

.metrichome {
	width: 100%;
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
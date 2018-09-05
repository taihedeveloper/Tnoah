<template>
	<div class="strategyhome">
		<div class="navigation">
			<el-breadcrumb separator="/">
				<el-breadcrumb-item :to="{ path: '/' }">首页</el-breadcrumb-item>
				<el-breadcrumb-item :to="{ path: '/group' }">集群配置 {{groupName}}</el-breadcrumb-item>
				<el-breadcrumb-item :to="{ path: '/group/'+groupId+'/template/'}">模板配置</el-breadcrumb-item>
				<el-breadcrumb-item>策略配置</el-breadcrumb-item>
			</el-breadcrumb>
		</div>
		<div class="operation">
			<div class="button-area" style="display:block">
				<button class="add-blue-button" @click="showDialog" v-if="canWrite"><span></span>新建策略</button>
				<button class="add-gray-button" @click="noPermission" v-else><span></span>新建策略</button>
			</div>
		</div>
		<div class="configList">
			<div class="table-div">
				<table class="manage-table">
					<thead>
						<tr>
							<th width="3%"><input type="checkbox" name="check-all" disabled="disabled"></th>
							<th width="6%">策略id</th>
							<th width="5%">tpl_id</th>
							<th width="15%">采集项</th>
							<!-- <th width="10%">tags</th> -->
							<th width="7%">报警级别</th>
							<th width="9%">最大报警次数</th>
							<th width="15%">报警策略</th>
							<th width="15%">备注</th>
							<th width="15%">报警生效时间</th>
							<th width="10%">操作</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item,index) in strategyList">
							<td><input type="checkbox" name="check-one" disabled="disabled"></td>
							<td>{{item.id}}</td>
							<td>{{item.tpl_id}}</td>
							<td>{{item.metric}}</td>
							<!-- <td>{{item.tags}}</td> -->
							<td>{{item.priority}}</td>
							<td>{{item.max_step}}</td>
							<td>{{item.func}} {{item.op}} {{item.right_value}}</td>
							<td>{{item.note}}</td>
							<td>{{item.run_begin}}-{{item.run_end}}</td>
							<td>
								<template v-if="item.metric=='BUS_Tagent_Heartbeat' || !canWrite">
									<a href="javascript:;" :data-id="item.id" :data-index="index" @click="editStrategy(item,false)">查看</a>
									<a href="javascript:;" style="color:#c5c5c5"  @click="noPermission">删除</a>
								</template>
								<template v-else>
									<a href="javascript:;" :data-id="item.id" :data-index="index" @click="editStrategy(item,true)">编辑</a>
									<a href="javascript:;" :data-id="item.id" :data-index="index" @click="deleteStrategy(item.id)">删除</a>
								</template>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<Strategy :editinitformdata="editGetFormData"></Strategy>
	</div>

</template>
<script>
import {mapGetters,mapActions} from 'vuex'
import vueGetData from "../../Js/vueGetData.js"
import {Breadcrumb, BreadcrumbItem,Input} from 'element-ui'
import Strategy from "../dialogs/Strategy.vue"

	export default{
		name:'home',
		data () {
			return {
				groupId:0,
				groupName:'',
				templateId:0,
				editGetFormData:{},	//编辑时数据
				canWrite:false,
			}
		},
		computed:mapGetters({
			strategyList:"strategylist",
			serviceLineName: "serviceLineName",
		}),
		created () {
			this.$store.dispatch("getServiceLineName");
			this.fetchData();
		},
		watch:{
			'$route': 'fetchData',
			strategyList:function(){
				let len = this.strategyList.length;
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
				this.templateId = this.$route.params.templateid;
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
				this.getStrategyList()
			},
			getStrategyList:function(){
				this.$store.dispatch("getStrategyList",{"tpl_id":this.templateId})
			},
			deleteStrategy:function(id){
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
					vueGetData.getData("deletestrategy",data,function(jsondata){
			        	if(jsondata.body.data == 1){
			        		vueGetData.creatTips("删除策略成功");
							_self.getStrategyList()
			        	}else{
			        		vueGetData.creatTips("删除策略失败");
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
			editStrategy:function(item,canWrite){
				item["canWrite"] = canWrite;
				this.editGetFormData = item;
				this.showDialog();
			}
		},
		components: {
			// 'el-breadcrumb': Breadcrumb,
			// 'el-breadcrumb-item': BreadcrumbItem,
			// 'el-input':Input
			Strategy
		}
	}
</script>
<style lang="less" >
@import "../../Css/rightside.less";

.strategyhome {
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
		position:absolute;
		left:230px;
		top:28px;
		cursor:pointer;
	}
	.currentItem{
		font-size:20px ;
		font-weight:dold;
		padding-bottom:10px;
	}
	.operation{
		padding: 5px 20px;
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
}
</style>
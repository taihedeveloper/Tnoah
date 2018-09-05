<template>
	<div class="home">
		<SideLeft></SideLeft>
		<div name="rightside">
			<div class="topbox">
				<div class="selectbox" style="float:left">
					<div class="title">机器
					</div>
					<div class="content">
						<div class="contentHead clearfix">
							<input type="text" id="searchMechine" v-model="mechineSearch"><span id="searchM" @click="searchIp"></span>
							<ul>
								<li @click="checkten()">+10</li>
								<li @click="checkall()">全选</li>
								<li @click="uncheckips()">取消</li>
								<li>{{selectIps.length}}/{{mechineTable.length}}</li>
							</ul>
						</div>
						<div class="contentTable">
							<table >
								<tbody>
									<tr v-for="(value,index) in mechineTable">
										<td style="width:2%"><input type="checkbox" name="check-ip" v-on:click="getParams()"></td>
										<td  style="width:10%">{{value.ip_addr}}</td>
										<td  style="width:20%">{{value.name}}</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<div class="selectbox" style="float:right">
					<div class="title">监控项
						<input type="text" id="searchItem" v-model="metricSearch"><span id="searchI" @click="searchItem"></span>
					</div>
					<div class="content">
						<div class="contentHead clearfix">
							<ul>
								<li id="CPU" @click="getCpu()" v-show="cpuSearch">CPU</li>
								<li id="MEM" @click="getMem()" v-show="memSearch">内存</li>
								<li id="DISK" @click="getDisk()" v-show="diskSearch">磁盘空间</li>
								<li id="DISK_IO" @click="getDiskIo()" v-show="diskioSearch">磁盘IO</li>
								<li id="NET" @click="getNet()" v-show="netSearch">网卡IO</li>
								<li id="PROCESS" @click="getProcess()" v-show="processSearch">进程</li>
								<li id="BUSINESS" @click="getBusiness()" v-show="businessSearch">业务</li>
								<li id="PORT" @click="getPort()" v-show="portSearch">端口</li>
							</ul>
						</div>
						<div class="contentTable" v-show="currentItem=='CPU'">
							<table >
								<tbody>
									<tr v-for="(value,index) in cpuItemTable">
										<td style="width:2%"><input type="checkbox"  name="check-item" v-on:click="getParams()"></td>
										<td  style="width:10%">{{value.name}}</td>
										<td  style="width:20%">{{value.addi}}</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="contentTable" v-show="currentItem=='MEM'">
							<table >
								<tbody>
									<tr v-for="(value,index) in memItemTable">
										<td style="width:2%"><input type="checkbox"  name="check-item" v-on:click="getParams()"></td>
										<td  style="width:10%">{{value.name}}</td>
										<td  style="width:20%">{{value.addi}}</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="contentTable" v-show="currentItem=='DISK'">
							<table >
								<tbody>
									<tr v-for="(value,index) in diskItemTable">
										<td style="width:2%"><input type="checkbox"  name="check-item" v-on:click="getParams()"></td>
										<td  style="width:10%">{{value.name}}</td>
										<td  style="width:20%">{{value.addi}}</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="contentTable" v-show="currentItem=='DISK_IO'">
							<table >
								<tbody>
									<tr v-for="(value,index) in diskioItemTable">
										<td style="width:2%"><input type="checkbox"  name="check-item" v-on:click="getParams()"></td>
										<td  style="width:10%">{{value.name}}</td>
										<td  style="width:20%">{{value.addi}}</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="contentTable" v-show="currentItem=='NET'">
							<table >
								<tbody>
									<tr v-for="(value,index) in netItemTable">
										<td style="width:2%"><input type="checkbox"  name="check-item" v-on:click="getParams()"></td>
										<td  style="width:10%">{{value.name}}</td>
										<td  style="width:20%">{{value.addi}}</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="contentTable" v-show="currentItem=='PROCESS'">
							<table >
								<tbody>
									<tr v-for="(value,index) in processItemTable">
										<td style="width:2%"><input type="checkbox"  name="check-item" v-on:click="getParams()"></td>
										<td  style="width:10%">{{value.name}}</td>
										<td  style="width:20%">{{value.addi}}</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="contentTable" v-show="currentItem=='BUSINESS'">
							<table >
								<tbody>
									<tr v-for="(value,index) in businessItemTable">
										<td style="width:2%"><input type="checkbox"  name="check-item" v-on:click="getParams()"></td>
										<td  style="width:10%">{{value.name}}</td>
										<td  style="width:20%">{{value.addi}}</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="contentTable" v-show="currentItem=='PORT'">
							<table >
								<tbody>
									<tr v-for="(value,index) in portItemTable">
										<td style="width:2%"><input type="checkbox"  name="check-item" v-on:click="getParams()"></td>
										<td  style="width:10%">{{value.name}}</td>
										<td  style="width:20%">{{value.addi}}</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div class="graphbox" style="clear:both;">
				<div class="graphHead">
					<ul>
						<li>趋势图</li>
						<li>
							<el-date-picker v-model="value1" type="datetime" placeholder="开始时间" @change="pickStartTime" >
	    					</el-date-picker>
						</li>
						-
						<li>
							<el-date-picker v-model="value2" type="datetime" placeholder="结束时间" @change="pickEndTime">
	    					</el-date-picker>
						</li>
						<li id="oneHour" @click="setOneHour" class="cur">1小时</li>
						<li id="oneDay" @click="setOneDay">1天</li>
						<li id="threeDay" @click="setThreeDay">3天</li>
						<li id="sevenDay" @click="setSevenDay">7天</li>
						<li><button class="button" @click="sureClick()">确定</button></li>
						<li><button class="button" @click="setOneHour()">取消</button></li>
					</ul>
				</div>
			</div>
			<div v-for="(index,item) in graphList" id="gBox">
				<div class="graph" :id="'graphbox'+item">
					<div class="graphClose" @click="closeGraph(item)"></div>
					<div :id="'graph'+item" class="echartsGraph">
					</div>
				</div>
			</div>
		</div>
	</div>

</template>
<script>
import {mapGetters,mapActions} from 'vuex'
import {DatePicker} from 'element-ui'
import vueGetData from "../../Js/vueGetData.js"
import echarts from "../../Js/plugins/echarts.js"
import SideLeft from '../SideLeft.vue'

	export default{
		name:'home',
		data () {
			return {
				mechineTable: [],
				copyMechineTable:[],
				mechineSearch:'',//搜索机器的ip or 名字
				metricSearch:'',//搜索监控项
				monitorItems:{},//存储某个机器下的所有监控项
				currentItem:'CPU',//存储某个机器下的当前监控tag
				cpuItemTable: [],//cpu tag下的监控项
				copyCpuItemTable: [],//cpu tag下的监控项
				cpuSearch:true,
				memItemTable: [],//mem tag下的监控项
				copyMemItemTable: [],//mem tag下的监控项
				memSearch:true,
				diskItemTable: [],//disk tag下的监控项
				copyDiskItemTable: [],//disk tag下的监控项
				diskSearch:true,
				diskioItemTable: [],//diskio tag下的监控项
				copyDiskioItemTable: [],//diskio tag下的监控项
				diskioSearch:true,
				netItemTable: [],//net tag下的监控项
				copyNetItemTable: [],//net tag下的监控项
				netSearch:true,
				processItemTable: [],//process tag下的监控项
				copyProcessItemTable: [],//process tag下的监控项
				processSearch:true,
				businessItemTable: [],//business tag下的监控项
				copyBusinessItemTable: [],//business tag下的监控项
				businessSearch:true,
				portItemTable: [],//port tag下的监控项
				copyPortItemTable: [],//port tag下的监控项
				portSearch:true,
				value1:'',//开始时间的日期形式
				value2:'',//结束时间的日期形式
				startTime:'',//开始时间的时间戳形式
				endTime:'',//结束时间的时间戳形式
				currentTime:'oneHour',//存储当前时间快捷方式
				selectIps:[],
				selectItems:[],
				params:{
				},
				graphList:[]
			}
		},
		computed:mapGetters({
			treeId:"treeId",
		}),
		watch:{
			treeId: function(){
				//取消已选项
				this.uncheckips();
				this.mechineSearch="";
				this.metricSearch="";

				var items=document.getElementsByName("check-item");
                var len=items.length;
                if(len>0){
                	for(var i=0;i<len;i++){
                		items[i].checked=false;
                	}
                }
				let data = {"group_id":this.treeId}
				vueGetData.getData("getmachineinfos",data,function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		this.mechineTable = jsondata.body.data;
		        		this.copyMechineTable = jsondata.body.data;
		        	}else{
		        		this.mechineTable = [];
		        		this.copyMechineTable = [];
		        	}
		        }.bind(this),function(){

		        }.bind(this));
				//获取采集项
		        vueGetData.getData("getgroupmetricsname",data,function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		let json = jsondata.body.data;
		        		for(var item in json){
		        			this.cpuSearch = true;
		        			this.memSearch = true;
		        			this.diskSearch = true;
		        			this.diskioSearch = true;
		        			this.netSearch = true;
		        			this.netSearch = true;
		        			this.businessSearch = true;
		        			this.portSearch = true;
		        			if(item == 'CPU'){
		        				this.cpuItemTable = json[item];
		        				this.copyCpuItemTable = json[item];
		        			}else if(item == 'MEM'){
		        				this.memItemTable = json[item];
		        				this.copyMemItemTable = json[item];
		        			}else if(item == 'DISK'){
		        				this.diskItemTable = json[item];
		        				this.copyDiskItemTable = json[item];
		        			}else if(item == 'DISK_IO'){
		        				this.diskioItemTable = json[item];
		        				this.copyDiskioItemTable = json[item];
		        			}else if(item == 'NET'){
		        				this.netItemTable = json[item];
		        				this.copyNetItemTable = json[item];
		        			}else if(item == 'PROCESS'){
		        				this.processItemTable = json[item];
		        				this.copyProcessItemTable = json[item];
		        			}else if(item == 'BUSINESS'){
		        				this.businessItemTable = json[item];
		        				this.copyBusinessItemTable = json[item];
		        			}else if(item == 'PORT'){
		        				this.portItemTable = json[item];
		        				this.copyPortItemTable = json[item];
		        			}
	        			}

		        		//-------------------------------------
	        			// for(var item in json){
	        			// 	this.monitorItems[item]=json[item];
	        			// }
	        			this.currentItem='CPU';
	        			// this.monitorItemTable=json[this.currentItem]
	        			document.getElementById(this.currentItem).setAttribute("class","cur")
	        			//-------------------------------------
		        	}
		        }.bind(this),function(){

		        }.bind(this));
			},
		},
		methods: {
			searchIp:function(){
				if(!this.treeId){
					vueGetData.creatTips("请选择集群");
					return false;
				}
				this.uncheckips()
				this.mechineSearch = vueGetData.trim(this.mechineSearch)
				if(this.mechineSearch==''){
					this.mechineTable = this.copyMechineTable;
				}else{
					var tempTable = [];
					for(let i=0;i<this.copyMechineTable.length;i++){
						if(this.copyMechineTable[i]["ip_addr"].indexOf(this.mechineSearch)>=0 || this.copyMechineTable[i]["name"].indexOf(this.mechineSearch)>=0){
							tempTable.push(this.copyMechineTable[i])
						}
					}
					this.mechineTable=tempTable;
				}
			},
			searchItem:function(){
				if(!this.treeId){
					vueGetData.creatTips("请选择集群");
					return false;
				}
				var items=document.getElementsByName("check-item");
                var len=items.length;
                if(len>0){
                	for(var i=0;i<len;i++){
                		items[i].checked=false;
                	}
                }
				this.metricSearch = vueGetData.trim(this.metricSearch)
				if(this.metricSearch==''){
					document.getElementById(this.currentItem).setAttribute("class","")
					this.currentItem='CPU';
					document.getElementById(this.currentItem).setAttribute("class","cur")
					this.cpuItemTable = this.copyCpuItemTable;
					this.cpuSearch = true;
					this.memItemTable = this.copyMemItemTable;
					this.memSearch = true;
					this.diskItemTable = this.copyDiskItemTable;
					this.diskSearch = true;
					this.diskioItemTable = this.copyDiskioItemTable;
					this.diskioSearch = true;
					this.netItemTable = this.copyNetItemTable;
					this.netSearch = true;
					this.processItemTable = this.copyProcessItemTable;
					this.processSearch = true;
					this.businessItemTable = this.copyBusinessItemTable;
					this.businessSearch = true;
					this.portItemTable = this.copyPortItemTable;
					this.portSearch = true;
				}else{
					if(this.currentItem!=''){
						document.getElementById(this.currentItem).setAttribute("class","")
					}
					let tempItem = false;
					this.currentItem='';
					let tempTable = [];
					for(let i=0;i<this.copyPortItemTable.length;i++){
						if(this.copyPortItemTable[i]["name"].toLowerCase().indexOf(this.metricSearch.toLowerCase())>=0){
							tempTable.push(this.copyPortItemTable[i])
						}
					}
					if(tempTable.length>0){
						this.currentItem='PORT';
						this.portItemTable = tempTable;
						this.portSearch = true;
						tempItem = true;
					}else{
						this.portSearch = false;
					}
					tempTable = [];
					for(let i=0;i<this.copyBusinessItemTable.length;i++){
						if(this.copyBusinessItemTable[i]["name"].toLowerCase().indexOf(this.metricSearch.toLowerCase())>=0){
							tempTable.push(this.copyBusinessItemTable[i])
						}
					}
					if(tempTable.length>0){
						this.currentItem='BUSINESS';
						this.businessItemTable = tempTable;
						this.businessSearch = true;
						tempItem = true;
					}else{
						this.businessSearch = false;
					}
					tempTable = [];
					for(let i=0;i<this.copyProcessItemTable.length;i++){
						if(this.copyProcessItemTable[i]["name"].toLowerCase().indexOf(this.metricSearch.toLowerCase())>=0){
							tempTable.push(this.copyProcessItemTable[i])
						}
					}
					if(tempTable.length>0){
						this.currentItem='PROCESS';
						this.processItemTable = tempTable;
						this.processSearch = true;
						tempItem = true;
					}else{
						this.processSearch = false;
					}
					tempTable = [];
					for(let i=0;i<this.copyNetItemTable.length;i++){
						if(this.copyNetItemTable[i]["name"].toLowerCase().indexOf(this.metricSearch.toLowerCase())>=0){
							tempTable.push(this.copyNetItemTable[i])
						}
					}
					if(tempTable.length>0){
						this.currentItem='NET';
						this.netItemTable = tempTable;
						this.netSearch = true;
						tempItem = true;
					}else{
						this.netSearch = false;
					}
					tempTable = [];
					for(let i=0;i<this.copyDiskioItemTable.length;i++){
						if(this.copyDiskioItemTable[i]["name"].toLowerCase().indexOf(this.metricSearch.toLowerCase())>=0){
							tempTable.push(this.copyDiskioItemTable[i])
						}
					}
					if(tempTable.length>0){
						this.currentItem='DISK_IO';
						this.diskioItemTable = tempTable;
						this.diskioSearch = true;
						tempItem = true;
					}else{
						this.diskioSearch = false;
					}
					tempTable = [];
					for(let i=0;i<this.copyDiskItemTable.length;i++){
						if(this.copyDiskItemTable[i]["name"].toLowerCase().indexOf(this.metricSearch.toLowerCase())>=0){
							tempTable.push(this.copyDiskItemTable[i])
						}
					}
					if(tempTable.length>0){
						this.currentItem='DISK';
						this.diskItemTable = tempTable;
						this.diskSearch = true;
						tempItem = true;
					}else{
						this.diskSearch = false;
					}
					tempTable = [];
					for(let i=0;i<this.copyMemItemTable.length;i++){
						if(this.copyMemItemTable[i]["name"].toLowerCase().indexOf(this.metricSearch.toLowerCase())>=0){
							tempTable.push(this.copyMemItemTable[i])
						}
					}
					if(tempTable.length>0){
						this.currentItem='MEM';
						this.memItemTable = tempTable;
						this.memSearch = true;
						tempItem = true;
					}else{
						this.memSearch = false;
					}
					tempTable = [];
					for(let i=0;i<this.copyCpuItemTable.length;i++){
						if(this.copyCpuItemTable[i]["name"].toLowerCase().indexOf(this.metricSearch.toLowerCase())>=0){
							tempTable.push(this.copyCpuItemTable[i])
						}
					}
					if(tempTable.length>0){
						this.currentItem='CPU';
						this.cpuItemTable = tempTable;
						this.cpuSearch = true;
						tempItem = true;
					}else{
						this.cpuSearch = false;
					}
					if(!tempItem){
						vueGetData.creatTips("该监控项不存在");
						this.currentItem='CPU';
						document.getElementById(this.currentItem).setAttribute("class","cur")
						this.cpuItemTable = [];
						this.cpuSearch = true;
						this.memItemTable = [];
						this.memSearch = true;
						this.diskItemTable = [];
						this.diskSearch = true;
						this.diskioItemTable = [];
						this.diskioSearch = true;
						this.netItemTable = [];
						this.netSearch = true;
						this.processItemTable = [];
						this.processSearch = true;
						this.businessItemTable = [];
						this.businessSearch = true;
						this.portItemTable = [];
						this.portSearch = true;
					}
				}
			},
			checkten:function(){
				var ips=document.getElementsByName("check-ip");
                var len=ips.length;
                if(len>10){
                	for(var i=0;i<10;i++){
                		ips[i].checked=true;
                	}
                }else{
                	for(var i=0;i<len;i++){
                		ips[i].checked=true;
                	}
                }
                this.getParams()
			},
			checkall:function(){
                var ips=document.getElementsByName("check-ip");
                var len=ips.length;
                if(len>0){
                	for(var i=0;i<len;i++){
                		ips[i].checked=true;
                	}
                }
                this.getParams()
			},
			uncheckips:function(){
                var ips=document.getElementsByName("check-ip");
                var len=ips.length;
                if(len>0){
                	for(var i=0;i<len;i++){
                		ips[i].checked=false;
                	}
                }
                this.getParams()
			},
			getCpu:function(){
				document.getElementById(this.currentItem).setAttribute("class","")
				this.currentItem='CPU';
    			// this.monitorItemTable=this.monitorItems[this.currentItem]
    			document.getElementById(this.currentItem).setAttribute("class","cur")
    			// this.uncheckitems();
			},
			getMem:function(){
				document.getElementById(this.currentItem).setAttribute("class","")
				this.currentItem='MEM';
    			// this.monitorItemTable=this.monitorItems[this.currentItem]
    			document.getElementById(this.currentItem).setAttribute("class","cur")
    			// this.uncheckitems();
			},
			getDisk:function(){
				document.getElementById(this.currentItem).setAttribute("class","")
				this.currentItem='DISK';
    			// this.monitorItemTable=this.monitorItems[this.currentItem]
    			document.getElementById(this.currentItem).setAttribute("class","cur")
    			// this.uncheckitems();
			},
			getDiskIo:function(){
				document.getElementById(this.currentItem).setAttribute("class","")
				this.currentItem='DISK_IO';
    			// this.monitorItemTable=this.monitorItems[this.currentItem]
    			document.getElementById(this.currentItem).setAttribute("class","cur")
    			// this.uncheckitems();
			},
			getNet:function(){
				document.getElementById(this.currentItem).setAttribute("class","")
				this.currentItem='NET';
    			// this.monitorItemTable=this.monitorItems[this.currentItem]
    			document.getElementById(this.currentItem).setAttribute("class","cur")
    			// this.uncheckitems();
			},
			getProcess:function(){
				document.getElementById(this.currentItem).setAttribute("class","")
				this.currentItem='PROCESS';
    			// this.monitorItemTable=this.monitorItems[this.currentItem]
    			document.getElementById(this.currentItem).setAttribute("class","cur")
    			// this.uncheckitems();
			},
			getBusiness:function(){
				document.getElementById(this.currentItem).setAttribute("class","")
				this.currentItem='BUSINESS';
    			// this.monitorItemTable=this.monitorItems[this.currentItem]
    			document.getElementById(this.currentItem).setAttribute("class","cur")
    			// this.uncheckitems();
			},
			getPort:function(){
				document.getElementById(this.currentItem).setAttribute("class","")
				this.currentItem='PORT';
    			// this.monitorItemTable=this.monitorItems[this.currentItem]
    			document.getElementById(this.currentItem).setAttribute("class","cur")
    			// this.uncheckitems();
			},
			pickStartTime:function(){
				this.startTime=Date.parse(this.value1)/1000;
			},
			pickEndTime:function(){
				this.endTime=Date.parse(this.value2)/1000;
			},
			setOneHour:function(){
				document.getElementById(this.currentTime).setAttribute("class","")
				this.currentTime='oneHour';
    			document.getElementById(this.currentTime).setAttribute("class","cur")
    			//获取当前时间
				this.endTime = Date.parse(new Date())/1000;
				this.value2 = this.getFormatDate(this.endTime)
				this.startTime = Date.parse(new Date())/1000-3600;
				this.value1 = this.getFormatDate(this.startTime)
				this.getParams();
			},
			setOneDay:function(){
				document.getElementById(this.currentTime).setAttribute("class","")
				this.currentTime='oneDay';
    			document.getElementById(this.currentTime).setAttribute("class","cur")
    			//获取当前时间
				this.endTime = Date.parse(new Date())/1000;
				this.value2 = this.getFormatDate(this.endTime)
				this.startTime = Date.parse(new Date())/1000-86400;
				this.value1 = this.getFormatDate(this.startTime)
				this.getParams();
			},
			setThreeDay:function(){
				document.getElementById(this.currentTime).setAttribute("class","")
				this.currentTime='threeDay';
    			document.getElementById(this.currentTime).setAttribute("class","cur")
    			//获取当前时间
    			this.endTime = Date.parse(new Date())/1000;
				this.value2 = this.getFormatDate(this.endTime)
				this.startTime = Date.parse(new Date())/1000-259200;
				this.value1 = this.getFormatDate(this.startTime)
				this.getParams();
			},
			setSevenDay:function(){
				document.getElementById(this.currentTime).setAttribute("class","")
				this.currentTime='sevenDay';
    			document.getElementById(this.currentTime).setAttribute("class","cur")
    			//获取当前时间
    			this.endTime = Date.parse(new Date())/1000;
				this.value2 = this.getFormatDate(this.endTime)
				this.startTime = Date.parse(new Date())/1000-604800;
				this.value1 = this.getFormatDate(this.startTime)
				this.getParams();
			},
			getParams:function(){
				this.graphList=[]

				//获取监控项个数
				this.selectItems=[];
				this.selectIps=[];
				var items=document.getElementsByName("check-item");
				var itemLen=items.length;

				if(itemLen>0){
                	for(var i=0;i<itemLen;i++){
                		if(items[i].checked){
                			var row = items[i].parentNode.parentNode;
                			var content=vueGetData.trim(row.cells[1].innerHTML);
                			this.selectItems.push(content)
                		}
                	}
                }
                //获取机器ips
                var ips=document.getElementsByName("check-ip");
				var ipLen=ips.length;
				if(ipLen>0){
                	//检查是否选择ip
                	for(var i=0;i<ipLen;i++){
                		if(ips[i].checked){
                			var row = ips[i].parentNode.parentNode;
                			var content=vueGetData.trim(row.cells[1].innerHTML);
                			this.selectIps.push(content);
                		}
                	}
                }

                //数组方法
                if(this.selectItems.length>0 & this.selectIps.length>0){
                	for(var i=0;i<this.selectItems.length;i++){
                		this.graphList.push(i);
                	}
                	for(var i=0;i<this.selectItems.length;i++){
                		this.drawGraph(i)
                	}
                }
			},
			drawGraph:function(index){
				let ips=this.selectIps[0];
				for(var i=1;i<this.selectIps.length;i++){
					ips += ","+this.selectIps[i];
				}

				let data={
					"starttime" : this.startTime,
					"endtime" : this.endTime,
					"ips" : ips,
					"cols" : this.selectItems[index],
					"pid": this.treeId
					//"pid": "7"
				}

				if(this.endTime-this.startTime<=3600){
					data["type"]='sec';
				}else if(this.endTime-this.startTime>3600 || this.endTime-this.startTime<=86400){
					data["type"]='min';
					data["type_num"] = '2';
				}else if(this.endTime-this.startTime>86400 || this.endTime-this.startTime<=259200){
					data["type"]='min';
					data["type_num"] = '10';
				}else if(this.endTime-this.startTime>259200 || this.endTime-this.startTime<=604800){
					data["type"]='min';
					data["type_num"] = '20';
				}else{
					data["type"]='hour';
				}

				vueGetData.getData("gethbasedata",data,function(jsondata){
					if(jsondata.body.length==0|| jsondata.body.error === 22001){
						if(jsondata.body.error === 22001){
							console.log("22001,接口返回错误")
						}else{
							console.log("数据为空")
						}

						var id = 'graph'+index;
						var myChart = echarts.init(document.getElementById(id));
						var option = {
					        title: {
						        text: this.selectItems[index]
						    },
						    tooltip: {
						        trigger: 'axis'
						    },
						    legend: {
						        data:this.selectIps
						    },
						    xAxis: {
						    	type: 'category',
	        					boundaryGap: false,
	        					data: []
						    },
						    yAxis: {
						        splitLine: {
						            show: false
						        }
						    },
						    series: []
					    };
					    myChart.setOption(option);
					}else{
						//获取折线图横坐标数据
						let xData=[];
						if(data["type"]=='sec'){
							for (var start=this.startTime;start<=this.endTime;start++){
								var d = new Date(start * 1000);    //根据时间戳生成的时间对象
								var time = (d.getMonth()+1) + "-" + 
							           (d.getDate()) + " " + (d.getHours()) + ":" + 
							           (d.getMinutes()) + ":" + 
							           (d.getSeconds());
							    xData.push(time);
							}
						}else if(data["type"]=='min'){
							for (var start=this.startTime-this.startTime%60;start<=this.endTime;start = start+60){
								var d = new Date(start * 1000);    //根据时间戳生成的时间对象
								var time = (d.getMonth()+1) + "-" + 
							           (d.getDate()) + " " + (d.getHours()) + ":" + 
							           (d.getMinutes()) + ":" + 
							           (d.getSeconds());
							    xData.push(time);
							}
						}else if(data["type"]=='hour'){
							for (var start=this.startTime-this.startTime%3600;start<=this.endTime;start = start+3600){
								var d = new Date(start * 1000);    //根据时间戳生成的时间对象
								var time = (d.getMonth()+1) + "-" + 
							           (d.getDate()) + " " + (d.getHours()) + ":" + 
							           (d.getMinutes()) + ":" + 
							           (d.getSeconds());
							    xData.push(time);
							}
						}
						// var d = new Date(start * 1000);    //根据时间戳生成的时间对象
						// var startIndex = (d.getHours()) + ":" + 
						//            (d.getMinutes()) + ":" + 
						//            (d.getSeconds());
						//获取折线图纵坐标数据
						var ips=[];
						let yData = [];
						for (var i=0;i<this.selectIps.length;i++){
							//对应ip
							var ip=this.selectIps[i];
							//ip对应接口数据
							let resultData = jsondata.body[this.selectItems[index]];
							//接口数据转换为数组形式
							let tempY = []
							for(var item in resultData[ip]){
								var d = new Date(item * 1000);
								var time = (d.getMonth()+1) + "-" + 
						           (d.getDate()) + " " + (d.getHours()) + ":" + 
						           (d.getMinutes()) + ":" + 
						           (d.getSeconds());
								tempY.push([time,parseFloat(resultData[ip][item])])
							}
							//图标读取数据
							let tempData={
								name: ip,
						        type: 'line',
						        // smooth: true,
						        data:tempY
							}
							yData.push(tempData)
						}

						var id = 'graph'+index;
						var myChart = echarts.init(document.getElementById(id));
						var option = {
					        title: {
						        text: this.selectItems[index],
						        x:'center',
						        y:'bottom'
						    },
						    tooltip: {
						        trigger: 'axis'
						    },
						    legend: {
						    	//orient: 'vertical',
								x: 'left',
						        data:this.selectIps
						    },
						    grid: {
						        left: '4%',
						        right: '8%',
						        bottom: '10%',
						        containLabel: true
						    },
						    xAxis: {
						    	type: 'category',
	        					boundaryGap: false,
	        					data: xData
						    },
						    yAxis: {
						        splitLine: {
						            show: false
						        }
						    },
						    series: yData
					    };
					    myChart.setOption(option);
					    //定时器
					    //this.getnewDate(index,data,myChart,xData,yData)
					}
		        }.bind(this),function(){

		        }.bind(this));
			},
			getnewDate:function(index,data,myChart,xData,yData){
				//定时器有问题，待优化
				let ipArr = this.selectIps;
				let currentItem = this.selectItems[index]
				let timeInternal = 10;
				if(data["type"]=='sec'){
					timeInternal = 10;
				}else if(data["type"]=='min' && data["type_num"] == '2'){
					timeInternal = 120;
				}else if(data["type"]=='min' && data["type_num"] == '10'){
					timeInternal = 600;
				}else if(data["type"]=='min' && data["type_num"] == '20'){
					timeInternal = 1200;
				}else if(data["type"]=='hour'){
					timeInternal = 3600;
				}

				var timer = setInterval(function () {
					if(currentItem){
						data["starttime"] = data["endtime"];
						data["endtime"] = data["endtime"]+timeInternal;
						
						vueGetData.getData("gethbasedata",data,function(jsondata){
				        	if(jsondata.body.length==0){
				        		//console.log("数据为空")
				        	}else{
				        		//获取折线图横坐标数据
				        		xData.shift();
				        		for (var start=data["starttime"];start<=data["endtime"];start++){
									var d = new Date(start * 1000);    //根据时间戳生成的时间对象
									var time = (d.getMonth()+1) + "-" + 
								           (d.getDate()) + " " + (d.getHours()) + ":" + 
								           (d.getMinutes()) + ":" + 
								           (d.getSeconds());
								    xData.push(time);
								}
								//获取折线图纵坐标数据
								var ips=[];
								for (var i=0;i<ipArr.length;i++){
									//对应ip
									var ip=ipArr[i];
									//ip对应接口数据
									let resultData = jsondata.body[currentItem];
									//接口数据转换为数组形式
									for(var item in resultData[ip]){
										var d = new Date(item * 1000);
										var time = (d.getMonth()+1) + "-" + 
								           (d.getDate()) + " " + (d.getHours()) + ":" + 
								           (d.getMinutes()) + ":" + 
								           (d.getSeconds());
								        yData[i].data.shift()
										yData[i].data.push([time,parseFloat(resultData[ip][item])])
									}
								}
								myChart.setOption({
							    	xAxis: {
								    	type: 'category',
			        					boundaryGap: false,
			        					data: xData
								    },
							        series: yData
							    });
					        }
				        }.bind(this),function(){

				        }.bind(this));
					}
					else{
						clearInterval(timer)
					}
				}, timeInternal*1000);
			},
			getFormatDate:function(timestamp){
				var date = new Date(timestamp * 1000);    //根据时间戳生成的时间对象
				let month = date.getMonth()+1;
			    var currentdate = date.getFullYear() + "-" + month + "-" + date.getDate()
			            + " " + date.getHours() + ":" + date.getMinutes()
			            + ":" + date.getSeconds();
			    return currentdate;
			},
			sureClick:function(){
    			//获取当前时间
				if(this.endTime-this.startTime<=0){
					//获取当前时间
	    			this.endTime = Date.parse(new Date())/1000;
					this.value2 = this.getFormatDate(this.endTime)
					this.startTime = Date.parse(new Date())/1000-3600;
					this.value1 = this.getFormatDate(this.startTime)
				}else if(this.endTime-this.startTime != 3600){
					document.getElementById(this.currentTime).setAttribute("class","")
					document.getElementById('oneHour').setAttribute("class","")
					this.currentTime='oneHour';
				}
				this.getParams();
			},

			closeGraph:function(item){
				let tempItem = this.selectItems[item]
				this.selectItems.splice(item,1)

				var items=document.getElementsByName("check-item");
				var itemLen=items.length;
				if(itemLen>0){
                	for(var i=0;i<itemLen;i++){
                		if(tempItem==vueGetData.trim(items[i].parentNode.parentNode.cells[1].innerHTML)){
                			items[i].checked = false;
                		}
                	}
                }
                this.getParams();
			}
		},
		created(){
			//获取当前时间

			this.endTime = Date.parse(new Date())/1000;
			this.value2 = this.getFormatDate(this.endTime)
			this.startTime = Date.parse(new Date())/1000-3600;
			this.value1 = this.getFormatDate(this.startTime)
		},
		components: {
			SideLeft,
			'el-date-picker': DatePicker,
		}
	}
</script>
<style lang="less" >
@import "../../Css/mixin.less";
.el-input__icon+.el-input__inner {
    padding-right: 35px;
    width: 180px;
}
.el-input--small .el-input__inner {
    height: 30px;
    margin: 0px;
    width: 140px;
}
.home {
	padding-left: 240px;
	width: 100%;
}
.topbox {
	width: 100%;
	overflow:hidden;
	padding: 10px 0;
	.selectbox {
		width: 50%;
		height: 300px;
		padding: 10px 20px 0px 20px;

		.title {
		    display: block;
		    position: relative;
		    line-height: 40px;
		    font-size: 16px;
			padding-left: 10px;
		    color: #6d6d6d;
			background: #fff;
			margin-bottom: 8px;

		    #searchItem {
				float: right;
				height: 27px;
				width: 200px;
				font-size: 15px;
				font-weight: normal;
				border: 1px solid #e6e6e6;
				margin-top: 7px;
			}
			#searchI {
				position: absolute;
				top: 10px;
				right: 20px;
				width: 20px;
				height: 20px;
				background:url(@searchico) no-repeat;
				background-size:20px;
				cursor:pointer;
			}
		}
	}
}

.content {
	width: 100%;
	height: 260px;
	background-color:#fcfcfc;
}

.contentHead {
	height: 40px;
	background-color: #fff;
	position: relative;
	@h: 27px;
	line-height: @h;
	padding: 6px 0;
	font-size: 15px;

	#searchMechine {
		float:left;
		height: @h;
		width: 200px;
		border: 1px solid #e6e6e6;
	}
	#searchM {
		position: absolute;
		top: 10px;
		left: 180px;
		width: 20px;
		height: 20px;
		background:url(@searchico) no-repeat;
		background-size:20px;
		cursor:pointer;
	}

	li {
		display: inline;
		color: #3f3f3f;
		//text-decoration: underline;
		margin:0 1%;
		cursor:pointer;
	}
}

.contentTable{
	height:210px;
	overflow-y:auto;
	tr {
		border-bottom: 0;
		background: #f8f8f8;
	}
	td{
		height:35px;
		line-height:35px;
		font-size:14px;
		color: #666;
	}
	tr:nth-child(2n){
		background: #fff;
	}
}

.graphbox {
	margin: 20px;
	font-size: 15px;
	color: #fff;

	.graphHead{
		height: 50px;
		background-color: #fff;
		line-height: 50px;
		color: #3f3f3f;
	}
	li {
		display: inline;
		margin:0 10px;
		input{
			border: 1px solid #e6e6e6;
		}
		&.cur {
			color: #2bc8f2;
		}
	}
}

.graph{
	position: relative;
	float:left;
	width:50% !important;

	.echartsGraph{
		padding-left:20px;
		height:300px;
		width:100%;
		float:left;
	}
	.graphClose {
		position: absolute;
		top: 0;
		right: 0;
		cursor: pointer;
		border-radius: 5px;
		width: 50px;
		height: 50px;
		z-index: 100;

		&:after,&:before{
		    content: "";
		    position: absolute;
		    width: 2px;
		    height: 13px;
		    background: #bbbbbb;
		    font-size:0; 
		    line-height:0;
		    vertical-align:middle;
		    transform: rotate(45deg);
		    -webkit-transform: rotate(45deg);
		    transform-origin: 50% 50%;
		    -webkit-transform-origin: 50% 50%;
		    left: 24px;
		    top: 18px;
		}
		&:after{
			-webkit-transform: rotate(-45deg);
		}
	}
}

.button {
    height: 30px;
    padding: 0 10px;
    line-height: 28px;
    background-color: #2bc8f2;
    border-radius: 3px;
    color: #fff;
    font-size: 13px;
    display: inline-block;
    border: 1px solid #2bc8f2;
    position: relative;
    cursor:pointer;
}
.button:hover, .button:focus {
    color: #fff
}

.cur{
	font-weight: bold;
	text-decoration: underline;
}
</style>

<template>
	<div class="producthome">
		<div class="navigation">
			<el-breadcrumb separator="/">
				<el-breadcrumb-item :to="{ path: '/' }">首页</el-breadcrumb-item>
				<el-breadcrumb-item>报警仪表盘</el-breadcrumb-item>
			</el-breadcrumb>
		</div>
		<div class="timeBox">
			<span class="time">{{ date }}</span> 
		    <span class="time">{{ time }}</span> 
		</div>
		<div class="configList">
			<div class="table-div">
				<div class="table_head">
					<span class="title">
						集群异常列表
						<span class="updateButton" @click="getAlertList"></span>
					</span>
				</div>
				<table class="manage-table">
					<thead>
						<tr>
							<th width="5%"><input type="checkbox" name="check-all" disabled="disabled"></th>
							<th width="5%">id</th>
							<th width="20%">集群名称</th>
							<th width="30%">策略名称</th>
							<th width="10%">异常情况</th>
							<th width="15%">异常开始时间</th>
							<th width="10%">报警状态</th>
							<th width="15%">操作</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item,index) in alertList">
							<td><input type="checkbox" name="check-one" disabled="disabled"></td>
							<td>{{item.id}}</td>
							<td>{{item.group_name}}</td>
							<td>
								<el-tooltip placement="top-start" effect="light">
									<div slot="content">{{item.strategy_name}}</div>
									<span>{{item.strategy_name}}</span>
								</el-tooltip>
							</td>
							<td>
								<a href="javascript:;" :data-id="item.id" :data-index="index" @click="showError(item)">{{item.rate}}({{item.wrong_num}}/{{item.total_num}})</a>
							</td>
							<td>{{item.start_time | formatDate}}</td>
							<td v-if="item.show_status == 0">异常</td>
							<td v-else-if="item.show_status == 1">已屏蔽</td>
							<td>
								<template v-if="item.show_status == 0">
									<a href="javascript:;" :data-id="item.id" :data-index="index" @click="screenEvent(item)">屏蔽</a>
									<a href="javascript:;" :data-id="item.id" :data-index="index" @click="deleteEvent(item)">删除</a>
								</template>
								<template v-else>
									<a href="javascript:;" :data-id="item.id" :data-index="index" @click="showTime(item)">屏蔽时间</a>
									<a href="javascript:;" :data-id="item.id" :data-index="index" @click="unScreenEvent(item)">取消屏蔽</a>
									<a href="javascript:;" :data-id="item.id" :data-index="index" @click="deleteEvent(item)">删除</a>
								</template>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<ErrorDetail :editinitformdata1="editGetFormData1"></ErrorDetail>
		<ErrorScreen :editinitformdata2="editGetFormData2"></ErrorScreen>
	</div>

</template>
<script>
import {mapGetters,mapActions} from 'vuex'
import vueGetData from "../../Js/vueGetData.js"
import ErrorDetail from "../dialogs/ErrorDetail.vue"
import ErrorScreen from "../dialogs/ErrorScreen.vue"

	export default{
		name:'home',
		data () {
			return {
				addData:{
					name:'',
				},
				// updateData:[],
				canWrite:false,//是否有写权限
				date:'',
				time:'',
				week:['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'],
				editGetFormData1:{},	//编辑时数据
				editGetFormData2:{},	//编辑时数据
			}
		},
		created () {
			this.getAlertList();
			var timerID = setInterval(this.updateTime, 1000); 
			this.updateTime();
		},
		computed:mapGetters({
			alertList:"alertlist"
		}),
		methods: {
			getAlertList:function(){
				this.$store.dispatch("getAlertlist",{});
			},
			noPermission:function(){
				vueGetData.creatTips("无操作权限");
			},
			showError:function(item){
				let data = {"show_id":item.show_id}
		        vueGetData.getData("geteventipsshow",data,function(jsondata){
			        if(jsondata.body.error_code === 22000){
		        		if(jsondata.body.data){
		        			this.editGetFormData1 = jsondata.body.data;
		        			document.getElementsByClassName("dialog")[0].style.display = "block";
		        		}else{
		        			vueGetData.creatTips("异常详情:false");
		        		}
		        	}
		        }.bind(this),function(){

		        }.bind(this));
			},
			screenEvent:function(item){
				let data={};
				data["show_id"]=item.show_id;
				data["show_status"]=1;

				this.editGetFormData2 = data;
				document.getElementsByClassName("dialog")[1].style.display = "block";
			},
			showTime:function(item){
				let data = {"show_id":item.show_id}
		        vueGetData.getData("getshieldendtimeshow",data,function(jsondata){
			        if(jsondata.body.error_code === 22000){
		        		if(jsondata.body.data){
		        			vueGetData.creatTips("屏蔽结束时间:"+this.timestampToTime(jsondata.body.data),1000);
		        		}else{
		        			vueGetData.creatTips("获取报警屏蔽时间失败");
		        		}
		        	}
		        }.bind(this),function(){

		        }.bind(this));
			},
			unScreenEvent:function(item){
				let data={};
				data["show_id"]=item.show_id;
				data["show_status"]=0;

				vueGetData.getData("updateeventstatus",data,function(jsondata){
 					if(jsondata.body.error_code == 22000){
			        	vueGetData.creatTips("取消屏蔽成功");
						this.getAlertList()
			    	}
		        }.bind(this),function(){

		        }.bind(this));
			},
			deleteEvent:function(item){
				let data={};
				data["show_id"]=item.show_id;
				data["show_status"]=2;
				let _self = this;

				let str = '<div class="popCreat" id="deleteLogBox">'
		            +'<h3>确定要删除吗？</h3>'
		            +'<div class="btns"><span class="surebtn" id="surebtn">确定</span><span class="cancelbtn" id="cancelbtn">取消</span></div></div>';
				vueGetData.creatPop(str);

				let surebtn = document.getElementById("surebtn");
				let cancelbtn = document.getElementById("cancelbtn");

		        surebtn.onclick = function(){
					vueGetData.getData("updateeventstatus",data,function(jsondata){
	 					if(jsondata.body.error_code == 22000){
				        	vueGetData.creatTips("删除成功");
							_self.getAlertList()
				    	}else{
				    		vueGetData.creatTips("删除失败");
				    	}
			        }.bind(this),function(){

			        }.bind(this));
					vueGetData.closePop();
		        }
		        cancelbtn.onclick = function(){
		        	vueGetData.closePop();
		        }
			},
			updateTime:function(){
				var cd = new Date();
				this.time=this.zeroPadding(cd.getHours(), 2) + ':' + this.zeroPadding(cd.getMinutes(), 2) + ':' + this.zeroPadding(cd.getSeconds(), 2); 
				this.date = this.zeroPadding(cd.getFullYear(), 4) + '-' + this.zeroPadding(cd.getMonth()+1, 2) + '-' + this.zeroPadding(cd.getDate(), 2) + ' ' + this.week[cd.getDay()];
			},
			zeroPadding(num, digit){
				var zero = ''; 
			    for(var i = 0; i < digit; i++) { 
			        zero += '0'; 
			    } 
			    return (zero + num).slice(-digit);
			},
			timestampToTime(timestamp) {
		        var date = new Date(timestamp * 1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
		        let Y = date.getFullYear() + '-';
		        let M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-';
		        let D = (date.getDate() < 10 ? '0'+date.getDate() : date.getDate()) + ' ';
		        let h = (date.getHours() < 10 ? '0'+date.getHours():date.getHours()) + ':';
		        let m = (date.getMinutes() < 10 ? '0'+date.getMinutes():date.getMinutes())+ ':';
		        let s = date.getSeconds() < 10 ? '0'+ date.getSeconds() : date.getSeconds();
		        return Y+M+D+h+m+s;
		    }
		},
		components: {
			ErrorDetail,
			ErrorScreen,
		}
	}
</script>
<style lang="less" >
@import "../../Css/rightside.less";

.producthome {
	width: 100%;
	height:85%;
	position: absolute;
	overflow-y: auto;
	.navigation {
		padding:20px 20px 5px 20px;
		//background: @bgGray;
		//border-bottom: 1px solid rgba(78,105,130,.2);
	}
	.timeBox{
		padding: 10px 20px;
		background:#fff000;
		position:relative;
		text-align:center;
	}
}
.time{
	letter-spacing: 0.05em;
	font-size: 30px;
	padding: 5px 0;
}
.table_head{
	height: 40px;
	background-color: #f9f9f9;
	margin-bottom: 5px;
	.title{
		line-height: 40px;
		font-size: 25px;
		text-align:center;
		display:block;
	}
	.updateButton{
		display: block;
		width: 20px;
		height: 20px;
		margin:10px;
		margin-right:20px;
		float:right;
		cursor:pointer;
		background-image: url(/dist/Image/update.png);
		background-repeat: no-repeat;
		background-size: contain;
	}
}
</style>
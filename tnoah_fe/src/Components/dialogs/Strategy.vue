<template>
	<div class="dialog">
		<div class="dialogOverlay"></div>
		<div class="dialogContent">
			<div class="dialogClose" @click="hideDialog"></div>
			<div class="dialogDetail">
				<div class="dialogTitle">{{strategyformData.dialogTitle}}</div>
				<div class="dialogDiv">
					<div class="dialogSubDiv">
						<div class="subDivTitle requiredItem">采集项</div>
						<div class="subDivContent">
							<select name="reasonpselect" class="long-select" id="reasonpselect" v-model="strategyformData.reasonSelected" @change="resetSecondSelected">
								<option v-for="(value,index) in strategyformData.reasons" v-bind:value="index">{{value}}</option>
							</select>
							<select name="secondreasonpselect" class="long-select" id="secondreasonpselect" v-model="strategyformData.secondReasonSelected">
								<option v-for="(value,index) in strategyformData.secondReason[strategyformData.reasonSelected]" v-bind:value="index">{{value.name}}</option>
							</select>
						</div>
					</div>
					<div class="dialogSubDiv">
						<div class="subDivTitle requiredItem">备注</div>
						<div class="subDivContent"><input type="text" class="dataport-dialog-referer" v-model="strategyformData.note"><span style="color:red;font-size:9px;">(用于短信或邮件报警标题，尽量描述清楚)</span></div>
					</div>
					<div class="dialogSubDiv">
						<div class="subDivTitle requiredItem">报警策略</div>
						<div class="subDivContent">
							<div class="subDivCard">
								<div class="info-label">
								e.g. {{strategyformData.func[strategyformData.funcSelected]}}{${{strategyformData.value}}}{{strategyformData.options[strategyformData.opSelected]}}{{strategyformData.right_value}} 就报警
								</div>
								<span class="cardContent">
									<label class="name">条件</label>
									<select class="short-select" v-model="strategyformData.funcSelected">
										<option v-for="(value,index) in strategyformData.func" v-bind:value="index">{{value}}</option>
									</select>
									<!-- <input type="text" class="foreverFrequence sm-size" v-model="strategyformData.func">
									 (支持all/avg/sum/diff/pdiff/max/min) -->
								</span>
								<span class="cardContent">
									<label class="name">值</label>
									<input type="text" class="foreverFrequence sm-size" v-model="strategyformData.value">
								</span>
								<span class="cardContent">
									<label class="name">操作符</label>
									<select class="short-select" v-model="strategyformData.opSelected">
										<option v-for="(value,index) in strategyformData.options" v-bind:value="index">{{value}}</option>
									</select>
								</span>
								<span class="cardContent">
									<label class="name">数量</label>
									<input type="number" class="foreverFrequence sm-size" v-model="strategyformData.right_value"  min=0>
								</span>

							</div>
						</div>
					</div>
					<div class="dialogSubDiv">
						<div class="subDivTitle requiredItem">最大报警数</div>
						<div class="subDivContent">
							<input type="number" class="foreverFrequence sm-size" v-model="strategyformData.max_step" min=1>(触发报警后最多报警次数)
						</div>
					</div>
					<div class="dialogSubDiv">
						<div class="subDivTitle requiredItem">报警级别</div>
						<div class="subDivContent">
							<div class="subDivCard">
								<div class="info-label">
								0级、1级既发短信也发邮件，2级只发邮件
								</div>
								<label><input type="radio" name="level" value="0" v-model="strategyformData.priority" id="level0"><span for="level0">0级</span></label>
								<label><input type="radio" name="level" value="1" v-model="strategyformData.priority" id="level1"><span for="level1">1级</span></label>
								<label><input type="radio" name="level" value="2" v-model="strategyformData.priority" id="level2"><span for="level2">2级</span></label>
							</div>
						</div>
					</div>
					<div class="dialogSubDiv">
						<div class="subDivTitle  requiredItem">有效监控时间</div>
						<div class="subDivContent">
							<input class="validtime" type="text" v-model="strategyformData.startTime"> 至 <input class="validtime" type="text" v-model="strategyformData.endTime">
						</div>
					</div>

					<div class="dialogButtonDiv">
						<a href="javascript:;" class="dialog-blue-button" @click="addStrategy" v-if="strategyformData.canWrite">保存</a>
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
	name: "Dialogdataport",
	data () {
		return {
			groupId:0,
			templateId:0,
			strategyId:0,
			strategyformData: {
				dialogTitle: '新建策略',
				metric:"",
				priority:"0",
				note:"",
				funcSelected:0,
				opSelected:0,
				// func:'',
				func:["all","avg","sum","diff","pdiff","max","min"],
				value:"3",
				options:["=","!=","<","<=",">",">="],
				right_value:0,
				max_step:"3",
				startTime: "00:00",
				endTime:"23:59",
				reasonSelected:0,
				reasons:[],
				secondReasonSelected:0,
				secondReason:[],
				isEdit:false,
				canWrite:true,
			},
			copyStrategyformData:{},
		}
	},
	props: ["editinitformdata"],
	computed:mapGetters({
		serviceLineId:"serviceLineId"
	}),
	watch: {
		editinitformdata:function(){
			let json = this.editinitformdata;
			this.strategyId = json["id"];
			// this.strategyformData.func = json["func"];
			this.strategyformData.max_step = json["max_step"];
			this.strategyformData.note = json["note"];
			this.strategyformData.priority = json["priority"];
			this.strategyformData.right_value = json["right_value"];
			this.strategyformData.startTime = json["run_begin"];
			this.strategyformData.endTime = json["run_end"];
			this.strategyformData.isEdit = true;
			this.strategyformData.canWrite = json["canWrite"];

			let temp=json["func"].split("$")
			let temp0 = temp[0].slice(0,-1)
			let temp1 = temp[1].slice(0,-1)
			this.strategyformData.value = temp1;
			for(let i=0;i<this.strategyformData.func.length;i++){
				if(this.strategyformData.func[i]==temp0){
					this.strategyformData.funcSelected = i;
				}
			}

			for(let i=0;i<this.strategyformData.options.length;i++){
				if(this.strategyformData.options[i]==json["op"]){
					this.strategyformData.opSelected = i;
				}
			}

			for(let i=0;i<this.strategyformData.reasons.length;i++){
				for(let j=0;j<this.strategyformData.secondReason[i].length;j++){
					if(this.strategyformData.secondReason[i][j].name==json["metric"]){
						this.strategyformData.reasonSelected = i;
						this.strategyformData.secondReasonSelected = j;
					}
				}
			}
		}
	},

	methods: {
		hideDialog: function(){
			//重置初始化数据
			for(let key in this.copyStrategyformData){
				this.strategyformData[key] = this.copyStrategyformData[key];
			}
			this.$store.dispatch("getStrategyList",{"tpl_id":this.templateId})
			document.getElementsByClassName("dialog")[0].style.display = "none";
		},
		resetSecondSelected:function(){
			this.strategyformData.secondReasonSelected = 0;
		},
		addStrategy:function(){
			this.strategyformData.note = vueGetData.trim(this.strategyformData.note);
			this.strategyformData.value = vueGetData.trim(this.strategyformData.value);

			this.strategyformData.startTime = vueGetData.trim(this.strategyformData.startTime);
			this.strategyformData.endTime = vueGetData.trim(this.strategyformData.endTime);

			if(!this.strategyformData.note){
				vueGetData.creatTips("请填写策略备注");
				return false;
			}
			if(!this.strategyformData.func){
				vueGetData.creatTips("请填写报警条件");
				return false;
			}

			if(this.strategyformData.right_value==null){
				vueGetData.creatTips("请填写报警数量");
				return false;
			}
			if(this.strategyformData.right_value<0){
				vueGetData.creatTips("报警数量不能为负数");
				return false;
			}
			if(this.strategyformData.max_step==null){
				vueGetData.creatTips("请填写最大报警次数");
				return false;
			}
			if(this.strategyformData.max_step<1){
				vueGetData.creatTips("最大报警次数不能小于1");
				return false;
			}
			if(!this.strategyformData.startTime){
				vueGetData.creatTips("请填写报警起始时间");
				return false;
			}
			if(!this.strategyformData.endTime){
				vueGetData.creatTips("请填写报警终止时间");
				return false;
			}
			let data = {};
			data["tpl_id"] = this.templateId;
			data["note"] = this.strategyformData.note;
			data["metric"] = this.strategyformData.secondReason[this.strategyformData.reasonSelected][this.strategyformData.secondReasonSelected].name;
			data["func"] = this.strategyformData.func[this.strategyformData.funcSelected]+"{$"+this.strategyformData.value+"}";
			data["op"] = this.strategyformData.options[this.strategyformData.opSelected];
			data["right_value"] = this.strategyformData.right_value;
			data["max_step"] = this.strategyformData.max_step;
			data["priority"] = this.strategyformData.priority;

			var reg =/^([0-1]\d|2[0-3]):[0-5]\d$/;
	        if(reg.test(this.strategyformData.startTime))
	        {
	            data["run_begin"] = this.strategyformData.startTime;
	        }
	        else
	        {
	        	vueGetData.creatTips("您的有效监控开始时间输入错误，请重新输入");
				return false;
	        }

	        if(reg.test(this.strategyformData.endTime))
	        {
	            data["run_end"] = this.strategyformData.endTime;
	        }
	        else
	        {
	        	vueGetData.creatTips("您的有效监控结束时间输入错误，请重新输入");
				return false;
	        }

	        let _self = this;
	        if(this.strategyformData.isEdit){
	        	data["id"] = this.strategyId;
	        	vueGetData.getData("updatestrategy",data,function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		vueGetData.creatTips("修改成功");
		        		this.$store.dispatch("getStrategyList",{"tpl_id":this.templateId})
		        		_self.hideDialog();
		        	}else{
		        		vueGetData.creatTips("系统错误请联系管理员查看问题");
		        		_self.hideDialog();
		        	}
		        }.bind(this),function(){

		        }.bind(this));
	        }else{
	        	vueGetData.getData("addstrategy",data,function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		vueGetData.creatTips("添加成功");
		        		this.$store.dispatch("getStrategyList",{"tpl_id":this.templateId})
		        		_self.hideDialog();
		        	}else{
		        		vueGetData.creatTips("系统错误请联系管理员查看问题");
		        		_self.hideDialog();
		        	}
		        }.bind(this),function(){

		        }.bind(this));
	        }
		}
	},
	created: function(){
		this.groupId = this.$route.params.groupid;
		this.templateId = this.$route.params.templateid;

		//获取采集项
		vueGetData.getData("getgroupmetricsname",{"group_id":this.groupId},function(jsondata){
        	if(jsondata.body.error_code === 22000){
        		let json = jsondata.body.data;
        		for(var item in json){
        			this.strategyformData.reasons.push(item)
        			var tempReason = [];

        			if(item == 'BUSINESS'){
        				for(var child in json[item]){
        					if(json[item][child]["name"]!='BUS_Tagent_Heartbeat'){
		        				tempReason.push(json[item][child])
        					}
	        			}
        			}else{
        				for(var child in json[item]){
	        				tempReason.push(json[item][child])
	        			}
        			}
        			this.strategyformData.secondReason.push(tempReason)
        		}
        	}else{
        		console.log("error_code:22001")
        	}
        }.bind(this),function(){

        }.bind(this));
		//保存初始化数据
		for(let key in this.strategyformData){
			this.copyStrategyformData[key] = this.strategyformData[key]
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
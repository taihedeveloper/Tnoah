<template>
	<div class="dialog">
		<div class="dialogOverlay"></div>
		<div class="dialogContent">
			<div class="dialogClose" @click="hideDialog"></div>
			<div class="dialogDetail">
				<div class="dialogTitle">{{businessformData.dialogTitle}}</div>
				<div class="dialogDiv">
					<div class="dialogSubDiv">
						<div class="subDivTitle requiredItem">名称</div>
						<div class="subDivContent"  v-show="!businessformData.isEdit"><input type="text" class="dataport-dialog-referer" placeholder="" v-model="businessformData.name"><span style="color:red;margin-left:10px;">(业务类命名以"BUS_"为前缀)</span></div>
						<div class="subDivContent"  v-show="businessformData.isEdit"><input type="text" class="dataport-dialog-referer" placeholder="" v-model="businessformData.name" disabled="disabled"></div>
					</div>
					<div class="dialogSubDiv">
						<div class="subDivTitle requiredItem">匹配方式</div>
						<div class="subDivContent">
							<div class="subDivCard">
								<div class="info-label">
								match_str:正则匹配&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;awk_str:列匹配&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;search_str:正则查找
								</div>
								<label><input type="radio" name="match_type" value="match_str" v-model="businessformData.match_type" id="match_str"><span for="match_str">match_str</span></label>
								<label><input type="radio" name="match_type" value="awk_str" v-model="businessformData.match_type" id="awk_str"><span for="awk_str">awk_str</span></label>
								<label><input type="radio" name="match_type" value="search_str" v-model="businessformData.match_type" id="search_str"><span for="search_str">search_str</span></label>
							</div>
						</div>
					</div>
					<div class="dialogSubDiv">
						<div class="subDivTitle requiredItem">匹配内容</div>
						<div class="subDivContent"><input type="text" class="dataport-dialog-referer" placeholder="" v-model="businessformData.value"></div>
					</div>
					<div class="dialogSubDiv">
						<div class="subDivTitle">过滤内容</div>
						<div class="subDivContent"><input type="text" class="dataport-dialog-referer" placeholder="" v-model="businessformData.filter"></div>
					</div>
					<div class="dialogSubDiv">
						<div class="subDivTitle requiredItem">日志路径</div>
						<div class="subDivContent" v-show="!businessformData.isEdit"><input type="text" class="dataport-dialog-referer" placeholder="" v-model="businessformData.log_path"></div>
						<div class="subDivContent" v-show="businessformData.isEdit"><input type="text" class="dataport-dialog-referer" placeholder="" v-model="businessformData.log_path"  disabled="disabled"></div>
					</div>
					<div class="dialogSubDiv">
						<div class="subDivTitle requiredItem">日志风格</div>
						<div class="subDivContent">
							<div class="subDivCard">
								<div class="info-label">
								newly:每到整点生成新日志(e.g. lua.log.20170918)
								<br>
								split:每到整点移走当前日志，并新建新日志(e.g. access.log)
								{{businessformData.log_style}}
								</div>
								<template v-if="!businessformData.isEdit">
									<label><input type="radio" name="log_style" value="newly" v-model="businessformData.log_style" id="newly"><span for="newly">newly</span></label>
									<label><input type="radio" name="log_style" value="split" v-model="businessformData.log_style" id="split" ><span for="split">split</span></label>
								</template>
								<template v-else>
									<label><input type="radio" name="log_style" value="newly" v-model="businessformData.log_style" id="newly" disabled="dispatch"><span for="newly">newly</span></label>
									<label><input type="radio" name="log_style" value="split" v-model="businessformData.log_style" id="split" disabled="dispatch"><span for="split">split</span></label>
								</template>
							</div>
						</div>
					</div>
					<div class="dialogSubDiv" v-show="businessformData.log_style=='newly'">
						<div class="subDivTitle requiredItem">时间格式</div>
						<div class="subDivContent" v-show="!businessformData.isEdit"><input type="text" class="dataport-dialog-referer" placeholder="" v-model="businessformData.log_format"></div>
						<div class="subDivContent" v-show="businessformData.isEdit"><input type="text" class="dataport-dialog-referer" placeholder="" v-model="businessformData.log_format" disabled="disabled"></div>
					</div>
					<div class="dialogSubDiv">
						<div class="subDivTitle requiredItem">日志样例</div>
						<div class="subDivContent"><textarea type="text" class="long-textarea" placeholder="" v-model="businessformData.example"></textarea></div>
					</div>
					<div class="dialogButtonDiv">
						<a href="javascript:;" class="dialog-blue-button" @click="addBusiness">保存</a>
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
			businessformData: {
				dialogTitle: '新建采集项',
				name:"",
				match_type:"match_str",
				value:"",
				filter:"",
				log_path:"",
				log_style:"newly",
				log_format:"",
				isEdit:false,
				example:""
			},
			copybusinessformData:{},
		}
	},
	// props: ["editinitformdata"],
	computed:mapGetters({
		serviceLineId:"serviceLineId",
		editinitformdata:"businessdata"
	}),
	watch: {
		editinitformdata:function(){
			let json = this.editinitformdata;
			this.businessformData.name = json["item_name_prefix"];
			this.businessformData.log_path = json["log_path"];
			this.businessformData.log_style = json["log_style"];
			this.businessformData.log_format = json["log_format"];
			this.businessformData.isEdit = true;
			this.businessformData.filter = json["filt_str"];

			if(json["match_str"]){
				this.businessformData.match_type = 'match_str';
				this.businessformData.value = json['match_str'];
			}else if(json["awk_str"]){
				this.businessformData.match_type = 'awk_str';
				this.businessformData.value = json['awk_str'];
			}else{//search_Str
				this.businessformData.match_type = 'search_str';
				this.businessformData.value = json['search_str'];
			}
		}
	},

	methods: {
		hideDialog: function(){
			//重置初始化数据
			for(let key in this.copybusinessformData){
				this.businessformData[key] = this.copybusinessformData[key];
			}
			this.$store.dispatch("getBusinessList",{"group_id":this.groupId,"tag":'BUSINESS'});
			document.getElementsByClassName("dialog")[0].style.display = "none";
		},
		addBusiness:function(){
			this.businessformData.name = vueGetData.trim(this.businessformData.name);
			this.businessformData.value = vueGetData.trim(this.businessformData.value);
			this.businessformData.log_path = vueGetData.trim(this.businessformData.log_path);

			let data = {};
			data["group_id"] = this.groupId;
			data["tag"] = 'BUSINESS';

			if(!this.businessformData.name){
				vueGetData.creatTips("请填写采集项名称");
				return false;
			}
			if(!this.businessformData.value){
				vueGetData.creatTips("请填写匹配内容");
				return false;
			}
			if(!this.businessformData.log_path){
				vueGetData.creatTips("请填写日志路径");
				return false;
			}
			if(this.businessformData.log_style=='newly'){
				if(!this.businessformData.log_format){
					vueGetData.creatTips("请填写时间格式");
					return false;
				}
				this.businessformData.log_format = vueGetData.trim(this.businessformData.log_format);
				data["log_format"] = this.businessformData.log_format;
			}
			if(!this.businessformData.example){
				vueGetData.creatTips("请填写日志样例");
				return false;
			}
			data["filt_str"] = this.businessformData.filter;
			data["log_eg"] = this.businessformData.example;

			data["name"] = this.businessformData.name;
			data["match_type"] = this.businessformData.match_type;
			data["val"] = this.businessformData.value;
			data["log_path"] = this.businessformData.log_path;
			data["log_style"] = this.businessformData.log_style;
			data["log_format"] = this.businessformData.log_format;

	        let _self = this;
	        if(this.businessformData.isEdit){
	        	vueGetData.getData("setgroupconf",data,function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		if(jsondata.body.data == 1){
		        			vueGetData.creatTips("修改成功");

			        		this.$store.dispatch("getBusinessList",{"group_id":this.groupId,"tag":'BUSINESS'});
			        		_self.hideDialog();
		        		}else{
		        			vueGetData.creatTips("未做任何修改");
		        			_self.hideDialog();
		        		}
		        	}else if(jsondata.body.error_code === 22001){
		        		if(jsondata.body.error_msg == "match error"){
		        			vueGetData.creatTips("日志样例匹配失败，请重新填写");
		        		}else{
		        			vueGetData.creatTips("系统错误请联系管理员查看问题");
		        			_self.hideDialog();
		        		}
		        	}
		        }.bind(this),function(){

		        }.bind(this));
	        }else{
	        	vueGetData.getData("addgroupconf",data,function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		if(jsondata.body.data == 1){
		        			vueGetData.creatTips("添加成功");

			        		this.$store.dispatch("getBusinessList",{"group_id":this.groupId,"tag":'BUSINESS'});
			        		_self.hideDialog();
		        		}else{
		        			vueGetData.creatTips("添加失败,请确认无重复采集项");
		        		}
		        	}else if(jsondata.body.error_code === 22001){
		        		if(jsondata.body.error_msg == "match error"){
		        			vueGetData.creatTips("日志样例匹配失败，请重新填写");
		        		}else{
		        			vueGetData.creatTips("系统错误请联系管理员查看问题");
		        			_self.hideDialog();
		        		}
		        	}else if(jsondata.body.error_code === 22003){
		        		vueGetData.creatTips("采集项重名，请修改后重试");
		        	}
		        }.bind(this),function(){

		        }.bind(this));
	        }
		}
	},
	created: function(){
		this.groupId = this.$route.params.groupid;
		//保存初始化数据
		for(let key in this.businessformData){
			this.copybusinessformData[key] = this.businessformData[key]
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
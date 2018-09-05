<template>
	<div class="dialog">
		<div class="dialogOverlay"></div>
		<div class="dialogContent">
			<div class="dialogClose" @click="hideDialog"></div>
			<div class="dialogDetail">
				<div class="dialogTitle">{{dialogTitle}}</div>
				<div class="dialogDiv">
					<div class="dialogSubDiv">
						<div class="subDivTitle requiredItem">屏蔽类型</div>
						<div class="subDivContent">
							<label><input type="radio" name="type" value="0" v-model="shieldType" id="type0"><span for="type0">短信/微信/邮件</span></label>
							<label><input type="radio" name="type" value="1" v-model="shieldType" id="type1"><span for="type1">短信/微信</span></label>
							<label><input type="radio" name="type" value="2" v-model="shieldType" id="type2"><span for="type2">短信</span></label>
						</div>
					</div>
					<div class="dialogSubDiv">
						<div class="subDivTitle requiredItem">屏蔽时间</div>
						<div class="subDivContent">
							<input class="validtime" type="text" v-model="startTime"> 至 <input class="validtime" type="text" v-model="endTime">
						</div>
					</div>
					<div class="dialogButtonDiv">
						<a href="javascript:;" class="dialog-blue-button" @click="screenEvent">保存</a>
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
	name: "DialogError",
	data () {
		return {
			dialogTitle:"异常屏蔽",
			shieldType:0,
			startTime: "00:00",
			endTime:"23:59",
			showId:"",
		}
	},
	props: ["editinitformdata2"],
	computed:mapGetters({
	}),
	watch: {
		editinitformdata2:function(){
			this.showId = this.editinitformdata2["show_id"];
			this.startTime = this.stampToTime(Date.parse(new Date())/1000);
			this.endTime = this.stampToTime(Date.parse(new Date())/1000+3600);
		}
	},

	methods: {
		hideDialog: function(){
			this.shieldType=0;
			document.getElementsByClassName("dialog")[1].style.display = "none";
		},
		stampToTime: function(timeStamp) {//时间戳转化为时间
		    var date = new Date();
		    date.setTime(timeStamp * 1000);
		    var y = date.getFullYear();
		    var m = date.getMonth() + 1;
		    m = m < 10 ? ('0' + m) : m;
		    var d = date.getDate();
		    d = d < 10 ? ('0' + d) : d;
		    var h = date.getHours();
		    h = h < 10 ? ('0' + h) : h;
		    var minute = date.getMinutes();
		    var second = date.getSeconds();
		    minute = minute < 10 ? ('0' + minute) : minute;
		    second = second < 10 ? ('0' + second) : second;
		    return y + '-' + m + '-' + d+' '+h+':'+minute+':'+second;
		},
		timeToStamp: function(time){
			var timestamp = Date.parse(new Date(time));
			timestamp = timestamp / 1000;
			return timestamp;
		},
		screenEvent:function(){
			let data={};
			data["show_status"] = 1;
			data["show_id"] = this.showId;
			data["shield_type"] = this.shieldType;
			if(!this.startTime){
				vueGetData.creatTips("请填写屏蔽开始时间");
				return;
			}
			if(!this.endTime){
				vueGetData.creatTips("请填写屏蔽结束时间");
				return;
			}
			data["start_time"] = this.timeToStamp(this.startTime);
			data["end_time"] = this.timeToStamp(this.endTime);
			if(data["start_time"]>(Date.parse(new Date())/1000+604800)){
				vueGetData.creatTips("屏蔽开始时间不能超过7天");
				return;
			}
			if(data["end_time"]>(Date.parse(new Date())/1000+604800)){
				vueGetData.creatTips("屏蔽结束时间不能超过7天");
				return;
			}

			vueGetData.getData("updateeventstatus",data,function(jsondata){
	        	if(jsondata.body.error_code == 22000){
	        		vueGetData.creatTips("屏蔽成功");
	        		this.hideDialog()
	        		this.$store.dispatch("getAlertlist",{});
	        	}else{
	        		vueGetData.creatTips("屏蔽失败");
	        	}
	        }.bind(this),function(){

	        }.bind(this));
		}
	},
	created: function(){
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
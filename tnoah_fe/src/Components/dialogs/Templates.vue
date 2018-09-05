<template>
	<div class="dialog">
		<div class="dialogOverlay"></div>
		<div class="dialogContent">
			<div class="dialogClose" @click="hideDialog"></div>
			<div class="dialogDetail">
				<div class="dialogTitle">{{templateformData.dialogTitle}}</div>
				<div class="dialogDiv">
					<div class="dialogSubDiv">
						<div class="subDivTitle requiredItem">模板id</div>
						<div class="subDivContent"><input type="text" class="dataport-dialog-referer" v-model="templateformData.id" readonly="readonly"></div>
					</div>
					<div class="dialogSubDiv">
						<div class="subDivTitle requiredItem">复制集群</div>
						<div class="subDivContent">
							<el-tree :data="groups" show-checkbox node-key="id" :default-expanded-keys="[0]" ref="tree">
							</el-tree>
						</div>
					</div>
					<div class="dialogButtonDiv">
						<a href="javascript:;" class="dialog-blue-button" @click="saveCopy">保存</a>
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
			templateformData: {
				dialogTitle: '复制模板',
				id:"",
			},
			groups:[{
				id:0,
				label:"服务树",
				children:[]
			}],
		}
	},
	props: ["editinitformdata"],
	computed:mapGetters({
		treelist:"treelist",
	}),
	watch: {
		editinitformdata:function(){
			let json = this.editinitformdata;
			this.templateformData.id = json["tpl_id"];
		},
		treelist: function(){
			for(var i=0;i<this.treelist.length;i++){
				//获取一级目录
				let data={};
				data["id"] = this.treelist[i].id;
				data["label"] = this.treelist[i].service_line_name;
				data["children"] = [];
				vueGetData.getData("getgroupinfos",{"service_line_id":this.treelist[i].id},function(jsondata){
		        	if(jsondata.body.error_code === 22000){
		        		let list = jsondata.body.data;
		        		for (var j=0;j<list.length;j++){
		        			let tempData = {"id":list[j].id,"label":list[j].group_name};
		        			data["children"].push(tempData);
		        		}
		        	}
		        }.bind(this),function(){

		        }.bind(this));

		        this.groups[0].children.push(data)
			}
		}
	},

	methods: {
		hideDialog: function(){
			//重置初始化数据
			for(let key in this.copytemplateformData){
				this.templateformData[key] = this.copytemplateformData[key];
			}
			this.groups=[{
				id:0,
				//value:0,
				label:"服务树",
				children:[]
			}]
			document.getElementsByClassName("dialog")[0].style.display = "none";
		},
		saveCopy:function(){
			let data={}
			data["tpl_id"] = this.templateformData.id;
			if(this.$refs.tree.getCheckedNodes()[0].id==0){
				data["is_all"] = 1;
			}else{
				let groupIds=this.$refs.tree.getCheckedKeys();
				if(groupIds.length==0){
					vueGetData.creatTips("请选择有效集群");
					return false;
				}
				let groupIdsStr="";
				for(let i=0;i<groupIds.length;i++){
					groupIdsStr = groupIdsStr+groupIds[i]+","
				}
				data["group_id"] = groupIdsStr.substring(0,groupIdsStr.length-1);
			}

			vueGetData.getData("copystrategy",data,function(jsondata){
	        	if(jsondata.body.error_code === 22000){
	        		vueGetData.creatTips("复制模板成功");
	        	}else if(jsondata.body.error_code === 22001){
	        		vueGetData.creatTips("复制模板失败");
	        	}else if(jsondata.body.error_code === 22002){
	        		vueGetData.creatTips("复制模板部分失败");
	        	}
	        }.bind(this),function(){

	        }.bind(this));

			this.hideDialog();
		}
	},
	created(){
	}

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
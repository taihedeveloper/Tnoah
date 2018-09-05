<template>
	<div class="dialog">
		<div class="dialogOverlay"></div>
		<div class="dialogContent">
			<div class="dialogClose" @click="hideDialog"></div>
			<div class="dialogDetail">
				<div class="dialogTitle">{{dialogTitle}}</div>
				<div class="dialogDiv">
					<table class="manage-table">
						<thead>
							<tr>
								<th width="5%"><input type="checkbox" name="check-all" disabled="disabled"></th>
								<th width="95%">异常机器</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="(item,index) in errorList">
								<td><input type="checkbox" name="check-one" disabled="disabled"></td>
								<td>{{item}}</td>
							</tr>
						</tbody>
					</table>
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
			dialogTitle:"异常详情",
			errorList:[]
		}
	},
	props: ["editinitformdata1"],
	computed:mapGetters({
	}),
	watch: {
		editinitformdata1:function(){
			this.errorList=this.editinitformdata1.split("|")
		}
	},

	methods: {
		hideDialog: function(){
			document.getElementsByClassName("dialog")[0].style.display = "none";
		},
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
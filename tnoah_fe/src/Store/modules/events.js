import * as  types from '../mutation-types.js'
import vueGetData from "../../Js/vueGetData.js"

 //定义变量
const state = {
    alertlist:[],
}

//事件处理：异步请求、判断、流程控制
const actions = {
    getAlertlist:function({commit},paramsJson){
        vueGetData.getData("geteventshow",paramsJson,function(jsondata){
            if(jsondata.body.error_code === 22000){
                let list = jsondata.body.data;
                commit(types.ALERTLIST,list); //将commit中指定的名称放在mutation-types中定义
            }
        },function(err){
            console.log(err)
        })
    },
}
//处理状态、数据的变化
const mutations = {
    [types.ALERTLIST](state , list){ //ALERTLIST 需使用[]
        state.alertlist = list;
    },
}

//导出数据
const getters = {
    alertlist(state){
        return state.alertlist;
    },
}

export default{
    state,
    actions,
    mutations,
    getters
}
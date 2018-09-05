import * as  types from '../mutation-types.js'
import vueGetData from "../../Js/vueGetData.js"

 //定义变量
const state = {
    treelist:[],
    treeId:null,
}

//事件处理：异步请求、判断、流程控制
const actions = {
    getTreeList:function({commit},paramsJson){
        vueGetData.getData("getallserviceline",paramsJson,function(jsondata){
            if(jsondata.body.error_code === 22000){
                let list = jsondata.body.data;
                commit(types.TREELIST,list); //将commit中指定的名称放在mutation-types中定义
            }   
        },function(err){
            console.log(err)
        })
    },
    pushTreeId: function({commit},paramsJson) {
        let id = paramsJson.id;
        commit(types.TREEID,id)
    },
}
//处理状态、数据的变化
const mutations = {
    [types.TREELIST](state , list){ //TREELIST 需使用[]
        state.treelist = list;
    },
    [types.TREEID](state , id){
        state.treeId = id;
    },
}

//导出数据
const getters = {
    treelist(state){
        return state.treelist;
    },
    treeId(state){
        return state.treeId;
    },
}

export default{
    state,
    actions,
    mutations,
    getters
}
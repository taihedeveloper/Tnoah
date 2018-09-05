import * as  types from '../mutation-types.js'
import vueGetData from "../../Js/vueGetData.js"

 //定义变量
const state = {
    serviceLineIndex:null,
    serviceLineName:"",
    strategylist:[],
    businesslist:[],
    businessdata:{}
}

//事件处理：异步请求、判断、流程控制
const actions = {
    getServiceLineIndex: function({commit}) {
        let index = window.localStorage.getItem("serviceLineIndex");
        commit(types.SERVICELINEINDEX,index)
    },
    getServiceLineName: function({commit}) {
        let name = window.localStorage.getItem("serviceLineName");
        commit(types.SERVICELINENAME,name)
    },
    getStrategyList:function({commit},paramsJson){
        vueGetData.getData("getstrategylist",paramsJson,function(jsondata){
            if(jsondata.body.error_code === 22000){
                let list = jsondata.body.data;
                commit(types.STRATEGYLIST,list); //将commit中指定的名称放在mutation-types中定义
            }
        },function(err){
            console.log(err)
        })
    },
    getBusinessList:function({commit},paramsJson){
        vueGetData.getData("getgroupconf",paramsJson,function(jsondata){
            if(jsondata.body.error_code === 22000){
                let list = jsondata.body.data;
                commit(types.BUSINESSLIST,list); //将commit中指定的名称放在mutation-types中定义
            }else{
                console.log(jsondata.body.msg)
            }
        },function(err){
            console.log(err)
        })
    },
    pushBusinessData:function({commit},paramsJson){
        var data = paramsJson;
        commit(types.BUSINESSDATA,data);
    },

}
//处理状态、数据的变化
const mutations = {
    [types.SERVICELINEINDEX](state , index){
        state.serviceLineIndex = index;
    },
    [types.SERVICELINENAME](state , name){
        state.serviceLineName = name;
    },
    [types.STRATEGYLIST](state , list){ //STRATEGYLIST 需使用[]
        state.strategylist = list;
    },
    [types.BUSINESSLIST](state , list){ //BUSINESSLIST 需使用[]
        state.businesslist = list;
    },
    [types.BUSINESSDATA](state , data){
        state.businessdata = data;
    }
}

//导出数据
const getters = {
    serviceLineIndex(state){
        return state.serviceLineIndex;
    },
    serviceLineName(state){
        return state.serviceLineName;
    },
    strategylist(state){
        return state.strategylist;
    },
    businesslist(state){
        return state.businesslist;
    },
    businessdata(state){
        return state.businessdata;
    }
}

export default{
    state,
    actions,
    mutations,
    getters
}
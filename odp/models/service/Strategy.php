<?php
/**
*   策略CURD操作
*   @author dupeng@taihe.com
*/
class Service_Strategy {

    /**
    *   @desc   删除制定的策略
    *   @return int/NULL
    */
    public function deleteStrategy($id) {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Strategy');
        $where  = array(
            'id=' => $id,
        );
        $infos = $daoObj->doDelete($where);
        return $infos;
    }

    /**
    *   @desc   向某个模板下增加报警策略
    *   @return int/NULL
    */
    public function addStrategyByTid($param) {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Strategy');      
        $infos = $daoObj->doInsert($param);

        return $infos;
    }

    /**
    *   @desc   更新模板信息
    *   @return int/NULL
    */
    public function updateStrategy($param) {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Strategy');
        $strategyArr = array(
                'id' => $id,
                'note' => $note,
                'metric' => $metric,
                'func' => $func,
                'op' => $op,
                'right_value' => $right_value,
                'max_step' => $max_step,
                'priority' => $priority,
                'run_begin' => $run_begin,
                'run_end' => $run_end,
            );
        $update = array();
        if(isset($param['note'])){
            $update['note'] = $param['note'];
        }
        if(isset($param['metric'])){
            $update['metric'] = $param['metric'];
        }
        if(isset($param['func'])){
            $update['func'] = $param['func'];
        }
        if(isset($param['op'])){
            $update['op'] = $param['op'];
        }
        if(isset($param['right_value'])){
            $update['right_value'] = $param['right_value'];
        }
        if(isset($param['max_step'])){
            $update['max_step'] = $param['max_step'];
        }
        if(isset($param['priority'])){
            $update['priority'] = $param['priority'];
        }
        if(isset($param['run_begin'])){
            $update['run_begin'] = $param['run_begin'];
        }
        if(isset($param['run_end'])){
            $update['run_end'] = $param['run_end'];
        }        
        $where = array(
            'id=' => $param['id']
        );

        $infos = $daoObj->doUpdate($update, $where);
        return $infos;
    }

    /**
    *   @desc   查询指定模板下的策略
    *   @return array
    */
    public function getStrategy($id) {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Strategy');
		$select = array('*');
        $where = array(
            'id = ' => $id,
            );
        $infos = $daoObj->doSelect($select,$where);
        return $infos;
    }
    /**
    *   @desc   查询指定模板下的策略列表
    *   @return array
    */
    public function getStrategylistByTid($tpl_id) {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Strategy');
        $select = array('*');
        $where = array(
            'tpl_id = ' => $tpl_id,
            );
        $infos = $daoObj->doSelect($select,$where);
        return $infos;
    }

    /**
    *   @desc 报警策略复制到其他集群功能
    *   @desc 1、先复制模板基本信息  2、添加集群与模板的映射关系  3、获取策略信息并复制策略以及对应的模板
    */
    public function copyStrategy($paramAttr,&$wrongGroupId,$mapping){
        //1、先复制模板基本信息,并添加到对应的集群和模板的映射关系
        $tpl_id = $paramAttr['tpl_id'];
        $tpl_daoObj = Dao_DaoFactory::getInstance('Dao_Template');
        $select = array('*');
        $where = array(
            'id = ' => $tpl_id,
            );
        $strInfo = $tpl_daoObj->doSelect($select,$where);
        if(empty($strInfo)){
            return NULL;
        }
        $templateInfo = array();
        foreach ($strInfo as $key => $value) {
            $templateInfo['tpl_name'] = $value['tpl_name'];
            $templateInfo['create_at'] = time();

        }

        //2、读取策略信息
        $str_daoObj = Dao_DaoFactory::getInstance('Dao_Strategy');
        $select = array('*');
        $where = array(
            'tpl_id = ' => $tpl_id,
            );
        $infos = $str_daoObj->doSelect($select,$where);
        if(is_null($infos)){
            return NULL;
        }
        $strategyInfo = array();
        foreach ($infos as $key => $value) {
            $strategyArr = array(
                $value['metric'],
                "",
                $value['max_step'],
                $value['priority'],
                $value['func'],
                $value['op'],
                $value['right_value'],
                $value['note'],
                $value['run_begin'],
                $value['run_end'],
            );
            $strategyInfo[] = $strategyArr;
        }

        $group_id = $paramAttr['group_id'];
    
        $groupArr = explode(',', $group_id);
        foreach ($groupArr as $key => $value) {   

            $daoObj = Dao_DaoFactory::getInstance('Dao_Template');
            //获取产品线对应报警组uic
            $perUlr = Bd_Conf::getAppConf('permission2/url').$mapping[$value];
            $uicResult = $daoObj->getUicArrInfos($perUlr);
            if(!empty($uicResult)){
               $uic = implode(',',array_keys($uicResult));
               $templateInfo['uic'] = $uic;
            }
            
            $infos = $daoObj->doInsert($templateInfo);
            if( !$infos ||  $infos != 1){
               $wrongGroupId[]= $value;
               continue;
            }
            $tpl_id = $daoObj->getInsertID();
            //先插入模板，然后关联集群关系
            $grpdaoObj = Dao_DaoFactory::getInstance('Dao_GroupTpl');
            $insertArr2 = array(
                'group_id' => $value,
                'tpl_id' => $tpl_id,
                'bind_user' => "admin",
            ); 
            $infos = $grpdaoObj->doInsert($insertArr2);
            if( !$infos ||  $infos != 1){
               $wrongGroupId[]= $value;
               continue;
            }
            if(empty($strategyInfo)){
                continue;
            }
            //批量插入策略信息
            foreach ($strategyInfo as &$val) {
                $val[10] = $tpl_id;
            }

            $fields = "metric,tags,max_step,priority,func,op,right_value,note,run_begin,run_end,tpl_id";
            $infos = $str_daoObj->multiInsert($fields,$strategyInfo);
            if( !$infos ){
               $wrongGroupId[]= $value;
               continue;
            }

        }

        if(empty($wrongGroupId)){
            return count($groupArr);
        }else{
            return "not all yet";
        }

    }

    
        
}
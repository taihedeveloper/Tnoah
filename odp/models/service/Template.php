<?php
/**
*   模板CURD操作
*   @author dupeng@taihe.com
*/
class Service_Template {

    /**
    *   @desc   删除某个集群下的模板，暂时不删除模板信息
    *   @return int/NULL
    */
    public function deleteTemplate($deletaArr) {
        $daoObj = Dao_DaoFactory::getInstance('Dao_GroupTpl');
        $where  = array(
            'group_id = ' => $deletaArr['group_id'],
            'tpl_id = ' => $deletaArr['tpl_id'],
        );
        $infos = $daoObj->doDelete($where);
        return $infos;
    }

    /**
    *   @desc   增加集群模板
    *   @return int/NULL
    */
    public function addTemplate($param) {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Template');
        $insertArr = array(
            'tpl_name' => $param['tpl_name'],
            'create_user' => $param['create_user'],
            'create_at' => $param['create_at'],
            'uic' => $param['uic'],
        ); 
        $infos = $daoObj->doInsert($insertArr);
        if( !$infos ||  $infos != 1){
           return NULL; 
        }
        $tpl_id = $daoObj->getInsertID();
        $grpdaoObj = Dao_DaoFactory::getInstance('Dao_GroupTpl');
        $insertArr2 = array(
            'group_id' => $param['group_id'],
            'tpl_id' => $tpl_id,
            'bind_user' => $param['create_user'],
        ); 
        $infos = $grpdaoObj->doInsert($insertArr2);
                

        return $tpl_id;
    }

    /**
    *   @desc   更新某个模板信息
    *   @return int/NULL
    */
    public function updateTemplate($paramArr) {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Template');
        $update = array(); 

        if($paramArr['tpl_name'] && $paramArr['tpl_name'] != ''){
            $update['tpl_name'] = $paramArr['tpl_name'];
        } 
        if($paramArr['uic'] && $paramArr['uic'] != ''){
            $update['uic'] = $paramArr['uic'];
        }         
        $where = array(
            'id=' => $paramArr['tpl_id'],
        );
        $infos = $daoObj->doUpdate($update, $where);

        return $infos;
    }

    /**
    *   @desc 根据集群id获取所有模板信息
    *   @param $group_id
    *   @return array
    */
    public function getTemplates($group_id) {
        //获取集群相关的所有tpl id
        $daoObj = Dao_DaoFactory::getInstance('Dao_GroupTpl');
		$select = array('*');
        $where  = array(
            'group_id =' => $group_id
        );
        $infos = $daoObj->doSelect($select,$where);
        if(NULL === $infos){
            return false;
        }
        $tplidArr = array();
        foreach($infos as $tplgrp){
            $tplidArr[] = $tplgrp['tpl_id'];
        }

        if(!Tnoah_Util::isComResult($tplidArr)){
            return false;
        }
        $tplidStr = implode(',', $tplidArr);
        $tpldaoObj = Dao_DaoFactory::getInstance('Dao_Template');
        $whereTpl = array(
            '`id` IN ('.$tplidStr.')'
        );
        $infos = $tpldaoObj->doSelect($select,$whereTpl);


        
        $group_ids = array();
        foreach ($infos as $key => $value) {
           $uicTmp = $value['uic'];
           $uicArr = explode(',', $uicTmp);

           $group_ids = array_merge($group_ids,$uicArr);
        }

        $group_ids = array_unique($group_ids);
        
        $group_str = implode(',', $group_ids);
        
        //参数错误的时候，从配置文件取消息
        $perUlr = Bd_Conf::getAppConf('permission/url').$group_str;
        $uicResult = $tpldaoObj->getUicArrInfos($perUlr);
        
        
        foreach ($infos as $key => &$value) {
           $uicArr = array();
           $uicTmp = $value['uic'];
           $uicArrTmp = explode(',', $uicTmp);

           foreach ($uicArrTmp as $_ => $groupid) {
               if(!empty($uicResult) && $uicResult[intval($groupid)]){
                   $uicArr[] = $uicResult[$groupid];
               }
           }

           $value['uicArr'] = $uicArr;
        }
        
        return $infos;
    }
}

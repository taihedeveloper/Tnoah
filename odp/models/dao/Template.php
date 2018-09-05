<?php

/**
* template 模板相关操作Dao层
* @author dupeng@taihe.com
*/
class Dao_Template {

    private $_tbName = 'tnoah_tpl';
    private $_group = 'tnoah';
    
    public function doSelect($select, $where = NULL) {
        $dbObj = Dao_DBFactory::getInstance($this->_group);
        if(!$dbObj) {	
			return NULL;
        }
        $selectInfos = $dbObj->select($this->_tbName, $select, $where);
        return $selectInfos;
    }

    public function doInsert($insert) {
        $dbObj = Dao_DBFactory::getInstance($this->_group);
        if(!$dbObj) {	
			return NULL;
        }
        $insertInfos = $dbObj->insert($this->_tbName, $insert);
        return $insertInfos;
    }

    public function doDelete($delete) {
        $dbObj = Dao_DBFactory::getInstance($this->_group);
        if(!$dbObj) {	
			return NULL;
        }
        $deleteInfos = $dbObj->delete($this->_tbName, $delete);
        return $deleteInfos;
    }

    public function doUpdate($update, $where) {
        $dbObj = Dao_DBFactory::getInstance($this->_group);
        if(!$dbObj) {	
			return NULL;
        }

        $updateInfos = $dbObj->update($this->_tbName, $update, $where);
        return $updateInfos;
    }

    public function getInsertID() {
        $dbObj = Dao_DBFactory::getInstance($this->_group);
        if(!$dbObj) {   
            return NULL;
        }

        $insertIDInfos = $dbObj->getInsertID();
        return $insertIDInfos;
    }
    //批量获取报警组详细信息
     public function getUicArrInfos($url) {
        //分别获取报警组的信息
        $httpObj = new Tnoah_HttpProxy();
        $ret = $httpObj->get($url);
        $ret = json_decode($ret,true);
        $result = array();
        if(!empty($ret["error_code"])&&$ret["error_code"] == 22000 ){
            foreach($ret['result'] as $key => $value) {
                if($value && $value['group_id']){
                    $result[$value['group_id']] = $value;
                }
            }

            return $result;
        }else{
            return array();
        }
    }



}
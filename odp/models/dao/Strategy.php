<?php

/**
* strategy 策略相关操作Dao层
* @author dupeng@taihe.com
*/
class Dao_Strategy {

    private $_tbName = 'tnoah_strategy';
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

    public function multiInsert($fields,$insert) {
        $dbObj = Dao_DBFactory::getInstance($this->_group);
        if(!$dbObj) {   
            return NULL;
        }
        $insertInfos = $dbObj->multiInsert($this->_tbName, $fields,$insert);
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

}
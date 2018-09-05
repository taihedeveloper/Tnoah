<?php

/**
* Machine Dao层
* @author zhangweizhi@taihe.com
*/
class Dao_Machine {

    private $_tbName = 'tnoah_machine';
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

}
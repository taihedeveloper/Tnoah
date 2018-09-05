<?php
/**
*   机器CURD操作
*   @author zhangweizhi@taihe.com
*/
class Service_Metric {

    /**
    *   @desc   删除指定监控项
    *   @return int/NULL
    */
    public function deleteMetric($id) {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Metric');
        $where  = array(
            'id=' => $id,
        );
        $infos = $daoObj->doDelete($where);
        return $infos;
    }

    /**
    *   @desc   增加监控项
    *   @return int/NULL
    */
    public function addMetric($name, $tag, $status, $add_user, $addition) {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Metric');
        $insert = array(
            'metric_name'         => $name,
            'tag'                 => $tag,
            'cur_status'          => $status,
            'add_time'            => time(),
            'add_user'            => $add_user,
            'addition'            => $addition,
        );        
        $infos = $daoObj->doInsert($insert);
        return $infos;
    }

    /**
    *   @desc   更新指定监控项
    *   @return int/NULL
    */
    public function updateMetric($id, $name, $tag, $status, $add_user, $addition) {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Metric');
        $update = array(
            'metric_name'         => $name,
            'tag'                 => $tag,
            'cur_status'          => $status,
            'add_user'            => $add_user,
            'addition'            => $addition,
        );         
        $where = array(
            'id=' => $id
        );
        $infos = $daoObj->doUpdate($update, $where);
        return $infos;
    }

    /**
    *   @desc   查询监控项
    *   @return array
    */
    public function getMetricInfos() {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Metric');
		$select = array('*');
        $infos = $daoObj->doSelect($select);
        return $infos;
    }

    /**
    *   @desc  查询指定性能类监控项
    *   @param 
    *   @return array
    */
    public function getNatureInfos($tag) {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Metric');
        $select = array('*');
        $where  = array(
            'tag=' => $tag,
        );
        $infos = $daoObj->doSelect($select, $where);
        return $infos;
    }

    /**
    *   @desc  查询全部性能类监控项名称
    *   @param 
    *   @return array
    */
    public function getAllNatureInfos() {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Metric');
        $select = array('metric_name', 'tag', 'addition');
        $where = array(
            '`tag` IN ('."'CPU', 'MEM', 'DISK', 'DISK_IO', 'NET', 'SELF_CHECK'".')'
        );
        $infos = $daoObj->doSelect($select, $where);
        return $infos;
    }

    // /**
    // *   @desc   查询端口监控项
    // *   @param  集群id
    // *   @return array
    // */
    // public function getPortMetrics($groupId) {
        
    // }

    // /**
    // *   @desc   查询进程监控项
    // *   @return array
    // */
    // public function getProcMetrics($groupId) {

    // }

    // /**
    // *   @desc   查询业务监控项
    // *   @return array
    // */
    // public function getBussinessMetrics($groupId) {
        
    // }

    // /**
    // *   @desc   查询性能监控项
    // *   @param  性能标签(CPU/MEM/DISK/IO/NETCARD)
    // *   @return array
    // */
    // public function getNatureMetrics($tag) {
        
    // }
}

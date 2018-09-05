<?php

/**
 * Created by PhpStorm.
 * User: ydp
 * Date: 2017/8/30
 * Time: 17:02
 */
class Dao_SparkTask
{
    private $db;
    private $tbName;

    public function __construct() {
        $this->db = Dao_DBFactory::getInstance('tnoah');
        $this->tbName = "tnoah_spark_task";
    }

    public function insertSparkTask($pid, $pname,$taskid){
        $insertArr = array(
            'pid' => $pid,
            'pname' => $pname,
            'taskid' => $taskid,
        );

        $insertResult = $this->db->insert($this->tbName, $insertArr);
        if(NULL === $insertResult) {
           return false;
        }
        return true;
    }

    public function updateSparkTask($pid,$taskid,$status){
        $insertArr = array(
            'taskid' => $taskid,
            'status' => $status,
        );
        $conds = array(
            'pid=' => $pid,
        );

        $insertResult = $this->db->update($this->tbName, $insertArr,$conds);
        if(NULL === $insertResult) {
            return false;
        }
        return true;
    }

    public function getSparkTask($pid){
        $arr = array(
            'id' => id,
            'taskid' => taskid,
            'pname' => pname,
            'status' => status,
            'app_name' => app_name,
            'table_name' => table_name,
            'jar_name' => jar_name,
        );
        $conds = array(
            'pid=' => $pid,
        );

        $selResult = $this->db->select($this->tbName, $arr,$conds);
        if(NULL === $selResult) {
            return false;
        }
        return $selResult;
    }

    public function getAllSparkTask(){
        $arr = array(
            'taskid' => taskid,
            'id' => id,
            'pid' => pid,
            'pname' => pname,
            'status' => status,
            'jar_name' => jar_name,
        );

        $selResult = $this->db->select($this->tbName, $arr,null);
        if(NULL === $selResult) {
            return false;
        }
        return $selResult;
    }

}
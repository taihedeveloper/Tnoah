<?php
/**
 * @name Service_Spark_SparkTask
 * @desc
 * @author ydp
 */

class Service_SparkTask
{
    private $dao;

    public function __construct() {
        $this->dao = Dao_DaoFactory::getInstance("Dao_SparkTask");
    }

    public function insertSparkTask($pid, $pname, $taskid){

        $insertResult = $this->dao->insertSparkTask($pid, $pname, $taskid);
        if(NULL === $insertResult) {
           return false;
        }
        return true;
    }

    public function updateSparkTask($pid,$taskid,$status){
        $insertResult = $this->dao->updateSparkTask($pid, $taskid,$status);
        if(NULL === $insertResult) {
            return false;
        }
        return true;
    }

    public function getSparkTask($pid){
        $selResult = $this->dao->getSparkTask($pid);
        if(NULL === $selResult) {
            return false;
        }
        return $selResult;
    }

    public function getAllSparkTask(){
        $selResult = $this->dao->getAllSparkTask();
        if(NULL === $selResult) {
            return false;
        }
        return $selResult;
    }

}
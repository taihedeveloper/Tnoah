<?php
/**
 * @name Action_SparkStatus
 * @desc 查询spark任务状态
 * @author ydp
 */
 class Action_SparkStatus extends Ap_Action_Abstract {

     public function execute() {
         $arrRequest = Saf_SmartMain::getCgi();
         $arrInput = $arrRequest['get'];
         $pid = $arrInput["pid"];

         $service = Service_LibFactory::getInstance("Service_SparkTask");
         $ret = $service->getSparkTask($pid);
         if(empty($ret)){
             $result["error"] = 22001;
             Tnoah_OutPutUtil::response($result);
         }

         $taskid = $ret[0]["taskid"];

         //设置spark地址
         $url = "http://bj-music-data-hadoop2.tj:6066/v1/submissions/status/".$taskid;

         //提交任务
         $httpObj = new Tnoah_HttpProxy();
         $ret = $httpObj->get($url);
         $ret = json_decode($ret,true);
         $result = array();
         if(!empty($ret)){
             $result["error"] = 22000;
             $result["driverState"] = $ret["driverState"];
             $result["message"] = $ret["message"];
             Tnoah_OutPutUtil::response($result);
         }else{
             $result["error"] = 22001;
             Tnoah_OutPutUtil::response($result);
         }
     }
 }
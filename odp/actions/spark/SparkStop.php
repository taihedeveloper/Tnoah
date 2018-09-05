<?php
/**
 * @name Action_SparkStop
 * @desc kill spark任务
 * @author ydp
 */
 class Action_SparkStop extends Ap_Action_Abstract {
     public function execute() {

         $arrRequest = Saf_SmartMain::getCgi();
         $arrInput = $arrRequest['get'];
         $id = $arrInput["id"];
         $pid = $arrInput["pid"];

         $result = array();

         //先查出pid对应的taskid
         $service = Service_LibFactory::getInstance("Service_SparkTask");
         $ret = $service->getSparkTask($pid);
         if(empty($ret)){
             $result["error"] = 22001;
             Tnoah_OutPutUtil::response($result);
         }

         $taskid = $ret[0]["taskid"];


         //设置spark地址
         $url = "http://***.***.***.***:port/v1/submissions/kill/".$taskid;

         //提交任务
         $httpObj = new Tnoah_HttpProxy();
         $ret = $httpObj->post($url);
         $ret = json_decode($ret,true);
         $result = array();
         if(!empty($ret)&&$ret["success"] == true){
             $result["error"] = 22000;
             //把id更新进DB
             $service = Service_LibFactory::getInstance("Service_SparkTask");
             $rr = $service->updateSparkTask($pid, 0,-1);
             //end
             $result["driverState"] = $ret["driverState"];
             $result["message"] = $ret["message"];
             Tnoah_OutPutUtil::response($result);
         }else{
             $result["error"] = 22001;
             $result["message"] = $ret["message"];
             Tnoah_OutPutUtil::response($result);
         }
     }
 }